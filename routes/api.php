<?php

use App\Http\Controllers\API\MainController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('v1/index', [MainController::class, 'index']);


Route::post('v1/galeri', [MainController::class, 'createGaleri']);
Route::post('v1/galeri/update/{id}', [MainController::class, 'updateGaleri']);