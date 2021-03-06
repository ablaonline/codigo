<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Foros
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

$contenido = "";
global $configuracion, $textos, $modulo, $url_ruta, $sesion_usuarioSesion, $sql;

if (isset($url_ruta) && $sql->existeItem('foros', 'id', $url_ruta)) {
    $contenido = "";
    $foro      = new Foro($url_ruta);

    if (isset($foro->id)) {
        Plantilla::$etiquetas["TITULO_PAGINA"] .= " :: ".$textos->id("MODULO_ACTUAL");
        Plantilla::$etiquetas["DESCRIPCION"]    = $foro->titulo;

        $botones      = "";

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $foro->idAutor)) {
            $destino = "/ajax/forums/edit";
            $datoForo = array("idModulo" => $foro->idModuloActual, "idRegistro" => $foro->idRegistro, "id" => $foro->id);
            $botones .= HTML::botonEliminarItem($foro->id, $foro->urlBase);
            $botones .= HTML::contenedor(HTML::botonAjax("lapiz", $textos->id("MODIFICAR"), $destino, $datoForo), "contenedorBotonesLista", "contenedorBotonesLista");
            $botones  = HTML::contenedor($botones, "botonesInternos", "botonesInternos");
         }
         

                
         
         
         

        $bloqueForo     = $botones;
        $listaForos     = array();
        

            $autor       = HTML::parrafo(preg_replace("/\%1/", HTML::enlace($foro->autor, HTML::urlInterna("USUARIOS", $foro->usuarioAutor)), $textos->id("PUBLICADO_POR")));
            $imagenAutor = HTML::enlace(HTML::imagen($foro->fotoAutor, "miniaturaForos"), HTML::urlInterna("USUARIOS", $foro->usuarioAutor));
            $titulo      = HTML::parrafo($foro->titulo, "tituloForos margenInferior");
            $descripcion = HTML::parrafo($foro->descripcion, "margenInferior descripcionForos");
            $fecha       = HTML::parrafo(date("D, d M Y h:i:s A", $foro->fecha), "pequenia cursiva negrilla izquierda");

            $header     = HTML::contenedor($fecha.$botones, "headerForo");
            $subHeader  = HTML::contenedor($imagenAutor.$autor, "subHeaderForo");
            $centro     = HTML::contenedor($titulo.$descripcion, "centerForo");
            $footer     = HTML::contenedor(HTML::frase($textos->id("CATEGORIA"), "negrilla").": ".$foro->categoria, "footerForo");

            $bloqueForo  = HTML::contenedor($header.$subHeader.$centro.$footer, "cuadroForo", "cuadroForo");

         if (isset($sesion_usuarioSesion)) {
            $moduloActual = new Modulo("FOROS");
            $campo        = HTML::campoOculto("idForo", $foro->id); 
            $boton        = HTML::boton("comentario", $textos->id("RESPONDER"), "flotanteDerecha margenInferior");
            $bloqueForo  .= HTML::forma(HTML::urlInterna("FOROS", "", true, "replyTopic"), $campo.$boton);
            //boton de pruebas
            //$bloqueForo  .= HTML::boton("", "prueba", "", "", "botonProbando");
            $bloqueForo  .= HTML::contenedor("", "");         

          } else {
            $bloqueForo .= HTML::parrafo($textos->id("ERROR_RESPUESTA_FORO_SESION"), "margenInferior");
          }

            
        if ($foro->mensajes) {        

            foreach ($foro->listarMensajes() as $mensaje) {

                if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $mensaje->idAutor || $sesion_usuarioSesion->id == $foro->idAutor)) {
                    $botonEliminar = HTML::botonAjax("basura", "ELIMINAR", HTML::urlInterna("FOROS", "", true, "deleteReply"), array("id" => $mensaje->id));
                    $botonEliminar = HTML::contenedor($botonEliminar, "botonesForo");
                }
               
              //  $contenidoForo .= HTML::enlace(HTML::imagen($mensaje->fotoAutor), HTML::urlInterna("USUARIOS", $mensaje->usuarioAutor));
              //  $contenidoForo .= HTML::parrafo(date("D, d M Y h:i:s A", $mensaje->fecha), "pequenia cursiva negrilla derecha");
                //$contenidoForo .= HTML::parrafo($mensaje->contenido, "margenInferior");
               // $contenidoForo .= HTML::parrafo(preg_replace("/\%1/", HTML::enlace($mensaje->autor, HTML::urlInterna("USUARIOS", $mensaje->usuarioAutor)), $textos->id("PUBLICADO_POR")));
               // $contenidoForo  = HTML::contenedor($contenidoForo, "cuadroForo");

                $autor       = HTML::parrafo(preg_replace("/\%1/", HTML::enlace($mensaje->autor, HTML::urlInterna("USUARIOS", $mensaje->usuarioAutor)), $textos->id("PUBLICADO_POR")));
                $imagenAutor = HTML::enlace(HTML::imagen($mensaje->fotoAutor, "miniaturaForos"), HTML::urlInterna("USUARIOS", $mensaje->usuarioAutor));
                //$titulo      = HTML::parrafo($mensaje->titulo, "tituloForos margenInferior");
                $descripcion = HTML::parrafo($mensaje->contenido, "margenInferior");
                $fecha       = HTML::parrafo(date("D, d M Y h:i:s A", $mensaje->fecha), "pequenia cursiva negrilla derecha");

                $header     = HTML::contenedor($fecha, "headerMensajes");
                $subHeader  = HTML::contenedor($imagenAutor.$autor.$botonEliminar, "subHeaderMensajes");
                $centro     = HTML::contenedor($descripcion, "centerMensajes");
                //$footer     = HTML::contenedor(HTML::frase($textos->id("CATEGORIA"), "negrilla").": ".$foro->categoria, "footerForo");

                $contenidoForo  = HTML::contenedor($header.$subHeader.$centro, "cuadroForo", "cuadroForo".$mensaje->id);

                $listaForos[]  .= $contenidoForo;
            }
        }
        $nuevosRegistros   = HTML::contenedor("", "nuevosMensajes", "nuevosMensajes");
        $bloqueRespuestas  = $nuevosRegistros.HTML::lista($listaForos, "listaVertical listaConIconos bordeSuperiorLista", "botonesOcultos");
        
        $titulo = "";
        if($foro->idModuloActual && $foro->idRegistro){
            
            $nombreModulo = $sql->obtenerValor("modulos", "nombre", "id = ".$foro->idModuloActual);
            $modulo1      = new Modulo($nombreModulo);  
            $sql->depurar = true;            
            $tituloItem   = $sql->obtenerValor($modulo1->tabla, "nombre", "id = ".$foro->idRegistro);

            $titulo = HTML::enlace(strtoupper($nombreModulo), "/".$modulo1->url, "subrayado")." :: ".HTML::enlace(strtoupper($tituloItem), "/".$modulo1->url."/".$foro->idRegistro , "subrayado")." :: ". $foro->titulo;
            
        }else{
            $titulo = HTML::enlace(strtoupper($textos->id("MODULO_ACTUAL")), "/".$modulo->url, "subrayado")." :: ". $foro->titulo;
        }
        
        $contenido   = HTML::bloque("foro_".$foro->id, $titulo, $bloqueForo);
        $contenido  .= HTML::bloque("respuestasForo_".$foro->id, $textos->id("RESPUESTAS_FORO"), $bloqueRespuestas);

    }
} else {

 $nombreBloque = $textos->id("MODULO_ACTUAL");
 $foro      = new Foro();
 $excluidas    = "";

/////////// DATOS DE PAGINACION ///////////////////////////////////////////   
    $listaItems   = array();
    $registros    = $configuracion["GENERAL"]["registrosPorPaginaTabla"];
    
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
if ((isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicion($foro->idModulo)) || $sesion_usuarioSesion->idTipo == 0) {
    $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($foro->urlBase, $textos->id("ADICIONAR_FORO")), "derecha margenInferior");

} else {
    $botonAdicionar = "";
}


//Recursos::escribirTxt("permiso: ".Recursos::verificarPermisosAdicion($foro->idModulo), 23);

/**
 *
 * Boton que carga la ventana modal para realizar la busqueda
 *
 **/
  $buscador = HTML::contenedor(HTML::botonAjax("masGrueso", $textos->id("BUSCAR"), HTML::urlInterna("FOROS", 0, true, "searchForums")), "flotanteDerecha");




  $cat = "";
  if(isset($url_funcionalidad) && $url_funcionalidad == "category"){
     $cat = $url_categoria;
   }





  $urlModulo = "forums";
  $idModulo  = $foro->idModulo;
  $valPredeterminado = $cat;
  $nombreModulo = "FOROS";
  $filtroCategoria = Categoria::selectorCategorias($urlModulo, $idModulo, $valPredeterminado, $nombreModulo, $botonAdicionar.$buscador, "si");  


$cantidadForos = sizeof($foro->listarForos(0, 0, $excluidas, "", $cat));


$listaForos   = array();
$fila         = 0;
$arregloForos = $foro->listarForos($registroInicial, $registros, $excluidas, "", $cat);


if ($foro->registros) {


$reg = sizeof($arregloForos);


   if($reg > 0){

    /**********************Calcular el total de registros activos***************************/  

         $totalRegistrosActivos = 0;

         foreach ($arregloForos as $elemento) {
              
            if($elemento->activo){$totalRegistrosActivos++;}

          }
   /**************************************************************************************/


    foreach ($arregloForos as $elemento) {

        if($elemento->activo == 1){
            $fila++;
            $item   = "";
            $celdas = array();
            
            $numRespuestas = $foro->contarMensajes($elemento->id);

            
        
                $botones = "";
             if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $elemento->idAutor)) {
                $botones .= "";
                $botones .= HTML::botonModificarItem($elemento->id, $foro->urlBase);
                $botones .= HTML::botonEliminarItem($elemento->id, $foro->urlBase);
                $botones  = HTML::contenedor($botones, "oculto flotanteDerecha");           

             }

                $persona  =  new Persona($elemento->idAutor);       

                $celdas[0]  = HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."foros2.png", "centrado");            
                $celdas[1]  = HTML::parrafo(HTML::enlace($elemento->titulo, $elemento->url), "centrado negrilla");
                $celdas[2]  = HTML::parrafo($elemento->categoria, "centrado");
                $celdas[3]  = HTML::parrafo(date("D, d M Y h:i:s A", $elemento->fecha), "centrado");
                $celdas[4]  = HTML::parrafo(HTML::enlace($elemento->autor, HTML::urlInterna("USUARIOS", $elemento->usuarioAutor)), "centrado negrilla");
                $celdas[5]  = HTML::parrafo($numRespuestas, "centrado negrilla");
            
                
            $arregloCeldas[$fila] = $celdas;
           }//fin si el foro esta activo        

    }//fin del foreach

    $iconoAbla = HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."ablaonline.ico");

    $columnas = array(
                    HTML::parrafo($iconoAbla, "centrado"),
                    HTML::parrafo($textos->id("TITULO"), "centrado"),
                    HTML::parrafo($textos->id("CATEGORIA"), "centrado"),
                    HTML::parrafo($textos->id("FECHA_PUBLICACION"), "centrado"),
                    HTML::parrafo($textos->id("AUTOR"), "centrado"),
                    HTML::parrafo($textos->id("NUM_RESPUESTAS"), "centrado")
                    );


    $estilosColumnas = array("columnaUno", "columnaDos", "columnaTres", "columnaCuatro", "columnaCinco", "columnaSeis");
    $opciones = array("cellpadding" => "3", "border" => "1", "cellspacing" => "1");
    $item        .= HTML::tabla($columnas, $arregloCeldas, "tablaForos", "tablaForos", $estilosColumnas, "", $opciones);
    $listaForos[] = $item;

//////////////////paginacion /////////////////////////////////////////////////////

     $paginacion = Recursos:: mostrarPaginador($cantidadForos, $registroInicial, $registros, $pagina, $totalPaginas);

     $listaForos[]   = $paginacion;


}else{

$listaForos = array($textos->id("SIN_REGISTROS"));

}


}

$listaForos   = HTML::lista($listaForos, "listaVertical listaConIconos bordeSuperiorLista", "botonesOcultos");
$listaForos   = $filtroCategoria .$listaForos;
$contenido   .= HTML::bloque("listadoForos", $nombreBloque, $listaForos);




}

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;

?>