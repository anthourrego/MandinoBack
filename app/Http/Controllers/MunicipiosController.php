<?php

namespace App\Http\Controllers;

use App\Models\municipios;
use Illuminate\Http\Request;
use DB;

class MunicipiosController extends Controller {
    
    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $ciudad = municipios::find($request->id);
        
        if(is_object($ciudad)){
            DB::beginTransaction();
            $ciudad->flag = $request->estado;
        
            if ($ciudad->save()) {
                $resp["success"] = true;
                $resp["msj"] = "La ciudad " . $ciudad->name . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            }else{
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        }else{
            $resp["msj"] = "No se ha encontrado la ciudad";
        }
        return $resp; 
    }

    public function crear(Request $request){
        $resp["success"] = false;
        $validar = municipios::where([
            ['name', $request->name], 
            ["state_id", $request->state_id]
        ])->get();

        if($validar->isEmpty()){
            $ciudad = new municipios;
            $ciudad->name = $request->name;
            $ciudad->state_id = $request->state_id;
            $ciudad->flag = $request->estado;
            
            if($ciudad->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado la ciudad correctamente.";
            }else{
                $resp["msj"] = "No se ha creado la ciudad " . $request->name;
            }
        }else{
            $resp["msj"] = "La ciudad " . $request->name . " ya se encuentra registrado.";
        }

        return $resp;
    }

    public function update(Request $request) {
        $resp["success"] = false;
        $validar = municipios::where([
            ['id', '<>', $request->id],
            ['state_id', $request->state_id],
            ['name', $request->name]
          ])->get();
  
        if ($validar->isEmpty()) {

            $muni = municipios::find($request->id);

            if(!empty($muni)){
                if ($muni->name != $request->name || $muni->state_id != $request->state_id || $muni->flag != $request->flag) {

                    $muni->name = $request->name;
                    $muni->state_id = $request->state_id;
                    $muni->flag = $request->flag;
                    
                    if ($muni->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado la ciudad";
            }
        }else{
            $resp["msj"] = "La ciudad " . $request->name . " ya se encuentra registrado";
        }
        
        return $resp;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\municipios  $municipios
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request){
        $query = DB::table("municipios AS m")
            ->select("m.id"
                ,"m.name"
                ,"d.id AS id_departamento"
                ,"d.name AS nombre_departamento"
                ,"p.id AS id_pais"
                ,"p.name AS nombre_pais"
                ,"m.flag"
                ,"m.created_at"
            )->join("departamentos AS d", function ($join) {
                $join->on('m.state_id', 'd.id')->where('d.flag', 1);
            })
            ->join("paises AS p", function ($join) {
                $join->on('d.country_id', 'p.id')->where('d.flag', 1);
            })->where("p.flag", 1);
        
        if(isset($request->paises)) {
            $query->whereIn("p.id", $request->paises);
        }

        if(isset($request->departamentos)) {
            $query->whereIn("d.id", $request->departamentos);
        }

        if ($request->estado != '') {
            $query->where("m.flag", $request->estado);
        }

        $query->orderBy('m.name', 'asc');

        return datatables()->query($query)->rawColumns(['m.name', 'nombre_departamento', 'nombre_pais'])->make(true);
    }

    public function lista(Request $request){
        $query = DB::table("municipios AS m")
                ->select("m.id", "m.name")
                ->join('departamentos AS d', 'm.state_id', '=', 'd.id')
                ->join('paises AS p', 'd.country_id', '=', 'p.id');

        if(isset($request->departamentos)) {
            $query = $query->whereIn("d.id", $request->departamentos);
        }

        $query = $query->where([
            ["p.flag", 1]
            ,["d.flag", 1]
            ,["m.flag", 1]
        ])->orderBy('m.name', 'asc')->get();

        //El pro
        return $query;
    }

        /**
     * lista los departamentos de un país especifico
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /* public function lista(Request $request){
        return municipios::select('id', 'name')->where("country_id", $request->idPais)->where("state_id", $request->idDepto)->get();
    } */


    /**
     * regresa ids de pais y departamento
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function ubicacion($idMunicipio){

        // SELECT p.id as pais, d.id as departamento FROM municipios m JOIN departamentos d ON m.state_id = d.id JOIN paises p ON d.country_id = p.id WHERE m.id = 21195


        $query = municipios::select("paises.id as pais", "departamentos.id as depto");
        $query->join('departamentos', 'municipios.state_id', '=', 'departamentos.id');
        $query->join('paises', 'departamentos.country_id', '=', 'paises.id');
        $query->where('municipios.id',$idMunicipio);

        return $query->get();
    }

}
