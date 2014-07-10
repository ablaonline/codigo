<?php

/**
 *
 * @package     FOLCS
 * @subpackage  comunicado
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/





if (isset($url_accion)) {
    switch ($url_accion) {
 
        case "edit"     :   modificarComunicado($forma_datos);
                            break;
        
    }
}




 /**
 *
 *Metodo Modificar BLog
 *
 **/

function modificarComunicado($datos = array()) {
    global $textos, $sql, $configuracion;

    $comunicado    = new Comunicado(1);
    $destino = "/ajax".$comunicado->urlBase."/edit";

   
    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255, $comunicado->titulo);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[contenido]", 10, 60, $comunicado->contenido, "editor");          
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_COMUNICADO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 700;
        $respuesta["alto"]    = 540;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["contenido"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");

        }else {
                
              // $sql->depurar = true;
              if ($comunicado->modificar($datos)) {
                $respuesta["error"]   = false;
                $respuesta["accion"]  = "recargar";

              }else{
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");

              }
        }
    }

    Servidor::enviarJSON($respuesta);
}

?>