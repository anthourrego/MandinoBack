<?php

namespace App\Http\Controllers;

use App\Models\cursos;
use Illuminate\Http\Request;
use DB;

class CursosController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function crear(Request $request){
        $nombre = $request->nombre;
        $descripcion = $request->descripcion;
        $estado = $request->estado;
        return $this->crearCurso($nombre, $descripcion, $estado);
    }

    private function crearCurso($nombre, $descripcion, $estado){
        $resp["success"] = false;

        $validar = cursos::where([
            ['nombre', $nombre], 
        ])->get();

        if($validar->isEmpty()){
            $curso = new cursos;
            $curso->nombre = $nombre;
            $curso->descripcion = $descripcion;
            $curso->estado = $estado;
            
            if($curso->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado el curso correctamente.";
                $resp["id"] = $curso->id;
            }else{
                $resp["msj"] = "No se ha creado el curso " . $nombre;
            }
        }else{
            $resp["msj"] = "El curso " . $nombre . " ya se encuentra registrado.";
        }

        return $resp;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\cursos  $cursos
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $query = cursos::select('id', 'nombre', 'descripcion', 'estado', 'created_at');
        if ($request->estado != '') {
            $query->where("estado", $request->estado);
        }
        return datatables()->eloquent($query)->rawColumns(['nombre', 'descripcion'])->make(true);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\cursos  $cursos
     * @return \Illuminate\Http\Response
     */
    public function actualizar(Request $request)
    {
        $resp["success"] = false;
        $validar = cursos::where([
            ['id', '<>', $request->id],
            ['nombre', $request->nombre]
        ])->get();
  
        if ($validar->isEmpty()) {

            $curso = cursos::find($request->id);

            if(!empty($curso)){
                if ($curso->nombre != $request->nombre || $curso->descripcion != $request->descripcion || $curso->estado != $request->estado) {

                    $curso->nombre = $request->nombre;
                    $curso->descripcion = $request->descripcion;
                    $curso->estado = $request->estado;
                    
                    if ($curso->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado el curso";
            }
        }else{
            $resp["msj"] = "el curso " . $request->nombre . " ya se encuentra registrado";
        }
        
        return $resp;
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $curso = cursos::find($request->id);
        
        if(is_object($curso)){
            DB::beginTransaction();
            $curso->estado = $request->estado;
        
            if ($curso->save()) {
                $resp["success"] = true;
                $resp["msj"] = "La escuela " . $curso->nombre . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
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

    // asignación escuelas_cursos
    public function asignar(Request $request){
        $resp["success"] = false;

        try {
            $query = DB::table('escuelas_cursos')->insert([
               "fk_curso" => $request->fk_curso,
               "fk_escuela" => $request->fk_escuela,
               "fk_curso_dependencia" => $request->fk_curso_dependencia,
               "estado" => 1,
               "orden" => $request->orden        
            ]);

            $resp["success"] = true;
            $resp["msj"] = " curso asignado correctamente.";
            DB::commit();

            return $resp;
        } catch (\Exception $e) {
            DB::rollback();

            $resp["msj"] = " error al asignar curso.";

            return $resp;
        }

    }

    // listado escuelas_cursos
    public function listarEscuelasCursos($idEscuela){
        
        $query = DB::table('escuelas_cursos')->join('cursos', 'escuelas_cursos.fk_curso', '=', 'cursos.id');
        $query->where('escuelas_cursos.fk_escuela',$idEscuela);
        $query->where('escuelas_cursos.estado',1);
        $query->select(
            "escuelas_cursos.id as escuelas_cursos_id",
            "escuelas_cursos.estado as escuelas_cursos_estado", 
            "escuelas_cursos.orden as escuelas_cursos_orden", 
            "escuelas_cursos.fk_escuela as fk_escuela",
            "escuelas_cursos.fk_curso_dependencia as escuelas_cursos_dependencia",
            "cursos.id as curso_id", "cursos.nombre as curso_nombre", 
            "cursos.descripcion as curso_descripcion",
        );
        $query->orderBy('escuelas_cursos_orden','asc');
        
        return $query->get();

    }

    //desasignar escuela_curso
    public function desasignar(Request $request){

        $resp["success"] = false;

        $validar =  DB::table('escuelas_cursos')->where([
            ['id', '<>', $request->id],
        ])->get();
  

        if (!$validar->isEmpty()) {

            $dependencias = DB::table('escuelas_cursos')->where('fk_curso_dependencia',$request->curso_id)->where('estado',1)->where('fk_escuela', $request->fk_escuela)->get();

            if($dependencias->isEmpty()){
                $escuelaCurso = DB::table('escuelas_cursos')->where('id',$request->id);
                if ( $escuelaCurso->update(["estado" => 0]) ) {
                    $resp["success"] = true;
                    $resp["msj"] = "Se ha desasignado el curso";
                }else{
                    $resp["msj"] = "Error al desasignar";
                }
            }else{
                $resp["msj"] = "Curso es dependencia de otros cursos, no se puede desasignar";
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

        foreach ($request->cursos as $curso) {

            $id = $curso['escuelas_cursos_id'];
            $orden = $curso['escuelas_cursos_orden'];
            try {
                $escuelaCurso = DB::table('escuelas_cursos')->where('id',$id);
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

    //agregar dependencia escuela_curso
    public function agregarDependencia(Request $request){
        $resp["success"] = false;

        $id = $request['id'];
        $idDependencia =  $request['idDependencia'];
        $validar =  DB::table('escuelas_cursos')->where([
            ['id', '<>', $request->id],
        ])->get();
  
        if (!$validar->isEmpty()) {
            $escuelaCurso = DB::table('escuelas_cursos')->where('id',$id);

            if ( $escuelaCurso->update(["fk_curso_dependencia" => $idDependencia]) ) {
                $resp["success"] = true;

                $resp["msj"] = "dependencia".($idDependencia == null ? " des" : " ")."asignada";
            }else{
                $resp["msj"] = "error al asignar dependencia";
            }
        }else{
            $resp["msj"] = "no se encuentra el curso asignado";
        }
        
        return $resp;

    }

    public function traerCurso($id){
        return cursos::select( "nombre", "descripcion")->where("id", $id)->get();
    }

    public function clonar(Request $request){
        $resp["success"] = false;

        $id = $request->id; //id del curso a clonar 
        $nombre = $request->nombre;
        $cursos = cursos::select("descripcion", "estado")->where("id", $id)->get();

        if(!($cursos->isEmpty())){
            $curso = $cursos[0];
            try{
                // creación de nuevo curso
                $nuevoCurso = $this->crearCurso($nombre, $curso->descripcion, $curso->estado);
                //if($nuevoCurso['id']){
                if(true){
                    // listado de unidades que hacen parte del curso para proceder a clonarlas
                    $unidadesController = new UnidadesController();
                    $leccionesController = new LeccionesController();
                    $oldUnidadesCursos = $unidadesController->listarUnidadesCursos($id);
                    
                    //fk_curso: 1
                    //unidad_descripcion: "nada"
                    //unidad_id: 1
                    //unidad_nombre: "Unidad 1"
                    //unidades_cursos_dependencia: null
                    //unidades_cursos_estado: 1
                    //unidades_cursos_id: 1
                    //unidades_cursos_orden: 1

                    // creación estructura de nuevas unidades y lecciones
                    $nuevasUnidades = array();
                    $nuevasLecciones = array();
        
                    foreach( $oldUnidadesCursos as $unidadCurso ){
                        $result = $unidadesController->traerUnidad($unidadCurso->unidad_id);
                        $unidad = $result[0];
                        $unidad->nombre = $unidad->nombre."-".$nombre;
                        array_push($nuevasUnidades, $unidad);

                        $nuevasLecciones[$unidad->nombre] = array();
                        $oldLeccionesUnidades = $leccionesController->listarLeccionesUnidades($unidadCurso->unidad_id);

                        foreach( $oldLeccionesUnidades as $leccionUnidad ){
                            $result = $leccionesController->traerLeccion($leccionUnidad->lecciones_id);
                            $leccion = $result[0];
                            $leccion->nombre = $leccion->nombre."-".$nombre;
                            array_push($nuevasLecciones[$unidad->nombre], $leccion);
                        }
                    }

                    
                    $unidadesIds = array();
                    // creación nuevas Unidades
                    foreach( $nuevasUnidades as $nuevaUnidad ){
                        $nombre =  $nuevaUnidad->nombre;
                        $descripcion =  $nuevaUnidad->descripcion;
                        $estado =  $nuevaUnidad->estado;

                        $unidad = $unidadesController->crearUnidad($nombre, $descripcion, $estado);
                        if($unidad['id']){
                            array_push($unidadesIds, $unidad['id']);
                        }
                    }
                    
                    return $unidadesIds;
                }
            }catch(\Exception $e){
                $resp["msj"] = "Nombre de curso ya existe";
                return $e;//$resp;
            }
        
        
        }else{
            $resp["msj"] = "Curso no encontrado";
            return $resp;
        }
    }
}
