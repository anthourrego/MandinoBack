<?php

namespace App\Http\Controllers;

use App\Models\municipios;
use Illuminate\Http\Request;

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
    public function show(municipios $municipios)
    {
        //
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


        /**
     * lista los departamentos de un país especifico
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function lista(Request $request){
        return municipios::select('id', 'name')->where("country_id", $request->idPais)->where("state_id", $request->idDepto)->get();
    }


    /**
     * regresa ids de pais y departamento
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function ubicacion(Request $request){

        /* const $query = municipios::select('municipios.*','departamentos.*');
        $query->join('departamentos', 'municipios.state_id', '=', $request->idMunicipio);
        $query->join('departamentos', 'departamentos.id', '=', $request->idMunicipio); */
    }

}
