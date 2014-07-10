<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Mensajes
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/


if (isset($url_accion)) {
    switch ($url_accion) {      
 
        case "sendMessage"          :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                       enviarMensaje($forma_datos, $forma_id_usuario_destinatario);
                                       break;
	    case "replyMessage"         :   ($forma_procesar) ? $datos = $forma_datos : $datos = $forma_id;
                                       responderMensaje($datos);
                                       break;
        case "deleteMessage"        :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                       eliminarMensaje($forma_id, $confirmado);
                                       break;       
       
    }
}




/**
 *
 * Funcion que se encarga de mostrar el formulario para enviar un mensaje a un contacto haciendo click en el sobresito
 * de esta forma si se puede capturar correctamente el id del usuario. Seguidamente con el id del usuario
 * se consulta en lista_usuarios el nombre del usuario y se carga el formulario con este nombre en un textfield
 * que sea de solo lectura para evitar cambios.
 * @global type $textos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function enviarMensaje($datos = array(), $id_usuario_destinatario) {
    global $textos, $sql, $sesion_usuarioSesion;

    $usuario = new Contacto();
    $destino = "/ajax".$usuario->urlBase."/sendMessage";

    if (empty($datos)) {
        
        $nombre  = $sql->obtenerValor("lista_usuarios", "nombre", "id = '".$id_usuario_destinatario."'");      
        
        $sobre = HTML::contenedor("", "fondoSobre", "fondoSobre");
        
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("CONTACTO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("", 50, 255, $nombre, "", "", array("readOnly" => "true"));
        $codigo .= HTML::campoOculto("datos[id_usuario_destinatario]", $id_usuario_destinatario);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[contenido]", 10, 60, "", "", "txtAreaLimitado511");
        $codigo .= HTML::parrafo($textos->id("MAXIMO_TEXTO_511"), "maximoTexto", "maximoTexto");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($sobre."<br>");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");        
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ENVIAR_MENSAJE"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 430;
        $respuesta["alto"]    = 450;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["id_usuario_destinatario"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTACTO");

        } elseif (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["contenido"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");

        } else {
           
                $datos["id_usuario_remitente"] = $sesion_usuarioSesion->id;
                $datos["titulo"]               = strip_tags($datos["titulo"]);
                $datos["contenido"]            = strip_tags($datos["contenido"]);
                $datos["fecha"]                = date("Y-m-d G:i:s");
                $datos["leido"]                = 0;

                $mensaje = $sql->insertar("mensajes", $datos);

                if ($mensaje) {
                    $respuesta["error"]         = false;
                    $respuesta["accion"]        = "insertar";
                    $respuesta["insertarAjax"]  = true;

                } else {
                    $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                }
            
        }
    }

    Servidor::enviarJSON($respuesta);
}






?>
