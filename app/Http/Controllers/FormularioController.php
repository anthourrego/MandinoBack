<?php

namespace App\Http\Controllers;

use App\Models\formulario;
use Illuminate\Http\Request;
use DB;

class FormularioController extends Controller
{
    public function show(Request $request){
        $query = formulario::select(
            "formularios.id"
            ,"formularios.nombre"
            ,"formularios.idPais"
            ,"formularios.idPerfil"
            ,"formularios.estado"
            ,"formularios.created_at"
            ,"paises.name AS Nombre_Pais"
            ,"perfiles.nombre AS Nombre_Perfil"
        );
        $query->join('paises', 'formularios.idPais', '=', 'paises.id');
        $query->join('perfiles', 'formularios.idPerfil', '=', 'perfiles.id');
        if ($request->estado != '') {
            $query->where("formularios.estado", $request->estado);
        }
        return datatables()->eloquent($query)->rawColumns(['Nombre_Pais', 'Nombre_Perfil', 'nombre'])->make(true);
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $form = formulario::find($request->id);
        
        if(is_object($form)){
            DB::beginTransaction();
            $form->estado = $request->estado;
        
            if ($form->save()) {
                $resp["success"] = true;
                $resp["msj"] = "El formulario " . $form->nombre . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            }else{
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        }else{
            $resp["msj"] = "No se ha encontrado el formulario";
        }
        return $resp; 
    }

    public function crear(Request $request){
        $resp["success"] = false;
        $validar = formulario::where([
            ['nombre', $request->nombre], 
        ])->get();

        if($validar->isEmpty()){
            $form = new formulario;
            $form->nombre = $request->nombre;
            $form->idPais = $request->idPais;
            $form->idPerfil = $request->idPerfil;
            $form->estado = $request->estado;
            
            if($form->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado el formulario correctamente.";
            }else{
                $resp["msj"] = "No se ha creado el formulario " . $request->nombre;
            }
        }else{
            $resp["msj"] = "El formulario " . $request->nombre . " ya se encuentra registrado.";
        }

        return $resp;
    }

    public function actualizar(Request $request) {
        $resp["success"] = false;
        $validar = formulario::where([
            ['id', '<>', $request->id],
            ['nombre', $request->nombre]
          ])->get();
  
        if ($validar->isEmpty()) {

            $form = formulario::find($request->id);

            if(!empty($form)){
                if ($form->nombre != $request->nombre || $form->idPais != $request->idPais || $form->idPerfil != $request->idPerfil || $form->estado != $request->estado) {

                    $form->nombre = $request->nombre;
                    $form->idPais = $request->idPais;
                    $form->idPerfil = $request->idPerfil;
                    $form->estado = $request->estado;
                    
                    if ($form->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado el formulario";
            }
        }else{
            $resp["msj"] = "El formulario " . $request->nombre . " ya se encuentra registrado";
        }
        
        return $resp;
    }

    public function traerFormulario($id){
        return formulario::select("nombre", "idPais", "idPerfil")->where("id", $id)->where("estado", 1)->first();
    }
}
