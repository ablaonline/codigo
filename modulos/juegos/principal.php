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
 **/
global $url_ruta, $sesion_usuarioSesion, $modulo;
if (isset($url_ruta)) {
    $contenido = "";
    $juego   = new Juego($url_ruta);

    if (isset($juego->id)) {
        Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: ".$textos->id("MODULO_ACTUAL");
        Plantilla::$etiquetas["DESCRIPCION"]    = $juego->titulo;

        $tituloBloque = $textos->id("MAS_JUEGOS");
        $excluidas    = array($juego->id);
        $botones      = "";
        $tituloPrincipal = HTML::enlace(strtoupper($textos->id("MODULO_ACTUAL")), "/".$modulo->url, "subrayado")." :: ". $juego->nombre;

        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
            $botones        = "";
            $botones       .= HTML::botonEliminarItem($juego->id, $juego->urlBase);
            $botones       .= HTML::botonModificarItem($juego->id, $juego->urlBase);            
            $botones        = HTML::contenedor($botones, "botonesInternos", "botonesInternos");
        }

        $contenidoJuego    = $botones;
        $contenidoJuego   .= HTML::contenedor($juego->script, "centrado");
        $contenidoJuego   .= HTML::contenedor(HTML::botonesCompartir(), "botonesCompartir");
        $contenido         = HTML::bloque("juego_".$juego->id, $tituloPrincipal, $contenidoJuego, "", "botonesOcultos");
        $contenido        .= HTML::bloque("bloqueComentariosJuego".$juego->id, $textos->id("COMENTARIOS"), Recursos::bloqueComentarios("JUEGOS", $juego->id, $juego->idAutor));
    }

} else {
    $tituloBloque = $textos->id("MODULO_ACTUAL");
    $juego        = new Juego();
    $excluidas    = "";
}

   /////////// DATOS DE PAGINACION ///////////////////////////////////////////   
    $listaItems   = array();
    $registros    = $configuracion["GENERAL"]["registrosPorPagina"];
    
    if (isset($forma_pagina)) {
    $pagina = $forma_pagina;

    } else {
    $pagina = 1;
   }

    $registroInicial = ($pagina - 1) * $registros;
    /////////////////////////////////////////////////////////////////////////////////////////////

    /**
     *
     * Formulario para adicionar un nuevo elemento
     *
     **/
    if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
        $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($juego->urlBase, $textos->id("ADICIONAR_JUEGO")), "derecha margenInferior");

    } else {
        $botonAdicionar = "";
    }


    /**
    *
    * Boton que carga la ventana modal para realizar la busqueda
    *
    **/
    $nuevosRegistros = HTML::contenedor("", "nuevosRegistros", "nuevosRegistros");//Contenedor donde se guardaran los nevos registros que se bayan insertando via ajax
    $buscador        =  HTML::contenedor(HTML::botonAjax("masGrueso", $textos->id("BUSCAR"), HTML::urlInterna("JUEGOS", 0, true, "searchGames")), "flotanteDerecha").$nuevosRegistros;



$listaJuegos = array();
$fila         = 0;

$reg = $juego->registros;
$totalRegistrosActivos = $juego->registrosActivos;

$arregloJuegos = $juego->listar($registroInicial, $registros, $excluidas);



if ($reg) {

    foreach ($arregloJuegos as $elemento) {
        $fila++;
        $item   = "";
        $celdas = array();

        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
            $botones        = "";
            $botones       .= HTML::botonEliminarItemAjax($elemento->id, $juego->urlBase);
            $botones       .= HTML::botonModificarItemAjax($elemento->id, $juego->urlBase);            
            $item          .= HTML::contenedor($botones, "botonesLista", "botonesLista");
            $item          .= HTML::enlace(HTML::imagen($elemento->imagen, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $elemento->url);
            $item          .= HTML::enlace(HTML::parrafo($elemento->nombre, "negrilla"), $elemento->url);
            $item          .= HTML::parrafo($elemento->descripcion, "margenInferior");
            $item           = HTML::contenedor($item, "tablaCompleta2");
            $item           = HTML::contenedor($item, "contenedorListaJuegos", "contenedorListaJuegos".$elemento->id);
            $listaJuegos[]  = $item;

        } else {
            $item      = HTML::enlace(HTML::imagen($elemento->imagen, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $elemento->url);
            $item     .= HTML::enlace(HTML::parrafo($elemento->nombre, "negrilla"), $elemento->url);            
            $item2     = HTML::parrafo(substr($elemento->descripcion, 0, 150)."...", "margenInferior");
            $item     .= HTML::contenedor($item2, "fondoUltimos5Gris");//barra del contenedor gris            

            $listaJuegos[]  = $item;
        }//fin del else

    }//fin del foreach

//////////////////paginacion /////////////////////////////////////////////////////
     $paginacion = Recursos:: mostrarPaginador($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas);
     $listaJuegos[] = $paginacion;

}//fin del IF que pregunta si hay registros de juegos

$listaJuegos  = HTML::lista($listaJuegos, "listaVertical listaConIconos bordeSuperiorLista", "botonesOcultos");
$listaJuegos  = $botonAdicionar.$buscador.$listaJuegos;
$contenido     .= HTML::bloque("listadoJuegos", $tituloBloque, $listaJuegos);

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;

?>
