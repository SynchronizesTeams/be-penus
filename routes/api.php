<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MainController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');
});

Route::middleware('auth:sanctum')->group(function()  {
    Route::prefix('v1/galeri')->group(function (){
        Route::post('/create', [MainController::class, 'createGaleri']);
        Route::post('/update/{id}', [MainController::class, 'updateGaleri']);
        Route::post('/delete/{id}', [MainController::class, 'deleteGaleri']);    
    });

    Route::prefix('v1/sarana',)->group(function(){
        Route::post('/create', [MainController::class, 'createSarana']);
        Route::post('/update/{id}', [MainController::class, 'updateSarana']);
        Route::post('/delete/{id}', [MainController::class, 'deleteSarana']);
    });

    Route::prefix('v1/berita',)->group(function(){
        Route::post('/create', [MainController::class, 'createBerita']);
        Route::post('/update/{id}', [MainController::class, 'updateBerita']);
        Route::post('/delete/{id}', [MainController::class, 'deleteBerita']);
    });
    
});

Route::get('v1/index', [MainController::class, 'index']);

Route::prefix('v1/misc')->group(function() {
    Route::get('tag', [MainController::class, 'getTags']);
});
    