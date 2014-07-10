<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Anuncio
 * @author      Pablo A. Vélez <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 *
 **/

if (isset($url_ruta)) {
    $contenido = "";
    $anuncio   = new Anuncio($url_ruta);



    if (isset($anuncio->id)) {
        Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: ".$textos->id("MODULO_ACTUAL");
        Plantilla::$etiquetas["DESCRIPCION"]    = $anuncio->titulo;

        $tituloBloque = $textos->id("MAS_ANUNCIOS");
        $excluidas    = array($anuncio->id);
        $botones      = "";

        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
            $botones .= HTML::botonModificarItem($anuncio->id, $anuncio->urlBase);
            $botones .= HTML::botonEliminarItem($anuncio->id, $anuncio->urlBase);
            $botones  = HTML::contenedor($botones, "botonesLista", "botonesLista");
        }

        $contenidoAnuncio  = $botones;
        $contenidoAnuncio .= HTML::imagen($anuncio->imagenPrincipal, "flotanteIzquierda  margenDerecha margenInferior");
        $contenidoAnuncio .= HTML::parrafo($textos->id("FECHA_CREACION").":   ".HTML::frase(date("D, d M Y h:i:s A",$anuncio->fechaCreacion), "regular")."<br><br>", "media cursiva negrilla izquierda margenInferior");
        $contenidoAnuncio .= HTML::parrafo($textos->id("FECHA_INICIO_PUBLICACION").":   ".HTML::frase(date("D, d M Y h:i:s A",$anuncio->fechaInicial), "regular")."<br>", "media cursiva negrilla izquierda margenInferior");
        $contenidoAnuncio .= HTML::parrafo($textos->id("FECHA_FINAL_PUBLICACION").":   ".HTML::frase(date("D, d M Y h:i:s A",$anuncio->fechaFinal), "regular")."<br><br>", "media cursiva negrilla izquierda margenInferior");    
        $contenidoAnuncio .= HTML::contenedor(HTML::frase($textos->id("DESCRIPCION"), "negrilla").": ".$anuncio->descripcion, "justificado");
       
        $contenido         = HTML::bloque("anuncio_".$anuncio->id, $anuncio->titulo, $contenidoAnuncio, "", "botonesOcultos");
        //$contenido        .= HTML::bloque("bloqueComentariosNoticia", $textos->id("COMENTARIOS"), Recursos::bloqueComentarios("NOTICIAS", $anuncio->id, $anuncio->idAutor));
    }

} else {
    $tituloBloque = $textos->id("MODULO_ACTUAL");
    $anuncio      = new Anuncio();
    $excluidas    = array();

}

//$anuncio      = new Anuncio();

/////////// DATOS DE PAGINACION ///////////////////////////////////////////   
    $listaItems   = array();
    $registros    = $configuracion["GENERAL"]["registrosPorPagina"];
    
    if (isset($forma_pagina)) {
    $pagina = $forma_pagina;

    } else {
    $pagina = 1;
   }

    $registroInicial = ($pagina - 1) * $registros;

/////////////////////////////////////////////////////////////////////////////////////////////77



/**
 *
 * Formulario para adicionar un nuevo elemento
 *
 **/
if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
    $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($anuncio->urlBase, $textos->id("ADICIONAR_ANUNCIO")), "derecha margenInferior");

} else {
    $botonAdicionar = "";
}

$listaAnuncios  = array();
$fila           = 0;
$arregloAnuncio = $anuncio->listar(0, 0, $excluidas);

if ($anuncio->registros) {



/***** Identificar el tipo de perfil del ususario  ************/
     if(isset($sesion_usuarioSesion)){
       $idTipo  = $sesion_usuarioSesion->idTipo;

       }else{
         $idTipo = 99; 

       }

/***** fin de identificar el tipo de perfil del ususario  ****/


   /**********************Calcular el total de registros activos***************************/     

         $totalRegistrosActivos = 0;

         foreach ($arregloAnuncio as $elemento) {
              
            if($elemento->activo){$totalRegistrosActivos++;}

              }


   /**************************************************************************************/
       


        $reg = sizeof($arregloAnuncio);
        if($reg > 0){ 

                foreach ($arregloAnuncio as $elemento) {
                    $fila++;
                    $item      = "";
                    $celdas    = array();
                    $imgActivo = "";//imagen que mostrara el icono de seleccionado
                    $fechaActivacion = "";//fecha de activación del banner, solo se muestra si el banner esta activo

                    if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $anuncio->idAutor)) {
                        $botones = "";
                        $botones .= HTML::botonEliminarItem($elemento->id, $anuncio->urlBase);
                        $botones .= HTML::botonModificarItem($elemento->id, $anuncio->urlBase);
                        
                        $item    .= HTML::contenedor($botones, "botonesLista", "botonesLista");

                        $item .= HTML::parrafo($textos->id("TITULO"), "negrilla");
                        $item .= HTML::parrafo(HTML::enlace($elemento->titulo, $elemento->url), "negrilla");
                        
                        
                        if ($elemento->activo) {
                            $estado           = HTML::parrafo($textos->id("ACTIVO"));
                            $imgActivo       .= HTML::contenedor("", "iconoActivo");
                            $fechaActivacion .= HTML::parrafo($textos->id("FECHA_PUBLICACION"), "negrilla").HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaInicial));

                        } else {
                            $estado = HTML::parrafo($textos->id("INACTIVO"));
                        }

                        $celdas[0][]  = HTML::parrafo($textos->id("TITULO").$reg, "negrilla").HTML::parrafo($elemento->titulo);
                        $celdas[0][]  = HTML::parrafo($textos->id("ESTADO"), "negrilla").HTML::parrafo($estado);
                        $celdas[1][]  = HTML::parrafo($textos->id("FECHA_CREACION"), "negrilla").HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaCreacion));
                        $celdas[1][]  = HTML::parrafo($imgActivo);
                        $celdas[1][]  = $fechaActivacion;                      


                        $item        .= HTML::tabla(array(), $celdas, "tablaCompleta2");
                        $listaAnuncios[] = $item;

                    } else {

                        if ($elemento->activo) {
                            $item .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), $elemento->url);
                            $item .= HTML::enlace(HTML::parrafo($elemento->titulo, "negrilla"), $elemento->url);
                        

                            $item2 = HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaPublicacion), "pequenia cursiva negrilla margenInferior");
                            $item2.= HTML::parrafo(substr($elemento->resumen, 0, 60)."...");
                            $item .= HTML::contenedor($item2, "fondoUltimos5GrisB"); //barra del contenedor gris

                            $listaAnuncios[] = $item;
                        
                        }
                    }

                }//fin del foreach

        }else{
            $listaAnuncios[] = $textos->id("NO_HAY_BANNER_REGISTRADOS");

        }
//////////////////paginacion /////////////////////////////////////////////////////

   if ($reg > $registros) {
       $totalPaginas  = ceil($reg / $registros);
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

       

      // $infoPaginacion = HTML::parrafo($textos->id("EXISTEN_VARIOS").$noticia->registros." ".$textos->id("MODULO_ACTUAL").
                        //"<br>You're watching ".($registroInicial + 1)." to ".($registroInicial + $registros), "negrilla");


       
       $infoPaginacion = Recursos::contarPaginacion($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas);
      

      $listaAnuncios[]   = HTML::contenedor($botonPrimera.$botonAnterior.$botonSiguiente.$botonUltima.$infoPaginacion, "centrado");
   }//fin del if de la paginacion


}//fin del if($noticias->registros)

$listaAnuncios  = HTML::lista($listaAnuncios, "listaVertical bordeSuperiorLista", "botonesOcultos");
$listaAnuncios  = $botonAdicionar.$listaAnuncios;
$contenido     .= HTML::bloque("listadoNoticias", $tituloBloque, $listaAnuncios);

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;

?>