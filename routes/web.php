<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiamondController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;

Route::get('/', [DiamondController::class, 'list'])->name('diamond.list');
Route::post('/data', [DiamondController::class, 'data'])->name('diamond.data');
Route::post('/update-data', [DiamondController::class, 'updateData'])->name('diamond.update.data');
Route::post('/import-save', [DiamondController::class, 'importSave'])->name('diamond.import.save');
Route::post('/export-xlsx', [DiamondController::class, 'exportXlsx'])->name('diamond.export.xlsx');
Route::post('/export-csv', [DiamondController::class, 'exportCsv'])->name('diamond.export.csv');

Route::middleware(['auth'])->group(function () {
    Route::get('/import', [DiamondController::class, 'import'])->name('diamond.import');
});
Auth::routes();
Route::get('/home', [HomeController::class, 'index'])->name('home');