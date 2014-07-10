<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Contactos
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * 
 * Modificado el: 16-01-12
 *
 * */
global $configuracion, $sesion_usuarioSesion, $textos;

$contacto = new Contacto();
$contenido = "";



if (isset($url_ruta)) {
    $user = new Usuario($url_ruta);
} else {
    $user = new Usuario($sesion_usuarioSesion->id);
}



$registros = $configuracion["GENERAL"]["registrosPorPagina"];

if (isset($forma_pagina)) {
    $pagina = $forma_pagina;
} else {
    $pagina = 1;
}

$registroInicial = ($pagina - 1) * $registros;

////////////////////////////////////////////////////////////////////////////    

$tituloBloque = $textos->id("CONTACTOS") . ": " . HTML::frase($contacto->contarContactos($user->id), "cantidadContactos");

$buscadorFiltro = HTML::contenedor(HTML::botonAjax("masGrueso", HTML::frase($textos->id("BUSCAR"), "botonPequeno"), HTML::urlInterna("CONTACTOS", 0, true, "searchContacts")), "flotanteDerecha");


$contactos = new Contacto();
$listaPendientes = array();


$tamanoArregloContactos = $contactos->contarContactos($user->id);

$arregloContactos = $contactos->listarContactos($registroInicial, $registros, array('0'), "", $user->id);




$arregloPendientes = $contactos->listarSolicitudesAmistad(0, 0, NULL, "", $user->id);
$tamanoArregloPendientes = sizeof($arregloPendientes);

if ($tamanoArregloPendientes > 0) {//si el usuario tiene solicitudes de amistad
    foreach ($arregloPendientes as $contacto) {//recorro las solicitudes de amistad pendientes de aceptar

        //$solicitante = new Usuario($contacto->id);
        //Creo los dos botones para aceptar o rechazar una solicitud de amistad
        $url = "/users/" . $contacto->usuario;
        
        $formaAceptar = Contacto::formaAceptarAmistad($contacto->id);
        $formaRechazar = Contacto::formaRechazarAmistad($contacto->id);

        $imagen = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $contacto->imagen;

        $item = HTML::enlace(HTML::imagen($imagen, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $url);

        $item .= HTML::enlace(HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . $contacto->genero . ".png") . $contacto->nombre, "negrilla"), $url);
        $item3 = "";

        if (!empty($contacto->centro)) {
            $item3 .= HTML::parrafo($contacto->centro . $formaRechazar . $formaAceptar, "pequenia cursiva negrilla margenInferior");
        } else {
            $item3 .= HTML::parrafo($textos->id("SIN_CENTRO_BINACIONAL") . $formaRechazar . $formaAceptar, "pequenia cursiva negrilla margenInferior");
        }

        if (!empty($contacto->ciudad)) {
            $item3 .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["iconosBanderas"] . "/" . strtolower($contacto->codigoIsoPais) . ".png", "miniaturaBanderas") . " " . $contacto->ciudad . ", " . $contacto->pais);
        } else {
            $item3 .= HTML::parrafo($textos->id("SIN_CIUDAD"), "pequenia negrilla");
        }
        $item .= HTML::contenedor($item3, "fondoUltimos5Gris"); //barra del contenedor gris                        
        $item = HTML::contenedor($item, "contactosPendientes", "contactosPendientes" . $contacto->id);
        $listaPendientes[] = $item;
    }//fin del foreach

    $listaPendientes = HTML::lista($listaPendientes, "listaVertical listaConIconos bordeInferiorLista", "botonesOcultos");
    $contenido .= HTML::bloque("listadoUsuarios", $textos->id("SOLICITUDES_DE_AMISTAD") . ": " . HTML::frase($tamanoArregloPendientes."", "cantidadAmigosPendientes"), $listaPendientes);
}


if ($tamanoArregloContactos > 0) {//si el usuario actual tiene contactos


    if ($tamanoArregloPendientes > 0) {
        foreach ($arregloPendientes as $contacto) {//si el usuario tiene solicitudes de amistad, las recorro y las pongo en el DOM pero ocultas
                                                   //para en caso de aceptar alguna, se muestre directamente en sus contactos
            //$solicitante = new Usuario($contacto->id);
            //Creo los dos botones para aceptar o rechazar una solicitud de amistad
            $formaEliminar = Contacto::formaEliminarAmistad($contacto->id); //boton para eliminar una amistad
            $formaEnviarMensaje = Contacto::formaEnviarMensaje($contacto->id);
            
            $url = "/users/" . $contacto->usuario;

            $imagen = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $contacto->imagen;

            $item = HTML::enlace(HTML::imagen($imagen, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $url);
            $item .= HTML::enlace(HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . $contacto->genero . ".png") . $contacto->nombre, "negrilla"), $url);
            $item3 = "";

            if (!empty($contacto->centro)) {
                $item3 .= HTML::parrafo($contacto->centro . $formaEliminar . $formaEnviarMensaje, "pequenia cursiva negrilla margenInferior");
            } else {
                $item3 .= HTML::parrafo($textos->id("SIN_CENTRO_BINACIONAL") . $formaEliminar . $formaEnviarMensaje, "pequenia cursiva negrilla margenInferior");
            }

            if (!empty($contacto->ciudad)) {
                $item3 .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["iconosBanderas"] . "/" . strtolower($contacto->codigoIsoPais) . ".png", "miniaturaBanderas") . " " . $contacto->ciudad . ", " . $contacto->pais);
            } else {
                $item3 .= HTML::parrafo($textos->id("SIN_CIUDAD"), "pequenia negrilla");
            }
            $item .= HTML::contenedor($item3, "fondoUltimos5Gris"); //barra del contenedor gris                        
            $item = HTML::contenedor($item, "contactosPendientes", "contactosNuevosPendientes" . $contacto->id, array("style" => "display: none"));
            $listaContactos[] = $item;
        }//fin del foreach          
    }



    foreach ($arregloContactos as $contacto) {//recorro y muestro los contactos del usuario

        $formaEliminar = Contacto::formaEliminarAmistad($contacto->id); //boton para eliminar una amistad
        $formaEnviarMensaje = Contacto::formaEnviarMensaje($contacto->id);

        $url = "/users/" . $contacto->usuario;
        //$user = new Usuario($contacto->id);
        //$amigo = new Persona($user->idPersona);
        $imagenMiniatura = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $contacto->imagen;

        $item = HTML::enlace(HTML::imagen($imagenMiniatura, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $url);
        $item .= HTML::enlace(HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . $contacto->genero . ".png") . $contacto->nombre, "negrilla"), $url);
        $item3 = "";
        if (!empty($contacto->centro)) {
            $item3 .= HTML::parrafo($contacto->centro . $formaEliminar . $formaEnviarMensaje, "pequenia cursiva negrilla margenInferior");
        } else {
            $item3 .= HTML::parrafo($textos->id("SIN_CENTRO_BINACIONAL") . $formaEliminar . $formaEnviarMensaje, "pequenia cursiva negrilla margenInferior");
        }

        if (!empty($contacto->ciudad)) {
            $item3 .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["iconosBanderas"] . "/" . strtolower($contacto->codigoIsoPais) . ".png", "miniaturaBanderas") . " " . $contacto->ciudad . ", " . $contacto->pais);
        } else {
            $item3 .= HTML::parrafo($textos->id("SIN_CIUDAD"), "pequenia negrilla");
        }
        $item .= HTML::contenedor($item3, "fondoUltimos5Gris"); //barra del contenedor gris
        $item = HTML::contenedor($item, "contactosActuales", "contactosActuales" . $contacto->id);
        // Recursos::escribirTxt("Este es: ".$amigo->persona->nombreCompleto, 5);  
        $listaContactos[] = $item;
    }//fin del foreach
    //////////////////paginacion /////////////////////////////////////////////////////
    $paginacion = Recursos:: mostrarPaginador($tamanoArregloContactos, $registroInicial, $registros, $pagina);

    $listaContactos[] = $paginacion;

    $listaContactos = HTML::lista($listaContactos, "listaVertical listaConIconos bordeInferiorLista", "botonesOcultos");
    $contenido .= HTML::bloque("listadoUsuarios", $tituloBloque , $buscadorFiltro.$listaContactos);
} else {//si el usuario no tiene contactos

    if ($tamanoArregloPendientes > 0) {//verifico si el usuario tiene solicitudes de amistad y las pongo en el dom ocultas
                                       //por si aun asi, no tiene contactos, y acepta una solicitud, se muestre como su contacto
        foreach ($arregloPendientes as $contacto) {
            
            //$solicitante = new Usuario($contacto->id);
            //Creo los dos botones para aceptar o rechazar una solicitud de amistad
            $url = "/users/" . $contacto->usuario;
            $formaEliminar = Contacto::formaEliminarAmistad($contacto->id); //boton para eliminar una amistad

            $imagenMiniatura = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $contacto->imagen;

            $item = HTML::enlace(HTML::imagen($imagenMiniatura, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $url);
            $item .= HTML::enlace(HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . $contacto->genero . ".png") . $contacto->nombre, "negrilla"), $url);
            $item3 = "";

            if (!empty($contacto->centro)) {
                $item3 .= HTML::parrafo($contacto->centro . $formaEliminar, "pequenia cursiva negrilla margenInferior");
            } else {
                $item3 .= HTML::parrafo($textos->id("SIN_CENTRO_BINACIONAL") . $formaEliminar, "pequenia cursiva negrilla margenInferior");
            }

            if (!empty($contacto->ciudad)) {
                $item3 .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["iconosBanderas"] . "/" . strtolower($contacto->codigoIsoPais) . ".png", "miniaturaBanderas") . " " . $contacto->ciudad . ", " . $contacto->pais);
            } else {
                $item3 .= HTML::parrafo($textos->id("SIN_CIUDAD"), "pequenia negrilla");
            }
            $item .= HTML::contenedor($item3, "fondoUltimos5Gris"); //barra del contenedor gris                        
            $item = HTML::contenedor($item, "contactosPendientes", "contactosNuevosPendientes" . $contacto->id, array("style" => "display: none"));
            $listaContactos[] = $item;
        }//fin del foreach          
    }

    $listaContactos = HTML::lista($listaContactos, "listaVertical listaConIconos bordeInferiorLista", "botonesOcultos");
    $texto = HTML::contenedor($textos->id("NO_TIENES_CONTACTOS"), "contactosPendientes", "sinContactos");
    $contenido .= HTML::bloque("listadoUsuarios", $tituloBloque . "<br>" . $buscadorFiltro, $texto . "<br>" . $listaContactos);
}


Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;
?>
