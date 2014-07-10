<?php

/**
 * @package     FOLCS
 * @subpackage  Bulletin Board item_bb = Clase encargada de las interacciones CRUD con la BD
 *              así como de generar la estructura de la tabla del listado general
 * @author      Pablo A. Vélez <pavelez@genesyscorporation.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */

$contenido = '';//contenido HTML que será devuelto una vez se ejecute el codigo php

//capturar el valor de la subcategoria que fue enviado por POST
$idSubcategoria =  ($forma_filter) ? $forma_filter : '0';

global $url_ruta, $sesion_usuarioSesion, $modulo;

if (isset($url_ruta)) {
    
    $objeto = new BulletinBoardItem($url_ruta);
    
    if (isset($objeto->id)) {
        
        Plantilla::$etiquetas['TITULO_PAGINA'] .= ' :: ' . $textos->id('MODULO_ACTUAL');
        Plantilla::$etiquetas['DESCRIPCION']    = $objeto->titulo;

        $tituloBloque       = $textos->id('MAS_ITEMS_BB');
        $excluidas          = array($objeto->id);
        $botones            = '';
        $tituloPrincipal    = HTML::enlace(strtoupper($textos->id('BULLETIN_BOARD')), '/bulletin_board', 'subrayado').' :: '. $objeto->titulo;

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $objeto->idAutor)) {
            $botones .= HTML::botonEliminarItem($objeto->id, $objeto->urlBase);
            $botones .= HTML::botonModificarItem($objeto->id, $objeto->urlBase);
            $botones = HTML::contenedor($botones, 'botonesInternos', 'botonesInternos');
        }

        $comentario     = new Comentario();
        $comentarios    = $comentario->contar('ITEMS_BB', $objeto->id);

        if (isset($sesion_usuarioSesion)) {
            $meGusta = Recursos::cargarMegusta($objeto->idModulo, $objeto->id, $sesion_usuarioSesion->id);

        } else {
            $meGusta = Recursos::mostrarMegusta($objeto->idModulo, $objeto->id); //este no permite votar, solo muestra y indica que se debe iniciar sesion

        }

        $rutaImgEstilos = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'];

        $txtComentarios = (!$comentarios) ? 'SIN_COMENTARIOS' : ($comentarios == 1) ? 'COMENTARIO' : 'COMENTARIOS';
        $imgComentarios = (!$comentarios) ? 'posted.png' : 'postedOn.png';

        $comentarios  = ' &nbsp;&nbsp; | &nbsp;&nbsp;' . HTML::imagen($rutaImgComents . $imgComentarios, 'imgCommPosted') . $comentarios . ' ' . strtolower($textos->id($txtComentarios));
        
        //Mostrar el Genero del autor
        $usuario   = new Usuario(); 

        $publicadoPor = HTML::frase(HTML::imagen($rutaImgEstilos . $usuario->getGenero($objeto->idAutor) . '.png') . preg_replace('/\%1/', HTML::enlace($objeto->usuario->usuario, HTML::urlInterna('USUARIOS', $objeto->usuario->usuario)), $textos->id('PUBLICADO_POR')), 'margenInferior');
        $comentarios  = HTML::contenedor($publicadoPor . $comentarios, 'infoItem', 'infoItem');    

        $bloqueMeGusta = HTML::contenedor($meGusta, 'meGusta', 'meGusta');         

        $contenidoBulletinBoardItem = $botones;
        $contenidoBulletinBoardItem .= HTML::parrafo(date('D, d M Y h:i:s A', $objeto->fechaCreacion), 'pequenia cursiva negrilla derecha margenInferior');
        $contenidoBulletinBoardItem .= HTML::contenedor($objeto->descripcion, 'justificado ');
        $contenidoBulletinBoardItem .= HTML::contenedor(HTML::botonesCompartir(), 'botonesCompartir');

        $contenidoBulletinBoardItem  = HTML::contenedor($contenidoBulletinBoardItem, 'contenedorContenido');

        $contenidoBulletinBoardItem .= $comentarios;
        $contenidoBulletinBoardItem .= $bloqueMeGusta;

        $recursos = array(
             HTML::frase($textos->id('VIDEOS'), 'letraBlanca')       => Recursos::bloqueVideos('ITEMS_BB', $objeto->id, $objeto->idAutor),
             HTML::frase($textos->id('AUDIOS'), 'letraBlanca')       => Recursos::bloqueAudios('ITEMS_BB', $objeto->id, $objeto->idAutor),
             HTML::frase($textos->id('IMAGENES'), 'letraBlanca')     => Recursos::bloqueImagenes('ITEMS_BB', $objeto->id, $objeto->idAutor),
             HTML::frase($textos->id('GALERIAS'), 'letraBlanca')     => Recursos::bloqueGalerias('ITEMS_BB', $objeto->id, $objeto->idAutor),
             HTML::frase($textos->id('DOCUMENTOS'), 'letraBlanca')   => Recursos::bloqueArchivos('ITEMS_BB', $objeto->id, $objeto->idAutor),
             HTML::frase($textos->id('FOROS'), 'letraBlanca')        => Recursos::bloqueForos('ITEMS_BB', $objeto->id, $objeto->idAutor),
             HTML::frase($textos->id('ENLACES'), 'letraBlanca')      => Recursos::bloqueEnlaces('ITEMS_BB', $objeto->id, $objeto->idAutor),

        );

        $multimedia .= HTML::contenedor(HTML::pestanas2('recursosPaginas', $recursos), 'pestanasRecursosItems margenSuperiorDoble');
        $multimedia .= '<div class="" id="sombraFondoCursos"></div> ';        

        $contenidoBulletinBoardItem .= $multimedia;

        $contenido  = HTML::bloque('objeto_bb_' . $objeto->id, $tituloPrincipal, $contenidoBulletinBoardItem, '', 'botonesOcultos');

        $contenido .= HTML::bloque('bloqueComentariosBulletinBoardItem', $textos->id('COMENTARIOS'), Recursos::bloqueComentarios('ITEMS_BB', $objeto->id, $objeto->idAutor));

    }

} else {    
    $objeto                 = new BulletinBoardItem();

    $objetoSubcategorias    = new SubCategoriaBB($idSubcategoria);

    $objetoCategorias       = $objetoSubcategorias->categoria;     

    $tituloBloque = HTML::enlace(strtoupper($textos->id('BULLETIN_BOARD')), '/bulletin_board', 'subrayado')." :: ".$objetoSubcategorias->titulo;


    //add the About the module information
    $ruta       = $objetoSubcategorias->imagenPrincipal;
    $clase      = "imagenAbout";
    $id         = "imagenAbout";
    $opciones   = array("alt" => $textos->id("IMAGEN_ABOUT"));
    $imagen     = HTML::imagen($ruta, $clase, $id, $opciones);

    $titulo         = HTML::contenedor($objetoSubcategorias->titulo, 'titulo cursiva negrilla tituloAbout');
    $descripcion    = HTML::contenedor($objetoSubcategorias->descripcion, 'normal descripcionAbout');
    $imagen         = HTML::contenedor($imagen, 'imagenAboutBB');
    $codAbout       = $imagen . $titulo . $descripcion;
    $codAbout       = HTML::contenedor($codAbout, 'contenedorAbout', 'contenedorAbout');

    $contenidoAbout = $codAbout;

    
    $excluidas = array();

    /**
     * Datos para la paginacion
     * */
    $registros          = $configuracion['GENERAL']['registrosPorPagina'];
    $pagina             = (isset($forma_pagina)) ? $forma_pagina : 1;
    $registroInicial    = ($pagina - 1) * $registros;

    /**
     * Capturar el tipo de usuario que tiene el usuario actual
     * */
    $idTipo = (isset($sesion_usuarioSesion)) ? $sesion_usuarioSesion->idTipo : 99;

    /**
     * Formulario para adicionar un nuevo elemento
     * */
    $botonAdicionar = '';

    if (
        (isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicionItem('SUBCATEGORIAS_BB', $forma_filter)) 
            || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) && $idSubcategoria != "0" ) {
        $data = array("id_subcategoria" => $idSubcategoria);
        $botonAdicionar = HTML::contenedor(HTML::botonAjax("masGrueso", $textos->id('ADICIONAR_ITEM'), HTML::urlInterna('ITEMS_BB', 0, true, 'add'), $data), 'derecha margenInferior');

    } 

    /**
     * Boton que carga la ventana modal para realizar la busqueda
     * */
    $buscador = "";
    if ($idSubcategoria != "0") {
        $data = array("id_subcategoria" => $idSubcategoria);
        $buscador .= HTML::contenedor(HTML::botonAjax('buscar', $textos->id('BUSCAR'), HTML::urlInterna('ITEMS_BB', 0, true, 'search'), $data), 'flotanteDerecha');
    }

    /**
     *
     * Cargar el select que muestra las categorias pertenecientes a este modulo
     *
     * */
    //cargar el selector de categorias con la informacion correspondiente
    $arregloCategorias  = $objetoCategorias->listar(0, 0, array(0), '', 'n.titulo', 'CATEGORIAS_BB');
    $subcategoriasBB    = array();

    if ($objetoCategorias->registros) { 
        foreach ($arregloCategorias as $key => $value) {
            //llenar el arreglo de id de categorias disponibles para filtrar las subcategorias
            $arregloIdCategorias[] = $value->id;
        }

        $condicionSubCat        = " n.id_categoria IN ('".implode('\',\'',$arregloIdCategorias)."') AND n.activo = '1'";    

        $arregloSubcategorias  = $objetoSubcategorias->listar(0, 0, array(0), $condicionSubCat); 

        if ($objetoSubcategorias->registros) {
            foreach ($arregloSubcategorias as $key => $value) {
                $subcategoriasBB[$value->id] = $value->titulo;
            }
        }

        if ($idSubcategoria == "0") {
            $subcategoriasBB["0"] = $textos->id("SELECCIONAR");
        }

    }

    $filtroCategoria  = HTML::frase($textos->id("SELECCIONE_SUBCATEGORIA"), "negrilla cursiva");
    $filtroCategoria .= HTML::listaDesplegable("datos[id_subcategoria]", $subcategoriasBB, $idSubcategoria, "listaSeleccionaSubCategoria noChosen margenIzquierda", "listaSeleccionaSubCategoria");

    $cabeceraModulo  = $contenidoAbout;
    $cabeceraModulo .= $filtroCategoria;
    $cabeceraModulo .= $botonAdicionar; 
    $cabeceraModulo .= $buscador;

    //Si no se ha seleccionado ninguna subcategoria se ejecuta este bloque de codigo, y el return detiene la ejecucion del codigo de este archivo
    if ($idSubcategoria == "0") {
        $mensajeSinSubcategoria  = HTML::parrafo($textos->id("POR_FAVOR_SELECCIONE_SUBCATEGORIA"), "textoSelectorSubcategoria");
        $mensajeSinSubcategoria  = HTML::contenedor($mensajeSinSubcategoria, "contenedorSinSubcategoria");

        $listaBulletinBoardItems    = $cabeceraModulo . $mensajeSinSubcategoria;

        $contenido .= HTML::bloque('listadoBulletinBoardItems', $tituloBloque, $listaBulletinBoardItems); 

        Plantilla::$etiquetas['BLOQUE_IZQUIERDO'] = $contenido;

        return true;
    }    

    /**
     *
     * Declaracion del arreglo lista... y carga de datos en él
     *
     * */
    $condicion = " n.id_subcategoria = '".$idSubcategoria."'";

    $listaBulletinBoardItems    = array();
    $arregloBulletinBoardItems  = $objeto->listar($registroInicial, $registros, $excluidas, $condicion);

    if ($objeto->registros) {

        $consultarTodos = $objeto->listar(0, 0, $excluidas, $condicion);
        $reg = sizeof($consultarTodos);

        if ($reg > 0) {

            /**
             * Calcular el total de registros Activos
             * */
            $totalRegistrosActivos = 0;

            foreach ($consultarTodos as $elemento) {
                if ($elemento->activo && $elemento->id != 0) {
                    $totalRegistrosActivos++;
                }
            }


            foreach ($arregloBulletinBoardItems as $elemento) {

                $item = '';
                $celdas = array();

                    if ($elemento->activo || $sesion_usuarioSesion->id == 0) {

                        if ( (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $elemento->idAutor) ) {
                            $botones  = '';
                            $botones .= HTML::botonEliminarItemAjax($elemento->id, $objeto->urlBase);
                            $botones .= HTML::botonModificarItemAjax($elemento->id, $objeto->urlBase);
                            $item    .= HTML::contenedor($botones, 'botonesLista', 'botonesLista');
                        }

                        $comentario = new Comentario();

                        $contenedorComentarios = $comentario->mostrarComentarios($objeto->idModulo, $elemento->id);
                        $contenedorMeGusta = Recursos::mostrarContadorMeGusta($objeto->idModulo, $elemento->id);
                        $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, 'mostrarPosted');
                        //seleccionar el genero de una persona 
                        $usuario = new Usuario();
                        $item .= HTML::enlace(HTML::imagen($elemento->subcategoria->imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('ITEMS_BB', $elemento->id));
                        $item .= HTML::parrafo(HTML::imagen($rutaImgEstilos . $usuario->getGenero($elemento->idAutor) . '.png') . str_replace('%1', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->autor)) . ' On ' . HTML::frase(date('D, d M Y', $elemento->fechaCreacion), 'pequenia cursiva negrilla') . $comentarios, $textos->id('PUBLICADO_POR')));
                        $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                        $item2 .= HTML::parrafo($elemento->resumen, 'pequenia cursiva');
                        $item .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris

                        $item = HTML::contenedor($item, 'contenedorListaBulletinBoardItems', 'contenedorListaBulletinBoardItems' . $elemento->id);

                        $listaBulletinBoardItems[] = $item;
                    }

            }

            $datosPaginacion = array("filter" => $idSubcategoria);

            $paginacion = Recursos:: mostrarPaginador($totalRegistrosActivos, $registroInicial, $registros, $pagina, $datosPaginacion);
            $listaBulletinBoardItems[] = $paginacion;

        } else {
            $listaBulletinBoardItems = array($textos->id('SIN_REGISTROS'));

        }

    }

    $listaBulletinBoardItems    = HTML::lista($listaBulletinBoardItems, 'listaVertical listaConIconos bordeSuperiorLista', 'botonesOcultos', 'listaBulletinBoardItems');
    $listaBulletinBoardItems    = $cabeceraModulo . $listaBulletinBoardItems;

    $contenido .= HTML::bloque('listadoBulletinBoardItems', $tituloBloque, $listaBulletinBoardItems);

}

Plantilla::$etiquetas['BLOQUE_IZQUIERDO'] = $contenido;
