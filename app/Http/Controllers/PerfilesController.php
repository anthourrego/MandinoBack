<?php

namespace App\Http\Controllers;

use App\Models\perfiles;
use Illuminate\Http\Request;
use DB;

class PerfilesController extends Controller {

    public function lista(){
        return perfiles::select("id", "nombre")->where("estado", 1)->get();
    }

    public function crear(Request $request){
        $resp["success"] = false;
        $validar = perfiles::where([
            ['nombre', $request->nombre], 
        ])->get();

        if($validar->isEmpty()){
            $perfil = new perfiles;
            $perfil->nombre = $request->nombre;
            $perfil->estado = $request->estado;

            if($perfil->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado el perfil correctamente.";
            }else{
                $resp["msj"] = "No se ha creado el perfil " . $request->nombre;
            }
        }else{
            $resp["msj"] = "El perfil " . $request->nombre . " ya se encuentra registrado.";
        }

        return $resp;
    }

    public function actualizar(Request $request){
        $resp["success"] = false;
        $validar = perfiles::where([
            ['id', '<>', $request->id],
            ['nombre', $request->nombre]
          ])->get();
  
        if ($validar->isEmpty()) {

            $perfil = perfiles::find($request->id);

            if(!empty($perfil)){
                if ($perfil->nombre != $request->nombre || 
                    $perfil->estado != $request->estado
                    ) {

                    $perfil->nombre = $request->nombre;
                    $perfil->estado = $request->estado;
                    
                    if ($perfil->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado el perfil";
            }
        }else{
            $resp["msj"] = "El perfil " . $request->nombre . " ya se encuentra registrado";
        }
        
        return $resp;
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $perfil = perfiles::find($request->id);
        
        if(is_object($perfil)){
            DB::beginTransaction();
            $perfil->estado = $request->estado;
        
            if ($perfil->save()) {
                $resp["success"] = true;
                $resp["msj"] = "El perfil " . $perfil->nombre . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            }else{
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        }else{
            $resp["msj"] = "No se ha encontrado el perfil";
        }
        return $resp; 
    }

    public function show(Request $request){
        $query = perfiles::select('id', 'nombre', 'estado', 'created_at');
        if ($request->estado != '') {
            $query->where("estado", $request->estado);
        }
        return datatables()->eloquent($query)->rawColumns(['nombre'])->make(true);
    }

    public function permisos($idPerfil, $idUsuario = 0){
        $resp['permisos'] = $this->arbol($idPerfil);
        if ($idUsuario == 0) {
            $resp['escuelas'] = $this->escuelas($idPerfil);
            $resp['categorias'] = $this->categorias($idPerfil);
        } else {
            $resp['permisosUsuario'] = $this->arbol($idPerfil, null, $idUsuario);
        }

        return $resp;
    }

    public function arbol($idPerfil, $permiso = null, $idUsuario = null){
        $query = DB::table("permisos AS p")
                    ->select(
                        "p.id AS value"
                        ,"p.tag AS text"
                    )->addSelect(['contHijos' => DB::table("permisos AS per")->selectRaw('count(*)')->whereColumn('per.fk_permiso', 'p.id')])
                    ->selectRaw("(
                        CASE 
                            WHEN '$idPerfil' = 'null'
                                THEN IF(ps.fk_usuario IS NULL, 0, 1)
                            ELSE IF(ps.fk_perfil IS NULL, 0, 1) 
                        END
                    ) AS checked")
                    ->leftjoin("permisos_sistema as ps", function ($join) use ($idPerfil, $idUsuario) {
                        $join = $join->on('p.id', 'ps.fk_permiso')->where('ps.estado', 1);
                        if ($idPerfil == 'null') {
                            $join->whereNull("ps.fk_perfil");
                        } else {
                            $join->where('ps.fk_perfil', $idPerfil);
                        }
                        if (is_null($idUsuario)) {
                            $join->whereNull("ps.fk_usuario");
                        } else {
                            $join->where("ps.fk_usuario", $idUsuario);
                        }
                    });
    

        if ($permiso == null) {
            $query = $query->whereNull('p.fk_permiso');
        } else {
            $query = $query->where('p.fk_permiso', $permiso);
        }

        $query = $query->get();

        foreach ($query as $per) {
            if ($per->contHijos > 0) {
                $per->children = $this->arbol($idPerfil, $per->value, $idUsuario);
            }
        }

        return $query; 
    }

    public function escuelas($idPerfil){
        $query = DB::table("escuelas AS e")
        ->select(
            "e.id"
            ,"e.nombre"
        )->selectRaw("(CASE WHEN ps.fk_perfil IS NULL THEN 0 ELSE 1 END) AS checked")
        ->leftjoin("permisos_sistema as ps", function ($join) use ($idPerfil) {
            $join->on('e.id', 'ps.fk_escuelas')
            ->where('ps.fk_perfil', $idPerfil)
            ->where('ps.estado', 1);
        })->get();

        return $query;
    }

    public function categorias($idPerfil){
        $query = DB::table("toma_control_categorias AS tcc")
        ->select(
            "tcc.id"
            ,"tcc.nombre"
        )->selectRaw("(CASE WHEN ps.fk_perfil IS NULL THEN 0 ELSE 1 END) AS checked")
        ->leftjoin("permisos_sistema as ps", function ($join) use ($idPerfil) {
            $join->on('tcc.id', 'ps.fk_categorias_toma_control')
            ->where('ps.fk_perfil', $idPerfil)
            ->where('ps.estado', 1);
        })->where('tcc.estado', 1)
        ->get();

        return $query;
    }

    public function reiniciarProgreso(Request $request) {
        $resp["success"] = false;
    
        $usuarios = DB::table('users')->where('fk_perfil', $request->perfil)->where('estado', 1)->get('id');

        if (count($usuarios) > 0) {

            $escuelas = $this->escuelas($request->perfil);
            
            $escuelasIds = [];
            foreach ($escuelas as $esc) $escuelasIds[] = $esc->id;

            $lecciones = DB::table('escuelas_cursos AS EC')
                ->selectRaw('LU.fk_leccion, L.tipo')
                ->join("unidades_cursos AS UC", function ($join) {
                    $join->on('EC.fk_curso', 'UC.fk_curso')->where('UC.estado', 1);
                })
                ->join("lecciones_unidades AS LU", function ($join) {
                    $join->on('UC.fk_unidad', 'LU.fk_unidad')->where('LU.estado', 1);
                })
                ->join("lecciones AS L", "LU.fk_leccion", "=", "L.id")
                ->whereIn('EC.fk_escuela', $escuelasIds)
                ->where('EC.estado', 1)
                ->get();

            $user = new UserController();

            DB::beginTransaction();

            foreach ($usuarios as $userid) {
                $resp = $user->eliminarProgresos($lecciones, $userid->id);
                if ($resp['success'] === false) {
                    DB::rollback();
                    return $resp;
                }
            }

            DB::commit();
            return $resp;

        } else {
            $resp['msj'] = "No se encontraron usuarios con este perfil.";
        }
        return $resp;
    }

    public function activarIntroduccion(Request $request) {
        $resp["success"] = false;
        $resp['msj'] = "Introducción activada correctamente.";
    
        $usuarios = DB::table('users')->where('fk_perfil', $request->perfil)->where('estado', 1)->where('introduccion', 0)->get('id');

        if (count($usuarios) > 0) {

            $usersIds = [];
            foreach ($usuarios as $us) $usersIds[] = $us->id;

            DB::beginTransaction();
            
            try {
                DB::table('users')->whereIn('id', $usersIds)->update(['introduccion' => 1]);
                DB::commit();
                $resp["success"] = true;
            } catch (\Exception $e) {
                DB::rollback();
                $resp['msj'] = "No fue posible actualizar la información.";
                return;
            }

        } else {
            $resp['msj'] = "No se encontraron usuarios para actualizar.";
        }
        return $resp;
    }

    public function guardarPermiso(Request $request){
        $resp["success"] = false;
        $cont = 0;
        DB::beginTransaction();

        if (isset($request->permisos)) {
            DB::table('permisos_sistema')->where("fk_perfil", $request->idPerfil)->whereNotNull('fk_permiso')->whereNull('fk_usuario')->delete(); 
            $permisoSeleccionados = $this->permisosGuardar($request->permisos, []);
            foreach ($permisoSeleccionados as $value) {
                try {
                    DB::table('permisos_sistema')
                        ->where("fk_perfil", $request->idPerfil)
                        ->where('fk_permiso', $value)
                        ->whereNotNull('fk_usuario')
                        ->delete(); 
                    DB::table('permisos_sistema')->insert([
                        "fk_perfil" => $request->idPerfil
                        ,"fk_permiso" => $value
                        ,"tipo" => 0
                    ]);
                } catch (\Exception $e) {
                    DB::rollback();
                    $cont++;
                    break;
                }
            }
        }
        
        if (isset($request->escuelas)) {
            DB::table('permisos_sistema')->where("fk_perfil", $request->idPerfil)->whereNotNull('fk_escuelas')->delete(); 
            
            foreach ($request->escuelas as $value) {
                try {
                    DB::table('permisos_sistema')
                        ->where('fk_escuelas', $value)
                        ->whereNotNull('fk_usuario')
                        ->delete();
                    DB::table('permisos_sistema')->insert([
                        "fk_perfil" => $request->idPerfil
                        ,"fk_escuelas" => $value
                        ,"tipo" => 0
                    ]);
                } catch (\Exception $e) {
                    DB::rollback();
                    $cont++;
                    break;
                }
            }
        }
        
        if (isset($request->categorias)) {
            DB::table('permisos_sistema')->where("fk_perfil", $request->idPerfil)->whereNotNull('fk_categorias_toma_control')->delete(); 
            
            foreach ($request->categorias as $value) {
                try {
                    DB::table('permisos_sistema')->insert([
                        "fk_perfil" => $request->idPerfil
                        ,"fk_categorias_toma_control" => $value
                        ,"tipo" => 0
                    ]);
                } catch (\Exception $e) {
                    DB::rollback();
                    $cont++;
                    break;
                }
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

    public function permisosGuardar($permisos, $ids) {
        foreach($permisos as $permiso) {
            $query = DB::table("permisos")->select("id", "fk_permiso")->where('id', $permiso)->first();
            if (!in_array($query->id, $ids)) {
                $ids[] = $query->id;
            }
            if (!is_null($query->fk_permiso)) {
                $ids = $this->permisosGuardar([$query->fk_permiso], $ids);
            }
        }
        return $ids;
    }


}
