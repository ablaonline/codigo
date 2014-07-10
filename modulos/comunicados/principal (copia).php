<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Blogs
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/



if (isset($url_ruta)) {
    $contenido = "";
    $blog   = new Blog($url_ruta);

    if (isset($blog->id)) {
        Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: ".$textos->id("MODULO_ACTUAL");
        Plantilla::$etiquetas["DESCRIPCION"]    = $blog->titulo;

        $tituloBloque = $textos->id("MAS_BLOGS");
        $excluidas    = array($blog->id);
        $botones      = "";

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $blog->idAutor)) {
            $botones .= HTML::botonModificarItem($blog->id, $blog->urlBase);
            $botones .= HTML::botonEliminarItem($blog->id, $blog->urlBase);
            $botones  = HTML::contenedor($botones, "oculto flotanteDerecha margenIzquierda");
        }

        $comentario  = new Comentario();
        $comentarios = $comentario->contar("BLOGS", $blog->id);

        if (!$comentarios) {
            $comentarios = " &nbsp;&nbsp; |  &nbsp;&nbsp;".HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."posted.png", "imgCommPosted").$textos->id("SIN_COMENTARIOS");

        } elseif ($comentarios == 1) {
            $comentarios = " &nbsp;&nbsp; | &nbsp;&nbsp;".HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."postedOn.png", "imgCommPosted").$comentarios." ".strtolower($textos->id("COMENTARIO"));

        } else {
            $comentarios = " &nbsp;&nbsp; | &nbsp;&nbsp;".HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."postedOn.png", "imgCommPosted").$comentarios." ".strtolower($textos->id("COMENTARIOS"));
        }

        //Mostrar el Genero del autor
        $persona =  new Persona($blog->idAutor);

        $contenidoBlog  = $botones;
        $contenidoBlog .= HTML::parrafo(date("D, d M Y h:i:s A", $blog->fechaPublicacion), "pequenia cursiva negrilla derecha");
        $contenidoBlog .= HTML::contenedor($blog->contenido, "contenido justificado");
        $contenidoBlog .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].$persona->idGenero.".png").preg_replace("/\%1/", HTML::enlace($blog->autor, HTML::urlInterna("USUARIOS", $blog->usuarioAutor)), $textos->id("PUBLICADO_POR")).$comentarios, "margenInferior");
        $contenidoBlog .= HTML::contenedor(HTML::botonesCompartir(), "botonesCompartir");
        $contenido      = HTML::bloque("blog_".$blog->id, $blog->titulo, $contenidoBlog, "", "botonesOcultos");
        $contenido     .= HTML::bloque("bloqueComentariosBlog", $textos->id("COMENTARIOS"), Recursos::bloqueComentarios("BLOGS", $blog->id, $blog->idAutor), NULL, NULL, "-IS");

    }

} else {
    $tituloBloque = $textos->id("MODULO_ACTUAL");
    $blog      = new Blog();
    $excluidas    = "";






}


///////////////////////////// DATOS DE PAGINACION ///////////////////////////////////////////   
    $listaItems   = array();
    $registros    = $configuracion["GENERAL"]["registrosPorPagina"];
    
    if (isset($forma_pagina)) {
    $pagina = $forma_pagina;

    } else {
    $pagina = 1;
   }

    $registroInicial = ($pagina - 1) * $registros;

/////////////////////////////////////////////////////////////////////////////////////////////






/**
 *
 * Formulario para adicionar un nuevo elemento
 *
 **/
if (isset($sesion_usuarioSesion)) {
    $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($blog->urlBase, $textos->id("ADICIONAR_BLOG")), "derecha margenInferior");

} else {
    $botonAdicionar = "";
}

$listaBlogs   =  array();
$fila         =  0;

if ($blog->registros) {


/***** Identificar el tipo de perfil del ususario  ************/
  if(isset($sesion_usuarioSesion)){

   $idTipo  = $sesion_usuarioSesion->idTipo;

   }else{

    $idTipo = 99; 

   }

/***** fin de identificar el tipo de perfil del ususario  ****/



   /**********************Calcular el total de registros activos***************************/     

         $totalRegistrosActivos=0;

         foreach ($blog->listar2(0, 0, $excluidas, "", $idTipo) as $elemento) {
              
            if($elemento->activo){$totalRegistrosActivos++;}

              }


   /**************************************************************************************/


        $reg = sizeof($blog->listar2(0, 0, $excluidas, "", $idTipo));



    foreach ($blog->listar2($registroInicial, $registros, $excluidas, "", $idTipo) as $elemento) {
        $fila++;
        $item   = "";
        $celdas = array();

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $elemento->idAutor)) {
            $botones = "";
            $botones .= HTML::botonModificarItem($elemento->id, $blog->urlBase);
            $botones .= HTML::botonEliminarItem($elemento->id, $blog->urlBase);
            $item    .= HTML::contenedor($botones, "oculto flotanteDerecha");

            $item .= HTML::parrafo($textos->id("TITULO"), "negrilla");
            $item .= HTML::parrafo(HTML::enlace($elemento->titulo, $elemento->url), "negrilla");

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
            $listaBlogs[] = $item;

        } else {

               if ($elemento->activo) {
                $comentario  = new Comentario();
                $comentarios = $comentario->contar("BLOGS", $elemento->id);

               if (!$comentarios) {
                  $comentarios = HTML::contenedor(HTML::contenedor(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."posted.png", "imgCommPosted").HTML::contenedor(" 0", "mostrarDivNums"), "mostrarPostedSup").HTML::contenedor(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."awardOff.png", "imgCommPosted").HTML::contenedor(" 0", "mostrarDivNums"), "mostrarPostedInf"), "mostrarPosted");

                  } elseif ($comentarios == 1) {
                  $comentarios = HTML::contenedor(HTML::contenedor(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."postedOn.png", "imgCommPosted").HTML::contenedor(" 1", "mostrarDivNums"), "mostrarPostedSup").HTML::contenedor(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."awardOn.png", "imgCommPosted").HTML::contenedor(" 7", "mostrarDivNums"), "mostrarPostedInf"), "mostrarPosted");

                  } else {
                //$comentarios = " | ".HTML::icono("comentario").$comentarios." ".strtolower($textos->id("COMENTARIOS"));
                  $comentarios = HTML::contenedor(HTML::contenedor(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."postedOn.png", "imgCommPosted").HTML::contenedor($comentarios, "mostrarDivNums"), "mostrarPostedSup").HTML::contenedor(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."awardOn.png", "imgCommPosted").HTML::contenedor("7", "mostrarDivNums"), "mostrarPostedInf"), "mostrarPosted");
                }

                 //seleccionar el genero de una persona 
                 $persona =  new Persona($elemento->idAutor);

                 $item     = HTML::enlace(HTML::imagen($elemento->fotoAutor, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("USUARIOS", $elemento->usuarioAutor));
                 $item    .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].$persona->idGenero.".png").preg_replace("/\%1/", HTML::enlace($elemento->autor, HTML::urlInterna("USUARIOS", $elemento->usuarioAutor)).$comentarios, $textos->id("PUBLICADO_POR")));
            
                // $item    .= HTML::parrafo($persona->idGenero); 
 
                 $item2     = HTML::enlace(HTML::parrafo($elemento->titulo, "negrilla"), $elemento->url);
                 $item2    .= HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaPublicacion), "pequenia cursiva negrilla");
                 $item      .=HTML::contenedor($item2, "fondoUltimos5GrisB"); //barra del contenedor gris

                 $listaBlogs[] = $item;

            }//fin del  SI Blog es activo

        }//fin del SI NO es ni el autor ni el administrador

    }//fin del foreach


//////////////////paginacion /////////////////////////////////////////////////////

   if ($reg > $registros) {
       $totalPaginas  = ceil($blog->registros / $registros);
       $botonPrimera  = $botonUltima = $botonAnterior  = $botonSiguiente = "";

      if ($pagina > 1) {
        $botonPrimera   = HTML::campoOculto("pagina", 1);
        $botonPrimera  .= HTML::boton("primero", $textos->id("PRIMERA_PAGINA"), "directo");
        $botonPrimera   = HTML::forma("", $botonPrimera);
        $botonAnterior  = HTML::campoOculto("pagina", $pagina-1);
        $botonAnterior .= HTML::boton("anterior", $textos->id("PAGINA_ANTERIOR"), "directo");
        $botonAnterior  = HTML::forma("", $botonAnterior);
      }

      if ($pagina < $totalPaginas) {
        $botonSiguiente  = HTML::campoOculto("pagina", $pagina+1);
        $botonSiguiente .= HTML::boton("siguiente", $textos->id("PAGINA_SIGUIENTE"), "directo");
        $botonSiguiente  = HTML::forma("", $botonSiguiente);
        $botonUltima     = HTML::campoOculto("pagina", $totalPaginas);
        $botonUltima    .= HTML::boton("ultimo", $textos->id("ULTIMA_PAGINA"), "directo");
        $botonUltima     = HTML::forma("", $botonUltima);
      }

     $infoPaginacion = Recursos::contarPaginacion($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas);

      $listaBlogs[]   = HTML::contenedor($botonPrimera.$botonAnterior.$botonSiguiente.$botonUltima.$infoPaginacion, "centrado");
   }//fin del if de la paginacion





}

$listaBlogs  = HTML::lista($listaBlogs, "listaVertical listaConIconos bordeSuperiorLista", "botonesOcultos");
$listaBlogs  = $botonAdicionar.$listaBlogs;
$contenido     .= HTML::bloque("listadoBlogs", $tituloBloque, $listaBlogs);

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;

?>