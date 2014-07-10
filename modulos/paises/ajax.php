<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Paises
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

if (isset($url_accion)) {
    switch ($url_accion) {
        case "add"      :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                            adicionarPais($datos);
                            break;
        case "edit"     :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                            modificarPais($forma_id, $datos);
                            break;
        case "delete"   :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                            eliminarPais($forma_id, $confirmado);
                            break;
    }
}

function adicionarPais($datos = array()) {
    global $textos, $sql;

    $pais    = new Pais();
    $destino = "/ajax".$pais->urlBase."/add";

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 30, 255);
        $codigo .= HTML::parrafo($textos->id("CODIGO_ISO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[codigo_iso]", 2, 2);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_PAIS"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 300;
        $respuesta["alto"]    = 180;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif (empty($datos["codigo_iso"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CODIGO");

        } elseif (preg_match("/[^A-Za-z\,\ ]/", $datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_NOMBRE");

        } elseif (preg_match("/[^A-Z]/", $datos["codigo_iso"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_CODIGO");

        } elseif ($sql->existeItem("paises", "nombre", $datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_EXISTE_NOMBRE");

        } elseif ($sql->existeItem("paises", "codigo_iso", $datos["codigo_iso"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_EXISTE_CODIGO");

        } else {

            if ($pais->adicionar($datos)) {
                $respuesta["error"]   = false;
                $respuesta["mensaje"] = $textos->id("PAIS_ADICIONADO");
                $respuesta["accion"]  = "recargar";

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

function modificarPais($id, $datos = array()) {
    global $textos, $sql;

    $pais    = new Pais($id);
    $destino = "/ajax".$pais->urlBase."/edit";

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 30, 255, $pais->nombre);
        $codigo .= HTML::parrafo($textos->id("CODIGO_ISO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[codigo_iso]", 2, 2, $pais->codigo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
         $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_PAIS"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 300;
        $respuesta["alto"]    = 180;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif (empty($datos["codigo_iso"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CODIGO");

        } elseif (preg_match("/[^A-Za-z\,\ ]/", $datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_NOMBRE");

        } elseif (preg_match("/[^A-Z]/", $datos["codigo_iso"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_CODIGO");

        } elseif ($sql->existeItem("paises", "nombre", $datos["nombre"], "id != '".$pais->id."'")) {
            $respuesta["mensaje"] = $textos->id("ERROR_EXISTE_NOMBRE");

        } elseif ($sql->existeItem("paises", "codigo_iso", $datos["codigo_iso"], "id != '$id'")) {
            $respuesta["mensaje"] = $textos->id("ERROR_EXISTE_CODIGO");

        } else {

            if ($pais->modificar($datos)) {
                $respuesta["error"]   = false;
                $respuesta["mensaje"] = $textos->id("PAIS_MODIFICADO");
                $respuesta["accion"]  = "recargar";

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

function eliminarPais($id, $confirmado) {
    global $textos, $sql;

    $pais    = new Pais($id);
    $destino = "/ajax".$pais->urlBase."/delete";

    if (!$confirmado) {
        $nombre  = HTML::frase($pais->nombre, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
         $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_PAIS"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 200;
        $respuesta["alto"]    = 120;
    } else {
        if ($pais->eliminar()) {
            $respuesta["error"]   = false;
            $respuesta["mensaje"] = $textos->id("PAIS_ELIMINADO");
            $respuesta["accion"]  = "recargar";

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}

?>