<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class DashboardController extends Controller
{
    public function obtenerTotales(Request $request) {

        $totales = [];
        $totalesLecciones = [];

        $totalTipoLecciones = DB::table('lecciones')
            ->selectRaw("
                COUNT(*) AS Total
                , SUM(IF(tipo = 1, 1, 0)) AS TotalTexto
                , SUM(IF(tipo = 2, 1, 0)) AS TotalEvaluaciones
                , SUM(IF(tipo = 3, 1, 0)) AS TotalVideos
                , SUM(IF(tipo = 4, 1, 0)) AS TotalJuegos
            ")
            ->where("estado", 1)
            ->first(); 

        $totales[] = (object) array(
            "color" => "bg-primary",
            "total" => DB::table('users')->where("estado", 1)->count(),
            "titulo" => "Usuarios Registrados",
            "icono" => "fas fa-users",
            "ir" => "administrativo/usuarios"
        );

        $totales[] = (object) array(
            "color" => "bg-secondary",
            "total" => DB::table('perfiles')->where("estado", 1)->count(),
            "titulo" => "Perfiles",
            "icono" => "fas fa-id-badge",
            "ir" => "administrativo/perfiles"
        );

        $totales[] = (object) array(
            "color" => "bg-light",
            "total" => DB::table('toma_controls')->where("estado", 1)->count(),
            "titulo" => "Videos Toma Control",
            "icono" => "fas fa-video",
            "ir" => "administrativo/toma-control"
        );

        $totales[] = (object) array(
            "color" => "bg-success",
            "total" => DB::table('escuelas')->where("estado", 1)->count(),
            "titulo" => "Escuelas",
            "icono" => "fas fa-school",
            "ir" => "administrativo/escuelas"
        );

        $totales[] = (object) array(
            "color" => "bg-light",
            "total" => DB::table('cursos')->where("estado", 1)->count(),
            "titulo" => "Cursos",
            "icono" => "fas fa-graduation-cap",
            "ir" => "administrativo/cursos"
        );

        $totales[] = (object) array(
            "color" => "bg-secondary",
            "total" => DB::table('unidades')->where("estado", 1)->count(),
            "titulo" => "Unidades",
            "icono" => "fas fa-layer-group",
            "ir" => "administrativo/unidades"
        );

        $totales[] = (object) array(
            "color" => "bg-primary",
            "total" => $totalTipoLecciones->Total,
            "titulo" => "Lecciones",
            "icono" => "fas fa-list-ol",
            "ir" => "administrativo/lecciones"
        );

        $totalesLecciones[] = (object) array(
            "color" => "bg-info",
            "total" => $totalTipoLecciones->TotalTexto,
            "titulo" => "Texto",
            "icono" => "fas fa-text-height",
            "ir" => "administrativo/lecciones"
        );

        $totalesLecciones[] = (object) array(
            "color" => "bg-warning",
            "total" => $totalTipoLecciones->TotalEvaluaciones,
            "titulo" => "Evaluaciones",
            "icono" => "fas fa-question",
            "ir" => "administrativo/lecciones"
        );

        $totalesLecciones[] = (object) array(
            "color" => "bg-danger",
            "total" => $totalTipoLecciones->TotalVideos,
            "titulo" => "Videos",
            "icono" => "fas fa-play",
            "ir" => "administrativo/lecciones"
        );

        $totalesLecciones[] = (object) array(
            "color" => "bg-success",
            "total" => $totalTipoLecciones->TotalJuegos,
            "titulo" => "Juegos",
            "icono" => "fas fa-layer-group",
            "ir" => "administrativo/lecciones"
        );

        return array(
            "totales" => $totales
            , "totalesLecciones" => $totalesLecciones
        );
    }

    public function obtenerComparaciones(Request $request) {

        $leccionesUno = DB::table('lecciones_progreso_usuarios')
            ->selectRaw("
                COUNT(*) AS Total
                , CONVERT(fecha_completado, DATE) AS Fecha
            ")
            ->whereRaw("CONVERT(fecha_completado, DATE) BETWEEN '$request->inicioUltSemana' AND '$request->diaActual'")
            ->groupByRaw("CONVERT(fecha_completado, DATE)")
            ->get();

        $leccionesDos = DB::table('lecciones_progreso_usuarios')
            ->selectRaw("
                COUNT(*) AS Total
                , CONVERT(fecha_completado, DATE) AS Fecha
            ")
            ->whereRaw("CONVERT(fecha_completado, DATE) BETWEEN '$request->inicioUltAntes' AND '$request->inicioUltSemana'")
            ->groupByRaw("CONVERT(fecha_completado, DATE)")
            ->get();

        $tomaControlUno = DB::table('toma_control_visualizaciones')
            ->selectRaw("
                COUNT(*) AS Total
                , CONVERT(created_at, DATE) AS Fecha
            ")
            ->whereRaw("completo = 1 AND CONVERT(created_at, DATE) BETWEEN '$request->inicioUltSemana' AND '$request->diaActual'")
            ->groupByRaw("CONVERT(created_at, DATE)")
            ->get();

        $tomaControlDos = DB::table('toma_control_visualizaciones')
            ->selectRaw("
                COUNT(*) AS Total
                , CONVERT(created_at, DATE) AS Fecha
            ")
            ->whereRaw("completo = 1 AND CONVERT(created_at, DATE) BETWEEN '$request->inicioUltAntes' AND '$request->inicioUltSemana'")
            ->groupByRaw("CONVERT(created_at, DATE)")
            ->get();

        
        $tomaControlMasVistos = DB::table('toma_control_visualizaciones')
            ->selectRaw("
                COUNT(*) AS Total
                , toma_controls.nombre AS Nombre
            ")
            ->join('toma_controls', 'toma_control_visualizaciones.fk_toma_control', '=', 'toma_controls.id')
            ->whereRaw("completo = 1 AND CONVERT(toma_control_visualizaciones.created_at, DATE) BETWEEN '$request->inicioUltSemana' AND '$request->diaActual'")
            ->groupBy("fk_toma_control", "toma_controls.nombre")
            ->orderByRaw("COUNT(*)")
            ->limit(5)
            ->get();

        $leccionesTopUser = DB::table('lecciones_progreso_usuarios')
            ->selectRaw("
                COUNT(*) AS Total
                , users.nombre AS Nombre
            ")
            ->join('users', 'lecciones_progreso_usuarios.fk_user', '=', 'users.id')
            ->whereRaw("CONVERT(fecha_completado, DATE) BETWEEN '$request->inicioUltSemana' AND '$request->diaActual'")
            ->groupBy("fk_user", "users.nombre")
            ->orderByRaw("COUNT(*)")
            ->limit(5)
            ->get();


        return array(
            "semUno" => $leccionesUno
            , "semDos" => $leccionesDos
            , "semUnoToma" => $tomaControlUno
            , "semDosToma" => $tomaControlDos
            , "tomaControlVisualTop" => $tomaControlMasVistos
            , "leccionesTopUser" => $leccionesTopUser
        );

    }
}
