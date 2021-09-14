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

    public function permisos($idPerfil){
        $resp['permisos'] = $this->arbol($idPerfil);
        $resp['escuelas'] = $this->escuelas($idPerfil);

        return $resp;
    }

    public function arbol($idPerfil, $permiso = null){
        $query = DB::table("permisos AS p")
                    ->select(
                        "p.id AS value"
                        ,"p.tag AS text"
                    )->addSelect(['contHijos' => DB::table("permisos AS per")->selectRaw('count(*)')->whereColumn('per.fk_permiso', 'p.id')])
                    ->selectRaw("(CASE WHEN ps.fk_perfil IS NULL THEN 0 ELSE 1 END) AS checked")
                    ->leftjoin("permisos_sistema as ps", function ($join) use ($idPerfil) {
                        $join->on('p.id', 'ps.fk_permiso')
                        ->where('ps.fk_perfil', $idPerfil)
                        ->where('ps.estado', 1);
                    });
    

        if ($permiso == null) {
            $query = $query->whereNull('p.fk_permiso');
        } else {
            $query = $query->where('p.fk_permiso', $permiso);
        }

        $query = $query->get();

        foreach ($query as $per) {
            if ($per->contHijos > 0) {
                $per->children = $this->arbol($idPerfil, $per->value);
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

    public function guardarPermiso(Request $request){
        $resp["success"] = false;
        $cont = 0;
        DB::beginTransaction();

        if (isset($request->permisos)) {
            DB::table('permisos_sistema')->where("fk_perfil", $request->idPerfil)->whereNull('fk_escuelas')->delete(); 
            
            foreach ($request->permisos as $value) {
                try {
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
            DB::table('permisos_sistema')->where("fk_perfil", $request->idPerfil)->whereNull('fk_permiso')->delete(); 
            
            foreach ($request->escuelas as $value) {
                try {
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


}
