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
use App\Http\Controllers\TomaControlCategoriasController;
use App\Http\Controllers\CursosController;
use App\Http\Controllers\TomaControlController;

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

Route::middleware(['guest', 'cors'])->group(function () {
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
        Route::post('listaDepartamentos', [DepartamentosController::class, 'lista']);
        /* Route::get('lista/{pais}', [DepartamentosController::class, 'lista']); */
    });

    //Municipios
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
        Route::get('traerEscuela/{id}', [EscuelasController::class, 'traerEscuela']);
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
        Route::post('upload', [UserController::class, 'upload']);
        Route::post('setFoto', [UserController::class, 'setFoto']);
        Route::get('categorias/{idUsuario}/{idPerfil}', [UserController::class, 'categorias']);
        
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

    //Toma Control Categorias
    Route::prefix('categorias-toma-control')->group(function () {
        Route::post('obtener', [TomaControlCategoriasController::class, 'show']);
        Route::post('cambiarEstado', [TomaControlCategoriasController::class, 'cambiarEstado']);
        Route::post('crear', [TomaControlCategoriasController::class, 'crear']);
        Route::post('actualizar', [TomaControlCategoriasController::class, 'actualizar']);
        Route::post('lista', [TomaControlCategoriasController::class, 'lista']);
    });


    //Cursos
    Route::prefix('cursos')->group(function () {
        Route::post('crear', [CursosController::class, 'crear']);
        Route::post('obtener', [CursosController::class, 'show']);
        Route::post('editar', [CursosController::class, 'actualizar']);
        Route::post('cambiarEstado', [CursosController::class, 'cambiarEstado']);

    });


    //Escuelas-Cursos
    Route::prefix('escuelas_cursos')->group(function () {
        Route::post('asignar', [CursosController::class, 'asignar']);
        Route::post('desasignar', [CursosController::class, 'desasignar']);
        Route::post('actualizarOrden', [CursosController::class, 'actualizarOrden']);
        Route::get('listarEscuelasCursos/{idEscuela}', [CursosController::class, 'listarEscuelasCursos']);
    });
    
    
    //Toma Control
    Route::prefix('toma-control')->group(function () {
        Route::post('obtener', [TomaControlController::class, 'show']);
        Route::post('cambiarEstado', [TomaControlController::class, 'cambiarEstado']);
        Route::post('crear', [TomaControlController::class, 'crear']);
        Route::post('actualizar', [TomaControlController::class, 'actualizar']);
        Route::post('lista', [TomaControlController::class, 'lista']);
        Route::post('upload', [TomaControlController::class, 'upload']);
        Route::get('storage/{id}/{tipo}/{filename}', [TomaControlController::class, 'devolverStorage']);
        Route::post('delete', [TomaControlController::class, 'deleteFile']);
    });


});
