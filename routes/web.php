<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;

Route::get('/share/ncrs/{id}/print', [PublicController::class, 'print']);

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
