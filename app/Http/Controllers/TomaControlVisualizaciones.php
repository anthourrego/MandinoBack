<?php

namespace App\Http\Controllers;

use App\Models\toma_control_visualizaciones;
use Illuminate\Http\Request;
use DB;

class TomaControlVisualizacionesController extends Controller
{

  public function crear(Request $request){
    $resp["success"] = false;
    $validar = toma_control_visualizaciones::where([
        ['fk_user', $request->fk_user], 
        ['fk_toma_control', $request->fk_toma_control]
    ])->get();

    if($validar->isEmpty()){
        $visualizacion = new toma_control_visualizaciones;
        $visualizacion->tiempo = $request->tiempo;
        $visualizacion->fk_user = $request->fk_user;
        $visualizacion->fk_toma_control = $request->fk_toma_control;
        $visualizacion->completo = $request->completo;
        
        if($visualizacion->save()){
            $resp["success"] = true;
            $resp["msj"] = "Visualización guardada correctamente.";
            $resp["id"] = $visualizacion->id;
        }else{
            $resp["msj"] = "No fue posible guardar la visualización.";
        }
    }else{
        $resp["msj"] = "El video ya tiene registro de visualización.";
    }
    return $resp;
  }

  public function actualizar(Request $request) {
    $resp["success"] = false;
    
    $visualizacion = toma_control_visualizaciones::find($request->id);

    if(!empty($visualizacion)){
      
      $visualizacion->tiempo = $request->tiempo;
      $visualizacion->completo = $request->completo;
      
      if ($visualizacion->save()) {
        $resp["success"] = true;
        $resp["msj"] = "Se han actualizado los datos";
      }else{
        $resp["msj"] = "No se han guardado cambios";
      }
    }else{
      $resp["msj"] = "No se ha encontrado la visualización";
    }
    return $resp;
  }

}
