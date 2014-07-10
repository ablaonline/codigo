<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Cultural por centro
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 COLOMBOAMERICANO
 * @version     0.1
 *
 **/
$contenido = "";
if (isset($url_ruta)) {
    $contenido = "";
    $centro   = new Centro($url_ruta);

    if (isset($centro->id)) {  
        Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: ".$textos->id("MODULO_ACTUAL");
        Plantilla::$etiquetas["DESCRIPCION"]    = $centro->nombre;

        $nombreBloque = $textos->id("MAS_CENTROS");
        $excluidas    = array($centro->id);
        $botones      = "";

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0)) {
            $botones .= HTML::botonEliminarItem($centro->id, $centro->urlBase);
            $botones .= HTML::botonModificarItem($centro->id, $centro->urlBase);
            
            $botones  = HTML::contenedor($botones, "botonesInternos", "botonesInternos");
        }

        $contenidoCentro  = $botones;
        $logoCentro       = "";

        if ($centro->logo) {
            $logoCentro       = HTML::imagen($centro->logo, "margenInferior margenDerecha flotanteIzquierda");
        }

        $contenidoCentro .= HTML::contenedor($logoCentro.$centro->descripcion, "justificado margenInferior");
        $contenidoCentro .= HTML::contenedor(HTML::botonesCompartir(), "botonesCompartir");
        $contenido        = HTML::bloque("centro_".$centro->id, $centro->nombre, $contenidoCentro, "", "botonesOcultos");

        $sedes = new Sede();

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0)) {
            $botonAdicionar = HTML::botonAjax("masGrueso", "ADICIONAR_SEDE", HTML::urlInterna("CENTROS", "", true, "addBranch"), array("centro" => $centro->id));
            $botonAdicionar = HTML::contenedor($botonAdicionar, "flotanteDerecha margenInferior");
            $listaSedes[]   = $botonAdicionar;
        }

        foreach ($sedes->listar(0, 0, $excluidas, "s.id_centro = '".$centro->id."'") as $sede) {
            $botones = "";

            if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0)) {
                $botones .= HTML::botonAjax("lapiz", "MODIFICAR", HTML::urlInterna("CENTROS", "", true, "editBranch"), array("id" => $sede->id));
                $botones .= HTML::botonAjax("basura", "ELIMINAR", HTML::urlInterna("CENTROS", "", true, "deleteBranch"), array("id" => $sede->id));
                $botones  = HTML::contenedor($botones, "oculto flotanteDerecha", "botonesInternos");
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

        $contenido .= HTML::bloque("", $textos->id("SEDES"), HTML::lista($listaSedes, "listaVertical bordeSuperiorLista", "botonesOcultos"));

        $Recursos = array(
            HTML::frase($textos->id("VIDEOS"), "letraBlanca")        => Recursos::bloqueVideos("CENTROS", $centro->id, $centro->idAutor),
            HTML::frase($textos->id("AUDIOS"), "letraBlanca")        => Recursos::bloqueAudios("CENTROS", $centro->id, $centro->idAutor),
            HTML::frase($textos->id("IMAGENES"), "letraBlanca")      => Recursos::bloqueImagenes("CENTROS", $centro->id, $centro->idAutor),
            HTML::frase($textos->id("DOCUMENTOS"), "letraBlanca")    => Recursos::bloqueArchivos("CENTROS", $centro->id, $centro->idAutor),
            HTML::frase($textos->id("FOROS"), "letraBlanca")         => Recursos::bloqueForos("CENTROS", $centro->id, $centro->idAutor)
        );

        $contenido 	.= HTML::pestanas2("RecursosCentro", $Recursos);
        
        $chat = "";
		/*** Tinychat ***/
		$parametros = array(
			"room"          => "ablaonline_courses_".$curso->id,
			"colorbk"       => "0x2A2D85",
			"api"           => "none",
			"langdefault"   => "en",
			"join"			=> "auto",
			"owner"			=> "none",
			"oper" 			=> "none",
			"nick"			=> "invited"
		);

		if (isset($sesion_usuarioSesion)) {
			$parametros["nick"] = $sesion_usuarioSesion->usuario;
			if ($sesion_usuarioSesion->id == 0) {
				unset($parametros["owner"]);
				unset($parametros["oper"]);
			}
		}

		$chat .= "
		<script type=\"text/javascript\">
		  var tinychat = ".json_encode($parametros).";
		</script>
		<script src=\"http://tinychat.com/js/embed.js\"></script>
		<div id=\"client\"></div>
		";
        
		$contenido	.= "<span class=\"bloqueTitulo encabezadoBloque ui-helper-clearfix ui-widget-header ui-corner-top\">".$textos->id("CHAT")."</span>\n";
		$contenido	.= $chat;
    }

} else {
    $nombreBloque = $textos->id("MODULO_ACTUAL");
    $evento  = new Evento();    
    $noticia = new Noticia();
    $excluidas    = "";
    
    $listaNoticias = array();
    $listaEventos = array();
    
    
    //Datos del paginador para noticias
    $registros = $configuracion["GENERAL"]["registrosPorPagina"];
    if (isset($forma_pagina)) {
        $pagina = $forma_pagina;
    } else {
        $pagina = 1;
    }
    $registroInicial = ($pagina - 1) * $registros;
    
    //datos del paginador para eventos
    $registros1 = $configuracion["GENERAL"]["registrosPorPagina"];
    if (isset($forma_pagina1)) {
        $pagina1 = $forma_pagina1;
    } else {
        $pagina1 = 1;
    }
    $registroInicial1 = ($pagina1 - 1) * $registros1;



    /**
    *
    * Capturar el tipo de usuario que tiene el usuario actual
    *
    * */
    if (isset($sesion_usuarioSesion)) {
        $idTipo = $sesion_usuarioSesion->idTipo;
    } else {
        $idTipo = 99;
    }
    

    

    /**
     * Formulario para adicionar un nuevo elemento
     **/
    if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0) || (isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicion(34))) {        
        $botonAdicionarNoticia  = HTML::contenedor(HTML::botonAjax("mas", $textos->id("AGREGAR_NOTICIA_CULTURAL"), "/ajax/news/addCulturalNew"), "alineadoDerecha");
        $botonAdicionarEvento   = HTML::contenedor(HTML::botonAjax("mas", $textos->id("AGREGAR_EVENTO_CULTURAL"), "/ajax/events/addCulturalEvent"), "alineadoDerecha");
        $botonAdicionar  = HTML::contenedor($botonAdicionarNoticia.$botonAdicionarEvento, "margenInferiorTriple");
    } else {
        $botonAdicionar = "";
    }
    
    //COdigo para listar las noticias
    $arregloNoticias = $noticia->listar($registroInicial, $registros, $excluidas, "", $idTipo, $noticia->idModulo, "10");
    
//    if ($noticia->registros) {

        foreach ($arregloNoticias as $elemento) {

                $item = "";
                $celdas = array();
                $totalRegistrosActivos = 0;

                    if ($elemento->activo) {

                        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->id == $elemento->idAutor)) {
                            $botones = "";
                            $botones .= HTML::botonEliminarItemAjax($elemento->id, $noticia->urlBase);
                            $botones .= HTML::botonModificarItemAjax($elemento->id, $noticia->urlBase);
                            $item .= HTML::contenedor($botones, "botonesLista", "botonesLista");
                        }

                        $comentario = new Comentario();

                        $contenedorComentarios = $comentario->mostrarComentarios($noticia->idModulo, $elemento->id);
                        $contenedorMeGusta = Recursos::mostrarContadorMeGusta($noticia->idModulo, $elemento->id);
                        $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, "mostrarPosted");
                        //seleccionar el genero de una persona 
                        $persona = new Persona($elemento->idAutor);
                        $item .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("NOTICIAS", $elemento->id));
                        $item .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . $persona->idGenero . ".png") . preg_replace("/\%1/", HTML::enlace($elemento->autor, HTML::urlInterna("USUARIOS", $elemento->usuarioAutor)) . "On " . HTML::frase(date("D, d M Y", $elemento->fechaPublicacion), "pequenia cursiva negrilla") . $comentarios, $textos->id("PUBLICADO_POR")));
                        $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, "negrilla"), $elemento->url);
                        $item2 .= HTML::parrafo($elemento->resumen, "pequenia cursiva");
                        $item .= HTML::contenedor($item2, "fondoUltimos5GrisB"); //barra del contenedor gris

                        $item = HTML::contenedor($item, "contenedorListaNoticias", "contenedorListaNoticias" . $elemento->id);

                        $listaNoticias[] = $item;
                        $totalRegistrosActivos++;
                    }
                
            }//fin del foreach
            //paginacion /////////////////////////////////////////////////////
            $paginacion = Recursos:: mostrarPaginador($totalRegistrosActivos, $registroInicial, $registros, $pagina);
            $listaNoticias[] = $paginacion;
//        } else {
//            $listaNoticias = array($textos->id("SIN_REGISTROS"));
//        }
            $listaNoticias = HTML::lista($listaNoticias, "listaVertical listaConIconos bordeSuperiorLista", "botonesOcultos", "listaNoticias");
  //Fin del metodo listar noticias  
    
  
        
        
    //Codigo para listar los Eventos
    $arregloEventos = $evento->listar($registroInicial, $registros, $excluidas, "", $idTipo, $evento->idModulo, "10");
    
//    if ($evento->registros) {

         foreach ($arregloEventos as $elemento) {

                $item = "";
                $celdas = array();
                $totalRegistrosActivos1 = 0;
                    if ($elemento->activo) {

                        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->id == $elemento->idAutor)) {
                            $botones = "";
                            $botones .= HTML::botonEliminarItemAjax($elemento->id, $evento->urlBase);
                            $botones .= HTML::botonModificarItemAjax($elemento->id, $evento->urlBase);
                            $item .= HTML::contenedor($botones, "botonesLista", "botonesLista");
                        }

                        $comentario = new Comentario();

                        $contenedorComentarios = $comentario->mostrarComentarios($evento->idModulo, $elemento->id);
                        $contenedorMeGusta = Recursos::mostrarContadorMeGusta($evento->idModulo, $elemento->id);
                        $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, "mostrarPosted");
                        //seleccionar el genero de una persona 
                        $item .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("EVENTOS", $elemento->id));
                        $item .= HTML::parrafo(HTML::frase($textos->id("CIUDAD"), "negrilla").": ".$elemento->ciudad.", ".$elemento->pais .HTML::imagen($elemento->iconoBandera, "iconoBanderaEventoCultural") . HTML::frase($textos->id("FECHA_INICIO").": ", "negrilla margenIzquierdaDoble").date("D, d M Y", $elemento->fechaInicio) . $comentarios, $textos->id("PUBLICADO_POR"));
                        $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, "negrilla"), $elemento->url);
                        $item2 .= HTML::parrafo($elemento->resumen, "pequenia cursiva");
                        $item .= HTML::contenedor($item2, "fondoUltimos5GrisB"); //barra del contenedor gris

                        $item = HTML::contenedor($item, "contenedorListaEventos", "contenedorListaEventos" . $elemento->id);

                        $listaEventos[] = $item;
                        $totalRegistrosActivos1 ++;
                    }
                
          }//fin del foreach
//////////////////paginacion /////////////////////////////////////////////////////
          
          if(sizeof($listaEventos) > 0){
            $paginacion = Recursos:: mostrarPaginador2($totalRegistrosActivos1, $registroInicial1, $registros1, $pagina1);
            $listaEventos[] = $paginacion;
          }else{
              $listaEventos = array($textos->id("SIN_EVENTOS"));
          }
//        } else {
//            $listaEventos = array($textos->id("SIN_REGISTROS"));
//        }     
        $listaEventos = HTML::lista($listaEventos, "listaVertical listaConIconos bordeSuperiorLista", "botonesOcultos", "listaEventos");
    
    
    
    
    $barraCultural = array(
        HTML::frase($textos->id("NOTICIAS_CULTURALES"), "letraBlanca", "pestanaListaNoticias") => $listaNoticias,
        HTML::frase($textos->id("EVENTOS_CULTURALES"), "letraBlanca", "pestanaListaEventos") => $listaEventos
    );
        

    $scriptMapa    = HTML::bloqueNoticiasCultural();

    //$mapa       = HTML::contenedor("", "mapa", "mapaCentros");
    $contenido .= HTML::bloque("", $textos->id("MODULO_ACTUAL"), $botonAdicionar.$scriptMapa);
    $contenido .= HTML::contenedor(HTML::pestanas2("barraCultural", $barraCultural), "pestanasRecursosCultural");

}

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"]       = $contenido;

?>
