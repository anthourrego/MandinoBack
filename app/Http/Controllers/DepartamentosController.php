<?php

namespace App\Http\Controllers;

use App\Models\departamentos;
use Illuminate\Http\Request;
use DB;

class DepartamentosController extends Controller {

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\departamentos  $departamentos
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, departamentos $departamentos) {
        $resp["success"] = false;
        $validar = departamentos::where([
            ['id', '<>', $request->id],
            ['country_id', $request->country_id],
            ['name', $request->name]
          ])->get();
  
        if ($validar->isEmpty()) {

            $departamento = departamentos::find($request->id);

            if(!empty($departamento)){
                if ($departamento->name != $request->name || $departamento->country_id != $request->country_id || $departamento->state_code != $request->state_code || $departamento->flag != $request->flag) {

                    $departamento->name = $request->name;
                    $departamento->country_id = $request->country_id;
                    $departamento->state_code = $request->state_code;
                    $departamento->flag = $request->flag;
                    
                    if ($departamento->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado el departamento";
            }
        }else{
            $resp["msj"] = "El departamento " . $request->name . " ya se encuentra registrado";
        }
        
        return $resp;
    }

    public function show(Request $request){
        $query = departamentos::select(
                "departamentos.id"
                ,"departamentos.name"
                ,"departamentos.state_code"
                ,"paises.id AS country_id"
                ,"paises.name AS nombre_pais"
                ,"departamentos.flag"
                ,"departamentos.created_at"
            );
        $query->join('paises', 'departamentos.country_id', '=', 'paises.id');
        if(isset($request->paises)) {
            $query->whereIn("departamentos.country_id", $request->paises);
        }

        if ($request->estado != '') {
            $query->where("departamentos.flag", $request->estado);
        }

        return datatables()->eloquent($query)->rawColumns(['departamentos.name', 'nombre_pais'])->make(true);
    }

    public function crear(Request $request){
        $resp["success"] = false;
        $validar = departamentos::where([
            ['name', $request->name], 
            ["country_id", $request->country_id]
        ])->get();

        if($validar->isEmpty()){
            $departamento = new departamentos;
            $departamento->name = $request->name;
            $departamento->country_id = $request->country_id;
            $departamento->state_code = $request->state_code;
            $departamento->flag = $request->flag;
            
            if($departamento->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado el departamento correctamente.";
            }else{
                $resp["msj"] = "No se ha creado el el departamento " . $request->name;
            }
        }else{
            $resp["msj"] = "El departamento " . $request->name . " ya se encuentra registrado.";
        }

        return $resp;
    }


    /**
     * lista los departamentos de un país especifico
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /* public function lista($pais){
        return departamentos::select('id', 'name')
            ->where([
                ["country_id", $pais]
            ])->orderBy('name', 'asc')->get();
    } */

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $dep = departamentos::find($request->id);
        
        if(is_object($dep)){
            DB::beginTransaction();
            $dep->flag = $request->estado;
        
            if ($dep->save()) {
                $resp["success"] = true;
                $resp["msj"] = "El departamento " . $dep->name . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            }else{
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        }else{
            $resp["msj"] = "No se ha encontrado el departamento";
        }
        return $resp; 
    }

    public function lista(Request $request){
        $query = departamentos::select('departamentos.id', 'departamentos.name')
            ->join("paises AS p", function ($join) {
                $join->on('departamentos.country_id', 'p.id')->where('p.flag', 1);
            })
            ->where("departamentos.flag", 1);
        
        if (isset($request->pais)) {
            $query = $query->whereIn("departamentos.country_id", $request->pais);
        }

        return  $query->orderBy('departamentos.name', 'asc')->get();
    }

    
}