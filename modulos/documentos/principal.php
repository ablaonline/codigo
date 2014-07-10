<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Documentos
 * @author      Pablo Andr�s V�lez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * 
 * Modificado el: 13-01-2012
 *
 * */
global $configuracion, $sesion_usuarioSesion, $sql, $textos;

$excluidas = "";

//Recibi las dos variables que vienen por get 
$modulo = $url_funcionalidad; // el modulo que estamos trabajando
$registro = $url_categoria;     // el id registro en la BD
$modulo = $sql->obtenerValor("modulos", "nombre", "url = '" . $modulo . "'"); //obtengo el nombre del modulo para poder crear el objeto
$moduloActual = new Modulo($modulo);
Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: " . $textos->id("DOCUMENTOS");

//Script que realiza el nombre o titulo del registro que vemos actualmente
$campo = "nombre";
if ($moduloActual->id == 4) {
    $campo = "sobrenombre";
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


$tituloBloque = $textos->id("DOCUMENTOS") . ": " . $url_funcionalidad . ": " . $nombreItem;

$archivos = new Documento();
$listaArchivos = array();
$botonEliminar = "";

$cantidadDocumentos = $archivos->contar($modulo, $registro);

if ($cantidadDocumentos) {

    $comentario = new Comentario();

    if (($modulo == "USUARIOS" && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $registro) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0)) {
        $botonAgregar = HTML::campoOculto("idModulo", $moduloActual->id);
        $botonAgregar .= HTML::campoOculto("idRegistro", $registro);
        $botonAgregar .= HTML::boton("documentoNuevo", $textos->id("ADICIONAR_ARCHIVO"), "flotanteDerecha margenInferior");
        $botonAgregar = HTML::forma(HTML::urlInterna("DOCUMENTOS", "", true, "addDocument"), $botonAgregar);
    }


    foreach ($archivos->listar($registroInicial, $registros, $modulo, $registro) as $archivo) {

        $comentarios = $comentario->contar('DOCUMENTOS', $archivo->id);
        if (!$comentarios) {
            $comentarios = ' 0';
        }

        if (($modulo == "USUARIOS" && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $registro) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0)) {
            $botonEliminar = HTML::botonAjax("basura", "ELIMINAR", HTML::urlInterna("DOCUMENTOS", "", true, "deleteDocument"), array("id" => $archivo->id));
            $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, "contenedorBotonesLista", "contenedorBotonesLista"), "botonesLista flotanteDerecha", "botonesLista");
        }

        $contenedorComentarios = HTML::contenedor($comentarios, 'contenedorBotonComentariosItems botonComentariosItemsGris', 'contenedorBotonComentariosItems', array('ruta' => '/ajax/users/comment', 'modulo' => 'DOCUMENTOS', 'registro' => $archivo->id, 'propietarioItem' => $archivo->idAutor, 'ayuda' => $textos->id('HACER_COMENTARIO')));
        $contenedorComentarios = HTML::contenedor($contenedorComentarios, 'contenedorComentariosListaDocumentos');



        $contenidoArchivo = $botonEliminar . $contenedorComentarios;
        $contenidoArchivo .= HTML::enlace(HTML::imagen($archivo->icono, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $archivo->enlace);
        $contenidoArchivo .= HTML::parrafo(HTML::enlace($archivo->titulo, $archivo->enlace));
        $contenidoArchivo2 = HTML::parrafo($archivo->descripcion);
        $contenidoArchivo2 .= HTML::parrafo(HTML::frase($textos->id("ENLACE") . ": ", "negrilla") . $archivo->enlace, "margenSuperior");
        $contenidoArchivo .= HTML::contenedor($contenidoArchivo2, "contenedorGrisLargo");

        $listaArchivos[] = HTML::contenedor($contenidoArchivo, "contenedorListaDocumentos", "contenedorDocumento" . $archivo->id);
    }//fin del foreach
    
    $paginacion = Recursos:: mostrarPaginador($cantidadDocumentos, $registroInicial, $registros, $pagina, $totalPaginas);

    $listaArchivos[] = $paginacion;
    
    
    
} else {
    $listaArchivos[] = HTML::frase(HTML::parrafo($textos->id("SIN_ARCHIVOS"), "sinRegistros", "sinRegistros"), "margenInferior");
}


$bloqueArchivos .= $botonAgregar . HTML::lista($listaArchivos, "listaVertical bordeSuperiorLista", "botonesOcultos", "listaDocumentos");


$contenido = HTML::bloque("listadoUsuarios", $tituloBloque, $bloqueArchivos);


Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;
?>
