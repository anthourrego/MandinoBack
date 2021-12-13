<?php

namespace App\Http\Controllers;

use App\Models\lecciones;
use Illuminate\Http\Request;
use DB;

class LeccionesController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function crear(Request $request){
        $nombre = $request->nombre;
        $contenido = $request->contenido;
        $estado = $request->estado;
        $tipo = $request->tipo;
        $url_contenido = $request->url_contenido;

        return $this->crearLeccion($nombre, $contenido, $estado, $tipo, $url_contenido);
    }

    public function crearLeccion($nombre, $contenido, $estado, $tipo, $url_contenido){
        $resp["success"] = false;
         
        $validar = lecciones::where([
            ['nombre', $nombre], 
        ])->get();


        if($validar->isEmpty()){
            $leccion = new lecciones;
            $leccion->nombre = $nombre;
            $leccion->contenido = $contenido;
            $leccion->estado = $estado;
            $leccion->tipo = $tipo;
            $leccion->url_contenido = $url_contenido;

            if($leccion->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado la lección correctamente.";
                $resp["id"] = $leccion->id;
            }else{
                $resp["msj"] = "No se ha creado la lección " . $request->nombre;
            }
        }else{
            $resp["msj"] = "la lección" . $nombre . " ya se encuentra registrada.";
        }

        return $resp;

    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Models\lecciones  $lecciones
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $query = lecciones::select('id', 'nombre', 'contenido', 'estado', 'tipo','created_at');
        if ($request->estado != '') {
            $query->where("estado", $request->estado);
        }
        return datatables()->eloquent($query)->rawColumns(['nombre', 'tipo'])->make(true);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\lecciones  $lecciones
     * @return \Illuminate\Http\Response
     */
    public function actualizar(Request $request)
    {
        $resp["success"] = false;
        $validar = lecciones::where([
            ['id', '<>', $request->id],
            ['nombre', $request->nombre]
        ])->get();
  
        if ($validar->isEmpty()) {

            $leccion = lecciones::find($request->id);

            if(!empty($leccion)){
                if ($leccion->contenido != $request->contenido || $leccion->estado != $request->estado || $leccion->nombre != $request->nombre || $leccion->url_contenido != $request->url_contenido || $leccion->tipo != $request->tipo ) {

                    $leccion->nombre = $request->nombre;
                    $leccion->contenido = $request->contenido;
                    $leccion->estado = $request->estado;
                    $leccion->tipo = $request->tipo;
                    $leccion->url_contenido = $request->url_contenido;
            
                    
                    if ($leccion->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado la lección";
            }
        }else{
            $resp["msj"] = "la lección " . $request->nombre . " ya se encuentra registrada";
        }
        
        return $resp;
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $leccion = lecciones::find($request->id);
        
        if(is_object($leccion)){
            DB::beginTransaction();
            $leccion->estado = $request->estado;
        
            if ($leccion->save()) {
                $resp["success"] = true;
                $resp["msj"] = "La escuela " . $leccion->nombre . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
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

    public function traerLeccion($id){
        return lecciones::select("*")->where("id", $id)->get();
    }

    // listado lecciones_unidades
    public function listarLeccionesUnidades($idUnidad){
    
        $query = DB::table('lecciones_unidades')->join('lecciones', 'lecciones_unidades.fk_leccion', '=', 'lecciones.id');
        $query->where('lecciones_unidades.fk_unidad', $idUnidad);
        $query->where('lecciones_unidades.estado',1);
        $query->select(
            "lecciones_unidades.id as lecciones_unidades_id",
            "lecciones_unidades.estado as lecciones_unidades_estado", 
            "lecciones_unidades.orden as lecciones_unidades_orden", 
            "lecciones_unidades.fk_unidad as fk_unidad",
            "lecciones_unidades.fk_leccion_dependencia as lecciones_unidades_dependencia",
            "lecciones.id as lecciones_id", "lecciones.nombre as lecciones_nombre", 
            "lecciones.tipo as lecciones_tipo"
        );
        $query->orderBy('lecciones_unidades_orden','asc');
        
        return $query->get();

    }

    
    // asignación lecciones_unidades
    public function asignar(Request $request){
        $resp["success"] = false;
        try {

            $fk_unidad = $request->fk_unidad;
            $fk_leccion = $request->fk_leccion;
            $fk_leccion_dependencia = $request->fk_leccion_dependencia;
            $estado = 1;
            $orden = $request->orden;  
            DB::commit();
            return $this->asignarLeccionUnidad($fk_unidad, $fk_leccion, $fk_leccion_dependencia, $orden);
        } catch (\Exception $e) {
            DB::rollback();
            $resp["msj"] = " error al asignar lección.";

            return $resp;
        }
    }

    public function asignarLeccionUnidad($fk_unidad, $fk_leccion, $fk_leccion_dependencia, $orden){

        $resp["success"] = false;
        try {
            $query = DB::table('lecciones_unidades')->insert([
               "fk_unidad" => $fk_unidad,
               "fk_leccion" => $fk_leccion,
               "fk_leccion_dependencia" => $fk_leccion_dependencia,
               "estado" => 1,
               "orden" => $orden        
            ]);

            $resp["success"] = true;
            $resp["msj"] = " lección asignada correctamente.";
            DB::commit();

            return $resp;
        } catch (\Exception $e) {
            DB::rollback();

            $resp["msj"] = " error al asignar lección.";
            return $resp;
        }

    }

    //desasignar lecciones_unidades
    public function desasignar(Request $request){

        $resp["success"] = false;

        $validar =  DB::table('lecciones_unidades')->where([
            ['id', '<>', $request->id],
        ])->get();
  

        if (!$validar->isEmpty()) {

            $dependencias = DB::table('lecciones_unidades')->where('fk_leccion_dependencia',$request->leccion_id)->where('estado',1)->where('fk_unidad',$request->fk_unidad)->get();

            if($dependencias->isEmpty()){
                $unidadCurso = DB::table('lecciones_unidades')->where('id',$request->id);
                
                if ( $unidadCurso->update(["estado" => 0]) ) {
                    $resp["success"] = true;
                    $resp["msj"] = "Se ha desasignado la leccion";
                }else{
                    $resp["msj"] = "Error al desasignar";
                }
            }else{
                $resp["msj"] = "Leccion es dependencia de otras leccion, no se puede desasignar";
            }
        
            
        }else{
            $resp["msj"] = "no se encuentra lección asignada";
        }
        
        return $resp;

    }

    //actualizar orden lecciones_unidades
    public function actualizarOrden(Request $request){

        $resp["success"] = false;
        $opts = [];

        foreach ($request->lecciones as $leccion) {

            $id = $leccion['lecciones_unidades_id'];
            $orden = $leccion['lecciones_unidades_orden'];
        
            try {
    
                $leccionUnidad = DB::table('lecciones_unidades')->where('id',$id);

                if ( $leccionUnidad->update(["orden" => $orden]) ) {
                    $resp["success"] = true;
                    $resp["msj"] = "Se ha cambiado el orden de lección";
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
    
    //agregar dependencia lecciones_unidades
    public function agregarDependencia(Request $request){
        $resp["success"] = false;

        $id = $request['id'];
        $idDependencia =  $request['idDependencia'];
        $validar =  DB::table('lecciones_unidades')->where([
            ['id', '<>', $request->id],
        ])->get();
    
        if (!$validar->isEmpty()) {
            $escuelaCurso = DB::table('lecciones_unidades')->where('id',$id);

            if ( $escuelaCurso->update(["fk_leccion_dependencia" => $idDependencia]) ) {
                $resp["success"] = true;

                $resp["msj"] = "dependencia".($idDependencia == null ? " des" : " ")."asignada";
            }else{
                $resp["msj"] = "error al asignar dependencia";
            }
        }else{
            $resp["msj"] = "no se encuentra la lección asignado";
        }
        
        return $resp;

    }
}

