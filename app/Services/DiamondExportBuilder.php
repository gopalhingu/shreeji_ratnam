<?php

namespace App\Services;

use App\Support\ExcelColumn;

/**
 * Builds the same export columns, serial numbers, and footer totals the
 * spreadsheet export used to build in memory.
 */
class DiamondExportBuilder
{
    private $columns;
    private $authenticated;
    private $exceptions;
    private $serial = 0;
    private $totalWeight = 0;
    private $totalAmount = 0;

    public function __construct(array $columns, $authenticated)
    {
        $this->columns = $columns;
        $this->authenticated = (bool) $authenticated;
        if ($this->authenticated) {
            $this->exceptions = ['id', 'created_at', 'updated_at'];
        } else {
            $this->exceptions = ['id', 'reference', 'bargaining_price_per_carat', 'bargaining_total_price', 'created_at', 'updated_at'];
        }
    }

    public function header($hasRows)
    {
        if (!$hasRows) {
            return ['Serial No.'];
        }

        $header = ['Serial No.'];
        foreach ($this->columns as $key => $label) {
            if (in_array($key, $this->exceptions, true)) {
                continue;
            }
            $header[] = $label;
        }

        return $header;
    }

    public function mapRow(array $record)
    {
        $this->serial++;
        $row = [$this->serial];
        $this->totalWeight += (float) (isset($record['weight']) ? $record['weight'] : 0);
        $this->totalAmount += (float) (isset($record['total_price']) ? $record['total_price'] : 0);

        foreach ($this->columns as $key => $label) {
            if (in_array($key, $this->exceptions, true)) {
                continue;
            }
            $row[] = array_key_exists($key, $record) ? $record[$key] : null;
        }

        return $row;
    }

    public function highlightColumns()
    {
        return array_map(function ($letter) {
            return ExcelColumn::index($letter);
        }, array_keys($this->totalLetters()));
    }

    public function totalsRow(array $header)
    {
        $values = $this->totalValues();
        $width = count($header);
        foreach ($values as $index => $value) {
            if ($index > $width) {
                $width = $index;
            }
        }

        $row = array_fill(0, $width, null);
        foreach ($values as $index => $value) {
            $row[$index - 1] = $value;
        }

        return $row;
    }

    private function totalLetters()
    {
        if ($this->authenticated) {
            return ['H' => 'weight', 'Z' => 'average', 'AA' => 'amount'];
        }

        return ['G' => 'weight', 'Y' => 'average', 'Z' => 'amount'];
    }

    private function totalValues()
    {
        $average = 0;
        if ($this->totalWeight > 0) {
            $average = round($this->totalAmount / $this->totalWeight, 2);
        }

        $amounts = [
            'weight' => $this->totalWeight,
            'average' => $average,
            'amount' => $this->totalAmount,
        ];
        $cells = [];
        foreach ($this->totalLetters() as $letter => $key) {
            $cells[ExcelColumn::index($letter)] = $amounts[$key];
        }

        return $cells;
    }
}
