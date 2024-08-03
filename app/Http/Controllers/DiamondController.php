<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Diamond;
use Illuminate\Support\Facades\Schema;

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

            foreach ($formattedData as $key => $value) {

                array_shift($value); // Remove first element
                array_pop($value); // Remove last element

                $value['report_date'] = !empty($value['report_date']) ? date("Y-m-d", strtotime($value['report_date'])) : date("Y-m-d");
                $value['price_per_carat'] = (!empty($value['price_per_carat']) && (int)$value['price_per_carat'] > 0) ? $value['price_per_carat'] : '0';
                $value['total_price'] = (!empty($value['total_price']) && (int)$value['total_price'] > 0) ? $value['total_price'] : '0';

                // echo "<pre>";
                // print_r($value);
                // die;

                $getRecord = Diamond::where("stock_id", $value['stock_id'])->first();
                if(!empty($getRecord)) {
                    Diamond::where("stock_id", $value['stock_id'])->update($value);
                } else {
                    Diamond::create($value);
                }
            }

            // $file->move(public_path('excel'), $newFileName);
            // $filePath = public_path('excel/'.$newFileName);

            return redirect()->back()->with('success', 'Excel file imported successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function format_column($column)
    {
        return strtolower(str_replace(" ", "_", str_replace('%', 'percentage', str_replace('#', 'number', $column))));
    }

    public function format_column_reverse($column)
    {
        return ucwords(str_replace("_", " ", str_replace('percentage', '%', str_replace('number', '#', $column))));
    }

    public function list()
    {
        $shapes = Diamond::select('shape')->whereNotNull('shape')->distinct()->pluck('shape');
        $colors = Diamond::select('color')->whereNotNull('color')->distinct()->pluck('color');
        $clarities = Diamond::select('clarity')->whereNotNull('clarity')->distinct()->pluck('clarity');
        $cuts = Diamond::select('cut')->whereNotNull('cut')->distinct()->pluck('cut');
        $polish = Diamond::select('polish')->whereNotNull('polish')->distinct()->pluck('polish');
        $symmetries = Diamond::select('symmetry')->whereNotNull('symmetry')->distinct()->pluck('symmetry');
        $labs = Diamond::select('lab')->whereNotNull('lab')->distinct()->pluck('lab');
        $columns = Schema::getColumnListing('diamonds');
        $columnWithValue = [];
        foreach ($columns as $key => $value) {
            $columnWithValue[$value] = $this->format_column_reverse($value);
        }

        return view("diamond.list",compact('shapes','colors','clarities','cuts','polish','symmetries','labs', 'columnWithValue'));
    }

    public function data(Request $request)
    {       
        $currentPage = $request->input('currentPage', 1);
        $currentPerPage = $request->input('currentPerPage', 10);
        $currentSortColumn = $request->input('currentSortColumn', 'id');
        $currentSortDirection = $request->input('currentSortDirection', 'asc');
        $totalPage = $request->input('totalPage', '');
        $indexID = $request->input('indexID', '');
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
        $shapeList = $request->input('shapeList', []);
        $colorList = $request->input('colorList', []);
        $clarityList = $request->input('clarityList', []);
        $cutList = $request->input('cutList', []);
        $polishList = $request->input('polishList', []);
        $symmetryList = $request->input('symmetryList', []);
        $labList = $request->input('labList', []);

        // Query the database with pagination and sorting
        $query = Diamond::query()
        ->when($minCarat, function ($query, $minCarat) {
            return $query->where('price_per_carat', '>=', $minCarat);
        })
        ->when($maxCarat, function ($query, $maxCarat) {
            return $query->where('price_per_carat', '<=', $maxCarat);
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
            return $query->where('stock_id', $stockId);
        })
        ->when($reportNumber, function ($query, $reportNumber) {
            return $query->where('report_number', $reportNumber);
        })
        ->when($type, function ($query, $type) {
            return $query->where('growth_type', $type);
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

    public function jsonData()
    {
        $record = Diamond::all()->toArray();
        return response()->json($record);
    }
}
