<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use DB;

class UserController extends Controller {
    //
    public function inicioSesion($nroDoc, $pass){
        $resp["success"]= false;
        //Validaos por numero de documento, email y usuario
        $usuario = User::select(
                "users.id"
                ,"users.nro_documento"
                ,"users.usuario"
                ,"users.password"
                ,"users.nombre"
                ,"users.nombre1"
                ,"users.nombre2"
                ,"users.apellido1"
                ,"users.apellido2"
                ,"users.foto"
                ,"users.email"
                ,"users.telefono"
                ,"users.fk_municipio"
                ,"users.fk_perfil"
                ,"m.name AS ciudad_nombre"
                ,"d.id AS dep_id"
                ,"d.name AS dep_nombre"
                ,"p.id AS pais_id"
                ,"p.name AS pais_nombre"
            )->join("municipios AS m", "users.fk_municipio", "m.id")
            ->join("departamentos AS d", "m.state_id", "d.id")
            ->join("paises AS p", "m.country_id", "p.id")
            ->where(function($query) use ($nroDoc) {
                $query->orWhere('nro_documento', $nroDoc)
                    ->orWhere('email', $nroDoc)
                    ->orWhere('usuario', $nroDoc);
            })->first();

        if (is_object($usuario)){
            if(Hash::check($pass, $usuario->password)){
                $usuario->nombreCompleto = $usuario->nombre;

                $resp['success'] = true;
                $resp['menu'] = $this->permisos($usuario->id, $usuario->fk_perfil, true);
                $resp['permisos'] = $this->permisos($usuario->id, $usuario->fk_perfil, true, null, false);
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
        $validar = User::where('nro_documento', $request->nro_documento)->get();
        
        if($validar->isEmpty()){
            $validar = User::where('usuario', $request->usuario)->get();
            if($validar->isEmpty()){
                $validar = User::where('email', $request->email)->get();
                if($validar->isEmpty()){

                    $usuario = new User;
                    $usuario->nro_documento = $request->nro_documento;
                    $usuario->usuario = $request->usuario;
                    $usuario->password = Hash::make($request->nro_documento, ['rounds' => 15]);
                    $usuario->nombre = $request->nombre1 . ' ' . (strlen($request->nombre2) > 0 ? $request->nombre2 . ' ' : '') . $request->apellido1 . (strlen($request->apellido2) > 0 ? ' ' . $request->apellido2 : '');
                    $usuario->nombre1 = $request->nombre1;
                    $usuario->nombre2 = $request->nombre2;
                    $usuario->apellido1 = $request->apellido1;
                    $usuario->apellido2 = $request->apellido2;
                    $usuario->email = $request->email;
                    $usuario->telefono = $request->telefono;
                    $usuario->estado = $request->estado;
                    $usuario->fk_municipio = $request->fk_municipio;
                    $usuario->fk_perfil = $request->fk_perfil == "null" ? null : $request->fk_perfil;

                    if( $usuario->save() ){
                        $resp["success"] = true;
                        $resp["msj"] = "Se ha creado el usuario";
                        $resp["id"]= $usuario->id;
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

    public function setFoto(Request $request){
        $resp["success"] = false;

        $user = User::find($request->id);
        if (is_object($user)){
            
            $user->foto = $request->fotoUrl;
           
            if( $user->save() ){
                $resp["success"] = true;
                $resp["msj"] = "Foto Actualizada";
            }else{
                $resp["msj"] = "No se actualizado foto";
            }
               
        }else{
            $resp["msj"] = "El usuario no se encuentra";
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
            ,"users.fk_perfil"
            ,"m.name AS ciudad_nombre"
            ,"d.id AS dep_id"
            ,"d.name AS dep_nombre"
            ,"p.id AS pais_id"
            ,"p.name AS pais_nombre"
            ,"per.nombre AS perfil_nombre"
        )->join("municipios AS m", "users.fk_municipio", "m.id")
        ->join("departamentos AS d", "m.state_id", "d.id")
        ->join("paises AS p", "m.country_id", "p.id")
        ->leftjoin("perfiles AS per", "users.fk_perfil", "per.id")
        ->where([
            ["m.flag", 1]
            ,["d.flag", 1]
            ,["p.flag", 1]
        ]);

        if (isset($request->estado)) {
            $query = $query->where("users.estado", $request->estado);
        }

        if (isset($request->paises)) {
            $query = $query->whereIn("p.id", $request->paises);
        }

        if (isset($request->departamentos)) {
            $query = $query->whereIn("d.id", $request->departamentos);
        }

        if (isset($request->ciudades)) {
            $query = $query->whereIn("m.id", $request->ciudades);
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
                    $usuario = User::find($request->id);
                    if(!empty($usuario)){
                        if (
                            $usuario->nro_documento != $request->nro_documento ||
                            $usuario->usuario != $request->usuario ||
                            $usuario->nombre1 != $request->nombre1 ||
                            $usuario->nombre2 != $request->nombre2 ||
                            $usuario->apellido1 != $request->apellido1 ||
                            $usuario->apellido2 != $request->apellido2 ||
                            $usuario->email != $request->email ||
                            $usuario->telefono != $request->telefono ||
                            $usuario->estado != $request->estado ||
                            $usuario->fk_municipio != $request->fk_municipio ||
                            $usuario->fk_perfil != $request->fk_perfil ||
                            $usuario->foto != $request->foto
                        ) {
                        
                        $usuario->nro_documento = $request->nro_documento;
                        $usuario->usuario = $request->usuario;
                        $usuario->nombre1 = $request->nombre1;
                        $usuario->nombre2 = $request->nombre2;
                        $usuario->apellido1 = $request->apellido1;
                        $usuario->apellido2 = $request->apellido2; 
                        $usuario->nombre = $request->nombre1 . ' ' . (strlen($request->nombre2) > 0 ? $request->nombre2 . ' ' : '') . $request->apellido1 . (strlen($request->apellido2) > 0 ? ' ' . $request->apellido2 : '');
                        $usuario->email = $request->email; 
                        $usuario->telefono = $request->telefono; 
                        $usuario->estado = $request->estado; 
                        $usuario->fk_municipio = $request->fk_municipio;
                        $usuario->fk_perfil = $request->fk_perfil == "null" ? null : $request->fk_perfil;

                        if ($usuario->save()) {
                            $resp["success"] = true;
                            $resp["msj"] = "Se han actualizado los datos";
                            $resp["id"]= $usuario->id;

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

    public function permisos($idUsuario, $idPerfil, $menu = false, $permiso = null, $hijos = true){
        $idPerfil = is_null($idPerfil) ? 0 : $idPerfil;

        $query = DB::table("permisos AS p")
                    ->select(
                        "p.id"
                        ,"p.nombre"
                        ,"p.tag"
                        ,"p.icono"
                        ,"p.ruta"
                        ,"p.fk_permiso"
                    )->addSelect(['contHijos' => DB::table("permisos AS per")->selectRaw('count(*)')->whereColumn('per.fk_permiso', 'p.id')])
                    ->selectRaw("(CASE WHEN ps.fk_usuario IS NULL THEN CASE WHEN ps.fk_perfil IS NULL THEN 0 ELSE 1 END ELSE 1 END) AS aplicaPermiso")
                    ->leftjoin("permisos_sistema as ps", function ($join) use ($idUsuario, $idPerfil) {
                        $join->on('p.id', 'ps.fk_permiso')
                        ->where(function($query) use ($idUsuario, $idPerfil) {
                            return $query->where("ps.fk_usuario", $idUsuario)
                                        ->orWhere("ps.fk_perfil", $idPerfil);
                        })->where('ps.estado', 1);
                    });
        
        if ($menu == true) {
            $query = $query->where(function($query) use ($idUsuario, $idPerfil) {
                return $query->whereNotNull("ps.fk_usuario")
                            ->orWhereNotNull("ps.fk_perfil");
            });
        }

        if ($hijos){
            if ($permiso == null) {
                $query = $query->whereNull('p.fk_permiso');
            } else {
                $query = $query->where('p.fk_permiso', $permiso);
            }
        }
        
        $query = $query->groupBy("p.id", "p.nombre", "p.tag", "p.icono", "p.ruta", "p.fk_permiso")->get();
        $quries = DB::getQueryLog();
        if ($hijos){ 
            foreach ($query as $per) {
                if ($per->contHijos > 0) {
                    $per->hijos = $this->permisos($idUsuario, $idPerfil, $menu, $per->id);
                }
            }
        }
        return $query; 
    }

    public function checkearUsuario($usuario){
        $existeUsuario = User::where('usuario', $usuario)->get();

        return $existeUsuario->isEmpty();
        
    }

    public function guardarPermiso(Request $request){
        $resp["success"] = false;
        DB::beginTransaction();

        DB::table('permisos_sistema')->where("fk_usuario", $request->idUsuario)->delete(); 
        
        $cont = 0;
        foreach ($request->permisos as $value) {
            try {
                DB::table('permisos_sistema')->insert([
                    "fk_usuario" => $request->idUsuario
                    ,"fk_permiso" => $value
                    ,"tipo" => 0
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                $cont++;
                break;
            }
        }

        if ($cont == 0) {
            DB::commit();
            $resp["success"] = true;
            $resp["msj"] = "Permisos actualizados correctamente.";
        } else {
            DB::rollback();
            $resp["msj"] = "Error al actualizar los permisos.";
        }

        return $resp;
    }

    public function escuelas($idUsuario, $idRol){
       $query = DB::table("permisos_sistema AS PS")
                ->select(
                    "E.id"
                    ,"E.nombre"
                    ,"E.descripcion"
                )->join("escuelas AS E", "PS.fk_escuelas", "=", "E.id")  
                ->where(function($query) use ($idUsuario, $idRol) {
                    return $query->where("PS.fk_perfil", $idRol)
                                ->orWhere("PS.fk_usuario", $idUsuario);
                })->whereNotNull("PS.fk_escuelas")->get();

        return $query; 
    }

    public function editarPefil(Request $request){
        $resp["success"] = false;
        $usuario = User::find($request->idUsuario);

        if(!empty($usuario)){
            if (
                $usuario->nombre1 != $request->nombre1 ||
                $usuario->nombre2 != $request->nombre2 ||
                $usuario->apellido1 != $request->apellido1 ||
                $usuario->apellido2 != $request->apellido2 ||
                $usuario->email != $request->email ||
                $usuario->telefono != $request->telefono ||
                $usuario->fk_municipio != $request->idCiudad
            ) {
            
                $usuario->nombre1 = $request->nombre1;
                $usuario->nombre2 = $request->nombre2;
                $usuario->apellido1 = $request->apellido1;
                $usuario->apellido2 = $request->apellido2; 
                $usuario->nombre = $request->nombre1 . ' ' . (strlen($request->nombre2) > 0 ? $request->nombre2 . ' ' : '') . $request->apellido1 . (strlen($request->apellido2) > 0 ? ' ' . $request->apellido2 : '');
                $usuario->email = $request->email; 
                $usuario->telefono = $request->telefono; 
                $usuario->fk_municipio = $request->idCiudad;

                if ($usuario->save()) {
                    $resp["success"] = true;
                    $resp["msj"] = "Se han actualizado los datos";
                }else{
                    $resp["msj"] = "No se han guardado cambios";
                }
            } else {
                $resp["msj"] = "Por favor realice algún cambio";
            }
        }else{
            $resp["msj"] = "No se ha encontrado el usuario";
        }
        
        return $resp;
    }

    public function cambiarPass(Request $request){
        $resp["success"] = false;
        $usuario = User::find($request->idUsuario);

        if(!empty($usuario)){
            if(Hash::check($request->passActual, $usuario->password)){
                if(trim($request->passNueva) === trim($request->passNuevaConfirm)){
                    $usuario->password = Hash::make(trim($request->passNueva), ['rounds' => 15]);
                    if($usuario->save()){
                        $resp["success"] = true;
                        $resp["msj"] = "Contraseña actualizada";
                    }else{
                        $resp["msj"] = "No se ha actualizado la contraseña";
                    }
                } else {
                    $resp["msj"] = "La contraseña nueva no coincide.";
                }
            } else {
                $resp["msj"] = "La contraseña actual no coincide.";
            }
        }else{
            $resp["msj"] = "No se ha encontrado el usuario";
        }

        return $resp;

    }
    
    public function upload(Request $request){
    
        $uploaded = Storage::putFileAs('public/'.$request->ruta, $request->file, $request->nombre);

        $resp["success"] = true;
        $resp["ruta"] = $uploaded;

        return $resp;
    }
}
