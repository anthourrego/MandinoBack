<?php

namespace App\Http\Controllers;

use App\Models\certificados;
use Illuminate\Http\Request;
use App\Http\Requests\StorecertificadosRequest;
use App\Http\Requests\UpdatecertificadosRequest;
use DB;
use Illuminate\Http\File as FileDos;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\PDF;

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

    public function datosVariables($retornar = 0) {
        $path = resource_path($this->urlVar);

        $file = File::get($path);

        if ($retornar == 1) {
            return $file;
        } else {
            $type = File::mimeType($path);
    
            $response = Response::make($file, 200);
            $response->header("Content-Type", $type); 
    
            return $response;
        }
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

            if($certificado->save()){

                try {
                    $pathView = base_path("resources/views/certificados");

                    if (!file_exists($pathView)) {
                        mkdir($pathView, 0777, true);
                    }

                    $pathView .= "/$certificado->id.php";

                    $file = fopen($pathView, "w");

                    fwrite($file, '');

                    fclose($file);

                    $resp["success"] = true;
                    $resp["msj"] = "Se ha creado el certificado correctamente.";
                } catch (Exception $e) {
                    $resp["msj"] = "No se ha creado el certificado " . $request->nombre;
                }

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

    public function guardarEstructura(Request $request) {

        $data = json_decode($this->datosVariables(1));

        $path = storage_path("app/public/certificados/$request->idCertificado/vista.php");
        
        try {
            Storage::put("public/certificados/$request->idCertificado/estructura.tpl", $request->estructura);
        
            foreach($data as $key => $var) {
                $request->estructura = str_replace($var->valorRepor, "<?= $$var->valorDB; ?>", $request->estructura);
            }

            Storage::put("public/certificados/$request->idCertificado/vista.php", $request->estructura);

            $pathView = base_path("resources/views/certificados");

            if (!file_exists($pathView)) {
                mkdir($pathView, 0777, true);
            }

            $pathView .= "/$request->idCertificado.php";

            $file = fopen($pathView, "w");

            fwrite($file, $request->estructura);

            fclose($file);

        } catch (Exception $e) {
            return array(
                "valido" => 0,
                "mensaje" => "No fue posible modificar el certificado",
                "error" => $e
            );
        }
        
        return array(
            "valido" => 1,
            "mensaje" => "Modificado correctamente"
        );
    }

    public function obtenerEstructura($id) {

        $contents = "";

        if (Storage::disk('public')->exists("certificados/$id/estructura.tpl")) {
            $contents = Storage::get("public/certificados/$id/estructura.tpl");
        }

        return array(
            "valido" => 1,
            "contenido" => $contents
        );

    }

    public function verCertificado($id, $download, $prueba) {
        
        $path = "certificados/$id";

        $info = [
            "NombreCurso" => "Hoalita",
            "CantUnidades" => 2,
            "DocumentoEstudiante" => 1234567890,
            "FechaFinalizacion" => "2022-04-08",
            "NombreEstudiante" => "Alejandro"
        ];

        $vars = [];

        foreach ($info as $key => $value) {
            $text = $value;
            if ($prueba == 1) {
                $text = "xxxxx";
            }
            $vars[$key] = $text;
        }

        if ($download == 1) {
            return PDF::loadView($path, $vars)->download();
        } else {
            return PDF::loadView($path, $vars)->stream();
        }
    }

    // listado certificados
    public function listarCertificadosDisponibles($idUser) {

        $lecProg = DB::table('lecciones_progreso_usuarios')
            ->selectRaw('
                lecciones_progreso_usuarios.fk_leccion
                , lecciones_progreso_usuarios.fecha_completado'
            )->where('lecciones_progreso_usuarios.fk_user', $idUser)
            ->whereNotNull('lecciones_progreso_usuarios.fecha_completado');

        $lecciones = DB::table('lecciones_unidades')
            ->selectRaw('IF(
                COUNT(*) = (
                    IF(progLec.fecha_completado, COUNT(*), 0)
                ), 1, 0) AS Completa,
                lecciones_unidades.fk_unidad
            ')
            ->leftJoinSub($lecProg, "progLec", function ($join) {
                $join->on("lecciones_unidades.fk_leccion", "=", "progLec.fk_leccion");
            })
            ->where('lecciones_unidades.estado', 1)
            ->groupBy('lecciones_unidades.fk_unidad');

        $resultado = DB::table('unidades_cursos')
            ->select(
                "unidades_cursos.fk_curso",
                "unidades.id AS unidadId",
                "unidades.nombre AS text",
                "unidades.color AS color",
                "lecciones.Completa",
                "certificados.id AS idCertificado"
            )
            ->selectRaw("CONCAT('uni', unidades.id) AS idList")
            ->join("unidades", function ($join) {
                $join->on('unidades_cursos.fk_unidad', 'unidades.id')->where('unidades.estado', 1);
            })
            ->leftjoin("certificados", function ($join) {
                $join->on('unidades_cursos.fk_unidad', 'certificados.fk_unidad')->where('certificados.estado', 1);
            })
            ->joinSub($lecciones, "lecciones", function ($join) {
                $join->on("unidades_cursos.fk_unidad", "lecciones.fk_unidad")->where("lecciones.Completa", 1);
            })
            ->where('unidades_cursos.estado', 1)
            ->get();
            
        $cursos = array();

        foreach ($resultado as $key => $result) {

            $enc = array_search($result->fk_curso, array_column($cursos, 'cursoId'));

            if ($enc == false) {

                $curso = DB::table('escuelas_cursos')
                    ->select(
                        "escuelas_cursos.fk_escuela",
                        "cursos.id AS cursoId",
                        "cursos.nombre AS text",
                        "certificados.id AS idCertificado"
                    )
                    ->selectRaw("CONCAT('cur', cursos.id) AS idList")
                    ->join("cursos", function ($join) use ($result) {
                        $join->on('escuelas_cursos.fk_curso', 'cursos.id')->where('cursos.estado', 1)->where("cursos.id", $result->fk_curso);
                    })
                    ->leftjoin("certificados", function ($join) use ($result) {
                        $join->on('escuelas_cursos.fk_curso', 'certificados.fk_curso')->where('certificados.estado', 1)->where("certificados.fk_unidad", $result->unidadId);
                    })
                    ->where('escuelas_cursos.estado', 1)
                    ->get()->first();

                $curso->totalHijos = DB::table('unidades_cursos')->where('unidades_cursos.fk_curso', $result->fk_curso)->where('unidades_cursos.estado', 1)->count();

                $curso->children = [];
                array_push($curso->children, $result);
                array_push($cursos, $curso);
            } else {
                array_push($cursos[$enc]->children, $result);
            }
        }

        $escuelas = array();
        foreach ($cursos as $llave => $cur) {

            $enc = array_search($cur->fk_escuela, array_column($escuelas, 'escuelaId'));

            if ($enc == false) {

                $escuela = DB::table('escuelas')
                    ->select(
                        "escuelas.id AS escuelaId",
                        "escuelas.nombre AS text",
                        "certificados.id AS idCertificado"
                    )
                    ->selectRaw("CONCAT('esc', escuelas.id) AS idList")
                    ->leftjoin("certificados", function ($join) use ($cur) {
                        $join->on('escuelas.id', 'certificados.fk_escuela')->where('certificados.estado', 1)->where("certificados.fk_curso", $cur->cursoId);
                    })
                    ->where('escuelas.estado', 1)
                    ->get()->first();

                $escuela->totalHijos = DB::table('escuelas_cursos')->where('escuelas_cursos.fk_escuela', $cur->fk_escuela)->where('escuelas_cursos.estado', 1)->count();

                $escuela->children = [];
                array_push($escuela->children, $cur);
                array_push($escuelas, $escuela);
            } else {
                array_push($escuelas[$enc]->children, $cur);
            }
        }
        return $escuelas;
    }

}