<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;

Route::get('/share/ncrs/{id}/print', [PublicController::class, 'print']);

Route::fallback(function () {
    if (request()->is('api') || request()->is('api/*')) {
        abort(404);
    }
    return view('welcome');
});
