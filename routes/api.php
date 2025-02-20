<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiamondController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('/json-data/{type}', [DiamondController::class, 'jsonData'])->name('diamond.json.data');
Route::any('/status/{type}/{stockId}', [DiamondController::class, 'updateStatus'])->name('diamond.update.status');
Route::any('/run-command/{type}/{mig}', [DiamondController::class, 'runCommand'])->name('diamond.run.command');
