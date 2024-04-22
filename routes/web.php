<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use Elastic\Elasticsearch\ClientBuilder;

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


Route::get('/jac', [SearchController::class, 'index']);
Route::get('/autocomplete/{query?}', [SearchController::class, 'autocomplete']);
Route::get('/search/{query?}/{cp?}/{ps?}', [SearchController::class, 'search']);
Route::post('/filter', [SearchController::class, 'filter']);
Route::post('/save', [SearchController::class, 'propertyDocumentSave']);
Route::post('/update', [SearchController::class, 'propertyDocumentUpdate']);