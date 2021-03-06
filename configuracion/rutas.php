<?php

/**
 *
 * @package     FOLCS
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

/**
 * Rutas de archivos y directorios principales
 */
$configuracion["RUTAS"] = array(
    "base"               => "/home/ablito/ablaonline",
    "media"              => "../media", //en la juega pana ��Importante, arriba el media si esta afuera //
    "clases"             => "clases",
    "clasesEnDesarrollo" => "clases/en_desarrollo/",
    "clasesBasicas"      => "clases/Comprimidos/ClasesBasicas.php",
    "clasesModulos"      => "clases/Comprimidos/ClasesModulos.php",
    "idiomas"            => "idiomas",
    "modulos"            => "modulos",
    "plantillas"         => "plantillas",
    "fuentes"            => "recursos/fuentes/fuentes.php",
    "pdfs"               => "archivos/pdfs",  
    "imagenesEstaticas"  => "imagen/estaticas",
    "iconosBanderas"     => "imagen/estaticas/banderas",
    "imagenesDinamicas"  => "imagen/dinamicas/normales",
    "imagenesMiniaturas" => "imagen/dinamicas/miniaturas",
    "imagenesJuegos"     => "imagen/dinamicas/miniaturas/juegos",
    "archivos"           => "archivos",
    "archivosActividades" => "archivosActividades",
    "audios"             => "audios",
    "iconoUsuario"       => "00000001.png",
    "video"              => "video",
    "audio"              => "audio",
    "documentos"         => "documentos",
    "estilos"            => "estilos",
    "imagenesEstilos"    => "estilos/imagenes/",
    "javascript"         => "javascript1",
    "reproductor"        => "reproductor/reproductor.swf",
    "archivoGeneral"     => "general",
    "masculino"          => "masculino.png",
    "femenino"           => "femenino.png",
    "banner"             => "00000001.gif",
    "botonRSS"           => "boton-rss.png",
    "botonFacebook"      => "boton-facebook.png",
    "botonTwitter"       => "boton-twitter.png"
);

/**
 * Rutas de plantillas de c�digo HTML
 */
$configuracion["PLANTILLAS"] = array(
    "principal" => "principal.htm",
    "interna"   => "interna.htm"
);

/**
 * Rutas de hojas de estilos (CSS) est�ndar
 */
$configuracion["ESTILOS"]["GENERAL"] = array(
    "general_v2.2.css",
    "jquery.css",
    "sexyalertbox.css",
    "plugins/flexnav.css",
    "plugins/chosen.min.css",
    "plugins/colpick.css"
    
);

$configuracion["ESTILOS"]["INICIO"] = array(
    "prettyPhoto.css",
    "plugins/parallax.style.css",
    "liteaccordion.css",
);

$configuracion["ESTILOS"]["EVENTOS"] = array(
    "prettyPhoto.css",
    "plugins/galleria.classic.css",
    "plugins/fullcalendar.css",
    "plugins/fullcalendar.print.css",
    'modulos/eventos/eventos.css'
);



$configuracion["ESTILOS"]["USUARIOS"] = array(
    "prettyPhoto.css",
    "jplayer.blue.css",
    "plugins/galleria.classic.css"
);

$configuracion["ESTILOS"]["CATEGORIAS_BB"] = array(
    "prettyPhoto.css",
    'modulos/categorias_bb/categorias_bb.css'
);

$configuracion["ESTILOS"]["SUBCATEGORIAS_BB"] = array(
    "prettyPhoto.css",
    'modulos/subcategorias_bb/subcategorias_bb.css'
);

$configuracion["ESTILOS"]["ITEMS_BB"] = array(
    "prettyPhoto.css",
    'modulos/items_bb/items_bb.css'
);

$configuracion["ESTILOS"]["CURSOS"] = array(
    "prettyPhoto.css",
    "jplayer.blue.css",
    "plugins/galleria.classic.css",
    'modulos/cursos/cursos.css'
);

$configuracion["ESTILOS"]["VIDEOS"] = array(
    "prettyPhoto.css"
);

$configuracion["ESTILOS"]["IMAGENES"] = array(
    "prettyPhoto.css"
);

$configuracion["ESTILOS"]["BLOGS"] = array(
    "prettyPhoto.css",
    "modulos/blogs/blogs.css",
    "plugins/galleria.classic.css"
);

$configuracion["ESTILOS"]["NOTICIAS"] = array(
    "prettyPhoto.css",
    "modulos/noticias/noticias.css",
    "plugins/galleria.classic.css"
);

$configuracion["ESTILOS"]["CULTURAL"] = array(
    "liteaccordion.css",
    "prettyPhoto.css"
);

$configuracion["ESTILOS"]["CENTROS"] = array(
    "prettyPhoto.css",
    "jplayer.blue.css"
);

$configuracion["ESTILOS"]["PAGINAS"] = array(
    "prettyPhoto.css"
);

$configuracion["ESTILOS"]["BULLETIN_BOARD"] = array(  
    "modulos/bulletin_board/bulletin_board.css",
    "modulos/bulletin_board/component.css"
);


/**
 * Rutas de archivos de JavaScript
 */
$configuracion["JAVASCRIPT"]["GENERAL"] = array(
    "varios_web_v1.4.js",
    "editor/ckeditor.js",
    "editor/adapters/jquery.js",
    "plugins/sexyalertbox.mini.js",
    "plugins/jquery.flexnav.min.js", 
    "plugins/chosen.jquery.min.js", 
    "plugins/colpick.js", 
);



$configuracion["JAVASCRIPT"]["INICIO"] = array(
    "prettyPhoto/js/jquery.prettyPhoto.js",
    "plugins/parallax.js",
    "plugins/modernizr.custom.js",
    "jquery.easing.1.3.js",
    "liteaccordion.jquery.js",
    "modulos/inicio/inicio.js"
);

$configuracion["JAVASCRIPT"]["EVENTOS"] = array(
    "ui.timepickr.js",
    "galeria/galleria.min.js",
    "plugins/fullcalendar.min.js",
    "prettyPhoto/js/jquery.prettyPhoto.js",
    "modulos/eventos/eventos.js"
);

$configuracion["JAVASCRIPT"]["USUARIOS"] = array(    
    "prettyPhoto/js/jquery.prettyPhoto.js",
    "jplayer/jplayer.playlist.js",
    "jplayer/jplayer.js",
    "galeria/galleria.min.js",
    "galeria/galleria.classic.min.js",
    "modulos/usuarios/usuarios.js"
);

$configuracion["JAVASCRIPT"]["CATEGORIAS_BB"] = array(    
    "prettyPhoto/js/jquery.prettyPhoto.js"
);

$configuracion["JAVASCRIPT"]["SUBCATEGORIAS_BB"] = array(    
    "prettyPhoto/js/jquery.prettyPhoto.js"
);

$configuracion["JAVASCRIPT"]["ITEMS_BB"] = array(    
    "prettyPhoto/js/jquery.prettyPhoto.js",
    "modulos/items_bb/items_bb.js",
);

$configuracion["JAVASCRIPT"]["BULLETIN_BOARD"] = array(    
    "plugins/jquery.mixitup.min.js",
    "modulos/bulletin_board/modernizr.custom.js",
    "modulos/bulletin_board/grid.js",
    "modulos/bulletin_board/bulletin_board.js",
);

$configuracion["JAVASCRIPT"]["CURSOS"] = array(
    "prettyPhoto/js/jquery.prettyPhoto.js",
    "jplayer/jplayer.playlist.js",
    "jplayer/jplayer.js",
    "galeria/galleria.min.js",
    "galeria/galleria.classic.min.js",
    "modulos/cursos/cursos.js"  
    
);

$configuracion["JAVASCRIPT"]["VIDEOS"] = array(
    "prettyPhoto/js/jquery.prettyPhoto.js"    
);

$configuracion["JAVASCRIPT"]["IMAGENES"] = array(
    "prettyPhoto/js/jquery.prettyPhoto.js"    
);


$configuracion["JAVASCRIPT"]["BLOGS"] = array(
    "prettyPhoto/js/jquery.prettyPhoto.js",
    "galeria/galleria.min.js",
    "modulos/blogs/blogs.js"
);

$configuracion["JAVASCRIPT"]["NOTICIAS"] = array(
    "prettyPhoto/js/jquery.prettyPhoto.js",
    "galeria/galleria.min.js",
    "modulos/noticias/noticias.js"
);


$configuracion["JAVASCRIPT"]["CULTURAL"] = array(    
    "jquery.easing.1.3.js",
    "liteaccordion.jquery.js",
    "modulos/cultural/cultural.js"   
);

$configuracion["JAVASCRIPT"]["CENTROS"] = array(
   // "http://maps.google.com/maps/api/js?sensor=false", 
   // "http://www.google.com/jsapi",
    "prettyPhoto/js/jquery.prettyPhoto.js",
    "jplayer/jplayer.playlist.js",
    "jplayer/jplayer.js",
    "modulos/centros/centros.js"
);

$configuracion["JAVASCRIPT"]["PAGINAS"] = array(
    "prettyPhoto/js/jquery.prettyPhoto.js"    
);


?>
