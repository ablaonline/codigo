<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Perfiles
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/

$contenido    = "";
$perfil       = new Perfil();
$tituloBloque = $textos->id("MODULO_ACTUAL");
$listaItems   = array();

/**
 *
 * Formulario para adicionar un nuevo elemento
 *
 **/
if (isset($sesion_usuarioSesion)) {
    $contenido .= HTML::contenedor(HTML::botonAdicionarItem($perfil->urlBase, $textos->id("ADICIONAR_PERFIL")), "derecha margenInferior");
}

$fila = 0;

foreach ($perfil->listar(0, 0, array(0)) as $elemento) {
    $fila++;
    $item = "";

    if (isset($sesion_usuarioSesion)) {
        $botones = "";

        if ($fila > 1) {
            $botones .= HTML::botonSubirItem($elemento->id, $perfil->urlBase);
        }

        if ($fila < $perfil->registros) {
            $botones .= HTML::botonBajarItem($elemento->id, $perfil->urlBase);
        }
        $botones .= HTML::botonEliminarItem($elemento->id, $perfil->urlBase);
        $botones .= HTML::botonModificarItem($elemento->id, $perfil->urlBase);
        
        $item    .= HTML::contenedor($botones, "botonesLista", "botonesLista");
    }
    $item .= HTML::frase($elemento->nombre, "negrilla");
    $item  = HTML::contenedor($item, "contenedorListaPerfiles", "contenedorListaPerfiles".$elemento->id);

    $listaItems[] = $item;
}

$contenido .= HTML::lista($listaItems, "listaVertical bordeSuperiorLista", "botonesOcultos altura45px", "listaPerfiles");

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = HTML::bloque("bloquePerfiles", $tituloBloque, $contenido);

?>