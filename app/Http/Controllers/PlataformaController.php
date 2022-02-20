<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class PlataformaController extends Controller{
    
    public function datosJSON(){

        $path = resource_path('assets/plataforma/images.json');

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

    public function actualizar(){
        return "funca";
    }

}
