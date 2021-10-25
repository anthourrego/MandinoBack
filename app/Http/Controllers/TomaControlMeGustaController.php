<?php

namespace App\Http\Controllers;

use App\Models\toma_control_me_gusta;
use Illuminate\Http\Request;
use DB;

class TomaControlMeGustaController extends Controller
{

  public function crear(Request $request){
    $resp["success"] = false;

    $megusta = new toma_control_me_gusta;
    $megusta->me_gusta = $request->me_gusta;
    $megusta->fk_user = $request->fk_user;
    $megusta->fk_toma_control = $request->fk_toma_control;
    
    if($megusta->save()){
      $resp["success"] = true;
      $resp["msj"] = "Me gusta guardado correctamente.";
      $resp["id"] = $megusta->id;
    }else{
      $resp["msj"] = "No fue posible guardar el megusta.";
    }
    return $resp;
  }

  public function actualizar(Request $request) {
    $resp["success"] = false;
    $megusta = toma_control_me_gusta::find($request->id);

    if(!empty($megusta)){

      $megusta->me_gusta = $request->me_gusta;

      if ($megusta->save()) {
        $resp["success"] = true;
        $resp["msj"] = "Se han actualizado los datos";
        $resp['id'] = $request->id;
      }else{
        $resp["msj"] = "No se han guardado cambios";
      }
    }else{
        $resp["msj"] = "No se ha encontrado el me gusta";
    }
    
    return $resp;
  }

}
