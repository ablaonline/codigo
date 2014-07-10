<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Centros
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

$contenido = "";
global $sesion_usuarioSesion, $url_ruta, $modulo;

if (isset($url_ruta)) {
    $contenido = "";
    $centro   = new Centro($url_ruta);

    if (isset($centro->id)) {  
        Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: ".$textos->id("MODULO_ACTUAL");
        Plantilla::$etiquetas["DESCRIPCION"]    = $centro->nombre;

        $nombreBloque = $textos->id("MAS_CENTROS");
        $excluidas    = array($centro->id);
        $botones      = "";


	/* Determinar si el usuario que esta visitando es el administrador del centro actual */
	$esAdministrador = $sql->existeItem('admin_centro', 'id_usuario', $sesion_usuarioSesion->id, 'id_centro = "'.$url_ruta.'"');

       /* Determinar si el usuario que visita es un advisor o un bibliotecario */

        $permisoSobreBibliotecario = $sql->obtenerValor("relacion_perfiles", "perfil_padre", "perfil_padre = '".$sesion_usuarioSesion->idTipo."' AND perfil_hijo = '101'");
        $permisoSobreEducationUsa  = $sql->obtenerValor("relacion_perfiles", "perfil_padre", "perfil_padre = '".$sesion_usuarioSesion->idTipo."' AND perfil_hijo = '105'");


        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0) || isset($sesion_usuarioSesion) && $esAdministrador || ($centro->id == 33 && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 101 || $permisoSobreBibliotecario) || ($centro->id == 34 && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 105 || $permisoSobreEducationUsa)) {
            $botones .= HTML::botonEliminarItem($centro->id, $centro->urlBase);
            $botones .= HTML::botonModificarItem($centro->id, $centro->urlBase);
            
            $botones  = HTML::contenedor($botones, "botonesInternos", "botonesInternos");
        }

        $contenidoCentro  = $botones;
        $logoCentro       = "";

        if ($centro->logo) {
            $logoCentro   = HTML::imagen($centro->logo, "margenInferior margenDerecha margenIzquierda mitadMargenSuperior flotanteIzquierda imagenCentro");
        }
        
        if ($centro->paginaWeb) {
            $pagina   = HTML::parrafo(HTML::enlace($centro->paginaWeb, "http://".$centro->paginaWeb, "", "", array("target" => "_blank")), "justificado margenSuperior");
        }        

	$descripcion = HTML::contenedor($centro->descripcion, "justificado");

        $contenidoCentro .= HTML::contenedor($logoCentro.$descripcion.$pagina, "margenInferior mitadMargenIzquierda margenDerecha");
        $contenidoCentro .= HTML::contenedor(HTML::botonesCompartir(), "botonesCompartir");
        $contenido        = HTML::bloque("centro_".$centro->id, $centro->nombre, $contenidoCentro, "", "botonesOcultos");

        $sedes = new Sede();

        if ( (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0) ) || (isset($sesion_usuarioSesion) && $esAdministrador) || ($centro->id == 33 && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 101 || $permisoSobreBibliotecario) || ($centro->id == 34 && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 105 || $permisoSobreEducationUsa) ) {
            $botonAdicionar = HTML::botonAjax("masGrueso", "ADICIONAR_SEDE", HTML::urlInterna("CENTROS", "", true, "addBranch"), array("centro" => $centro->id));
            $botonAdicionar = HTML::contenedor($botonAdicionar, "flotanteDerecha margenInferior");
            $listaSedes[]   = $botonAdicionar;
        }

        foreach ($sedes->listar(0, 0, $excluidas, "s.id_centro = '".$centro->id."'") as $sede) {
            $botones = "";

            if ( (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0) ) || (isset($sesion_usuarioSesion) && $esAdministrador ) || ($centro->id == 33 && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 101 || $permisoSobreBibliotecario) || ($centro->id == 34 && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 105 || $permisoSobreEducationUsa) ) {
                $botones .= HTML::contenedor(HTML::botonAjax("basura", "ELIMINAR", HTML::urlInterna("CENTROS", "", true, "deleteBranch"), array("id" => $sede->id)), "contenedorBotonesLista", "contenedorBotonesLista");
                $botones .= HTML::contenedor(HTML::botonAjax("lapiz", "MODIFICAR", HTML::urlInterna("CENTROS", "", true, "editBranch"), array("id" => $sede->id)), "contenedorBotonesLista", "contenedorBotonesLista");
                $botones  = HTML::contenedor($botones, "botonesLista", "botonesLista");
            }

            $contenidoSede  = $botones;
            $contenidoSede .= HTML::parrafo($sede->nombre, "negrilla");
            $contenidoSede .= HTML::parrafo($sede->ciudad.", ".$sede->pais);

            if ($sede->direccion) {
                $contenidoSede .= HTML::parrafo($textos->id("DIRECCION"), "negrilla margenSuperior");
                $contenidoSede .= HTML::parrafo($sede->direccion);
            }

            if ($sede->telefono1) {
                $contenidoSede .= HTML::parrafo($textos->id("TELEFONO"), "negrilla margenSuperior");
                $contenidoSede .= HTML::parrafo($sede->telefono1." ".$sede->telefono2);
            }

            if ($sede->celular) {
                $contenidoSede .= HTML::parrafo($textos->id("CELULAR"), "negrilla margenSuperior");
                $contenidoSede .= HTML::parrafo($sede->celular);
            }

            if ($sede->correo) {
                $contenidoSede .= HTML::parrafo($textos->id("CORREO"), "negrilla margenSuperior");
                $contenidoSede .= HTML::parrafo(HTMl::enlace($sede->correo, "mailto:".$sede->correo));
            }

            $listaSedes[]   = $contenidoSede;
        }

        $bloqueSedes = HTML::contenedor(HTML::lista($listaSedes, "listaVertical bordeSuperiorLista", "botonesOcultos"), '');
        $propietario = "";
        

        
        if($centro->id == 33){//verificar que el centro sea IRC o Education USA
            if( ($centro->id == 33 && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 101 || $permisoSobreBibliotecario) || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0){

		$esBibliotecario = false;
		if( (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 101) || ($permisoSobreBibliotecario) ){
		    $esBibliotecario = true;
		}
                $Recursos = array(
		    HTML::frase($textos->id("SEDES"), "letraBlanca")         => $bloqueSedes,
                    HTML::frase($textos->id("VIDEOS"), "letraBlanca")        => Recursos::bloqueVideos("CENTROS", $centro->id, $propietario, $esBibliotecario),
                    HTML::frase($textos->id("AUDIOS"), "letraBlanca")        => Recursos::bloqueAudios("CENTROS", $centro->id, $propietario, $esBibliotecario),
                    HTML::frase($textos->id("IMAGENES"), "letraBlanca")      => Recursos::bloqueImagenes("CENTROS", $centro->id, $propietario, $esBibliotecario),
                    HTML::frase($textos->id("GALERIAS"), "letraBlanca")      => Recursos::bloqueGalerias("CENTROS", $centro->id, $propietario, $esBibliotecario),
                    HTML::frase($textos->id("DOCUMENTOS"), "letraBlanca")    => Recursos::bloqueArchivos("CENTROS", $centro->id, $propietario, $esBibliotecario),
                    HTML::frase($textos->id("FOROS"), "letraBlanca")         => Recursos::bloqueForos("CENTROS", $centro->id, $propietario),
		   
                );
            $contenido 	.= HTML::pestanas2("RecursosCentro", $Recursos); 
	    $contenido .=  HTML::agregarIframe('http://media.ablaonline.org/phpfreechat?id_item='.$centro->id.'&usuario='.$sesion_usuarioSesion->usuario.'&nombre_sala='.$textos->id('CHAT_DE').$centro->nombre.'&id_modulo='.$modulo->id, 700, 430);
            }
        }elseif($centro->id == 34){
            if( ($centro->id == 34 && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 105 || $permisoSobreEducationUsa) || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0){
		$esAdvisor = false;
		if( (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 105) || ($permisoSobreEducationUsa) ){
		    $esAdvisor = true;
		}
                $Recursos = array(
		    HTML::frase($textos->id("SEDES"), "letraBlanca")         => $bloqueSedes,
                    HTML::frase($textos->id("VIDEOS"), "letraBlanca")        => Recursos::bloqueVideos("CENTROS", $centro->id, $propietario, $esAdvisor),
                    HTML::frase($textos->id("AUDIOS"), "letraBlanca")        => Recursos::bloqueAudios("CENTROS", $centro->id, $propietario, $esAdvisor),
                    HTML::frase($textos->id("IMAGENES"), "letraBlanca")      => Recursos::bloqueImagenes("CENTROS", $centro->id, $propietario, $esAdvisor),
                    HTML::frase($textos->id("GALERIAS"), "letraBlanca")      => Recursos::bloqueGalerias("CENTROS", $centro->id, $propietario, $esAdvisor),
                    HTML::frase($textos->id("DOCUMENTOS"), "letraBlanca")    => Recursos::bloqueArchivos("CENTROS", $centro->id, $propietario, $esAdvisor),
                    HTML::frase($textos->id("FOROS"), "letraBlanca")         => Recursos::bloqueForos("CENTROS", $centro->id, $propietario),
		    
                );
            $contenido 	.= HTML::pestanas2("RecursosCentro", $Recursos); 
	    $contenido .=  HTML::agregarIframe('http://media.ablaonline.org/phpfreechat?id_item='.$centro->id.'&usuario='.$sesion_usuarioSesion->usuario.'&nombre_sala='.$textos->id('CHAT_DE').$centro->nombre.'&id_modulo='.$modulo->id, 700, 430);	    
            } 
        }else{
            $Recursos = array(
		HTML::frase($textos->id("SEDES"), "letraBlanca")         => $bloqueSedes,
                HTML::frase($textos->id("VIDEOS"), "letraBlanca")        => Recursos::bloqueVideos("CENTROS", $centro->id, $propietario, $esAdministrador),
                HTML::frase($textos->id("AUDIOS"), "letraBlanca")        => Recursos::bloqueAudios("CENTROS", $centro->id, $propietario, $esAdministrador),
                HTML::frase($textos->id("IMAGENES"), "letraBlanca")      => Recursos::bloqueImagenes("CENTROS", $centro->id, $propietario, $esAdministrador),
                HTML::frase($textos->id("GALERIAS"), "letraBlanca")      => Recursos::bloqueGalerias("CENTROS", $centro->id, $propietario, $esAdministrador),
                HTML::frase($textos->id("DOCUMENTOS"), "letraBlanca")    => Recursos::bloqueArchivos("CENTROS", $centro->id, $propietario, $esAdministrador),
                HTML::frase($textos->id("FOROS"), "letraBlanca")         => Recursos::bloqueForos("CENTROS", $centro->id, $propietario),
            );
            $contenido 	.= HTML::pestanas2("RecursosCentro", $Recursos); 
        }
        
    }

} else {
    $nombreBloque = $textos->id("MODULO_ACTUAL");
    $centro      = new Centro();
    $excluidas    = "";


    /**
     *
     * Formulario para adicionar un nuevo elemento
     *
     **/
    if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0)) {
        $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($centro->urlBase, $textos->id("ADICIONAR_CENTRO")), "flotanteDerecha margenInferior");

    } else {
        $botonAdicionar = "";
    }

    $listaCentros = array();
    $fila         = 0;
    $datosMapa    = array();

    if ($centro->registros) {
        $selector = "";

        foreach ($centro->listar(0, 0, $excluidas) as $elemento) {
            $fila++;
            $item   = "";
            $celdas = array();
            

            if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0)) {
                $botones  = "";
                $botones .= HTML::botonEliminarItem($elemento->id, $centro->urlBase);
                $botones .= HTML::botonModificarItem($elemento->id, $centro->urlBase);
                
                $item    .= HTML::contenedor($botones, "botonesLista",  "botonesLista");
            }

            if ($elemento->activo) {

                if (!isset($paisActual)) {
                    $paisActual   = $elemento->idPais;
                    $listaPaises  = array($elemento->pais);
                    $listaCentros = array();
                    $selector[$elemento->pais] = $elemento->pais;
                }

                if ($paisActual != $elemento->idPais) {
                    $contenidos[]  = HTML::lista($listaCentros, "listaVertical bordeSuperiorLista", "botonesOcultos");
                    $listaCentros  = array();
                    $listaPaises[] = $elemento->pais;
                    $paisActual    = $elemento->idPais;
                    $selector[$elemento->pais] = $elemento->pais;
                }

                $item .= HTML::enlace(HTML::parrafo($elemento->nombre, "negrilla"), $elemento->url);
                $item .= HTML::parrafo($elemento->ciudad.", ".$elemento->estado, "margenInferior");
                $listaCentros[] = $item;


                $sedes = new Sede();
                if ($sedes->registros) {
                    $datos = $sedes->listar(0, 0, "", "s.id_centro = '".$elemento->id."'");
                    if ($datos) {
                        foreach ($datos as $sede) {

                            if (($sede->latitud != 0) && ($sede->longitud != 0)) {
                                $nombre      = $elemento->nombre." :: ".$sede->ciudad.", ".$elemento->pais;
                                $datosMapa[] = "['$sede->latitud','$sede->longitud','$nombre']";

                            }//fin del if($sede->latitud)

                        }//fin del foreach($datos as $sede)

                    }//fin del if($datos)
                }
            }//fin del if($elemento->activo)  


        }//fin del foreach($centro->listar)  

    }//fin if $centro->registros

   $selectorPais  = HTML::listaDesplegable("selectorPais", $selector, "", "flotanteIzquierda margenInferior margenIzquierda", "selectorPais", "", array("onchange" => "ubicarPais('selectorPais'); return false;"));
    $formaPais     = HTML::forma($_SERVER["PHP_SELF"], $selectorPais);

    $contenidos[]  = HTML::lista($listaCentros, "listaVertical bordeSuperiorLista", "botonesOcultos");

    $mapa       = HTML::contenedor(HTML::mapaNuevo(), "mapaNuevo", "mapaNuevo");
    $contenido .= HTML::bloque("", $textos->id("MODULO_ACTUAL"), $botonAdicionar.$mapa);
    $contenido .= HTML::acordeonLargo($listaPaises, $contenidos, "", ""); 
}


Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;

?>
