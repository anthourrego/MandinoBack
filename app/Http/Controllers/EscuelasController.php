<?php

namespace App\Http\Controllers;

use App\Models\Escuelas;
use Illuminate\Http\Request;
use DB;

class EscuelasController extends Controller {
    /**
     * Display the specified resource.
     *
     * @param  \App\Escuelas  $escuelas
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request){
        $query = Escuelas::select('id', 'nombre', 'descripcion', 'estado', 'created_at');
        if ($request->estado != '') {
            $query->where("estado", $request->estado);
        }
        return datatables()->eloquent($query)->rawColumns(['nombre', 'descripcion'])->make(true);
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $escuela = Escuelas::find($request->id);
        
        if(is_object($escuela)){
            DB::beginTransaction();
            $escuela->estado = $request->estado;
        
            if ($escuela->save()) {
                $resp["success"] = true;
                $resp["msj"] = "La escuela " . $escuela->nombre . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
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

    public function crear(Request $request){
        $resp["success"] = false;
        $validar = Escuelas::where([
            ['nombre', $request->nombre], 
        ])->get();

        if($validar->isEmpty()){
            $escuela = new Escuelas;
            $escuela->nombre = $request->nombre;
            $escuela->descripcion = $request->descripcion;
            $escuela->estado = $request->estado;
            
            if($escuela->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado la escuela correctamente.";
            }else{
                $resp["msj"] = "No se ha creado la escuela " . $request->nombre;
            }
        }else{
            $resp["msj"] = "La escuela " . $request->nombre . " ya se encuentra registrado.";
        }

        return $resp;
    }

    public function actualizar(Request $request) {
        $resp["success"] = false;
        $validar = Escuelas::where([
            ['id', '<>', $request->id],
            ['nombre', $request->nombre]
          ])->get();
  
        if ($validar->isEmpty()) {

            $escuela = Escuelas::find($request->id);

            if(!empty($escuela)){
                if ($escuela->nombre != $request->nombre || $escuela->descripcion != $request->descripcion || $escuela->estado != $request->estado) {

                    $escuela->nombre = $request->nombre;
                    $escuela->descripcion = $request->descripcion;
                    $escuela->estado = $request->estado;
                    
                    if ($escuela->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado la escuela";
            }
        }else{
            $resp["msj"] = "La escuela " . $request->nombre . " ya se encuentra registrado";
        }
        
        return $resp;
    }

    public function lista(){
        return Escuelas::select("id", "nombre")->where("estado", 1)->get();
    }

    public function traerEscuela($id){
        return Escuelas::select( "nombre", "descripcion")->where("id", $id)->get();
    }

}
