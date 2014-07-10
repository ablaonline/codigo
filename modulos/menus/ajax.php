<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Menus
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
                            adicionarMenu($datos);
                            break;
        case "edit"     :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                            modificarMenu($forma_id, $datos);
                            break;
        case "delete"   :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                            eliminarMenu($forma_id, $confirmado);
                            break;
        case "up"       :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                            subirMenu($forma_id, $confirmado);
                            break;
        case "down"     :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                            bajarMenu($forma_id, $confirmado);
                            break;
    }
}

function adicionarMenu($datos = array()) {
    global $textos, $sql;

    $menu    = new Menu();
    $destino = "/ajax".$menu->urlBase."/add";

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("DESPUES_DE"), "negrilla margenSuperior");

        $filas   = $sql->seleccionar(array("menus"), array("orden", "nombre"), "id IS NOT NULL", "id", "orden ASC");

        while ($fila = $sql->filaEnObjeto($filas)) {
            $ubicacion[$fila->orden] = $fila->nombre;
        }

        $codigo .= HTML::listaDesplegable("datos[orden]", $ubicacion);
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 30, 60);
        $codigo .= HTML::parrafo($textos->id("DESTINO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[destino]", 60, 255);
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", true).$textos->id("ACTIVO"), "margenSuperior");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = $textos->id("ADICIONAR_MENU");
        $respuesta["ancho"]   = 500;
        $respuesta["alto"]    = 250;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif (preg_match("/[^0-9A-Za-z\,\ \-\_]/", $datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_NOMBRE");

        } elseif ($sql->existeItem("menus", "nombre", $datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_EXISTE_NOMBRE");

        } else {

            if ($menu->adicionar($datos)) {
                $respuesta["error"]   = false;
                //$respuesta["mensaje"] = $textos->id("MENU_ADICIONADO");
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
 */
function modificarMenu($id, $datos = array()) {
    global $textos, $sql;

    $menu    = new Menu($id);
    $destino = "/ajax".$menu->urlBase."/edit";

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("DESPUES_DE"), "negrilla margenSuperior");

        $filas   = $sql->seleccionar(array("menus"), array("orden", "nombre"), "id != '".$menu->id."'", "id", "orden ASC");

        while ($fila = $sql->filaEnObjeto($filas)) {

            if (!isset($orden)) {
                $orden = $fila->orden;
            }

            $ubicacion[$fila->orden] = $fila->nombre;

            if ($fila->orden > $orden && $fila->orden < $menu->orden) {
                $orden = $fila->orden;
            }
        }

        $codigo .= HTML::listaDesplegable("datos[orden]", $ubicacion, $orden);
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 30, 255, $menu->nombre);
        $codigo .= HTML::parrafo($textos->id("DESTINO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[destino]", 40, 255, $menu->destino);
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", true).$textos->id("ACTIVO"), "margenSuperior");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_MENU"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 400;
        $respuesta["alto"]    = 280;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif (preg_match("/[^0-9A-Za-z\,\ \-\_]/", $datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_NOMBRE");

        } elseif ($sql->existeItem("menus", "nombre", $datos["nombre"], "id != '".$menu->id."'")) {
            $respuesta["mensaje"] = $textos->id("ERROR_EXISTE_NOMBRE");

        } else {

            if ($menu->modificar($datos)) {
                $respuesta["error"]   = false;
                //$respuesta["mensaje"] = $textos->id("MENU_MODIFICADO");
                $respuesta["accion"]  = "recargar";

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}







function eliminarMenu($id, $confirmado) {
    global $textos, $sql;

    $menu    = new Menu($id);
    $destino = "/ajax".$menu->urlBase."/delete";

    if (!$confirmado) {
        $nombre  = HTML::frase($menu->nombre, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = $textos->id("ELIMINAR_MENU");
        $respuesta["ancho"]   = 200;
        $respuesta["alto"]    = 120;

    } else {
        $respuesta["error"]   = true;

        if ($sql->existeItem("paginas", "id_menu", $menu->id)) {
            $respuesta["destino"] = "#cuadroDialogo";
            $respuesta["mensaje"] = preg_replace("/\%1/", "'".$menu->nombre."'", $textos->id("ERROR_ELEMENTO_EN_USO"));

        } elseif ($menu->eliminar()) {
            $respuesta["error"]  = false;
            $respuesta["accion"] = "recargar";

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}


function subirMenu($id, $confirmado) {
    global $textos, $sql;

    $menu    = new Menu($id);
    $destino = "/ajax".$menu->urlBase."/up";

    if (!$confirmado) {
        $nombre  = HTML::frase($menu->nombre, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_MODIFICACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = $textos->id("MODIFICAR_MENU");
        $respuesta["ancho"]   = 200;
        $respuesta["alto"]    = 120;

    } else {

        if ($menu->subir()) {
            $respuesta["error"]   = false;
            //$respuesta["mensaje"] = $textos->id("MENU_MODIFICADO");
            $respuesta["accion"]  = "recargar";

        } else {
            $respuesta["error"]   = true;
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}

function bajarMenu($id, $confirmado) {
    global $textos, $sql;

    $menu    = new Menu($id);
    $destino = "/ajax".$menu->urlBase."/down";

    if (!$confirmado) {
        $nombre  = HTML::frase($menu->nombre, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_MODIFICACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = $textos->id("MODIFICAR_MENU");
        $respuesta["ancho"]   = 200;
        $respuesta["alto"]    = 120;

    } else {

        if ($menu->bajar()) {
            $respuesta["error"]   = false;
            //$respuesta["mensaje"] = $textos->id("MENU_MODIFICADO");
            $respuesta["accion"]  = "recargar";

        } else {
            $respuesta["error"]   = true;
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}

?>