<?php

namespace App\Http\Controllers;

use App\Models\toma_control_comentarios;
use Illuminate\Http\Request;
use DB;

class TomaControlComentariosController extends Controller
{

  public function crear(Request $request){
    $resp["success"] = false;

    $comentario = new toma_control_comentarios;
    $comentario->comentario = $request->comentario;
    $comentario->fk_user = $request->fk_user;
    $comentario->fk_toma_control = $request->fk_toma_control;
    $comentario->fk_comentario = $request->fk_comentario;
    $comentario->estado = 1;
    $comentario->visibilidad = 1;
    
    if($comentario->save()){
        $resp["success"] = true;
        $resp["msj"] = "Comentario guardado correctamente.";
        $resp["id"] = $comentario->id;
    }else{
        $resp["msj"] = "No fue posible guardar el comentario.";
    }
    return $resp;
  }

}
