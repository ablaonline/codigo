<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Documentos
 * @author      Pablo Andrés Vélez Vidal  <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 Colombo Americano Cali
 * @version     0.1
 *
 **/

global $url_accion, $forma_procesar, $forma_id, $forma_datos;

if (isset($url_accion)) {
    switch ($url_accion) {
        case "addDocument"  :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                adicionarDocumento($datos);
                                break;

        case "deleteDocument":  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                eliminarDocumento($forma_id, $confirmado);
                                break;


        case "searchDocuments" : ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                buscarDocumentos($forma_datos);
                                break;        
    }
}



/**
 *
 * Funcion que se encarga de mostrar el formulario para ingresar un nuevo documento, y de ingresarlo via Ajax
 * 
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_recurso
 * @global type $forma_idModulo
 * @global type $forma_idRegistro
 * @param type $datos 
 */

function adicionarDocumento($datos = array()) {
    global $sql, $textos, $configuracion, $archivo_recurso, $forma_idModulo, $forma_idRegistro;

    $moduloInicio   = new Modulo("DOCUMENTOS");
    $destino        = "/ajax/".$moduloInicio->url."/addDocument";
    $respuesta      = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos[idModulo]", $forma_idModulo);
        $codigo .= HTML::campoOculto("datos[idRegistro]", $forma_idRegistro);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 40, 255);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[descripcion]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("ARCHIVO"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("recurso", 50, 255);
        
        $mod = $sql->obtenerValor("modulos", "nombre", "id = '".$forma_idModulo."'");
        
        if($mod == "CURSOS"){
            $codigo .= HTML::parrafo(HTML::campoChequeo("datos[notificar_estudiantes]", true).$textos->id("NOTIFICAR_ESTUDIANTES"), "margenSuperior");
        } elseif($mod == "USUARIOS"){
            $codigo .= HTML::parrafo(HTML::campoChequeo("datos[publicar_en_muro]", true).$textos->id("PUBLICAR_EN_MURO"), "margenSuperior");
        }        
        
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_ARCHIVO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 430;
        $respuesta["alto"]    = 315;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

        } elseif (empty($archivo_recurso["tmp_name"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_ARCHIVO");

        } elseif ($archivo_recurso["size"] > $configuracion["DIMENSIONES"]["maximoPesoArchivo"]) {
            $respuesta["mensaje"] = $textos->id("ERROR_PESO_ARCHIVO");

        } else {

            $formato = strtolower(substr($archivo_recurso["name"], strrpos($archivo_recurso["name"], ".")+1));

            if (!in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))) {
                $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_ARCHIVO");

            } else {

                $documento = new Documento();
                $idDocumento = $documento->adicionar($datos);

                if ( $idDocumento ) {
                    
         /********************** En este Bloque se Arma el Contenido del nuevo Documento que se acaba de Registrar  **********************/
                   $documento  = new Documento($idDocumento); 
                    
                    $botonEliminar = HTML::nuevoBotonEliminarRegistro($documento->id, "documents/deleteDocument");
                    $botonEliminar = HTML::contenedor($botonEliminar, "botonesLista", "botonesLista");          
                    $contenidoArchivo   = $botonEliminar;
                    $contenidoArchivo  .= HTML::enlace(HTML::imagen($documento->icono, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $documento->enlace);
                    $contenidoArchivo  .= HTML::parrafo(HTML::enlace($documento->titulo, $documento->enlace));
                    $contenidoArchivo2  = HTML::parrafo($documento->descripcion);
                    $contenidoArchivo2 .= HTML::parrafo(HTML::frase($textos->id("ENLACE").": ", "negrilla").$documento->enlace, "margenSuperior");
                    $contenidoArchivo  .= HTML::contenedor($contenidoArchivo2, "contenedorGrisLargo");                
                    $contenidoArchivo   = "<li class = 'botonesOcultos' style='border-top: 1px dotted #E0E0E0;'>".HTML::contenedor($contenidoArchivo, "contenedorListaDocumentos", "contenedorDocumento".$documento->id)."</li>";
            
        /*******************************************************************************************************************************/
                   
                    $respuesta["error"]                = false;
                    $respuesta["accion"]               = "insertar";
                    $respuesta["contenido"]            = $contenidoArchivo;
                    $respuesta["idContenedor"]         = "#contenedorDocumento".$idDocumento;
                    $respuesta["insertarAjax"]         = true;
                    $respuesta["destino"]              = "#listaDocumentos";

                } else {
                    $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                }
            }

        }
    }

    Servidor::enviarJSON($respuesta);
}

 
 
 
/**
 * Funcion que se encarga de eliminar un documento
 * 
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */ 
function eliminarDocumento($id, $confirmado) {
    global $textos, $sql;

    $archivo      = new Documento($id);
    $moduloInicio = new Modulo("DOCUMENTOS");
    $destino      = "/ajax/".$moduloInicio->url."/deleteDocument";

    if (!$confirmado) {
        $nombre  = HTML::frase($archivo->descripcion, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION_ARCHIVO"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_ARCHIVO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 170;

    } else {

        if ($archivo->eliminar()) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "insertar";
            $respuesta["idContenedor"]      = "#contenedorDocumento".$id;
            $respuesta["eliminarAjaxLista"] = true;

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}



   

/**
*
*Metodo que carga el formulario para buscar y filtrar Juegos  por contenido
*
**/

function buscarDocumentos($datos) {
    global $textos, $sql, $configuracion, $sesion_usuarioSesion;

    $juego = new Juego();
    $destino = "/ajax".$juego->urlBase."/searchGames";

    if (empty($datos)) {

        $forma2  = HTML::campoOculto("datos[criterio]", "titulo");
        $forma2 .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $forma2 .= HTML::parrafo(HTML::campoTexto("datos[patron]", 30, 255).HTML::boton("buscar", $textos->id("BUSCAR")), "margenSuperior");

      //  $codigo1  = HTML::forma($destino, $forma1);
        $codigo1  = HTML::forma($destino, $forma2);
        $codigo   = HTML::contenedor($codigo1, "bloqueBorde");
        $codigo  .= HTML::contenedor("","margenSuperior", "resultadosBuscarJuegos");

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("BUSCAR_JUEGOS"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 500;
        $respuesta["alto"]    = 400;

    } else {

     if (!empty($datos["criterio"]) && !empty($datos["patron"])) {

            if ($datos["criterio"] == "titulo") {
                    $palabras = explode(" ", $datos["patron"]);

                    foreach ($palabras as $palabra) {
                        $listaPalabras[] = trim($palabra);
                    }
            }

            $tablas = array(
                            "j" => "juegos",
                            "i" => "imagenes"
                            );

            $columnas = array(
                            "id"          =>    "j.id",
                            "nombre"      =>    "j.nombre",
                            "descripcion" =>    "j.descripcion", 
                            "id_imagen"   =>    "j.id_imagen",
                            "idImagen"    =>    "i.id",
                            "ruta"        =>    "i.ruta"
                            );
            $condicion = "(j.id_imagen = i.id AND j.nombre REGEXP '(".implode("|", $palabras).")') OR( j.id_imagen = i.id AND j.descripcion REGEXP '(".implode("|", $palabras).")')";
            
            //$sql->depurar = true;
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
                        
            $resaltado = HTML::frase($datos['patron'], "resaltado");
            $listaJuegos = array();
            
            while ($fila = $sql->filaEnObjeto($consulta)) {
                $imagen   =  $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesDinamicas"]."/".$fila->imagen;        
                $item     = HTML::enlace(HTML::imagen($imagen, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("JUEGOS", $fila->id));                           
                $item3    = HTML::parrafo(HTML::enlace(str_ireplace($palabras, $resaltado, $fila->nombre)." "." ".HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."goButton.png"), HTML::urlInterna("JUEGOS", $fila->id)), "negrilla");
                $item3   .= HTML::parrafo( substr($fila->descripcion, 0, 50)."...", " cursiva pequenia");                
                $item     = HTML::contenedor($item3, "fondoBuscadorBlogs");//barra del contenedor gris
                $listaJuegos[] = $item;

             }

            $listaJuegos = HTML::lista($listaJuegos, "listaVertical listaConIconos bordeSuperiorLista");
                

            $respuesta["accion"]    = "insertar";
            $respuesta["contenido"] = $listaJuegos;
            $respuesta["destino"]   = "#resultadosBuscarJuegos";

        } else {
            $respuesta["error"]   = true;
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CADENA_BUSQUEDA");
        }

    }

    Servidor::enviarJSON($respuesta);
}




?>