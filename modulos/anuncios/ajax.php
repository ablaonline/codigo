<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Anuncio
 * @author      Pablo A. V�lez <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 *
 **/

if (isset($url_accion)) {
    switch ($url_accion) {
        case "add"      :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                            adicionarAnuncio($datos);
                            break;
        case "edit"     :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                            modificarAnuncio($forma_id, $datos);
                            break;
        case "delete"   :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                            eliminarAnuncio($forma_id, $confirmado);
                            break;
    }
}


/**
*
*Metodo que se encarga de adicionar un registro de un banner en la BD
*@param array $datos  Ingresa los valores que se van a registrar en la BD
*@return boolean  
*
**/


function adicionarAnuncio($datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    $anuncio    = new Anuncio();
    $destino = "/ajax".$anuncio->urlBase."/add";


    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("VINCULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[vinculo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, "", "editor");
        $codigo .= HTML::parrafo($textos->id("IMAGEN"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("imagen", 50, 255);         
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", true).$textos->id("ACTIVO"), "margenSuperior");     
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_ANUNCIO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 500;
        $respuesta["alto"]    = 600;

    } else {
        $respuesta["error"]   = true;
        if(!empty($archivo_imagen['tmp_name'])){
            $validarFormato = Recursos::validarArchivo($archivo_imagen, array('jpg','png','gif', 'jpeg'));
         }


        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        }elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

        }elseif (empty($datos["vinculo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_VINCULO");

        }elseif (empty($archivo_imagen["tmp_name"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_IMAGEN");

        } elseif ($validarFormato) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_IMAGEN_ANUNCIO");
            
        }else{              
		$consulta = $anuncio->adicionar($datos);
                if ($consulta) {
                   
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
*Metodo que se encarga de modificar la info de un banner
*
**/


function modificarAnuncio($id, $datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    $anuncio   = new Anuncio($id);
    $destino   = "/ajax".$anuncio->urlBase."/edit";
    $idArchivo = "";


    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255, $anuncio->titulo); 
        $codigo .= HTML::parrafo($textos->id("VINCULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[vinculo]", 50, 255, $anuncio->vinculo);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, $anuncio->descripcion, "editor");
        $codigo .= HTML::parrafo($textos->id("IMAGEN"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("imagen", 50, 255);       
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", $anuncio->activo).$textos->id("ACTIVO"), "margenSuperior");       
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_ANUNCIO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 500;
        $respuesta["alto"]    = 580;

    } else {
        $respuesta["error"]   = true;


        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

         }elseif (empty($datos["descripcion"])){
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

        }elseif (empty($datos["vinculo"])){
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_VINCULO");

        }elseif (!empty($archivo_imagen["tmp_name"])) {

	     $validarFormato = Recursos::validarArchivo($archivo_imagen, array("jpg","png","gif","jpeg"));
             $area  = getimagesize($archivo_imagen["tmp_name"]);     
          
             if ($validarFormato) {
                 $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_IMAGEN_BANNER");

              } elseif ($area[0] != $configuracion["DIMENSIONES"]["BANNER"][0] || $area[1] != $configuracion["DIMENSIONES"]["BANNER"][1]) {
                    $respuesta["mensaje"] = $textos->id("ERROR_AREA_IMAGEN_BANNER");
                }          

           } 
               
               if (!isset($respuesta["mensaje"])) {        

                    if ($anuncio->modificar($datos)) {
                        //Recursos::escribirTxt("Aqui estoy..: ".$noticia->idImagen, $noticia->idModulo);
                        $respuesta["error"]   = false;
                        $respuesta["accion"]  = "recargar";
                     } else {
                        $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                     }
                }
           
    }

    Servidor::enviarJSON($respuesta);
}//fin del metodo modificar noticias




/**
*
*Metodo que se encarga de modificar la info de un banner
*@param int $id Entero identificador de la BD del anuncio
*@param boolean $confirmado booleano que Confirma la eliminacion del anuncio
*
**/

function eliminarAnuncio($id, $confirmado) {
    global $textos, $sql;

    $anuncio    = new Anuncio($id);
    $destino = "/ajax".$anuncio->urlBase."/delete";

    if (!$confirmado) {
        $titulo  = HTML::frase($anuncio->titulo, "negrilla");
        $titulo  = str_replace("%1", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_ANUNCIO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 280;
        $respuesta["alto"]    = 150;
    } else {


         if ($anuncio->eliminar()) {  
               $respuesta["error"]   = false;
               $respuesta["accion"]  = "recargar";
          }else{                                
                 
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
         }

    }

    Servidor::enviarJSON($respuesta);
}




?>