<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaisesController;
use App\Http\Controllers\DepartamentosController;
use App\Http\Controllers\MunicipiosController;

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

Route::middleware(['guest'])->group(function () {
    //Rutas a las que se permitirá acceso
    Route::get('login/{nroDoc}/{pass}', [UserController::class, 'inicioSesion']);
    
    /* Route::get('login/{nroDoc}/{pass}', 'UserController@inicioSesion'); */
    /* Route::get('listaPlatos', 'PlatosController@listaPlatos'); */
    /* Route::get('validarToken/{tiempoToken}', 'UserController@validarToken');  */
    //Route::post('registrarse', 'UsuariosController@registrarse');
    //Route::get('platos/lista', 'PlatosController@show');
    //Route::get('platos/dia', 'PlatosController@platosDia');
    //Route::get('promo/lista', 'PromocionesController@show');
    //Route::post('pedidos/crear', 'PedidoController@realizarPedido');
    
    //Paises
    Route::prefix('paises')->group(function () {
        Route::post('obtener', [PaisesController::class, 'show']);
        Route::post('cambiarEstado', [PaisesController::class, 'cambiarEstado']);
        Route::post('crear', [PaisesController::class, 'crear']);
        Route::post('actualizar', [PaisesController::class, 'update']);
        Route::get('lista', [PaisesController::class, 'lista']);
    });

    //Departamentos
    Route::prefix('departamentos')->group(function () {
        Route::post('obtener', [DepartamentosController::class, 'show']);
        Route::post('crear', [DepartamentosController::class, 'crear']);
        Route::post('actualizar', [DepartamentosController::class, 'update']);
        Route::post('lista', [DepartamentosController::class, 'lista']);

        /* Route::post('cambiarEstado', [PaisesController::class, 'cambiarEstado']);*/
    });

    Route::prefix('municipios')->group(function () {
        Route::post('lista', [MunicipiosController::class, 'lista']);
        Route::post('ubicacion', [MunicipiosController::class, 'ubicacion']);
    });


    Route::prefix('usuarios')->group(function () {
        Route::post('obtener', [UserController::class, 'obtener']);
        Route::post('cambiarEstado', [UserController::class, 'cambiarEstado']);
        Route::post('crear', [UserController::class, 'crear']);
        Route::post('eliminar', [UserController::class, 'eliminar']);
        Route::post('editar', [UserController::class, 'editar']);
    });
  });
