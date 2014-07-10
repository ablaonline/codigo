<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Asociados
 * @author      Pablo A. Vélez <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 *
 * */
if (isset($url_accion)) {
    switch ($url_accion) {
        case "add" : ($forma_procesar) ? $datos = $forma_datos : $datos = array();
            adicionarAsociado($datos);
            break;
        case "edit" : ($forma_procesar) ? $datos = $forma_datos : $datos = array();
            modificarAsociado($forma_id, $datos);
            break;
        case "delete" : ($forma_procesar) ? $confirmado = true : $confirmado = false;
            eliminarAsociado($forma_id, $confirmado);
            break;
    }
}

/**
 *
 * Metodo que se encarga de adicionar un registro de un asociado en la BD
 * @param array $datos  Ingresa los valores que se van a registrar en la BD
 * @return boolean  
 *
 * */
function adicionarAsociado($datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    $asociado = new Asociado();
    $destino = "/ajax" . $asociado->urlBase . "/add";


    if (empty($datos)) {
        $codigo = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("VINCULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[vinculo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, "", "editor");
        $codigo .= HTML::parrafo($textos->id("IMAGEN"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("imagen", 50, 255);
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", true) . $textos->id("ACTIVO"), "margenSuperior");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"]  = true;
        $respuesta["codigo"]   = $codigo;
        $respuesta["titulo"]   = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_ASOCIADO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"]  = "#cuadroDialogo";
        $respuesta["ancho"]    = 500;
        $respuesta["alto"]     = 600;
        
    } else {
        $respuesta["error"] = true;
        $validarFormato     = Recursos::validarArchivo($archivo_imagen, array("jpg", "png", "gif", "jpeg"));
        

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");
        } elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");
        } elseif (empty($datos["vinculo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_VINCULO");
        } elseif (empty($archivo_imagen["tmp_name"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_IMAGEN");
        } elseif (!empty($archivo_imagen["tmp_name"])) {            
            $area = getimagesize($archivo_imagen["tmp_name"]);
            //Recursos::escribirTxt("no esta vacio la imagen4: ".$configuracion["DIMENSIONES"]["ASOCIADO"][0]." otra: ".$configuracion["DIMENSIONES"]["ASOCIADO"][1]);
            if ($validarFormato) {                
                $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_IMAGEN_ASOCIADO");
            } elseif ($area[0] != $configuracion["DIMENSIONES"]["ASOCIADO"][0] || $area[1] != $configuracion["DIMENSIONES"]["ASOCIADO"][1]) {
                $respuesta["mensaje"] = $textos->id("ERROR_AREA_IMAGEN_ASOCIADO");
            }
        }
           if(!$respuesta["mensaje"]){
                if ($asociado->adicionar($datos)) {
                    $respuesta["error"]  = false;
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
 * Metodo que se encarga de modificar la info de un asociado
 *
 * */
function modificarAsociado($id, $datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    $asociado = new Asociado($id);
    $destino = "/ajax" . $asociado->urlBase . "/edit";


    if (empty($datos)) {
        $codigo = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255, $asociado->titulo);
        $codigo .= HTML::parrafo($textos->id("VINCULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[vinculo]", 50, 255, $asociado->vinculo);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, $asociado->descripcion, "editor");
        $codigo .= HTML::parrafo($textos->id("IMAGEN"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("imagen", 50, 255);
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", $asociado->activo) . $textos->id("ACTIVO"), "margenSuperior");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"]  = true;
        $respuesta["codigo"]   = $codigo;
        $respuesta["destino"]  = "#cuadroDialogo";
        $respuesta["titulo"]   = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_ASOCIADO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]    = 500;
        $respuesta["alto"]     = 580;
    } else {
        $respuesta["error"]    = true;

        if (isset($archivo_imagen)) {
            $validarFormato = Recursos::validarArchivo($archivo_imagen, array("jpg", "png", "gif", "jpeg"));
        }


        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");
        } elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");
        } elseif (empty($datos["vinculo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_VINCULO");
        } elseif (!empty($archivo_imagen["tmp_name"])) {
            $area = getimagesize($archivo_imagen["tmp_name"]);
            if ($validarFormato) {
                $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_IMAGEN_ASOCIADO");
            } elseif ($area[0] != $configuracion["DIMENSIONES"]["ASOCIADO"][0] || $area[1] != $configuracion["DIMENSIONES"]["ASOCIADO"][1]) {
                $respuesta["mensaje"] = $textos->id("ERROR_AREA_IMAGEN_ASOCIADO");
            }
        }

        if (!isset($respuesta["mensaje"])) {

            if ($asociado->modificar($datos)) {
                //Recursos::escribirTxt("Aqui estoy..: ".$noticia->idImagen, $noticia->idModulo);
                $respuesta["error"] = false;
                $respuesta["accion"] = "recargar";
            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }
       }
    }

    Servidor::enviarJSON($respuesta);
}

//fin del metodo modificar noticias

/**
 *
 * Metodo que se encarga de modificar la info de un asociado
 * @param int $id Entero identificador de la BD del asociado
 * @param boolean $confirmado booleano que Confirma la eliminacion del asociado
 *
 * */
function eliminarAsociado($id, $confirmado) {
    global $textos, $sql;

    $asociado = new Asociado($id);
    $destino = "/ajax" . $asociado->urlBase . "/delete";

    if (!$confirmado) {
        $titulo = HTML::frase($asociado->titulo, "negrilla");
        $titulo = preg_replace("/\%1/", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo = HTML::forma($destino, $codigo);

        $respuesta["generar"]  = true;
        $respuesta["codigo"]   = $codigo;
        $respuesta["destino"]  = "#cuadroDialogo";
        $respuesta["titulo"]   = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_ASOCIADO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]    = 280;
        $respuesta["alto"]     = 150;
    } else {


        if ($asociado->eliminar()) {
            $respuesta["error"]  = false;
            $respuesta["accion"] = "recargar";
        } else {

            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}

?>