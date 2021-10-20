<?php

namespace App\Http\Controllers;

use App\Models\toma_control;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
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
        $query = toma_control::select(
                    'toma_controls.id'
                    ,'toma_controls.nombre'
                    ,'toma_controls.descripcion'
                    ,'toma_controls.visibilidad'
                    ,'toma_controls.comentarios'
                    ,'toma_controls.estado'
                    ,'toma_controls.created_at'
                    ,'toma_controls.ruta'
                    ,'toma_controls.poster'
                )->selectRaw("GROUP_CONCAT(tcuc.fk_categoria) AS categorias")
                ->join("toma_control_u_categorias AS tcuc", "toma_controls.id", "tcuc.fk_toma_control");
        if ($request->estado != '') {
            $query = $query->where("toma_controls.estado", $request->estado);
        }
        $query->groupBy('toma_controls.id');
        
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
        $datos = json_decode($request->datos);
        $validar = toma_control::where([
            ['nombre', $datos->nombre], 
        ])->get();

        if($validar->isEmpty()){
            
            DB::beginTransaction();

            $toma = new toma_control;
            $toma->nombre = $datos->nombre;
            $toma->visibilidad = $datos->visibilidad;
            $toma->comentarios = $datos->comentarios;
            $toma->descripcion = $datos->descripcion;
            $toma->estado = $datos->estado;
            $toma->ruta = "video." . $request->file('file')->getClientOriginalExtension();
            $toma->poster = isset($request->poster) ? "poster." . $request->file('poster')->getClientOriginalExtension() : NULL;
            
            if($toma->save()){
                $cont = 0;
                foreach ($datos->categorias as $value) {
                    try {
                        DB::table('toma_control_u_categorias')->insert([
                            "fk_toma_control" => $toma->id
                            ,"fk_categoria" => $value
                            ,"created_at" => date("Y-m-d H:i:s")
                            ,"updated_at" => date("Y-m-d H:i:s")
                        ]);
                    } catch (\Exception $e) {
                        $cont++;
                        break;
                    }
                }

                if ($cont > 0) {
                    DB::rollback();
                    $resp["msj"] = "No fue posible guardar a " . $datos->nombre;
                } else {

                    $rutaVideo = 0;
                    $rutaPoster = 0;
                    try {
                        $rutaVideo = Storage::putFileAs('public/' . $request->ruta . "/" . $toma->id, $request->file, "video." . $request->file('file')->getClientOriginalExtension());
                    } catch (\Exception $e) {
                        $rutaVideo = 0;
                    }

                    if(isset($request->poster)){
                        try {
                            $rutaPoster = Storage::putFileAs('public/' . $request->ruta . "/" . $toma->id, $request->poster, "poster." . $request->file('poster')->getClientOriginalExtension());
                        } catch (\Exception $e) {
                            $rutaPoster = 0;
                        }
                    } else {
                        $rutaPoster = 1; 
                    }

                    if ($rutaVideo == 0 && $rutaPoster == 0) {
                        DB::rollback();
                        $resp["msj"] = "Error al subir el video.";
                    } else {
                        DB::commit();
                        $resp["success"] = true;
                        $resp["msj"] = $datos->nombre . " se ha creado correctamente.";
                    }
                }
            }else{
                $resp["msj"] = "No se ha creado a " . $datos->nombre;
            }
        }else{
            $resp["msj"] = $datos->nombre . " ya se encuentra registrado.";
        }

        return $resp;
    }

    public function actualizar(Request $request) {
        $resp["success"] = false;
        $datos = json_decode($request->datos);
        $validar = toma_control::where([
            ['id', '<>', $datos->id],
            ['nombre', $datos->nombre]
          ])->get();
  
        if ($validar->isEmpty()) {

            $toma = toma_control::find($datos->id);

            if(!empty($toma)){
                if ($toma->nombre != $datos->nombre || $toma->descripcion != $datos->descripcion || $toma->visibilidad != $datos->visibilidad || $toma->comentarios != $datos->comentarios || $toma->estado != $datos->estado) {

                    $toma->nombre = $datos->nombre;
                    $toma->descripcion = $datos->descripcion;
                    $toma->visibilidad = $datos->visibilidad;
                    $toma->comentarios = $datos->comentarios;
                    $toma->estado = $datos->estado;
                    
                    if ($toma->save()) {
                        DB::table('toma_control_u_categorias')->where("fk_toma_control", $toma->id)->delete(); 
                        $cont = 0;
                        foreach ($datos->categorias as $value) {
                            try {
                                DB::table('toma_control_u_categorias')->insert([
                                    "fk_toma_control" => $toma->id
                                    ,"fk_categoria" => $value
                                ]);
                            } catch (\Exception $e) {
                                $cont++;
                                break;
                            }
                        }

                        if ($cont > 0) {
                            DB::rollback();
                            $resp["msj"] = "No fue posible guardar a " . $datos->nombre;
                        } else {

                            $rutaVideo = 0;
                            $rutaPoster = 0;
                            if ($request->file && $datos->cambioVideo) {
                                try {
                                    $rutaVideo = Storage::putFileAs('public/' . $request->ruta . "/" . $toma->id, $request->file, "video." . $request->file('file')->getClientOriginalExtension());
                                } catch (\Exception $e) {
                                    $rutaVideo = 0;
                                }
                            }

                            if(isset($request->poster) && $datos->cambioPoster){
                                try {
                                    $rutaPoster = Storage::putFileAs('public/' . $request->ruta . "/" . $toma->id, $request->poster, "poster." . $request->file('poster')->getClientOriginalExtension());
                                } catch (\Exception $e) {
                                    $rutaPoster = 0;
                                }
                            } else {
                                $rutaPoster = 1; 
                            }

                            if ($rutaVideo == 0 && $rutaPoster == 0) {
                                DB::rollback();
                                $resp["msj"] = "Error al subir el video.";
                            } else {
                                DB::commit();
                                $resp["success"] = true;
                                $resp["msj"] = $datos->nombre . " se ha modificado correctamente.";
                            }
                        }
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado a " . $datos->nombre;
            }
        }else{
            $resp["msj"] = $datos->nombre . " ya se encuentra registrado";
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

    public function devolverStorage($id, $tipo, $filename){
        $path = storage_path('app/public/toma-control/'. $id . '/' . $filename);
        if (!File::exists($path)) {
            if($tipo == 1) {
                $path = resource_path('assets/videos/error.mp4');
            } else {
                $path = resource_path('assets/image/nofoto.png');
            }
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type); 

        return $response;
    }

    public function deleteFile(Request $request){
    
        $uploaded = Storage::delete('public/toma-control/' . $request->ruta);

        $resp["success"] = $uploaded;
        $resp["msj"] = ($uploaded ? 'Eliminado correctamente' : 'No fue posible eliminar el archivo');

        return $resp;
    }

    public function videoVisualizar($id) {
        $query = toma_control::select(
                'toma_controls.nombre'
                ,'toma_controls.descripcion'
                ,'toma_controls.visibilidad'
                ,'toma_controls.comentarios'
                ,'toma_controls.estado'
                ,'toma_controls.created_at'
                ,'toma_controls.ruta'
                ,'toma_controls.poster'
                ,'tcv.tiempo'
                ,'tcv.completo'
                ,'tcv.id AS idVisualizacion'
            )->leftJoin("toma_control_visualizaciones AS tcv", "toma_controls.id", "tcv.fk_toma_control")
            ->where("toma_controls.id", $id);
        return $query->first();
    }

    public function videosSugeridos($id) {
        $query = toma_control::select(
                'toma_controls.id'
                ,'toma_controls.nombre'
                ,'toma_controls.descripcion'
                ,'toma_controls.visibilidad'
                ,'toma_controls.comentarios'
                ,'toma_controls.estado'
                ,'toma_controls.created_at'
                ,'toma_controls.ruta'
                ,'toma_controls.poster'
            )
            ->where("toma_controls.estado", 1)
            ->where("toma_controls.id", "<>", $id);
        return $query->get();
    }

    public function videos(Request $request){
        $query = DB::table("toma_control_u_categorias AS TCUC")
                    ->select(
                        "TC.id"
                        ,"TC.nombre"
                        ,"TC.descripcion"
                        ,"TC.poster"
                        ,"TC.ruta"
                        ,"TC.created_at"
                    )->selectRaw("COUNT(TCV.fk_toma_control) AS Vistas")
                    ->leftJoin("toma_controls AS TC", "TCUC.fk_toma_control", "TC.id")
                    ->leftJoin("toma_control_visualizaciones AS TCV", "TCUC.fk_toma_control", "TCV.fk_toma_control")
                    ->where("TC.estado", 1)
                    ->where("TC.visibilidad", 1)
                    ->groupBy("TCUC.fk_toma_control")
                    ->get();

        return $query; 
    }
}
