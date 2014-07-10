<?php

/**
 *
 * @package     FOLCS
 * @subpackage  eventos
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 COLOMBOAMERICANO CALI
 * @version     0.1
 *
 * */

global $url_ruta, $sesion_usuarioSesion, $url_funcionalidad, $modulo, $sesion_usuarioSesion;
$excepcion  = "";

if (isset($url_ruta)) {
    $contenido  = "";
    $evento     = new Evento($url_ruta);
    $excepcion  = $url_ruta;


    if (isset($evento->id)) {
        Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: " . $textos->id("MODULO_ACTUAL");
        Plantilla::$etiquetas["DESCRIPCION"] = $evento->titulo;

        $tituloBloque = $textos->id("MAS_EVENTOS");
        $excluidas = array($evento->id);
        $botones = "";
        $tituloPrincipal = HTML::enlace(strtoupper($textos->id("MODULO_ACTUAL")), "/".$modulo->url, "subrayado")." :: ". $evento->titulo;

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $evento->idAutor)) {

            $botones .= HTML::botonEliminarItem($evento->id, $evento->urlBase);
            $botones .= HTML::botonModificarItem($evento->id, $evento->urlBase);
            $botones = HTML::contenedor($botones, "botonesInternos", "botonesInternos");
        }

        $comentario = new Comentario();
        $comentarios = $comentario->contar("EVENTOS", $evento->id);

        if (isset($sesion_usuarioSesion)) {
            $meGusta = Recursos::cargarMegusta($evento->idModulo, $evento->id, $sesion_usuarioSesion->id);
        } else {
            $meGusta = Recursos::mostrarMegusta($evento->idModulo, $evento->id); //este no permite votar, solo muestra y indica que se debe iniciar sesion
        }

        if (!$comentarios) {
            $comentarios = " &nbsp;&nbsp; |  &nbsp;&nbsp;" . HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "posted.png", "imgCommPosted") . $textos->id("SIN_COMENTARIOS");
            $comentarios .= HTML::contenedor($meGusta, "meGusta", "meGusta");
        } elseif ($comentarios == 1) {
            $comentarios = " &nbsp;&nbsp; | &nbsp;&nbsp;" . HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "postedOn.png", "imgCommPosted") . $comentarios . " " . strtolower($textos->id("COMENTARIO"));
            $comentarios .= HTML::contenedor($meGusta, "meGusta", "meGusta");
        } else {
            $comentarios = " &nbsp;&nbsp; | &nbsp;&nbsp;" . HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "postedOn.png", "imgCommPosted") . $comentarios . " " . strtolower($textos->id("COMENTARIOS"));
            $comentarios .= HTML::contenedor($meGusta, "meGusta", "meGusta");
        }

        //seleccionar el genero de una persona 
        $usuario   =  new Usuario();

        $contenidoEvento = $botones;
        $contenidoEvento .= HTML::imagen($evento->imagenPrincipal, "imagenItem imagenEvento flotanteIzquierda  margenDerecha margenInferior", "imagenEvento");
        $contenidoEvento .= HTML::parrafo(HTML::frase($textos->id("FECHA_INICIO").": ", "negrilla").date("D, d M Y", strtotime($evento->fechaInicio)), "normal cursiva  margenInferior margenIzquierda");
        $contenidoEvento .= HTML::parrafo(HTML::frase($textos->id("HORA_INICIO").": ", "negrilla margenIzquierdaDoble").$evento->horaInicio, "normal cursiva  margenInferior margenIzquierdaDoble");
        $contenidoEvento .= HTML::parrafo(HTML::frase($textos->id("FECHA_FIN").": ", "negrilla").date("D, d M Y", strtotime($evento->fechaFin)), "normal cursiva  margenInferior margenSuperior margenIzquierda");
        $contenidoEvento .= HTML::parrafo(HTML::frase($textos->id("HORA_FIN").": ", "negrilla margenIzquierdaDoble").$evento->horaFin, "normal cursiva  margenInferior ");
        $contenidoEvento .= HTML::parrafo(HTML::frase($textos->id("CIUDAD").": ", "negrilla").$evento->ciudad.", ".$evento->pais.HTML::imagen($evento->iconoBandera, "margenIzquierda margenInferior"), "normal cursiva  margenInferior margenSuperior");
        $contenidoEvento .= HTML::parrafo(HTML::frase($textos->id("CENTRO").": ", "negrilla").$evento->centro.", ".$evento->ciudadCentro, "normal cursiva  margenInferior margenSuperior");
        $contenidoEvento .= HTML::parrafo(HTML::frase($textos->id("LUGAR").": ", "negrilla").$evento->lugar, "normal cursiva  margenInferiorTriple margenSuperior margenIzquierda");
        $contenidoEvento .= HTML::contenedor(HTML::parrafo($textos->id("DESCRIPCION").": ", "negrilla").$evento->descripcion, "justificado");
        $contenidoEvento .= HTML::contenedor(HTML::parrafo($textos->id("INFO_CONTACTO").": ", "negrilla").nl2br($evento->infoContacto), "infoContactoEvento margenSuperior margenDerechoCuadruple margenInferiorDoble");
        $contenidoEvento .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . $usuario->getGenero($elemento->idAutor). ".png") . preg_replace("/\%1/", HTML::enlace($evento->autor, HTML::urlInterna("USUARIOS", $evento->usuarioAutor)), $textos->id("PUBLICADO_POR")) . $comentarios, "margenInferior");
        $contenidoEvento .= HTML::contenedor(HTML::botonesCompartir(), "botonesCompartir");

        /************************************************** PROXIMOS EVENTOS ******************************************/
        $proximosEventos = $evento->proximosEventos();

        /****************************************************************************************************************************/

        $contenido = HTML::bloque("evento_" . $evento->id, $tituloPrincipal, $contenidoEvento, "", "botonesOcultos");
        $contenido .= $proximosEventos;
        $contenido .= Galeria::cargarGaleria($evento->idModulo, $evento->id);
        $contenido .= HTML::bloque("bloqueComentariosEvento", $textos->id("COMENTARIOS"), Recursos::bloqueComentarios("EVENTOS", $evento->id, $evento->idAutor));
    }
} else {
    $tituloBloque = $textos->id("MODULO_ACTUAL");
    $evento = new Evento();
    $excluidas = array();


/**
 * Datos para la paginacion
 * */

$registros = $configuracion["GENERAL"]["registrosPorPagina"];

if (isset($forma_pagina)) {
    $pagina = $forma_pagina;
} else {
    $pagina = 1;
}
$registroInicial = ($pagina - 1) * $registros;

/**
 * Capturar el tipo de usuario que tiene el usuario actual
 * */
if (isset($sesion_usuarioSesion)) {
    $idTipo = $sesion_usuarioSesion->idTipo;
} else {
    $idTipo = 99;
}

/**
 * Formulario para adicionar un nuevo elemento
 * */
if ((isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicion($evento->idModulo)) || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
    $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($evento->urlBase, $textos->id("ADICIONAR_EVENTO")), "derecha margenInferior");
    $img = HTML::imagen($configuracion['SERVIDOR']['media'].'/'.$configuracion['RUTAS']['imagenesEstilos'].'ayuda.png', 'margenSuperior');
    $videoCrearEvento = HTML::enlace($textos->id('VIDEO_CREAR_EVENTO').' '.$img, 'http://www.youtube.com/watch?v=SuUZRF5LS64', 'estiloBoton', '', array('rel' => 'prettyPhoto[]', 'ayuda' => $textos->id('VIDEO_CREAR_EVENTO')) );
    $botonAdicionar .= $videoCrearEvento;
				
} else {
    $botonAdicionar = "";
}

/**
 * Boton que carga la ventana modal para realizar la busqueda
 * */
$buscador = HTML::contenedor(HTML::botonAjax("masGrueso", $textos->id("BUSCAR"), HTML::urlInterna("EVENTOS", 0, true, "searchEvents")), "flotanteDerecha");

/**
 * Capturar la categoria por la cual se van a filtrar los items
 * */
$cat = "";
if (isset($url_funcionalidad) && $url_funcionalidad == "category") {
    $cat = $url_categoria;
}

    /**
     * Cargar el select que muestra las categorias pertenecientes a este modulo
     * */
    $urlModulo          = "events";
    $idModulo           = $evento->idModulo;
    $valPredeterminado  = $cat;
    $nombreModulo       = "EVENTOS";
    $filtroCategoria = Categoria::selectorCategorias($urlModulo, $idModulo, $valPredeterminado, $nombreModulo, $botonAdicionar . $buscador, "si");

    /**
     * Declaracion del arreglo lista... y carga de datos en él
     * */
    $listaEventos = array();
    $arregloEventos = $evento->listar($registroInicial, $registros, $excluidas, "", $idTipo, $evento->idModulo, $cat);

    if ($evento->registros) {

        $reg = sizeof($evento->listar(0, 0, $excluidas, "", $idTipo, $evento->idModulo, $cat));

        if ($reg > 0) {

            /**
             * Calcular el total de registros Activos
             * */
            $totalRegistrosActivos = 0;
            foreach ($evento->listar(0, 0, $excluidas, "", $idTipo, $evento->idModulo, $cat) as $elemento) {
                if ($elemento->activo) {
                    $totalRegistrosActivos++;
                }
            }

            foreach ($arregloEventos as $elemento) {
                if($elemento->id != $excepcion){
                        $item = "";
                        $celdas = array();

                        //if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0) || isset($sesion_usuarioSesion) && Perfil::verificarPermisosConsulta($evento->idModulo)) {
                            
			    $botones = "";
                            if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $elemento->idAutor)) {                                
                                $botones .= HTML::botonEliminarItemAjax($elemento->id, $evento->urlBase);
                                $botones .= HTML::botonModificarItemAjax($elemento->id, $evento->urlBase);
                            }

                            $item .= HTML::contenedor($botones, "botonesLista", "botonesLista");

                            $item .= HTML::parrafo($textos->id("NOMBRE_EVENTO").": ".HTML::enlace($elemento->titulo, $elemento->url), "negrilla");


                            if ($elemento->activo) {
                                $estado = HTML::parrafo($textos->id("ACTIVO"));
                            } else {
                                $estado = HTML::parrafo($textos->id("INACTIVO"));
                            }

                            $celdas[0][] = HTML::parrafo(str_replace("%1", HTML::parrafo($elemento->autor), $textos->id("PUBLICADO_POR") ), "negrilla") ;
                            $celdas[0][] = HTML::parrafo($textos->id("LUGAR"), "negrilla") . HTML::parrafo($elemento->lugar);
                            $celdas[0][] = HTML::parrafo($textos->id("CIUDAD"), "negrilla") . HTML::parrafo($elemento->ciudad.", ".$elemento->pais);
                            $celdas[0][] = HTML::parrafo($textos->id("CENTRO_EVENTO"), "negrilla") . HTML::parrafo($elemento->centro.", ".$elemento->ciudadCentro);
                            $celdas[1][] = HTML::parrafo($textos->id("FECHA_INICIO"), "negrilla") . HTML::parrafo(date("D, d M Y", $elemento->fechaInicio));
                            $celdas[1][] = HTML::parrafo($textos->id("HORA_INICIO"), "negrilla") . HTML::parrafo($elemento->horaInicio);
                            $celdas[1][] = HTML::parrafo($textos->id("FECHA_FIN"), "negrilla") . HTML::parrafo(date("D, d M Y", $elemento->fechaFin));
                            $celdas[1][] = HTML::parrafo($textos->id("HORA_FIN"), "negrilla") . HTML::parrafo($elemento->horaFin);
                            $item .= HTML::tabla(array(), $celdas, "tablaCompleta2");
                            $item = HTML::contenedor($item, "contenedorListaEventos", "contenedorListaEventos" . $elemento->id);
                            $listaEventos[] = $item;

                       // } 
                }
            } //fin del foreach
//////////////////paginacion ///////////////////
            $paginacion = Recursos:: mostrarPaginador($totalRegistrosActivos, $registroInicial, $registros, $pagina);
            $listaEventos[] = $paginacion;
        } else {
            $listaEventos = array($textos->id("SIN_REGISTROS"));
        }
    }//fin del if($eventos->registros)


    $listaEventos = HTML::lista($listaEventos, "listaVertical listaConIconos bordeSuperiorLista", "botonesOcultos", "listaEventos");
    $listaEventos = $filtroCategoria . $listaEventos;
    $contenido .= HTML::bloque("listadoEventos", $tituloBloque, $listaEventos);

}

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;
?>