<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Galerias
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 Colombo-Americano Soft.
 * @version     0.1
 * 
 * Modificado el 10-04-12
 * 
 **/

global $url_accion, $forma_procesar, $forma_id, $forma_datos, $forma_idModulo, $forma_idRegistro;

if (isset($url_accion)) {
    switch ($url_accion) {
        case "add"            : ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                adicionarGaleria($datos, $forma_idModulo, $forma_idRegistro);
                                break;
        case "edit"           : ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                modificarGaleria($forma_id, $datos);
                                break;
        case "delete"         : ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                eliminarGaleria($forma_id, $confirmado);
                                break;
        case "show"           : mostrarGaleria($forma_id);
                                break;
                            
    }
}


/**
 * Metodo adicionar Galeria
 * @global type $textos
 * @param type $datos 
 */
function adicionarGaleria($datos = array(), $idModulo = NULL, $idRegistro = NULL) {
    global $textos, $sql;

    $galeria   = new Galeria();
    $destino   = "/ajax".$galeria->urlBase."/add";
    $respuesta = array();

    if (empty($datos)) {
        $codigo  = Galeria::formularioAdicionarGaleria($idModulo, $idRegistro);
        
        $mod = $sql->obtenerValor("modulos", "nombre", "id = '".$idModulo."'");
        
        if($mod == "CURSOS"){
            $codigo .= HTML::parrafo(HTML::campoChequeo("datos[notificar_estudiantes]", true).$textos->id("NOTIFICAR_ESTUDIANTES"), "margenSuperior");
        } elseif($mod == "USUARIOS"){
            $codigo .= HTML::parrafo(HTML::campoChequeo("datos[publicar_en_muro]", true).$textos->id("PUBLICAR_EN_MURO"), "margenSuperior");
        }        
                
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk").HTML::frase("     ".$textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_GALERIA"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 600;
        $respuesta["alto"]    = 550;

    } else {

        $respuesta["error"]   = true;
        
        $cantImagenes       = $datos["cantCampoImagenGaleria"];//aqui llega el valor del campo oculto en el formulario que determina cuantas imagenes nuevas se van a ingresar
                
        $erroresImagenes = Galeria::validarImagenesGaleria($cantImagenes);//metodo que se encarga de validar las imagenes que se quieren guardar en la galeria

        if($erroresImagenes != ""){//verifico si hubo imagenes con errores de formato
            $respuesta["mensaje"] = str_replace("%1", $erroresImagenes, $textos->id("ERROR_FORMATO_IMAGEN_GALERIA"));
            
        } else if (empty($datos["titulo_galeria"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } else if (empty($datos["descripcion_galeria"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

        } else {

            $idGaleria = $galeria->adicionar($datos);
            if ($idGaleria) {             
                $respuesta["error"]    = false;
                $respuesta["accion"]   = "recargar";

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }

        }
    }

    Servidor::enviarJSON($respuesta);
} //Fin del metodo de adicionar Galeria


/**
 * Metodo modificar Galeria
 * @global type $textos
 * @param type $id
 * @param type $datos 
 */
function modificarGaleria($id, $datos = array()) {
    global $textos;

    $objeto    = new Galeria($id);
    $destino = "/ajax".$objeto->urlBase."/edit";
    $respuesta = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo  = Galeria::formularioModificarGaleria($id, $objeto->idModulo, $objeto->idRegistro);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "", "", "").HTML::frase("     ".$textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_GALERIA"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 600;
        $respuesta["alto"]    = 550;
        

    } else {
        $respuesta["error"]   = true;

        $cantImagenes       = $datos["cantCampoImagenGaleria"];//aqui llega el valor del campo oculto en el formulario que determina cuantas imagenes nuevas se van a ingresar
                
        $erroresImagenes = Galeria::validarImagenesGaleria($cantImagenes);//metodo que se encarga de validar las imagenes que se quieres guardar en la galeria

        if($erroresImagenes != ""){//verifico si hubo imagenes con errores de formato
            $respuesta["mensaje"] = str_replace("%1", $erroresImagenes, $textos->id("ERROR_FORMATO_IMAGEN_GALERIA"));
            
        } else if (empty($datos["titulo_galeria"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["descripcion_galeria"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");

        } else {
              
              if ($objeto->modificar($datos)) {                                    
                        $respuesta["error"]   = false;
                        $respuesta["accion"]  = "recargar";

              }else{
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
function eliminarGaleria($id, $confirmado) {
    global $textos;

    $objeto    = new Galeria($id);
    $destino   = "/ajax".$objeto->urlBase."/delete";
    $respuesta = array();

    if (!$confirmado) {
        $titulo  = HTML::frase($objeto->titulo, "negrilla");
        $titulo  = preg_replace("/\%1/", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_GALERIA"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 300;
        $respuesta["alto"]    = 170;
    } else {

        if ($objeto->eliminar()) {
            $respuesta["error"]    = false;
            $respuesta["accion"]   = "recargar";          
            
        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}


/**
 * mostrar
 * @global type $sql
 * @global type $textos
 * @param type $id 
 */
function mostrarGaleria($id){

    $galeria = new Galeria($id);    
    $codigo  = $galeria->mostrarGaleria($id);
    $codigo .= HTML::parrafo($galeria->descripcion, "descripcionGaleria"); 
    $respuesta = array();
    
    $respuesta["generar"] = true;
    $respuesta["codigo"]  = $codigo;
    $respuesta["destino"] = "#cuadroDialogo";
    $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($galeria->titulo, "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
    $respuesta["ancho"]   = 750;
    $respuesta["alto"]    = 500;
        
    Servidor::enviarJSON($respuesta);
}

?>