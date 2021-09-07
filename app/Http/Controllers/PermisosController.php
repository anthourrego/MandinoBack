<?php

namespace App\Http\Controllers;

use App\Models\permisos;
use Illuminate\Http\Request;
use DB;

class PermisosController extends Controller {
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\permisos  $permisos
     * @return \Illuminate\Http\Response
     */
    public function show($permiso = null){

        $query = permisos::addSelect(['contHijos' => DB::table("permisos AS per")->selectRaw('count(*)')
                            ->whereColumn('per.fk_permiso', 'permisos.id')
                        ]);
                        
        if ($permiso == null) {
            $query = $query->whereNull('fk_permiso');
        } else {
            $query = $query->where('fk_permiso', $permiso);
        }

        $query = $query->get();

        foreach ($query as $per) {
            if ($per->contHijos > 0) {
                $per->hijos = $this->show($per->id);
            }
        }

        return $query; 
    }

    public function crear(Request $request){
        $resp["success"] = false;
        $validar = permisos::where([
            ['nombre', $request->nombre], 
        ])->get();

        if($validar->isEmpty()){
            $permiso = new permisos;
            $permiso->nombre = $request->nombre;
            $permiso->icono = $request->icono;
            $permiso->ruta = $request->ruta;
            $permiso->tag = $request->tag;
            $permiso->fk_permiso = $request->fk_permiso;
            $permiso->estado = $request->estado;

            if($permiso->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado el permiso correctamente.";
            }else{
                $resp["msj"] = "No se ha creado el el permiso " . $request->nombre;
            }
        }else{
            $resp["msj"] = "El permiso " . $request->nombre . " ya se encuentra registrado.";
        }

        return $resp;
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $permiso = permisos::find($request->id);
        
        if(is_object($permiso)){
            DB::beginTransaction();
            $permiso->estado = $request->estado;
        
            if ($permiso->save()) {
                $resp["success"] = true;
                $resp["msj"] = "El permiso " . $permiso->nombre . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            }else{
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        }else{
            $resp["msj"] = "No se ha encontrado el permiso";
        }
        return $resp; 
    }

    public function update(Request $request) {
        $resp["success"] = false;
        $validar = permisos::where([
            ['id', '<>', $request->id],
            ['nombre', $request->nombre]
          ])->get();
  
        if ($validar->isEmpty()) {

            $permisos = permisos::find($request->id);

            if(!empty($permisos)){
                if ($permisos->nombre != $request->nombre || $permisos->icono != $request->icono || $permisos->ruta != $request->ruta || $permisos->tag != $request->tag || $permisos->fk_permiso != $request->fk_permiso || $permisos->estado != $request->estado) {

                    $permisos->nombre = $request->nombre;
                    $permisos->icono = $request->icono;
                    $permisos->ruta = $request->ruta;
                    $permisos->tag = $request->tag;
                    $permisos->fk_permiso = $request->fk_permiso;
                    $permisos->estado = $request->estado;
                    
                    if ($permisos->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado el permiso";
            }
        }else{
            $resp["msj"] = "El permiso " . $request->nombre . " ya se encuentra registrado";
        }
        
        return $resp;
    }

}
