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
        $resp["success"] = false;
        $validar = cursos::where([
            ['nombre', $request->nombre], 
        ])->get();

        if($validar->isEmpty()){
            $curso = new cursos;
            $curso->nombre = $request->nombre;
            $curso->descripcion = $request->descripcion;
            $curso->estado = $request->estado;

            if($curso->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado el curso correctamente.";
            }else{
                $resp["msj"] = "No se ha creado el curso " . $request->nombre;
            }
        }else{
            $resp["msj"] = "El curso " . $request->nombre . " ya se encuentra registrado.";
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
    public function listaCursosProgreso($idEscuela){
        $query = DB::table('escuelas_cursos')
            ->join('cursos', 'escuelas_cursos.fk_curso', '=', 'cursos.id')
            ->where('escuelas_cursos.fk_escuela', $idEscuela)
            ->where('escuelas_cursos.estado', 1)
            ->select(
                "escuelas_cursos.id as escuelasCursoId",
                "cursos.id as cursoId",
                "cursos.nombre as nombre", 
                "cursos.descripcion as descripcion",
            )->orderBy('escuelas_cursos.orden','asc');
        
        return $query->get();

    }
}
