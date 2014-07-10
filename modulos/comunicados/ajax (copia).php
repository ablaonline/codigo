<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Blogs
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
                            adicionarBlog($datos);
                            break;
        case "edit"     :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                            modificarBlog($forma_id, $datos);
                            break;
        case "delete"   :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                            eliminarBlog($forma_id, $confirmado);
                            break;
    }
}







function adicionarBlog($datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    $blog    = new Blog();
    $destino = "/ajax".$blog->urlBase."/add";

/******************************/
  
   $perfil = new Perfil();

/*****************************/


    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[contenido]", 10, 60, "", "editor");
        $codigo .= HTML::parrafo($textos->id("PALABRAS_CLAVES"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[palabrasClaves]", 50, 255);
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", true).$textos->id("ACTIVO"), "margenSuperior");


        //pongo los dos radiobutton que verifica si es publico a privado

        $opcionesPublico = array("onClick" => "$('#listaCheckUsuarios').css({ display: 'none'})");
        $opcionesPrivado = array("onClick" => "$('#listaCheckUsuarios').css({ display: 'block'})");
        $codigo .= HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");

/*******  cargo los checks de cada uno de los perfiles con los que quiero compartir mi item  *********/

        $checks = $perfil->mostrarChecks(); 

            
        $codigo .= HTML::parrafo($checks, "margenSuperior");

/************************* Fin de la carga de los checks  **********************************************/


        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = $textos->id("ADICIONAR_BLOG");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 750;
        $respuesta["alto"]    = 540;

    } else {

        $respuesta["error"]   = true;

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["contenido"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");

        }elseif ($datos["visibilidad"]=="privado" && empty($datos["perfiles"])  ) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SELECCIONAR_PERFILES");

        } else {

            if ($blog->adicionar($datos)) {
                $respuesta["error"]   = false;
                $respuesta["accion"]  = "recargar";

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }

        }
    }

    Servidor::enviarJSON($respuesta);
}









function modificarBlog($id, $datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    $blog    = new Blog($id);
    $destino = "/ajax".$blog->urlBase."/edit";

/******************************/
  
   $perfil = new Perfil();

/*****************************/



    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255, $blog->titulo);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[contenido]", 10, 60, $blog->contenido, "editor");
        $codigo .= HTML::parrafo($textos->id("PALABRAS_CLAVES"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[palabrasClaves]", 50, 255, $blog->palabrasClaves);
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", $blog->activo).$textos->id("ACTIVO"), "margenSuperior");
        

   //pongo los dos radiobutton que verifica si es publico a privado

        $opcionesPublico = array("onClick" => "$('#listaCheckUsuarios').css({ display: 'none'})");
        $opcionesPrivado = array("onClick" => "$('#listaCheckUsuarios').css({ display: 'block'})");
        $codigo .= HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");

/*******  cargo los checks de cada uno de los perfiles con los que quiero compartir mi item  *********/

        $checks = $perfil->mostrarChecks(); 

            
        $codigo .= HTML::parrafo($checks, "margenSuperior");

/************************* Fin de la carga de los checks  **********************************************/


        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = $textos->id("MODIFICAR_BLOG");
        $respuesta["ancho"]   = 700;
        $respuesta["alto"]    = 500;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["contenido"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");

        } elseif ($datos["visibilidad"]=="privado" && empty($datos["perfiles"])  ) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SELECCIONAR_PERFILES");

        }else {

            if ($blog->modificar($datos)) {
                $respuesta["error"]   = false;
                $respuesta["accion"]  = "recargar";

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}











function eliminarBlog($id, $confirmado) {
    global $textos, $sql;

    $blog    = new Blog($id);
    $destino = "/ajax".$blog->urlBase."/delete";

    if (!$confirmado) {
        $titulo  = HTML::frase($blog->titulo, "negrilla");
        $titulo  = preg_replace("/\%1/", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = $textos->id("ELIMINAR_BLOG");
        $respuesta["ancho"]   = 200;
        $respuesta["alto"]    = 120;
    } else {

        if ($blog->eliminar()) {
            $respuesta["error"]   = false;
            $respuesta["accion"]  = "recargar";

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}

?>