<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Bulletin Board
 * @author      Pablo Andrés Vélez Vidal. <pavelez@genesyscorporation.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2013 Genesys Corporation
 * @version     0.1
 * 
 *
 * */

global $url_ruta, $sesion_usuarioSesion, $modulo, $forma_subcategoria, $forma_item, $configuracion;

$contenido = '';//contenido HTML que será devuelto una vez se ejecute el codigo php


    //cargar las subcateforias
    $objetoSubcategorias    = new SubCategoriaBB();

    $objetoCategorias       = new CategoriaBB();

    Plantilla::$etiquetas['TITULO_PAGINA'] .= ' :: ' . $textos->id('MODULO_ACTUAL');
    Plantilla::$etiquetas['DESCRIPCION'] = $objeto->titulo;    

    $tituloBloque   = $textos->id('MODULO_ACTUAL');

    $objeto         = new CategoriaBB();

    $excluidas      = array();

    /**
     * Datos para la paginacion
     * */
    $registros = $configuracion['GENERAL']['registrosPorPagina'];

    if (isset($forma_pagina)) {
        $pagina = $forma_pagina;

    } else {
        $pagina = 1;

    }

    $registroInicial = ($pagina - 1) * $registros;

    /**
     * Formulario para adicionar un nuevo elemento 
     * */
    $botonAdicionar = '';

    if ((isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicion($objeto->idModulo)) || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
        $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($objeto->urlBase, $textos->id('ADICIONAR_ITEM')), 'derecha margenInferior');

    }

    /**
     * Boton que carga la ventana modal para realizar la busqueda
     * */
    $buscador = HTML::contenedor(HTML::botonAjax('masGrueso', $textos->id('BUSCAR'), HTML::urlInterna('NOTICIAS', 0, true, 'searchNews')), 'flotanteDerecha');

    /**
     * Declaracion del arreglo lista de categorias... y carga de datos en él
     * */
    $listaTempCat       = array();
    $excluidas          = array("0");
    $arregloCategorias  = $objetoCategorias->listar($registroInicial, $registros, $excluidas, '', 'n.titulo', 'CATEGORIAS_BB');

    $contenidoListaCat = '<p class="listaSinCategorias">No hay categorias disponibles</p>';

    //add the base About us bulletin board info
    $ruta       = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstaticas"] . "/logo_abla.jpg";
    $clase      = "imagenAboutBB";
    $id         = "imagenAboutBB";
    $opciones   = array("alt" => $textos->id("IMAGEN_ABOUT_BB"));
    $imagen     = HTML::imagen($ruta, $clase, $id, $opciones);

    $titulo         = HTML::contenedor($textos->id("TITULO_ABOUT_BB"), 'titulo cursiva negrilla tituloAboutBB');
    $descripcion    = HTML::contenedor($textos->id("DESCRIPCION_ABOUT_BB"), 'normal descripcionAboutBB');
    $imagen         = HTML::contenedor($imagen, 'imagenAboutBB');
    $item           = $imagen . $titulo . $descripcion;
    $item           = HTML::contenedor($item, 'contenedorAboutBB', 'contenedorAboutBB');

    $contenidoAboutBB   = $item;



    $arregloIdCategorias = array();

    if ($objetoCategorias->registros) {
        
        $titulo  = HTML::parrafo($textos->id("VER_TODOS"), 'filter verTodos', '', array("data-filter" => "all"));

        $contenidoListaCat = $titulo.'<ul id="listaFiltros" class="og-grid">';
        //$item    = HTML::contenedor($titulo, 'contenedorListaCategorias', 'contenedorListaCategorias_0' , array("ayuda" => $textos->id("AYUDA_FILTRAR_TODOS")));

        //$contenidoListaCat .= '<li class="filter buttonCategory" 
                                //data-filter="all">'.$item.'</li>';         

        //loop over the categories and generate the filter menu
        foreach ($arregloCategorias as $key => $value) {
            $titulo   = HTML::parrafo($value->titulo, 'tituloCategoria');

            $opciones = array(
                            "data-action"       => "zoom-in", 
                            "data-titulo"       => $value->titulo,
                            "data-descripcion"  => $value->descripcion,
                            "data-imagen"       => $value->imagenPrincipal,
                            "ayuda"             => $textos->id("AYUDA_VER_MAS")
                            );

            $verMas     = HTML::contenedor($textos->id("VER_MAS"), "verMas", "verMas_".$key, $opciones);

            $item = $titulo . $verMas;

            $opcionesContenedor = array(
                "style" => "background-color: ".$value->color." !important;",                
                );
            $item = HTML::contenedor($item, 'contenedorListaCategorias', 'contenedorListaCategorias' . $value->id, $opcionesContenedor);

            $contenidoListaCat .= '<li class="filter buttonCategory" 
                                    data-filter="category_'.$value->id.'" ayuda ="'.$textos->id("AYUDA_FILTRAR_CATEGORIA").'">'.$item.'</li>';    

            //llenar el arreglo de id de categorias disponibles para filtrar las subcategorias
            $arregloIdCategorias[] = $value->id;
           
        }

        $contenidoListaCat .= '</ul>';

        $contenidoListaCat = HTML::contenedor($contenidoListaCat, 'containerListaCategorias');

    }

    /**
     * Declaracion del arreglo lista de subcategorias... y carga de datos en él
     * */
    $condicionSubCat        = " n.id_categoria IN ('".implode('\',\'',$arregloIdCategorias)."') AND n.activo = '1'";

    $listaTempSubcat       = array();

    $excluidas = array(0);

    $arregloSubcategorias  = $objetoSubcategorias->listar(0, 0, $excluidas, $condicionSubCat);

    $contenidoListaSubcat = '<p class="listaSinCategorias">No hay categorias disponibles</p>';

    $arregloIdSubcategorias = array();

    if ($objetoSubcategorias->registros) {
        $contenidoListaSubcat = '<ul id="listaSubcategorias" class="listaSubcategorias">';

        foreach ($arregloSubcategorias as $key => $value) {

            $titulo         = HTML::contenedor($value->titulo, 'tituloSubcategoria');

            $desc           = (strlen($value->resumen) > 350) ? substr($value->resumen, 0, 500).'...' : $value->resumen;

            $descripcion    = HTML::contenedor($desc, 'descripcionSubcategoria');

            $ruta       = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $value->imagen;
            $clase      = "imagenSubcategoria";
            $id         = "imagenSubcategoria";
            $opciones   = array("alt" => $textos->id("IMAGEN_SUBCATEGORIA"));
            $imagen     = HTML::imagen($ruta, $clase, $id, $opciones);

            $item = $imagen . $titulo . $descripcion ;

            $opcionesContenedor = array(
                "style" => "background-color: ".$value->color." !important;",
                "ayuda" => $textos->id("AYUDA_SUBCATEGORIAS")
                );

            $item = HTML::contenedor($item, 'contenedorListaSubcategorias subcategorias', 'contenedorListaSubcategorias' . $value->id, $opcionesContenedor);

            $contenidoListaSubcat .= '<li class="mix category_'.$value->idCategoria.' subcategoria_bb" data-id="'.$value->id.'">'.$item.'</li>';    
           
        }

        $contenidoListaSubcat .= '</ul>';

        $contenidoListaSubcat = HTML::contenedor($contenidoListaSubcat, 'containerListaSubcategorias');

    }   

    //$listaCategorias = HTML::lista($arregloCategorias, 'listaVertical listaConIconos bordeSuperiorLista filter', 'botonesOcultos', 'listaCategorias');

    $contenidoBulletinBoard    .= $contenidoAboutBB;
    $contenidoBulletinBoard    .= $contenidoListaCat;
    $contenidoBulletinBoard    .= $contenidoListaSubcat;

    $contenido  = HTML::bloque('listado_categorias_bb', $tituloBloque, $contenidoBulletinBoard, '', '');

    Plantilla::$etiquetas['BLOQUE_IZQUIERDO'] = $contenido;
