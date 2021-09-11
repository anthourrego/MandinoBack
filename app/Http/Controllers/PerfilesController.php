<?php

namespace App\Http\Controllers;

use App\Models\perfiles;
use Illuminate\Http\Request;
use DB;

class PerfilesController extends Controller {

    public function lista(){
        return perfiles::select("id", "nombre")->where("estado", 1)->get();
    }

    public function crear(Request $request){
        $resp["success"] = false;
        $validar = perfiles::where([
            ['nombre', $request->nombre], 
        ])->get();

        if($validar->isEmpty()){
            $perfil = new perfiles;
            $perfil->nombre = $request->nombre;
            $perfil->estado = $request->estado;

            if($perfil->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado el perfil correctamente.";
            }else{
                $resp["msj"] = "No se ha creado el perfil " . $request->nombre;
            }
        }else{
            $resp["msj"] = "El perfil " . $request->nombre . " ya se encuentra registrado.";
        }

        return $resp;
    }

    public function actualizar(Request $request){
        $resp["success"] = false;
        $validar = perfiles::where([
            ['id', '<>', $request->id],
            ['nombre', $request->nombre]
          ])->get();
  
        if ($validar->isEmpty()) {

            $perfil = perfiles::find($request->id);

            if(!empty($perfil)){
                if ($perfil->nombre != $request->nombre || 
                    $perfil->estado != $request->estado
                    ) {

                    $perfil->nombre = $request->nombre;
                    $perfil->estado = $request->estado;
                    
                    if ($perfil->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado el perfil";
            }
        }else{
            $resp["msj"] = "El perfil " . $request->nombre . " ya se encuentra registrado";
        }
        
        return $resp;
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $perfil = perfiles::find($request->id);
        
        if(is_object($perfil)){
            DB::beginTransaction();
            $perfil->estado = $request->estado;
        
            if ($perfil->save()) {
                $resp["success"] = true;
                $resp["msj"] = "El perfil " . $perfil->nombre . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            }else{
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        }else{
            $resp["msj"] = "No se ha encontrado el perfil";
        }
        return $resp; 
    }

    public function show(Request $request){
        $query = perfiles::select('id', 'nombre', 'estado', 'created_at');
        if ($request->estado != '') {
            $query->where("estado", $request->estado);
        }
        return datatables()->eloquent($query)->rawColumns(['nombre'])->make(true);
    }

}
