<?php

namespace App\Http\Controllers;

use App\Models\departamentos;
use Illuminate\Http\Request;

class DepartamentosController extends Controller
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
    public function create()
    {
        //
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
     * 
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\departamentos  $departamentos
     * @return \Illuminate\Http\Response
     */
    public function edit(departamentos $departamentos)
    {
        //
    }

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

            if(!empty($pais)){
                if ($departamento->name != $request->name || $departamento->country_id != $request->country_id || $departamento->state_code != $request->state_code || $departamento->flag != $request->flag) {

                    $departamento->name = $request->name;
                    $departamento->country_id = $request->country_id;
                    $departamento->state_code = $request->state_code;
                    $departamento->flag = $request->flag;
                    
                    if ($pais->save()) {
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\departamentos  $departamentos
     * @return \Illuminate\Http\Response
     */
    public function destroy(departamentos $departamentos)
    {
        //
    }

    public function show(Request $request, departamentos $paises){
        // var_dump(count($request->paises));
        $query = departamentos::select("*");

        if(isset($request->paises)) {
            $query->whereIn("country_id", $request->paises);
        }

        if ($request->estado != '') {
            $query->where("flag", $request->estado);
        }

        return datatables()->eloquent($query)->toJson();
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
            
            if($pais->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado el pais correctamente.";
            }else{
                $resp["msj"] = "No se ha creado el el departamento " . $request->name;
            }
        }else{
            $resp["msj"] = "El departamento " . $request->name . " ya se encuentra registrado.";
        }

        return $resp;
    }

}