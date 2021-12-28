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
        $query = unidades::select('id', 'nombre', 'descripcion', 'estado', 'created_at');
        if ($request->estado != '') {
            $query->where("estado", $request->estado);
        }
        return datatables()->eloquent($query)->rawColumns(['nombre', 'descripcion'])->make(true);
    }

    public function crear(Request $request){
        $nombre = $request->nombre;
        $descripcion = $request->descripcion;
        $estado = $request->estado;

        return $this->crearUnidad($nombre, $descripcion, $estado);
    }

    public function crearUnidad($nombre, $descripcion, $estado){

        $resp["success"] = false;
        $validar = unidades::where([
            ['nombre', $nombre], 
        ])->get();

        if($validar->isEmpty()){
            $unidad = new unidades;
            $unidad->nombre =$nombre;
            $unidad->descripcion =$descripcion;
            $unidad->estado =$estado;

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
                if ($unidad->nombre != $request->nombre || $unidad->descripcion != $request->descripcion || $unidad->estado != $request->estado) {

                    $unidad->nombre = $request->nombre;
                    $unidad->descripcion = $request->descripcion;
                    $unidad->estado = $request->estado;
                    
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
            ['id', '<>', $request->id],
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
        $validar =  DB::table('unidades_cursos')->where([
            ['id', '<>', $request->id],
        ])->get();
  
        if (!$validar->isEmpty()) {
            $escuelaCurso = DB::table('unidades_cursos')->where('id',$id);

            if ( $escuelaCurso->update(["fk_unidad_dependencia" => $idDependencia]) ) {
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

    
    public function clonar(Request $request){
        $resp["success"] = false;

        $id = $request->id; //id del curso a clonar 
        $nombre = $request->nombre;
        $unidades = unidades::select("descripcion", "estado", "nombre")->where("id", $id)->get();
        if(!($unidades->isEmpty())){
            try{
                $unidad = $unidades[0];
                DB::beginTransaction();
                // creación de nueva unidad
                $nuevaUnidad = $this->crearUnidad($unidad->nombre."-".$nombre, $unidad->descripcion, $unidad->estado);
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
