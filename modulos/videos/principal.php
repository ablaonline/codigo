<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Videos
 * @author      Pablo Andr�s V�lez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * 
 * Modificado el: 16-02-2012
 *
 * */
global $configuracion, $sesion_usuarioSesion, $sql, $textos;



//Recibi las dos variables que vienen por get 
$modulo = $url_funcionalidad; // el modulo que estamos trabajando
$registro = $url_categoria;     // el id registro en la BD
$modulo = $sql->obtenerValor("modulos", "nombre", "url = '" . $modulo . "'"); //obtengo el nombre del modulo para poder crear el objeto
$moduloActual = new Modulo($modulo);
Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: " . $textos->id("VIDEOS");

//Script que realiza el nombre o titulo del registro que vemos actualmente
$campo = "nombre";
if ($moduloActual->id == 4) {
    $campo = "sobrenombre";
} else {
    $usuarioAutor = $sql->obtenerValor($moduloActual->tabla, "id_usuario", "id = '" . $registro . "'");
}

$nombreItem = $sql->obtenerValor($moduloActual->tabla, $campo, "id = '" . $registro . "'");
if (sizeof($nombreItem) > 30) {
    $nombreItem = substr($nombreItem, 0, 30) . "...";
}


$registros = 15;//$configuracion["GENERAL"]["registrosPorPagina"];

if (isset($forma_pagina)) {
    $pagina = $forma_pagina;
} else {
    $pagina = 1;
}
$registroInicial = ($pagina - 1) * $registros;
/////////////////////////////////////////////////////////////////////


$tituloBloque = $textos->id("VIDEOS") . ": " . $url_funcionalidad . ": " . $nombreItem;

$videos        = new Video();
$listaVideos   = array();
$botonEliminar = "";
$bloqueVideos  = "";

$cantidadVideos = $videos->contar($modulo, $registro);

if ($cantidadVideos) {


    if (($modulo == "USUARIOS" && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $registro) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) || $modulo != "USUARIOS" && (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuarioAutor)) {
        $botonAgregar  = HTML::campoOculto("idModulo", $moduloActual->id);
        $botonAgregar .= HTML::campoOculto("idRegistro", $registro);
        $botonAgregar .= HTML::boton("video", $textos->id("ADICIONAR_VIDEO"), "flotanteDerecha margenInferior");
        $botonAgregar  = HTML::forma(HTML::urlInterna("INICIO", "", true, "addVideo"), $botonAgregar);
    }


    foreach ($videos->listar($registroInicial, $registros, $modulo, $registro) as $video) {

        if (($modulo == "USUARIOS" && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $registro) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) || $modulo != "USUARIOS" && (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuarioAutor)) {
            $botonEliminar = HTML::botonAjax("basura", "ELIMINAR", HTML::urlInterna("INICIO", "", true, "deleteVideo"), array("id" => $video->id));
            $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, "contenedorBotonesLista", "contenedorBotonesLista"), "botonesLista flotanteDerecha", "botonesLista");
        }

        $contenidoVideo = $botonEliminar;
        if ($video->enlace != "--") {
            if (preg_match("/youtube/i", $video->enlace)) {
                //Aqui entraria toda la validacion de si viene de youtube
                $codigo = explode("=", $video->enlace);
                $codigo = explode("&", $codigo[1]);

                if (!preg_match("/http/i", $video->enlace)) {
                    $video->enlace = "http://" . $video->enlace;
                }

                $imagen = HTML::imagen("http://img.youtube.com/vi/" . $codigo[0] . "/0.jpg", "miniaturaVideo");
                $contenidoVideo .= HTML::enlace($imagen, $video->enlace, "enlaceVideo", "", array("rel" => "prettyPhoto[]"));
            } else if (preg_match("/vimeo/i", $video->enlace)) {
                //Aqu� viene lo de vimeo

                if (preg_match("/http/i", $video->enlace)) {
                    $codigo = explode("/", $video->enlace);
                    $imgid = $codigo[3];
                } else {
                    $video->enlace = "http://" . $video->enlace;
                    $codigo = explode("/", $video->enlace);
                    $imgid = $codigo[3];
                }

                $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$imgid.php"));
                $img = $hash[0]['thumbnail_medium'];
                //Recursos::escribirTxt("el id: ".$imgid."-- la ruta: ".$img);
                $imagen = HTML::imagen($img, "miniaturaVideo");
                $contenidoVideo .= HTML::enlace($imagen, $video->enlace, "enlaceVideo", "", array("rel" => "prettyPhoto[]"));
            } else {
                //aqui vendria un error                        
            }
        } else {
            $reproductor = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["reproductor"] . "?file=";
            $contenidoVideo .= HTML::enlace("", $reproductor . $video->ruta, "recursoVideo");
        }
        $contenidoVideo .= HTML::enlace(HTML::parrafo($video->titulo, "negrilla"), $video->enlace, "enlaceVideo", "", array("rel" => "prettyPhoto[]"));
        $contenidoVideo .= HTML::parrafo($video->descripcion, "");

        //$contenidoVideo .= HTML::parrafo(HTML::frase($textos->id("ENLACE").": ", "negrilla").$video->ruta, "centrado margenSuperior margenInferior");
        $contenidoVideo = HTML::contenedor($contenidoVideo, "listaVideos", "contenedorVideo" . $video->id);
        $listaVideos[] .= $contenidoVideo;
    }//fin del foreach

    $paginacion = Recursos:: mostrarPaginador($cantidadVideos, $registroInicial, $registros, $pagina);

    $listaVideos[] = $paginacion;
} else {
    $listaVideos[] = HTML::frase(HTML::parrafo($textos->id("SIN_ARCHIVOS"), "sinRegistros", "sinRegistros"), "margenInferior");
}


$bloqueVideos .= $botonAgregar . HTML::lista($listaVideos, "listaVertical bordeSuperiorLista", "botonesOcultos", "listaDocumentos");


$contenido = HTML::bloque("listadoUsuarios", $tituloBloque, $bloqueVideos);


Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;
?>
