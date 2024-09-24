<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Diamond;
use Exception;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class DiamondController extends Controller
{
    public function import()
    {
        return view("diamond.import");
    }
    
    public function importSave(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('import_file');
        $extension = $file->getClientOriginalExtension();
        $fileName = $file->getClientOriginalName();
        $newFileName = time().'_'.$fileName;

        try {
            $fileTypeAccept = ['csv', 'xlsx'];
            if(!in_array($extension, $fileTypeAccept)) {
                return redirect()->back()->with('error', 'Please upload a valid Excel or CSV file.');
            }

            $spreadsheet = IOFactory::load($file->path());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            // Use the first row as keys
            $header = array_shift($data);
            $newHeader = [];
            foreach ($header as $key => $value) {
                if($key == 0) {
                    $newHeader[] = 'id';
                } else {
                    $newHeader[] = $this->format_column($value);
                }
            }
            $formattedData = array_map(function ($row) use ($newHeader) {
                return array_combine($newHeader, $row);
            }, $data);

            // echo "<pre>";
            // print_r($formattedData);
            // die;

            if (count($formattedData) > 0) {
                Diamond::truncate();
            }

            foreach ($formattedData as $key => $value) {

                array_shift($value); // Remove first element
                array_pop($value); // Remove last element

                $value['report_date'] = !empty($value['report_date']) ? date("Y-m-d", strtotime($value['report_date'])) : date("Y-m-d");
                $value['ratio'] = !empty($value['ratio']) ? sprintf("%.2f", $value['ratio']) : sprintf("%.2f", ((float)($value['length'] ?? 0) / (float)($value['width'] ?? 0)));
                $value['rap_amount'] = !empty($value['rap_amount']) ? sprintf("%.2f", $value['rap_amount']) : sprintf("%.2f", (((float)($value['weight'] ?? 0)) * (float)($value['live_rap'] ?? 0)));
                $value['price_per_carat'] = !empty($value['price_per_carat']) ? sprintf("%.2f", $value['price_per_carat']) : sprintf("%.2f", (((float)($value['live_rap'] ?? 0) * (((float)($value['discounts'] ?? 0)) / 100)) + (float)($value['live_rap'] ?? 0)));
                $value['total_price'] = !empty($value['total_price']) ? sprintf("%.2f", $value['total_price']) : sprintf("%.2f", (((float)($value['weight'] ?? 0)) * ($value['price_per_carat'] ?? 0)));
                $value['bargaining_price_per_carat'] = !empty($value['bargaining_price_per_carat']) ? sprintf("%.2f", $value['bargaining_price_per_carat']) : sprintf("%.2f", ((float)($value['bargaining_price_per_carat'] ?? 0)));
                $value['bargaining_total_price'] = !empty($value['bargaining_total_price']) ? sprintf("%.2f", $value['bargaining_total_price']) : sprintf("%.2f", ((float)($value['weight'] ?? 0) * ((float)($value['bargaining_price_per_carat'] ?? 0))));

                // echo "<pre>";
                // print_r($value);
                // die;

                if(!empty($value['stock_id'])) {
                    $getRecord = Diamond::where("stock_id", $value['stock_id'])->first();
                    if(!empty($getRecord)) {
                        Diamond::where("stock_id", $value['stock_id'])->update($value);
                    } else {
                        Diamond::create($value);
                    }
                }
            }

            // $file->move(public_path('excel'), $newFileName);
            // $filePath = public_path('excel/'.$newFileName);

            return redirect()->back()->with('success', 'Excel file imported successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function list()
    {
        $status = Diamond::select('status')->whereNotNull('status')->distinct()->pluck('status');
        $shapes = Diamond::select('shape')->whereNotNull('shape')->distinct()->pluck('shape');
        $colors = Diamond::select('color')->whereNotNull('color')->distinct()->pluck('color');
        $clarities = Diamond::select('clarity')->whereNotNull('clarity')->distinct()->orderBy('clarity', 'ASC')->pluck('clarity');
        $cuts = Diamond::select('cut')->whereNotNull('cut')->distinct()->pluck('cut');
        $polish = Diamond::select('polish')->whereNotNull('polish')->distinct()->pluck('polish');
        $symmetries = Diamond::select('symmetry')->whereNotNull('symmetry')->distinct()->pluck('symmetry');
        $labs = Diamond::select('lab')->whereNotNull('lab')->distinct()->pluck('lab');
        $columnWithValue = $this->columnWithValue();
        if (!Auth::user()) {
            unset($columnWithValue['bargaining_price_per_carat']);
            unset($columnWithValue['bargaining_total_price']);
        }

        // echo "<pre>";
        // print_r($columnWithValue);
        // die;

        return view("diamond.list",compact('status', 'shapes', 'colors', 'clarities', 'cuts', 'polish', 'symmetries', 'labs', 'columnWithValue'));
    }

    public function data(Request $request)
    {
        $columnWithValue = $this->columnWithValue();
        $columns = array_keys($columnWithValue);
        if (Auth::user()) {
            $excludeColumns = ['created_at', 'updated_at'];
            $selectedColumns = array_diff($columns, $excludeColumns);
        } else {
            $excludeColumns = ['price_per_carat', 'total_price', 'bargaining_price_per_carat', 'bargaining_total_price', 'created_at', 'updated_at'];
            $selectedColumns = array_diff($columns, $excludeColumns);
            $selectedColumns = array_merge($selectedColumns, [
                'bargaining_price_per_carat as price_per_carat', 
                'bargaining_total_price as total_price'
            ]);
        }
        
        $currentPage = $request->input('currentPage', 1);
        $currentPerPage = $request->input('currentPerPage', 10);
        $currentSortColumn = $request->input('currentSortColumn', 'id');
        $currentSortDirection = $request->input('currentSortDirection', 'asc');
        // $totalPage = $request->input('totalPage', '');
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
        $stockId = $request->input('stockId', '');
        $reportNumber = $request->input('reportNumber', '');
        $type = $request->input('type', '');
        $checkedRecord = $request->input('checkedRecord', []);
        $statusList = $request->input('statusList', []);
        $shapeList = $request->input('shapeList', []);
        $colorList = $request->input('colorList', []);
        $clarityList = $request->input('clarityList', []);
        $cutList = $request->input('cutList', []);
        $polishList = $request->input('polishList', []);
        $symmetryList = $request->input('symmetryList', []);
        $labList = $request->input('labList', []);
        $stockId = preg_replace('/\D/', '', $stockId);

        // Query the database with pagination and sorting
        $query = Diamond::query()
        ->select($selectedColumns)
        ->when($minCarat, function ($query, $minCarat) {
            return $query->where('weight', '>=', (float)$minCarat);
        })
        ->when($maxCarat, function ($query, $maxCarat) {
            return $query->where('weight', '<=', (float)$maxCarat);
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
            return $query->where('stock_id', 'LIKE', '%'.$stockId.'%');
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
        ->orderBy($currentSortColumn, $currentSortDirection);

        $totalStock = $query->count();
        $totalCarat = $query->sum('weight') ?: 0;
        $totalAmount = $query->sum('total_price') ?: 0;

        if(is_array($checkedRecord) && count($checkedRecord) > 0) {
            return response()->json([
                'total_stock' => $totalStock,
                'total_carat' => $totalCarat,
                'total_amount' => $totalAmount,
            ]);
        }

        $data = $query->paginate($currentPerPage, ['*'], 'page', $currentPage);

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

    public function jsonData($type = 2, Request $request)
    {
        try {
            $columnWithValue = $this->columnWithValue();
            $columns = array_keys($columnWithValue);
        
            // echo "<pre>";
            // print_r($columns);
            // die; 

            if ($type == 1) {
                $excludeColumns = ['bargaining_price_per_carat', 'bargaining_total_price', 'created_at', 'updated_at'];
                $selectedColumns = array_diff($columns, $excludeColumns);
                $records = Diamond::select($selectedColumns)->get()->toArray();
                return response()->json($records);
            } 
            else if ($type == 2) {
                $excludeColumns = ['price_per_carat', 'total_price', 'bargaining_price_per_carat', 'bargaining_total_price', 'created_at', 'updated_at'];
                $selectedColumns = array_diff($columns, $excludeColumns);
                $finalColumns = array_merge($selectedColumns, [
                    'bargaining_price_per_carat as price_per_carat', 
                    'bargaining_total_price as total_price'
                ]);
                $records = Diamond::select($finalColumns)->get()->toArray();
                return response()->json($records);
            }

            $responseMessage = [
                'status' => false,
                'message' => 'Plese pass type (1 or 2)!',
            ];
            return response()->json($responseMessage, 404);
        } catch (Exception $e) {
            $responseMessage = [
                'status' => false,
                'message' => $e->getMessage(),
            ];
            return response()->json($responseMessage, 500);
        }
    }

    public function exportCsv(Request $request) 
    {
        $column = $this->columnWithValue();
        $data = $this->getDataForExport($request);
        $excelData = $this->excelData($column, $data);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->fromArray($excelData['header'], null, 'A1');

        // Insert data
        $sheet->fromArray($excelData['data'], null, 'A2');

        // Add custom row at the end
        $lastRow = $sheet->getHighestRow() + 2;
        $sheet->setCellValue("G{$lastRow}", $excelData['totalWeight']);
        $sheet->setCellValue("Y{$lastRow}", $excelData['averageAmount']);
        $sheet->setCellValue("Z{$lastRow}", $excelData['totalAmount']);
        $sheet->getStyle("A{$lastRow}:AY{$lastRow}")->getFont()->setBold(true);

        // Set response headers
        $filename = 'export.csv';
        $writer = new Csv($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp_file);

        return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
    }

    public function exportXlsx(Request $request) 
    {
        $column = $this->columnWithValue();
        $data = $this->getDataForExport($request);

       /*  $columnWithValue = $this->columnWithValue();
        $columns = array_keys($columnWithValue);
        if (Auth::user()) {
            $excludeColumns = ['created_at', 'updated_at'];
            $selectedColumns = array_diff($columns, $excludeColumns);
        } else {
            $excludeColumns = ['price_per_carat', 'total_price', 'bargaining_price_per_carat', 'bargaining_total_price', 'created_at', 'updated_at'];
            $selectedColumns = array_diff($columns, $excludeColumns);
            $selectedColumns = array_merge($selectedColumns, [
                'bargaining_price_per_carat as price_per_carat', 
                'bargaining_total_price as total_price'
            ]);
        } */

        $excelData = $this->excelData($column, $data);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->fromArray($excelData['header'], null, 'A1');

        // Insert data
        $sheet->fromArray($excelData['data'], null, 'A2');

        // Make the first row bold
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

        // Freeze the first column
        $sheet->freezePane('A2');

        // Set auto column widths
        foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Manually set column widths
        $columnIndex = 0;
        foreach ($excelData['header'] as $header) {
            $maxLength = strlen($header);
            foreach ($excelData['data'] as $row) {
                if (isset($row[$columnIndex])) {
                    $length = strlen($row[$columnIndex]);
                    if ($length > $maxLength) {
                        $maxLength = $length;
                    }
                }
            }
            $sheet->getColumnDimensionByColumn($columnIndex + 1)->setWidth($maxLength + 2);
            $columnIndex++;
        }

        // Add custom row at the end
        $lastRow = $sheet->getHighestRow() + 2;
        $sheet->setCellValue("G{$lastRow}", $excelData['totalWeight']);
        $sheet->setCellValue("Y{$lastRow}", $excelData['averageAmount']);
        $sheet->setCellValue("Z{$lastRow}", $excelData['totalAmount']);
        $sheet->getStyle("A{$lastRow}:{$sheet->getHighestColumn()}{$lastRow}")->getFont()->setBold(true);

        // Set background color for a specific column 
        $colorColumn = ['G', 'Y', 'Z'];
        foreach ($colorColumn as $key => $value) {
            $sheet->getStyle($value . '1:' . $value . $sheet->getHighestRow())
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFFF00'); // Yellow color
        }

        // Set response headers
        $filename = 'export.xlsx';
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp_file);

        return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
    }

    private function excelData($column, $data) 
    {
        if (Auth::user()) {
            $exception = ['id', 'created_at', 'updated_at'];
        } else {
            $exception = ['id', 'bargaining_price_per_carat', 'bargaining_total_price', 'created_at', 'updated_at'];
        }
        $header = ['Serial No.'];
        $totalWeight = 0;
        $totalAmount = 0;
        $averageAmount = 0;
        $excelArray = [];
        $i = 0;
        foreach ($data as $key => $value) {
            $array = [];
            $array['id'] = $i += 1;
            $totalWeight += (float)$value['weight'];
            $totalAmount += (float)$value['total_price'];
            foreach ($column as $k => $v) {
                if(in_array($k, $exception)) {
                    continue;
                }
                if ($key == 0) {
                    $header[] = $v;
                }
                $array[$k] = $value[$k];
            }
            $excelArray[] = $array;
        }
        $averageAmount = round(($totalAmount / $totalWeight), 2);
        return [
            'header' => $header,
            'data' => $excelArray,
            'totalWeight' => $totalWeight,
            'totalAmount' => $totalAmount,
            'averageAmount' => $averageAmount,
        ];
    }

    private function format_column($column)
    {
        return strtolower(str_replace(" ", "_", str_replace('%', 'percentage', str_replace('#', 'number', $column))));
    }

    private function format_column_reverse($column)
    {
        return ucwords(str_replace("_", " ", str_replace('percentage', '%', str_replace('number', '#', $column))));
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

    private function getDataForExport(Request $request)
    {
        $columnWithValue = $this->columnWithValue();
        $columns = array_keys($columnWithValue);
        if (Auth::user()) {
            $excludeColumns = ['id', 'created_at', 'updated_at'];
            $selectedColumns = array_diff($columns, $excludeColumns);
        } else {
            $excludeColumns = ['id', 'price_per_carat', 'total_price', 'bargaining_price_per_carat', 'bargaining_total_price', 'created_at', 'updated_at'];
            $selectedColumns = array_diff($columns, $excludeColumns);
            $selectedColumns = array_merge($selectedColumns, [
                'bargaining_price_per_carat as price_per_carat', 
                'bargaining_total_price as total_price'
            ]);
        }

        // $currentPage = $request->input('currentPage', 1);
        // $currentPerPage = $request->input('currentPerPage', 10);
        $currentSortColumn = $request->input('currentSortColumn', 'id');
        $currentSortDirection = $request->input('currentSortDirection', 'asc');
        // $totalPage = $request->input('totalPage', '');
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
        $stockId = $request->input('stockId', '');
        $reportNumber = $request->input('reportNumber', '');
        $type = $request->input('type', '');
        $checkedRecord = $request->input('checkedRecord', []);
        $statusList = $request->input('statusList', []);
        $shapeList = $request->input('shapeList', []);
        $colorList = $request->input('colorList', []);
        $clarityList = $request->input('clarityList', []);
        $cutList = $request->input('cutList', []);
        $polishList = $request->input('polishList', []);
        $symmetryList = $request->input('symmetryList', []);
        $labList = $request->input('labList', []);
        $stockId = preg_replace('/\D/', '', $stockId);

        // Query the database with pagination and sorting
        $query = Diamond::query()
        ->select($selectedColumns)
        ->when($minCarat, function ($query, $minCarat) {
            return $query->where('weight', '>=', (float)$minCarat);
        })
        ->when($maxCarat, function ($query, $maxCarat) {
            return $query->where('weight', '<=', (float)$maxCarat);
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
            return $query->where('stock_id', 'LIKE', '%'.$stockId.'%');
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
        ->orderBy($currentSortColumn, $currentSortDirection);

        $record = $query->get()->toArray();
        return  $record;
    }

    public function runCommand($type, $mig)
    {
        if($type == 97531) {
            Artisan::call('view:clear');
            // Artisan::call('view:cache');
            // Artisan::call('route:clear');
            // Artisan::call('route:cache');
            // Artisan::call('config:clear');
            // Artisan::call('config:cache');
            // Artisan::call('optimize');
            Artisan::call('optimize:clear');

            if($mig == 13579) {
                Artisan::call('migrate:refresh');
                Artisan::call('db:seed');
                return response()->json(['status' => 1, 'message' => 'You excuted artisan command!'], 200);
            }
            return response()->json(['status' => 2, 'message' => 'You don\'t full excuted artisan command!'], 301);
        } else {
            return response()->json(['status' => 0, 'message' => 'You don\'t excuted artisan command!'], 401);
        }
    }
}
