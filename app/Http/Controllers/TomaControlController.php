<?php

namespace App\Http\Controllers;

use App\Models\toma_control;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use DB;
use DateTime;
use FFMpeg AS FFMpeg2;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Filters\Frame\FrameFilters;

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
                ->selectRaw("'preview.gif' AS gif")
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
            $toma->poster = isset($request->poster) ? "poster." . $request->file('poster')->getClientOriginalExtension() : 'poster.png';
            
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
                        $resp["msj"] = "Error al subir el video.";
                    }

                    if($rutaVideo != 0) {
                        $ffprobe = FFProbe::create();
                        $duracion = (int) $ffprobe->format(storage_path('app/' . $rutaVideo))->get('duration');

                        $timeSkip = rand(1, $duracion - 3);
                        $videoOpen = $request->ruta . "/" . $toma->id . "/video." . $request->file('file')->getClientOriginalExtension();

                        if(isset($request->poster)){
                            try {
                                $rutaPoster = Storage::putFileAs('public/' . $request->ruta . "/" . $toma->id, $request->poster, "poster." . $request->file('poster')->getClientOriginalExtension());
                            } catch (\Exception $e) {
                                $rutaPoster = 0;
                                $resp["msj"] = "Error al subir el poster.";
                            }
                        } else {
                            try {
                                FFMpeg2::fromDisk('public')
                                ->open($videoOpen)
                                ->getFrameFromSeconds($timeSkip)
                                ->addFilter(function (FrameFilters $filters) {
                                    $filters->custom('scale=320:180');
                                })
                                ->export()
                                ->toDisk('public')
                                ->save($request->ruta . "/" . $toma->id . "/poster.png");
                                $rutaPoster = 'poster.png';
                            } catch (\Throwable $th) {
                                $rutaPoster = 0; 
                                $resp["msj"] = "Error al subir el poster predeterminado.";
                            }
                        }

                        try {
                            $gifPath = storage_path("app/public/" . $request->ruta . "/" . $toma->id . "/preview.gif");
                            $ffmpeg = FFMpeg::create();
                            $ffmpegVideo = $ffmpeg->open(storage_path('app/' . $rutaVideo));
                            $ffmpegVideo->gif(TimeCode::fromSeconds($timeSkip), new Dimension(320, 180), 3)->save($gifPath);
                        } catch (\Throwable $th) {
                            $resp["msj"] = "Error al crear la vista previa.";
                            $rutaPoster = 0; 
                            $rutaVideo == 0;
                        }
                    }

                    if ($rutaVideo == 0 || $rutaPoster == 0) {
                        DB::rollback();
                        $delete = Storage::deleteDirectory('public/' . $request->ruta . "/" . $toma->id);
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

    public function devolverStorage($id, $tipo, $filename, $navegador){
        $path = storage_path('app/public/toma-control/'. $id . '/' . $filename);
        if (!File::exists($path)) {
            if($tipo == 1) {
                $path = resource_path('assets/videos/error.mp4');
            } else {
                $path = resource_path('assets/image/nofoto.png');
            }
        }

        $file = File::get($path);
        $size = File::size($path);
        $type = File::mimeType($path);

        $codigo = 206;
        if ($navegador == 'firefox') $codigo = 200;

        $response = Response::make($file, $codigo);
        $response->header("Content-Type", $type); 
        $response->header("Content-Range", "bytes 0-" . ($size - 1) . "/" . $size); 

        return $response;
    }

    public function deleteFile(Request $request){
    
        $uploaded = Storage::delete('public/toma-control/' . $request->ruta);

        $resp["success"] = $uploaded;
        $resp["msj"] = ($uploaded ? 'Eliminado correctamente' : 'No fue posible eliminar el archivo');

        return $resp;
    }

    public function videoVisualizar($video, $usuario) {

        $me_gusta = DB::table('toma_control_me_gustas')
            ->select('me_gusta', 'id', 'fk_toma_control')
            ->where('fk_user', $usuario);

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
                ,'tcmg.id AS idMeGusta'
                ,'tcmg.me_gusta AS meGusta'
            )->leftJoin("toma_control_visualizaciones AS tcv", "toma_controls.id", "tcv.fk_toma_control")
            ->leftJoinSub($me_gusta, "tcmg", function ($join) {
                $join->on("toma_controls.id", "=", "tcmg.fk_toma_control");
            })->where("toma_controls.id", $video)
            ->first();

        if ($query->comentarios == 1) {
            $comentarios = new TomaControlComentariosController();
            $query->listaComentarios = $comentarios->lista($video);
        }

        return $query;
    }

    public function videosSugeridos(Request $request) {
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
            ->where("toma_controls.visibilidad", 1)
            ->where("toma_controls.estado", 1)
            ->where("toma_controls.id", "<>", $request->idActual)->get();

        foreach ($query as $ite) {
            $date1 = new DateTime();
            $date2 = new DateTime($ite->created_at);
            $diff = $date1->diff($date2);

            $ite->fecha = $this->formatoFecha($diff);
            $ite->gif = "preview.gif";
        }
        
        return $query;
    }

    public function videos(Request $request){
        
        $query = DB::table("toma_control_u_categorias AS TCUC")
                    ->select(
                        "TC.id"
                        ,"TC.nombre"
                        ,"TC.descripcion"
                        ,"TC.visibilidad"
                        ,"TC.comentarios"
                        ,"TC.estado"
                        ,"TC.created_at"
                        ,"TC.ruta"
                        ,"TC.poster"
                        ,"TCV.tiempo"
                        ,"TCV.completo"
                        ,"TCV.id AS idVisualizacion"
                    )->selectRaw("COUNT(TCV.fk_toma_control) AS Vistas")
                    ->leftJoin("toma_controls AS TC", "TCUC.fk_toma_control", "TC.id")
                    ->leftJoin("toma_control_visualizaciones AS TCV", "TCUC.fk_toma_control", "TCV.fk_toma_control")
                    ->where("TC.estado", 1)
                    ->where("TC.visibilidad", 1);

        if(isset($request->buscar)) {
            $query = $query->where("TC.nombre", 'like', '%' . $request->buscar . '%');
        }

        if(isset($request->categorias)){
            if(count($request->categorias) == 0){
                $userController = new UserController();
                $categoriasUser = $userController->categorias($request->usuariosId, $request->PerfilUsuario);
                $categorias = [];
                foreach ($categoriasUser as $ite) {
                    $categorias[] = $ite->id;
                }
                $query = $query->whereIn('TCUC.fk_categoria', $categorias);
            } else {
                $query = $query->whereIn('TCUC.fk_categoria', $request->categorias);
            }
        }
                    
        $query = $query->groupBy("TCUC.fk_toma_control")
            ->orderBy('TC.created_at', 'DESC')
            ->offset($request->inicio)->limit($request->cantidad)
            ->get();

        foreach ($query as $ite) {
            $date1 = new DateTime();
            $date2 = new DateTime($ite->created_at);
            $diff = $date1->diff($date2);

            $ite->fecha = $this->formatoFecha($diff);
            $ite->gif = "preview.gif";
        }

        return $query; 
    }

    function formatoFecha($df) {

        $str = '';
        //$str .= ($df->invert == 1) ? ' - ' : '';

        if ($df->y > 0) {
            // years
            $str .= ($df->y > 1) ? $df->y . ' años' : $df->y . ' año';
        } else if ($df->m > 0) {
            // month
            $str .= ($df->m > 1) ? $df->m . ' meses' : $df->m . ' mes';
        } else if ($df->d > 0) {
            // days
            $str .= ($df->d > 1) ? $df->d . ' días' : $df->d . ' día';
        } else if ($df->h > 0) {
            // hours
            $str .= ($df->h > 1) ? $df->h . ' horas' : $df->h . ' hora';
        } else if ($df->i > 0) {
            // minutes
            $str .= ($df->i > 1) ? $df->i . ' minutos' : $df->i . ' minuto';
        } else if ($df->s > 0) {
            // seconds
            $str .= ($df->s > 1) ? $df->s . ' segundos' : $df->s . ' segundo';
        }
    
        return $str;
    }
}
