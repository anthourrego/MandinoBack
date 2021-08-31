<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use DB;

class UserController extends Controller {
    //
    public function inicioSesion($nroDoc, $pass){
        $resp["success"]= false;
        //Validaos por numero de documento, email y usuario
        $usuario = User::where(function($query) use ($nroDoc) {
            $query->orWhere('nro_documento', $nroDoc)
                ->orWhere('email', $nroDoc)
                ->orWhere('usuario', $nroDoc);
        })->first();

        if (is_object($usuario)){
            if(Hash::check($pass, $usuario->password)){
                $resp['success'] = true;
                $resp['msj'] = $usuario;
            }else{
                $resp["msj"] = 'Contraseña incorrecta';
            }
        }else {
            $resp["msj"] = 'Usuario no existe';
        }

        return $resp;
    } 
}
