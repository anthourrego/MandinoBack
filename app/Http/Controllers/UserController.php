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

    public function crear(Request $request){
        $resp["success"] = false;
        $validar = User::where('nro_documento', $request->documento)->get();
        
        if($validar->isEmpty()){
            $validar = User::where('usuario', $request->usuario)->get();
            if($validar->isEmpty()){
                $validar = User::where('email', $request->email)->get();
                if($validar->isEmpty()){

                    $usuario = new User;
                    $usuario->nro_documento = $request->nro_documento;
                    $usuario->usuario = $request->usuario;
                    $usuario->password = Hash::make($request->documento, ['rounds' => 15]);
                    $usuario->nombre1 = $request->nombre1;
                    $usuario->nombre2 = $request->nombre2;
                    $usuario->apellido1 = $request->apellido1;
                    $usuario->apellido2 = $request->apellido2;
                    $usuario->email = $request->email;
                    $usuario->telefono = $request->telefono;
                    $usuario->estado = $request->estado;
                    $usuario->fk_municipio = $request->fk_municipio;

                    /*Queda pendiente la validacion de la foto*/
                    
                    if($usuario->save()){
                        $resp["success"] = true;
                        $resp["msj"] = "Se ha creado el usuario";
                    }else{
                        $resp["msj"] = "No se ha creado el usuario";
                    }
                } else {
                    $resp["msj"] = "El correo " . $request->email . " ya se encuentra registrado";
                }
            } else {
                $resp["msj"] = "El usuario " . $request->usuario . " ya se encuentra registrado";
            }
        }else{
            $resp["msj"] = "El número de documento " . $request->documento . " ya se encuentra registrado";
        }
    
        return $resp;
    }

    public function obtener(Request $request){
        $query = User::select(
            "users.id"
            ,"users.nro_documento"
            ,"users.usuario"
            ,"users.password"
            ,"users.nombre1"
            ,"users.nombre2"
            ,"users.apellido1"
            ,"users.apellido2"
            ,"users.foto"
            ,"users.email"
            ,"users.telefono"
            ,"users.estado"
            ,"users.fk_municipio"
            ,"m.name AS ciudad_nombre"
            ,"d.id AS dep_id"
            ,"d.name AS dep_nombre"
            ,"p.id AS pais_id"
            ,"p.name AS pais_nombre"
        )->join("municipios AS m", "users.fk_municipio", "m.id")
        ->join("departamentos AS d", "m.state_id", "d.id")
        ->join("paises AS p", "m.country_id", "p.id")
        ->where([
            ["m.flag", 1]
            ,["d.flag", 1]
            ,["p.flag", 1]
        ]);

        if (isset($request->estado)) {
            $query = $query->where("users.estado", $request->estado);
        }

        if (isset($request->pais)) {
            $query = $query->whereIn("p.id", $request->pais);
        }

        if (isset($request->departamento)) {
            $query = $query->whereIn("d.id", $request->departamento);
        }

        if (isset($request->ciudad)) {
            $query = $query->whereIn("m.id", $request->ciudad);
        }
        
        return datatables()->eloquent($query)->toJson();
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $user = User::find($request->id);
        
        if (is_object($user)){
            $user->estado = $request->estado;
        
            if ($user->save()) {
                $resp["success"] = true;
                $resp["msj"] = "El usuario " . $user->usuario . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            } else {
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        } else {
            $resp["msj"] = "No se ha encontrado el usuario";
        }
        return $resp; 
    }

    public function eliminar(Request $request){
        $resp["success"] = false;
        $usuario = User::find($request->id);
        
        if(!is_null($usuario)){    
          if ($usuario->delete()) {
            $resp["success"] = true;
            $resp["msj"] = "Se ha eliminado el usuario";
          } else {
            $resp["msj"] = "No se ha eliminado el usuario";
          }
        } else {
          $resp["msj"] = "No se ha encontrado el usuario";
        }
        return $resp; 
    }

    public function editar(Request $request){
        $resp["success"] = false;
        $validar = User::where([
                  ['id', '<>', $request->id],
                  ['nro_documento', $request->nro_documento]
                ])->get();
        
        if ($validar->isEmpty()) {
            $validar = User::where([
                ['id', '<>', $request->id],
                ['usuario', $request->usuario]
            ])->get();
            if ($validar->isEmpty()) {
                $validar = User::where([
                    ['id', '<>', $request->id],
                    ['email', $request->email]
                ])->get();
                if ($validar->isEmpty()) {
                    $usuario = Usuarios::find($request->id);
                    if(!empty($usuario)){
                        if ($usuario->nro_documento != $request->nro_documento || $usuario->usuario != $request->usuario || $usuario->nombre1 != $request->nombre1 || $usuario->nombre2 != $request->nombre2 || $usuario->apellido1 != $request->apellido1 || $usuario->apellido2 != $request->apellido2 || $usuario->email != $request->email || $usuario->telefono != $request->telefono || $usuario->estado != $request->estado || $usuario->fk_municipio != $request->fk_municipio) {
                        
                        $usuario->nro_documento = $request->nro_documento;
                        $usuario->usuario = $request->usuario;
                        $usuario->nombre1 = $request->nombre1;
                        $usuario->nombre2 = $request->nombre2;
                        $usuario->apellido1 = $request->apellido1;
                        $usuario->apellido2 = $request->apellido2; 
                        $usuario->email = $request->email; 
                        $usuario->telefono = $request->telefono; 
                        $usuario->estado = $request->estado; 
                        $usuario->fk_municipio = $request->fk_municipio; 
                        
                        if ($usuario->save()) {
                            $resp["success"] = true;
                            $resp["msj"] = "Se han actualizado los datos";
                        }else{
                            $resp["success"] = false;
                            $resp["msj"] = "No se han guardado cambios";
                        }
                        } else {
                        $resp["success"] = false;
                        $resp["msj"] = "Por favor realice algún cambio";
                        }
                    }else{
                        $resp["msj"] = "No se ha encontrado el usuario";
                    }
                } else {
                    $resp["msj"] = "El correo " . $request->email . " ya se encuentra registrado";  
                }
            } else {
                $resp["msj"] = "El usuario " . $request->usuario . " ya se encuentra registrado";
            }
        } else {
            $resp["msj"] = "El número de documento " . $request->nro_documento . " ya se encuentra registrado";
        }
        return $resp; 
    }

    public function permisos($idUsuario, $permiso = null){
        $query = DB::table("permisos AS p")
                    ->select(
                        "p.id"
                        ,"p.nombre"
                        ,"p.tag"
                        ,"p.icono"
                    )->addSelect(['contHijos' => DB::table("permisos AS per")->selectRaw('count(*)')->whereColumn('per.fk_permiso', 'p.id')])
                    ->selectRaw("(CASE WHEN ps.fk_usuario IS NULL THEN 0 ELSE 1 END) AS aplicaPermiso")
                    ->leftjoin("permisos_sistema as ps", function ($join) use ($idUsuario) {
                        $join->on('p.id', 'ps.fk_permiso')
                        ->where('ps.fk_usuario', $idUsuario);
                    });
                        
        if ($permiso == null) {
            $query = $query->whereNull('p.fk_permiso');
        } else {
            $query = $query->where('p.fk_permiso', $permiso);
        }

        $query = $query->get();

        foreach ($query as $per) {
            if ($per->contHijos > 0) {
                $per->hijos = $this->permisos($idUsuario, $per->id);
            }
        }

        return $query; 
    }
}
