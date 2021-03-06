<?php

/**
 * @package     FOLCS
 * @subpackage  Imagenes
 * @author      Pablo Andr�s V�lez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * */
global $sesion_usuarioSesion, $sql, $forma_pagina;

//Recibi las dos variables que vienen por get 
$modulo = $url_funcionalidad; // el modulo que estamos trabajando
$registro = $url_categoria;     // el id registro en la BD
$modulo = $sql->obtenerValor('modulos', 'nombre', 'url = "' . $modulo . '"'); //obtengo el nombre del modulo para poder crear el objeto
$moduloActual = new Modulo($modulo);

Plantilla::$etiquetas['TITULO_PAGINA'] .= ' :: ' . $textos->id('IMAGENES');

//Script que realiza el nombre o titulo del registro que vemos actualmente
$campo = 'nombre';
if ($moduloActual->id == 4) {
    $campo = 'sobrenombre';
} else {
    $usuarioAutor = $sql->obtenerValor($moduloActual->tabla, 'id_usuario', 'id = "' . $registro . '"');
}

$nombreItem = $sql->obtenerValor($moduloActual->tabla, $campo, 'id = "' . $registro . '"');
if (sizeof($nombreItem) > 30) {
    $nombreItem = substr($nombreItem, 0, 30) . '...';
}


$registros = 15; //$configuracion["GENERAL"]["registrosPorPagina"];

if (isset($forma_pagina)) {
    $pagina = $forma_pagina;
} else {
    $pagina = 1;
}
$registroInicial = ($pagina - 1) * $registros;
/////////////////////////////////////////////////////////////////////


$tituloBloque = $textos->id('IMAGENES') . ': ' . $url_funcionalidad . ': ' . $nombreItem;


if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) || ($modulo == 'USUARIOS' && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $registro) || $modulo != 'USUARIOS' && (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuarioAutor)) {

    $bloqueImagens = HTML::campoOculto('idModulo', $moduloActual->id);
    $bloqueImagens = HTML::campoOculto('modulo', $moduloActual->nombre);
    $bloqueImagens .= HTML::campoOculto('idRegistro', $registro);
    $bloqueImagens .= HTML::boton('imagen', $textos->id('ADICIONAR_IMAGEN'), 'flotanteDerecha margenInferior');
    $botonAgregar = HTML::forma(HTML::urlInterna('INICIO', '', true, 'addImage'), $bloqueImagens);
    $botonAgregar = HTML::contenedor($botonAgregar, 'flotanteDerecha');
}




$imagenes = new Imagen();
$listaImagens = array();
$botonEliminar = '';


$cantidadImagenes = $imagenes->contar($modulo, $registro);

if ($cantidadImagenes > 0) {

    $comentario = new Comentario();

    foreach ($imagenes->listar($registroInicial, $registros, $modulo, $registro) as $imagen) {

        $comentarios = $comentario->contar('IMAGENES', $imagen->id);
        if (!$comentarios) {
            $comentarios = ' 0';
        }

        if (($modulo = 'USUARIOS' && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $registro) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) || $modulo != 'USUARIOS' && (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuarioAutor)) {
            $botonEliminar = HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('INICIO', '', true, 'deleteImage'), array('id' => $imagen->id));
            $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista flotanteDerecha', 'botonesLista');

            $usuarioActual = new Usuario($registro);
            //$persona = new Persona($usuarioActual->idPersona);
        }

        $contenedorComentarios = HTML::contenedor($comentarios, 'contenedorBotonComentariosItems botonComentariosItemsGris', 'contenedorBotonComentariosItems', array('ruta' => '/ajax/users/comment', 'modulo' => 'IMAGENES', 'registro' => $imagen->id, 'propietarioItem' => $imagen->idAutor, 'ayuda' => $textos->id('HACER_COMENTARIO')));
        $contenedorComentarios = HTML::contenedor($contenedorComentarios, 'contenedorComentariosListaImagenes');

        if (($usuarioActual->persona->idImagen != $imagen->id) && ($imagen->id != 0)) {
            $contenidoImagen = $botonEliminar . $contenedorComentarios;
            $img = HTML::imagen($imagen->miniatura, 'listaImagenes recursoImagen', '', array('title' => $imagen->titulo));
            $contenidoImagen .= HTML::enlace($img, $imagen->ruta, '', '', array('rel' => 'prettyPhoto[' . $sesion_usuarioSesion->id . ' ]'));
            if ($imagen->titulo) {
                $contenidoImagen .= HTML::parrafo($imagen->titulo, 'negrilla');
            } else {
                $contenidoImagen .= HTML::parrafo('No title', 'negrilla');
            }
            if ($imagen->descripcion) {
                $contenidoImagen .= HTML::parrafo($imagen->descripcion);
            } else {
                $contenidoImagen .= HTML::parrafo('No description');
            }
            $contenidoImagen .= HTML::parrafo(HTML::frase($textos->id('ENLACE') . ': ', 'negrilla') . $imagen->ruta, 'margenSuperior');
            $contenidoImagen = HTML::contenedor($contenidoImagen, 'contenedorImagen', 'contenedorImagen' . $imagen->id);
            $listaImagens[] .= $contenidoImagen;
        }//fin de si el usuario va a ver su foto de perfil en el listado
    }

    $paginacion = Recursos:: mostrarPaginador($cantidadImagenes, $registroInicial, $registros, $pagina);

    $listaImagens[] = $paginacion;
} else {
    $listaImagens[] = HTML::frase($textos->id('SIN_IMAGENES'), 'margenInferior');
}


$contenido = HTML::lista($listaImagens, 'listaVertical listaConImagenes bordeSuperiorLista', 'botonesOcultos', 'listaImagenes');
$contenido = HTML::bloque('listadoUsuarios', $tituloBloque, $botonAgregar . $contenido);





Plantilla::$etiquetas['BLOQUE_IZQUIERDO'] = $contenido;
?>
