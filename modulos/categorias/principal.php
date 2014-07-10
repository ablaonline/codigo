<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Categorias
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 * */
if (isset($url_ruta)) {
    $contenido = "";
    $categoria = new Categoria($url_ruta);

    if (isset($categoria->id)) {
        Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: " . $textos->id("MODULO_ACTUAL");
        Plantilla::$etiquetas["DESCRIPCION"] = $categoria->nombre;

        $tituloBloque = $textos->id("MAS_CATEGORIAS");
        $excluidas = array($categoria->id);
        $botones = "";

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $categoria->idAutor)) {
            $botones .= HTML::botonModificarItem($categoria->id, $categoria->urlBase);
            $botones .= HTML::botonEliminarItem($categoria->id, $categoria->urlBase);
            $botones = HTML::contenedor($botones, "oculto flotanteDerecha margenIzquierda");
        }

        //Mostrar el Genero del autor
        $persona = new Persona($categoria->idAutor);

        $contenidoCategoria = $botones;
        $contenidoCategoria .= HTML::parrafo(date("D, d M Y h:i:s A", $categoria->fechaCreacion), "pequenia cursiva negrilla derecha");
        $contenidoCategoria .= HTML::contenedor($categoria->descripcion, "contenido justificado");
        $contenidoCategoria .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . $persona->idGenero . ".png") . preg_replace("/\%1/", HTML::enlace($categoria->autor, HTML::urlInterna("USUARIOS", $categoria->usuarioAutor)), $textos->id("PUBLICADO_POR")), "margenInferior");
        $contenidoCategoria .= HTML::contenedor(HTML::botonesCompartir(), "botonesCompartir");

        //$contenidoBlog .= Recursos::cargarMegusta($blog->idModulo, $blog->id, $sesion_usuarioSesion->idTipo);

        $contenido = HTML::bloque("categoria_" . $categoria->id, $categoria->nombre, $contenidoCategoria, "", "botonesOcultos");
    }
} else {
    $tituloBloque = $textos->id("MODULO_ACTUAL");
    $categoria = new Categoria();
    $excluidas = "";
}

global $configuracion;

/* * ********************* Creo el arreglo que me traera todos los registros *********************** */
$listadoDeCategorias = array();


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
/////////////////////////////////////////////////////////////////////


$listadoDeCategorias = $categoria->listar($registroInicial, $registros, $excluidas, "", $idTipo, $categoria->idModulo);

/**
 *
 * Formulario para adicionar un nuevo elemento
 *
 * */
if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) {
    $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($categoria->urlBase, $textos->id("ADICIONAR_CATEGORIA")), "derecha margenInferior");
} else {
    $botonAdicionar = "";
}



$listaCategorias = array();
$fila = 0;

if ($categoria->registros) {
    /*     * *** Identificar el tipo de perfil del ususario  *********** */
    if (isset($sesion_usuarioSesion)) {
        $idTipo = $sesion_usuarioSesion->idTipo;
    } else {
        $idTipo = 99;
    }

    /*     * *** fin de identificar el tipo de perfil del ususario  *** */
    
    

$listadoDeCategorias2 = $categoria->listar(0, 0, $excluidas, "", $idTipo, $categoria->idModulo);
    /*     * ********************Calcular el total de registros activos************************** */
    if ($listadoDeCategorias2 != NULL) {
        $totalRegistrosActivos = 0;
        //Recursos::escribirTxt("tipo: ".$idTipo."___ modulo: ".$categoria->idModulo, 5);   
        foreach ($listadoDeCategorias2 as $elemento) {

            if ($elemento->activo) {
                $totalRegistrosActivos++;
            }
        }
    }

    /*     * *********************************************************************************** */





    $reg = sizeof($listadoDeCategorias2); //cantidad de registros

    if ($listadoDeCategorias != NULL) {

        foreach ($listadoDeCategorias as $elemento) {
            $fila++;
            $item = "";
            $celdas = array();

            if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $elemento->idAutor)) {
                $botones = "";
                $botones .= HTML::botonEliminarItem($elemento->id, $categoria->urlBase);
                $botones .= HTML::botonModificarItem($elemento->id, $categoria->urlBase);

                $item .= HTML::contenedor($botones, "botonesLista", "botonesLista");
                $item .= HTML::parrafo($textos->id("NOMBRE"), "negrilla");
                $item .= HTML::parrafo(HTML::enlace($elemento->nombre, $elemento->url), "negrilla");

                if ($elemento->activo) {
                    $estado = HTML::parrafo($textos->id("ACTIVO"));
                } else {
                    $estado = HTML::parrafo($textos->id("INACTIVO"));
                }

                $celdas[0][] = HTML::parrafo($textos->id("AUTOR"), "negrilla") . HTML::parrafo($elemento->autor);
                $celdas[0][] = HTML::parrafo($textos->id("ESTADO"), "negrilla") . HTML::parrafo($estado);
                $celdas[1][] = HTML::parrafo($textos->id("FECHA_CREACION"), "negrilla") . HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaCreacion));
                $item .= HTML::tabla(array(), $celdas, "tablaCompleta2");
                $listaCategorias[] = $item;
            }
        }//fin del foreach
//////////////////////////////////////paginacion /////////////////////////////////////////////////////


        $paginacion = Recursos:: mostrarPaginador($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas);

        Recursos::escribirTxt("total registros activos: " . $totalRegistrosActivos . " registro inicial: " . $registroInicial . " registros: " . $registros . " pagina: " . $pagina . " total paginas: " . $totalPaginas);

        $listaCategorias[] = $paginacion;
    }//fin del if($listadoDeCategorias != NULL)
    else {
        $listaCategorias = array($textos->id("NO_HAY_MAS_CATEGORIAS"));
    }
}//fin del if($registros) osea, si hay algun registro
else {
    $contenido .= HTML::bloque("listadoBlogs", $textos->id("MODULO_ACTUAL"), $textos->id("NO_HAY_CATEGORIAS"));
}
$listaCategorias = HTML::lista($listaCategorias, "listaVertical listaConIconos bordeSuperiorLista", "botonesOcultos");
$listaCategorias = $botonAdicionar . $listaCategorias;
$contenido .= HTML::bloque("listadoBlogs", $tituloBloque, $listaCategorias);

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;
?>