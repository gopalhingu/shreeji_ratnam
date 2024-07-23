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

Route::get('/', function () {
    return view('welcome');
});

// Corrected route for importing diamond data
Route::get('/list', [DiamondController::class, 'list'])->name('diamond.list');
Route::post('/data', [DiamondController::class, 'data'])->name('diamond.data');
Route::get('/import', [DiamondController::class, 'import'])->name('diamond.import');
Route::post('/import-save', [DiamondController::class, 'importSave'])->name('diamond.import.save');
