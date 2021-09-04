<?php

namespace App\Http\Controllers;

use App\Models\municipios;
use Illuminate\Http\Request;
use DB;

class MunicipiosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\municipios  $municipios
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request){
        $query = DB::table("municipios AS m")
                ->select("m.id"
                    ,"m.name"
                    ,"d.id AS id_departamento"
                    ,"d.name AS nombre_departamento"
                    ,"p.id AS id_pais"
                    ,"p.name AS nombre_pais"
                    ,"m.flag"
                    ,"m.created_at"
                )->join('departamentos AS d', 'm.state_id', '=', 'd.id')
                ->join('paises AS p', 'd.country_id', '=', 'p.id');
        
        if(isset($request->paises)) {
            $query->whereIn("p.id", $request->paises);
        }

        if(isset($request->departamentos)) {
            $query->whereIn("d.id", $request->departamentos);
        }

        if ($request->estado != '') {
            $query->where("m.flag", $request->estado);
        }

        $query->where([
            ["p.flag", 1]
            ,["d.flag", 1]
        ])->orderBy('m.name', 'asc');

        return datatables()->query($query)->toJson();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\municipios  $municipios
     * @return \Illuminate\Http\Response
     */
    public function edit(municipios $municipios)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\municipios  $municipios
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, municipios $municipios)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\municipios  $municipios
     * @return \Illuminate\Http\Response
     */
    public function destroy(municipios $municipios)
    {
        //
    }
}
