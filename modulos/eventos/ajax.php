<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Eventos
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 * 
 * Modificado el 17-01-12
 *
 **/

global $url_accion, $forma_procesar, $forma_datos, $forma_id;

if (isset($url_accion)) {
    switch ($url_accion) {
        case "add"        :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              adicionarEvento($datos);
                               break; 
        case "addCulturalEvent" :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                adicionarEventoCultural($datos);
                                break;  
        case "edit"       :    ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              modificarEvento($forma_id, $datos);
                               break;
                           
        case "editRegister":   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              modificarEventoDesdeLista($forma_id, $datos);
                               break;
        case "delete"     :    ($forma_procesar) ? $confirmado = true : $confirmado = false;
                               eliminarEvento($forma_id, $confirmado);
                               break;
        case "deleteRegister" :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                               eliminarEventoDesdeLista($forma_id, $confirmado);
                               break;
        case "searchEvents" :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                buscarEventos($forma_datos);
                                break;
    }
}


/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function adicionarEvento($datos = array()) {
    global $textos, $configuracion, $archivo_imagen;

    $evento    = new Evento();
    $destino   = "/ajax".$evento->urlBase."/add";
    $respuesta = array();
    if (empty($datos)) {
        
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 40, 255, "", "", "", array("alt" => $textos->id("INGRESE_TITULO")));
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION_CORTA"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[resumen]", 60, 255, "", "", "", array("alt" => $textos->id("INGRESE_DESCRIPCION_CORTA")));
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, "", "editor", "", array("alt" => $textos->id("INGRESE_DESCRIPCION")));
        $codigo .= HTML::parrafo($textos->id("IMAGEN"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("imagen", 50, 255);
        $codigo .= HTML::parrafo($textos->id("SELECCIONE_CATEGORIA"), "negrilla margenSuperior");
        $codigo .= Categoria::mostrarSelectCategorias($evento->idModulo);
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", true).$textos->id("ACTIVO"), "margenSuperior");       
        $codigo .= Perfil::mostrarChecks("");//mostrar checkboxes para compartir perfiles
        $pestana1 = $codigo;
        $codigo  = HTML::parrafo($textos->id("CIUDAD_EVENTO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[ciudad]", 50, 255, "", "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCities"), "alt" => $textos->id("SELECCIONE_CIUDAD")));
        $codigo .= HTML::parrafo($textos->id("CENTRO_EVENTO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[centro]", 50, 255, "", "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCenters"), "alt" => $textos->id("SELECCIONE_CENTRO")));
        $codigo .= HTML::parrafo($textos->id("LUGAR"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[lugar]", 50, 255, "", "", "", array("alt" => $textos->id("INGRESE_LUGAR")));
        $codigo .= HTML::parrafo($textos->id("FECHA_INICIO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[fecha_inicio]", 12, 12, "", "fechaAntigua", "fechaInicio", array("alt" => $textos->id("SELECCIONE_FECHA_INICIO")));
        $codigo .= HTML::parrafo($textos->id("HORA_INICIO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[hora_inicio]", 50, 255, "", "selectorHora", "horaInicio", array("alt" => $textos->id("SELECCIONE_HORA_INICIO")));
        $codigo .= HTML::parrafo($textos->id("FECHA_FIN"), "negrilla margenSuperior oculto", "textoFechaFin");
        $codigo .= HTML::campoTexto("datos[fecha_fin]", 12, 12, "", "selectorFechaValidada oculto", "fechaFin", array("alt" => $textos->id("SELECCIONE_FECHA_FIN")));
        $codigo .= HTML::parrafo($textos->id("HORA_FIN"), "negrilla margenSuperior oculto", "textoHoraFin");
        $codigo .= HTML::campoTexto("datos[hora_fin]", 50, 255, "", "selectorHora oculto", "horaFin", array("alt" => $textos->id("SELECCIONE_HORA_FIN")));
        $codigo .= HTML::parrafo($textos->id("INFO_CONTACTO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[info_contacto]", 5, 60, "", "", "txtAreaLimitado300", array("alt" => $textos->id("INGRESE_INFO_CONTACTO")));
        $codigo .= HTML::parrafo($textos->id("MAXIMO_TEXTO_250"), "maximoTexto", "maximoTexto");

        $pestana2 = $codigo;
        
        $pestana3 = Galeria::formularioAdicionarGaleria();

        $pestanas = array(
            HTML::frase($textos->id("DATOS_EVENTO"), "letraBlanca") => $pestana1,
            HTML::frase($textos->id("INFORMACION_EVENTO"), "letraBlanca") => $pestana2,
            HTML::frase($textos->id("AGREGAR_GALERIA"), "letraBlanca") => $pestana3
        );
        
        $codigo = HTML::pestanas2("", $pestanas);
        
        $opciones = array("onClick" => "validarFormaEvento();");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "directo", "botonOk", "botonFormaEvento", "", $opciones).HTML::frase("     ".$textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true, "formaEvento");

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_EVENTO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 750;
        $respuesta["alto"]    = 800;

    } else {
        $respuesta["error"]   = true;
        if(!empty($archivo_imagen["tmp_name"])){
            $validarFormato = Recursos::validarArchivo($archivo_imagen, array("jpg","png","gif", "jpeg"));
            $area    = getimagesize($archivo_imagen["tmp_name"]);
         }
        
        $cantImagenes       = $datos["cantCampoImagenGaleria"];//aqui llega el valor del campo oculto en el formulario que determina cuantas imagenes nuevas se van a ingresar
                
        $erroresImagenes = Galeria::validarImagenesGaleria($cantImagenes);//metodo que se encarga de validar las imagenes que se quieres guardar en la galeria

        if($erroresImagenes != ""){//verifico si hubo imagenes con errores de formato
            $respuesta["mensaje"] = str_replace("%1", $erroresImagenes, $textos->id("ERROR_FORMATO_IMAGEN_GALERIA"));
            
        } else if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

            } elseif (empty($datos["resumen"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_RESUMEN");

            } elseif (empty($datos["descripcion"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

            } elseif (empty($datos["ciudad"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CIUDAD");

            } elseif (empty($datos["centro"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CENTRO");

            } elseif (empty($datos["lugar"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_LUGAR");

            } elseif (empty($datos["fecha_inicio"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_FECHA_INICIO");

            } elseif (empty($datos["hora_inicio"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_HORA_INICIO");

            }elseif (empty($datos["info_contacto"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_INFO_CONTACTO");

            } elseif (empty($archivo_imagen["tmp_name"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_IMAGEN");

            }  elseif ($datos["visibilidad"] == "privado" && empty($datos["perfiles"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SELECCIONAR_PERFILES");

            }elseif ($validarFormato) {
                $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_IMAGEN_EVENTO");
                
            }elseif ($area[0] != $configuracion["DIMENSIONES"]["EVENTOS"][0] || $area[1] != $configuracion["DIMENSIONES"]["EVENTOS"][1]) {
                        $respuesta["mensaje"] = $textos->id("ERROR_AREA_IMAGEN_EVENTO");
            }else {
          
                $idEvento = $evento->adicionar($datos);
                if ($idEvento) {  
                                       
                  /******* Armo el nuevo evento ya modificado y lo devuelvo via Ajax *******
                       $item = "";
                       $comentario  = new Comentario();
                       $elemento    = new Noticia($idNoticia);
                       $botonModificar = HTML::nuevoBotonModificarItem($elemento->id, $elemento->urlBase);
                       $botonEliminar  = HTML::nuevoBotonEliminarItem($elemento->id, $elemento->urlBase);
                       $item          .= HTML::contenedor($botonEliminar.$botonModificar, "botonesLista", "botonesLista");

                        $contenedorComentarios = $comentario->mostrarComentarios($noticia->idModulo, $elemento->id);
                        $contenedorMeGusta     = Recursos::mostrarContadorMeGusta($noticia->idModulo, $elemento->id);
                        $comentarios           = HTML::contenedor($contenedorComentarios.$contenedorMeGusta, "mostrarPosted");
                         //seleccionar el genero de una persona 
                        $persona =  new Persona($elemento->idAutor);
                        $item     .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("NOTICIAS", $elemento->id));
                        $item     .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].$persona->idGenero.".png").preg_replace("/\%1/", HTML::enlace($elemento->autor, HTML::urlInterna("USUARIOS", $elemento->usuarioAutor))."On ".HTML::frase(date("D, d M Y", $elemento->fechaPublicacion), "pequenia cursiva negrilla").$comentarios, $textos->id("PUBLICADO_POR")));
                        $item2     = HTML::enlace(HTML::parrafo($elemento->titulo, "negrilla"), $elemento->url);
                        $item2    .= HTML::parrafo($elemento->resumen, "pequenia cursiva");
                        $item     .= HTML::contenedor($item2, "fondoUltimos5GrisB"); //barra del contenedor gris
                        $contenidoNoticia = "<li class = 'botonesOcultos' style='border-top: 1px dotted #E0E0E0;'>".HTML::contenedor($item, "contenedorListaNoticias", "contenedorListaNoticias".$elemento->id)."</li>";
                  ***************************************************************************/
                    
                        $respuesta["error"]                = false;
                        $respuesta["accion"]               = "recargar";
//                        $respuesta["contenido"]            = $contenidoNoticia;
//                        $respuesta["idContenedor"]         = "#contenedorListaNoticias".$idNoticia;
//                        $respuesta["insertarAjax"]         = true;
//                        $respuesta["destino"]              = "#listaNoticias";

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
 * @global type $configuracion
 * @global type $archivo_imagen
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function adicionarEventoCultural($datos = array()) {
    global $textos, $configuracion, $archivo_imagen;

    $evento    = new Evento();
    $destino   = "/ajax".$evento->urlBase."/add";
    $respuesta = array();
    if (empty($datos)) {
        
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 40, 255, "", "", "", array("alt" => $textos->id("INGRESE_TITULO")));
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION_CORTA"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[resumen]", 60, 255, "", "", "", array("alt" => $textos->id("INGRESE_DESCRIPCION_CORTA")));
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, "", "editor", "", array("alt" => $textos->id("INGRESE_DESCRIPCION")));
        $codigo .= HTML::parrafo($textos->id("IMAGEN"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("imagen", 50, 255);
        $codigo .= HTML::parrafo($textos->id("SELECCIONE_CATEGORIA"), "negrilla margenSuperior");
        $codigo .= HTML::campoOculto("datos[categorias]", "10");//Categoria::mostrarSelectCategorias($evento->idModulo, "10");
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", true).$textos->id("ACTIVO"), "margenSuperior");       
        $codigo .= Perfil::mostrarChecks("");//mostrar checkboxes para compartir perfiles
        $pestana1 = $codigo;
        $codigo  = HTML::parrafo($textos->id("CIUDAD_EVENTO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[ciudad]", 50, 255, "", "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCities"), "alt" => $textos->id("SELECCIONE_CIUDAD")));
        $codigo .= HTML::parrafo($textos->id("CENTRO_EVENTO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[centro]", 50, 255, "", "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCenters"), "alt" => $textos->id("SELECCIONE_CENTRO")));
        $codigo .= HTML::parrafo($textos->id("LUGAR"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[lugar]", 50, 255, "", "", "", array("alt" => $textos->id("INGRESE_LUGAR")));
        $codigo .= HTML::parrafo($textos->id("FECHA_INICIO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[fecha_inicio]", 12, 12, "", "fechaAntigua", "fechaInicio", array("alt" => $textos->id("SELECCIONE_FECHA_INICIO")));
        $codigo .= HTML::parrafo($textos->id("HORA_INICIO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[hora_inicio]", 50, 255, "", "selectorHora", "horaInicio", array("alt" => $textos->id("SELECCIONE_HORA_INICIO")));
        $codigo .= HTML::parrafo($textos->id("FECHA_FIN"), "negrilla margenSuperior oculto", "textoFechaFin");
        $codigo .= HTML::campoTexto("datos[fecha_fin]", 12, 12, "", "selectorFechaValidada oculto", "fechaFin", array("alt" => $textos->id("SELECCIONE_FECHA_FIN")));
        $codigo .= HTML::parrafo($textos->id("HORA_FIN"), "negrilla margenSuperior oculto", "textoHoraFin");
        $codigo .= HTML::campoTexto("datos[hora_fin]", 50, 255, "", "selectorHora oculto", "horaFin", array("alt" => $textos->id("SELECCIONE_HORA_FIN")));
        $codigo .= HTML::parrafo($textos->id("INFO_CONTACTO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[info_contacto]", 5, 60, "", "", "txtAreaLimitado300", array("alt" => $textos->id("INGRESE_INFO_CONTACTO")));
        $codigo .= HTML::parrafo($textos->id("MAXIMO_TEXTO_250"), "maximoTexto", "maximoTexto");

        $pestana2 = $codigo;
        
        $pestana3 =  Galeria::formularioAdicionarGaleria();

        $pestanas = array(
            HTML::frase($textos->id("DATOS_EVENTO"), "letraBlanca") => $pestana1,
            HTML::frase($textos->id("INFORMACION_EVENTO"), "letraBlanca") => $pestana2,
             HTML::frase($textos->id("AGREGAR_GALERIA"), "letraBlanca") => $pestana3
        );
        
        $codigo = HTML::pestanas2("", $pestanas);
        
        $opciones = array("onClick" => "validarFormaEvento();");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "directo", "botonOk", "botonFormaEvento", "", $opciones).HTML::frase("     ".$textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true, "formaEvento");

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("AGREGAR_EVENTO_CULTURAL"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 750;
        $respuesta["alto"]    = 800;

    } else {
        $respuesta["error"]   = true;
        if(!empty($archivo_imagen["tmp_name"])){
            $validarFormato = Recursos::validarArchivo($archivo_imagen, array("jpg","png","gif", "jpeg"));
            $area    = getimagesize($archivo_imagen["tmp_name"]);
         }
        
        $cantImagenes       = $datos["cantCampoImagenGaleria"];//aqui llega el valor del campo oculto en el formulario que determina cuantas imagenes nuevas se van a ingresar
                
        $erroresImagenes = Galeria::validarImagenesGaleria($cantImagenes);//metodo que se encarga de validar las imagenes que se quieres guardar en la galeria

        if($erroresImagenes != ""){//verifico si hubo imagenes con errores de formato
            $respuesta["mensaje"] = str_replace("%1", $erroresImagenes, $textos->id("ERROR_FORMATO_IMAGEN_GALERIA"));
            
        } else if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

            } elseif (empty($datos["resumen"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_RESUMEN");

            } elseif (empty($datos["descripcion"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

            } elseif (empty($datos["ciudad"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CIUDAD");

            } elseif (empty($datos["centro"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CENTRO");

            } elseif (empty($datos["lugar"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_LUGAR");

            } elseif (empty($datos["fecha_inicio"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_FECHA_INICIO");

            } elseif (empty($datos["hora_inicio"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_HORA_INICIO");

            }elseif (empty($datos["info_contacto"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_INFO_CONTACTO");

            } elseif (empty($archivo_imagen["tmp_name"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_IMAGEN");

            }  elseif ($datos["visibilidad"] == "privado" && empty($datos["perfiles"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SELECCIONAR_PERFILES");

            }elseif ($validarFormato) {
                $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_IMAGEN_EVENTO");
                
            }elseif ($area[0] != $configuracion["DIMENSIONES"]["EVENTOS"][0] || $area[1] != $configuracion["DIMENSIONES"]["EVENTOS"][1]) {
                        $respuesta["mensaje"] = $textos->id("ERROR_AREA_IMAGEN_EVENTO");
            }else {
          
                $idEvento = $evento->adicionar($datos);
                if ($idEvento) {  
                                       
                  /******* Armo el nuevo evento ya modificado y lo devuelvo via Ajax *******
                       $item = "";
                       $comentario  = new Comentario();
                       $elemento    = new Noticia($idNoticia);
                       $botonModificar = HTML::nuevoBotonModificarItem($elemento->id, $elemento->urlBase);
                       $botonEliminar  = HTML::nuevoBotonEliminarItem($elemento->id, $elemento->urlBase);
                       $item          .= HTML::contenedor($botonEliminar.$botonModificar, "botonesLista", "botonesLista");

                        $contenedorComentarios = $comentario->mostrarComentarios($noticia->idModulo, $elemento->id);
                        $contenedorMeGusta     = Recursos::mostrarContadorMeGusta($noticia->idModulo, $elemento->id);
                        $comentarios           = HTML::contenedor($contenedorComentarios.$contenedorMeGusta, "mostrarPosted");
                         //seleccionar el genero de una persona 
                        $persona =  new Persona($elemento->idAutor);
                        $item     .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("NOTICIAS", $elemento->id));
                        $item     .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].$persona->idGenero.".png").preg_replace("/\%1/", HTML::enlace($elemento->autor, HTML::urlInterna("USUARIOS", $elemento->usuarioAutor))."On ".HTML::frase(date("D, d M Y", $elemento->fechaPublicacion), "pequenia cursiva negrilla").$comentarios, $textos->id("PUBLICADO_POR")));
                        $item2     = HTML::enlace(HTML::parrafo($elemento->titulo, "negrilla"), $elemento->url);
                        $item2    .= HTML::parrafo($elemento->resumen, "pequenia cursiva");
                        $item     .= HTML::contenedor($item2, "fondoUltimos5GrisB"); //barra del contenedor gris
                        $contenidoNoticia = "<li class = 'botonesOcultos' style='border-top: 1px dotted #E0E0E0;'>".HTML::contenedor($item, "contenedorListaNoticias", "contenedorListaNoticias".$elemento->id)."</li>";
                  ***************************************************************************/
                    
                        $respuesta["error"]                = false;
                        $respuesta["accion"]               = "recargar";
//                        $respuesta["contenido"]            = $contenidoNoticia;
//                        $respuesta["idContenedor"]         = "#contenedorListaNoticias".$idNoticia;
//                        $respuesta["insertarAjax"]         = true;
//                        $respuesta["destino"]              = "#listaNoticias";

                } else {
                    $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                }
            

        }
    }

    Servidor::enviarJSON($respuesta);
}











/**
 * Funcion que se encarga de modificar un evento directamente desde la lista haciendo uso de 
 * la tecnologia Ajax
 * 
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @param type $id entero -> identificador del evento a modificar
 * @param type $datos array ->arreglo con los datos a modificar
 */

function modificarEventoDesdeLista($id, $datos = array()) {
    global $textos, $configuracion, $archivo_imagen;
    
    $evento    = new Evento($id);
    $destino   = "/ajax".$evento->urlBase."/editRegister";
    $respuesta = array();
    
    //Recursos::escribirTxt("hola: ".$evento->ciudadCentro);
    
    if (empty($datos)) {
        
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 40, 255, $evento->titulo, "", "", array("alt" => $textos->id("INGRESE_TITULO")));
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION_CORTA"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[resumen]", 60, 255, $evento->resumen, "", "", array("alt" => $textos->id("INGRESE_DESCRIPCION_CORTA")));
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, $evento->descripcion, "editor", "", array("alt" => $textos->id("INGRESE_DESCRIPCION")));
        $codigo .= HTML::parrafo($textos->id("IMAGEN"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("imagen", 50, 255);
        $codigo .= HTML::parrafo($textos->id("SELECCIONE_CATEGORIA"), "negrilla margenSuperior");
        $codigo .= Categoria::mostrarSelectCategorias($evento->idModulo, $evento->idCategoria);
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", $evento->activo).$textos->id("ACTIVO"), "margenSuperior");       
        $codigo .= Perfil::mostrarChecks($id, $evento->idModulo);//mostrar checkboxes para compartir perfiles
        $pestana1 = $codigo;
        $codigo  = HTML::parrafo($textos->id("CIUDAD_EVENTO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[ciudad]", 50, 255, $evento->ciudad.", ".$evento->estado.", ".$evento->pais, "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCities"), "alt" => $textos->id("SELECCIONE_CIUDAD")));
        $codigo .= HTML::parrafo($textos->id("CENTRO_EVENTO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[centro]", 50, 255, $evento->centro.", ".$evento->ciudadCentro, "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCenters"), "alt" => $textos->id("SELECCIONE_CENTRO")));
        $codigo .= HTML::parrafo($textos->id("LUGAR"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[lugar]", 50, 255, $evento->lugar, "", "", array("alt" => $textos->id("INGRESE_LUGAR")));
        $codigo .= HTML::parrafo($textos->id("FECHA_INICIO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[fecha_inicio]", 12, 12, $evento->fechaInicio, "fechaAntigua", "fechaInicio", array("alt" => $textos->id("SELECCIONE_FECHA_INICIO")));
        $codigo .= HTML::parrafo($textos->id("HORA_INICIO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[hora_inicio]", 50, 255, $evento->horaInicio, "selectorHora", "horaInicio", array("alt" => $textos->id("SELECCIONE_HORA_INICIO")));
        $codigo .= HTML::parrafo($textos->id("FECHA_FIN"), "negrilla margenSuperior", "textoFechaFin");
        $codigo .= HTML::campoTexto("datos[fecha_fin]", 12, 12, $evento->fechaFin, "fechaAntigua", "fechaFin", array("alt" => $textos->id("SELECCIONE_FECHA_FIN")));
        $codigo .= HTML::parrafo($textos->id("HORA_FIN"), "negrilla margenSuperior", "textoHoraFin");
        $codigo .= HTML::campoTexto("datos[hora_fin]", 50, 255, $evento->horaFin, "selectorHora ", "horaFin", array("alt" => $textos->id("SELECCIONE_HORA_FIN")));
        $codigo .= HTML::parrafo($textos->id("INFO_CONTACTO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[info_contacto]", 5, 60, $evento->infoContacto, "", "txtAreaLimitado300", array("alt" => $textos->id("INGRESE_INFO_CONTACTO")));
        $codigo .= HTML::parrafo($textos->id("MAXIMO_TEXTO_250"), "maximoTexto", "maximoTexto");

        $pestana2 = $codigo;

        $pestanas = array(
            HTML::frase($textos->id("DATOS_EVENTO"), "letraBlanca") => $pestana1,
            HTML::frase($textos->id("INFORMACION_EVENTO"), "letraBlanca") => $pestana2            
        );
        
        $codigo = HTML::pestanas2("", $pestanas);
        
        $opciones = array("onClick" => "validarFormaEvento();");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "directo", "botonOk", "botonFormaEvento", "", $opciones).HTML::frase("     ".$textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true, "formaEvento");

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_EVENTO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 750;
        $respuesta["alto"]    = 800;

    } else {
        
        $respuesta["error"]   = true;
        if(!empty($archivo_imagen["tmp_name"])){
            $validarFormato = Recursos::validarArchivo($archivo_imagen, array("jpg","png","gif", "jpeg"));
            $area    = getimagesize($archivo_imagen["tmp_name"]);
            
            if ($validarFormato) {
                $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_IMAGEN_EVENTO");
                
            } elseif ($area[0] != $configuracion["DIMENSIONES"]["EVENTOS"][0] || $area[1] != $configuracion["DIMENSIONES"]["EVENTOS"][1]) {
                        $respuesta["mensaje"] = $textos->id("ERROR_AREA_IMAGEN_EVENTO");
            }
            
         }
        
        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

            } elseif (empty($datos["resumen"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_RESUMEN");

            } elseif (empty($datos["descripcion"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

            } elseif (empty($datos["ciudad"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CIUDAD");

            } elseif (empty($datos["centro"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CENTRO");

            } elseif (empty($datos["lugar"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_LUGAR");

            } elseif (empty($datos["fecha_inicio"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_FECHA_INICIO");

            } elseif (empty($datos["hora_inicio"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_HORA_INICIO");

            } elseif (empty($datos["info_contacto"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_INFO_CONTACTO");

            } elseif ($datos["visibilidad"] == "privado" && empty($datos["perfiles"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SELECCIONAR_PERFILES");

            }

            if (!isset($respuesta["mensaje"])) {           
                
                if ($evento->modificar($datos)) {
                    
              /******* Armo la nueva noticia ya modificada y la devuelvo via Ajax *******/
//                   $item = "";
//                   $comentario  = new Comentario();
//                   $elemento    = new Noticia($id);
//                   $botonModificar = HTML::nuevoBotonModificarItem($elemento->id, $elemento->urlBase);
//                   $botonEliminar  = HTML::nuevoBotonEliminarItem($elemento->id, $elemento->urlBase);
//                   $item          .= HTML::contenedor($botonEliminar.$botonModificar, "botonesLista", "botonesLista");
//                    
//                    $contenedorComentarios = $comentario->mostrarComentarios($noticia->idModulo, $elemento->id);
//                    $contenedorMeGusta     = Recursos::mostrarContadorMeGusta($noticia->idModulo, $elemento->id);
//                    $comentarios           = HTML::contenedor($contenedorComentarios.$contenedorMeGusta, "mostrarPosted");
//                     //seleccionar el genero de una persona 
//                    $persona =  new Persona($elemento->idAutor);
//                    $item     .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("NOTICIAS", $elemento->id));
//                    $item     .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].$persona->idGenero.".png").preg_replace("/\%1/", HTML::enlace($elemento->autor, HTML::urlInterna("USUARIOS", $elemento->usuarioAutor))."On ".HTML::frase(date("D, d M Y", $elemento->fechaPublicacion), "pequenia cursiva negrilla").$comentarios, $textos->id("PUBLICADO_POR")));
//                    $item2     = HTML::enlace(HTML::parrafo($elemento->titulo, "negrilla"), $elemento->url);
//                    $item2    .= HTML::parrafo($elemento->resumen, "pequenia cursiva");
//                    $item     .= HTML::contenedor($item2, "fondoUltimos5GrisB"); //barra del contenedor gris
//                    $contenidoNoticia = $item;


                     $respuesta["error"]              = false;
                     $respuesta["accion"]             = "recargar";
//                     $respuesta["contenido"]          = $contenidoNoticia;
//                     $respuesta["idContenedor"]       = "#contenedorListaNoticias".$id;
//                     $respuesta["modificarAjaxLista"] = true;
                    

                } else {
                    $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                }
            }
        }
    

    Servidor::enviarJSON($respuesta);
}





/**
 * Funcion que se encarga de modificar un evento 
 * 
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @param type $id entero -> identificador del evento a modificar
 * @param type $datos array ->arreglo con los datos a modificar
 */

function modificarEvento($id, $datos = array()) {
    global $textos, $configuracion, $archivo_imagen;

    $evento    = new Evento($id);
    $destino   = "/ajax".$evento->urlBase."/edit";
    $respuesta = array();


    if (empty($datos)) {
        
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 40, 255, $evento->titulo, "", "", array("alt" => $textos->id("INGRESE_TITULO")));
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION_CORTA"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[resumen]", 60, 255, $evento->resumen, "", "", array("alt" => $textos->id("INGRESE_DESCRIPCION_CORTA")));
        $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[descripcion]", 10, 60, $evento->descripcion, "editor", "", array("alt" => $textos->id("INGRESE_DESCRIPCION")));
        $codigo .= HTML::parrafo($textos->id("IMAGEN"), "negrilla margenSuperior");
        $codigo .= HTML::campoArchivo("imagen", 50, 255);
        $codigo .= HTML::parrafo($textos->id("SELECCIONE_CATEGORIA"), "negrilla margenSuperior");
        $codigo .= Categoria::mostrarSelectCategorias($evento->idModulo, $evento->idCategoria);
        $codigo .= HTML::parrafo(HTML::campoChequeo("datos[activo]", $evento->activo).$textos->id("ACTIVO"), "margenSuperior");       
        $codigo .= Perfil::mostrarChecks($id, $evento->idModulo);//mostrar checkboxes para compartir perfiles
        $pestana1 = $codigo;
        $codigo  = HTML::parrafo($textos->id("CIUDAD_EVENTO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[ciudad]", 50, 255, $evento->ciudad.", ".$evento->estado.", ".$evento->pais, "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCities"), "alt" => $textos->id("SELECCIONE_CIUDAD")));
        $codigo .= HTML::parrafo($textos->id("CENTRO_EVENTO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[centro]", 50, 255, $evento->centro.", ".$evento->ciudadCentro, "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCenters"), "alt" => $textos->id("SELECCIONE_CENTRO")));
        $codigo .= HTML::parrafo($textos->id("LUGAR"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[lugar]", 50, 255, $evento->lugar, "", "", array("alt" => $textos->id("INGRESE_LUGAR")));
        $codigo .= HTML::parrafo($textos->id("FECHA_INICIO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[fecha_inicio]", 12, 12, $evento->fechaInicio, "fechaAntigua", "fechaInicio", array("alt" => $textos->id("SELECCIONE_FECHA_INICIO")));
        $codigo .= HTML::parrafo($textos->id("HORA_INICIO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[hora_inicio]", 50, 255, $evento->horaInicio, "selectorHora", "horaInicio", array("alt" => $textos->id("SELECCIONE_HORA_INICIO")));
        $codigo .= HTML::parrafo($textos->id("FECHA_FIN"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[fecha_fin]", 12, 12, $evento->fechaFin, "fechaAntigua", "fechaFin", array("alt" => $textos->id("SELECCIONE_FECHA_FIN")));
        $codigo .= HTML::parrafo($textos->id("HORA_FIN"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[hora_fin]", 50, 255, $evento->horaFin, "selectorHora", "horaFin", array("alt" => $textos->id("SELECCIONE_HORA_FIN")));
        $codigo .= HTML::parrafo($textos->id("INFO_CONTACTO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[info_contacto]", 5, 60, $evento->infoContacto, "", "txtAreaLimitado300", array("alt" => $textos->id("INGRESE_INFO_CONTACTO")));
        $codigo .= HTML::parrafo($textos->id("MAXIMO_TEXTO_250"), "maximoTexto", "maximoTexto");
        $pestana2 = $codigo;

        $pestanas = array(
            HTML::frase($textos->id("DATOS_EVENTO"), "letraBlanca") => $pestana1,
            HTML::frase($textos->id("INFORMACION_EVENTO"), "letraBlanca") => $pestana2            
        );
        
        $codigo = HTML::pestanas2("", $pestanas);
        
        $opciones = array("onClick" => "validarFormaEvento();");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "directo", "botonOk", "botonFormaEvento", "", $opciones).HTML::frase("     ".$textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true, "formaEvento");

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_EVENTO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 750;
        $respuesta["alto"]    = 800;

    } else {
        
        $respuesta["error"]   = true;
        if(!empty($archivo_imagen["tmp_name"])){
            $validarFormato = Recursos::validarArchivo($archivo_imagen, array("jpg","png","gif", "jpeg"));
            $area    = getimagesize($archivo_imagen["tmp_name"]);
            
            if ($validarFormato) {
                $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_IMAGEN_EVENTO");                
            }elseif ($area[0] != $configuracion["DIMENSIONES"]["EVENTOS"][0] || $area[1] != $configuracion["DIMENSIONES"]["EVENTOS"][1]) {
                $respuesta["mensaje"] = $textos->id("ERROR_AREA_IMAGEN_EVENTO");
            }           
            
        }
        
        if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

            } elseif (empty($datos["resumen"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_RESUMEN");

            } elseif (empty($datos["descripcion"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_DESCRIPCION");

            } elseif (empty($datos["ciudad"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CIUDAD");

            } elseif (empty($datos["centro"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CENTRO");

            } elseif (empty($datos["lugar"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_LUGAR");

            } elseif (empty($datos["fecha_inicio"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_FECHA_INICIO");

            } elseif (empty($datos["hora_inicio"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_HORA_INICIO");

            } elseif (empty($datos["info_contacto"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_INFO_CONTACTO");

            } elseif ($datos["visibilidad"] == "privado" && empty($datos["perfiles"])) {
                $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SELECCIONAR_PERFILES");

            }

            if (!isset($respuesta["mensaje"])) {           

                if ($evento->modificar($datos)) {
                    
                     $respuesta["error"]              = false;
                     $respuesta["accion"]             = "recargar";
//                     $respuesta["contenido"]          = $contenidoNoticia;
//                     $respuesta["idContenedor"]       = "#contenedorListaNoticias".$id;
//                     $respuesta["modificarAjaxLista"] = true;
                    

                } else {
                    $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                }
            }
        }
    

    Servidor::enviarJSON($respuesta);
}




/**
*
*
*Metodo Para eliminar una noticia desde dentro de la noticia
*
**/


function eliminarEvento($id, $confirmado) {
    global $textos;

    $evento    = new Evento($id);
    $destino   = "/ajax".$evento->urlBase."/deleteRegister";
    $respuesta = array();

    if (!$confirmado) {
        $titulo  = HTML::frase($evento->titulo, "negrilla");
        $titulo  = str_replace("%1", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_EVENTO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 280;
        $respuesta["alto"]    = 150;
    } else {

         if ($evento->eliminar()) {  
               $respuesta["error"]   = false;
               $respuesta["accion"]  = "recargar";
          }else{                                
                 
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
         }

    }

    Servidor::enviarJSON($respuesta);
}



/**
*
*
*Metodo Para eliminar una noticia desde la lista general
*
**/

function eliminarEventoDesdeLista($id, $confirmado) {
    global $textos;

    $evento    = new Evento($id);
    $destino   = "/ajax".$evento->urlBase."/deleteRegister";
    $respuesta = array();
    
        
    if (!$confirmado) {
        $titulo  = HTML::frase($evento->titulo, "negrilla");
        $titulo  = preg_replace("/\%1/", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_EVENTO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 280;
        $respuesta["alto"]    = 150;
    } else {

         if ($evento->eliminar()) {  
               $respuesta["error"]   = false;
               $respuesta["accion"]  = "insertar";
               $respuesta["idContenedor"] = "#contenedorListaEventos".$id;
               $respuesta["eliminarAjaxLista"] = true;
          }else{                                
                 
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
         }

    }

    Servidor::enviarJSON($respuesta);
}


   

/**
*
*Metodo que carga el formulario para buscar y filtrar Noticiass  por contenido
*
**/

function buscarEventos($datos) {
    global $textos, $sql, $configuracion, $sesion_usuarioSesion;

    $evento         =   new Evento($id);
    $destino        =   "/ajax" . $evento->urlBase . "/searchEvents";
    $respuesta      =   array();
    $listaEventos   =   array();

    if (empty($datos)) {


        $forma2  = "";//HTML::campoOculto("datos[criterio]", "titulo");      
        
        $forma2  = HTML::parrafo($textos->id("BUSQUEDA_POR").": ", "negrilla margenInferior");
        
        $porTitulo  = HTML::parrafo(HTML::campoChequeo("datos[visibilidad]", "", "", "proximos") . $textos->id("EVENTOS_PROXIMOS") . HTML::campoChequeo("datos[visibilidad]", "", "", "pasados") . $textos->id("EVENTOS_PASADOS"), "margenSuperior");
        $porTitulo .= HTML::parrafo($textos->id("NOMBRE_EVENTO"), "negrilla margenSuperior");
        $porTitulo .= HTML::parrafo(HTML::campoTexto("datos[nombre]", 30, 255) . HTML::boton("buscar", $textos->id("BUSCAR"), "", "", "botonBuscarEventos"), "margenSuperior");
        $porTitulo  = HTML::contenedor($porTitulo, "oculto contenedorBuscadorEvento", "contenedorBusquedaEventoNombre");
        
        
        $porCiudad  = HTML::parrafo(HTML::campoChequeo("datos[visibilidad]", "", "", "proximos") . $textos->id("EVENTOS_PROXIMOS") . HTML::campoChequeo("datos[visibilidad]", "", "", "pasados") . $textos->id("EVENTOS_PASADOS"), "margenSuperior");
        $porCiudad .= HTML::parrafo($textos->id("CIUDAD_EVENTO"), "negrilla margenSuperior");
        $porCiudad .= HTML::parrafo(HTML::campoTexto("datos[ciudad]", 50, 255, "", "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCities"), "alt" => $textos->id("SELECCIONE_CIUDAD"))) . HTML::boton("buscar", $textos->id("BUSCAR"), "", "", "botonBuscarEventos"), "margenSuperior");
        $porCiudad  = HTML::contenedor($porCiudad, "oculto contenedorBuscadorEvento", "contenedorBusquedaEventoCiudad");
        
        
        $porCentro  = HTML::parrafo(HTML::campoChequeo("datos[visibilidad]", "", "", "proximos") . $textos->id("EVENTOS_PROXIMOS") . HTML::campoChequeo("datos[visibilidad]", "", "", "pasados") . $textos->id("EVENTOS_PASADOS"), "margenSuperior");
        $porCentro .= HTML::parrafo($textos->id("CENTRO_EVENTO"), "negrilla margenSuperior");
        $porCentro .= HTML::parrafo(HTML::campoTexto("datos[centro]", 50, 255, "", "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCenters"), "alt" => $textos->id("SELECCIONE_CENTRO"))) . HTML::boton("buscar", $textos->id("BUSCAR"), "", "", "botonBuscarEventos"), "margenSuperior");
        $porCentro  = HTML::contenedor($porCentro, "oculto contenedorBuscadorEvento", "contenedorBusquedaEventoCentro");
        
        //$forma2 .= HTML::parrafo($textos->id("BUSQUEDA_AVANZADA"), "negrilla estiloEnlace margenSuperiorDoble", "busquedaAvanzada");
        
        
        $porFecha  = HTML::parrafo($textos->id("SELECCIONE_EVENTOS_ENTRE_FECHAS"), "negrilla margenSuperior");
        $porFecha .= HTML::campoTexto("datos[fecha_uno]", 12, 12, "", "fechaAntigua margenSuperior", "fechaBusqueda", array("alt" => $textos->id("SELECCIONE_FECHA_1"))) . HTML::campoTexto("datos[fecha_dos]", 12, 12, "", "fechaAntigua margenIzquierdaTriple margenSuperior", "fechaBusqueda1", array("alt" => $textos->id("SELECCIONE_FECHA_2")));
        $porFecha .= HTML::parrafo(HTML::boton("buscar", $textos->id("BUSCAR")), "margenSuperior");
        $porFecha  = HTML::contenedor($porFecha, "oculto contenedorBuscadorEvento", "contenedorBusquedaEventoFecha");
        
        
        $forma2 .= HTML::parrafo(HTML::radioBoton("datos[criterio]", "", "", "nombre", "", "buscarEventoNombre") . $textos->id("NOMBRE_EVENTO")); 
        $forma2 .= HTML::parrafo(HTML::radioBoton("datos[criterio]", "", "", "fechas", "", "buscarEventoFecha")  . $textos->id("RANGO_FECHA_EVENTO")); 
        $forma2 .= HTML::parrafo(HTML::radioBoton("datos[criterio]", "", "", "ciudad", "", "buscarEventoCiudad") . $textos->id("CIUDAD_EVENTO")); 
        $forma2 .= HTML::parrafo(HTML::radioBoton("datos[criterio]", "", "", "centro", "", "buscarEventoCentro") . $textos->id("CENTRO_EVENTO")); 
        $forma2 .= HTML::contenedor("", "sombraInferior");
        $forma2 .= $porTitulo;
        $forma2 .= $porFecha;
        $forma2 .= $porCiudad;
        $forma2 .= $porCentro;
        
        
        $codigo1 = HTML::forma($destino, $forma2);
        $codigo  = HTML::contenedor($codigo1, "bloqueBorde");
        $codigo .= HTML::contenedor("", "margenSuperior", "resultadosBuscarEventos");

        $respuesta["generar"]   = true;
        $respuesta["codigo"]    = $codigo;
        $respuesta["destino"]   = "#cuadroDialogo";
        $respuesta["titulo"]    = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("BUSCAR_EVENTOS"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]     = 530;
        $respuesta["alto"]      = 400;
    } else {

        /**
         *Validacion de entrada de datos del cliente 
         */
        $respuesta["error"]   = true;
        
        if (!empty($datos["criterio"]) && $datos["criterio"] == "nombre" && $datos["nombre"] == "") {
             $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE_EVENTO");
            
        } elseif (!empty($datos["criterio"]) && $datos["criterio"] == "fechas" && $datos["fecha_uno"] == "" && $datos["fecha_dos"] == "") {
             $respuesta["mensaje"] = $textos->id("ERROR_DEBE_SELECCIONAR_LAS_DOS_FECHAS");
            
        } elseif (!empty($datos["criterio"]) && $datos["criterio"] == "ciudad" && $datos["ciudad"] == "") {
             $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CIUDAD_EVENTO");
             
        } elseif (!empty($datos["criterio"]) && $datos["criterio"] == "ciudad" && $datos["ciudad"] != "") {
             $ciudad = $sql->existeItem("lista_ciudades", "cadena", $datos["ciudad"]);
             if(!$ciudad){
                 $respuesta["mensaje"] = $textos->id("ERROR_DEBE_SELECCIONAR_UNA_CIUDAD_DE_LA_LISTA");
             }
             
        } elseif (!empty($datos["criterio"]) && $datos["criterio"] == "centro" && $datos["centro"] == "") {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CENTRO_EVENTO");
            
        } elseif (!empty($datos["criterio"]) && $datos["criterio"] == "centro" && $datos["centro"] != "") {
             $centro = $sql->existeItem("lista_centros", "nombre", $datos["centro"]);
             if(!$centro){
                 $respuesta["mensaje"] = $textos->id("ERROR_DEBE_SELECCIONAR_UNA_CIUDAD_DE_LA_LISTA");
             }
        }
        
        
        if(!isset($respuesta["mensaje"])){
                       /**** Identificar el tipo de perfil del ususario     ********/
            if (isset($sesion_usuarioSesion)) {
                $idTipo = $sesion_usuarioSesion->idTipo;
            } else {
                $idTipo = 99;
            }
            /**** fin de identificar el tipo de perfil del ususario ****/

            $palabras = explode(" ", $datos["nombre"]);

            foreach ($palabras as $palabra) {
                $palabrasResaltadas[] = HTML::frase($palabra, "resaltado");
                $palabrasMarcadas[] = "%" . $palabra . "%";
            }
            


            
            /**
             *Si el usuario va a buscar por el nombre del evento 
             */
            if($datos["criterio"] == "nombre"){                
                $tiempo = ""; //tiempo del evento, si es un evento proximo o un evento pasado    
                if (isset($datos["visibilidad"])) {
                if($datos["visibilidad"] == "proximos"){
                    $tiempo = "AND e.fecha_fin >= NOW()";
                    
                    } else {
                    $tiempo = "AND e.fecha_fin <= NOW()";
                    
                    }
                }
                
                $condicion = " (e.titulo REGEXP '(" . implode("|", $palabras) . ")' OR e.resumen REGEXP '(" . implode("|", $palabras) . ")' OR e.descripcion REGEXP '(" . implode("|", $palabras) . ")' $tiempo  )";

            } elseif($datos["criterio"] == "ciudad"){//Si el usuario va a buscar por la ciudad del evento
                $tiempo = "";   
                if (isset($datos["visibilidad"])) {
                if($datos["visibilidad"] == "proximos"){
                    $tiempo = "AND e.fecha_fin >= NOW()";
                    
                    } else {
                    $tiempo = "AND e.fecha_fin <= NOW()";
                    
                    }
                } 
                
                $idCiudad = $sql->obtenerValor("lista_ciudades", "id", "cadena = '".$datos["ciudad"]."'");                
                $condicion = " (e.id_ciudad = $idCiudad $tiempo )";
                
            } elseif($datos["criterio"] == "centro"){//Si el usuario va a buscar por el centro del evento
                $tiempo = "";   
                if (isset($datos["visibilidad"])) {
                if($datos["visibilidad"] == "proximos"){
                    $tiempo = "AND e.fecha_fin >= NOW()";
                    
                    } else {
                    $tiempo = "AND e.fecha_fin <= NOW()";
                    
                    }
                } 
                
                $idCentro = $sql->obtenerValor("lista_centros", "id", "nombre = '".$datos["centro"]."'");                
                $condicion = " (e.id_centro = $idCentro $tiempo )";
                
            }elseif($datos["criterio"] == "fechas"){//Si el usuario va a buscar por la ciudad del evento
                //tiempo del evento, si es un evento proximo o un evento pasado    
                if (!empty($datos["fecha_uno"]) && !empty($datos["fecha_dos"])) {
                    $condicion = " e.fecha_inicio BETWEEN '".$datos["fecha_uno"]."' AND '".$datos["fecha_dos"]."'";
                } 
            }



//////////////Sacar los id's de los EVENTOS que tiene permiso de ver el usuario actual//////////////
            $cond = "";
            if ($idTipo != 0) {
                $cond .= "id_perfil = " . $idTipo . " OR id_perfil = 99";
            }

            //$sql->depurar = true;
            $permisosEventos = $sql->seleccionar(array("permisos_eventos"), array("id_item", "id_perfil"), $cond);
            $permisos = array();

            while ($permiso = $sql->filaEnObjeto($permisosEventos)) {
                $permisos[] = $permiso->id_item;
            }
///////////////////////////////////////////////////////////////////////////////////////////           

            $tablas = array(
                "e" => "eventos"
            );

            $columnas = array(
                "id"            => "e.id",
                "titulo"        => "e.titulo",
                "resumen"       => "e.resumen",
                "contenido"     => "e.descripcion",
                "fecha_fin"     => "e.fecha_fin",
                "fecha_inicio"  => "e.fecha_inicio",
                "id_usuario"    => "e.id_usuario"
            );

            $sql->depurar = true;
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                while ($fila = $sql->filaEnObjeto($consulta)) {
                    if (in_array($fila->id, $permisos) || $fila->id_usuario == $sesion_usuarioSesion->id) {

                        $titulo = str_ireplace($palabras, $palabrasMarcadas, $fila->titulo);

                        $autor  = $sql->obtenerValor("usuarios", "sobrenombre", "id = '" . $fila->id_usuario . "'");
                        $item3  = HTML::parrafo(str_replace("%1", $autor, $textos->id("CREADO_POR")), "negrilla");
                        $item3 .= HTML::parrafo(HTML::enlace(str_ireplace($palabrasMarcadas, $palabrasResaltadas, $titulo) . " " . " " . HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "goButton.png"), HTML::urlInterna("EVENTOS", $fila->id)), "negrilla");
                        $item3 .= HTML::parrafo(str_replace("%1", $fila->fecha_publicacion, $textos->id("PUBLICADO_EN")), "negrilla cursiva pequenia");

                        $item = HTML::contenedor($item3, "fondoBuscadorEventos"); //barra del contenedor gris
                        $listaEventos[] = $item;
                    }
                }
            }
            if (sizeof($listaEventos) == 0) {
                $listaEventos[] = HTML::frase($textos->id("SIN_REGISTROS"));
            }

            $listaEventos = HTML::lista($listaEventos, "listaVertical listaConIconos bordeSuperiorLista");

            
            $respuesta["error"]                = false;
            $respuesta["accion"]        = "insertar";
            $respuesta["contenido"]     = $listaEventos;
            $respuesta["destino"]       = "#resultadosBuscarEventos";
            $respuesta["limpiaDestino"] = true;
            
        }

        
    }

    Servidor::enviarJSON($respuesta);
}

?>