<?php

namespace App\Http\Controllers;

use App\Models\certificados;
use Illuminate\Http\Request;
use App\Http\Requests\StorecertificadosRequest;
use App\Http\Requests\UpdatecertificadosRequest;
use DB;

class CertificadosController extends Controller
{

    private $urlVar  = 'assets/certificados/variables.json';

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
     * @param  \App\Http\Requests\StorecertificadosRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorecertificadosRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\certificados  $certificados
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request){
        $query = DB::table("certificados AS c")
            ->select("c.id"
                ,"c.nombre"
                ,"c.estado"
                ,"c.ruta"
                ,"c.fk_escuela"
                ,"c.fk_curso"
                ,"c.fk_unidad"
                ,"c.created_at"
                ,"e.nombre AS nombre_escuela"
                ,"cu.nombre AS nombre_curso"
                ,"u.nombre AS nombre_unidad"
                ,"c.estado"
            )->join("escuelas AS e", function ($join) {
                $join->on('c.fk_escuela', 'e.id');
            })
            ->join("cursos AS cu", function ($join) {
                $join->on('c.fk_curso', 'cu.id');
            })
            ->join("unidades AS u", function ($join) {
                $join->on('c.fk_unidad', 'u.id');
            });

        if ($request->estado != '') {
            $query->where("c.estado", $request->estado);
        }

        return datatables()->query($query)->rawColumns(['c.nombre', 'nombre_escuela', 'nombre_curso', 'nombre_unidad'])->make(true);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\certificados  $certificados
     * @return \Illuminate\Http\Response
     */
    public function edit(certificados $certificados)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\certificados  $certificados
     * @return \Illuminate\Http\Response
     */
    public function destroy(certificados $certificados)
    {
        //
    }

    function datosVariables() {
        $path = resource_path($this->urlVar);

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type); 

        return $response;
    }

    public function cambiarEstado(Request $request){
        $resp["success"] = false;
        $certificado = certificados::find($request->id);
        
        if(is_object($certificado)){
            DB::beginTransaction();
            $certificado->estado = $request->estado;
        
            if ($certificado->save()) {
                $resp["success"] = true;
                $resp["msj"] = "El certificado " . $certificado->nombre . " se ha " . ($request->estado == 1 ? 'habilitado' : 'deshabilitado') . " correctamente.";
                DB::commit();
            }else{
                DB::rollBack();
                $resp["msj"] = "No se han guardado cambios";
            }
        }else{
            $resp["msj"] = "No se ha encontrado la ciudad";
        }
        return $resp; 
    }

    public function crear(Request $request){
        $resp["success"] = false;
        $validar = certificados::where([
            ['nombre', $request->nombre]
        ])->get();

        if($validar->isEmpty()){
            $certificado = new certificados;
            $certificado->nombre = $request->nombre;
            $certificado->fk_escuela = $request->fk_escuela;
            $certificado->fk_unidad = $request->fk_unidad;
            $certificado->fk_curso = $request->fk_curso;
            $certificado->estado = $request->estado;
            $certificado->ruta = $request->ruta;

            if($certificado->save()){
                $resp["success"] = true;
                $resp["msj"] = "Se ha creado el certificado correctamente.";
            }else{
                $resp["msj"] = "No se ha creado el certificado " . $request->nombre;
            }
        }else{
            $resp["msj"] = "El certificado " . $request->name . " ya se encuentra registrado.";
        }

        return $resp;
    }

    public function update(Request $request) {
        $resp["success"] = false;
        $validar = certificados::where([
            ['id', '<>', $request->id],
            ['nombre', $request->nombre]
          ])->get();
  
        if ($validar->isEmpty()) {

            $certificado = certificados::find($request->id);

            if(!empty($certificado)){
                if ($certificado->nombre != $request->nombre || $certificado->fk_escuela != $request->fk_escuela || $certificado->estado != $request->estado || $certificado->fk_unidad != $request->fk_unidad || $certificado->fk_curso != $request->fk_curso) {

                    $certificado->nombre = $request->nombre;
                    $certificado->fk_escuela = $request->fk_escuela;
                    $certificado->fk_unidad = $request->fk_unidad;
                    $certificado->fk_curso = $request->fk_curso;
                    $certificado->estado = $request->estado;
                    $certificado->ruta = $request->ruta;
                    
                    if ($certificado->save()) {
                        $resp["success"] = true;
                        $resp["msj"] = "Se han actualizado los datos";
                    }else{
                        $resp["msj"] = "No se han guardado cambios";
                    }
                } else {
                    $resp["msj"] = "Por favor realice algún cambio";
                }
            }else{
                $resp["msj"] = "No se ha encontrado el certificado";
            }
        }else{
            $resp["msj"] = "El certificado " . $request->nombre . " ya se encuentra registrado";
        }
        
        return $resp;
    }

    public function lista(Request $request){
        $query = DB::table("certificados AS c")
                ->select("c.id", "c.nombre")
                ->join('escuelas AS e', 'c.fk_escuela', '=', 'e.id')
                ->join('unidades AS u', 'c.fk_unidad', '=', 'u.id')
                ->join('cursos AS cu', 'c.fk_curso', '=', 'cu.id');

        $query = $query->where([
            ["c.estado", 1]
            ,["u.estado", 1]
            ,["cu.estado", 1]
            ,["e.estado", 1]
        ])->orderBy('c.nombre', 'asc')->get();

        return $query;
    }
}
