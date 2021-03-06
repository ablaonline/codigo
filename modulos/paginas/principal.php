<?php

/**
 * @package     FOLCS
 * @subpackage  Paginas
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 * */
global $url_ruta, $modulo, $sesion_usuarioSesion;
$contenido = '';

if (isset($url_ruta)) {
    $pagina = new Pagina($url_ruta);

    if (isset($pagina->id)) {

        Plantilla::$etiquetas['TITULO_PAGINA'] .= ' :: ' . $pagina->titulo;
        Plantilla::$etiquetas['DESCRIPCION'] = $pagina->titulo;
        $botones = '';
        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) {
            $botones = HTML::botonEliminarItemAjax($pagina->id, $pagina->urlBase);
            $botones .= HTML::botonModificarItemAjax($pagina->id, $pagina->urlBase);
            $botones = HTML::contenedor($botones, 'botonesInternos', 'botonesInternos');
        }

        $contenidoPagina = HTML::contenedor($pagina->contenido, 'justificadoSinBorde');
        $contenidoPagina .= HTML::contenedor(HTML::botonesCompartir(), 'botonesCompartir');

		$multimedia = '';

		if ($pagina->multimedia) {

			$recursos = array(
				 HTML::frase($textos->id('VIDEOS'), 'letraBlanca')       => Recursos::bloqueVideos('PAGINAS', $pagina->id, $pagina->idAutor),
				 //HTML::frase($textos->id('AUDIOS'), 'letraBlanca')       => Recursos::bloqueAudios('PAGINAS', $pagina->id, $pagina->idAutor),
				 HTML::frase($textos->id('IMAGENES'), 'letraBlanca')     => Recursos::bloqueImagenes('PAGINAS', $pagina->id, $pagina->idAutor),
                                 HTML::frase($textos->id('GALERIAS'), 'letraBlanca')     => Recursos::bloqueGalerias('PAGINAS', $pagina->id, $pagina->idAutor),
				 HTML::frase($textos->id('DOCUMENTOS'), 'letraBlanca')   => Recursos::bloqueArchivos('PAGINAS', $pagina->id, $pagina->idAutor),
				 HTML::frase($textos->id('FOROS'), 'letraBlanca')        => Recursos::bloqueForos('PAGINAS', $pagina->id, $pagina->idAutor)
                                       
			);
                        
            $recursos[HTML::frase($textos->id('ENLACES'), 'letraBlanca')] = Recursos::bloqueEnlaces('PAGINAS', $pagina->id, $pagina->idAutor);
            $multimedia .= HTML::contenedor(HTML::pestanas2('recursosPaginas', $recursos), 'pestanasRecursosUsuarios margenSuperiorDoble');
			$multimedia .= '<div class="" id="sombraFondoCursos"></div> ';

		}
                

        $contenido .= HTML::bloque('pagina_' . $pagina->id, $pagina->titulo, $botones . '<br/><br/><br/>' .$multimedia. $contenidoPagina);





        $contenido .= HTML::bloque('bloqueComentariosNoticia', $textos->id('COMENTARIOS'), Recursos::bloqueComentarios('PAGINAS', $pagina->id, $pagina->idAutor));
    }
} else {

    $pagina = new Pagina();
    $tituloBloque = $textos->id($modulo->nombre);
    $listaItems = array();

    /**
     * Formulario para adicionar un nuevo elemento
     * */
    if (isset($sesion_usuarioSesion)) {
        $contenido .= HTML::contenedor(HTML::botonAdicionarItem($pagina->urlBase, $textos->id('ADICIONAR_PAGINA')), 'derecha margenInferior');
    }

    $fila = 0;

    foreach ($pagina->listar(0, 0, array(0)) as $elemento) {
        $fila++;
        $item = '';
        $celdas = array();

        if ((isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicion($pagina->idModulo)) || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
            $botones = '';

            if ($fila > 1) {
                $botones .= HTML::botonSubirItem($elemento->id, $pagina->urlBase);
            }

            if ($fila < $pagina->registros) {
                $botones .= HTML::botonBajarItem($elemento->id, $pagina->urlBase);
            }


            $botones .= HTML::botonEliminarItemAjax($elemento->id, $pagina->urlBase);
            $botones .= HTML::botonModificarItemAjax($elemento->id, $pagina->urlBase);
            $item .= HTML::contenedor($botones, 'botonesLista', 'botonesLista');
        }

        $item .= HTML::parrafo($textos->id('TITULO'), 'negrilla');
        $item .= HTML::parrafo(HTML::enlace($elemento->titulo, $elemento->url), 'negrilla');

        if ($elemento->activo) {
            $estado = HTML::parrafo($textos->id('ACTIVO'));
        } else {
            $estado = HTML::parrafo($textos->id('INACTIVO'));
        }

        $celdas[0][] = HTML::parrafo($textos->id('MENU'), 'negrilla') . HTML::parrafo($elemento->menu);
        $celdas[0][] = HTML::parrafo($textos->id('AUTOR'), 'negrilla') . HTML::parrafo($elemento->autor);
        $celdas[0][] = HTML::parrafo($textos->id('ESTADO'), 'negrilla') . HTML::parrafo($estado);
        $celdas[1][] = HTML::parrafo($textos->id('FECHA_CREACION'), 'negrilla') . HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaCreacion));
        $celdas[1][] = HTML::parrafo($textos->id('FECHA_PUBLICACION'), 'negrilla') . HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion));
        $celdas[1][] = HTML::parrafo($textos->id('FECHA_ACTUALIZACION'), 'negrilla') . HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaActualizacion));
        $item .= HTML::tabla(array(), $celdas, 'tablaCompleta2');
        $listaItems[] = $item;
    }

    $contenido .= HTML::lista($listaItems, 'listaVertical bordeSuperiorLista', 'botonesOcultos altura120px');
    $contenido = HTML::bloque('bloquePaginas', $tituloBloque, $contenido);
}

Plantilla::$etiquetas['BLOQUE_IZQUIERDO'] = $contenido;
?>
