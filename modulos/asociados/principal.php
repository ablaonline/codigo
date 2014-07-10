<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Asociado
 * @author      Pablo A. Vélez <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 *
 * */
if (isset($url_ruta)) {
    $contenido = "";
    $asociado = new Asociado($url_ruta);



    if (isset($asociado->id)) {
        Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: " . $textos->id("MODULO_ACTUAL");
        Plantilla::$etiquetas["DESCRIPCION"] = $asociado->titulo;

        $tituloBloque = $textos->id("MAS_ASOCIADOS");
        $excluidas = array($anuncio->id);
        $botones = "";

        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
            $botones .= HTML::botonModificarItem($asociado->id, $asociado->urlBase);
            $botones .= HTML::botonEliminarItem($asociado->id, $asociado->urlBase);
            $botones = HTML::contenedor($botones, "botonesLista", "botonesLista");
        }

        $contenidoAsociado = $botones;
        $contenidoAsociado .= HTML::imagen($asociado->imagenPrincipal, "flotanteIzquierda  margenDerecha margenInferior");
        $contenidoAsociado .= HTML::parrafo($textos->id("FECHA_CREACION") . ":   " . HTML::frase(date("D, d M Y h:i:s A", $asociado->fechaCreacion), "regular") . "<br><br>", "media cursiva negrilla izquierda margenInferior");
        $contenidoAsociado .= HTML::parrafo($textos->id("FECHA_INICIO_PUBLICACION") . ":   " . HTML::frase(date("D, d M Y h:i:s A", $asociado->fechaInicial), "regular") . "<br>", "media cursiva negrilla izquierda margenInferior");
        $contenidoAsociado .= HTML::parrafo($textos->id("FECHA_FINAL_PUBLICACION") . ":   " . HTML::frase(date("D, d M Y h:i:s A", $asociado->fechaFinal), "regular") . "<br><br>", "media cursiva negrilla izquierda margenInferior");
        $contenidoAsociado .= HTML::contenedor(HTML::frase($textos->id("DESCRIPCION"), "negrilla") . ": " . $asociado->descripcion, "justificado");

        $contenido = HTML::bloque("asociado_" . $asociado->id, $asociado->titulo, $contenidoAsociado, "", "botonesOcultos");
        //$contenido        .= HTML::bloque("bloqueComentariosNoticia", $textos->id("COMENTARIOS"), Recursos::bloqueComentarios("NOTICIAS", $anuncio->id, $anuncio->idAutor));
    }
} else {
    $tituloBloque = $textos->id("MODULO_ACTUAL");
    $asociado = new Asociado();
    $excluidas = array();
}

/**
 *
 * Formulario para adicionar un nuevo elemento
 *
 * */
if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
    $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($asociado->urlBase, $textos->id("ADICIONAR_ASOCIADO")), "derecha margenInferior");
} else {
    $botonAdicionar = "";
}

$listaAsociados = array();
$arregloAsociados = $asociado->listar(0, 0, "");


if ($asociado->registros > 0) {

    foreach ($arregloAsociados as $elemento) {
        $item = "";
        $celdas = array();

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $asociado->idAutor)) {
            $botones = "";
            $botones .= HTML::botonEliminarItem($elemento->id, $asociado->urlBase);
            $botones .= HTML::botonModificarItem($elemento->id, $asociado->urlBase);

            $item .= HTML::contenedor($botones, "botonesLista", "botonesLista");

            $item .= HTML::parrafo($textos->id("TITULO"), "negrilla");
            $item .= HTML::parrafo(HTML::enlace($elemento->titulo, $elemento->url), "negrilla");


            if ($elemento->activo) {
                $estado = HTML::parrafo($textos->id("ACTIVO"));
                $fechaActivacion = HTML::parrafo($textos->id("FECHA_PUBLICACION"), "negrilla") . HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaInicial));
            } else {
                $estado = HTML::parrafo($textos->id("INACTIVO"));
            }
            
            $imagen = $elemento->imagenPrincipal;

            $celdas[0][] = HTML::parrafo($textos->id("TITULO") , "negrilla") . HTML::parrafo($elemento->titulo);
            $celdas[0][] = HTML::parrafo($textos->id("ESTADO"), "negrilla") . HTML::parrafo($estado);
            $celdas[1][] = HTML::parrafo($textos->id("FECHA_CREACION"), "negrilla") . HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaCreacion));
            $celdas[1][] = HTML::parrafo(HTML::imagen($imagen, "miniaturaListaUltimos5"));
            $celdas[1][] = $fechaActivacion;

            $item .= HTML::tabla(array(), $celdas, "tablaCompleta2");
            $listaAsociados[] = $item;
        }
    }//fin del foreach
} else {
    $listaAsociados[] = $textos->id("NO_HAY_ASOCIADOS_REGISTRADOS");
}//fin del if($noticias->registros)

$listaAsociados = HTML::lista($listaAsociados, "listaVertical bordeSuperiorLista", "botonesOcultos");
$listaAsociados = $botonAdicionar . $listaAsociados;
$contenido .= HTML::bloque("listadoNoticias", $tituloBloque, $listaAsociados);

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;
?>