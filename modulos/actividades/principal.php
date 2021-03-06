<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Noticias
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 * 
 * Modificada el : 06-03-12
 *
 * */

$contenido = "";//contenido HTML que ser� devuelto una vez se ejecute el codigo php
global $url_ruta, $sesion_usuarioSesion, $modulo;


if (isset($url_ruta)) {
    
    $noticia = new Noticia($url_ruta);

    
    if (isset($noticia->id)) {
        Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: " . $textos->id("MODULO_ACTUAL");
        Plantilla::$etiquetas["DESCRIPCION"] = $noticia->titulo;

        $tituloBloque = $textos->id("MAS_NOTICIAS");
        $excluidas = array($noticia->id);
        $botones = "";
        $tituloPrincipal = HTML::enlace(strtoupper($textos->id("MODULO_ACTUAL")), "/".$modulo->url, "subrayado")." :: ". $noticia->titulo;

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $noticia->idAutor)) {

            $botones .= HTML::botonEliminarItem($noticia->id, $noticia->urlBase);
            $botones .= HTML::botonModificarItem($noticia->id, $noticia->urlBase);
            $botones = HTML::contenedor($botones, "botonesInternos", "botonesInternos");
        }

        $comentario = new Comentario();
        $comentarios = $comentario->contar("NOTICIAS", $noticia->id);

        if (isset($sesion_usuarioSesion)) {
            $meGusta = Recursos::cargarMegusta($noticia->idModulo, $noticia->id, $sesion_usuarioSesion->id);
        } else {
            $meGusta = Recursos::mostrarMegusta($noticia->idModulo, $noticia->id); //este no permite votar, solo muestra y indica que se debe iniciar sesion
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

        //Mostrar el Genero del autor
        $usuario   = new Usuario();        

        $contenidoNoticia = $botones;
        $contenidoNoticia .= HTML::imagen($noticia->imagenPrincipal, "flotanteIzquierda  margenDerecha margenInferior imagenNoticia");
        $contenidoNoticia .= HTML::parrafo(date("D, d M Y h:i:s A", $noticia->fechaPublicacion), "pequenia cursiva negrilla derecha margenInferior");
        $contenidoNoticia .= HTML::contenedor($noticia->contenido, "justificado");
        $contenidoNoticia .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . $usuario->getGenero($noticia->idAutor) . ".png") . preg_replace("/\%1/", HTML::enlace($noticia->autor, HTML::urlInterna("USUARIOS", $noticia->usuarioAutor)), $textos->id("PUBLICADO_POR")) . $comentarios, "margenInferior");
        $contenidoNoticia .= HTML::contenedor(HTML::botonesCompartir(), "botonesCompartir");


        /*         * ************************************************    MAS NOTICIAS DE ESTE USUARIO    ***************************************** */
        $masNoticiasUsuario = $noticia->masNoticiasUsuario($noticia->idAutor, $url_ruta);

        /*         * ************************************************************************************************************************* */

        $contenido  = HTML::bloque("noticia_" . $noticia->id, $tituloPrincipal, $contenidoNoticia, "", "botonesOcultos");
        $contenido .= Galeria::cargarGaleria($noticia->idModulo, $noticia->id);
        $contenido .= $masNoticiasUsuario;
        $contenido .= HTML::bloque("bloqueComentariosNoticia", $textos->id("COMENTARIOS"), Recursos::bloqueComentarios("NOTICIAS", $noticia->id, $noticia->idAutor));
    }
} else {
    $tituloBloque = $textos->id("MODULO_ACTUAL");
    $noticia = new Noticia();
    $excluidas = array();
}



/**
 *
 * Datos para la paginacion
 *
 * */
$registros = $configuracion["GENERAL"]["registrosPorPagina"];

if (isset($forma_pagina)) {
    $pagina = $forma_pagina;
} else {
    $pagina = 1;
}
$registroInicial = ($pagina - 1) * $registros;
//    $totalPaginas = 0;




/**
 *
 * Capturar el tipo de usuario que tiene el usuario actual
 *
 * */
if (isset($sesion_usuarioSesion)) {
    $idTipo = $sesion_usuarioSesion->idTipo;
} else {
    $idTipo = 99;
}



/**
 *
 * Formulario para adicionar un nuevo elemento
 *
 * */
if ((isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicion($noticia->idModulo)) || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
    $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($noticia->urlBase, $textos->id("ADICIONAR_NOTICIA")), "derecha margenInferior");
} else {
    $botonAdicionar = "";
}


/**
 *
 * Boton que carga la ventana modal para realizar la busqueda
 *
 * */
$buscador = HTML::contenedor(HTML::botonAjax("masGrueso", $textos->id("BUSCAR"), HTML::urlInterna("NOTICIAS", 0, true, "searchNews")), "flotanteDerecha");


/**
 *
 * Capturar la categoria por la cual se van a filtrar los items
 *
 * */
$cat = "";
if (isset($url_funcionalidad) && $url_funcionalidad == "category") {
    $cat = $url_categoria;
}


/**
 *
 * Verifico si lo que me estan pidiendo es los blogs que me gustan
 * en ese caso llamo al metodo mostrarBlogs que me gustan
 *
 * */
if ($cat != "i_like") {


    /**
     *
     * Cargar el select que muestra las categorias pertenecientes a este modulo
     *
     * */
    $urlModulo = "news";
    $idModulo = $noticia->idModulo;
    $valPredeterminado = $cat;
    $nombreModulo = "NOTICIAS";
    $filtroCategoria = Categoria::selectorCategorias($urlModulo, $idModulo, $valPredeterminado, $nombreModulo, $botonAdicionar . $buscador, "si");


    /**
     *
     * Declaracion del arreglo lista... y carga de datos en �l
     *
     * */
    $listaNoticias = array();
    $arregloNoticias = $noticia->listar($registroInicial, $registros, $excluidas, "", $idTipo, $noticia->idModulo, $cat);


    

    if ($noticia->registros) {

        $reg = sizeof($noticia->listar(0, 0, $excluidas, "", $idTipo, $noticia->idModulo, $cat));

        if ($reg > 0) {

            /**
             * Calcular el total de registros Activos
             * */
            $totalRegistrosActivos = 0;
            foreach ($noticia->listar(0, 0, $excluidas, "", $idTipo, $noticia->idModulo, $cat) as $elemento) {
                if ($elemento->activo) {
                    $totalRegistrosActivos++;
                }
            }


            foreach ($arregloNoticias as $elemento) {

                $item = "";
                $celdas = array();

                if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0)) {
                    $botones = "";
                    $botones .= HTML::botonEliminarItemAjax($elemento->id, $noticia->urlBase);
                    $botones .= HTML::botonModificarItemAjax($elemento->id, $noticia->urlBase);

                    $item .= HTML::contenedor($botones, "botonesLista", "botonesLista");

                    $item .= HTML::parrafo($textos->id("TITULO"), "negrilla");
                    $item .= HTML::parrafo(HTML::enlace($elemento->titulo, $elemento->url), "negrilla");

                    if ($elemento->activo) {
                        $estado = HTML::parrafo($textos->id("ACTIVO"));
                    } else {
                        $estado = HTML::parrafo($textos->id("INACTIVO"));
                    }

                    $celdas[0][] = HTML::parrafo($textos->id("AUTOR"), "negrilla") . HTML::parrafo($elemento->autor);
                    $celdas[0][] = HTML::parrafo($textos->id("ESTADO"), "negrilla") . HTML::parrafo($estado);
                    $celdas[1][] = HTML::parrafo($textos->id("FECHA_CREACION"), "negrilla") . HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaCreacion));
                    $celdas[1][] = HTML::parrafo($textos->id("FECHA_PUBLICACION"), "negrilla") . HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaPublicacion));
                    $celdas[1][] = HTML::parrafo($textos->id("FECHA_ACTUALIZACION"), "negrilla") . HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaActualizacion));
                    $item .= HTML::tabla(array(), $celdas, "tablaCompleta2");
                    $item = HTML::contenedor($item, "contenedorListaNoticias", "contenedorListaNoticias" . $elemento->id);
                    $listaNoticias[] = $item;
                } else {

                    if ($elemento->activo) {

                        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->id == $elemento->idAutor)) {
                            $botones = "";
                            $botones .= HTML::botonEliminarItemAjax($elemento->id, $noticia->urlBase);
                            $botones .= HTML::botonModificarItemAjax($elemento->id, $noticia->urlBase);
                            $item .= HTML::contenedor($botones, "botonesLista", "botonesLista");
                        }

                        $comentario = new Comentario();

                        $contenedorComentarios = $comentario->mostrarComentarios($noticia->idModulo, $elemento->id);
                        $contenedorMeGusta = Recursos::mostrarContadorMeGusta($noticia->idModulo, $elemento->id);
                        $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, "mostrarPosted");
                        //seleccionar el genero de una persona 
                        $usuario = new Usuario();
                        $item .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("NOTICIAS", $elemento->id));
                        $item .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . $usuario->getGenero($elemento->idAutor) . ".png") . preg_replace("/\%1/", HTML::enlace($elemento->autor, HTML::urlInterna("USUARIOS", $elemento->usuarioAutor)) . "On " . HTML::frase(date("D, d M Y", $elemento->fechaPublicacion), "pequenia cursiva negrilla") . $comentarios, $textos->id("PUBLICADO_POR")));
                        $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, "negrilla"), $elemento->url);
                        $item2 .= HTML::parrafo($elemento->resumen, "pequenia cursiva");
                        $item .= HTML::contenedor($item2, "fondoUltimos5GrisB"); //barra del contenedor gris

                        $item = HTML::contenedor($item, "contenedorListaNoticias", "contenedorListaNoticias" . $elemento->id);

                        $listaNoticias[] = $item;
                    }
                }
            }//fin del foreach
//////////////////paginacion /////////////////////////////////////////////////////
            $paginacion = Recursos:: mostrarPaginador($totalRegistrosActivos, $registroInicial, $registros, $pagina);
            $listaNoticias[] = $paginacion;
        } else {
            $listaNoticias = array($textos->id("SIN_REGISTROS"));
        }
    }//fin del if($noticias->registros)

    $listaNoticias = HTML::lista($listaNoticias, "listaVertical listaConIconos bordeSuperiorLista", "botonesOcultos", "listaNoticias");
    $listaNoticias = $filtroCategoria . $listaNoticias;
    $contenido .= HTML::bloque("listadoNoticias", $tituloBloque, $listaNoticias);
} else {//lo que estan pidiendo es las noticias que me gustan


    /**
     *
     * Cargar el select que muestra las categorias pertenecientes a este modulo, a su vez, se le incluyen el boton adicionar y el boton buscador
     * para que devuelva un contenedor con los tres elementos dentro bien organizados
     *
     * */
    $urlModulo = "news";
    $idModulo = $noticia->idModulo;
    $valPredeterminado = $cat;
    $nombreModulo = "NOTICIAS";
    $filtroCategoria = Categoria::selectorCategorias($urlModulo, $idModulo, $valPredeterminado, $nombreModulo, $botonAdicionar . $buscador, "si");

    $contenido .= HTML::bloque("listadoNoticias", $tituloBloque, $filtroCategoria . $noticia->NoticiasDestacadas());
}



Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;
?>