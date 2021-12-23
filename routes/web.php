<?php

use App\Helpers\ArrayHelper;
use chillerlan\QRCode\QRCode;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Qr\QrController;
use App\Http\Controllers\Receipt\ReceiptController;

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


Route::get('/qr/{code}', [QrController::class, 'show'])
    ->name('qr');

Route::get('/api/v1/receipt/{inn}/{approvedReceiptUuid}/print', [ReceiptController::class, 'print']);

Route::get('receipt/test', [ReceiptController::class, 'test']);

Route::get('preview', function () {
});
