<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Categorias
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/



if (isset($url_ruta)) {
    $contenido = "";
    $categoria   = new Categoria($url_ruta);

    if (isset($categoria->id)) {
        Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: ".$textos->id("MODULO_ACTUAL");
        Plantilla::$etiquetas["DESCRIPCION"]    = $categoria->nombre;

        $tituloBloque = $textos->id("MAS_CATEGORIAS");
        $excluidas    = array($categoria->id);
        $botones      = "";

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $categoria->idAutor)) {
            $botones .= HTML::botonModificarItem($categoria->id, $categoria->urlBase);
            $botones .= HTML::botonEliminarItem($categoria->id, $categoria->urlBase);
            $botones  = HTML::contenedor($botones, "oculto flotanteDerecha margenIzquierda");
        }     

        //Mostrar el Genero del autor
        $persona =  new Persona($categoria->idAutor);

        $contenidoCategoria  = $botones;
        $contenidoCategoria .= HTML::parrafo(date("D, d M Y h:i:s A", $categoria->fechaCreacion), "pequenia cursiva negrilla derecha");
        $contenidoCategoria .= HTML::contenedor($categoria->descripcion, "contenido justificado");
        $contenidoCategoria .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].$persona->idGenero.".png").preg_replace("/\%1/", HTML::enlace($categoria->autor, HTML::urlInterna("USUARIOS", $categoria->usuarioAutor)), $textos->id("PUBLICADO_POR")), "margenInferior");
        $contenidoCategoria .= HTML::contenedor(HTML::botonesCompartir(), "botonesCompartir");

        //$contenidoBlog .= Recursos::cargarMegusta($blog->idModulo, $blog->id, $sesion_usuarioSesion->idTipo);

        $contenido      = HTML::bloque("categoria_".$categoria->id, $categoria->nombre, $contenidoCategoria, "", "botonesOcultos");
       }

} else {
    $tituloBloque = $textos->id("MODULO_ACTUAL");
    $categoria      = new Categoria();
    $excluidas    = "";

}


/*********************** Creo el arreglo que me traera todos los registros ************************/
$listadoDeCategorias = array();
$listadoDeCategorias = $categoria->listar(0, 0, $excluidas, "", $idTipo, $categoria->idModulo);



////////////////////// DATOS DE PAGINACION ////////////////////////////   
    $listaItems   = array();
    $registros    = $configuracion["GENERAL"]["registrosPorPagina"];
    
    if (isset($forma_pagina)) {
    $pagina = $forma_pagina;

    } else {
    $pagina = 1;
   }

    $registroInicial = ($pagina - 1) * $registros;

/////////////////////////////////////////////////////////////////////



/**
 *
 * Formulario para adicionar un nuevo elemento
 *
 **/
    if (isset($sesion_usuarioSesion)) {
        $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($categoria->urlBase, $textos->id("ADICIONAR_CATEGORIA")), "derecha margenInferior");

      } else {
        $botonAdicionar = "";
     }



$listaCategorias   =  array();
$fila         =  0;

if ($categoria->registros) {
/***** Identificar el tipo de perfil del ususario  ************/
     if(isset($sesion_usuarioSesion)){
       $idTipo  = $sesion_usuarioSesion->idTipo;

       }else{
         $idTipo = 99; 

       }

/***** fin de identificar el tipo de perfil del ususario  ****/


/**********************Calcular el total de registros activos***************************/     
        if($listadoDeCategorias != NULL){
                $totalRegistrosActivos=0;
                //Recursos::escribirTxt("tipo: ".$idTipo."___ modulo: ".$categoria->idModulo, 5);   
                foreach ($listadoDeCategorias as $elemento) {  
                    
                    if($elemento->activo){
                        $totalRegistrosActivos++;
                    }
                }
         }

/**************************************************************************************/

     $reg = sizeof($listadoDeCategorias);//cantidad de registros

     if($listadoDeCategorias != NULL){
 
            foreach ($listadoDeCategorias as $elemento) {
                $fila++;
                $item   = "";
                $celdas = array();

                if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $elemento->idAutor)) {
                    $botones = "";
                    $botones .= HTML::botonModificarItem($elemento->id, $categoria->urlBase);
                    $botones .= HTML::botonEliminarItem($elemento->id, $categoria->urlBase);
                    $item    .= HTML::contenedor($botones, "oculto flotanteDerecha");
                    $item .= HTML::parrafo($textos->id("NOMBRE"), "negrilla");
                    $item .= HTML::parrafo(HTML::enlace($elemento->nombre, $elemento->url), "negrilla");

                    if ($elemento->activo) {
                        $estado = HTML::parrafo($textos->id("ACTIVO"));

                    } else {
                        $estado = HTML::parrafo($textos->id("INACTIVO"));
                    }

                    $celdas[0][]  = HTML::parrafo($textos->id("AUTOR"), "negrilla").HTML::parrafo($elemento->autor);
                    $celdas[0][]  = HTML::parrafo($textos->id("ESTADO"), "negrilla").HTML::parrafo($estado);
                    $celdas[1][]  = HTML::parrafo($textos->id("FECHA_CREACION"), "negrilla").HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaCreacion));
                    $item        .= HTML::tabla(array(), $celdas, "tablaCompleta2");
                    $listaCategorias[] = $item;

                } else {

                    if ($elemento->activo) {                   
                        //seleccionar el genero de una persona 
                        $persona =  new Persona($elemento->idAutor);

                        $item     = HTML::enlace(HTML::imagen($elemento->fotoAutor, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("USUARIOS", $elemento->usuarioAutor));
                        $item    .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"].$persona->idGenero.".png").preg_replace("/\%1/", HTML::enlace($elemento->autor, HTML::urlInterna("USUARIOS", $elemento->usuarioAutor)).$comentarios, $textos->id("PUBLICADO_POR")));                                         
                        $item2    = HTML::enlace(HTML::parrafo($elemento->nombre, "negrilla"), $elemento->url);
                        $item2   .= HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fechaCreacion), "pequenia cursiva negrilla");
                        $item    .=HTML::contenedor($item2, "fondoUltimos5GrisB"); //barra del contenedor gris

                        $listaCategorias[] = $item;

                    }//fin del  SI Blog es activo

                }//fin del SI NO es ni el autor ni el administrador

            }//fin del foreach

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

            $infoPaginacion = Recursos::contarPaginacion($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas);

            $listaCategorias[]   = HTML::contenedor($botonPrimera.$botonAnterior.$botonSiguiente.$botonUltima.$infoPaginacion, "centrado");
        }//fin del if de la paginacion

     }//fin del if($listadoDeCategorias != NULL)
      else{
            $listaCategorias = array($textos->id("NO_HAY_MAS_CATEGORIAS"));
            }

  }//fin del if($registros) osea, si hay algun registro
  else{ 
     $contenido .= HTML::bloque("listadoBlogs", $textos->id("MODULO_ACTUAL"), $textos->id("NO_HAY_CATEGORIAS")); 
     }
$listaCategorias  = HTML::lista($listaCategorias, "listaVertical listaConIconos bordeSuperiorLista", "botonesOcultos");
$listaCategorias  = $botonAdicionar.$listaCategorias;
$contenido     .= HTML::bloque("listadoBlogs", $tituloBloque, $listaCategorias);

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;

?>