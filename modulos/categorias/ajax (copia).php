<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Categorias
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/





if (isset($url_accion)) {
    switch ($url_accion) {
        case "add"      :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                            adicionarCategoria($datos);
                            break;
        case "edit"     :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                            modificarCategoria($forma_id, $datos);
                            break;
        case "delete"   :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                            eliminarCategoria($forma_id, $confirmado);
                            break;
    }
}




/**
*
*Metodo adicionar Categorias
*
**/

function adicionarCategoria($datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    $categoria    = new Categoria();
    $destino = "/ajax".$categoria->urlBase."/add";



    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, "", "editor");
        $codigo .= HTML::parrafo($textos->id("QUE_MODULOS_TIENEN_CATEGORIA"), "negrilla margenSuperior");
        $codigo .= Categoria::mostrarChecksModulos(array());//pongo los checkbox con la info de los modulos
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", true).$textos->id("ACTIVO"), "margenSuperior");


        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_CATEGORIA"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 770;
        $respuesta["alto"]    = 570;

    } else {

        $respuesta["error"]   = true;

        if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        }elseif ($sql->existeItem("categoria", "nombre", $datos["nombre"])) {
            $respuesta["mensaje"] = str_replace("%1", $datos['nombre'], $textos->id("ERROR_EXISTE_CATEGORIA"));

        } elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

        }elseif ($datos["visibilidad"]=="privado" && empty($datos["modulos"])  ) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SELECCIONAR_PERFILES");

        } else {

            if ($categoria->adicionar($datos)) {
                $respuesta["error"]   = false;
                $respuesta["accion"]  = "recargar";

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }

        }
    }

    Servidor::enviarJSON($respuesta);
} //Fin del metodo de adicionar Categorias




 /**
 *
 *Metodo Modificar Categoria
 *
 **/

function modificarCategoria($id, $datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    $categoria    = new Categoria($id);
    $destino = "/ajax".$categoria->urlBase."/edit";


    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 50, 255, $categoria->nombre);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, $categoria->descripcion, "editor");
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", $categoria->activo).$textos->id("ACTIVO"), "margenSuperior");
        
        $modulos = $sql->seleccionar(array("categoria_modulo"), array("id_categoria", "id_modulo"), "id_categoria = '$id'");
        //print_r($modulos);
        $codigo .= Categoria::mostrarChecksModulos($modulos);//pongo los checkbox con la info de los modulos

        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_CATEGORIA"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 700;
        $respuesta["alto"]    = 540;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif ($sql->existeItem("categoria", "nombre", $datos["nombre"])) {
            $respuesta["mensaje"] = str_replace("%1", $datos['nombre'], $textos->id("ERROR_EXISTE_CATEGORIA"));

        }elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

        } elseif ($datos["visibilidad"]=="privado" && empty($datos["perfiles"])  ) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SELECCIONAR_PERFILES");

        }else {
                
              // $sql->depurar = true;
              if ($categoria->modificar($datos)) {
                $respuesta["error"]   = false;
                $respuesta["accion"]  = "recargar";

              }else{
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");

              }
        }
    }

    Servidor::enviarJSON($respuesta);
}











function eliminarCategoria($id, $confirmado) {
    global $textos, $sql;

    $categoria    = new Categoria($id);
    $destino = "/ajax".$categoria->urlBase."/delete";

    if (!$confirmado) {
        $titulo  = HTML::frase($categoria->nombre, "negrilla");
        $titulo  = preg_replace("/\%1/", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_CATEGORIA"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 250;
        $respuesta["alto"]    = 150;
    } else {

        if ($categoria->eliminar()) {
            $respuesta["error"]   = false;
            $respuesta["accion"]  = "recargar";

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}

?>