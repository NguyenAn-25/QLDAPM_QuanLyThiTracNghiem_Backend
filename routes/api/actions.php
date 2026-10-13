<?php

use App\Http\Controllers\FunctionController;
use Illuminate\Support\Facades\Route;

Route::get('/actions', [FunctionController::class, 'index']);
