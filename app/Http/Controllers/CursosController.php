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

    // listado escuelas_cursos
    public function listaCursosProgreso($idEscuela, $idUser) {

        $unidades = DB::table('lecciones_unidades')
            ->selectRaw('IF(
                COUNT(*) = (
                    IF(lecciones_progreso_usuarios.fecha_completado, COUNT(*), 0)
                ), 1, 0) AS Completa,
                lecciones_unidades.fk_unidad
            ')
            ->join('lecciones_progreso_usuarios', 'lecciones_unidades.fk_leccion', '=', 'lecciones_progreso_usuarios.fk_leccion')
            ->where('lecciones_progreso_usuarios.fk_user', $idUser)
            ->groupBy('lecciones_unidades.fk_unidad');

        $cursos = DB::table('unidades_cursos')
            ->selectRaw('COUNT(*) AS cantUnidades,
                COUNT(UCT.Completa) AS cantUniCompletas,
                unidades_cursos.fk_curso
            ')
            ->leftJoinSub($unidades, "UCT", function ($join) {
                $join->on("unidades_cursos.fk_unidad", "=", "UCT.fk_unidad");
            })
            ->where('unidades_cursos.estado', 1)
            ->groupBy('unidades_cursos.fk_curso');

        $query = DB::table('escuelas_cursos')
            ->join('cursos', 'escuelas_cursos.fk_curso', '=', 'cursos.id')
            ->leftJoinSub($cursos, "CT", function ($join) {
                $join->on("escuelas_cursos.fk_curso", "=", "CT.fk_curso");
            })
            ->leftJoinSub($cursos, "CT2", function ($join) {
                $join->on("escuelas_cursos.fk_curso_dependencia", "=", "CT2.fk_curso");
            })
            ->where('escuelas_cursos.fk_escuela', $idEscuela)
            ->where('escuelas_cursos.estado', 1)
            ->select(
                "escuelas_cursos.id as escuelasCursoId",
                "cursos.id as cursoId",
                "cursos.nombre as nombre",
                "escuelas_cursos.fk_curso_dependencia AS cursoDepende",
                "cursos.descripcion as descripcion",
            )
            ->selectRaw(
                "IF(CT2.cantUniCompletas IS NULL, 0, CT2.cantUniCompletas) AS cantUniCompletasDepende,
                IF(CT2.cantUnidades IS NULL, 0, CT2.cantUnidades) AS cantUnidadesDepende,
                IF(CT.cantUniCompletas IS NULL, 0, CT.cantUniCompletas) AS cantUniCompletas,
                IF(CT.cantUnidades IS NULL, 0, CT.cantUnidades) AS cantUnidades,
                (
                    (
                        IF(CT.cantUniCompletas IS NULL, 0, CT.cantUniCompletas) * 100
                    ) / IF(CT.cantUnidades IS NULL, 0, CT.cantUnidades)
                ) AS progresoActual"
            )
            ->orderBy('escuelas_cursos.orden','asc');
        
        return $query->get();
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
                DB::beginTransaction();
                $nuevoCurso = $this->crearCurso($nombre, $curso->descripcion, $curso->estado);
                if(isset($nuevoCurso['id'])){
                    // listado de unidades que hacen parte del curso para proceder a clonarlas
                    $unidadesController = new UnidadesController();
                    $leccionesController = new LeccionesController();
                    $oldUnidadesCursos = $unidadesController->listarUnidadesCursos($id);

                    // creación estructura de nuevas unidades y lecciones
                    $nuevasLecciones = array();
                    $unidadesIds = array();
                    $unidadesOldIds = array(); // acá se almacenan los antiguos ids para mantener dependencias de unidadesCursos
                    $leccionesUnidadesDeps = array();

                    foreach( $oldUnidadesCursos as $unidadCurso ){
                        $result = $unidadesController->traerUnidad($unidadCurso->unidad_id);
                        $unidad = $result[0];
                        $unidad->nombre = $unidad->nombre."-".$nombre;
                        
                        
                        // datos unidad nueva
                        $nombreNuevo =  $unidad->nombre;
                        $descripcionNuevo =  $unidad->descripcion;
                        $estadoNuevo =  $unidad->estado;
                        // creación nueva unidad 
                        $unidadNueva = $unidadesController->crearUnidad($nombreNuevo, $descripcionNuevo, $estadoNuevo);

                        if(isset($unidadNueva['id'])){
                            $unidadesOldIds[$unidadCurso->unidad_id] = array('nuevoId' => $unidadNueva['id'], 'dependencia' => $unidadCurso->unidades_cursos_dependencia);
                            $unidadesIds[$unidadCurso->unidad_id] =  $unidadNueva['id'];
                        }

                        $nuevasLecciones[$unidadNueva['id']] = array();
                        $oldLeccionesUnidades = $leccionesController->listarLeccionesUnidades($unidadCurso->unidad_id);

                        foreach( $oldLeccionesUnidades as $leccionUnidad ){ // se traen las lecciones anteriores para duplicar

                            $leccionesUnidadesDeps[$unidadNueva['id']][$leccionUnidad->lecciones_id] = array('dependencia' => $leccionUnidad->lecciones_unidades_dependencia);

                            $result = $leccionesController->traerLeccion($leccionUnidad->lecciones_id);
                            $leccion = $result[0];
                            $leccion->nombre = $leccion->nombre."-".$nombre;

                            array_push($nuevasLecciones[$unidadNueva['id']], $leccion);
                        }
                    }



                   
                    $leccionesIds = array();
                    // creación nuevas Lecciones
                    foreach( $nuevasLecciones as $nuevaUnidadId => $lecciones ){
                        $leccionesIds[$nuevaUnidadId] = array();
                        
                        foreach($lecciones as $leccion){
                            
                            $nombre = $leccion->nombre;
                            $contenido = $leccion->contenido;
                            $estado = $leccion->estado;
                            $tipo = $leccion->tipo;
                            $url_contenido = $leccion->url_contenido;
                            $leccionNueva = $leccionesController->crearLeccion($nombre, $contenido, $estado, $tipo, $url_contenido);
    
                            
                            if(isset($leccionNueva['id'])){
                                $leccionesUnidadesDeps[$nuevaUnidadId][$leccion->id]['nuevoId'] = $leccionNueva['id'];
                                array_push($leccionesIds[$nuevaUnidadId], array('nuevoId' => $leccionNueva['id'], 'oldId' => $leccion->id));
                            }else{
                                //echo 'no se creó unidad: '.$nombre;
                            }
                        }
                    }

                    $contUnidades = 1;// contador para establecer orden;
                    foreach($unidadesIds as $oldId => $idUnidad ){ // asignar unidades_cursos
                        $dependencia = isset($unidadesIds[ $unidadesOldIds[$oldId]['dependencia']]) ? $unidadesIds[ $unidadesOldIds[$oldId]['dependencia']] : null;
                        $unidadesController->asignarUnidadCurso($nuevoCurso['id'], $idUnidad, $dependencia, $contUnidades );
                        $contUnidades++;                        
                    }

                    //asignar lecciones_unidades
                    foreach($leccionesIds as $idUnidad=>$leccion ){
                        $contLecciones = 1; // contador para establecer orden;
                        foreach($leccion as $ids){
                            
                            $dependencia = null;

                            if(isset($leccionesUnidadesDeps[$idUnidad][$ids['oldId']]['dependencia'])){
                                $dep = $leccionesUnidadesDeps[$idUnidad][$ids['oldId']]['dependencia'];
                                if(isset($dep)){
                                    $dependencia = $leccionesUnidadesDeps[$idUnidad][$dep]['nuevoId'];
                                }
                            }

                            $leccionesController->asignarLeccionUnidad($idUnidad, $ids['nuevoId'], $dependencia, $contLecciones);
                            $contLecciones++;
                        }
                    }

                    DB::commit();
                    
                    $resp["msj"] = "Curso Clonado";
                    $resp["success"] = true;                    
                    return $resp;
                }
            }catch(\Exception $e){
                $resp["msj"] = "nombre de curso ya existe";
                $resp['exc'] = $e;
                DB::rollBack();
                return $e;
            }
        }else{
            $resp["msj"] = "Curso no encontrado";
            return $resp;
        }
    }
}
