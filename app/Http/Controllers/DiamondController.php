<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diamond;
use App\Services\DiamondExportBuilder;
use App\Services\DiamondImportService;
use App\Services\StreamingXlsxWriter;
use Exception;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class DiamondController extends Controller
{
    public function import()
    {
        $rules = (new DiamondImportService())->clientRules();

        return view('diamond.import', [
            'importConfig' => [
                'batchUrl' => route('diamond.import.batch'),
                'batchSize' => $rules['batchSize'],
                'columns' => $rules['columns'],
            ],
        ]);
    }

    public function importBatch(Request $request)
    {
        $phase = (string) $request->input('phase');
        $token = (string) $request->input('token', '');
        $service = new DiamondImportService();

        try {
            if ($phase === 'start') {
                $headers = $request->input('headers', []);
                if (!is_array($headers)) {
                    throw new \RuntimeException('The Excel file is missing a header row.');
                }
                $started = $service->startClientImport($headers);

                return response()->json(['ok' => true] + $started);
            }
            if ($token === '') {
                throw new \RuntimeException('The import expired. Please choose the file again.');
            }
            if ($phase === 'rows') {
                $rows = $request->input('rows', []);
                if (!is_array($rows)) {
                    throw new \RuntimeException('The import batch is invalid.');
                }
                $progress = $service->appendClientRows($token, $rows);

                return response()->json(['ok' => true] + $progress);
            }
            if ($phase === 'finish') {
                $progress = $service->finishClientImport($token);

                return response()->json([
                    'ok' => true,
                    'message' => 'Excel file imported successfully.',
                ] + $progress);
            }
            if ($phase === 'cancel') {
                $service->cancelClientImport($token);

                return response()->json(['ok' => true]);
            }

            throw new \RuntimeException('The import request is invalid.');
        } catch (\RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Diamond import failed: ' . $e->getMessage());

            return response()->json([
                'ok' => false,
                'message' => 'The import could not be completed. The current catalog was not changed.',
            ], 422);
        }
    }

    public function importSave(Request $request)
    {
        $uploadError = $_FILES['import_file']['error'] ?? null;
        if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
            $limit = ini_get('upload_max_filesize') ?: '2M';

            return redirect()->back()->with('error', 'This Excel file is larger than the server upload limit (' . $limit . ').');
        }

        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('import_file');
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            $fileTypeAccept = ['xls', 'xlsx'];
            if (!in_array($extension, $fileTypeAccept, true)) {
                return redirect()->back()->with('error', 'Please upload a valid Excel or CSV file.');
            }

            $path = $file->getRealPath();
            if (!$path) {
                $path = $file->getPathname();
            }
            (new DiamondImportService())->import($path);

            return redirect()->back()->with('success', 'Excel file imported successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function list()
    {
        $status = Diamond::select('status')->whereNotNull('status')->distinct()->pluck('status');
        $location = Diamond::select('location')->whereNotNull('location')->distinct()->pluck('location');
        $shapes = Diamond::select('shape')->whereNotNull('shape')->distinct()->pluck('shape');
        $colors = Diamond::select('color')->whereNotNull('color')->distinct()->pluck('color');
        $clarities = Diamond::select('clarity')->whereNotNull('clarity')->distinct()->orderBy('clarity', 'ASC')->pluck('clarity');
        $cuts = Diamond::select('cut')->whereNotNull('cut')->distinct()->pluck('cut');
        $polish = Diamond::select('polish')->whereNotNull('polish')->distinct()->pluck('polish');
        $symmetries = Diamond::select('symmetry')->whereNotNull('symmetry')->distinct()->pluck('symmetry');
        $labs = Diamond::select('lab')->whereNotNull('lab')->distinct()->pluck('lab');
        $reference = Diamond::select('reference')->whereNotNull('reference')->distinct()->pluck('reference');
        $columnWithValue = $this->columnWithValue();
        if (!Auth::user()) {
            unset($columnWithValue['reference']);
            unset($columnWithValue['bargaining_price_per_carat']);
            unset($columnWithValue['bargaining_total_price']);
        }
        unset($columnWithValue['created_at']);
        unset($columnWithValue['updated_at']);

        // echo "<pre>";
        // print_r($columnWithValue);
        // die;

        return view("diamond.list",compact('status', 'location', 'shapes', 'colors', 'clarities', 'cuts', 'polish', 'symmetries', 'labs', 'reference', 'columnWithValue'));
    }

    public function data(Request $request)
    {
        $columnWithValue = $this->columnWithValue();
        $columns = array_keys($columnWithValue);
        if (Auth::user()) {
            $excludeColumns = ['created_at', 'updated_at'];
            $selectedColumns = array_diff($columns, $excludeColumns);
        } else {
            $excludeColumns = ['reference', 'price_per_carat', 'total_price', 'bargaining_price_per_carat', 'bargaining_total_price', 'created_at', 'updated_at'];
            $selectedColumns = array_diff($columns, $excludeColumns);
            $selectedColumns = array_merge($selectedColumns, [
                'bargaining_price_per_carat as price_per_carat',
                'bargaining_total_price as total_price'
            ]);
        }

        $currentPage = (int) $request->input('currentPage', 1);
        $currentPerPage = (int) $request->input('currentPerPage', 10);
        if ($currentPage < 1) {
            $currentPage = 1;
        }
        if ($currentPerPage < 1) {
            $currentPerPage = 10;
        }
        if ($currentPerPage > 100) {
            $currentPerPage = 100;
        }
        $checkedRecord = $this->requestList($request, 'checkedRecord');
        $query = $this->filteredDiamonds($request);
        $summary = (clone $query)->reorder()->selectRaw('COUNT(*) as total_stock, COALESCE(SUM(`weight`), 0) as total_carat, COALESCE(SUM(`total_price`), 0) as total_amount')->first();
        $totalStock = (int) $summary->total_stock;
        $totalCarat = $summary->total_carat ?: 0;
        $totalAmount = $summary->total_amount ?: 0;

        if (count($checkedRecord) > 0) {
            return response()->json([
                'total_stock' => $totalStock,
                'total_carat' => $totalCarat,
                'total_amount' => $totalAmount,
            ]);
        }

        $data = (clone $query)->select($selectedColumns)->paginate($currentPerPage, ['*'], 'page', $currentPage);

        return response()->json([
            'data' => $data->items(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
            'total' => $data->total(),
            'total_stock' => $totalStock,
            'total_carat' => $totalCarat,
            'total_amount' => $totalAmount,
        ]);
    }

    public function updateData(Request $request)
    {
        try {
            $fillable = array_flip((new Diamond())->getFillable());
            unset($fillable['stock_id']);
            $data = array_intersect_key($request->all(), $fillable);
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    unset($data[$key]);
                }
            }
            $update = $data === [] ? 0 : Diamond::where('stock_id', $request->stock_id)->update($data);
            if (!$update) {
                return response()->json(['status' => false, 'message' => 'Something went wrong!', 500]);
            }
            return response()->json(['status' => true, 'message' => 'Successfully!', 200]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 500]);
        }
    }

    public function jsonData(int $type = 2)
    {
        $currentDateTime = now()->toDateTimeString();

        Log::info("[$currentDateTime]", ["****************************************************************************************************"]);
        Log::info("[$currentDateTime] Type: ", [$type]);

        try {
            $columnWithValue = $this->columnWithValue();
            $columns = array_keys($columnWithValue);
            // echo "<pre>";
            // print_r($columns);
            // die;

            if ($type == 1) {
                $excludeColumns = ['reference', 'bargaining_price_per_carat', 'bargaining_total_price', 'created_at', 'updated_at'];
                $selectedColumns = array_diff($columns, $excludeColumns);
                $query = Diamond::query()->select($selectedColumns)->orderBy('id');

                return $this->streamJsonQuery($query, $currentDateTime);
            }
            else if ($type == 2) {
                $excludeColumns = ['reference', 'price_per_carat', 'total_price', 'bargaining_price_per_carat', 'bargaining_total_price', 'created_at', 'updated_at'];
                $selectedColumns = array_diff($columns, $excludeColumns);
                $finalColumns = array_merge($selectedColumns, [
                    'bargaining_price_per_carat as price_per_carat',
                    'bargaining_total_price as total_price'
                ]);
                $query = Diamond::query()->select($finalColumns)->orderBy('id');

                return $this->streamJsonQuery($query, $currentDateTime);
            }

            $responseMessage = [
                'status' => false,
                'message' => 'Plese pass type (1 or 2)!',
            ];
            Log::info("[$currentDateTime] Error: ", [json_encode($responseMessage)]);
            return response()->json($responseMessage, 404);
        } catch (Exception $e) {
            $responseMessage = [
                'status' => false,
                'message' => $e->getMessage(),
            ];
            Log::info("[$currentDateTime] Error: ", [json_encode($responseMessage)]);
            return response()->json($responseMessage, 500);
        }
    }
    public function newjsonData(int $type = 2)
    {
        $currentDateTime = now()->toDateTimeString();

        Log::info("[$currentDateTime]", ["****************************************************************************************************"]);
        Log::info("[$currentDateTime] Type: ", [$type]);

        try {
            $columnWithValue = $this->columnWithValue();
            $columns = array_keys($columnWithValue);


            if ($type == 1) {
                $excludeColumns = ['reference', 'bargaining_price_per_carat', 'bargaining_total_price', 'created_at', 'updated_at'];
                $selectedColumns = array_diff($columns, $excludeColumns);
                $query = Diamond::query()->select($selectedColumns)
                    ->selectRaw('ROUND(total_price + (total_price * 18 / 100),2) as total_price')
                    ->orderBy('id');

                return $this->streamJsonQuery($query, $currentDateTime);
            }
            else if ($type == 2) {
                $excludeColumns = ['reference', 'price_per_carat', 'total_price', 'bargaining_price_per_carat', 'bargaining_total_price', 'created_at', 'updated_at'];
                $selectedColumns = array_diff($columns, $excludeColumns);
                $finalColumns = array_merge($selectedColumns, [
                    'bargaining_price_per_carat as price_per_carat',
                    'bargaining_total_price as total_price'
                ]);
                $query = Diamond::query()->select($finalColumns)
                    ->selectRaw('ROUND(bargaining_total_price + (bargaining_total_price * 18 / 100),2) as total_price')
                    ->orderBy('id');

                return $this->streamJsonQuery($query, $currentDateTime);
            }

            $responseMessage = [
                'status' => false,
                'message' => 'Plese pass type (1 or 2)!',
            ];
            Log::info("[$currentDateTime] Error: ", [json_encode($responseMessage)]);
            return response()->json($responseMessage, 404);
        } catch (Exception $e) {
            $responseMessage = [
                'status' => false,
                'message' => $e->getMessage(),
            ];
            Log::info("[$currentDateTime] Error: ", [json_encode($responseMessage)]);
            return response()->json($responseMessage, 500);
        }
    }
    public function updateStatus(string $type = 'HOLD', string $stockId = ''): JsonResponse
    {
        $type = Str::upper($type);
        if (!in_array($type, ['AVAILABLE', 'ON MEMO', 'HOLD', 'SOLD'])) {
            $type = 'HOLD';
        }
        $currentDateTime = date('Y-m-d H:i:s');
        $diamond = Diamond::where('stock_id', $stockId)->first();
        if (!$diamond) {
            $responseMessage = [
                'status' => false,
                'message' => 'Diamond not found!',
            ];
            Log::info("[$currentDateTime] Error: ", [json_encode($responseMessage)]);
            return response()->json($responseMessage, 404);
        }

        $diamond->status = strtoupper($type);
        $diamond->save();

        return response()->json([
            'status' => true,
            'message' => 'Diamond status updated successfully!',
        ], 200);
    }

    public function exportCsv(Request $request)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'diamond_csv_');
        $handle = fopen($tempFile, 'wb');
        if ($handle === false) {
            abort(500, 'Unable to create the export file.');
        }

        try {
            $this->writeExport($request, function (array $row) use ($handle) {
                $this->writeCsvRow($handle, $row);
            }, function ($yellow) use ($handle) {
                fwrite($handle, PHP_EOL);
            });
        } catch (\Throwable $e) {
            fclose($handle);
            @unlink($tempFile);
            throw $e;
        }
        fclose($handle);

        return response()->download($tempFile, 'export.csv')->deleteFileAfterSend(true);
    }

    public function exportXlsx(Request $request)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'diamond_xlsx_');
        if ($tempFile === false) {
            abort(500, 'Unable to create the export file.');
        }
        @unlink($tempFile);
        $tempFile .= '.xlsx';

        $writer = new StreamingXlsxWriter();
        try {
            $this->writeExport($request, function (array $row, $bold, $trackWidth, array $yellow, $boldUntil) use ($writer) {
                $writer->addRow($row, $bold, $yellow, $trackWidth, $boldUntil);
            }, function ($yellow) use ($writer) {
                $writer->addRow([], false, $yellow, false);
            });
            $writer->save($tempFile);
        } catch (\Throwable $e) {
            @unlink($tempFile);
            throw $e;
        }

        return response()->download($tempFile, 'export.xlsx')->deleteFileAfterSend(true);
    }
    private function format_column_reverse($column)
    {
        return ucwords(str_replace("_", " ", str_replace('percentage', '%', str_replace('number', '#', str_replace('and', '&', $column)))));
    }

    private function columnWithValue()
    {
        $columns = Schema::getColumnListing('diamonds');
        $columnWithValue = [];
        foreach ($columns as $key => $value) {
            $columnWithValue[$value] = $this->format_column_reverse($value);
        }
        return $columnWithValue;
    }

    private function writeExport(Request $request, callable $writeRow, callable $writeBlank)
    {
        @set_time_limit(0);
        DB::disableQueryLog();

        $isUser = (bool) Auth::user();
        $builder = new DiamondExportBuilder($this->columnWithValue(), $isUser);
        $query = $this->filteredDiamonds($request)->select($this->exportSelectColumns($isUser));
        $hasRows = (clone $query)->exists();
        $header = $builder->header($hasRows);
        $yellow = $builder->highlightColumns();

        $writeRow($header, true, true, $yellow, count($header));
        if ($hasRows) {
            foreach ($query->lazy(300) as $record) {
                $writeRow($builder->mapRow($record->toArray()), false, true, $yellow, null);
            }
        }
        $writeBlank($yellow);
        $writeRow($builder->totalsRow($header), true, false, $yellow, null);
    }

    private function writeCsvRow($handle, array $values)
    {
        $values = array_map(function ($value) {
            return $value === null ? '' : $value;
        }, $values);
        $temporary = fopen('php://temp', 'r+');
        fputcsv($temporary, $values);
        rewind($temporary);
        $line = stream_get_contents($temporary);
        fclose($temporary);
        fwrite($handle, rtrim($line, "\r\n") . PHP_EOL);
    }

    private function exportSelectColumns($isUser)
    {
        $columns = array_keys($this->columnWithValue());
        if ($isUser) {
            return array_values(array_diff($columns, ['id', 'created_at', 'updated_at']));
        }

        $selected = array_values(array_diff($columns, [
            'id',
            'reference',
            'price_per_carat',
            'total_price',
            'bargaining_price_per_carat',
            'bargaining_total_price',
            'created_at',
            'updated_at',
        ]));

        return array_merge($selected, [
            'bargaining_price_per_carat as price_per_carat',
            'bargaining_total_price as total_price',
        ]);
    }

    private function streamJsonQuery($query, $currentDateTime)
    {
        DB::disableQueryLog();
        $count = (clone $query)->count();
        Log::info("[$currentDateTime] Response: ", [$count]);

        return response()->stream(function () use ($query) {
            echo '[';
            $first = true;
            foreach ($query->lazy(300) as $record) {
                if (!$first) {
                    echo ',';
                }
                $json = json_encode($record->toArray(), JSON_INVALID_UTF8_SUBSTITUTE);
                echo $json === false ? 'null' : $json;
                $first = false;
            }
            echo ']';
        }, 200, ['Content-Type' => 'application/json']);
    }

    private function filteredDiamonds(Request $request)
    {
        $columns = array_keys($this->columnWithValue());
        $sortColumn = (string) $request->input('currentSortColumn', 'id');
        $sortDirection = strtolower((string) $request->input('currentSortDirection', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (!in_array($sortColumn, $columns, true)) {
            $sortColumn = 'id';
        }

        $minCarat = $request->input('minCarat', '');
        $maxCarat = $request->input('maxCarat', '');
        $minLength = $request->input('minLength', '');
        $maxLength = $request->input('maxLength', '');
        $minWidth = $request->input('minWidth', '');
        $maxWidth = $request->input('maxWidth', '');
        $minHeight = $request->input('minHeight', '');
        $maxHeight = $request->input('maxHeight', '');
        $minDepth = $request->input('minDepth', '');
        $maxDepth = $request->input('maxDepth', '');
        $minRatio = $request->input('minRatio', '');
        $maxRatio = $request->input('maxRatio', '');
        $minTable = $request->input('minTable', '');
        $maxTable = $request->input('maxTable', '');
        $stockId = preg_replace('/\D/', '', (string) $request->input('stockId', ''));
        $reportNumber = $request->input('reportNumber', '');
        $type = $request->input('type', '');
        $checkedRecord = $this->requestList($request, 'checkedRecord');
        $statusList = $this->requestList($request, 'statusList');
        $locationList = $this->requestList($request, 'locationList');
        $shapeList = $this->requestList($request, 'shapeList');
        $colorList = $this->requestList($request, 'colorList');
        $clarityList = $this->requestList($request, 'clarityList');
        $cutList = $this->requestList($request, 'cutList');
        $polishList = $this->requestList($request, 'polishList');
        $symmetryList = $this->requestList($request, 'symmetryList');
        $labList = $this->requestList($request, 'labList');
        $referenceList = $this->requestList($request, 'referenceList');

        return Diamond::query()
            ->when($minCarat, function ($query, $minCarat) {
                return $query->where('weight', '>=', (float) $minCarat);
            })
            ->when($maxCarat, function ($query, $maxCarat) {
                return $query->where('weight', '<=', (float) $maxCarat);
            })
            ->when($minLength, function ($query, $minLength) {
                return $query->where('length', '>=', $minLength);
            })
            ->when($maxLength, function ($query, $maxLength) {
                return $query->where('length', '<=', $maxLength);
            })
            ->when($minWidth, function ($query, $minWidth) {
                return $query->where('width', '>=', $minWidth);
            })
            ->when($maxWidth, function ($query, $maxWidth) {
                return $query->where('width', '<=', $maxWidth);
            })
            ->when($minHeight, function ($query, $minHeight) {
                return $query->where('height', '>=', $minHeight);
            })
            ->when($maxHeight, function ($query, $maxHeight) {
                return $query->where('height', '<=', $maxHeight);
            })
            ->when($minDepth, function ($query, $minDepth) {
                return $query->where('depth_percentage', '>=', $minDepth);
            })
            ->when($maxDepth, function ($query, $maxDepth) {
                return $query->where('depth_percentage', '<=', $maxDepth);
            })
            ->when($minRatio, function ($query, $minRatio) {
                return $query->where('ratio', '>=', $minRatio);
            })
            ->when($maxRatio, function ($query, $maxRatio) {
                return $query->where('ratio', '<=', $maxRatio);
            })
            ->when($minTable, function ($query, $minTable) {
                return $query->where('table_percentage', '>=', $minTable);
            })
            ->when($maxTable, function ($query, $maxTable) {
                return $query->where('table_percentage', '<=', $maxTable);
            })
            ->when($stockId, function ($query, $stockId) {
                return $query->where('stock_id', 'LIKE', '%' . $stockId . '%');
            })
            ->when($reportNumber, function ($query, $reportNumber) {
                return $query->where('report_number', $reportNumber);
            })
            ->when($type, function ($query, $type) {
                return $query->where('growth_type', $type);
            })
            ->when($checkedRecord, function ($query, $checkedRecord) {
                return $query->whereIn('stock_id', $checkedRecord);
            })
            ->when($statusList, function ($query, $statusList) {
                return $query->whereIn('status', $statusList);
            })
            ->when($locationList, function ($query, $locationList) {
                return $query->whereIn('location', $locationList);
            })
            ->when($shapeList, function ($query, $shapeList) {
                return $query->whereIn('shape', $shapeList);
            })
            ->when($colorList, function ($query, $colorList) {
                return $query->whereIn('color', $colorList);
            })
            ->when($clarityList, function ($query, $clarityList) {
                return $query->whereIn('clarity', $clarityList);
            })
            ->when($cutList, function ($query, $cutList) {
                return $query->whereIn('cut', $cutList);
            })
            ->when($polishList, function ($query, $polishList) {
                return $query->whereIn('polish', $polishList);
            })
            ->when($symmetryList, function ($query, $symmetryList) {
                return $query->whereIn('symmetry', $symmetryList);
            })
            ->when($labList, function ($query, $labList) {
                return $query->whereIn('lab', $labList);
            })
            ->when($referenceList, function ($query, $referenceList) {
                return $query->whereIn('reference', $referenceList);
            })
            ->orderBy($sortColumn, $sortDirection)
            ->orderBy('id', $sortDirection);
    }

    private function requestList(Request $request, $key)
    {
        $value = $request->input($key, []);

        return is_array($value) ? $value : [];
    }
    public function runCommand($type, $mig)
    {
        if($type == 97531) {
            // Artisan::call('view:cache');
            Artisan::call('view:clear');
            // Artisan::call('route:cache');
            Artisan::call('route:clear');
            // Artisan::call('config:cache');
            Artisan::call('config:clear');
            // Artisan::call('optimize');
            Artisan::call('optimize:clear');

            if($mig == 13579) {
                // Artisan::call('migrate:refresh');
                // Artisan::call('db:seed');
                return response()->json(['status' => 1, 'message' => 'You excuted artisan command!'], 200);
            }
            return response()->json(['status' => 2, 'message' => 'You don\'t full excuted artisan command!'], 301);
        } else {
            return response()->json(['status' => 0, 'message' => 'You don\'t excuted artisan command!'], 401);
        }
    }
}
