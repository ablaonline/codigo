<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Menus
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 * */
$contenido = "";
$menu = new Menu();
$tituloBloque = $textos->id("MODULO_ACTUAL");
$listaItems = array();

/**
 *
 * Formulario para adicionar un nuevo elemento
 *
 * */
if (isset($sesion_usuarioSesion)) {
    $contenido .= HTML::contenedor(HTML::botonAdicionarItem($menu->urlBase, $textos->id("ADICIONAR_MENU")), "derecha margenInferior");
}

$fila = 0;

foreach ($menu->listar(0, 0, array(0)) as $elemento) {
    $fila++;
    $item = "";

    if (isset($sesion_usuarioSesion)) {
        $botones = "";

        if ($fila > 1) {
            $botones .= HTML::botonSubirItem($elemento->id, $menu->urlBase);
        }

        if ($fila < $menu->registros) {
            $botones .= HTML::botonBajarItem($elemento->id, $menu->urlBase);
        }

        $botones .= HTML::botonEliminarItem($elemento->id, $menu->urlBase);
        $botones .= HTML::botonModificarItem($elemento->id, $menu->urlBase);
        $item .= HTML::contenedor($botones, "botonesLista", "botonesLista");
    }

    $item .= HTML::parrafo($elemento->nombre . " (" . $elemento->paginas . ")", "negrilla");

    if ($elemento->destino) {
        $item .= HTML::parrafo($textos->id("DESTINO") . ": " . HTML::enlace($elemento->destino));
    }

    if ($elemento->activo) {
        $item .= HTML::parrafo($textos->id("ACTIVO"));
    } else {
        $item .= HTML::parrafo($textos->id("INACTIVO"));
    }

    $listaItems[] = $item;
}

$contenido .= HTML::lista($listaItems, "listaVertical bordeSuperiorLista", "botonesOcultos altura60px");

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = HTML::bloque("bloqueMenus", $tituloBloque, $contenido);
?>