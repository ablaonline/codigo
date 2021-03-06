<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Enlaces
 * @author      Pablo Andr�s V�lez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * 
 * Modificado el: 07-03-2012
 *
 * */
global $configuracion, $sesion_usuarioSesion, $sql, $textos;




//Recibi las dos variables que vienen por get 
$modulo = $url_funcionalidad; // el modulo que estamos trabajando
$registro = $url_categoria;     // el id registro en la BD
$modulo = $sql->obtenerValor("modulos", "nombre", "url = '" . $modulo . "'"); //obtengo el nombre del modulo para poder crear el objeto
$moduloActual = new Modulo($modulo);
Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: " . $textos->id("ENLACES");
$bloqueEnlaces = "";

//Script que realiza el nombre o titulo del registro que vemos actualmente
$campo = "nombre";
if ($moduloActual->id == 4) {
    $campo = "sobrenombre";
}

$nombreItem = $sql->obtenerValor($moduloActual->tabla, $campo, "id = '" . $registro . "'");
if (sizeof($nombreItem) > 30) {
    $nombreItem = substr($nombreItem, 0, 30) . "...";
}


$registros = $configuracion["GENERAL"]["registrosPorPagina"];

if (isset($forma_pagina)) {
    $pagina = $forma_pagina;
} else {
    $pagina = 1;
}
$registroInicial = ($pagina - 1) * $registros;
/////////////////////////////////////////////////////////////////////


$tituloBloque = $textos->id("ENLACES") . ": " . $url_funcionalidad . ": " . $nombreItem;

$enlaces        = new Enlace();
$listaEnlaces   = array();
$botonEliminar  = "";



$arregloEnlaces = $enlaces->listar($registroInicial, $registros, $modulo, $registro);
$cantidadEnlaces = sizeof($arregloEnlaces);

 /**
 * Calcular el total de registros Activos
 * */
 $totalRegistrosActivos = 0;
  foreach ($enlaces->listar(0, 0, $modulo, $registro) as $elemento) {
         $totalRegistrosActivos++;
  }


//Recursos::escribirTxt( "cantidad enlaces: ".$cantidadEnlaces);

if ($cantidadEnlaces) {

	$botonAgregar  = "";
    if (($modulo == "USUARIOS" && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $registro) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) || ($modulo == "CURSOS" && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $registro)) {
        $botonAgregar .= HTML::campoOculto("idModulo", $moduloActual->id);
        $botonAgregar .= HTML::campoOculto("idRegistro", $registro);
        $botonAgregar .= HTML::boton("enlaceNuevo", $textos->id("ADICIONAR_ENLACE"), "flotanteDerecha margenInferior");
        $botonAgregar  = HTML::forma(HTML::urlInterna("ENLACES", "", true, "addLink"), $botonAgregar);
    }

    
    foreach ($arregloEnlaces as $enlace) {
	    $botonEliminar = "";
        if (($modulo == "USUARIOS" && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $enlace->idAutor) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) || ($modulo == "CURSOS" && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $enlace->idAutor)) {
            $botonEliminar .= HTML::botonAjax("basura", "ELIMINAR", HTML::urlInterna("ENLACES", "", true, "deleteLink"), array("id" => $enlace->id));
            $botonEliminar  = HTML::contenedor(HTML::contenedor($botonEliminar, "contenedorBotonesLista", "contenedorBotonesLista"), "botonesLista flotanteDerecha", "botonesLista");
        }
        $contenidoEnlace   = $botonEliminar;
        $contenidoEnlace  .= HTML::enlace(HTML::imagen($enlace->icono, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $enlace->enlace);
        $contenidoEnlace  .= HTML::parrafo(HTML::enlace($enlace->titulo, $enlace->enlace));
        $contenidoEnlace2  = HTML::parrafo($enlace->descripcion);
        $contenidoEnlace2 .= HTML::parrafo(HTML::frase($textos->id("ENLACE") . ": ", "negrilla") . $enlace->enlace, "margenSuperior");
        $contenidoEnlace  .= HTML::contenedor($contenidoEnlace2, "contenedorGrisLargo");

        $listaEnlaces[] = HTML::contenedor($contenidoEnlace, "contenedorListaEnlaces", "contenedorEnlace" . $enlace->id);
    }//fin del foreach

    $paginacion = Recursos:: mostrarPaginador($totalRegistrosActivos, $registroInicial, $registros, $pagina);

    $listaEnlaces[] = $paginacion;
} else {
    $listaEnlaces[] = HTML::frase(HTML::parrafo($textos->id("SIN_ENLACES"), "sinRegistros", "sinRegistros"), "margenInferior");
}

$bloqueEnlaces .= $botonAgregar . HTML::lista($listaEnlaces, "listaVertical bordeSuperiorLista", "botonesOcultos", "listaEnlaces");

$contenido = HTML::bloque("listadoUsuarios", $tituloBloque, $bloqueEnlaces);

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;
?>
