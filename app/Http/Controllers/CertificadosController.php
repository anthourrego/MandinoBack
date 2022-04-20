<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorecertificadosRequest;
use App\Http\Requests\UpdatecertificadosRequest;
use App\Models\certificados;
use App\Models\User;
use Barryvdh\DomPDF\Facade\PDF;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\File as FileDos;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

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
            ->leftjoin("cursos AS cu", function ($join) {
                $join->on('c.fk_curso', 'cu.id');
            })
            ->leftjoin("unidades AS u", function ($join) {
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

    public function verCertificado($id, $download, $prueba, $usuario) {

        $certificado = certificados::where('id', $id)->get()->first();
        
        $datos = DB::table("users")
            ->select(
                "users.nombre AS NombreEstudiante"
                ,"users.nro_documento AS DocumentoEstudiante"
                ,"users.email AS EmailEstudiante"
                ,"users.telefono AS TelefonoEstudiante"
            )
            ->where("users.id", $usuario)
            ->get()->first();

        if (!isset($certificado->id) || !isset($datos->NombreEstudiante)) {
            if ($prueba == 1) {
                $datos = (object) array();
                $datos->NombreEstudiante = "";
                $datos->DocumentoEstudiante = "";
                $datos->EmailEstudiante = "";
                $datos->TelefonoEstudiante = "";
            } else {
                return '<h1>No se encontro información registrada</h1>';
            }
        }

        $datos->NombreUnidad = '';
        $datos->CantidadLecciones = '';
        if (!is_null($certificado->fk_unidad)) {

            $cantLecciones = DB::table('lecciones_unidades')
                ->selectRaw('COUNT(*) AS cantLecciones, lecciones_unidades.fk_unidad')
                ->where('lecciones_unidades.estado', 1)
                ->groupBy('lecciones_unidades.fk_unidad');

            $leccion = DB::table("unidades")
                ->select(
                    "unidades.nombre AS NombreUnidad"
                    ,"lecCant.cantLecciones AS CantidadLecciones"
                )
                ->leftJoinSub($cantLecciones, "lecCant", function ($join) {
                    $join->on("unidades.id", "=", "lecCant.fk_unidad");
                })
                ->where("unidades.id", $certificado->fk_unidad)
                ->get()->first();

            $datos = (object) array_merge((array) $datos, (array) $leccion);
        }

        $datos->NombreCurso = '';
        $datos->CantidadUnidades = '';
        if (!is_null($certificado->fk_curso)) {
            
            $cantUnidades = DB::table('unidades_cursos')
                ->selectRaw('COUNT(*) AS cantUnidades, unidades_cursos.fk_curso')
                ->where('unidades_cursos.estado', 1)
                ->groupBy('unidades_cursos.fk_curso');

            $curso = DB::table("cursos")
                ->select(
                    "cursos.nombre AS NombreCurso"
                    ,"uniCant.cantUnidades AS CantidadUnidades"
                )
                ->leftJoinSub($cantUnidades, "uniCant", function ($join) {
                    $join->on("cursos.id", "=", "uniCant.fk_curso");
                })
                ->where("cursos.id", $certificado->fk_curso)
                ->get()->first();

            $datos = (object) array_merge((array) $datos, (array) $curso);
        }

        $cantCursos = DB::table('escuelas_cursos')
                ->selectRaw('COUNT(*) AS cantCursos, escuelas_cursos.fk_escuela')
                ->where('escuelas_cursos.estado', 1)
                ->groupBy('escuelas_cursos.fk_escuela');

        $escuela = DB::table("escuelas")
            ->select(
                "escuelas.nombre AS NombreEscuela"
                ,"curCant.cantCursos AS CantidadCursos"
            )
            ->leftJoinSub($cantCursos, "curCant", function ($join) {
                $join->on("escuelas.id", "=", "curCant.fk_escuela");
            })
            ->where("escuelas.id", $certificado->fk_escuela)
            ->get()->first();

        $datos = (object) array_merge((array) $datos, (array) $escuela);

        date_default_timezone_set('America/Bogota');
        setlocale(LC_TIME, 'spanish');
        $datos->FechaGenerado = strftime("%d de %B de %Y %H:%M");

        $datos->FechaFinalizacion = '';

        $vars = [];
        
        foreach ($datos as $key => $value) {
            $text = $value;
            if ($prueba == 1) {
                $text = "xxxxx";
            }
            $vars[$key] = $text;
        }
        
        $path = "certificados/$id";
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
                    COUNT(*) = COUNT(progLec.fecha_completado), 1, 0
                ) AS Completa,
                lecciones_unidades.fk_unidad
            ')
            ->selectRaw('progLec.fecha_completado')
            ->leftJoinSub($lecProg, "progLec", function ($join) {
                $join->on("lecciones_unidades.fk_leccion", "progLec.fk_leccion");
            })
            ->where('lecciones_unidades.estado', 1)
            ->groupBy('lecciones_unidades.fk_unidad');

        $certificados = certificados::where('estado', 1)->get();

        foreach ($certificados as $key => $certifi) {

            // Validamos la unidad que este completa para ese usuario
            if (!is_null($certifi->fk_unidad)) {
                $unidad = $this->validarLecciones($lecciones, $certifi->fk_unidad);
                
                if ($unidad === false) {
                    unset($certificados[$key]);
                }
                continue;
            }

            // Validamos el curso que este completo para ese usuario
            if (is_null($certifi->fk_unidad) && !is_null($certifi->fk_curso)) {
                
                $curso = $this->unidadesCursos($certifi->fk_curso, $lecciones);

                if ($curso === false) {
                    unset($certificados[$key]);
                }
                continue;
            }

            // Validamos la escuela que este completa para ese usuario
            if (is_null($certifi->fk_curso)) {

                $cursos = DB::table('escuelas_cursos')
                    ->select("cursos.id AS cursoId")
                    ->join("cursos", function ($join) {
                        $join->on('escuelas_cursos.fk_curso', 'cursos.id')->where('cursos.estado', 1);
                    })
                    ->where('escuelas_cursos.estado', 1)
                    ->where('escuelas_cursos.fk_escuela', $certifi->fk_escuela)
                    ->get();

                if (count($cursos) > 0) {
                    foreach ($cursos as $ke => $curss) {
                        
                        $curso = $this->unidadesCursos($curss->cursoId, $lecciones);
    
                        if ($curso === false) {
                            unset($certificados[$key]);
                            continue;
                        }
                    }
                } else {
                    unset($certificados[$key]);
                    continue;
                }
            }
        }

        $datos = [];
        foreach ($certificados as $key => $value) $datos[] = $value;

        return $datos;
    }

    private function unidadesCursos($curso, $lecciones) {

        $unidads = DB::table('unidades_cursos')
            ->select("unidades.id AS unidadId")
            ->join("unidades", function ($join) {
                $join->on('unidades_cursos.fk_unidad', 'unidades.id')->where('unidades.estado', 1);
            })
            ->where('unidades_cursos.estado', 1)
            ->where('unidades_cursos.fk_curso', $curso)
            ->get();

        foreach ($unidads as $ke => $uni) {
            
            $unidad = $this->validarLecciones($lecciones, $uni->unidadId);

            if ($unidad === false) return $unidad;

        }
        return true;
    }

    private function validarLecciones($lecciones, $unidadId) {
        
        $unidad = DB::table('unidades')
            ->select("lecciones.*")
            ->joinSub($lecciones, "lecciones", function ($join) {
                $join->on("unidades.id", "lecciones.fk_unidad")->where("lecciones.Completa", 1);
            })
            ->where('unidades.estado', 1)
            ->where('unidades.id', $unidadId)
            ->get()->first();

        if (isset($unidad->Completa) && $unidad->Completa == 1) {
            return true;
        }
        return false;
    }

}