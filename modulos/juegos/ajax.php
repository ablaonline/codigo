<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Juegos
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 * 
 * Modificado el 17-01-12
 *
 **/

if (isset($url_accion)) {
    switch ($url_accion) {
        case "add"          :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                adicionarJuego($datos);
                                break;
        case "edit"         :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                modificarJuegoInternamente($forma_id, $datos);
                                break;
        case "editRegister" :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                modificarJuegoDesdeLista($forma_id, $datos);
                                break;
        case "delete"       :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                eliminarJuego($forma_id, $confirmado);
                                break;

        case "deleteRegister" :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                eliminarJuegoDesdeLista($forma_id, $confirmado);
                                break;

        case "searchGames" :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                buscarJuegos($forma_datos);
                                break;        
    }
}

function adicionarJuego($datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    $juego   = new Juego();
    $destino = "/ajax".$juego->urlBase."/add";

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, "", "editor");
        $codigo .= HTML::parrafo($textos->id("SCRIPT"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[script]", 5, 60);
        $codigo .= HTML::parrafo($textos->id("IMAGEN"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("imagen", 50, 255);
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", true).$textos->id("ACTIVO"), "margenSuperior");        
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk").HTML::frase("     ".$textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        $codigo .= "";
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_JUEGO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 500;
        $respuesta["alto"]    = 570;

    } else {
        $respuesta["error"]   = true;  
        if(!empty($archivo_imagen["tmp_name"])){
            $validarFormato = Archivo::validarArchivo($archivo_imagen, array("jpg","png","gif", "jpeg"));
            $area    = getimagesize($archivo_imagen["tmp_name"]);
        }
        
        if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

        } elseif (empty($datos["script"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SCRIPT");

        } elseif (empty($archivo_imagen["tmp_name"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_IMAGEN");

        } elseif ($validarFormato) {
                $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_IMAGEN");

        } elseif ($area[0] != $configuracion["DIMENSIONES"]["JUEGOS"][0] || $area[1] != $configuracion["DIMENSIONES"]["JUEGOS"][1]) {
                $respuesta["mensaje"] = $textos->id("ERROR_AREA_IMAGEN_JUEGO");

        } else {

         
                if ($idJuego = $juego->adicionar($datos)) {

        /********************** En este Bloque se Arma el Contenido del nuevo Juego que se acaba de Registrar  **********************/
                    $juego   = new Juego($idJuego);              
                    $botonModificar = HTML::nuevoBotonModificarItem($juego->id, $juego->urlBase);
                    $botonEliminar  = HTML::nuevoBotonEliminarItem($juego->id, $juego->urlBase);
                    $item          .= HTML::contenedor($botonEliminar.$botonModificar, "botonesLista", "botonesLista");
                    $item          .= HTML::enlace(HTML::imagen($juego->imagen, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $juego->url);
                    $item          .= HTML::enlace(HTML::parrafo($juego->nombre, "negrilla"), $juego->url);
                    $item          .= HTML::parrafo($juego->descripcion, "margenInferior");
                    $item           = HTML::contenedor($item, "tablaJuegosAjax");
                    $contenidoJuego = "<li class = 'botonesOcultos' style='border-top: 1px dotted #E0E0E0;'>".HTML::contenedor($item, "contenedorListaJuegos", "contenedorListaJuegos".$juego->id)."</li>";
        /*******************************************************************************************************************************/

                    $respuesta["error"]                = false;
                    $respuesta["accion"]               = "insertar";
                    $respuesta["contenido"]            = $contenidoJuego;
                    $respuesta["idContenedor"]         = "#contenedorListaJuegos".$idJuego;
                    $respuesta["insertarAjax"]         = true;
                    $respuesta["destino"]              = "#nuevosRegistros";

                } else {
                    $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                }
            }

        }    

    Servidor::enviarJSON($respuesta);
}





/**
 *
 * Funcion que se encarga de modificar un juego via Ajax desde el listado general
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @param type $id = identificador del juego en la base de datos que se va a modificar
 * @param type $datos = los datos del juego que van a ser reemplazados
 */ 
function modificarJuegoDesdeLista($id, $datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    $juego   = new Juego($id);
    $destino = "/ajax".$juego->urlBase."/editRegister";

    if (empty($datos)) {
        
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::campoOculto("datos[id_imagen]", $juego->idImagen);
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 50, 255, $juego->nombre);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, $juego->descripcion, "editor");
        $codigo .= HTML::parrafo($textos->id("SCRIPT"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[script]", 5, 60, $juego->script);
        $codigo .= HTML::parrafo($textos->id("IMAGEN"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("imagen", 50, 255);
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", $juego->activo).$textos->id("ACTIVO"), "margenSuperior");        
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk").HTML::frase("     ".$textos->id("REGISTRO_MODIFICADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_JUEGO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 500;
        $respuesta["alto"]    = 570;     

    } else {
        $respuesta["error"]   = true;

        if(!empty($archivo_imagen["tmp_name"])){
            $validarFormato = Archivo::validarArchivo($archivo_imagen, array("jpg","png","gif", "jpeg"));
            $area    = getimagesize($archivo_imagen["tmp_name"]);
        }

       if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

        } elseif (empty($datos["script"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SCRIPT");

        } elseif ($area[0] != $configuracion["DIMENSIONES"]["JUEGOS"][0] || $area[1] != $configuracion["DIMENSIONES"]["JUEGOS"][1]) {
                $respuesta["mensaje"] = $textos->id("ERROR_AREA_IMAGEN_JUEGO");

        } else {                 
                                  
                    if ($juego->modificar($datos)) {
            /********************** En este Bloque se Arma el Contenido del nuevo Juego que se acaba de Registrar  **********************/
                        $juego   = new Juego($id);                   
                        $botonEliminar  = HTML::nuevoBotonEliminarItem($juego->id, $juego->urlBase);
                        $botonModificar = HTML::nuevoBotonModificarItem($juego->id, $juego->urlBase);
                        $item          .= HTML::contenedor($botonEliminar.$botonModificar, "botonesLista", "botonesLista");
                        $item          .= HTML::enlace(HTML::imagen($juego->imagen, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $juego->url);
                        $item          .= HTML::enlace(HTML::parrafo($juego->nombre, "negrilla"), $juego->url);
                        $item          .= HTML::parrafo($juego->descripcion, "margenInferior");
                        $item           = HTML::contenedor($item, "tablaJuegosAjax");
                        $contenidoJuego = $item;/*HTML::contenedor($item, "contenedorListaJuegos", "contenedorListaJuegos".$juego->id);*/
            /*******************************************************************************************************************************/

                        $respuesta["error"]              = false;
                        $respuesta["accion"]             = "insertar";
                        $respuesta["contenido"]          = $contenidoJuego;
                        $respuesta["idContenedor"]       = "#contenedorListaJuegos".$id;
                        $respuesta["modificarAjaxLista"] = true;
                        //$respuesta["destino"]       = "#nuevosRegistros";

                    } else {
                        $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                    }


            }
    
    }
    Servidor::enviarJSON($respuesta);
    
 }

/**
 *
 * Funcion que se encarga de modificar un juego via Ajax desde dentro del item,
 *  es decir visualizando el juego completo.
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @param type $id = identificador del juego en la base de datos que se va a modificar
 * @param type $datos = los datos del juego que van a ser reemplazados
 */ 
function modificarJuegoInternamente($id, $datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    $juego   = new Juego($id);
    $destino = "/ajax".$juego->urlBase."/edit";

    if (empty($datos)) {
        
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::campoOculto("datos[id_imagen]", $juego->idImagen);
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 50, 255, $juego->nombre);
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, $juego->descripcion, "editor");
        $codigo .= HTML::parrafo($textos->id("SCRIPT"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[script]", 5, 60, $juego->script);
        $codigo .= HTML::parrafo($textos->id("IMAGEN"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("imagen", 50, 255);
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", $juego->activo).$textos->id("ACTIVO"), "margenSuperior");        
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk").HTML::frase("     ".$textos->id("REGISTRO_MODIFICADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        
        $codigo  = HTML::forma($destino, $codigo, "P", true);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_JUEGO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 500;
        $respuesta["alto"]    = 570;     

    } else {
        $respuesta["error"]   = true;
        if(!empty($archivo_imagen["tmp_name"])){
            $validarFormato = Archivo::validarArchivo($archivo_imagen, array("jpg","png","gif", "jpeg"));
            $area    = getimagesize($archivo_imagen["tmp_name"]);
        }

       if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif (empty($datos["descripcion"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

        } elseif (empty($datos["script"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SCRIPT");

        } elseif ($area[0] != $configuracion["DIMENSIONES"]["JUEGOS"][0] || $area[1] != $configuracion["DIMENSIONES"]["JUEGOS"][1]) {
                $respuesta["mensaje"] = $textos->id("ERROR_AREA_IMAGEN_JUEGO");

        } else {               
                                  
                    if ($juego->modificar($datos)) {
            /********************** En este Bloque se Arma el Contenido del nuevo Juego que se acaba de Registrar  **********************/
                        $juego   = new Juego($id);                   
                       // if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
                            $botones        = "";
                            $botones       .= HTML::nuevoBotonEliminarItemInterno($juego->id, $juego->urlBase);
                            $botones       .= HTML::nuevoBotonModificarItemInterno($juego->id, $juego->urlBase);            
                            $botones        = HTML::contenedor($botones, "botonesInternos", "botonesInternos");
                       // }
                        $contenidoJuego    = $botones;
                        $contenidoJuego   .= HTML::contenedor($juego->script, "centrado");
                        $contenidoJuego   .= HTML::contenedor(HTML::nuevosBotonesCompartir());
                        $contenidoJuego    = HTML::bloque("juego_".$juego->id, $juego->nombre, $contenidoJuego, "", "botonesOcultos");
                       
                        /*******************************************************************************************************************************/

                        $respuesta["error"]                 = false;
                        $respuesta["accion"]                = "insertar";
                        $respuesta["contenido"]             = $contenidoJuego;
                        $respuesta["idContenedor"]          = "#bloqueComentariosJuego".$id;
                        $respuesta["modificarAjaxInterno"]  = true;
                        //$respuesta["destino"]       = "#nuevosRegistros";

                    } else {
                        $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                    }


            }
    
    }
    Servidor::enviarJSON($respuesta);
    
 }

 
 
 
 

/**
*
*Metodo para eliminar Juegos del sistema
*
**/
function eliminarJuego($id, $confirmado) {
    global $textos, $sql;

    $juego    = new Juego($id);
    $destino = "/ajax".$juego->urlBase."/delete";

    if (!$confirmado) {
        $titulo  = HTML::frase($juego->titulo, "negrilla");
        $titulo  = str_replace("%1", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_JUEGO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 270;
        $respuesta["alto"]    = 170;
    } else {

        if ($juego->eliminar()) {
            $respuesta["error"]                     = false;
            $respuesta["accion"]                    = "insertar";
            $respuesta["idContenedor"]              = "#juego_".$id;
            $respuesta["idContenedorComentarios"]   = "#bloqueComentariosJuego".$id;
            $respuesta["eliminarAjaxInterno"]       = true;

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}




/**
*
*Metodo para eliminar Juegos del sistema directamente desde la lista Utilizando Ajax
*
**/
function eliminarJuegoDesdeLista($id, $confirmado) {
    global $textos, $sql;

    $juego    = new Juego($id);
    $destino = "/ajax".$juego->urlBase."/deleteRegister";

    if (!$confirmado) {
        $titulo  = HTML::frase($juego->nombre, "negrilla");
        $titulo  = str_replace("%1", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_JUEGO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 270;
        $respuesta["alto"]    = 170;
    } else {

        if ($juego->eliminar()) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "insertar";
            $respuesta["idContenedor"]      = "#contenedorListaJuegos".$id;
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

function buscarJuegos($datos) {
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
                        $palabrasResaltadas[] =  HTML::frase($palabra, "resaltado");
                        $palabrasMarcadas[]   =  "%".$palabra."%";
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
                        
            
            $listaJuegos = array();
            
            if($sql->filasDevueltas){
                while ($fila = $sql->filaEnObjeto($consulta)) {
                    
                    $nombre = str_ireplace($palabras, $palabrasMarcadas, $fila->nombre);
                    
                    $imagen   =  $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesDinamicas"]."/".$fila->imagen;        
                    $item     = HTML::enlace(HTML::imagen($imagen, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("JUEGOS", $fila->id));                           
                    $item3    = HTML::parrafo(HTML::enlace(str_ireplace($palabrasMarcadas, $palabrasResaltadas, $nombre)." "." ".HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."goButton.png"), HTML::urlInterna("JUEGOS", $fila->id)), "negrilla");
                    $item3   .= HTML::parrafo( substr($fila->descripcion, 0, 50)."...", " cursiva pequenia");                
                    $item     = HTML::contenedor($item3, "fondoBuscadorBlogs");//barra del contenedor gris
                    $listaJuegos[] = $item;

                 }//fin del while
            }//fin del if
            
            
            if(sizeof($listaJuegos) == 0){
                $listaJuegos[] = HTML::frase($textos->id("SIN_REGISTROS"));
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