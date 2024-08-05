<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiamondController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/welcome', function () {
    return view('welcome');
});

// Corrected route for importing diamond data
Route::get('/', [DiamondController::class, 'list'])->name('diamond.list');
Route::post('/data', [DiamondController::class, 'data'])->name('diamond.data');
Route::get('/import', [DiamondController::class, 'import'])->name('diamond.import');
Route::post('/import-save', [DiamondController::class, 'importSave'])->name('diamond.import.save');
Route::post('/export-xlsx', [DiamondController::class, 'exportXlsx'])->name('diamond.export.xlsx');
Route::post('/export-csv', [DiamondController::class, 'exportCsv'])->name('diamond.export.csv');
