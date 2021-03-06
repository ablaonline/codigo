<?php
/**
 * Archivo encargado de gestionar los elementos que se presentan en la p�gina principal:
 * - Resumen de noticias: Las cuatro (4) �ltimas noticias
 * - Resumen de blogs: Las cinco (5) �ltimas entradas del blog
 * @package     FOLCS
 * @subpackage  Inicio
 * @author      Pablo Andr�s V�lez Vidal  .:PAVLOV..; <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 CCCA
 * @version     0.1
 * */
global $configuracion, $sesion_usuarioSesion;

/* Resumen de las �ltimas cuatro noticias para animaci�n principal */
$noticias = new Noticia();
$contador = 0;
/* codigo para mostrar el boton editar para el comunicado */
$comunicado = new Comunicado(1);
$editaComunicado = '';
$imagenes = '';
$bloqueIzquierdo = '';

if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
    $editaComunicado .= HTML::contenedor(HTML::botonModificarItem(1, $comunicado->urlBase), 'editarComunicado');
}

$banerComunicado = HTML::contenedor(HTML::frase($comunicado->titulo, 'tituloComunicado') . HTML::parrafo($comunicado->contenido, 'parrafoComunicado') . '<br>' . $editaComunicado, 'bannerComunicados');

/* * *** Identificar el tipo de perfil del ususario  *********** */
if (isset($sesion_usuarioSesion)) {

    $usuario = new Usuario($sesion_usuarioSesion->id);
    $idTipo = $usuario->idTipo;
} else {

    $idTipo = 99;
}

$arregloNoticias = array();

/* * *** fin de identificar el tipo de perfil del ususario  *** */
foreach ($noticias->listar(0, 5, '', '', $idTipo, $noticias->idModulo) as $noticia) {
    $arregloNoticia = array();
    //mostrar en el titulo el numero de visitas
    $visits = '';
    if($noticia->numeroVisitas > 0) {
       if($noticia->numeroVisitas == 1) {
	  $visits = ' ... ('.$noticia->numeroVisitas.' visit)';
	} else {
	  $visits = ' ... ('.$noticia->numeroVisitas.' visits)';
	}
    } 

    $arregloNoticia['imagen'] = HTML::enlace(HTML::imagen($noticia->imagenPrincipal, 'noticia_'.$contador, '', array('alt' => 'noticia_'.$contador)), $noticia->url);
    $arregloNoticia['titulo'] = $noticia->titulo;
    $arregloNoticia['resumen'] = $noticia->resumen;
    $arregloNoticia['ruta'] = $noticia->url;
    
    $arregloNoticias[] = $arregloNoticia;
}


$resumenNoticias = HTML::bloqueNoticias($arregloNoticias);



/* Resumen de las �ltimas cinco entradas del blog */
$listaBlogs = $textos->id('SIN_CONTENIDO');
$blogs = new Blog();
$lista = array();

if ($blogs->registros) {

    /*     * *** Identificar el tipo de perfil del ususario  *********** */
    if (isset($sesion_usuarioSesion)) {
        $idTipo = $sesion_usuarioSesion->idTipo;
    } else {
        $idTipo = 99;
    }
    /*     * *** fin de identificar el tipo de perfil del ususario  *** */

    foreach ($blogs->listar(0, 5, '', '', $idTipo, $blogs->idModulo) as $blog) {

        if ($blog->activo) {
            $comentario = new Comentario();

            $contenedorComentarios = $comentario->mostrarComentarios($blog->idModulo, $blog->id);
            $contenedorMeGusta = Recursos::mostrarContadorMeGusta($blog->idModulo, $blog->id);
            $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, 'mostrarPosted');

            //seleccionar el genero de una persona 
            $usuario = new Usuario();

            $item = HTML::enlace(HTML::imagen($blog->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $blog->url);
            $item .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $usuario->getGenero($blog->idAutor) . '.png') . str_replace('%1', HTML::enlace($blog->autor, HTML::urlInterna('USUARIOS', $blog->usuarioAutor)) . $comentarios, $textos->id('PUBLICADO_POR')));
            $item2 = HTML::enlace(HTML::parrafo($blog->titulo, 'negrilla'), $blog->url);
            $item2 .= HTML::parrafo(date('D, d M Y h:i:s A', $blog->fechaPublicacion), 'pequenia cursiva negrilla');
            $item .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris

            $lista[] = $item;
        }
    }

    $listaBlogs = HTML::lista($lista, 'listaVertical listaConIconos bordeInferiorLista');
    $listaBlogs .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('BLOGS'), 'flotanteCentro margenSuperior');
}

/* Resumen de las �ltimos cinco cursos Registrados */
$listaCursos = $textos->id('SIN_CONTENIDO');
$cursos = new Curso();
$lista = array();

if ($cursos->registros) {

    foreach ($cursos->listar(0, 5, '', '', $idTipo, $cursos->idModulo) as $curso) {

        if ($curso->activo) {
            $usuario = new Usuario();
            $item = HTML::enlace(HTML::imagen($curso->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $curso->url);
            $item .= HTML::parrafo(HTML::enlace($curso->nombre, $curso->url) . ' ' . HTML::frase(str_replace('%1', HTML::enlace($curso->autor, HTML::urlInterna('USUARIOS', $curso->usuarioAutor)), HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $usuario->getGenero($curso->idAutor) . '.png') . $textos->id('CREADO_POR2')), 'flotanteCentro'));
            $item2 = HTML::parrafo(date('D, d M Y h:i:s A', $curso->fechaPublicacion), 'pequenia cursiva negrilla margenInferior');
            $item2 .= HTML::parrafo($curso->descripcion, 'margenInferior');
            $item .= HTML::contenedor($item2, 'fondoUltimos5GrisL margenInferior'); //barra del contenedor gris
            $lista[] = $item;
        }
    }

    $listaCursos = HTML::lista($lista, 'listaVertical listaConIconos bordeInferiorLista');
    $listaCursos .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('CURSOS'), 'flotanteCentro');
}

/* Resumen de los �ltimos cinco juegos */
$listaJuegos = $textos->id('SIN_CONTENIDO');
$juegos = new Juego();
$lista = array();

if ($juegos->registros) {

    foreach ($juegos->listar(0, 5) as $juego) {
        $item = HTML::enlace(HTML::imagen($juego->imagen, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $juego->url);
        $item .= HTML::enlace(HTML::parrafo($juego->nombre, 'negrilla'), $juego->url);
        $item2 = HTML::parrafo(substr($juego->descripcion, 0, 150) . '...', 'margenInferior');
        $item .= HTML::contenedor($item2, 'fondoUltimos5Gris'); //barra del contenedor gris
        $lista[] = $item;
    }

    $listaJuegos = HTML::lista($lista, 'listaVertical listaConIconos bordeInferiorLista');
    $listaJuegos .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('JUEGOS'), 'flotanteCentro');
}

/* Resumen de los �ltimos cinco usuarios registrados */
$listaUsuarios = $textos->id('SIN_CONTENIDO');
$usuarios = new Usuario();
$lista = array();

if ($usuarios->registros) {

    foreach ($usuarios->listar(0, 5) as $usuario) {
        $item = HTML::enlace(HTML::imagen($usuario->persona->imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $usuario->url);
        $item .= HTML::enlace(HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $usuario->persona->idGenero . '.png') . $usuario->persona->nombreCompleto, 'negrilla'), $usuario->url);
        $item3 = HTML::parrafo(date('D, d M Y h:i:s A', $usuario->fechaRegistro), 'pequenia cursiva negrilla margenInferior');
        if (!empty($usuario->persona->ciudadResidencia)) {
            $item3 .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['iconosBanderas'] . '/' . strtolower($usuario->persona->codigoIsoPais) . '.png', 'miniaturaBanderas') . ' ' . $usuario->persona->ciudadResidencia . ', ' . $usuario->persona->paisResidencia);
            // $item3 .= HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['iconosBanderas'].'/'.strtolower($usuario->persona->codigoIsoPais).'.png', 'miniaturaBanderas');
        }

        $item .=HTML::contenedor($item3, 'fondoUltimos5Gris'); //barra del contenedor gris
        $lista[] = $item;
    }

    $listaUsuarios = HTML::lista($lista, 'listaVertical listaConIconos bordeInferiorLista');
    $listaUsuarios .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('USUARIOS'), 'flotanteCentro');
}

/**
 * Presentar los res�menes en el cuadro de pesta�as
 * */
$resumenes = array(
    HTML::frase($textos->id('ULTIMOS_BLOGS'), 'letraBlanca') => $listaBlogs,
    HTML::frase($textos->id('ULTIMOS_USUARIOS'), 'letraBlanca') => $listaUsuarios,
    HTML::frase($textos->id('ULTIMOS_CURSOS'), 'letraBlanca') => $listaCursos,
    HTML::frase($textos->id('ULTIMOS_JUEGOS'), 'letraBlanca') => $listaJuegos
);


$rutaImagenTv = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].'/televisor_abla2.png';
$imagenTv = HTML::imagen($rutaImagenTv, '', '');

$rutaImagenTab = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].'/tablero2.jpg';
$imagenTab = HTML::imagen($rutaImagenTab, '', '');



$rutaVideo = HTML::enlace($imagenTv, 'http://www.youtube.com/watch?v=6HJB3KRE60c', '', '', array('rel' => 'prettyPhoto[]'));
$rutaClassAct = HTML::enlace($imagenTab, 'http://www.ablaonline.org/courses', '', '', array());

$contenedorIzq = HTML::contenedor($rutaVideo, 'homeMedioIzq');
$contenedorDer = HTML::contenedor($rutaClassAct, 'homeMedioDer');

$bloqueIzquierdo .= HTML::contenedor($contenedorIzq.$contenedorDer, 'contenedorHomeMedio');
$bloqueIzquierdo .= HTML::contenedor('', 'sombraInferior');
$bloqueIzquierdo .= HTML::pestanas2('pestanasResumenes', $resumenes);


Plantilla::$etiquetas['BLOQUE_NOTICIA'] = $resumenNoticias;
Plantilla::$etiquetas['BLOQUE_IZQUIERDO'] = $bloqueIzquierdo;
?>
