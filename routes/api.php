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
use App\Http\Controllers\TomaControlVisualizacionesController;
use App\Http\Controllers\UnidadesController;
use App\Http\Controllers\TomaControlComentariosController;
use App\Http\Controllers\TomaControlMeGustaController;
use App\Http\Controllers\LeccionesController;
use App\Http\Controllers\PlataformaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormularioController;

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

    //Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('totales', [DashboardController::class, 'obtenerTotales']);
        Route::post('obtenerComparaciones', [DashboardController::class, 'obtenerComparaciones']);
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
        Route::get('fotoPerfil/{idUsuario}', [UserController::class, 'fotoPerfil']);
        Route::post('deshacerProgreso', [UserController::class, 'deshacerProgreso']);
        Route::post('actualizarFotoPerfil', [UserController::class, 'actualizarFotoPerfil']);
        Route::get('listaUnidadesLeccionesAvance/{id}/{idUser}/{tipo}', [UserController::class, 'listaUnidadesLeccionesAvance']);
        Route::get('escuelasSinPerfil/{idUser}/{idPerfil}', [UserController::class, 'escuelasSinPerfil']);
        Route::post('guardarEscualesSinPerfil', [UserController::class, 'guardarEscualesSinPerfil']);
        Route::get('categoriasSinPerfil/{idUser}/{idPerfil}', [UserController::class, 'categoriasSinPerfil']);
        Route::post('guardarCategoriasSinPerfil', [UserController::class, 'guardarCategoriasSinPerfil']);
        Route::post('actualizarIntroduccion', [UserController::class, 'actualizarIntroduccion']);
    });

    //Perfiles
    Route::prefix('perfiles')->group(function () {
        Route::get('lista', [PerfilesController::class, 'lista']);
        Route::post('crear', [PerfilesController::class, 'crear']);
        Route::post('actualizar', [PerfilesController::class, 'actualizar']);
        Route::post('cambiarEstado', [PerfilesController::class, 'cambiarEstado']);
        Route::post('reiniciarProgreso', [PerfilesController::class, 'reiniciarProgreso']);
        Route::post('obtener', [PerfilesController::class, 'show']);
        Route::get('arbol/{idPerfil}', [PerfilesController::class, 'arbol']);
        Route::get('permisos/{idPerfil}/{idUsuario}', [PerfilesController::class, 'permisos']);
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
        Route::get('traerCurso/{id}', [CursosController::class, 'traerCurso']);
        Route::post('clonar', [CursosController::class, 'clonar']);
    });


    //Escuelas-Cursos
    Route::prefix('escuelas_cursos')->group(function () {
        Route::post('asignar', [CursosController::class, 'asignar']);
        Route::post('desasignar', [CursosController::class, 'desasignar']);
        Route::post('actualizarOrden', [CursosController::class, 'actualizarOrden']);
        Route::post('agregarDependencia', [CursosController::class, 'agregarDependencia']);
        Route::get('listarEscuelasCursos/{idEscuela}', [CursosController::class, 'listarEscuelasCursos']);
        Route::get('listaCursosProgreso/{idEscuela}/{idUser}', [CursosController::class, 'listaCursosProgreso']);
    });
    
    
    //Toma Control
    Route::prefix('toma-control')->group(function () {
        Route::post('obtener', [TomaControlController::class, 'show']);
        Route::post('cambiarEstado', [TomaControlController::class, 'cambiarEstado']);
        Route::post('crear', [TomaControlController::class, 'crear']);
        Route::post('actualizar', [TomaControlController::class, 'actualizar']);
        Route::post('lista', [TomaControlController::class, 'lista']);
        Route::post('upload', [TomaControlController::class, 'upload']);
        Route::get('storage/{id}/{tipo}/{filename}/{navegador}', [TomaControlController::class, 'devolverStorage']);
        Route::post('delete', [TomaControlController::class, 'deleteFile']);
        Route::get('visualizar/{video}/{usuario}', [TomaControlController::class, 'videoVisualizar']);
        Route::post('sugeridos', [TomaControlController::class, 'videosSugeridos']);
        Route::post('videos', [TomaControlController::class, 'videos']);
        Route::get('descargar/{id}/{name}', [TomaControlController::class, 'descargarAnexo']);
        Route::get('traerArchivos/{folderName}', [TomaControlController::class, 'traerArchivos']);
        Route::post('eliminarArchivo', [TomaControlController::class, 'eliminarArchivo']);
        Route::post('subirArchivo', [TomaControlController::class, 'subirArchivo']);
    });

    //Visualizaciones
    Route::prefix('visualizaciones')->group(function () {
        Route::post('crear', [TomaControlVisualizacionesController::class, 'crear']);
        Route::post('actualizar', [TomaControlVisualizacionesController::class, 'actualizar']);
    });

    //Unidades
    Route::prefix('unidades')->group(function () {
        Route::post('obtener', [UnidadesController::class, 'show']);
        Route::post('crear', [UnidadesController::class, 'crear']);
        Route::post('editar', [UnidadesController::class, 'actualizar']);
        Route::post('cambiarEstado', [UnidadesController::class, 'cambiarEstado']);
        Route::get('traerUnidad/{id}', [UnidadesController::class, 'traerUnidad']);
        Route::post('clonar', [UnidadesController::class, 'clonar']);
    });

    //Escuelas-Cursos
    Route::prefix('unidades_cursos')->group(function () {
        Route::post('asignar', [UnidadesController::class, 'asignar']);
        Route::post('desasignar', [UnidadesController::class, 'desasignar']);
        Route::post('actualizarOrden', [UnidadesController::class, 'actualizarOrden']);
        Route::post('agregarDependencia', [UnidadesController::class, 'agregarDependencia']);
        Route::get('listarUnidadesCursos/{idCurso}', [UnidadesController::class, 'listarUnidadesCursos']);
        Route::get('listaUnidadesProgreso/{idCurso}/{idUser}', [UnidadesController::class, 'listaUnidadesProgreso']);
    });
    
    //Comentarios
    Route::prefix('comentarios')->group(function () {
        Route::post('crear', [TomaControlComentariosController::class, 'crear']);
        Route::get('obtener/{id}', [TomaControlComentariosController::class, 'lista']);
    });

    //Me gusta
    Route::prefix('me-gusta')->group(function () {
        Route::post('crear', [TomaControlMeGustaController::class, 'crear']);
        Route::post('actualizar', [TomaControlMeGustaController::class, 'actualizar']);
    });


    //Lecciones
    Route::prefix('lecciones')->group(function () {
        Route::post('obtener', [LeccionesController::class, 'show']);
        Route::post('crear', [LeccionesController::class, 'crear']);
        Route::post('editar', [LeccionesController::class, 'actualizar']);
        Route::post('cambiarEstado', [LeccionesController::class, 'cambiarEstado']);
        Route::get('traerLeccion/{id}', [LeccionesController::class, 'traerLeccion']);
    });

    //Lecciones-unidades
    Route::prefix('lecciones_unidades')->group(function () {
        Route::post('asignar', [LeccionesController::class, 'asignar']);
        Route::post('desasignar', [LeccionesController::class, 'desasignar']);
        Route::post('actualizarOrden', [LeccionesController::class, 'actualizarOrden']);
        Route::post('agregarDependencia', [LeccionesController::class, 'agregarDependencia']);
        Route::get('listarLeccionesUnidades/{idUnidad}', [LeccionesController::class, 'listarLeccionesUnidades']);
        Route::get('listarLeccionesProgreso/{idUnidad}/{usuario}/{vista}', [LeccionesController::class, 'listarLeccionesProgreso']);
    });

    //lecciones-progreso
    Route::prefix('lecciones-progreso')->group(function () {
        Route::post('crear', [LeccionesController::class, 'crearProgreso']);
        Route::post('actualizar', [LeccionesController::class, 'actualizarProgreso']);
        Route::get('screen/{id}/{filename}/{navegador}', [LeccionesController::class, 'getScreenShot']);
        Route::post('cantidadIntentos', [LeccionesController::class, 'cantidadIntentos']);
    });

    // Videos
    Route::prefix('videos')->group(function () {
        Route::post('crear', [LeccionesController::class, 'crearVideo']);
        Route::post('actualizar', [LeccionesController::class, 'crearVideo']);
        Route::get('getVideo/{id}/{tipo}/{filename}/{navegador}', [LeccionesController::class, 'getVideo']);
    });

    // Archivos
    Route::prefix('archivos')->group(function () {
        Route::post('subir', [LeccionesController::class, 'subirArchivo']);
        Route::get('traerTodos/{folderName}', [LeccionesController::class, 'traerTodosArchivos']);
        Route::post('eliminar', [LeccionesController::class, 'eliminarArchivo']);
        Route::get('descargar/{folderName}/{archivo}', [LeccionesController::class, 'descargarArchivo']);
    });

    // Plataforma
    Route::prefix('plataforma')->group(function () {
        Route::get('datos', [PlataformaController::class, 'datosJSON']);
        Route::get('img/{nombreImg}', [PlataformaController::class, 'devolverImg']);
        Route::get('video/{filename}/{navegador}', [PlataformaController::class, 'devolverVideo']);
        Route::post('actualizar', [PlataformaController::class, 'actualizar']);
    });

    // Evaluaciones
    Route::prefix('evaluaciones')->group(function () {
        Route::post('guardar', [LeccionesController::class, 'guardarEvaluacion']);
        Route::post('actualizar', [LeccionesController::class, 'modificarEvaluacion']);
        Route::get('estructura/{idLeccion}', [LeccionesController::class, 'evaluacionEstructura']);
    });

    //Cuidades
    Route::prefix('formularios')->group(function () {
        Route::post('obtener', [FormularioController::class, 'show']);
        Route::post('cambiarEstado', [FormularioController::class, 'cambiarEstado']);
        Route::post('crear', [FormularioController::class, 'crear']);
        Route::post('actualizar', [FormularioController::class, 'actualizar']);
        Route::post('lista', [FormularioController::class, 'lista']);
        Route::get('traerFormulario/{id}', [FormularioController::class, 'traerFormulario']);
    });

});
