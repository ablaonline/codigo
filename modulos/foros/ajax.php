<?php

/**
 * @package     FOLCS
 * @subpackage  Foros
 * @author      Pablo Andres Velez Vidal
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 ColomboAmericano
 * @version     0.1
 * Modificado el 17-01-12
 **/
global $url_accion, $forma_procesar, $forma_id, $forma_datos;

if (isset($url_accion)) {
    switch ($url_accion) {
        case "addTopic"         :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    adicionarTema($datos);
                                    break;
       case "add"               :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    adicionarTema($datos);
                                    break;
       case "edit"             :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    modificarTema($forma_id, $datos);
                                    break;
        case "replyTopic"       :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    responderTema($datos);
                                    break;
        case "deleteTopic"      :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarTema($forma_id, $confirmado);
                                    break;
        case "delete"           :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarTema($forma_id, $confirmado);
                                    break;                                
        case "deleteRegister"   :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarTemaAjax($forma_id, $confirmado);
                                    break;                                

        case "deleteReply"      :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarMensaje($forma_id, $confirmado);
                                    break;
        case "searchForums"     :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    buscarForos($forma_datos);
                                    break;   

    }
}


/**
 * Funcion Para agregar un nuevo foro dentro de un item de un modulo
 * @global type $textos
 * @global type $sql
 * @global type $forma_idModulo
 * @global type $forma_idRegistro
 * @param type $datos 
 */
function adicionarTema($datos = array()) {
    global $textos, $forma_idModulo, $forma_idRegistro, $configuracion;

    $foro       = new Foro();
    $destino    = "/ajax/".$foro->url."/addTopic";
    $respuesta  = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos[idModulo]", $forma_idModulo);
        $codigo .= HTML::campoOculto("datos[idRegistro]", $forma_idRegistro);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[contenido]", 5, 50);

        if(empty($forma_idRegistro)){
          $codigo .= HTML::parrafo($textos->id("SELECCIONE_CATEGORIA"), "negrilla margenSuperior");
          $codigo .= Categoria::mostrarSelectCategorias($foro->idModulo);
        }
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", true).$textos->id("ACTIVO"), "margenSuperior");  
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[notificar_estudiantes]', true).$textos->id('NOTIFICAR_ESTUDIANTES'), 'margenSuperior');
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "", "botonEnviar"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("INICIAR_TEMA_FORO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 450;
        $respuesta["alto"]    = 350;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["contenido"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");

        } else {
            $foro = new Foro();
            $idForo = $foro->adicionar($datos);
            if ($idForo) {                
          /****** ELABORO EL FORO QUE ACABO DE INGRESAR EN LA BASE DE DATOS PARA ENVIARLO VIA AJAX *******/
                $foro           = new Foro($idForo);
                $botones        = HTML::nuevoBotonEliminarRegistro($idForo, "forums/deleteRegister");
                $botonEliminar  = HTML::contenedor($botones, "botonesLista", "botonesLista");            
                 
                $contenidoForo  = $botonEliminar;
                //seleccionar el genero de una persona 
                $usuario         =  new Usuario();
                $contenidoForo  .= HTML::enlace(HTML::imagen($foro->fotoAutor, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("USUARIOS", $foro->usuarioAutor));
                $contenidoForo  .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].$usuario->getGenero($foro->idAutor).".png").preg_replace("/\%1/", HTML::enlace($foro->autor, HTML::urlInterna("USUARIOS", $foro->usuarioAutor)), $textos->id("PUBLICADO_POR")));
                $contenidoForo2  = HTML::enlace(HTML::parrafo($foro->titulo, "negrilla"), $foro->enlace);
                $contenidoForo2 .= HTML::parrafo(date("D, d M Y h:i:s A", $foro->fecha), "pequenia cursiva negrilla");
                $contenidoForo  .= HTML::contenedor($contenidoForo2, "fondoUltimos5GrisB"); //barra del contenedor gris
                $respuestaForo   = "<li class = 'botonesOcultos' style='border-top: 1px dotted #E0E0E0;'>".HTML::contenedor($contenidoForo, "contenedorListaForos", "contenedorForo".$foro->id)."</li>";
         /************************************************************************************************/          
                $respuesta["error"]                = false;
                $respuesta["accion"]               = "insertar";
                $respuesta["contenido"]            = $respuestaForo;
                $respuesta["idContenedor"]         = "#contenedorForo".$idForo;
                $respuesta["insertarAjax"]         = true;
                $respuesta["destino"]              = "#listaForos";

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $forma_idModulo
 * @global type $forma_idRegistro
 * @param type $id
 * @param type $datos 
 */
function modificarTema($id, $datos = array()) {
    global $textos, $forma_idModulo, $forma_idRegistro;

    $foro      = new Foro($id);
    $destino   = "/ajax/".$foro->url."/edit";
    $respuesta = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos[id]", $id);
        $codigo .= HTML::campoOculto("datos[idModulo]", $forma_idModulo);
        $codigo .= HTML::campoOculto("datos[idRegistro]", $forma_idRegistro);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255, $foro->titulo);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[contenido]", 5, 50, $foro->descripcion);

        if(empty($forma_idRegistro)){
          $codigo .= HTML::parrafo($textos->id("SELECCIONE_CATEGORIA"), "negrilla margenSuperior");
          $codigo .= Categoria::mostrarSelectCategorias($foro->idModulo);
        }
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", true).$textos->id("ACTIVO"), "margenSuperior");  
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "", "botonEnviar"), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_FORO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 450;
        $respuesta["alto"]    = 350;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["contenido"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");

        } else {
            $foro = new Foro();

            if ($foro->modificar($datos)) {
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
 * @global type $textos
 * @global type $sql
 * @global type $forma_idForo
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function responderTema($datos = array()) {
    global $textos, $forma_idForo, $sesion_usuarioSesion;

    $destino   = "/ajax/forums/replyTopic";
    $respuesta = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos[idForo]", $forma_idForo);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[contenido]", 6, 50, "", "", "txtAreaLimitado511");
        $codigo .= HTML::parrafo($textos->id("MAXIMO_TEXTO_511"), "maximoTexto", "maximoTexto");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "", "botonEnviar"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"]   = true;
        
        $respuesta["codigo"]    = $codigo;
        $respuesta["destino"]   = "#cuadroDialogo";
        $respuesta["titulo"]    = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("RESPONDER"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]     = 450;
        $respuesta["alto"]      = 270;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["contenido"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");

        } else {
            $foro = new Foro();
            $idMensaje = $foro->adicionarMensaje($datos["idForo"], $datos["contenido"]);

            if ($idMensaje) {

                $respuesta["error"]   = false;
                $respuesta["accion"]  = "insertar";             
      
                $url     =  HTML::urlInterna("FOROS", "", true, "deleteReply");
                $boton   = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                            Delete</span></button>";
                $identificador = HTML::campoOculto("id", $idMensaje);
                
                $botonEliminar = HTML::contenedor(HTML::forma($url, $boton.$identificador), "botonesForo");                          

                $autor       = HTML::parrafo(preg_replace("/\%1/", HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna("USUARIOS", $sesion_usuarioSesion->usuario)), $textos->id("PUBLICADO_POR")));
                $imagenAutor = HTML::enlace(HTML::imagen($sesion_usuarioSesion->persona->imagenMiniatura, "miniaturaForos"), HTML::urlInterna("USUARIOS", $sesion_usuarioSesion->usuario));
                
                $descripcion = HTML::parrafo($datos[contenido], "margenInferior");
                $fecha       = HTML::parrafo(date("D, d M Y h:i:s A"), "pequenia cursiva negrilla derecha");
                $header     = HTML::contenedor($fecha, "headerMensajes");
                $subHeader  = HTML::contenedor($imagenAutor.$autor.$botonEliminar, "subHeaderMensajes");
                $centro     = HTML::contenedor($descripcion, "centerMensajes");
                $contenidoForo  = "<li>".HTML::contenedor($header.$subHeader.$centro, "cuadroForo oculto", "cuadroForo".$idMensaje)."</li>";

                $respuesta["contenido"]    = $contenidoForo; 
                $respuesta["idContenedor"] = "#cuadroForo".$idMensaje;
                $respuesta["insertarAjax"] = true;
                $respuesta["destino"]      = "#nuevosMensajes";

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function eliminarTema($id, $confirmado) {
    global $textos;

    $foro = new Foro($id);
    $destino      = "/ajax/forums/deleteTopic";
    $respuesta    = array();

    if (!$confirmado) {
        $nombre  = HTML::frase($foro->autor, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION_TEMA"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "", "botonEnviar"), "margenSuperior");        
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_TEMA"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 360;
        $respuesta["alto"]    = 150;

    } else {

        if ($foro->eliminar()) {
            $respuesta["error"]   = false;
            $respuesta["accion"]  = "recargar";

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }
    Servidor::enviarJSON($respuesta);
}


/**
 *
 * Funcion para eliminar un foro desde la lista via Ajax
 * 
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function eliminarTemaAjax($id, $confirmado) {
    global $textos;

    $foro         = new Foro($id);
    $destino      = "/ajax/".$foro->url."/deleteRegister";
    $respuesta    = array();

    if (!$confirmado) {
        $nombre  = HTML::frase($foro->autor, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION_TEMA"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "", "botonEnviar"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");       
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_TEMA"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 150;

    } else {

        if ($foro->eliminar()) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "insertar";
            $respuesta["idContenedor"]      = "#contenedorForo".$id;
            $respuesta["eliminarAjaxLista"] = true;

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }
    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function eliminarMensaje($id, $confirmado) {
    global $textos;

    $foro       = new Foro();    
    $destino    = "/ajax/".$foro->url."/deleteReply";
    $respuesta  = array();

    if (!$confirmado) {
        $nombre  = HTML::frase($foro->autor, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION_MENSAJE"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "", "botonEnviar"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_MENSAJE"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 150;

    } else {

        if ($foro->eliminarMessage($id)) {
            $respuesta["error"]   = false;
            $respuesta["accion"]  = "insertar";
            $respuesta["idContenedor"] = "#cuadroForo".$id;
            $respuesta["eliminarAjaxLista"] = true;

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }
    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function buscarForos($datos) {
    global $textos, $sql, $configuracion;

    $foro      = new Foro();
    $destino   = "/ajax".$foro->urlBase."/searchForums";
    $respuesta = array();

    if (empty($datos)) {

        $forma  = HTML::campoOculto("datos[criterio]", "titulo");
        $forma .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $forma .= HTML::parrafo(HTML::campoTexto("datos[patron]", 30, 255).HTML::boton("buscar", $textos->id("BUSCAR", "", "", "botonEnviar")), "margenSuperior");

        $codigo1  = HTML::forma($destino, $forma);
        $codigo   = HTML::contenedor($codigo1, "bloqueBorde");
        $codigo  .= HTML::contenedor("","margenSuperior", "resultadosBuscarNoticias");

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("BUSCAR_FOROS"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 530;
        $respuesta["alto"]    = 400;

    } else {

     if (!empty($datos["criterio"]) && !empty($datos["patron"])) {

          if ($datos["criterio"] == "titulo") {

                $palabras = explode(" ", $datos["patron"]);

                foreach ($palabras as $palabra) {
                    $palabrasResaltadas[] =  HTML::frase($palabra, "resaltado");
                    $palabrasMarcadas[]   =  "%".$palabra."%";
                }               
                
            }

         $condicion = "(f.titulo REGEXP '(".implode("|", $palabras).")' AND tipo = 2) OR (f.descripcion REGEXP '(".implode("|", $palabras).")' AND tipo = 2)";
         
            
            $tablas = array(
                        "f"  => "foros"
                            );

            $columnas = array(                               
                        "id"                =>  "f.id",
                        "titulo"            =>  "f.titulo",
                        "descripcion"       =>  "f.descripcion",
                        "fecha"             =>  "f.fecha",
                        "id_usuario"        =>  "f.id_usuario"
                            );

            //$sql->depurar = true;
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            
                     
            if ($sql->filasDevueltas) {
                $listaForos = array();
                while ($fila = $sql->filaEnObjeto($consulta)) {

                    $autor = $sql->obtenerValor("usuarios", "sobrenombre", "id = '".$fila->id_usuario."'");
                    $titulo = str_ireplace($palabras, $palabrasMarcadas, $fila->titulo);

                    $item3   = HTML::parrafo(str_replace("%1", $autor, $textos->id("CREADO_POR")), "negrilla");
                    $item3  .= HTML::parrafo(HTML::enlace(str_ireplace($palabrasMarcadas, $palabrasResaltadas, $titulo)." "." ".HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."goButton.png"), HTML::urlInterna("FOROS", $fila->id)), "negrilla");
                    $item3  .= HTML::parrafo(str_replace("%1", $fila->fecha, $textos->id("PUBLICADO_EN")), "negrilla cursiva pequenia");                  

                    $item    = HTML::contenedor($item3, "fondoBuscadorNoticias");//barra del contenedor gris
                    $listaForos[] = $item;   

                }
            }    
               if(sizeof($listaForos) == 0){
                    $listaForos[] = HTML::frase($textos->id("SIN_REGISTROS"));
                }

                $listaForos = HTML::lista($listaForos, "listaVertical listaConIconos bordeSuperiorLista");
            

            $respuesta["accion"]    = "insertar";
            $respuesta["contenido"] = $listaForos;
            $respuesta["destino"]   = "#resultadosBuscarNoticias";

        } else {
            $respuesta["error"]   = true;
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CADENA_BUSQUEDA");
        }

    }

    Servidor::enviarJSON($respuesta);
}


?>