<?php

namespace App\Http\Controllers;

use App\Models\permisos;
use Illuminate\Http\Request;

class PermisosController extends Controller {
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\permisos  $permisos
     * @return \Illuminate\Http\Response
     */
    public function show($permiso = null){

        if ($permiso == null) {
            $query = permisos::where("estado", 1)
                            ->whereNull('fk_permiso')
                            ->get();
        } else {
            $query = permisos::where("estado", 1)->where('fk_permiso', $permiso)->get();
        }

        foreach ($query as $per) {
            $cont = permisos::where("fk_permiso", $per->id)->count();
            if ($cont > 0) {
                $per->hijos = $this->show($per->id);
            }
        }

        return $query; 
    }

}
