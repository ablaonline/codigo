<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Cursos
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 * 
 * modificado el 20-01-12
 * 
 **/
global $url_ruta, $sql, $configuracion, $textos, $modulo, $sesion_usuarioSesion;
$contenido = "";

if (isset($url_ruta)) {
   
    $seguidor_actual	= false;

    
    $curso   = new Curso($url_ruta);

    if (isset($curso->id)) {
        Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: ".$textos->id("MODULO_ACTUAL");
        Plantilla::$etiquetas["DESCRIPCION"]    = $curso->nombre;

        $nombreBloque = $textos->id("MAS_CURSOS");
        $excluidas    = array($curso->id);
        $botones      = "";
        $tituloPrincipal = HTML::enlace(strtoupper($textos->id("MODULO_ACTUAL")), "/".$modulo->url, "subrayado")." :: ". $curso->nombre;
        
        $listaSeguidores		= HTML::frase($textos->id("SIN_SEGUIDORES"), "margenInferior");
        $consulta_seguidores	= $sql->seleccionar( array("cursos_seguidos"), array("id", "id_usuario"), "id_curso = '$curso->id'", "");
		if ($sql->filasDevueltas) {
			
			$lista			= array();
			$seguidor_actual	= false;
			while ($seguidor = $sql->filaEnObjeto($consulta_seguidores)) {
				
				$usuario_seguidor   = new Usuario($seguidor->id_usuario);

                $item     = HTML::enlace(HTML::imagen($usuario_seguidor->persona->imagenMiniatura, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $usuario_seguidor->url);
                $item    .= HTML::enlace(HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].$usuario_seguidor->persona->idGenero.".png").$usuario_seguidor->persona->nombreCompleto, "negrilla"), $usuario_seguidor->url);
                $item3    = HTML::parrafo(date("D, d M Y h:i:s A", $usuario_seguidor->fechaRegistro), "pequenia cursiva negrilla margenInferior");
        
                if (!empty($usuario_seguidor->persona->ciudadResidencia)) {
                    $item3    .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["iconosBanderas"]."/".strtolower($usuario_seguidor->persona->codigoIsoPais).".png", "miniaturaBanderas")." ".$usuario_seguidor->persona->ciudadResidencia.", ".$usuario_seguidor->persona->paisResidencia);
                    // $item3 .= HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["iconosBanderas"]."/".strtolower($usuario->persona->codigoIsoPais).".png", "miniaturaBanderas");
                }
                $item   .= HTML::contenedor($item3, "fondoUltimos5Gris");//barra del contenedor gris

				if ($seguidor->id_usuario == $sesion_usuarioSesion->id) {
					$seguidor_actual = true;
				}
				$lista[]  = $item;
			}
			$listaSeguidores	= HTML::lista($lista, "listaVertical listaConIconos bordeInferiorLista");
		}

		if (isset($sesion_usuarioSesion)) { 
			if ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $curso->idAutor) {
                                $botones .= HTML::botonEliminarItem($curso->id, $curso->urlBase);
				$botones .= HTML::botonModificarItem($curso->id, $curso->urlBase);				
				$botones  = HTML::contenedor($botones, "botonesInternos", "botonesInternos");
			} elseif ($seguidor_actual == false) {
				$botones .= HTML::contenedor(HTML::botonAjax("chequeo", "SEGUIR_CURSO", "/ajax$curso->urlBase/follow", array("id" => $curso->id)), "derecha margenInferior");
			}  elseif ($seguidor_actual == true) {
				$botones .= HTML::contenedor(HTML::botonAjax("cerrarGrueso", "ABANDONAR_CURSO", "/ajax$curso->urlBase/leave", array("id" => $curso->id)), "derecha margenInferior");
			}	
		}
		
		
		/*** Tinychat ***/
		$parametros = array(
			"room"          => "ablaonline_courses_".$curso->id,
			"colorbk"       => "0x2A2D85",
			"api"           => "none",
			"langdefault"   => "en",
			"join"		=> "auto"
		);

		if (isset($sesion_usuarioSesion)) {
			$parametros["nick"] = $sesion_usuarioSesion->usuario;
            if ($sesion_usuarioSesion->usuario != $curso->usuarioAutor) {
                $parametros["owner"]	= "none";
                $parametros["oper"] 	= "none";
			}
		}

/********* Contenido del Curso ****************************/
        $usuario  = new Usuario();
        $contenidoCurso   = $botones;
        $contenidoCurso  .= HTML::contenedor(HTML::frase(preg_replace("/\%1/", HTML::enlace($curso->autor, HTML::urlInterna("USUARIOS", $curso->usuarioAutor)), $textos->id("CREADO_POR").HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].$usuario->getGenero($curso->idAutor).".png")), " margenInferior"), "justificado margenInferior");
        
        $contenidoCurso  .= HTML::contenedor($curso->descripcion, "justificado negrilla margenInferior");
        $contenidoCurso  .= HTML::contenedor($curso->contenido, "justificado");
        $contenidoCurso  .= HTML::contenedor(HTML::botonesCompartir(), "botonesCompartir");
        $contenido        = HTML::bloque("curso_".$curso->id, $tituloPrincipal, $contenidoCurso, "", "botonesOcultos");



		if ($seguidor_actual == true || $sesion_usuarioSesion->id == $curso->idAutor) {

			$recursos = array(
				 HTML::frase($textos->id("SEGUIDORES"), "letraBlanca")   => $listaSeguidores,
				 HTML::frase($textos->id("VIDEOS"), "letraBlanca")       => Recursos::bloqueVideos("CURSOS", $curso->id, $curso->idAutor),
				 HTML::frase($textos->id("AUDIOS"), "letraBlanca")       => Recursos::bloqueAudios("CURSOS", $curso->id, $curso->idAutor),
				 HTML::frase($textos->id("IMAGENES"), "letraBlanca")     => Recursos::bloqueImagenes("CURSOS", $curso->id, $curso->idAutor),
                                 HTML::frase($textos->id("GALERIAS"), "letraBlanca")     => Recursos::bloqueGalerias("CURSOS", $curso->id, $curso->idAutor),
				 HTML::frase($textos->id("DOCUMENTOS"), "letraBlanca")   => Recursos::bloqueArchivos("CURSOS", $curso->id, $curso->idAutor),
				 HTML::frase($textos->id("FOROS"), "letraBlanca")        => Recursos::bloqueForos("CURSOS", $curso->id, $curso->idAutor)
                                       
			);
                        
                        if(isset($sesion_usuarioSesion) && Perfil::verificarPermisosConsulta("ENLACES") || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0){
                            $recursos[HTML::frase($textos->id("ENLACES"), "letraBlanca")] = Recursos::bloqueEnlaces("CURSOS", $curso->id, $curso->idAutor);
                        }

			//$contenido .= HTML::pestanas("recursosCurso", $recursos);
                        $contenido .= HTML::contenedor(HTML::pestanas2("recursosCurso", $recursos), "pestanasRecursosUsuarios");
//			$contenido .= HTML::contenedor("<span class=\"bloqueTitulo ui-helper-clearfix ui-widget-header ui-corner-top\">".$textos->id("SALON_CLASE")."</span>\n", "encabezadoBloque");
			$contenido .= "";//$chat->printChat();
			$contenido .= "<div class=\"sombraInferior\"></div>\n";
		}
    }
    

} else {
    $nombreBloque = $textos->id("MODULO_ACTUAL");
    $curso      = new Curso();
    $excluidas    = "";
}




/////////// DATOS DE PAGINACION ///////////////////////////////////////////   
    $listaItems   = array();
    $registros    = $configuracion["GENERAL"]["registrosPorPagina"];
    
    if (isset($forma_pagina)) {
    $pagina = $forma_pagina;

    } else {
    $pagina = 1;
   }

    $registroInicial = ($pagina - 1) * $registros;

/////////////////////////////////////////////////////////////////////////////


/**
 *
 * Formulario para adicionar un nuevo elemento
 *
 **/
if ((isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicion($curso->idModulo)) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0)) {
    $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($curso->urlBase, $textos->id("ADICIONAR_CURSO")), "derecha margenInferior");

} else {
    $botonAdicionar = "";
}



/**
 *
 * Boton que carga la ventana modal para realizar la busqueda
 *
 **/
  $buscador = HTML::contenedor(HTML::botonAjax("masGrueso", $textos->id("BUSCAR"), HTML::urlInterna("CURSOS", 0, true, "searchCourses")), "flotanteDerecha");




  $cat = "";
  if(isset($url_funcionalidad) && $url_funcionalidad == "category"){
     $cat = $url_categoria;
   }


/**
*
*Verifico si lo que me estan pidiendo es los blogs que me gustan
*en ese caso llamo al metodo mostrarBlogs que me gustan
*
**/
if($cat != "i_follow"){

    
  $urlModulo = "courses";
  $idModulo  = $curso->idModulo;
  $valPredeterminado = $cat;
  $nombreModulo = "CURSOS";
  $filtroCategoria = Categoria::selectorCategorias($urlModulo, $idModulo, $valPredeterminado, $nombreModulo, $botonAdicionar.$buscador, "si");  




$listaCursos = array();
$fila         = 0;

if ($curso->registros) {


/***** Identificar el tipo de perfil del ususario  ************/
     if(isset($sesion_usuarioSesion)){
       $idTipo  = $sesion_usuarioSesion->idTipo;

       }else{
         $idTipo = 99; 

       }

/***** fin de identificar el tipo de perfil del ususario  ****/



   /**********************Calcular el total de registros activos***************************/     

         $totalRegistrosActivos=0;

         foreach ($curso->listar(0, 0, $excluidas, "",  $idTipo, $curso->idModulo, $cat) as $elemento) {
              
            if($elemento->activo){$totalRegistrosActivos++;}

              }


   /**************************************************************************************/


          $reg = sizeof($curso->listar(0, 0, $excluidas, "", $idTipo, $curso->idModulo, $cat));

   if($reg > 0){


    foreach ($curso->listar($registroInicial, $registros, $excluidas, "", $idTipo, $curso->idModulo, $cat) as $elemento) {
        $fila++;
        $item   = "";
        $celdas = array();

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0)) {
            $botones = "";
            $botones .= HTML::botonEliminarItemAjax($elemento->id, $curso->urlBase);
            $botones .= HTML::botonModificarItemAjax($elemento->id, $curso->urlBase);
            
            $item    .= HTML::contenedor($botones, "botonesLista",  "botonesLista");

            $item .= HTML::parrafo($textos->id("TITULO"), "negrilla");
            $item .= HTML::parrafo(HTML::enlace($elemento->nombre, $elemento->url), "negrilla");

            if ($elemento->activo) {
                $estado = HTML::parrafo($textos->id("ACTIVO"));

            } else {
                $estado = HTML::parrafo($textos->id("INACTIVO"));
            }

            $celdas[0][]  = HTML::parrafo($textos->id("AUTOR"), "negrilla").HTML::parrafo($elemento->autor);
            $celdas[0][]  = HTML::parrafo($textos->id("ESTADO"), "negrilla").HTML::parrafo($estado);
            $celdas[1][]  = HTML::parrafo($textos->id("FECHA_CREACION"), "negrilla").HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaCreacion));
            $celdas[1][]  = HTML::parrafo($textos->id("FECHA_PUBLICACION"), "negrilla").HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaPublicacion));
            $celdas[1][]  = HTML::parrafo($textos->id("FECHA_ACTUALIZACION"), "negrilla").HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaActualizacion));
            $item        .= HTML::tabla(array(), $celdas, "tablaCompleta2");
            $item         = HTML::contenedor($item, "contenedorListaCursos", "contenedorListaCursos".$elemento->id);
            $listaCursos[] = $item;

        } else {

            if ($elemento->activo) {

               if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->id == $elemento->idAutor)) {
                    $botones = "";
                    $botones .= HTML::botonEliminarItemAjax($elemento->id, $curso->urlBase);
                    $botones .= HTML::botonModificarItemAjax($elemento->id, $curso->urlBase);                                
                    $item    .= HTML::contenedor($botones, "botonesLista", "botonesLista");
                }

                $usuario  = new Usuario();
                
                $item    .= HTML::enlace(HTML::imagen($elemento->fotoAutor, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $elemento->url);
                $item    .= HTML::parrafo(HTML::enlace($elemento->nombre, $elemento->url)." ".HTML::frase(preg_replace("/\%1/", HTML::enlace($elemento->autor, HTML::urlInterna("USUARIOS", $elemento->usuarioAutor)), HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].$usuario->getGenero($elemento->idAutor).".png").$textos->id("CREADO_POR2")), "flotanteCentro"));
                $item2    = HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaPublicacion), "pequenia cursiva negrilla margenInferior");
                $item2   .= HTML::parrafo($elemento->descripcion, "margenInferior");
                $item    .= HTML::contenedor($item2, "fondoUltimos5GrisL");//barra del contenedor gris
                $item     = HTML::contenedor($item, "contenedorListaCursos", "contenedorListaCursos".$elemento->id);

                $listaCursos[] = $item;
            }
        }

    }

//////////////////paginacion /////////////////////////////////////////////////////

     $paginacion = Recursos:: mostrarPaginador($totalRegistrosActivos, $registroInicial, $registros, $pagina);

      $listaCursos[]   = $paginacion;


}else{

$listaCursos = array($textos->id("SIN_REGISTROS"));

}


}

$listaCursos  = HTML::lista($listaCursos, "listaVertical listaConIconos bordeSuperiorLista", "botonesOcultos");
$listaCursos  = $filtroCategoria .$listaCursos;

$contenido   .= HTML::bloque("listadoCursos", $nombreBloque, $listaCursos);


}else{//lo que estan pidiendo es los cursos que sigo


    

/**
 *
 * Cargar el select que muestra las categorias pertenecientes a este modulo, a su vez, se le incluyen el boton adicionar y el boton buscador
 * para que devuelva un contenedor con los tres elementos dentro bien organizados
 *
 **/
  $urlModulo = "courses";
  $idModulo  = $curso->idModulo;
  $valPredeterminado = $cat;
  $nombreModulo = "CURSOS";
  $filtroCategoria = Categoria::selectorCategorias($urlModulo, $idModulo, $valPredeterminado, $nombreModulo, $botonAdicionar.$buscador, "si");   

    $contenido     .= HTML::bloque("listadoCursos", $tituloBloque, $filtroCategoria.$curso->cursosQueSigo() );
    


}


Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;

?>
