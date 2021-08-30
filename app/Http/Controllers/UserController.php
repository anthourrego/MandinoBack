<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {
    //
    public function inicioSesion($nroDoc, $pass){
        $resp["success"]= false;
        $usuario = User::where(array(
            'nro_documento' => $nroDoc
        ))->first();

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
