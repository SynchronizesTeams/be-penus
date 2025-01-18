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
    Route::prefix('v1/berita',)->group(function(){
        Route::post('/create', [MainController::class, 'createBerita']);
        Route::post('/update/{berita_id}', [MainController::class, 'updateBerita']);
        Route::post('/delete/{berita_id}', [MainController::class, 'deleteBerita']);
        Route::get('/show/{berita_id}', [MainController::class, 'showBerita']);
        Route::get('/show', [MainController::class, 'showBeritaAll']);
    });

    Route::prefix('v1/galeri')->group(function (){
        Route::post('/create', [MainController::class, 'createGaleri']);
        Route::post('/update/{galeri_id}', [MainController::class, 'updateGaleri']);
        Route::post('/delete/{galeri_id}', [MainController::class, 'deleteGaleri']);    
        Route::get('/show', [MainController::class, 'showGaleri']);    
        Route::get('/show/{galeri_id}', [MainController::class, 'showGaleriById']);    
    });
    
    Route::prefix('v1/sarana',)->group(function(){
        Route::post('/create', [MainController::class, 'createSarana']);
        Route::post('/update/{sarana_id}', [MainController::class, 'updateSarana']);
        Route::post('/delete/{sarana_id}', [MainController::class, 'deleteSarana']);
        Route::get('/show', [MainController::class, 'showSarana']);
        Route::get('/show/{sarana_id}', [MainController::class, 'showSaranaById']);
    });

    Route::get('user', [MainController::class, 'user']);
    
});






Route::prefix('v1/misc')->group(function() {
    
    Route::get('index', [MainController::class, 'index']);
    Route::get('tag', [MainController::class, 'getTags']);
});



    