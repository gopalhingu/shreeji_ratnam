<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Diamond;
use Yajra\DataTables\Facades\DataTables;

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

            // echo "<pre>";
			// print_r($data);
			// die;

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

    public function list()
    {
        $shapes = Diamond::select('shape')->whereNotNull('shape')->distinct()->pluck('shape');
        $colors = Diamond::select('color')->whereNotNull('color')->distinct()->pluck('color');
        $clarities = Diamond::select('clarity')->whereNotNull('clarity')->distinct()->pluck('clarity');
        $cuts = Diamond::select('cut')->whereNotNull('cut')->distinct()->pluck('cut');
        $polish = Diamond::select('polish')->whereNotNull('polish')->distinct()->pluck('polish');
        $symmetries = Diamond::select('symmetry')->whereNotNull('symmetry')->distinct()->pluck('symmetry');
        $labs = Diamond::select('lab')->whereNotNull('lab')->distinct()->pluck('lab');

        return view("diamond.list",compact('shapes','colors','clarities','cuts','polish','symmetries','labs'));
    }

    public function data(Request $request)
    {
        // Retrieve the parameters for pagination and sorting
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);
        $sortBy = $request->input('sortBy', 'id');
        $sortDirection = $request->input('sortDirection', 'asc');
        $minCarat = $request->input('minCarat', 0);
        $maxCarat = $request->input('maxCarat', 0);
        $shapes = $request->input('shapes', []);
        $colors = $request->input('colors', []);
        $clarities = $request->input('clarities', []);
        $cuts = $request->input('cuts', []);
        $polishes = $request->input('polishes', []);
        $symmetries = $request->input('symmetries', []);
        $stockId = $request->input('stockId', '');
        $reportNumber = $request->input('reportNumber', '');
        $type = $request->input('type', '');

        // Query the database with pagination and sorting
        $query = Diamond::query()
        ->when($minCarat, function ($query, $minCarat) {
            return $query->where('weight', '>=', $minCarat);
        })
        ->when($maxCarat, function ($query, $maxCarat) {
            return $query->where('weight', '<=', $maxCarat);
        })
        ->when($shapes, function ($query, $shapes) {
            return $query->whereIn('shape', $shapes);
        })
        ->when($colors, function ($query, $colors) {
            return $query->whereIn('color', $colors);
        })
        ->when($clarities, function ($query, $clarities) {
            return $query->whereIn('clarity', $clarities);
        })
        ->when($cuts, function ($query, $cuts) {
            return $query->whereIn('cut', $cuts);
        })
        ->when($polishes, function ($query, $polishes) {
            return $query->whereIn('polish', $polishes);
        })
        ->when($symmetries, function ($query, $symmetries) {
            return $query->whereIn('symmetry', $symmetries);
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
        ->orderBy($sortBy, $sortDirection);

        $totalStock = $query->count();
        $totalCarat = $query->sum('weight') ?: 0;
        $totalAmount = $query->sum('total_price') ?: 0;


        $data = $query->paginate($perPage, ['*'], 'page', $page);

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
