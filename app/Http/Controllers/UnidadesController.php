<?php

namespace App\Http\Controllers;

use App\Models\unidades;
use Illuminate\Http\Request;
use DB;

class UnidadesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\unidades  $unidades
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $query = unidades::select('id', 'nombre', 'descripcion', 'estado', 'created_at', 'color');
        if ($request->estado != '') {
            $query->where("estado", $request->estado);
        }
        return datatables()->eloquent($query)->rawColumns(['nombre', 'descripcion'])->make(true);
    }

    public function crear(Request $request){
        $nombre = $request->nombre;
        $descripcion = $request->descripcion;
        $estado = $request->estado;
        $color = $request->color;

        return $this->crearUnidad($nombre, $descripcion, $estado, $color);
    }

    public function crearUnidad($nombre, $descripcion, $estado, $color){

        $resp["success"] = false;
        $validar = unidades::where([
            ['nombre', $nombre], 
        ])->get();

        if($validar->isEmpty()){
            $unidad = new unidades;
            $unidad->nombre =$nombre;
            $unidad->descripcion =$descripcion;
            $unidad->estado =$estado;
            $unidad->color =$color;

            if($unidad->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado la unidad correctamente.";
                $resp["id"] = $unidad->id;
            }else{
                $resp["msj"] = "No se ha creado la unidad " . $nombre;
            }
        }else{
            $resp["msj"] = "la unidad " . $nombre . " ya se encuentra registrado.";
        }

        return $resp;
    }


    public function actualizar(Request $request){
        $resp["success"] = false;
        $validar = unidades::where([
            ['id', '<>', $request->id],
            ['nombre', $request->nombre]
        ])->get();
  
        if ($validar->isEmpty()) {

            $unidad = unidades::find($request->id);

            if(!empty($unidad)){
                if ($unidad->nombre != $request->nombre || $unidad->descripcion != $request->descripcion || $unidad->estado != $request->estado || $unidad->color != $request->color) {

                    $unidad->nombre = $request->nombre;
                    $unidad->descripcion = $request->descripcion;
                    $unidad->estado = $request->estado;
                    $unidad->color = $request->color;
                    
                    if ($unidad->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado la unidad";
            }
        }else{
            $resp["msj"] = "la unidad " . $request->nombre . " ya se encuentra registrada";
        }
        
        return $resp;
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $unidad = unidades::find($request->id);
        
        if(is_object($unidad)){
            DB::beginTransaction();
           $unidad->estado = $request->estado;
        
            if ($unidad->save()) {
                $resp["success"] = true;
                $resp["msj"] = "La escuela " .$unidad->nombre . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            }else{
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        }else{
            $resp["msj"] = "No se ha encontrado la escuela";
        }
        return $resp; 
    }

    public function traerUnidad($id){
        return unidades::select("id", "nombre", "descripcion", "estado")->where("id", $id)->get();
    }

    // asignación escuelas_cursos
    public function asignar(Request $request){

        
        $resp["success"] = false;
        $fk_curso = $request->fk_curso;
        $fk_unidad = $request->fk_unidad;
        $fk_unidad_dependencia = $request->fk_unidad_dependencia;
        $estado = 1;
        $orden = $request->orden;

        try{
            return $this->asignarUnidadCurso($fk_curso, $fk_unidad, $fk_unidad_dependencia , $orden);
            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
            $resp["msj"] = " error al asignar unidad.";

            return $resp;
        }

    }

    public function asignarUnidadCurso($fk_curso, $fk_unidad, $fk_unidad_dependencia , $orden){

        $resp["success"] = false;

        $query = DB::table('unidades_cursos')->insert([
            "fk_curso" => $fk_curso,
            "fk_unidad" => $fk_unidad,
            "fk_unidad_dependencia" => $fk_unidad_dependencia,
            "estado" => 1,
            "orden" => $orden        
        ]);

        $resp["success"] = true;
        $resp["msj"] = " unidad asignada correctamente.";
        DB::commit();

        return $resp;
    
    }

    // listado unidades_cursos
    public function listarUnidadesCursos($idCurso){
        
        $query = DB::table('unidades_cursos')->join('unidades', 'unidades_cursos.fk_unidad', '=', 'unidades.id');
        $query->where('unidades_cursos.fk_curso', $idCurso);
        $query->where('unidades_cursos.estado',1);
        $query->select(
            "unidades_cursos.id as unidades_cursos_id",
            "unidades_cursos.estado as unidades_cursos_estado", 
            "unidades_cursos.orden as unidades_cursos_orden", 
            "unidades_cursos.fk_curso as fk_curso",
            "unidades_cursos.fk_unidad_dependencia as unidades_cursos_dependencia",
            "unidades_cursos.tiempo_dependencia as unidades_cursos_tiempo_dependencia", 
            "unidades.id as unidad_id", "unidades.nombre as unidad_nombre", 
            "unidades.descripcion as unidad_descripcion",

        );
        $query->orderBy('unidades_cursos_orden','asc');
        
        return $query->get();

    }

    //desasignar escuela_curso
    public function desasignar(Request $request){

        $resp["success"] = false;

        $validar =  DB::table('unidades_cursos')->where([
            ['id', '=', $request->id],
        ])->get();
  

        if (!$validar->isEmpty()) {

            $dependencias = DB::table('unidades_cursos')->where('fk_unidad_dependencia',$request->unidad_id)->where('estado',1)->where('fk_curso',$request->curso_id)->get();

            if($dependencias->isEmpty()){
                $unidadCurso = DB::table('unidades_cursos')->where('id',$request->id);
                
                if ( $unidadCurso->update(["estado" => 0]) ) {
                    $resp["success"] = true;
                    $resp["msj"] = "Se ha desasignado la unidad";
                }else{
                    $resp["msj"] = "Error al desasignar";
                }
            }else{
                $resp["msj"] = "Unidad es dependencia de otras unidad, no se puede desasignar";
            }
        
            
        }else{
            $resp["msj"] = "no se encuentra curso asignado";
        }
        
        return $resp;

    }

    //actualizar orden escuela_curso
    public function actualizarOrden(Request $request){

        $resp["success"] = false;
        $opts = [];

        foreach ($request->unidades as $unidad) {

            $id = $unidad['unidades_cursos_id'];
            $orden = $unidad['unidades_cursos_orden'];
            try {
                $escuelaCurso = DB::table('unidades_cursos')->where('id',$id);
                if ( $escuelaCurso->update(["orden" => $orden]) ) {
                    $resp["success"] = true;
                    $resp["msj"] = "Se ha cambiado el curso";
                    array_push($opts, $id, $orden);
                }
               
            } catch (\Exception $e) {
                DB::rollback();
                $resp["msj"] = 'exepcion';
                return $resp;
                break;
            }

        }

        $resp["msj"] = "orden cambiado correctamente.";
        return $resp;
    }   

    //desasignar dependencia escuela_curso
    public function agregarDependencia(Request $request){
        $resp["success"] = false;

        $id = $request['id'];
        $idDependencia =  $request['idDependencia'];
        $diasDependencia =  $request['diasDependencia'];


        $validar =  DB::table('unidades_cursos')->where([
            ['id', '<>', $request->id],
        ])->get();
  
        if (!$validar->isEmpty()) {
            $escuelaCurso = DB::table('unidades_cursos')->where('id',$id);

            if ( $escuelaCurso->update(["fk_unidad_dependencia" => $idDependencia, "tiempo_dependencia" => $diasDependencia]) ) {
                $resp["success"] = true;

                $resp["msj"] = "dependencia".($idDependencia == null ? " des" : " ")."asignada";
            }else{
                $resp["msj"] = "error al asignar dependencia";
            }
        }else{
            $resp["msj"] = "no se encuentra la unidad asignado";
        }
        
        return $resp;

    }

    public function listaUnidadesProgreso($idCurso, $idUser) {

        $cantLecciones = DB::table('lecciones_unidades')
            ->selectRaw('COUNT(*) AS cantLecciones, lecciones_unidades.fk_unidad')
            ->where('lecciones_unidades.estado', 1)
            ->groupBy('lecciones_unidades.fk_unidad');

        $lecProg = DB::table('lecciones_progreso_usuarios')
            ->selectRaw('lecciones_progreso_usuarios.fk_leccion, lecciones_progreso_usuarios.fecha_completado')
            ->where('lecciones_progreso_usuarios.fk_user', $idUser);

        $lecciones = DB::table('lecciones_unidades')
            ->selectRaw('IF(
                COUNT(*) = (
                    IF(progLec.fecha_completado, COUNT(*), 0)
                ), 1, 0) AS Completa,
                (
                    (
                        IF(
                            COUNT(progLec.fecha_completado) IS NULL, 0, COUNT(progLec.fecha_completado)
                        ) * 100
                    ) / COUNT(*)
                ) AS progresoActual,
                lecciones_unidades.fk_unidad
            ')
            ->leftJoinSub($lecProg, "progLec", function ($join) {
                $join->on("lecciones_unidades.fk_leccion", "=", "progLec.fk_leccion");
            })
            ->leftJoinSub($cantLecciones, "lecCant", function ($join) {
                $join->on("lecciones_unidades.fk_unidad", "=", "lecCant.fk_unidad");
            })
            ->where('lecciones_unidades.estado', 1)
            ->groupBy('lecciones_unidades.fk_unidad');

        $query = DB::table('unidades_cursos')
            ->join('unidades', 'unidades_cursos.fk_unidad', '=', 'unidades.id')
            ->leftJoinSub($lecciones, "lecciones", function ($join) {
                $join->on("unidades_cursos.fk_unidad_dependencia", "=", "lecciones.fk_unidad");
            })
            ->leftJoinSub($lecciones, "lecciones2", function ($join) {
                $join->on("lecciones2.fk_unidad", "=", "unidades.id");
            })
            ->leftJoinSub($cantLecciones, "LCT", function ($join) {
                $join->on("LCT.fk_unidad", "=", "unidades.id");
            })
            ->where('unidades_cursos.fk_curso', $idCurso)
            ->where('unidades_cursos.estado', 1)
            ->select(
                "unidades_cursos.id AS unidadesCursosId",
                "unidades_cursos.tiempo_dependencia as tiempoDependencia",
                "unidades.id AS unidadId",
                "unidades.nombre AS nombre",
                "unidades.color AS color",
                "unidades_cursos.fk_unidad_dependencia AS unidadDependencia", 
                "unidades.descripcion AS descripcion",
                "lecciones2.*",
                "lecciones.Completa AS completaDepende",
                "lecciones.progresoActual AS progresoActualDepende",
                "LCT.cantLecciones"
            )->selectRaw("IF(lecciones.progresoActual = 100, (SELECT LPU.fecha_completado FROM lecciones_unidades LU LEFT JOIN lecciones_progreso_usuarios LPU ON LU.fk_leccion = LPU.fk_leccion WHERE LU.fk_unidad = unidades_cursos.fk_unidad_dependencia AND LPU.fk_user = $idUser AND LU.estado = 1  ORDER BY LU.orden DESC LIMIT 1), NULL) AS FechaCompletadoDepende")
            ->orderBy('unidades_cursos.orden','asc');
        
        $query = $query->get(); 

        return $query;
    }
    
    public function clonar(Request $request){
        $resp["success"] = false;

        $id = $request->id; //id del curso a clonar 
        $nombre = $request->nombre;
        $unidades = unidades::select("descripcion", "estado", "nombre", "color")->where("id", $id)->get();
        if(!($unidades->isEmpty())){
            try{
                $unidad = $unidades[0];
                DB::beginTransaction();
                // creación de nueva unidad
                $nuevaUnidad = $this->crearUnidad($unidad->nombre."-".$nombre, $unidad->descripcion, $unidad->estado, $unidad->color);
                $leccionesController = new LeccionesController();
                $oldLeccionesUnidades = $leccionesController->listarLeccionesUnidades($id);
                
                $nuevasLecciones = array();
                $idsDependencias = array();

                foreach( $oldLeccionesUnidades as $leccionUnidad ){

                    $idsDependencias[$leccionUnidad->lecciones_id] = array('dependencia' => $leccionUnidad->lecciones_unidades_dependencia );

                    $result = $leccionesController->traerLeccion($leccionUnidad->lecciones_id);
                    $leccion = $result[0];
                    $leccion->nombre = $leccion->nombre."-".$nombre;
                    array_push($nuevasLecciones, $leccion);
                }

                // return $idsDependencias;
                $leccionesIds = array();
                foreach( $nuevasLecciones as $nuevaLeccion => $leccion ){

                    $nombre = $leccion->nombre;
                    $contenido = $leccion->contenido;
                    $estado = $leccion->estado;
                    $tipo = $leccion->tipo;
                    $url_contenido = $leccion->url_contenido;

                    $leccionNueva = $leccionesController->crearLeccion($nombre, $contenido, $estado, $tipo, $url_contenido);
                    if($leccionNueva['id']){
                        $idsDependencias[$leccion->id]['nuevoId'] = $leccionNueva['id'];
                        array_push($leccionesIds, array('nuevoId' => $leccionNueva['id'], 'oldId' => $leccion->id));
                    }
                }

                //asignar lecciones_unidades
                $contLecciones = 1;
                foreach($leccionesIds as $leccion ){

                    $dependencia = null;
                    if(isset($idsDependencias[$leccion['oldId']]['dependencia'])){
                        $dep = $idsDependencias[$leccion['oldId']]['dependencia'];
                        $dependencia = $idsDependencias[$dep]['nuevoId'];
                    }
                    
                    $leccionesController->asignarLeccionUnidad($nuevaUnidad['id'], $leccion['nuevoId'], $dependencia, $contLecciones);
                    $contLecciones++; 
                }

                DB::commit();
                $resp["msj"] = "Curso Clonado";
                $resp["success"] = true;
                return $resp;
                
            }catch(\Exception $e){
                $resp["msj"] = "nombre de unidad ya existe";
                DB::rollBack();
                return $e;
            }
        }

    }

}
