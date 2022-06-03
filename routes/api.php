<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NinjasController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\EncargosController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('ninjas')->group(function()
{
    Route::post('/crear', [NinjasController::class, 'crear']);
    Route::put('/editar/{id}', [NinjasController::class, 'editar']);
    Route::get('/listar', [NinjasController::class, 'listar']);
});
Route::prefix('clientes')->group(function()
{
    Route::post('/crear', [ClientesController::class, 'crear']);
    Route::put('/editar/{id}', [ClientesController::class, 'editar']);
    Route::get('/listar', [ClientesController::class, 'listar']);
});
Route::prefix('misiones')->group(function()
{
    Route::post('/crear', [EncargosController::class, 'crear']);
    Route::put('/editar/{id}', [EncargosController::class, 'editar']);
    Route::put('/terminar/{id}', [EncargosController::class, 'terminar']);
    Route::get('/listar', [EncargosController::class, 'listar']);
    Route::put('/asignar/{encargoId}/{ninjaId}', [EncargosController::class, 'asignar']);
    Route::put('/desasignar/{misionId}/{ninjaId}', [EncargosController::class, 'desasignar']);

});
