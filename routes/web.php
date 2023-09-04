<?php

use App\Http\Controllers\AmoLeadController;
use App\Services\AmoClientService;
use App\Services\AmoTokenService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::match(['get', 'post'], '/token', function () {
    include '../resources/views/tokens/authorization.php';
});


