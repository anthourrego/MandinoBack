<?php

namespace App\Http\Controllers;

use App\Models\toma_control_categorias;
use Illuminate\Http\Request;
use DB;

class TomaControlCategoriasController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\toma_control_categorias  $toma_control_categorias
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $query = toma_control_categorias::select('id', 'nombre', 'estado', 'created_at');
        if ($request->estado != '') {
            $query->where("estado", $request->estado);
        }
        return datatables()->eloquent($query)->rawColumns(['nombre'])->make(true);
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $categoria = toma_control_categorias::find($request->id);
        
        if(is_object($categoria)){
            DB::beginTransaction();
            $categoria->estado = $request->estado;
        
            if ($categoria->save()) {
                $resp["success"] = true;
                $resp["msj"] = "La categoria " . $categoria->nombre . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            }else{
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        }else{
            $resp["msj"] = "No se ha encontrado la categoria";
        }
        return $resp; 
    }

    public function crear(Request $request){
        $resp["success"] = false;
        $validar = toma_control_categorias::where([
            ['nombre', $request->nombre], 
        ])->get();

        if($validar->isEmpty()){
            $categoria = new toma_control_categorias;
            $categoria->nombre = $request->nombre;
            $categoria->estado = $request->estado;
            
            if($categoria->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado la categoria correctamente.";
            }else{
                $resp["msj"] = "No se ha creado la categoria " . $request->nombre;
            }
        }else{
            $resp["msj"] = "La categoria " . $request->nombre . " ya se encuentra registrado.";
        }

        return $resp;
    }

    public function actualizar(Request $request) {
        $resp["success"] = false;
        $validar = toma_control_categorias::where([
            ['id', '<>', $request->id],
            ['nombre', $request->nombre]
          ])->get();
  
        if ($validar->isEmpty()) {

            $categoria = toma_control_categorias::find($request->id);

            if(!empty($categoria)){
                if ($categoria->nombre != $request->nombre || $categoria->estado != $request->estado) {

                    $categoria->nombre = $request->nombre;
                    $categoria->estado = $request->estado;
                    
                    if ($categoria->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado la categoria";
            }
        }else{
            $resp["msj"] = "La categoria " . $request->nombre . " ya se encuentra registrado";
        }
        
        return $resp;
    }

    public function lista(){
        return toma_control_categorias::select("id", "nombre")->where("estado", 1)->get();
    }
}
