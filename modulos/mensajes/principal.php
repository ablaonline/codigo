<?php

/**
 * @package     FOLCS
 * @subpackage  Mensajes
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * 
 * Modificado el: 11-01-2012
 * */
global $configuracion, $sesion_usuarioSesion, $sql, $textos, $url_ruta, $forma_pagina;

$contenido = "";


if (isset($url_ruta)) {
    $user = new Usuario($url_ruta);
} else {
    $user = new Usuario($sesion_usuarioSesion->id);
}

Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: " . $textos->id("MODULO_ACTUAL");

/**
 * Datos para la paginacion
 * */
$mensajes = new Mensaje();

$totalRegistros = $mensajes->contarMensajesUsuario();


$registros = $configuracion["GENERAL"]["registrosPorPagina"];

if (isset($forma_pagina)) {
    $pagina = $forma_pagina;
} else {
    $pagina = 1;
}
$registroInicial = ($pagina - 1) * $registros;



$tituloBloque       = $textos->id("MENSAJES") . " :: " . $user->usuario;
$modulo_principal   = new Modulo("USUARIOS");
$botonBorrar        = HTML::contenedor(HTML::boton("basura", $textos->id("ELIMINAR"), "directo", "", "botonBorrarMensajesMultiples", "", ""), "escondido contenedorBotonBorrarMensajesMultiples", "contenedorBotonBorrarMensajesMultiples");
$checkBorrar        = HTML::campoChequeo("", "", "marcarTodosLosMensajes", "marcarTodosLosMensajes") . HTML::frase($textos->id("SELECCIONAR_TODOS"), "negrilla letraMasGrande1 margenDerechaDoble margenSuperior");
$checkBorrar        = HTML::contenedor($checkBorrar, "flotanteIzquierda");
$contenedorBorrar   = HTML::contenedor($checkBorrar . $botonBorrar, "flotanteIzquierda bordeInferior mitadEspacioInferior");
$listaMensajes[]    = $contenedorBorrar;
$listaMensajes[]    = HTML::contenedor(HTML::botonAjax("sobreCerrado", $textos->id("ENVIAR_MENSAJE"), HTML::urlInterna("USUARIOS", 0, true, "sendMessage")), "flotanteDerecha");


//$sql->depurar = true;
$numMensajes = $sql->seleccionar(array("mensajes"), array("id", "id_usuario_destinatario", "titulo"), "id_usuario_destinatario = '" . $user->id . "'", "", "");

if ($sql->filasDevueltas) {

    foreach ($mensajes->listar($registroInicial, $registros, $user->id) as $mensaje) {

        $botones    = HTML::contenedor(HTML::botonAjax("basura", $textos->id("ELIMINAR"), "/ajax/$modulo_principal->url/deleteMessage", array("id" => $mensaje->id)), "flotanteCentro contenedorBotonesLista", "contenedorBotonesLista");
        $botones   .= HTML::contenedor(HTML::botonAjax("sobreCerrado", $textos->id("RESPONDER_MENSAJE"), "/ajax/$modulo_principal->url/replyMessage", array("id" => $mensaje->idAutor)), "flotanteDerecha contenedorBotonesLista", "contenedorBotonesLista");
        $item       = HTML::contenedor($botones, "botonesLista", "botonesLista");
        $item      .= HTML::campoChequeo("$mensaje->id", "", "checksMensajes");
        $item      .= HTML::enlace(HTML::imagen($mensaje->fotoAutor, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("USUARIOS", $mensaje->usuario));
        $item      .= HTML::parrafo(HTML::enlace($mensaje->autor, HTML::urlInterna("USUARIOS", $mensaje->usuario)), "negrilla");
        $item      .= HTML::parrafo(date("D, d M Y h:i:s A", $mensaje->fecha), "pequenia cursiva negrilla");


        $sobre   = "";
        $idSobre = "";
        if ($mensaje->leido == 0) {
            $sobre = HTML::contenedor("", "mensajesNuevos", "mensajeNuevo".$mensaje->id);
            $idSobre = "#mensajeNuevo".$mensaje->id;
        }
//        $url        = "/ajax/users/readMessage";
//        $opciones   = array("onClick" => "$('#mensajeNuevo". $mensaje->id ."').hide('slow');");
//        $boton      = HTML::botonImagenAjax(HTML::frase($mensaje->titulo . "... See Message" . HTML::icono("circuloFlechaDerecha") . $sobre, "estiloEnlace mensajeNuevoClick", "leerMensaje", $opciones), "", "", "", $url, $datos, "formaMeGusta");
        $opciones   = array("id_mensaje" => $mensaje->id, "icono_sobre" => $idSobre);
        $boton      = HTML::parrafo($mensaje->titulo . $textos->id("VER_MENSAJE") . HTML::icono("circuloFlechaDerecha") . $sobre, "estiloEnlace mensajeNuevoClick", "leerMensaje", $opciones);

        
        
        $item      .= $boton;
        $item       = HTML::contenedor($item, "", "contenedorMensajes" . $mensaje->id);

        $listaMensajes[] = $item;
    }//fin del foreach  

    $paginacion = Recursos:: mostrarPaginador($totalRegistros, $registroInicial, $registros, $pagina);

    $listaMensajes[] .= $paginacion;
} else {

    $listaMensajes[].= $textos->id("SIN_MENSAJES");
}//fin del if


$listaMensajes = HTML::lista($listaMensajes, "listaVertical listaConIconos bordeInferiorLista", "");
$contenido .= HTML::bloque("listadoUsuarios", $tituloBloque, $listaMensajes);


Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;
?>
