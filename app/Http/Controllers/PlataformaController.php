<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PlataformaController extends Controller{
    
    private $url  = 'assets/plataforma/images.json';

    public function datosJSON(){

        $path = resource_path($this->url);

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type); 

        return $response;
    }

    public function devolverImg($img){

        $path = storage_path('app/public/plataforma/' . $img);
        
        if (!File::exists($path)) {
            $path = resource_path('assets/image/user.png');
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type); 

        return $response;
    }

    public function actualizar(Request $request) {
        $resp["success"] = 1;
        try {

            $path = resource_path($this->url);
            $file = json_decode(File::get($path));
            $enc = array_search($request->id, array_column($file, "name"));
            
            $directorio = 'public/plataforma/';

            $file[$enc]->dark = ($request->dark == "true" ? true : false);
            if ($file[$enc]->dark == true) {
                $file[$enc]->colorFondo = false;
            } else {
                $file[$enc]->colorFondo = ($request->colorFondo == "false" || $request->colorFondo == "null" ? false : $request->colorFondo);
            }
            
            if ($request->file == 'null') {
                if (Storage::disk('public')->exists('plataforma/' . $file[$enc]->url)) {
                    Storage::delete('public/plataforma/' . $file[$enc]->url);
                }
                $file[$enc]->url = null;
            } else {
                $ruta = storage_path('app/public/plataforma/' . $request->nombreArchivo);
                $file[$enc]->url = $request->nombreArchivo;
                Storage::putFileAs('public/plataforma/', $request->file, $request->nombreArchivo);
            }
            File::replace($path, json_encode($file));
            $resp["msj"] = "Modificado correctamente.";
        } catch (\Exception $e) {
            $resp["success"] = 0;
            $resp["msj"] = "Error al modificar imagen.";
        }
        return $resp;
    }

}
