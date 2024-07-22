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

            $file->move(public_path('excel'), $newFileName);
            $filePath = public_path('excel/'.$newFileName);

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
        return view("diamond.list");
    }

    public function data(Request $request)
    {
        // $getRecord = Diamond::select(['id', 'growth_type', 'stock_id', 'report_number', 'lab', 'shape', 'ratio', 'color', 'clarity', 'rap_amount', 'discounts', 'total_price', '', 'cut', 'polish', 'symmetry', 'fluorescence_intensity'])->paginnate($request->input('per_page', 10));

        // $query = Diamond::select([
        //     'id', 'growth_type', 'stock_id', 'report_number', 'lab', 'shape', 
        //     'ratio', 'color', 'clarity', 'rap_amount', 'discounts', 'total_price', 
        //     'cut', 'polish', 'symmetry', 'fluorescence_intensity'
        // ]);

        $q = Diamond::latest()->get();
        
        return DataTables::of($q)
                ->make(true);
    }
}
