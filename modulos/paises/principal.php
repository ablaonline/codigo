<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Paises
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

$contenido    = "";
$pais         = new Pais();
$tituloBloque = $textos->id("MODULO_ACTUAL");
$listaItems   = array();
$registros    = 20;

if (isset($forma_pagina)) {
    $pagina = $forma_pagina;

} else {
    $pagina = 1;
}

$registroInicial = ($pagina - 1) * $registros;

/**
 *
 * Formulario para adicionar un nuevo elemento
 *
 **/
if (isset($sesion_usuarioSesion)) {
    $contenido .= HTML::contenedor(HTML::botonAdicionarItem($pais->urlBase, $textos->id("ADICIONAR_PAIS")), "derecha margenInferior");
}
//global $configuracion;

foreach ($pais->listar($registroInicial, $registros, array(0), "") as $elemento) {
    $item = "";

    if (isset($sesion_usuarioSesion)) {
        $botones  = HTML::botonModificarItem($elemento->id, $pais->urlBase);
        $botones .= HTML::botonEliminarItem($elemento->id, $pais->urlBase);
        $item    .= HTML::contenedor($botones, "oculto flotanteDerecha");
    }

    $item .=  HTML::contenedor($elemento->nombre);
    $item .=  HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["iconosBanderas"]."/".strtolower($elemento->codigo).".png", "miniaturaBanderas"));

    $listaItems[] = $item;
}

$reg = $pais->registros;

//////////////////paginacion /////////////////////////////////////////////////////

     $paginacion = Recursos:: mostrarPaginador($reg, $registroInicial, $registros, $pagina, $totalPaginas, $reg, $registros);

      $listaItems[] = $paginacion;


$contenido .= HTML::lista($listaItems, "listaVertical bordeSuperiorLista", "botonesOcultos altura45px");

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = HTML::bloque("bloquePaises", $tituloBloque, $contenido);

?>