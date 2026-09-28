<?php

namespace App\Services;

use App\Models\Diamond;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DiamondImportService
{
    const LOCK_NAME = 'shreeji_diamond_import';
    const CHUNK_SIZE = 200;
    const MAX_COLUMNS = 120;
    const MAX_ROWS = 100000;

    private $table;
    private $temp;
    private $backup;
    private $fillable;
    private $limits;
    private $runtime;
    private $lockOwner;
    private $cleanupAllowed = false;
    private $stagingReady = false;

    public function import($path)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        DB::disableQueryLog();
        $this->stagingReady = false;
        $this->claimLock('pid:' . getmypid());

        try {
            $this->acquireDatabaseLock();
            $this->bootFresh();
            $reader = new DiamondSheetReader();
            $chunk = [];
            foreach ($reader->rows($path) as $sheetRow) {
                if ($this->runtime['header'] === null) {
                    if ((int) $sheetRow['index'] !== 1) {
                        throw new RuntimeException('The Excel file is missing a header row.');
                    }
                    $this->runtime['header'] = $this->buildHeader($sheetRow['cells']);
                    continue;
                }
                $chunk[] = $sheetRow['cells'];
                if (count($chunk) >= self::CHUNK_SIZE) {
                    $this->ingestRows($chunk);
                    $chunk = [];
                }
            }
            if ($chunk !== []) {
                $this->ingestRows($chunk);
            }
            $this->commit();
        } catch (\Throwable $e) {
            if ($this->stagingReady) {
                $this->dropTemp();
                $this->stagingReady = false;
            }
            throw $e;
        } finally {
            $this->releaseDatabaseLock();
            $this->releaseLockFile();
        }
    }

    public function clientRules()
    {
        $fillable = (new Diamond())->getFillable();
        $limits = [];
        try {
            $this->prepareNames();
            $limits = $this->columnLimits($this->table);
        } catch (\Throwable $e) {
            $limits = [];
        }

        $columns = [];
        foreach ($fillable as $column) {
            if (isset($limits[$column])) {
                $columns[$column] = $limits[$column];
            } else {
                $columns[$column] = ['kind' => 'string', 'length' => 255];
            }
        }
        if (isset($columns['stock_id'])) {
            $columns['stock_id']['required'] = true;
        }

        return [
            'batchSize' => self::CHUNK_SIZE,
            'columns' => $columns,
        ];
    }

    public function startClientImport(array $headerCells)
    {
        @set_time_limit(0);
        DB::disableQueryLog();
        $this->cleanupAllowed = false;
        $this->stagingReady = false;
        $this->claimLock($this->owner());

        try {
            $this->acquireDatabaseLock();
            $this->bootFresh();
            $token = bin2hex(random_bytes(16));
            $this->runtime['token'] = $token;
            $this->runtime['header'] = $this->buildHeader($headerCells);
            $this->rememberRuntime();
            $this->cleanupAllowed = true;

            return ['token' => $token];
        } catch (\Throwable $e) {
            if ($this->stagingReady) {
                $this->dropTemp();
                $this->stagingReady = false;
            }
            $this->forgetRuntime();
            $this->releaseLockFile();
            throw $e;
        } finally {
            $this->releaseDatabaseLock();
        }
    }

    public function appendClientRows($token, array $rows)
    {
        @set_time_limit(0);
        DB::disableQueryLog();
        $this->cleanupAllowed = false;

        try {
            $this->touchLock($this->owner());
            $this->acquireDatabaseLock();
            $this->restoreRuntime($token);
            if (count($rows) > self::CHUNK_SIZE) {
                throw new RuntimeException('The import batch is too large.');
            }
            $results = $this->ingestRows($rows);
            $this->rememberRuntime();
            $progress = $this->progress();
            $progress['results'] = $results;

            return $progress;
        } catch (\Throwable $e) {
            if ($this->cleanupAllowed) {
                $this->dropTemp();
                $this->forgetRuntime();
                $this->releaseLockFile();
            }
            throw $e;
        } finally {
            $this->releaseDatabaseLock();
        }
    }

    public function finishClientImport($token)
    {
        @set_time_limit(0);
        DB::disableQueryLog();
        $this->cleanupAllowed = false;
        $this->touchLock($this->owner());
        $this->acquireDatabaseLock();

        try {
            $this->restoreRuntime($token);
            $progress = $this->progress();
            $this->commit();
            $this->forgetRuntime();
            $this->releaseLockFile();

            return $progress;
        } catch (\Throwable $e) {
            if ($this->cleanupAllowed) {
                $this->dropTemp();
                $this->forgetRuntime();
                $this->releaseLockFile();
            }
            throw $e;
        } finally {
            $this->releaseDatabaseLock();
        }
    }

    public function cancelClientImport($token)
    {
        try {
            $runtime = Cache::get($this->cacheKey());
            if (!is_array($runtime) || $token === '' || !isset($runtime['token']) || !hash_equals((string) $runtime['token'], (string) $token)) {
                return;
            }

            $this->claimLock($this->owner());
            $this->acquireDatabaseLock();
            try {
                $this->prepareNames();
                $this->dropTemp();
            } finally {
                $this->releaseDatabaseLock();
                $this->forgetRuntime();
                $this->releaseLockFile();
            }
        } catch (\Throwable $e) {
            // Cancel is a cleanup request and must not take down a newer import.
        }
    }

    public function buildHeader(array $cells)
    {
        $header = [];
        $used = [];
        foreach ($this->trimHeaderCells($cells) as $value) {
            $name = $this->canonicalColumn($this->formatColumn($value));
            if ($name === '') {
                $name = 'column';
            }
            $base = $name;
            $suffix = 2;
            while (isset($used[$name])) {
                $name = $base . '_' . $suffix;
                $suffix++;
            }
            $used[$name] = true;
            $header[] = $name;
        }

        if (count($header) > self::MAX_COLUMNS) {
            throw new RuntimeException('The Excel file has too many columns.');
        }
        if (!in_array('stock_id', $header, true)) {
            throw new RuntimeException('The Excel file must include a Stock Id column.');
        }

        return $header;
    }

    public function formatColumn($column)
    {
        $column = str_replace(["\xC2\xA0", "\r", "\n", "\t"], ' ', (string) $column);
        $column = trim(preg_replace('/\s+/u', ' ', $column));

        return strtolower(str_replace(' ', '_', str_replace('%', 'percentage', str_replace('#', 'number', str_replace('&', 'and', $column)))));
    }

    public function trimHeaderCells(array $cells)
    {
        $width = 0;
        foreach (array_values($cells) as $index => $value) {
            if ($value !== null && trim((string) $value) !== '') {
                $width = $index + 1;
            }
        }
        if ($width < 1) {
            throw new RuntimeException('The Excel file is missing a header row.');
        }

        return array_slice(array_values($cells), 0, $width);
    }

    /**
     * @param array $seenStockIds lowercase stock ids already accepted or already stored
     */
    public function rowIssues(array $header, array $cells, array $limits, array $seenStockIds)
    {
        $width = count($header);
        $record = array_combine($header, $this->alignCells($cells, $width));
        if (!is_array($record)) {
            return [
                'blank' => false,
                'errors' => ['The row does not match the header.'],
                'record' => [],
                'stock_id' => '',
                'stock_key' => '',
            ];
        }
        if (!$this->rowHasValue($record)) {
            return [
                'blank' => true,
                'errors' => [],
                'record' => $record,
                'stock_id' => '',
                'stock_key' => '',
            ];
        }

        $record = $this->normalizeRow($record);
        $errors = [];
        $stock = isset($record['stock_id']) ? trim((string) $record['stock_id']) : '';
        $stockKey = $stock === '' ? '' : mb_strtolower($stock, 'UTF-8');
        if ($stock === '') {
            $errors[] = 'Stock id is required.';
        } elseif (isset($seenStockIds[$stockKey])) {
            $errors[] = 'Duplicate stock id "' . $stock . '".';
        }

        foreach ($limits as $column => $limit) {
            if (!isset($record[$column]) || $record[$column] === null || $record[$column] === '') {
                continue;
            }
            if (!isset($limit['kind'])) {
                continue;
            }
            $value = $record[$column];
            if ($limit['kind'] === 'decimal') {
                if (!is_numeric($value)) {
                    $errors[] = $this->columnLabel($column) . ' must be a number.';
                    continue;
                }
                $scale = (int) $limit['scale'];
                $formatted = sprintf('%.' . $scale . 'f', (float) $value);
                $integer = ltrim((string) strtok($formatted, '.'), '-');
                $integerDigits = (int) $limit['precision'] - $scale;
                if (strlen($integer) > $integerDigits) {
                    $errors[] = $this->columnLabel($column) . ' is too large.';
                }
            } elseif ($limit['kind'] === 'string' && !is_numeric($value) && strlen((string) $value) > (int) $limit['length']) {
                $errors[] = $this->columnLabel($column) . ' is longer than ' . (int) $limit['length'] . ' characters.';
            }
        }

        return [
            'blank' => false,
            'errors' => $errors,
            'record' => $record,
            'stock_id' => $stock,
            'stock_key' => $stockKey,
        ];
    }

    public function normalizeRow(array $value)
    {
        $reportDate = $this->cellValue($value, 'report_date');
        $value['report_date'] = $reportDate !== '' ? $reportDate : date('Y-m-d');

        $length = (float) $this->cellValue($value, 'length');
        $width = (float) $this->cellValue($value, 'width');
        $ratio = $this->cellValue($value, 'ratio');
        $value['ratio'] = ($ratio !== '' && $ratio !== '0')
            ? sprintf('%.2f', (float) $ratio)
            : sprintf('%.2f', $length / ($width > 0 ? $width : 1));

        $weight = (float) $this->cellValue($value, 'weight');
        $liveRap = (float) $this->cellValue($value, 'live_rap');
        $rapAmount = $this->cellValue($value, 'rap_amount');
        $value['rap_amount'] = ($rapAmount !== '' && $rapAmount !== '0')
            ? sprintf('%.2f', (float) $rapAmount)
            : sprintf('%.2f', $weight * $liveRap);

        $price = $this->cellValue($value, 'price_per_carat');
        $discounts = (float) $this->cellValue($value, 'discounts');
        $value['price_per_carat'] = ($price !== '' && $price !== '0')
            ? sprintf('%.2f', (float) $price)
            : sprintf('%.2f', ($liveRap * ($discounts / 100)) + $liveRap);

        $total = $this->cellValue($value, 'total_price');
        $value['total_price'] = ($total !== '' && $total !== '0')
            ? sprintf('%.2f', (float) $total)
            : sprintf('%.2f', $weight * (float) $value['price_per_carat']);

        $bargain = $this->cellValue($value, 'bargaining_price_per_carat');
        $value['bargaining_price_per_carat'] = ($bargain !== '' && $bargain !== '0')
            ? sprintf('%.2f', (float) $bargain)
            : sprintf('%.2f', (float) $bargain);

        $bargainTotal = $this->cellValue($value, 'bargaining_total_price');
        $value['bargaining_total_price'] = ($bargainTotal !== '' && $bargainTotal !== '0')
            ? sprintf('%.2f', (float) $bargainTotal)
            : sprintf('%.2f', $weight * (float) $value['bargaining_price_per_carat']);

        return $value;
    }

    private function prepareNames()
    {
        $this->table = (new Diamond())->getTable();
        $this->temp = $this->table . '_import_new';
        $this->backup = $this->table . '_import_old';
    }

    private function bootFresh()
    {
        $this->prepareNames();
        $this->fillable = (new Diamond())->getFillable();
        $this->limits = $this->columnLimits($this->table);
        $this->runtime = [
            'token' => null,
            'header' => null,
            'data_rows' => 0,
            'stored' => 0,
            'skipped_stock' => 0,
            'skipped_duplicate' => 0,
            'skipped_invalid' => 0,
            'now' => now()->toDateTimeString(),
        ];
        $this->dropTemp();
        $this->stagingReady = true;
        DB::statement('CREATE TABLE ' . $this->quote($this->temp) . ' LIKE ' . $this->quote($this->table));
        DB::statement('ALTER TABLE ' . $this->quote($this->temp) . ' AUTO_INCREMENT = 1');
    }

    private function restoreRuntime($token)
    {
        $runtime = Cache::get($this->cacheKey());
        if (!is_array($runtime) || $token === '' || empty($runtime['header']) || !is_array($runtime['header']) || !isset($runtime['token']) || !hash_equals((string) $runtime['token'], (string) $token)) {
            throw new RuntimeException('The import expired. Please choose the file again.');
        }
        $this->prepareNames();
        $this->fillable = (new Diamond())->getFillable();
        $this->limits = $this->columnLimits($this->table);
        $this->runtime = $runtime;
        $this->cleanupAllowed = true;
    }

    private function ingestRows(array $rows)
    {
        $header = $this->runtime['header'];
        $seen = $this->existingStockKeys($header, $rows);
        $results = [];
        $pending = [];

        foreach ($rows as $cells) {
            if (!is_array($cells)) {
                $results[] = ['ok' => false, 'error' => 'The row could not be read.'];
                $this->runtime['skipped_invalid']++;
                continue;
            }

            try {
                $issues = $this->rowIssues($header, $cells, $this->limits, $seen);
            } catch (RuntimeException $e) {
                $results[] = ['ok' => false, 'error' => $e->getMessage()];
                $this->runtime['skipped_invalid']++;
                continue;
            }

            if ($issues['blank']) {
                $results[] = ['ok' => true, 'blank' => true];
                continue;
            }

            $this->runtime['data_rows']++;
            if ($this->runtime['data_rows'] > self::MAX_ROWS) {
                throw new RuntimeException('The Excel file has too many rows to import.');
            }
            if ($issues['errors'] !== []) {
                $this->countSkip($issues['errors']);
                $results[] = ['ok' => false, 'error' => implode(' ', $issues['errors'])];
                continue;
            }

            if ($issues['stock_key'] !== '') {
                $seen[$issues['stock_key']] = true;
            }

            $resultIndex = count($results);
            $results[] = ['ok' => true];
            try {
                $pending[] = [
                    'index' => $resultIndex,
                    'insert' => $this->insertPayload($issues['record'], $issues['stock_id']),
                ];
            } catch (RuntimeException $e) {
                $results[$resultIndex] = ['ok' => false, 'error' => $e->getMessage()];
                $this->runtime['skipped_invalid']++;
                continue;
            }
            $this->runtime['stored']++;

            if (count($pending) >= self::CHUNK_SIZE) {
                $this->insertPending($pending, $results);
                $pending = [];
            }
        }

        if ($pending !== []) {
            $this->insertPending($pending, $results);
        }

        return $results;
    }

    private function existingStockKeys(array $header, array $rows)
    {
        $seen = [];
        $stockIndex = array_search('stock_id', $header, true);
        if ($stockIndex === false) {
            return $seen;
        }

        $lookup = [];
        foreach ($rows as $cells) {
            if (!is_array($cells) || !array_key_exists($stockIndex, $cells)) {
                continue;
            }
            $stock = $this->sanitizeCell(isset($cells[$stockIndex]) ? $cells[$stockIndex] : null);
            if ($stock !== null && $stock !== '') {
                $lookup[] = $stock;
            }
        }
        if ($lookup === []) {
            return $seen;
        }

        $found = DB::table($this->temp)->whereIn('stock_id', array_values(array_unique($lookup)))->pluck('stock_id');
        foreach ($found as $id) {
            $seen[mb_strtolower((string) $id, 'UTF-8')] = true;
        }

        return $seen;
    }

    private function insertPayload(array $record, $stockId)
    {
        $insert = [];
        foreach ($this->fillable as $column) {
            $value = array_key_exists($column, $record) ? $record[$column] : null;
            $insert[$column] = $this->fitColumn($column, $value, $this->limits);
        }
        $insert['stock_id'] = $stockId;
        $insert['created_at'] = $this->runtime['now'];
        $insert['updated_at'] = $this->runtime['now'];

        return $insert;
    }

    private function insertPending(array $pending, array &$results)
    {
        $rows = [];
        foreach ($pending as $item) {
            $rows[] = $item['insert'];
        }

        try {
            DB::beginTransaction();
            DB::table($this->temp)->insert($rows);
            DB::commit();
            return;
        } catch (\Throwable $e) {
            try {
                DB::rollBack();
            } catch (\Throwable $ignored) {
                // The failed statement may already have ended the transaction.
            }
            if (!$this->isRowLevelDatabaseError($e) || count($pending) === 1) {
                if (count($pending) === 1 && $this->isRowLevelDatabaseError($e)) {
                    $this->rejectPendingRow($pending[0], $results, $e);
                    return;
                }
                throw $e;
            }
        }

        foreach ($pending as $item) {
            $this->insertPending([$item], $results);
        }
    }

    private function rejectPendingRow(array $item, array &$results, \Throwable $e)
    {
        $message = $e->getMessage();
        if (stripos($message, 'Duplicate') !== false) {
            $error = 'Duplicate stock id.';
            $this->runtime['skipped_duplicate']++;
        } else {
            $error = 'This row could not be saved.';
            $this->runtime['skipped_invalid']++;
            Log::warning('Diamond import skipped a row: ' . $message);
        }
        $results[$item['index']] = ['ok' => false, 'error' => $error];
        $this->runtime['stored']--;
    }

    private function isRowLevelDatabaseError(\Throwable $e)
    {
        $message = $e->getMessage();

        return stripos($message, 'Integrity constraint violation') !== false
            || stripos($message, 'Duplicate') !== false
            || stripos($message, 'Data too long') !== false
            || stripos($message, 'Out of range') !== false
            || stripos($message, 'Incorrect') !== false;
    }

    private function countSkip(array $errors)
    {
        $message = implode(' ', $errors);
        if ($errors === ['Stock id is required.']) {
            $this->runtime['skipped_stock']++;
            return;
        }
        if (strpos($message, 'Duplicate stock id') === 0 && count($errors) === 1) {
            $this->runtime['skipped_duplicate']++;
            return;
        }
        $this->runtime['skipped_invalid']++;
    }

    private function alignCells(array $cells, $width)
    {
        $cells = array_values($cells);
        if (count($cells) < $width) {
            $cells = array_pad($cells, $width, null);
        } elseif (count($cells) > $width) {
            $cells = array_slice($cells, 0, $width);
        }
        foreach ($cells as $index => $value) {
            $cells[$index] = $this->sanitizeCell($value);
        }

        return $cells;
    }

    private function sanitizeCell($value)
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return null;
        }
        $text = trim(str_replace("\xC2\xA0", ' ', (string) $value));
        if ($text === '') {
            return null;
        }
        $numeric = str_replace([',', ' ', '$', '₹'], '', $text);
        if (substr($numeric, -1) === '%') {
            $numeric = substr($numeric, 0, -1);
        }
        if ($numeric !== '' && is_numeric($numeric)) {
            $text = $numeric;
        }
        if (strlen($text) > 4000) {
            $text = function_exists('mb_substr') ? mb_substr($text, 0, 4000, 'UTF-8') : substr($text, 0, 4000);
        }

        return $text;
    }

    private function cellValue(array $row, $key)
    {
        if (!isset($row[$key]) || $row[$key] === null) {
            return '';
        }

        return trim((string) $row[$key]);
    }

    private function progress()
    {
        return [
            'data_rows' => (int) $this->runtime['data_rows'],
            'stored' => (int) $this->runtime['stored'],
            'skipped' => (int) $this->runtime['skipped_stock'] + (int) $this->runtime['skipped_duplicate'] + (int) $this->runtime['skipped_invalid'],
        ];
    }

    private function commit()
    {
        $stored = (int) $this->runtime['stored'];
        $actual = (int) DB::table($this->temp)->count();
        if ($stored < 1 || $actual < 1) {
            $this->dropTemp();
            throw new RuntimeException('No valid rows were found. The current catalog was not changed.');
        }
        $this->swapTables($this->table, $this->temp, $this->backup);
    }

    private function dropTemp()
    {
        if (!$this->temp) {
            $this->prepareNames();
        }
        DB::statement('DROP TABLE IF EXISTS ' . $this->quote($this->temp));
    }

    private function owner()
    {
        $id = Auth::id();
        if ($id) {
            return 'user:' . $id;
        }

        return 'session:' . session()->getId();
    }

    private function cacheKey()
    {
        return 'diamond-import:' . $this->owner();
    }

    private function rememberRuntime()
    {
        Cache::put($this->cacheKey(), $this->runtime, now()->addMinutes(30));
    }

    private function forgetRuntime()
    {
        Cache::forget($this->cacheKey());
    }

    private function lockPath()
    {
        return storage_path('framework/diamond-import.lock');
    }

    private function claimLock($owner)
    {
        $path = $this->lockPath();
        $now = time();
        if (is_file($path)) {
            $current = json_decode((string) file_get_contents($path), true);
            $age = $now - (int) (is_array($current) ? ($current['time'] ?? 0) : 0);
            $same = is_array($current) && ($current['owner'] ?? '') === $owner;
            $abandoned = $age > 600;
            if (!$same && !$abandoned) {
                throw new RuntimeException('Another import is already running. Please wait and try again.');
            }
        }
        $this->lockOwner = $owner;
        file_put_contents($path, json_encode(['owner' => $owner, 'time' => $now]));
    }

    private function touchLock($owner)
    {
        $path = $this->lockPath();
        if (!is_file($path)) {
            throw new RuntimeException('The import expired. Please choose the file again.');
        }
        $current = json_decode((string) file_get_contents($path), true);
        if (!is_array($current) || ($current['owner'] ?? '') !== $owner) {
            throw new RuntimeException('Another import is already running. Please wait and try again.');
        }
        $current['time'] = time();
        $this->lockOwner = $owner;
        file_put_contents($path, json_encode($current));
    }

    private function releaseLockFile()
    {
        if (!$this->lockOwner) {
            return;
        }
        $path = $this->lockPath();
        if (!is_file($path)) {
            return;
        }
        $current = json_decode((string) file_get_contents($path), true);
        if (is_array($current) && ($current['owner'] ?? '') === $this->lockOwner) {
            @unlink($path);
        }
        $this->lockOwner = null;
    }

    private function acquireDatabaseLock()
    {
        try {
            $locked = DB::selectOne('SELECT GET_LOCK(?, ?) AS locked', [self::LOCK_NAME, 30]);
        } catch (\Throwable $e) {
            $locked = (object) ['locked' => 1];
        }
        if (!$locked || (int) $locked->locked !== 1) {
            throw new RuntimeException('Another import is already running. Please wait and try again.');
        }
    }

    private function releaseDatabaseLock()
    {
        try {
            DB::select('SELECT RELEASE_LOCK(?)', [self::LOCK_NAME]);
        } catch (\Throwable $e) {
            // The connection is closing and the lock is released with it.
        }
    }

    private function columnLabel($column)
    {
        return ucwords(str_replace('_', ' ', (string) $column));
    }

    private function canonicalColumn($name)
    {
        $aliases = [
            'stockid' => 'stock_id',
            'stock_no' => 'stock_id',
            'stock_number' => 'stock_id',
        ];

        return isset($aliases[$name]) ? $aliases[$name] : $name;
    }

    private function columnLimits($table)
    {
        $limits = [];
        foreach (DB::select('SHOW COLUMNS FROM ' . $this->quote($table)) as $column) {
            $type = strtolower((string) $column->Type);
            if (preg_match('/^varchar\((\d+)\)/', $type, $matches)) {
                $limits[$column->Field] = ['kind' => 'string', 'length' => (int) $matches[1]];
            } elseif (preg_match('/^decimal\((\d+),(\d+)\)/', $type, $matches)) {
                $limits[$column->Field] = [
                    'kind' => 'decimal',
                    'precision' => (int) $matches[1],
                    'scale' => (int) $matches[2],
                ];
            } elseif (preg_match('/text|blob/', $type)) {
                $limits[$column->Field] = ['kind' => 'text'];
            }
        }

        return $limits;
    }

    /**
     * Short measurement columns are VARCHAR(5). A binary float such as
     * 65.599999999999994 does not fit, so numeric overflow is shortened
     * until it does. Text values are left unchanged.
     */
    private function fitColumn($column, $value, array $limits)
    {
        if ($value === null || $value === '' || !isset($limits[$column]) || !is_numeric($value)) {
            return $value;
        }

        $limit = $limits[$column];
        if ($limit['kind'] === 'decimal') {
            $formatted = sprintf('%.' . $limit['scale'] . 'f', (float) $value);
            $integerDigits = $limit['precision'] - $limit['scale'];
            $integer = strtok($formatted, '.');
            $integer = ltrim((string) $integer, '-');
            if (strlen($integer) <= $integerDigits) {
                return $formatted;
            }

            throw new RuntimeException('The value "' . $value . '" is too large for the ' . $column . ' column.');
        }

        if ($limit['kind'] !== 'string') {
            return $value;
        }

        if (strlen((string) $value) <= $limit['length']) {
            return $value;
        }

        $number = (float) $value;
        for ($places = min(6, $limit['length'] - 1); $places >= 0; $places--) {
            $text = $places === 0
                ? sprintf('%.0f', round($number))
                : rtrim(rtrim(sprintf('%.' . $places . 'F', round($number, $places)), '0'), '.');
            if ($text === '' || $text === '-') {
                $text = '0';
            }
            if (strlen($text) <= $limit['length']) {
                return $text;
            }
        }

        return $value;
    }

    private function rowHasValue(array $record)
    {
        foreach ($record as $value) {
            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    private function swapTables($table, $temp, $backup)
    {
        DB::statement('DROP TABLE IF EXISTS ' . $this->quote($backup));
        DB::statement(
            'RENAME TABLE ' . $this->quote($table) . ' TO ' . $this->quote($backup)
            . ', ' . $this->quote($temp) . ' TO ' . $this->quote($table)
        );
        DB::statement('DROP TABLE IF EXISTS ' . $this->quote($backup));
    }

    private function quote($identifier)
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
