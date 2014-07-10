<?php

/**
 * @package     FOLCS
 * @subpackage  Audios
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 **/

global $url_accion, $forma_procesar, $forma_id, $forma_datos;

if (isset($url_accion)) {
    switch ($url_accion) {
        case "addAudio"    :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                adicionarAudio($datos);
                                break;

        case "deleteAudio" :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                eliminarAudio($forma_id, $confirmado);
                                break;      
    }
}

/**
 * Funcion que se encarga de mostrar el formulario para ingresar un nuevo archivo de audio
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_recurso
 * @global type $forma_idModulo
 * @global type $forma_idRegistro
 * @param type $datos 
 */
function adicionarAudio($datos = array()) {
    global $textos, $sql, $configuracion, $archivo_recurso, $forma_idModulo, $forma_idRegistro;

    $destino = "/ajax/audios/addAudio";

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos[idModulo]", $forma_idModulo);
        $codigo .= HTML::campoOculto("datos[idRegistro]", $forma_idRegistro);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 30, 255);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[descripcion]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("ARCHIVO"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("recurso", 40, 255);
        
        $mod = $sql->obtenerValor("modulos", "nombre", "id = '".$forma_idModulo."'");
        
        if($mod == "CURSOS"){
            $codigo .= HTML::parrafo(HTML::campoChequeo("datos[notificar_estudiantes]", true).$textos->id("NOTIFICAR_ESTUDIANTES"), "margenSuperior");

        } elseif($mod == "USUARIOS"){
            $codigo .= HTML::parrafo(HTML::campoChequeo("datos[publicar_en_muro]", true).$textos->id("PUBLICAR_EN_MURO"), "margenSuperior");

        }        
        
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior botonOk", "", "botonOk");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_AUDIO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 420;
        $respuesta["alto"]    = 270;

    } else {

        $respuesta["error"]   = true;

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

        } elseif (empty($archivo_recurso["tmp_name"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_ARCHIVO");

        } elseif ($archivo_recurso["size"] > $configuracion["DIMENSIONES"]["maximoPesoArchivo"]) {
            $respuesta["mensaje"] = $textos->id("ERROR_PESO_ARCHIVO1");

        } else {

            $formato = strtolower(substr($archivo_recurso["name"], strrpos($archivo_recurso["name"], ".")+1));

            if (!in_array($formato, array("mp3", "wma", "wav", 'ogg', '3gp', '3gpp'))) {
                $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_AUDIO");

            } else {

                $recurso = new Audio();

                if ($recurso->adicionar($datos)) {
                    $respuesta["error"]   = false;
                    $respuesta["accion"]  = "recargar";

                } else {
                    $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                }
            }

        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Funcion que se encarga de eliminar un archivo de audio
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */ 
function eliminarAudio($id, $confirmado) {
    global $textos, $sql;

    $id = trim(htmlspecialchars($id));

    if(!is_numeric($id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $audio        = new Audio($id);
    $destino      = "/ajax/audios/deleteAudio";
    $respuesta    = array();
    $idAudio      = $audio->id;

    if (!$confirmado) {
        $nombre  = HTML::frase($audio->titulo, "negrilla");
        $nombre  = str_replace("%1", $nombre, $textos->id("CONFIRMAR_ELIMINACION_AUDIO"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_AUDIO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 170;

    } else {

        if ($audio->eliminar()) {
            $respuesta["error"]        = false;
            $respuesta["accion"]       = "insertar";
            $respuesta["objetivo"]     = "eliminarAudio";
            $respuesta["idLiAudio"]    = "#audio_".$idAudio;

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}
