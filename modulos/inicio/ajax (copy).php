<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Inicio
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

global $url_accion, $forma_procesar, $forma_id, $url_cadena, $forma_mes;


if (isset($url_accion)) {
    switch ($url_accion) {
        case "addComment"       :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    adicionarComentario($datos);
                                    break;
        case "deleteComment"    :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarComentario($forma_id, $confirmado);
                                    break;
        case "deleteNotification" : ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarNotificacion($forma_id, $confirmado);
                                    break;      
        case "addVideo"         :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    adicionarVideo($datos);
                                    break;
        case "deleteVideo"      :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarVideo($forma_id, $confirmado);
                                    break;
        case "deleteNewVideo"   :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarVideoUsuario($forma_id, $confirmado);
                                    break;
        case "addImage"         :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    adicionarImagen($datos);
                                    break;
        case "deleteImage"      :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarImagen($forma_id, $confirmado);
                                    break;
        case "listUsers"        :   listarUsuarios($url_cadena);
                                    break;
        case "listCities"       :   listarCiudades($url_cadena);
                                    break;
        case "listJustCities"   :   listarSoloCiudades($url_cadena);
                                    break;
        case "listCenters"      :   listarCentros($url_cadena);
                                    break;
        case "listProfiles"     :   listarPerfiles($url_cadena);
                                    break;
        case "iamConected"      :   estoyConectado();
                                    break;
        case "cargarFechasEventos" :   cargarFechasEventos($forma_mes);
                                    break;                                
        case "deleteNotifications":   borrarNotificaciones();
                                    break;
        case "callScript"         :   estoyConectados();
                                    break;
    }
}


/**
 * 
 * @global type $textos
 * @global type $sql
 * @global type $forma_idModulo
 * @global type $forma_idRegistro
 * @param type $datos 
 */
function adicionarComentario($datos = array()) {
    global $textos, $forma_idModulo, $forma_idRegistro;

    $moduloInicio = new Modulo("INICIO");
    $destino      = "/ajax/".$moduloInicio->url."/addComment";
    $respuesta    = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos[idModulo]", $forma_idModulo);
        $codigo .= HTML::campoOculto("datos[idRegistro]", $forma_idRegistro);
        $codigo .= HTML::parrafo($textos->id("COMENTARIO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[contenido]", 5, 60, "", "", "txtAreaLimitado300");
        $codigo .= HTML::parrafo($textos->id("MAXIMO_TEXTO_250"), "maximoTexto", "maximoTexto");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("COMENTAR"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 410;
        $respuesta["alto"]    = 260;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["contenido"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_COMENTARIO");

        } else {
            $comentario = new Comentario();
            
            $idComentario = $comentario->adicionar($datos);

            if ($idComentario) {
                
            /******** CONTENIDO QUE SE VA A DEVOLVER VIA AJAX **********************************/
                $coment = new Comentario($idComentario);
                $botones       = HTML::nuevoBotonEliminarRegistro($idComentario, "home/deleteComment");
                $botonEliminar = HTML::contenedor($botones, "botonesLista", "botonesLista");            
                 
                $contenidoComentario  = $botonEliminar;
                $contenidoComentario .= HTML::enlace(HTML::imagen($coment->fotoAutor, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("USUARIOS", $coment->usuarioAutor));
                $contenidoComentario .= HTML::parrafo(HTML::enlace($coment->autor, HTML::urlInterna("USUARIOS", $coment->usuarioAutor)).$textos->id("USUARIO_DIJO"), "negrilla margenInferior");
                $contenidoComentario .= HTML::parrafo(nl2br($coment->contenido));
                $contenidoComentario .= HTML::parrafo(date("D, d M Y h:i:s A", $coment->fecha), "pequenia cursiva negrilla margenSuperior margenInferior");
                $respuestaComentarios = "<li class='nuevosComentarios'>".HTML::contenedor($contenidoComentario, "contenedorListaComentarios", "contenedorComentario".$coment->id)."</li>";
           /**************************************************************************************/     

                $respuesta["error"]                = false;
                $respuesta["accion"]               = "insertar";
                $respuesta["contenido"]            = $respuestaComentarios;
                $respuesta["idContenedor"]         = "#contenedorComentario".$idComentario;
                $respuesta["insertarAjax"]         = true;
                $respuesta["destino"]              = "#listaComentarios";
               

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * Funcion que muestra la ventana modal con el formulario para la confirmación y eliminacion de
 * un comentario haciendo uso de Ajax
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function eliminarComentario($id, $confirmado) {
    global $textos;

    $comentario   = new Comentario($id);
    $moduloInicio = new Modulo("INICIO");
    $destino      = "/ajax/".$moduloInicio->url."/deleteComment";
    $respuesta    = array();

    if (!$confirmado) {
        $nombre  = HTML::frase($comentario->autor, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION_COMENTARIO"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_COMENTARIO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 150;

    } else {

        if ($comentario->eliminar()) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "insertar";
            $respuesta["idContenedor"]      = "#contenedorComentario".$id;
            $respuesta["eliminarAjaxLista"] = true;

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $textos
 * @global type $forma_idModulo
 * @global type $forma_idRegistro
 * @param type $datos 

function adicionarVideo($datos = array()) {
    global $textos, $forma_idModulo, $forma_idRegistro;

    $moduloInicio = new Modulo("INICIO");
    $destino      = "/ajax/".$moduloInicio->url."/addVideo";
    $respuesta    = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos[idModulo]", $forma_idModulo);
        $codigo .= HTML::campoOculto("datos[idRegistro]", $forma_idRegistro);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 40, 50);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[descripcion]", 50, 120);
        $codigo .= HTML::parrafo($textos->id("ENLACE_AL_VIDEO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[enlace]", 60, 255, "", "", "", array("ayuda" => $textos->id("AYUDA_FORMATO_ENLACE_VIDEO")));
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_VIDEO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 400;
        $respuesta["alto"]    = 280;

    } else {
        $respuesta["error"]   = true;
        //quitar posibles llaves de apertura de codigo
        $datos["enlace"] = str_replace("<", "", $datos["enlace"]);

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["enlace"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_ENLACE_VIDEO");

        } elseif ( !preg_match("/\byoutube\b/i", $datos["enlace"]) && !preg_match("/\bvimeo\b/i", $datos["enlace"])   ) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_ENLACE_VIDEO");
            
        } elseif ( preg_match("/\biframe\b/i", $datos["enlace"]) || !filter_var($datos["enlace"], FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED) ) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_CODIGO_ENLACE_VIDEO");
        } else {          
                $video = new Video();
                    
                if ($video->adicionar($datos)) {
                    $respuesta["error"]   = false;
                    $respuesta["accion"]  = "recargar";

                } else {
                    $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                }
        }
    }

    Servidor::enviarJSON($respuesta);
} */

/**
 *
 * @global type $textos
 * @global type $forma_idModulo
 * @global type $forma_idRegistro
 * @param type $datos 
 */
function adicionarVideo($datos = array()) {
    global $textos, $sql, $forma_idModulo, $forma_idRegistro;

    $moduloInicio = new Modulo("INICIO");
    $destino      = "/ajax/".$moduloInicio->url."/addVideo";
    $respuesta    = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos[idModulo]", $forma_idModulo);
        $codigo .= HTML::campoOculto("datos[idRegistro]", $forma_idRegistro);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 40, 50);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[descripcion]", 50, 120);
        $codigo .= HTML::parrafo($textos->id("ENLACE_AL_VIDEO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[enlace]", 60, 255, "", "", "", array("ayuda" => $textos->id("AYUDA_FORMATO_ENLACE_VIDEO")));
        $mod = $sql->obtenerValor("modulos", "nombre", "id = '".$forma_idModulo."'");
        
        if($mod == "CURSOS"){
            $codigo .= HTML::parrafo(HTML::campoChequeo("datos[notificar_estudiantes]", true).$textos->id("NOTIFICAR_ESTUDIANTES"), "margenSuperior");
        } elseif($mod == "USUARIOS"){
            $codigo .= HTML::parrafo(HTML::campoChequeo("datos[publicar_en_muro]", true).$textos->id("PUBLICAR_EN_MURO"), "margenSuperior");
        }
        
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_VIDEO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 400;
        $respuesta["alto"]    = 280;

    } else {
        $respuesta["error"]   = true;
        //quitar posibles llaves de apertura de codigo
        $datos["enlace"] = str_replace("<", "", $datos["enlace"]);

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["enlace"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_ENLACE_VIDEO");

        } elseif ( !preg_match("/\byoutube\b/i", $datos["enlace"]) && !preg_match("/\bvimeo\b/i", $datos["enlace"])   ) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_ENLACE_VIDEO");
            
        } elseif ( preg_match("/\biframe\b/i", $datos["enlace"]) || !filter_var($datos["enlace"], FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED) ) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_CODIGO_ENLACE_VIDEO");
        } else {          
                $video = new Video();
                    
                if ($video->adicionar($datos)) {
                    $respuesta["error"]   = false;
                    $respuesta["accion"]  = "recargar";

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
 * @param type $id
 * @param type $confirmado 
 */
function eliminarVideo($id, $confirmado) {
    global $textos;

    $video        = new Video($id);
    $moduloInicio = new Modulo("INICIO");
    $destino      = "/ajax/".$moduloInicio->url."/deleteVideo";
    $respuesta    = array();

    if (!$confirmado) {
        $nombre  = HTML::frase($video->descripcion, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION_VIDEO"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_VIDEO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 170;

    } else {

        if ($video->eliminar()) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "recargar";

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $textos
 * @param type $id
 * @param type $confirmado 
 */
function eliminarVideoUsuario($id, $confirmado) {
    global $textos;

    $video        = new Video($id);
    $moduloInicio = new Modulo("INICIO");
    $destino      = "/ajax/".$moduloInicio->url."/deleteNewVideo";
    $respuesta    = array();

    if (!$confirmado) {
        $nombre  = HTML::frase($video->descripcion, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION_VIDEO"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_VIDEO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 170;

    } else {

        if ($video->eliminar()) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "recargar";

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}



/**
 *
 * @global type $textos
 * @param type $id
 * @param type $confirmado 
 */
function eliminarAudio($id, $confirmado) {
    global $textos;

    $audio        = new Audio($id);
    $moduloInicio = new Modulo("INICIO");
    $destino      = "/ajax/".$moduloInicio->url."/deleteAudio";
    $respuesta    = array();

    if (!$confirmado) {
        $nombre  = HTML::frase($audio->descripcion, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION_AUDIO"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_AUDIO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 150;

    } else {

        if ($audio->eliminar()) {
            $respuesta["error"]   = false;
            $respuesta["accion"]  = "recargar";

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $textos
 * @global type $configuracion
 * @global type $archivo_recurso
 * @global type $forma_idRegistro
 * @global type $forma_modulo
 * @param type $datos 
 */
function adicionarImagen($datos = array()) {
    global $textos, $configuracion, $archivo_recurso, $forma_idRegistro, $forma_modulo;

    $moduloInicio = new Modulo("INICIO");
    $destino      = "/ajax/".$moduloInicio->url."/addImage";
    $respuesta    = array();
    
    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        //$codigo .= HTML::campoOculto("datos[idModulo]", $forma_idModulo);
        $codigo .= HTML::campoOculto("datos[modulo]", $forma_modulo);
        $codigo .= HTML::campoOculto("datos[idRegistro]", $forma_idRegistro);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[descripcion]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("ARCHIVO"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("recurso", 50, 255);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_IMAGEN"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 270;

    } else {
        $respuesta["error"]   = true;
        
        if ( !empty($archivo_recurso["tmp_name"]) ) {   
            $validarFormato  = Archivo::validarArchivo($archivo_recurso, array("jpg","png","gif", "jpeg"));
            //$area  = getimagesize($archivo_recurso["tmp_name"]);
        }

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($archivo_recurso["tmp_name"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_ARCHIVO");

        } elseif ($archivo_recurso["size"] > $configuracion["DIMENSIONES"]["maximoPesoArchivo"]) {
            $respuesta["mensaje"] = $textos->id("ERROR_PESO_ARCHIVO");

        } elseif ($validarFormato) {
                $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_IMAGEN");
                
            } else {
                
                $recurso = new Imagen();
                
                $idImagen = $recurso->adicionar($datos);
                
                if ($idImagen) {                  
                    $respuesta["error"]                = false;
                    $respuesta["accion"]               = "recargar";

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
 * @param type $id
 * @param type $confirmado 
 */
function eliminarImagen($id, $confirmado) {
    global $textos;

    $imagen       = new Imagen($id);
    $moduloInicio = new Modulo("INICIO");
    $destino      = "/ajax/".$moduloInicio->url."/deleteImage";
    $respuesta    = array();

    if (!$confirmado) {
        $nombre  = HTML::frase($imagen->descripcion, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION_IMAGEN"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_IMAGEN"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 150;

    } else {

        if ($imagen->eliminar()) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "insertar";
            $respuesta["idContenedor"]      = "#contenedorImagen".$id;
            $respuesta["eliminarAjaxLista"] = true;

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * Funcion que muestra la ventana modal con el formulario para la confirmación y eliminacion de
 * las Notificaciones del usuario haciendo uso de Ajax
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function eliminarNotificacion($id, $confirmado) {
    global $textos, $sql;

    $moduloInicio = new Modulo("INICIO");
    $destino      = "/ajax/".$moduloInicio->url."/deleteNotification";
    $respuesta    = array();

    if (!$confirmado) {
        $nombre  = HTML::frase($comentario->autor, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION_NOTIFICACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_NOTIFICACION"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 170;

    } else {
           $consulta = $sql->eliminar("notificaciones", "id = '".$id."'");
        
        if ($consulta) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "insertar";
            $respuesta["idContenedor"]      = "#contenedorNotificacion".$id;
            $respuesta["eliminarAjaxLista"] = true;

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $sql
 * @param type $cadena 
 */
function listarUsuarios($cadena) {
    global $sql;
    
    $respuesta = array();
    $consulta  = $sql->seleccionar(array("lista_usuarios"), array("nombre"), "nombre LIKE '%$cadena%'", "", "nombre ASC", 0, 20);

    while ($fila = $sql->filaEnObjeto($consulta)) {
        $respuesta[] = $fila->nombre;
    }

    Servidor::enviarJSON($respuesta);
}


/**
 * 
 * @global type $sql
 * @param type $cadena 
 */
function listarCiudades($cadena) {
    global $sql;
    
    $respuesta = array();
    $consulta  = $sql->seleccionar(array("lista_ciudades"), array("cadena"), "nombre LIKE '%$cadena%'", "", "cadena ASC", 0, 20);

    while ($fila = $sql->filaEnObjeto($consulta)) {
        $respuesta[] = $fila->cadena;
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $sql
 * @param type $cadena 
 */
function listarSoloCiudades($cadena) {
    global $sql;
    
    $respuesta = array();
    $consulta  = $sql->seleccionar(array("c" => "ciudades", "e" => "estados"), array("CONCAT(c.nombre,', ', e.nombre) AS ciudad"), "c.id_estado = e.id AND c.nombre LIKE '%$cadena%'", "", "c.nombre ASC", 0, 20);

    while ($fila = $sql->filaEnObjeto($consulta)) {
        $respuesta[] = $fila->ciudad;
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $sql
 * @param type $cadena 
 */
function listarCentros($cadena) {
    global $sql;
     
    $respuesta = array();
    $consulta  = $sql->seleccionar(array("lista_centros"), array("nombre"), "nombre LIKE '%$cadena%'", "", "nombre ASC", 0, 20);

    while ($fila = $sql->filaEnObjeto($consulta)) {
        $respuesta[] = $fila->nombre;
    }
   
    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $sql
 * @param type $cadena 
 */
function listarPerfiles($cadena) {
    global $sql;
     
    $respuesta = array();
    $consulta  = $sql->seleccionar(array("tipos_usuario"), array("nombre"), "nombre LIKE '%$cadena%' AND visibilidad = '1'", "", "nombre ASC", 0, 20);

    while ($fila = $sql->filaEnObjeto($consulta)) {
        $respuesta[] = $fila->nombre;
    }
   
    Servidor::enviarJSON($respuesta);
}


/**
 *
 * Función que se encarga de verificar el estado de conexion de un usuario,
 * al tiempo que va borrando los registros de la tabla usuarios conectados que no han sido actualizados
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @global type $configuracion 
 * 
 */
function estoyConectado() {
    global $sql, $sesion_usuarioSesion;
    $respuesta = "";

    if (isset($sesion_usuarioSesion)) {
        $existe = $sql->existeItem("usuarios_conectados", "id_usuario", $sesion_usuarioSesion->id);

        if ($existe) {
            $datos      = array("tiempo" => date("Y-m-d H:i:s"));
            $consulta   = $sql->modificar("usuarios_conectados", $datos, "id_usuario = " . $sesion_usuarioSesion->id . "");
            $sql->eliminar("usuarios_conectados", "UNIX_TIMESTAMP(tiempo) <=  (UNIX_TIMESTAMP() - 40)  ");//Esto de aqui hay que revisar como se crea un script unico que corra y lo haga
        } else {

            $datos = array(
                "id_usuario" => $sesion_usuarioSesion->id,
                "usuario"    => $sesion_usuarioSesion->usuario,
                "nombre"     => $sesion_usuarioSesion->persona->nombreCompleto,
                "tiempo"     => date("Y-m-d H:i:s")
            );

            $consulta = $sql->insertar("usuarios_conectados", $datos);
        }

        //Refrescar la pestaña del acordeon de los contactos
        $codigo     = Contacto::amigosConectados();
        $numAmigos  = Contacto::cantidadAmigosConectados();
        //mostrar las notificaciones del usuario
        $lista = Usuario::mostrarNotificacionesDinamicas();
        
        if($lista == "sin_notificaciones"){
            
        }else{
            $respuesta["mostrarNotificacion"] = true;
            $respuesta["contenido2"]          = $lista;
        }

        $respuesta["error"]               = NULL;
        $respuesta["accion"]              = "insertar";
        $respuesta["refrescarContactos"]  = true;
        $respuesta["iamConected"]         = true;
        $respuesta["numAmigosConectados"] = $numAmigos;
        $respuesta["limpiar"]             = false;
        $respuesta["contenido"]           = $codigo;
        $respuesta["destino"]             = "#bloqueContactosConectados_" . $sesion_usuarioSesion->id;

        
    } else {
        Usuario::cerrarSesion();
        
    }
    //Recursos::escribirTxt("Esta es la fecha : ".$hora["hours"] . ":" . $hora["minutes"] . ":" . $hora["seconds"] );
    Servidor::enviarJSON($respuesta);
    
}//fin del metodo de estoy conectado


/**
 *
 * @global type $sql
 * @global type $sesion_usuarioSesion 
 */
function borrarNotificaciones(){
    global $sql, $sesion_usuarioSesion;
    //codigo para borrar las notificaciones de un usuario
    $datos = array(
    "leido" => '1'
    );
     $sql->modificar("notificaciones", $datos, "id_usuario = '" . $sesion_usuarioSesion->id . "' AND leido = '0'");
    //$sql->depurar = true;
     $sql->eliminar("notificaciones", "UNIX_TIMESTAMP(fecha) <=  (UNIX_TIMESTAMP() - 604800) AND leido = '1' "); 
}



/**
 *
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $mes 
 */
function cargarFechasEventos($mes){
    global $sql, $sesion_usuarioSesion;

    $respuesta = array();
    $arreglo = array();
    
    if (isset($sesion_usuarioSesion)) {
        $idTipo = $sesion_usuarioSesion->idTipo;
    } else {
        $idTipo = 99;
    }
        
    $idModulo = $sql->obtenerValor("modulos", "id", "nombre = 'EVENTOS'");
    
    
    $tablas = array(
        "e" => "eventos"
        );
    
    $condicion = "MONTH(fecha_inicio) = '".$mes."'";
    if ($idTipo != 99){        
        if($idTipo != 0){
            $tablas["pi"] = "permisos_item";
            $condicion .= " AND e.id = pi.id_item AND pi.id_modulo = '".$idModulo."' AND id_perfil IN(99, ".$idTipo.")"; 
        }
    }else{
        $tablas["pi"] = "permisos_item";
        $condicion   .= " AND e.id = pi.id_item AND pi.id_modulo = '".$idModulo."' AND id_perfil IN(99)"; 
    }
    //$sql->depurar = true;
    $consulta = $sql->seleccionar($tablas, array("id"), $condicion);      
    
    while ($evento = $sql->filaEnObjeto($consulta)) {        
        $event = new Evento($evento->id); 
        if(!isset($arreglo[$event->fechaInicio])){
            $arreglo[$event->fechaInicio]   = "";
        }
        $arreglo[$event->fechaInicio] .= $event->fechaInicio."¬".$event->titulo."¬".$event->id."¬".$event->imagenMiniatura."¬".$event->ciudad.", ".$event->pais."¬".$event->lugar."¬".$event->centro.", ".$event->ciudadCentro."¬".$event->iconoBandera."~";   

    }
          
    
    $respuesta["error"]             = NULL;
    $respuesta["accion"]            = "insertar";
    $respuesta["cargarFechaEvento"] = true;
    $respuesta["ids"]               = implode("|", $arreglo);    
    
    Servidor::enviarJSON($respuesta);   
    
}



?>