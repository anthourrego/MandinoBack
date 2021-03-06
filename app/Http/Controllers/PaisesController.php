<?php

namespace App\Http\Controllers;

use App\Models\Paises;
use Illuminate\Http\Request;
use DB;

class PaisesController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Paises  $paises
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Paises $paises){

        if ($request->estado != '') {
            $query = $paises::where("flag", $request->estado);
        } else {
            $query = $paises::query();
        }
        return datatables()->eloquent($query)->rawColumns(['name'])->make(true);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Paises  $paises
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Paises $paises){   
        $resp["success"] = false;
        $validar = Paises::where([
            ['id', '<>', $request->id],
            ['name', $request->name]
          ])->get();
  
        if ($validar->isEmpty()) {

            $pais = Paises::find($request->id);

            if(!empty($pais)){
                if ($request->name !=  $pais->name || $request->numeric_code !=  $pais->numeric_code || $request->phone_code !=  $pais->phone_code || $request->flag !=  $pais->flag) {
                    
                    $pais->name = $request->name;
                    $pais->numeric_code = $request->numeric_code;
                    $pais->phone_code = $request->phone_code;
                    $pais->flag = $request->flag;
                    
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
                $resp["msj"] = "No se ha encontrado el país";
            }
        }else{
            $resp["msj"] = "El país " . $request->name . " ya se encuentra registrado";
        }
        
        return $resp;
    }

    public function crear(Request $request){
        $resp["success"] = false;
        $validar = Paises::where('name', $request->name)->get();

        if($validar->isEmpty()){
            $pais = new Paises;
            $pais->name = $request->name;
            $pais->numeric_code = $request->numeric_code;
            $pais->phone_code = $request->phone_code;
            $pais->flag = $request->flag;
            
            if($pais->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado el pais correctamente.";
            }else{
                $resp["msj"] = "No se ha creado el el pais " . $request->name;
            }
        }else{
            $resp["msj"] = "El pais " . $request->name . " ya se encuentra registrado.";
        }

        return $resp;
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $pais = Paises::find($request->id);
        
        if(is_object($pais)){
            DB::beginTransaction();
            $pais->flag = $request->estado;
        
            if ($pais->save()) {
                $resp["success"] = true;
                $resp["msj"] = "El país " . $pais->name . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            }else{
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        }else{
            $resp["msj"] = "No se ha encontrado el pais";
        }
        return $resp; 
    }

    public function lista(){
        return Paises::select('id', 'name')->where("flag", 1)->get();
    }
}
