<?php

namespace App\Http\Controllers;

use App\Models\departamentos;
use Illuminate\Http\Request;

class DepartamentosController extends Controller
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
     * 
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\departamentos  $departamentos
     * @return \Illuminate\Http\Response
     */
    public function edit(departamentos $departamentos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\departamentos  $departamentos
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, departamentos $departamentos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\departamentos  $departamentos
     * @return \Illuminate\Http\Response
     */
    public function destroy(departamentos $departamentos)
    {
        //
    }

    public function show(Request $request, departamentos $paises){

        if ($request->estado != '') {
            $query = departamentos::where(["flag", $request->estado]);
        } else {
            $query = departamentos::query();
        }

        return datatables()->eloquent($query)->toJson();
    }
}