<?php

namespace App\Http\Controllers;

use App\Models\cursos;
use Illuminate\Http\Request;
use DB;

class CursosController extends Controller
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
    public function crear(Request $request){
        $resp["success"] = false;
        $validar = cursos::where([
            ['nombre', $request->nombre], 
        ])->get();

        if($validar->isEmpty()){
            $curso = new cursos;
            $curso->nombre = $request->nombre;
            $curso->descripcion = $request->descripcion;
            $curso->estado = $request->estado;

            if($curso->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado el curso correctamente.";
            }else{
                $resp["msj"] = "No se ha creado el curso " . $request->nombre;
            }
        }else{
            $resp["msj"] = "El curso " . $request->nombre . " ya se encuentra registrado.";
        }

        return $resp;
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
     * @param  \App\Models\cursos  $cursos
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $query = cursos::select('id', 'nombre', 'descripcion', 'estado', 'created_at');
        if ($request->estado != '') {
            $query->where("estado", $request->estado);
        }
        return datatables()->eloquent($query)->rawColumns(['nombre', 'descripcion'])->make(true);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\cursos  $cursos
     * @return \Illuminate\Http\Response
     */
    public function edit(cursos $cursos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\cursos  $cursos
     * @return \Illuminate\Http\Response
     */
    public function actualizar(Request $request)
    {
        $resp["success"] = false;
        $validar = cursos::where([
            ['id', '<>', $request->id],
            ['nombre', $request->nombre]
        ])->get();
  
        if ($validar->isEmpty()) {

            $curso = cursos::find($request->id);

            if(!empty($curso)){
                if ($curso->nombre != $request->nombre || $curso->descripcion != $request->descripcion || $curso->estado != $request->estado) {

                    $curso->nombre = $request->nombre;
                    $curso->descripcion = $request->descripcion;
                    $curso->estado = $request->estado;
                    
                    if ($curso->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado el curso";
            }
        }else{
            $resp["msj"] = "el curso " . $request->nombre . " ya se encuentra registrado";
        }
        
        return $resp;
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\cursos  $cursos
     * @return \Illuminate\Http\Response
     */
    public function destroy(cursos $cursos)
    {
        //
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $curso = cursos::find($request->id);
        
        if(is_object($curso)){
            DB::beginTransaction();
            $curso->estado = $request->estado;
        
            if ($curso->save()) {
                $resp["success"] = true;
                $resp["msj"] = "La escuela " . $curso->nombre . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            }else{
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        }else{
            $resp["msj"] = "No se ha encontrado la escuela";
        }
        return $resp; 
    }


    // asignación escuelas_cursos
    public function asignar(Request $request){
        $resp["success"] = false;

        try {
            $query = DB::table('escuelas_cursos')->insert([
               "fk_curso" => $request->fk_curso,
               "fk_escuela" => $request->fk_escuela,
               "fk_curso_dependencia" => $request->fk_curso_dependencia,
               "estado" => 1,
               "orden" => $request->orden        
            ]);

            $resp["success"] = true;
            $resp["msj"] = " curso asignado correctamente.";
            DB::commit();

            return $resp;
        } catch (\Exception $e) {
            DB::rollback();

            $resp["msj"] = " error al asignar curso.";

            return $resp;
        }

    }


    // listado escuelas_cursos
    public function listarEscuelasCursos($idEscuela){
        
        $query = DB::table('escuelas_cursos')->join('cursos', 'escuelas_cursos.fk_curso', '=', 'cursos.id');
        $query->where('escuelas_cursos.fk_escuela',$idEscuela);
        $query->select("escuelas_cursos.id as escuelas_cursos_id", "cursos.id as curso_id", "cursos.nombre as curso_nombre", "cursos.descripcion as curso_descripcion");
        
        return $query->get();

    }


}
