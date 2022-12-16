<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\usuarioscontroller;
use App\Http\Controllers\personasinforma;

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

Route::post('adduser',[usuarioscontroller::class,'AddUser']);
Route::post('verifyuser',[usuarioscontroller::class,'verificar'])->name('verificar')->middleware('signed');
Route::post('/activeuser',[usuarioscontroller::class,'verificarPerfil']);

Route::middleware('auth:sanctum','role:administrador','estado')->group(function(){
    Route::post('/cambiarpass',[usuarioscontroller::class,'cambiarContraseña']);
    Route::post('/desactivaruser',[usuarioscontroller::class,'desactivarUser']);
    Route::post('/addpersona',[personasinforma::class,'addpersona']);
    Route::post('/addinforma/{id?}',[personasinforma::class,'add_informacion_persona']);
});
Route::middleware('auth:sanctum','estado')->group(function(){
    Route::get('/logout',[usuarioscontroller::class,'logout']);
});

Route::prefix('instUno')->middleware()->group(function () {
   
});


Route::get('/login',[usuarioscontroller::class,'login']);

