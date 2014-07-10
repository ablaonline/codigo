<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Enlaces
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

if (isset($url_accion)) {
    switch ($url_accion) {
        case "addLink"  :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                adicionarEnlace($datos);
                                break;

        case "deleteLink":  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                eliminarEnlace($forma_id, $confirmado);
                                break;


        case "searchLinks" : ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                buscarEnlaces($forma_datos);
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

function adicionarEnlace($datos = array()) {
    global $textos, $forma_idModulo, $forma_idRegistro;

    $modulo= new Modulo("ENLACES");
    $destino = "/ajax/".$modulo->url."/addLink";

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos[idModulo]", $forma_idModulo);
        $codigo .= HTML::campoOculto("datos[idRegistro]", $forma_idRegistro);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[descripcion]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("ENLACE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[enlace]", 50, 255);
        //$codigo .= HTML::parrafo($textos->id("SELECCIONE_CATEGORIA"), "negrilla margenSuperior");
        //$codigo .= Categoria::mostrarSelectCategorias($modulo->idModulo);//este metodo devuelve el select con las categorias pertenecientes a Blogs
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", true).$textos->id("ACTIVO"), "margenSuperior");
        $codigo .= Perfil::mostrarChecks("");//metodo que devuelve los checks para escoger los perfiles
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_ENLACE"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 450;
        $respuesta["alto"]    = 400;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

        } elseif (empty($datos["enlace"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_ENLACE");

        } elseif ($datos["visibilidad"]=="privado" && empty($datos["perfiles"])  ) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SELECCIONAR_PERFILES");

        } else {

                $enlace = new Enlace();
                $idEnlace = $enlace->adicionar($datos);

                if ( $idEnlace ) {
                    
         /********************** En este Bloque se Arma el Contenido del nuevo Documento que se acaba de Registrar  **********************/
                    $enlace  = new Enlace($idEnlace); 
                    
                    $botonEliminar = HTML::nuevoBotonEliminarRegistro($enlace->id, "links/deleteLink");
                    $botonEliminar = HTML::contenedor($botonEliminar, "botonesLista", "botonesLista");          
                    $contenidoEnlace   = $botonEliminar;
                    $contenidoEnlace  .= HTML::enlace(HTML::imagen($enlace->icono, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $enlace->enlace);
                    $contenidoEnlace  .= HTML::parrafo(HTML::enlace($enlace->titulo, $enlace->enlace));
                    $contenidoEnlace2  = HTML::parrafo($enlace->descripcion);
                    $contenidoEnlace2 .= HTML::parrafo(HTML::frase($textos->id("ENLACE").": ", "negrilla").$enlace->enlace, "margenSuperior");
                    $contenidoEnlace  .= HTML::contenedor($contenidoEnlace2, "contenedorGrisLargo");                
                    $contenidoEnlace   = "<li class = 'botonesOcultos' style='border-top: 1px dotted #E0E0E0;'>".HTML::contenedor($contenidoEnlace, "contenedorListaDocumentos", "contenedorDocumento".$enlace->id)."</li>";
            
        /*******************************************************************************************************************************/
                    $respuesta = array();
                    $respuesta["error"]                = false;
                    $respuesta["accion"]               = "insertar";
                    $respuesta["contenido"]            = $contenidoEnlace;
                    $respuesta["idContenedor"]         = "#contenedorEnlace".$idEnlace;
                    $respuesta["insertarAjax"]         = true;
                    $respuesta["destino"]              = "#listaEnlaces";

                } else {
                    $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
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
function eliminarEnlace($id, $confirmado) {
    global $textos;

    $enlace       = new Enlace($id);
    $modulo                                                                                      
    = new Modulo("ENLACES");
    $destino      = "/ajax/".$modulo->url."/deleteLink";
    $respuesta = "";

    if (!$confirmado) {
        $nombre  = HTML::frase($enlace->titulo, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION_ENLACE"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_ENLACE"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 170;

    } else {
        
        if ($enlace->eliminar()) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "insertar";
            $respuesta["idContenedor"]      = "#contenedorEnlace".$id;
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

function buscarEnlaces($datos) {
    global $textos, $sql, $configuracion, $sesion_usuarioSesion;

    $enlace = new Enlace();
    $destino = "/ajax".$enlace->urlBase."/searchLinks";

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
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("BUSCAR_ENLACES"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
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