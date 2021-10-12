<?php

namespace App\Http\Controllers;

use App\Models\toma_control;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DB;

class TomaControlController extends Controller
{
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\toma_control  $toma_control
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $query = toma_control::select('id', 'nombre', 'descripcion', 'visibilidad', 'comentarios', 'estado', 'created_at');
        if ($request->estado != '') {
            $query->where("estado", $request->estado);
        }
        return datatables()->eloquent($query)->rawColumns(['nombre', 'descripcion'])->make(true);
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $toma = toma_control::find($request->id);
        
        if(is_object($toma)){
            DB::beginTransaction();
            $toma->estado = $request->estado;
        
            if ($toma->save()) {
                $resp["success"] = true;
                $resp["msj"] = $toma->nombre . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            }else{
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        }else{
            $resp["msj"] = "No se ha encontrado la " . $toma->nombre;
        }
        return $resp; 
    }

    public function crear(Request $request){
        $resp["success"] = false;
        $validar = toma_control::where([
            ['nombre', $request->nombre], 
        ])->get();

        if($validar->isEmpty()){
            $toma = new toma_control;
            $toma->nombre = $request->nombre;
            $toma->visibilidad = $request->visibilidad;
            $toma->comentarios = $request->comentarios;
            $toma->estado = $request->estado;
            
            if($toma->save()){
                $cont = 0;
                foreach ($request->categorias as $value) {
                    try {
                        DB::table('toma_control_u_categorias')->insert([
                            "fk_toma_control" => $toma->id
                            ,"fk_categoria" => $value
                        ]);
                    } catch (\Exception $e) {
                        DB::rollback();
                        $cont++;
                        break;
                    }
                }

                if ($cont > 0) {
                    $resp["msj"] = "No fue posible guardar a " . $request->nombre;
                } else {
                    $resp["success"] = true;
                    $resp["msj"] = $request->nombre . " se ha creado correctamente.";
                    $resp["insertado"] = $toma->id;
                }

            }else{
                $resp["msj"] = "No se ha creado a " . $request->nombre;
            }
        }else{
            $resp["msj"] = $request->nombre . " ya se encuentra registrado.";
        }

        return $resp;
    }

    public function actualizar(Request $request) {
        $resp["success"] = false;
        $validar = toma_control::where([
            ['id', '<>', $request->id],
            ['nombre', $request->nombre]
          ])->get();
  
        if ($validar->isEmpty()) {

            $toma = toma_control::find($request->id);

            if(!empty($toma)){
                if ($toma->nombre != $request->nombre || $toma->estado != $request->estado) {

                    $toma->nombre = $request->nombre;
                    $toma->visibilidad = $request->visibilidad;
                    $toma->comentarios = $request->comentarios;
                    $toma->estado = $request->estado;
                    
                    if ($toma->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado a " . $request->nombre;
            }
        }else{
            $resp["msj"] = $request->nombre . " ya se encuentra registrado";
        }
        
        return $resp;
    }

    public function lista(){
        return toma_control::select('id', 'nombre', 'descripcion')->where("estado", 1)->get();
    }

    public function upload(Request $request){
    
        $uploaded = Storage::putFileAs('public/' . $request->ruta, $request->file, $request->nombre);

        $resp["success"] = true;
        $resp["ruta"] = $uploaded;

        return $resp;
    }
}
