<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaisesController;
use App\Http\Controllers\DepartamentosController;
use App\Http\Controllers\MunicipiosController;
use App\Http\Controllers\EscuelasController;
use App\Http\Controllers\PermisosController;
use App\Http\Controllers\PerfilesController;

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
        Route::post('cambiarEstado', [DepartamentosController::class, 'cambiarEstado']);
        Route::post('lista', [DepartamentosController::class, 'lista']);
        /* Route::get('lista/{pais}', [DepartamentosController::class, 'lista']); */
    });

    Route::prefix('municipios')->group(function () {
        Route::post('lista', [MunicipiosController::class, 'lista']);
        Route::get('ubicacion/{idMunicipio}', [MunicipiosController::class, 'ubicacion']);
    });

    //Cuidades
    Route::prefix('ciudades')->group(function () {
        Route::post('obtener', [MunicipiosController::class, 'show']);
        Route::post('cambiarEstado', [MunicipiosController::class, 'cambiarEstado']);
        Route::post('crear', [MunicipiosController::class, 'crear']);
        Route::post('actualizar', [MunicipiosController::class, 'update']);
        Route::post('lista', [MunicipiosController::class, 'lista']);
    });

    //Cuidades
    Route::prefix('escuelas')->group(function () {
        Route::post('obtener', [EscuelasController::class, 'show']);
        Route::post('cambiarEstado', [EscuelasController::class, 'cambiarEstado']);
        Route::post('crear', [EscuelasController::class, 'crear']);
        Route::post('actualizar', [EscuelasController::class, 'actualizar']);
        Route::post('lista', [EscuelasController::class, 'lista']);
    });

    //Permisos
    Route::prefix('permisos')->group(function () {
        Route::get('obtener', [PermisosController::class, 'show']);
        Route::post('crear', [PermisosController::class, 'crear']);
        Route::post('cambiarEstado', [PermisosController::class, 'cambiarEstado']);
        Route::post('actualizar', [PermisosController::class, 'update']);
    });

    //Usuarios
    Route::prefix('usuarios')->group(function () {
        Route::post('obtener', [UserController::class, 'obtener']);
        Route::post('cambiarEstado', [UserController::class, 'cambiarEstado']);
        Route::post('crear', [UserController::class, 'crear']);
        Route::post('eliminar', [UserController::class, 'eliminar']);
        Route::post('editar', [UserController::class, 'editar']);
        Route::get('permisos/{idUsuario}', [UserController::class, 'permisos']);
        Route::post('guardarPermiso', [UserController::class, 'guardarPermiso']);
        Route::get('checkearUsuario/{usuario}', [UserController::class, 'checkearUsuario']);
        Route::get('escuelas/{idUsuario}/{idRol}', [UserController::class, 'escuelas']);
        Route::post('editarPefil', [UserController::class, 'editarPefil']);
        Route::post('cambiarPass', [UserController::class, 'cambiarPass']);
    });

    //Perfiles
    Route::prefix('perfiles')->group(function () {
        Route::get('lista', [PerfilesController::class, 'lista']);
        Route::post('crear', [PerfilesController::class, 'crear']);
        Route::post('actualizar', [PerfilesController::class, 'actualizar']);
        Route::post('cambiarEstado', [PerfilesController::class, 'cambiarEstado']);
        Route::post('obtener', [PerfilesController::class, 'show']);
        Route::get('arbol/{idPerfil}', [PerfilesController::class, 'arbol']);
        Route::get('permisos/{idPerfil}', [PerfilesController::class, 'permisos']);
        Route::post('guardarPermiso', [PerfilesController::class, 'guardarPermiso']);
    });
  });
