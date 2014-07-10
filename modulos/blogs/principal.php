<?php
/**
 * @package     FOLCS
 * @subpackage  Blogs
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 Colombo-Americano Soft.
 * @version     0.2
 **/
global $url_ruta, $sql, $configuracion, $textos, $modulo, $sesion_usuarioSesion, $forma_pagina, $url_funcionalidad, $url_categoria;
$contenido = '';

if (isset($url_ruta)) {
    
    $blog   = new Blog($url_ruta);
    

    if (isset($blog->id)) {
        Plantilla::$etiquetas['TITULO_PAGINA'] .= ' :: '.$textos->id('MODULO_ACTUAL').RECURSOS::obtenerNavegador($_SERVER['HTTP_USER_AGENT']);
        Plantilla::$etiquetas['DESCRIPCION']    = $blog->titulo;

        $tituloBloque = $textos->id('MAS_BLOGS');
        $excluidas    = array($blog->id);
        $botones      = '';
        $tituloPrincipal = HTML::enlace(strtoupper($textos->id('MODULO_ACTUAL')), '/'.$modulo->url, 'subrayado').' :: '. $blog->titulo;

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $blog->idAutor)) {
            $botones .= HTML::botonEliminarItem($blog->id, $blog->urlBase);
            $botones .= HTML::botonModificarItem($blog->id, $blog->urlBase);            
            $botones  = HTML::contenedor($botones, 'botonesInternos', 'botonesInternos');
        }

        $comentario  = new Comentario();
        $comentarios = $comentario->contar('BLOGS', $blog->id);

            if (isset($sesion_usuarioSesion)){
            $meGusta = Recursos::cargarMegusta($blog->idModulo, $blog->id, $sesion_usuarioSesion->id);

            }else{
            $meGusta = Recursos::mostrarMegusta($blog->idModulo, $blog->id);//este no permite votar, solo muestra y indica que se debe iniciar sesion

            }

        if (!$comentarios) {
            $comentarios  = ' &nbsp;&nbsp; |  &nbsp;&nbsp;'.HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].'posted.png', 'imgCommPosted').$textos->id('SIN_COMENTARIOS');
            $comentarios .= HTML::contenedor($meGusta, 'meGusta', 'meGusta');
        } elseif ($comentarios == 1) {
            $comentarios = ' &nbsp;&nbsp; | &nbsp;&nbsp;'.HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].'postedOn.png', 'imgCommPosted').$comentarios.' '.strtolower($textos->id('COMENTARIO'));
            $comentarios .= HTML::contenedor($meGusta, 'meGusta', 'meGusta');
        } else {
            $comentarios = ' &nbsp;&nbsp; | &nbsp;&nbsp;'.HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].'postedOn.png', 'imgCommPosted').$comentarios.' '.strtolower($textos->id('COMENTARIOS'));
            $comentarios .= HTML::contenedor($meGusta, 'meGusta', 'meGusta');
        }

        //Mostrar el Genero del autor
        $usuario =  new Usuario();

        $contenidoBlog  = $botones;
        $contenidoBlog .= HTML::parrafo(date('D, d M Y h:i:s A', $blog->fechaPublicacion), 'pequenia cursiva negrilla derecha');
        $contenidoBlog .= HTML::contenedor($blog->contenido, 'contenido justificado');
        $contenidoBlog .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].$usuario->getGenero($blog->idAutor).'.png').preg_replace('/\%1/', HTML::enlace($blog->autor, HTML::urlInterna('USUARIOS', $blog->usuarioAutor)), $textos->id('PUBLICADO_POR')).$comentarios, 'margenInferior');
        $contenidoBlog .= HTML::contenedor(HTML::botonesCompartir(), 'botonesCompartir');

        //$contenidoBlog .= Recursos::cargarMegusta($blog->idModulo, $blog->id, $sesion_usuarioSesion->idTipo);


/**************************************************    MAS BLOGS DE ESTE USUARIO    ******************************************/
/**
 * Capturar el tipo de usuario que tiene el usuario actual
 **/
     if(isset($sesion_usuarioSesion)){
         $idTipo = $sesion_usuarioSesion->idTipo;
       }else{
         $idTipo = 99; 
       }
        $acordeon   = '';//array($blog->id);
        $arregloBlogs = $blog->listarMasBlogs(0, 5, '' , '', $idTipo, $blog->idModulo, '', $blog->idAutor, $url_ruta);
        $listaMasBlogs = array($textos->id('MAS_BLOGS_DE_ESTE_USUARIO'));
        $listaBlogs = array();
        

        if(sizeof($arregloBlogs) > 0){

              foreach ($arregloBlogs as $elemento) {
                  $item   = '';
             
                    if ($elemento->activo) {
                        $comentario  = new Comentario();
                    
                        $contenedorComentarios = $comentario->mostrarComentarios($elemento->idModulo, $elemento->id);
                        $contenedorMeGusta     = Recursos::mostrarContadorMeGusta($elemento->idModulo, $elemento->id);
                        $comentarios           = HTML::contenedor($contenedorComentarios.$contenedorMeGusta, 'mostrarPosted');
                        //seleccionar el genero de una persona 
                        $usuario   =  new Usuario();
                        $item      = HTML::enlace(HTML::imagen($elemento->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $elemento->url);
                        $item     .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].$usuario->getGenero($elemento->idAutor).'.png').preg_replace('/\%1/', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)).$comentarios, $textos->id('PUBLICADO_POR')));
                        $item2     = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                        $item2    .= HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion), 'pequenia cursiva negrilla');
                        $item     .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris
                        $item      = HTML::contenedor($item, 'listadoBlogs');
                        $listaBlogs[] = $item;

                      }//fin del  SI Blog es activo

                }//fin del foreach

            //print_r($listaBlogs);
            $masBlogsUsuario = HTML::acordeonLargo2($listaMasBlogs, $listaBlogs, 'masBlogs'.$blog->id, '');
            
       }//fin del if      

   
/****************************************************************************************************************************/


        $contenido      = HTML::bloque('blog_'.$blog->id, $tituloPrincipal, $contenidoBlog, '', 'botonesOcultos');
        $contenido     .= Galeria::cargarGaleria($blog->idModulo, $blog->id);
        $contenido     .= $masBlogsUsuario;
        $contenido     .= HTML::bloque('bloqueComentariosBlog'.$blog->id, $textos->id('COMENTARIOS'), Recursos::bloqueComentarios('BLOGS', $blog->id, $blog->idAutor), NULL, NULL, '-IS');

    }

} else {
    $tituloBloque = $textos->id('MODULO_ACTUAL');
    $blog      = new Blog();
    $excluidas    = '';
    $url_ruta = '';


/**
 * Datos para la paginacion
 **/
    $registros    = $configuracion['GENERAL']['registrosPorPagina'];
    
        if (isset($forma_pagina)) {
        $pagina = $forma_pagina;

        } else {
        $pagina = 1;
        }
     $registroInicial = ($pagina - 1) * $registros;
/////////////////////////////////////////////////////////////////////


/**
 * Capturar el tipo de usuario que tiene el usuario actual
 **/
     if(isset($sesion_usuarioSesion) ){
         $idTipo = $sesion_usuarioSesion->idTipo;
       }else{
         $idTipo = 99; 
       }


/**
 * Formulario para adicionar un nuevo elemento
 **/
    if ((isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicion($blog->idModulo)) || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
        $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($blog->urlBase, $textos->id('ADICIONAR_BLOG')), 'derecha margenInferior');

      } else {
        $botonAdicionar = '';
     }


/**
 * Boton que carga la ventana modal para realizar la busqueda
 **/
  $nuevosRegistros = HTML::contenedor('', 'nuevosRegistros', 'nuevosRegistros');//Contenedor donde se guardaran los nevos registros que se bayan insertando via ajax
  $buscador =  HTML::contenedor(HTML::botonAjax('masGrueso', $textos->id('BUSCAR'), HTML::urlInterna('BLOGS', 0, true, 'searchBlogs')), 'flotanteDerecha').$nuevosRegistros;



/**
 * Capturar la categoria por la cual se van a filtrar los items
 **/
  $cat = '';
  if(isset($url_funcionalidad) && $url_funcionalidad == 'category'){
     $cat = $url_categoria;
   }


/**
*Verifico si lo que me estan pidiendo es los blogs que me gustan
*en ese caso llamo al metodo mostrarBlogs que me gustan
**/

if($cat != 'i_like'){





/**
 * Cargar el select que muestra las categorias pertenecientes a este modulo, a su vez, se le incluyen el boton adicionar y el boton buscador
 * para que devuelva un contenedor con los tres elementos dentro bien organizados
 **/
  $urlModulo = 'blogs';
  $idModulo  = $blog->idModulo;
  $valPredeterminado = $cat;
  $nombreModulo = 'BLOGS';
  $filtroCategoria = Categoria::selectorCategorias($urlModulo, $idModulo, $valPredeterminado, $nombreModulo, $botonAdicionar.$buscador, 'si');  



/**
 * Declaracion del arreglo lista... y carga de datos en él
 **/
$listaBlogs   =  array();
$arregloBlogs = $blog->listar($registroInicial, $registros, $excluidas, '', $idTipo, $blog->idModulo, $cat);



if ($blog->registros) {

/**
 * Calcular el total de registros Activos, teniendo en cuenta el perfil del usuario
 * y los registros permitidos segun el perfil
 **/
         $totalRegistrosActivos=0;
         $arregloBlog = $blog->listar(0, 0, $excluidas, '', $idTipo, $blog->idModulo, $cat);
         $reg = sizeof($arregloBlog);
         
         if($reg > 0){
            foreach ($arregloBlog as $elemento) {              
                  if($elemento->activo){
                       $totalRegistrosActivos++;
                   }
            }

         }
   

   if($reg > 0){
            foreach ($arregloBlogs as $elemento) {
                $item   = '';
                $celdas = array();

                if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0)) {
                    $botones = '';
                    $botones .= HTML::botonEliminarItemAjax($elemento->id, $blog->urlBase);
                    $botones .= HTML::botonModificarItemAjax($elemento->id, $blog->urlBase);                    
                    $item    .= HTML::contenedor($botones, 'botonesLista', 'botonesLista');

                    $item .= HTML::parrafo($textos->id('TITULO'), 'negrilla');
                    $item .= HTML::parrafo(HTML::enlace($elemento->titulo, $elemento->url), 'negrilla');

                    if ($elemento->activo) {
                        $estado = HTML::parrafo($textos->id('ACTIVO'));

                    } else {
                        $estado = HTML::parrafo($textos->id('INACTIVO'));
                    }

                    $celdas[0][]  = HTML::parrafo($textos->id('AUTOR'), 'negrilla').HTML::parrafo($elemento->autor);
                    $celdas[0][]  = HTML::parrafo($textos->id('ESTADO'), 'negrilla').HTML::parrafo($estado);
                    $celdas[1][]  = HTML::parrafo($textos->id('FECHA_CREACION'), 'negrilla').HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaCreacion));
                    $celdas[1][]  = HTML::parrafo($textos->id('FECHA_PUBLICACION'), 'negrilla').HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion));
                    $celdas[1][]  = HTML::parrafo($textos->id('FECHA_ACTUALIZACION'), 'negrilla').HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaActualizacion));
                    $item        .= HTML::tabla(array(), $celdas, 'tablaCompleta2 ');
                    $item         = HTML::contenedor($item, 'contenedorListaBlogs', 'contenedorListaBlogs'.$elemento->id);
                    $listaBlogs[] = $item;

                } else {

                    if ($elemento->activo) {
                        if(isset($url_ruta) && $elemento->id != $url_ruta){
                        
                            $comentario  = new Comentario();

                              if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->id == $elemento->idAutor)) {
                                    $botones = '';
                                    $botones .= HTML::botonEliminarItemAjax($elemento->id, $blog->urlBase);
                                    $botones .= HTML::botonModificarItemAjax($elemento->id, $blog->urlBase);                                
                                    $item    .= HTML::contenedor($botones, 'botonesLista', 'botonesLista');
                               }
                            $contenedorComentarios = $comentario->mostrarComentarios($elemento->idModulo, $elemento->id);
                            $contenedorMeGusta     = Recursos::mostrarContadorMeGusta($elemento->idModulo, $elemento->id);
                            $comentarios           = HTML::contenedor($contenedorComentarios.$contenedorMeGusta, 'mostrarPosted');

                            //seleccionar el genero de una persona 
                            $usuario  =  new Usuario();
                            $item    .= HTML::enlace(HTML::imagen($elemento->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $elemento->url);
                            $item    .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].$usuario->getGenero($elemento->idAutor).'.png').preg_replace('/\%1/', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)).$comentarios, $textos->id('PUBLICADO_POR')));                                
                            $item2     = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url, 'verMas');
                            $item2    .= '<br/><br/>'.HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion), 'pequenia cursiva negrilla');
                            $item     .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris
                            $item      = HTML::contenedor($item, 'contenedorListaBlogs', 'contenedorListaBlogs'.$elemento->id);
                            $listaBlogs[] = $item;
                        }

                    }//fin del  SI Blog es activo

                }//fin del SI NO es ni el autor ni el administrador

            }//fin del foreach


        //////////////////paginacion /////////////////////////////////////////////////////
            $paginacion = Recursos:: mostrarPaginador($totalRegistrosActivos, $registroInicial, $registros, $pagina);

            $listaBlogs[]   = $paginacion;
        }else{

             $listaBlogs= array(HTML::parrafo( $textos->id('SIN_REGISTROS'), 'sinRegistros', 'sinRegistros')) ;
        }

   }//fin del if($blog->registros)

$listaBlogs  = HTML::lista($listaBlogs, 'listaVertical listaConIconos bordeSuperiorLista', 'botonesOcultos', 'listaBlogs');
$listaBlogs  = $filtroCategoria.HTML::contenedor($listaBlogs, 'listaBlog', 'listaBlog');
$contenido  .= HTML::bloque('listadoBlogs', $tituloBloque, $listaBlogs);


}else{//lo que estan pidiendo es los blogs que me gustan
 

/**
 * Cargar el select que muestra las categorias pertenecientes a este modulo, a su vez, se le incluyen el boton adicionar y el boton buscador
 * para que devuelva un contenedor con los tres elementos dentro bien organizados
 **/
  $urlModulo = 'blogs';
  $idModulo  = $blog->idModulo;
  $valPredeterminado = $cat;
  $nombreModulo = 'BLOGS';
  $filtroCategoria = Categoria::selectorCategorias($urlModulo, $idModulo, $valPredeterminado, $nombreModulo, $botonAdicionar.$buscador, 'si');  

  $contenido     .= HTML::bloque('listadoBlogs', $tituloBloque, $filtroCategoria.$blog->blogsQueMeGustan() );

}


}
Plantilla::$etiquetas['BLOQUE_IZQUIERDO'] = $contenido;

?>