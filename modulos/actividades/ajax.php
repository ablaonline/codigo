<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Actividades
 * @author      Pablo Andrés Vélez Vidal
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 Colombo-Americano
 * @version     0.1
 *
 * */
global $url_accion, $forma_id, $forma_procesar, $forma_procesar, $forma_datos, $forma_id_curso, $forma_idActividad, $forma_idRespuesta, $forma_id_actividad;

if (isset($url_accion)) {
    switch ($url_accion) {
        case "add" : $datos = ($forma_procesar) ? $forma_datos : array();
            adicionarActividad($datos, $forma_id_curso);
            break;

        case "editRegister" : $datos = ($forma_procesar) ? $forma_datos : array();
            modificarActividad($forma_id, $datos, $forma_id_curso);
            break;

        case "deleteRegister" : $confirmado = ($forma_procesar) ? true : false;
            eliminarActividad($forma_id, $confirmado);
            break;

        case "delete" : $confirmado = ($forma_procesar) ? true : false;
            eliminarRespuestaActividad($forma_id, $confirmado);
            break;      

        case "search" : $datos = ($forma_procesar) ? $forma_datos : array();
            buscarActividad($forma_datos);
            break;

        case "see" : consultarActividad($forma_idActividad);
            break;

        case "seeGrade" : consultarCalificacion($forma_idRespuesta);
            break;     

        case "responseActivity" : $datos = ($forma_procesar) ? $forma_datos : array();
            responderActividad($datos, $forma_id_actividad);
            break;

        case "listResponses" : listarRespuestas($forma_id_actividad);
            break;

        case "seeResponse" : consultarRespuesta($forma_idRespuesta);
            break;

        case "gradeResponse" : $datos = ($forma_procesar) ? $forma_datos : array();
            calificarRespuesta($forma_idRespuesta, $datos);
            break;

    }

}

/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @param type $id
 * @param type $datos 
 */
function consultarActividad($id) {
    global $textos, $configuracion, $sql, $sesion_usuarioSesion;

    $actividadCurso = new ActividadCurso($id);
    $respuesta = array();

    $rbtnArchivo1 = true; //Pongo valores por defecto de si van o no
    $rbtnVideo1 = false; //a estar chequeados u ocultos ciertos campos segun sea un archivo o un video
    $textoArchivoActividad1 = ''; //por defecto supongo que el "archivo" almacenado es un archivo guardado en el servidor de ABLA
    $claseCampoArchivo1 = '';
    $claseTextoArchivo1 = 'oculto';

    $tipo = Recursos::getTipoArchivo($actividadCurso->archivoActividad1); //traigo el tipo del archivo

    if ($tipo == 'video') {//verifico si es un video de youtube
        $rbtnArchivo1 = false; //y según el tipo del archivo doy los valores correspondientes
        $rbtnVideo1 = true;
        $textoArchivoActividad1 = $actividadCurso->archivoActividad1;
        $claseCampoArchivo1 = 'oculto';
        $claseTextoArchivo1 = '';
    }

    $rbtnArchivo2 = true;
    $rbtnVideo2 = false;
    $textoArchivoActividad2 = '';
    $claseCampoArchivo2 = '';
    $claseTextoArchivo2 = 'oculto';

    $tipo2 = Recursos::getTipoArchivo($actividadCurso->archivoActividad2);

    if ($tipo2 == 'video') {
        $rbtnArchivo2 = false;
        $rbtnVideo2 = true;
        $textoArchivoActividad2 = $actividadCurso->archivoActividad2;
        $claseCampoArchivo2 = 'oculto';
        $claseTextoArchivo2 = '';
    }


    $codigo = HTML::campoOculto("procesar", "true");
    $codigo .= HTML::campoOculto("id", $id);
    $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior subtitulo");
    $codigo .= HTML::parrafo($actividadCurso->titulo, '');
    $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior subtitulo");
    $codigo .= HTML::parrafo($actividadCurso->descripcion, "");
    $codigo .= HTML::parrafo($textos->id("FECHA_LIMITE_ENTREGA_ACTIVIDAD"), "negrilla margenSuperior subtitulo");
    $codigo .= HTML::parrafo($actividadCurso->fechaLimite, '');

    if ($actividadCurso->archivoActividad1 != '') {
        $codigo .= HTML::parrafo($textos->id("ARCHIVO_ACTIVIDAD1") . ": " . $actividadCurso->enlaceArchivoActividad1, "negrilla subtitulo margenSuperior", "");
    }

    if ($actividadCurso->archivoActividad2 != '') {
        $codigo .= HTML::parrafo($textos->id("ARCHIVO_ACTIVIDAD2") . ": " . $actividadCurso->enlaceArchivoActividad2, "negrilla subtitulo margenSuperiorDoble");
    }

    if($actividadCurso->diasRestantes >= 0){
        $resuelta = $sql->existeItem('respuestas_actividades', 'id_usuario', $sesion_usuarioSesion->id, 'id_actividad = "'.$actividadCurso->id.'"');
        if($resuelta){
            $imgResuelta = HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "/check_verde.png", ' imgIndicadorRespuesta verActividad manito medioMargenIzquierda margenSuperior', 'checkVerde', array('ayuda' => $textos->id('ACTIVIDAD_RESUELTA'), 'idActividad' => $actividad->id));
            $codigo .= HTML::frase($imgResuelta.$textos->id('ACTIVIDAD_RESUELTA'), 'fraseInfoRespuesta', 'fraseInfoRespuesta');

            $calificado = $sql->obtenerValor('respuestas_actividades', 'id', 'id_usuario = "'.$sesion_usuarioSesion->id.'" AND id_actividad = "'.$actividadCurso->id.'"');
	    $nota = $sql->obtenerValor('respuestas_actividades', 'nota', 'id = "'.$calificado.'" ');
            if($nota){
                $codigo .= HTML::parrafo(HTML::frase('* '.$textos->id('VER_CALIFICACION'), 'estiloEnlace subtitulo consultarCalificacion margenSuperiorDoble', '', array('idRespuesta' => $calificado, 'ruta' => '/ajax/activities/seeGrade')), 'margenSuperiorDoble');
            } else {
                $codigo .= HTML::parrafo('* '.$textos->id('NO_HAS_SIDO_CALIFICADO'), 'margenSuperiorDoble subtitulo negrilla');
            }

        } else {
            $codigo .= HTML::botonAjax('lapiz', $textos->id('RESPONDER_ACTIVIDAD'), '/ajax/activities/responseActivity', array('id_actividad' => $id), 'margenSuperiorDoble');
            
        }   
    } else {
        $codigo .= HTML::parrafo($textos->id('FECHA_LIMITE_ALCANZADA'), 'subtitulo letraRoja margenSuperior', '');
    }

    $respuesta["generar"] = true;
    $respuesta['cargarJs'] = true;
    $respuesta['archivoJs'] = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['javascript'] . '/modulos/cursos/funcionesConsultarActividad.js';
    $respuesta["codigo"] = $codigo;
    $respuesta["destino"] = "#cuadroDialogo";
    $respuesta["titulo"] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("CONSULTAR_ITEM"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
    $respuesta["ancho"] = 780;
    $respuesta["alto"] = 600;


    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @param type $id
 * @param type $datos 
 */
function consultarRespuesta($id) {
    global $textos, $configuracion, $sesion_usuarioSesion;

    $respuestaActividad = new RespuestaActividad($id);
    $respuesta = array();

    $rbtnArchivo1 = true; //Pongo valores por defecto de si van o no
    $rbtnVideo1 = false; //a estar chequeados u ocultos ciertos campos segun sea un archivo o un video
    $textoArchivoRespuesta1 = ''; //por defecto supongo que el "archivo" almacenado es un archivo guardado en el servidor de ABLA
    $claseCampoArchivo1 = '';
    $claseTextoArchivo1 = 'oculto';
    $tipo = Recursos::getTipoArchivo($respuestaActividad->archivoRespuesta1); //traigo el tipo del archivo
    if ($tipo == 'video') {//verifico si es un video de youtube
        $rbtnArchivo1 = false; //y según el tipo del archivo doy los valores correspondientes
        $rbtnVideo1 = true;
        $textoArchivoRespuesta1 = $respuestaActividad->archivoRespuesta1;
        $claseCampoArchivo1 = 'oculto';
        $claseTextoArchivo1 = '';
    }

    $rbtnArchivo2 = true;
    $rbtnVideo2 = false;
    $textoArchivoRespuesta2 = '';
    $claseCampoArchivo2 = '';
    $claseTextoArchivo2 = 'oculto';
    $tipo2 = Recursos::getTipoArchivo($respuestaActividad->archivoRespuesta2);
    if ($tipo2 == 'video') {
        $rbtnArchivo2 = false;
        $rbtnVideo2 = true;
        $textoArchivoRespuesta2 = $respuestaActividad->archivoRespuesta2;
        $claseCampoArchivo2 = 'oculto';
        $claseTextoArchivo2 = '';
    }



    $pestana1 = HTML::campoOculto("id", $id);
    $pestana1 .= HTML::parrafo($textos->id("AUTOR"), "negrilla margenSuperior subtitulo");
    $pestana1 .= HTML::parrafo(HTML::enlace($respuestaActividad->usuario . HTML::imagen($respuestaActividad->imagenAutor, 'medioMargenIzquierda miniaturaImagenUsuariosNormal'), HTML::urlInterna("USUARIOS", $respuestaActividad->usuario, true)), '', '', array('target' => '_blank'));
    $pestana1 .= HTML::parrafo($textos->id("FECHA_PUBLICACION"), "negrilla margenSuperior subtitulo");
    $pestana1 .= HTML::parrafo(date('D, d M Y', $respuestaActividad->fechaPublicacion), "");    
    $pestana1 .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior subtitulo");
    $pestana1 .= HTML::parrafo($respuestaActividad->titulo, '');
    $pestana1 .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior subtitulo");
    $pestana1 .= HTML::parrafo($respuestaActividad->descripcion, "");
    if ($respuestaActividad->archivoRespuesta1 != '') {
        $pestana1 .= HTML::parrafo($textos->id("ARCHIVO_ACTIVIDAD1") . ": " . $respuestaActividad->enlaceArchivoRespuesta1, "negrilla subtitulo margenSuperior", "");
    }

    if ($respuestaActividad->archivoRespuesta2 != '') {
        $pestana1 .= HTML::parrafo($textos->id("ARCHIVO_ACTIVIDAD2") . ": " . $respuestaActividad->enlaceArchivoRespuesta2, "negrilla subtitulo margenSuperiorDoble");
    }


    if ($sesion_usuarioSesion->id == 0 || $sesion_usuarioSesion->id == $respuestaActividad->actividad->idUsuario) {
        $destino = "/ajax/activities/gradeResponse";

        $pestana2 .= HTML::parrafo($textos->id("CALIFICAR_RESPUESTA"), "negrilla margenSuperior");
        $selectorCalificacion = '';
        if ($respuestaActividad->actividad->tipoCalificacion == '1') {
            $explicacionTipoCalificacion = HTML::frase($textos->id('EXPLICACION_CALIFICACION_TIPO'.$respuestaActividad->actividad->tipoCalificacion), 'margenIzquierda');
            $arregloTipoCalificacion = array($textos->id('CALIFICACION_LETRA_5') => $textos->id('CALIFICACION_LETRA_5'), $textos->id('CALIFICACION_LETRA_4') => $textos->id('CALIFICACION_LETRA_4'), $textos->id('CALIFICACION_LETRA_3') => $textos->id('CALIFICACION_LETRA_3'), $textos->id('CALIFICACION_LETRA_2') => $textos->id('CALIFICACION_LETRA_2'), $textos->id('CALIFICACION_LETRA_1') => $textos->id('CALIFICACION_LETRA_1'));
            $selectorCalificacion .= HTML::listaDesplegable('datos[nota]', $arregloTipoCalificacion, $respuestaActividad->nota, 'selectorTipoCalificacion', 'selectorTipoCalificacion', '').$explicacionTipoCalificacion;
        } else if ($respuestaActividad->actividad->tipoCalificacion == '2') {
            $explicacionTipoCalificacion = HTML::frase($textos->id('EXPLICACION_CALIFICACION_TIPO'.$respuestaActividad->actividad->tipoCalificacion), 'margenIzquierda');
            $arregloTipoCalificacion = array('1' => '  1', '2' => '  2', '3' => '  3', '4' => '  4', '5' => '  5');
            $selectorCalificacion .= HTML::listaDesplegable('datos[nota]', $arregloTipoCalificacion, $respuestaActividad->nota, 'selectorTipoCalificacion margenIzquierdaDoble', 'selectorTipoCalificacion', '').$explicacionTipoCalificacion;
        } else if ($respuestaActividad->actividad->tipoCalificacion == '3') {
            $explicacionTipoCalificacion = HTML::frase($textos->id('EXPLICACION_CALIFICACION_TIPO'.$respuestaActividad->actividad->tipoCalificacion), 'margenIzquierda');
            $selectorCalificacion .= HTML::campoTexto("datos[nota]", 5, 3, $respuestaActividad->nota, 'rangoNumeros margenIzquierdaDoble', '', array('rango' => '0-100')).$explicacionTipoCalificacion;
        }

        $pestana2 .= $selectorCalificacion;
        $pestana2 .= HTML::campoOculto("idRespuesta", $id);
        $pestana2 .= HTML::campoOculto("procesar", "true");
        $pestana2 .= HTML::campoOculto("datos[dialogo]", "", "idDialogo");
        $pestana2 .= HTML::parrafo($textos->id("RETROALIMENTACION"), "negrilla margenSuperior");
        $pestana2 .= HTML::areaTexto("datos[retroalimentacion]", 10, 30, $respuestaActividad->retroalimentacion, "editor");
        $pestana2 .= HTML::parrafo(HTML::boton("chequeo", $textos->id("CALIFICAR_ACTIVIDAD"), "", "", "") . HTML::frase("     " . $textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso"), 'margenSuperior');
        $pestana2 = HTML::forma($destino, $pestana2, "P", true);

        $pestanas = array(
            HTML::frase($textos->id("INFORMACION_RESPUESTA"), "letraBlanca") => $pestana1,
            HTML::frase($textos->id("CALIFICAR_RESPUESTA"), "letraBlanca") => $pestana2
        );


        $codigo .= HTML::pestanas2("", $pestanas);
    } else {
        $codigo .= $pestana1;
    }



    $respuesta["generar"] = true;
    $respuesta['cargarJs'] = true;
    $respuesta['archivoJs'] = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['javascript'] . '/modulos/cursos/funcionesConsultarRespuesta.js';
    $respuesta["codigo"] = $codigo;
    $respuesta["destino"] = "#cuadroDialogo";
    $respuesta["titulo"] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("CONSULTAR_RESPUESTA"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
    $respuesta["ancho"] = 780;
    $respuesta["alto"] = 600;


    Servidor::enviarJSON($respuesta);
}



/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @param type $id
 * @param type $datos 
 */
function consultarCalificacion($id) {
    global $textos;

    $respuestaActividad = new RespuestaActividad($id);
    $respuesta = array();

    $codigo = '';
    $calificacion = str_replace('%1', $respuestaActividad->nota, $textos->id('TEXTO_CALIFICACION'));
    $calificacion = str_replace('%2', $respuestaActividad->actividad->titulo, $calificacion);
    $codigo .= HTML::parrafo($calificacion,  'margenSuperior subtitulo');
    $codigo .= HTML::parrafo($textos->id('CALIFICACION_TIPO'.$respuestaActividad->actividad->tipoCalificacion),  'margenSuperior subtitulo');
    $codigo .= HTML::parrafo($textos->id("RETROALIMENTACION"), "negrilla margenSuperior subtitulo");
    $codigo .= HTML::parrafo($respuestaActividad->retroalimentacion, "margenSuperior");


    $respuesta["generar"] = true;
    $respuesta["codigo"] = $codigo;
    $respuesta["destino"] = "#cuadroDialogo";
    $respuesta["titulo"] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("CONSULTAR_CALIFICACION"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
    $respuesta["ancho"] = 600;
    $respuesta["alto"] = 400;


    Servidor::enviarJSON($respuesta);
}




/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @param type $id
 * @param type $datos 
 */
function calificarRespuesta($id, $datos) {


    $objeto = new RespuestaActividad($id);
    $respuesta = array();
//    print_r($datos);
    $cuadroDialogo = $datos['dialogo'];
    unset($datos['dialogo']);
    $objeto->modificarCalificacion($datos);

    $respuesta["error"] = false;
    $respuesta['accion'] = 'insertar';
    $respuesta['calificarRespuesta'] = true;
    $respuesta['cuadroDialogo'] = $cuadroDialogo;
    $respuesta['idContenedor'] = '#contenedorRespuesta' . $id;

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function adicionarActividad($datos = array(), $idCurso = NULL) {
    global $textos;

    $actividadCurso = new ActividadCurso();
    $destino = "/ajax" . $actividadCurso->urlBase . "/add";
    $respuesta = array();

    if (empty($datos)) {

        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos[id_curso]", $idCurso);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 30, "", "editor");

        $titulo = HTML::parrafo($textos->id("SELECCIONAR_TIPO_CALIFICACION"), "negrilla margenSuperior");
        $arregloTipoCalificacion = array('1' => $textos->id('TIPO_CALIFICACION_1'), '2' => $textos->id('TIPO_CALIFICACION_2'), '3' => $textos->id('TIPO_CALIFICACION_3'));
        $selectorTipoCalificacion = HTML::listaDesplegable('datos[tipo_calificacion]', $arregloTipoCalificacion, '0', 'selectorTipoCalificacion', 'selectorTipoCalificacion', '');

        $explicacionCalificacion1 = HTML::contenedor($textos->id('EXPLICACION_CALIFICACION_TIPO1'), 'explicacionCalificacion', 'explicacionCalificacion1');
        $explicacionCalificacion2 = HTML::contenedor($textos->id('EXPLICACION_CALIFICACION_TIPO2'), 'explicacionCalificacion oculto', 'explicacionCalificacion2');
        $explicacionCalificacion3 = HTML::contenedor($textos->id('EXPLICACION_CALIFICACION_TIPO3'), 'explicacionCalificacion oculto', 'explicacionCalificacion3');
        $explicacionCalificacion = HTML::contenedor($titulo . $selectorTipoCalificacion . $explicacionCalificacion1 . $explicacionCalificacion2 . $explicacionCalificacion3, 'explicacionCalificacionGeneral');


        $codigo .= HTML::parrafo($textos->id("FECHA_LIMITE_ENTREGA_ACTIVIDAD"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[fecha_limite]", 12, 12, "", "fechaReciente", "fechaLimite", array("alt" => $textos->id("SELECCIONE_FECHA_LIMITE_ENTREGA_RESPUESTAS"))) . $explicacionCalificacion;
        $codigo .= HTML::parrafo($textos->id("ARCHIVO_ACTIVIDAD1"), "negrilla subtitulo margenSuperior");
        $codigo .= HTML::radioBoton('recurso1', true, '', 'otro_archivo', array(), 'rbtnOtroArchivo') . HTML::frase($textos->id("OTRO_ARCHIVO"), 'negrilla margenSuperior ');
        $codigo .= HTML::radioBoton('recurso1', false, 'margenIzquierdaDoble', 'video_youtube', array(), 'rbtnVideo') . HTML::frase($textos->id("VIDEO_DE_YOUTUBE"), 'negrilla margenSuperior');
        $codigo .= HTML::parrafo(HTML::campoArchivo('recurso_1', '', 'campoArchivoRecurso1') . HTML::campoTexto('datos[recurso_1]', 40, 250, '', 'oculto txtYoutube', 'campoVideoRecurso1', array("ayuda" => $textos->id("AYUDA_FORMATO_ENLACE_VIDEO"))), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id("ARCHIVO_ACTIVIDAD2"), "negrilla subtitulo margenSuperiorDoble");
        $codigo .= HTML::radioBoton('recurso2', true, '', 'otro_archivo2', array(), 'rbtnOtroArchivo2') . HTML::frase($textos->id("OTRO_ARCHIVO"), 'negrilla margenSuperior ');
        $codigo .= HTML::radioBoton('recurso2', false, 'margenIzquierdaDoble', 'video_youtube2', array(), 'rbtnVideo2') . HTML::frase($textos->id("VIDEO_DE_YOUTUBE"), 'negrilla margenSuperior');
        $codigo .= HTML::parrafo(HTML::campoArchivo('recurso_2', '', 'campoArchivoRecurso2') . HTML::campoTexto('datos[recurso_2]', 40, 255, '', 'oculto txtYoutube', 'campoVideoRecurso2', array("ayuda" => $textos->id("AYUDA_FORMATO_ENLACE_VIDEO"))), 'margenSuperior');
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[notificar_estudiantes]", true) . $textos->id("NOTIFICAR_ESTUDIANTES"), "margenSuperior");

        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk") . HTML::frase("     " . $textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        $codigo = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"] = $codigo;
        $respuesta["titulo"] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_ITEM"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"] = 780;
        $respuesta["alto"] = 600;
    } else {
        $respuesta["error"] = true;


        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");
        } elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");
        } else {

            $idActividad = $actividadCurso->adicionar($datos);
            if ($idActividad) {

                $respuesta["error"] = false;
                $respuesta["accion"] = "recargar";
            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @param type $id
 * @param type $datos 
 */
function modificarActividad($id, $datos = array()) {
    global $textos, $configuracion;

    $actividadCurso = new ActividadCurso($id);
    $destino = "/ajax" . $actividadCurso->urlBase . "/editRegister";
    $respuesta = array();


    if (empty($datos)) {

        $rbtnArchivo1 = true; //Pongo valores por defecto de si van o no
        $rbtnVideo1 = false; //a estar chequeados u ocultos ciertos campos segun sea un archivo o un video
        $textoArchivoActividad1 = ''; //por defecto supongo que el "archivo" almacenado es un archivo guardado en el servidor de ABLA
        $claseCampoArchivo1 = '';
        $claseTextoArchivo1 = 'oculto';
        $tipo = Recursos::getTipoArchivo($actividadCurso->archivoActividad1); //traigo el tipo del archivo
        if ($tipo == 'video') {//verifico si es un video de youtube
            $rbtnArchivo1 = false; //y según el tipo del archivo doy los valores correspondientes
            $rbtnVideo1 = true;
            $textoArchivoActividad1 = $actividadCurso->archivoActividad1;
            $claseCampoArchivo1 = 'oculto txtYoutube';
            $claseTextoArchivo1 = '';
        }

        $rbtnArchivo2 = true;
        $rbtnVideo2 = false;
        $textoArchivoActividad2 = '';
        $claseCampoArchivo2 = '';
        $claseTextoArchivo2 = 'oculto';
        $tipo1 = Recursos::getTipoArchivo($actividadCurso->archivoActividad2);
        if ($tipo1 == 'video') {
            $rbtnArchivo2 = false;
            $rbtnVideo2 = true;
            $textoArchivoActividad2 = $actividadCurso->archivoActividad2;
            $claseCampoArchivo2 = 'oculto txtYoutube';
            $claseTextoArchivo2 = '';
        }


        $codigo = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::campoOculto("datos[id_curso]", $actividadCurso->idCurso);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255, $actividadCurso->titulo);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 30, $actividadCurso->descripcion, "editor");
        $codigo .= HTML::parrafo($textos->id("FECHA_LIMITE_ENTREGA_ACTIVIDAD"), "negrilla margenSuperior");


        $titulo = HTML::parrafo($textos->id("SELECCIONAR_TIPO_CALIFICACION"), "negrilla margenSuperior");
        $arregloTipoCalificacion = array('1' => $textos->id('TIPO_CALIFICACION_1'), '2' => $textos->id('TIPO_CALIFICACION_2'), '3' => $textos->id('TIPO_CALIFICACION_3'));
        $selectorTipoCalificacion = HTML::listaDesplegable('datos[tipo_calificacion]', $arregloTipoCalificacion, $actividadCurso->tipoCalificacion, 'selectorTipoCalificacion', 'selectorTipoCalificacion', '');

        $explicacionCalificacion1 = HTML::contenedor($textos->id('EXPLICACION_CALIFICACION_TIPO1'), 'explicacionCalificacion', 'explicacionCalificacion1');
        $explicacionCalificacion2 = HTML::contenedor($textos->id('EXPLICACION_CALIFICACION_TIPO2'), 'explicacionCalificacion oculto', 'explicacionCalificacion2');
        $explicacionCalificacion3 = HTML::contenedor($textos->id('EXPLICACION_CALIFICACION_TIPO3'), 'explicacionCalificacion oculto', 'explicacionCalificacion3');
        $explicacionCalificacion = HTML::contenedor($titulo . $selectorTipoCalificacion . $explicacionCalificacion1 . $explicacionCalificacion2 . $explicacionCalificacion3, 'explicacionCalificacionGeneral');




        $codigo .= HTML::campoTexto("datos[fecha_limite]", 12, 12, $actividadCurso->fechaLimite, "fechaReciente", "fechaLimite", array("alt" => $textos->id("SELECCIONE_FECHA_LIMITE_ENTREGA_RESPUESTAS"))) . $explicacionCalificacion;
        $codigo .= HTML::parrafo($textos->id("ARCHIVO_ACTIVIDAD1") . ": " . $actividadCurso->enlaceArchivoActividad1, "negrilla subtitulo margenSuperior");
        $codigo .= HTML::radioBoton('recurso1', $rbtnArchivo1, 'margenSuperiorDoble', 'otro_archivo', array(), 'rbtnOtroArchivo') . HTML::frase($textos->id("OTRO_ARCHIVO"), 'negrilla margenSuperior ');
        $codigo .= HTML::radioBoton('recurso1', $rbtnVideo1, 'margenIzquierdaDoble margenSuperiorDoble', 'video_youtube', array(), 'rbtnVideo') . HTML::frase($textos->id("VIDEO_DE_YOUTUBE"), 'negrilla margenSuperior');
        $codigo .= HTML::parrafo(HTML::campoArchivo('recurso_1', '', 'campoArchivoRecurso1', array('class' => $claseCampoArchivo1)) . HTML::campoTexto('datos[recurso_1]', 40, 250, $textoArchivoActividad1, $claseTextoArchivo1, 'campoVideoRecurso1', array("ayuda" => $textos->id("AYUDA_FORMATO_ENLACE_VIDEO"))), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id("ARCHIVO_ACTIVIDAD2") . ": " . $actividadCurso->enlaceArchivoActividad2, "negrilla subtitulo margenSuperiorDoble");
        $codigo .= HTML::radioBoton('recurso2', $rbtnArchivo2, 'margenSuperiorDoble', 'otro_archivo2', array(), 'rbtnOtroArchivo2') . HTML::frase($textos->id("OTRO_ARCHIVO"), 'negrilla margenSuperior ');
        $codigo .= HTML::radioBoton('recurso2', $rbtnVideo2, 'margenIzquierdaDoble margenSuperiorDoble', 'video_youtube2', array(), 'rbtnVideo2') . HTML::frase($textos->id("VIDEO_DE_YOUTUBE"), 'negrilla margenSuperior');
        $codigo .= HTML::parrafo(HTML::campoArchivo('recurso_2', '', 'campoArchivoRecurso2', array('class' => $claseCampoArchivo2)) . HTML::campoTexto('datos[recurso_2]', 40, 255, $textoArchivoActividad2, $claseTextoArchivo2, 'campoVideoRecurso2', array("ayuda" => $textos->id("AYUDA_FORMATO_ENLACE_VIDEO"))), 'margenSuperior');
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[notificar_estudiantes]", true) . $textos->id("NOTIFICAR_ESTUDIANTES"), "margenSuperior");

        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk") . HTML::frase("     " . $textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        $codigo = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta['cargarJs'] = true;
        $respuesta['archivoJs'] = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['javascript'] . '/modulos/cursos/funcionesConsultarActividad.js';
        $respuesta["codigo"] = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_ITEM"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"] = 780;
        $respuesta["alto"] = 600;
    } else {
        $respuesta["error"] = true;

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");
        } elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");
        } else {

            $idActividad = $actividadCurso->modificar($datos);
            if ($idActividad) {
                $respuesta["error"] = false;
                $respuesta["accion"] = "recargar";
            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Metodo Para eliminar una actividadCurso desde la lista general
 * */
function eliminarActividad($id, $confirmado) {
    global $textos;

    $actividadCurso = new ActividadCurso($id);
    $destino = "/ajax" . $actividadCurso->urlBase . "/deleteRegister";
    $respuesta = array();

    if (!$confirmado) {
        $titulo = HTML::frase($actividadCurso->titulo, "negrilla");
        $titulo = str_replace("%1", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"] = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_ACTIVIDAD"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"] = 350;
        $respuesta["alto"] = 140;
    } else {

        if ($actividadCurso->eliminar()) {
            $respuesta["error"] = false;
            $respuesta["accion"] = "recargar";
        } else {

            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}



/**
 * Metodo Para eliminar una actividadCurso desde la lista general
 * */
function eliminarRespuestaActividad($id, $confirmado) {
    global $textos;

    $respuestaActividad = new RespuestaActividad($id);
    $destino = "/ajax/activities/delete";
    $respuesta = array();

    if (!$confirmado) {
        $titulo = HTML::frase($respuestaActividad->titulo, "negrilla");
        $titulo = str_replace("%1", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"] = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_ACTIVIDAD"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"] = 350;
        $respuesta["alto"] = 140;
    } else {

        if ($respuestaActividad->eliminar()) {
            $respuesta["error"] = false;
            $respuesta["accion"] = "recargar";
        } else {

            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}





/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function responderActividad($datos = array(), $idActividad = NULL) {
    global $textos, $configuracion;

    $objeto = new RespuestaActividad();
    $destino = "/ajax/activities/responseActivity";
    $respuesta = array();

    if (empty($datos)) {

        $codigo = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos[id_actividad]", $idActividad);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255, '', 'selectorHora');
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 5, 60, "", "editor");
        $codigo .= HTML::parrafo($textos->id("ARCHIVO_ACTIVIDAD1"), "negrilla subtitulo margenSuperior");
        $codigo .= HTML::radioBoton('recurso1', true, '', 'otro_archivo', array(), 'rbtnOtroArchivo') . HTML::frase($textos->id("OTRO_ARCHIVO"), 'negrilla margenSuperior ');
        $codigo .= HTML::radioBoton('recurso1', false, 'margenIzquierdaDoble', 'video_youtube', array(), 'rbtnVideo') . HTML::frase($textos->id("VIDEO_DE_YOUTUBE"), 'negrilla margenSuperior');
        $codigo .= HTML::parrafo(HTML::campoArchivo('recurso_1', '', 'campoArchivoRecurso1') . HTML::campoTexto('datos[recurso_1]', 40, 250, '', 'oculto txtYoutube', 'campoVideoRecurso1', array("ayuda" => $textos->id("AYUDA_FORMATO_ENLACE_VIDEO"))), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id("ARCHIVO_ACTIVIDAD2"), "negrilla subtitulo margenSuperiorDoble");
        $codigo .= HTML::radioBoton('recurso2', true, '', 'otro_archivo2', array(), 'rbtnOtroArchivo2') . HTML::frase($textos->id("OTRO_ARCHIVO"), 'negrilla margenSuperior ');
        $codigo .= HTML::radioBoton('recurso2', false, 'margenIzquierdaDoble', 'video_youtube2', array(), 'rbtnVideo2') . HTML::frase($textos->id("VIDEO_DE_YOUTUBE"), 'negrilla margenSuperior');
        $codigo .= HTML::parrafo(HTML::campoArchivo('recurso_2', '', 'campoArchivoRecurso2') . HTML::campoTexto('datos[recurso_2]', 40, 255, '', 'oculto txtYoutube', 'campoVideoRecurso2', array("ayuda" => $textos->id("AYUDA_FORMATO_ENLACE_VIDEO"))), 'margenSuperior');

        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk") . HTML::frase("     " . $textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        $codigo = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta['cargarJs'] = true;
        $respuesta['archivoJs'] = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['javascript'] . '/modulos/cursos/funcionesResponderActividad.js';
        $respuesta["codigo"] = $codigo;
        $respuesta["titulo"] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("RESPONDER_ACTIVIDAD"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"] = 780;
        $respuesta["alto"] = 600;
    } else {
        $respuesta["error"] = true;


        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");
        } elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");
        } else {

            $idActividad = $objeto->adicionar($datos);
            if ($idActividad) {

                $respuesta["error"] = false;
                $respuesta["accion"] = "recargar";
            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Funcion que lista las respuestas de los estudiantes a las actividades
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @param type $id
 * @param type $datos 
 */
function listarRespuestas($idActividad) {
    global $textos, $configuracion, $sesion_usuarioSesion;

    $objeto = new RespuestaActividad();
    $respuesta = array();

    $contenidoRespuesta = '';

    $listaRespuestas = array();

    $cantidadRespuestas = $objeto->contar($idActividad);

    if ($cantidadRespuestas) {


        foreach ($objeto->listar(0, 0, '', 'ra.id_actividad = "' . $idActividad . '" AND ') as $resp) {

            $contenidoRespuesta = '';
            $botones1 = '';
            if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $resp->actividad->curso->idAutor) {
                $botones1 .= HTML::botonEliminarItem($resp->id, '/activities', 'contenedorBotonesListaActividades');
                $botones1 = HTML::contenedor($botones1, "botonesListaActividades", "botonesListaActividades");
            }
            $contenidoRespuesta .= $botones1;
            $contenidoRespuesta .= HTML::imagen($objeto->icono, "flotanteIzquierda  margenDerecha miniaturaLista32px verRespuestaActividad manito", '', array('idRespuesta' => $resp->id));
            $contenidoRespuesta .= HTML::parrafo($resp->titulo, 'estiloEnlace verRespuestaActividad', '', array('idRespuesta' => $resp->id));

            /*if (strlen($resp->descripcion) > 100) {
                $descripcionRespuesta = substr($resp->descripcion, 0, 97) . '...';
            } else {
                $descripcionRespuesta = $resp->descripcion;
            }*/

	    $descripcionRespuesta = '';

            $contenidoRespuesta2 = HTML::parrafo($descripcionRespuesta, '');
            $enlaceUsuario = HTML::enlace($resp->nombreAutor . HTML::imagen($resp->imagenAutor, 'medioMargenIzquierda miniaturaImagenUsuarios'), HTML::urlInterna('USUARIOS', $resp->usuario, false), '', '', array('target' => '_blank'));
            if ($resp->nota == '') {
                $enlaceUsuario .= HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "/bandera_roja.png", ' imgIndicadorNota verRespuestaActividad manito', 'banderaRoja', array('ayuda' => $textos->id('NECESITA_CALIFICACION'), 'idRespuesta' => $resp->id));
            } else {
                $enlaceUsuario .= HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "/check_verde.png", ' imgIndicadorNota verRespuestaActividad manito', 'checkVerde', array('ayuda' => $textos->id('CALIFICADO'), 'idRespuesta' => $resp->id));
            }
            $contenidoRespuesta2 .= HTML::parrafo(HTML::frase($textos->id('PUBLICADO_EL') . ': ', 'negrilla') . date("D, d M Y", strtotime($resp->fechaPublicacion)) . ' ' . HTML::frase($textos->id('POR') . ': ', 'negrilla') . $enlaceUsuario, 'unCuartoMargenSuperior', '');
            $contenidoRespuesta .= HTML::contenedor($contenidoRespuesta2, "contenedorGrisActividades");

            $listaRespuestas[] = HTML::contenedor($contenidoRespuesta, "contenedorListaRespuestas", "contenedorRespuesta" . $resp->id);
        }//fin del foreach
    } else {
        $listaRespuestas[] = HTML::frase(HTML::parrafo($textos->id("SIN_ACTIVIDADES"), "sinRegistros", "sinRegistros"), "margenInferior");
    }


    $contenidoRespuestas .= HTML::lista($listaRespuestas, "listaVertical bordeSuperiorLista", "botonesOcultos", "listaActividades");


    $respuesta["generar"] = true;
    $respuesta['cargarJs'] = true;
    $respuesta['archivoJs'] = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['javascript'] . '/modulos/cursos/funcionesListarRespuestas.js';
    $respuesta["codigo"] = $contenidoRespuestas;
    $respuesta["destino"] = "#cuadroDialogo";
    $respuesta["titulo"] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("LISTAR_RESPUESTAS"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
    $respuesta["ancho"] = 780;
    $respuesta["alto"] = 600;


    Servidor::enviarJSON($respuesta);
}

/**
 * 
 */
function buscarActividad() {
    
}

?>