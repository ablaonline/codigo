<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Audios
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * 
 * Modificado el: 16-02-2012
 *
 * */
global $configuracion, $sesion_usuarioSesion, $sql, $textos;

$excluidas = "";

//Recibi las dos variables que vienen por get 
$modulo = $url_funcionalidad; // el modulo que estamos trabajando
$registro = $url_categoria;     // el id registro en la BD
$modulo = $sql->obtenerValor("modulos", "nombre", "url = '" . $modulo . "'"); //obtengo el nombre del modulo para poder crear el objeto
$moduloActual = new Modulo($modulo);
Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: " . $textos->id("AUDIOS");

//Script que realiza el nombre o titulo del registro que vemos actualmente
$campo = "nombre";
if ($moduloActual->id == 4) {
    $campo = "sobrenombre";
}else{
    $usuarioAutor = $sql->obtenerValor($moduloActual->tabla, "id_usuario", "id = '" . $registro . "'");
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


$tituloBloque = $textos->id("AUDIOS") . ": " . $url_funcionalidad . ": " . $nombreItem;

$audios = new Audio();
$listaAudios = array();
$botonEliminar = "";

$cantidadAudios = $audios->contar($modulo, $registro);

if ($cantidadAudios) {

    $reproductor = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["reproductor"]."?file=";
    
    if (($modulo == "USUARIOS" && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $registro) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) ||  $modulo != "USUARIOS" && (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuarioAutor) ) {
        $botonAgregar = HTML::campoOculto("idModulo", $moduloActual->id);
        $botonAgregar .= HTML::campoOculto("idRegistro", $registro);
        $botonAgregar .= HTML::boton("conVolumen", $textos->id("ADICIONAR_AUDIO"), "flotanteDerecha margenInferior");
        $botonAgregar = HTML::forma(HTML::urlInterna("AUDIOS", "", true, "addAudio"), $botonAgregar);
    }


   foreach ($audios->listar($registroInicial, $registros, $modulo, $registro) as $audio) {

                if (($modulo == "USUARIOS" && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $registro) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) ||  $modulo != "USUARIOS" && (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuarioAutor) ) {
                    $botonEliminar = HTML::botonAjax("basura", "ELIMINAR", HTML::urlInterna("AUDIOS", "", true, "deleteAudio"), array("id" => $audio->id));
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, "contenedorBotonesLista", "contenedorBotonesLista"), "botonesLista flotanteDerecha", "botonesLista");
                }

                $contenidoAudio  = $botonEliminar;
                $contenidoAudio .= HTML::enlace("", $reproductor.$audio->enlace, "recursoAudio");
                $contenidoAudio .= HTML::parrafo($audio->titulo, "negrilla");
                $contenidoAudio .= HTML::parrafo($audio->descripcion);
                $contenidoAudio .= HTML::parrafo(HTML::frase($textos->id("ENLACE").": ", "negrilla").$audio->enlace);
                $listaAudios[]  .= HTML::contenedor($contenidoAudio, "contenedorListaAudios", "contenedorAudio".$audio->id);
    }

    $paginacion = Recursos:: mostrarPaginador($cantidadAudios, $registroInicial, $registros, $pagina, $totalPaginas);

    $listaAudios[] = $paginacion;
} else {
    $listaAudios[] = HTML::frase(HTML::parrafo($textos->id("SIN_ARCHIVOS"), "sinRegistros", "sinRegistros"), "margenInferior");
}


$bloqueAudios .= $botonAgregar . HTML::lista($listaAudios, "listaVertical bordeSuperiorLista", "botonesOcultos", "listaDocumentos");


$contenido = HTML::bloque("listadoUsuarios", $tituloBloque, $bloqueAudios);


Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;
?>
