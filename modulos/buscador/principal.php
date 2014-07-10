<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

$patron    = preg_replace("/(\s+)/", "|", trim($forma_campoBuscador));
$patron    = preg_replace("/(\w+)/", "[[:<:]]\\1[[:>:]]", $patron);
$condicion = "titulo REGEXP '$patron' OR resumen REGEXP '$patron' OR contenido REGEXP '$patron' OR extra REGEXP '$patron'";
$consulta  = $sql->seleccionar(array("buscador"), array("url", "titulo", "resumen"), $condicion);

if ($sql->filasDevueltas) {
    Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: ".$textos->id($modulo);
    Plantilla::$etiquetas["DESCRIPCION"]    = $patron;
    $filas = array();

    while ($fila = $sql->filaEnObjeto($consulta)) {
        $item     = HTML::enlace($fila->titulo, $fila->url);
        $item    .= HTML::parrafo(strip_tags($fila->resumen), "margenInferior");
        $filas[]  = $item;
    }

    $contenido = HTML::lista($filas, "listaVertical bordeInferiorLista");

} else {
    $contenido = $textos->id("BUSQUEDA_SIN_RESULTADOS");
}

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = HTML::bloque("", $textos->id("RESULTADOS_BUSQUEDA"), $contenido);


?>