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

  public function lista($id, $comentario = null){
    $comentarios = toma_control_comentarios::select(
        "toma_control_comentarios.id",
        "toma_control_comentarios.comentario",
        "toma_control_comentarios.estado",
        "toma_control_comentarios.visibilidad",
        "toma_control_comentarios.fk_comentario",
        "u.nombre AS usuario"
      )->addSelect(['contHijos' => DB::table("toma_control_comentarios AS tcc")->selectRaw('count(*)')
        ->whereColumn('tcc.fk_comentario', 'toma_control_comentarios.id')
      ])
      ->join("users AS u", "toma_control_comentarios.fk_user", "u.id")
      ->where("toma_control_comentarios.visibilidad", 1)
      ->where("toma_control_comentarios.fk_toma_control", $id);
    if (is_null($comentario)) {
      $comentarios = $comentarios->whereNull("toma_control_comentarios.fk_comentario");
    } else {
      $comentarios = $comentarios->where("toma_control_comentarios.fk_comentario", $comentario);
    }
    $comentarios = $comentarios->get();
    foreach ($comentarios as $valor) {
      if ($valor->contHijos > 0) {
        $valor->children = $this->lista($id, $valor->id);
      }
    }
    return $comentarios;
}

}
