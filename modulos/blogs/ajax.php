<?php
/**
 * @package     FOLCS
 * @subpackage  Blogs
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 Colombo-Americano Soft.
 * @version     0.2
 **/

global $url_accion, $forma_id, $forma_procesar, $forma_datos;

if (isset($url_accion)) {
    switch ($url_accion) {
        case 'add'              :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    adicionarBlog($datos);
                                    break;
        case 'edit'             :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    modificarBlogInternamente($forma_id, $datos);
                                    break;
        case 'editRegister'     :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    modificarBlogDesdeLista($forma_id, $datos);
                                    break;
        case 'delete'           :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarBlog($forma_id, $confirmado);
                                    break;
        case 'deleteRegister'   :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarBlogDesdeLista($forma_id, $confirmado);
                                    break;

        case 'searchBlogs'      :   $datos = ($forma_procesar) ? $forma_datos : array();
                                    buscarBlogs($forma_datos);
                                    break;                        

    }
}


/**
 *
 * @global type $textos
 * @global type $configuracion
 * @param type $datos 
 */
function adicionarBlog($datos = array()) {
    global $textos,  $configuracion;

    $blog      = new Blog();
    $destino   = '/ajax'.$blog->urlBase.'/add';
    $respuesta = array();

    if (empty($datos)) {        
        
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo]', 50, 255);
        $codigo .= HTML::parrafo($textos->id('CONTENIDO'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[contenido]', 10, 60, '', 'editor');
        $codigo .= HTML::parrafo($textos->id('PALABRAS_CLAVES'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[palabrasClaves]', 50, 255);
        $codigo .= HTML::parrafo($textos->id('SELECCIONE_CATEGORIA'), 'negrilla margenSuperior');
        $codigo .= Categoria::mostrarSelectCategorias($blog->idModulo);//este metodo devuelve el select con las categorias pertenecientes a Blogs
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', true).$textos->id('ACTIVO'), 'margenSuperior');
        $codigo .= Perfil::mostrarChecks('');//metodo que devuelve los checks para escoger los perfiles
        
        $pestana1 = $codigo;
        $pestana2 = Galeria::formularioAdicionarGaleria();
        
        $pestanas = array(
            HTML::frase($textos->id('INFORMACION_BLOG'), 'letraBlanca') => $pestana1,
            HTML::frase($textos->id('AGREGAR_GALERIA'), 'letraBlanca') => $pestana2            
        );
        
        $codigo = HTML::pestanas2('', $pestanas);
        
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), 'botonOk', 'botonOk', 'botonOk').HTML::frase('     '.$textos->id('REGISTRO_AGREGADO'), 'textoExitoso', 'textoExitoso'), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo, 'P', true);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ADICIONAR_BLOG'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['ancho']   = 750;
        $respuesta['alto']    = 600;

    } else {

        $respuesta['error']   = true;
        
        $cantImagenes       = $datos['cantCampoImagenGaleria'];//aqui llega el valor del campo oculto en el formulario que determina cuantas imagenes nuevas se van a ingresar
                
        $erroresImagenes = Galeria::validarImagenesGaleria($cantImagenes);//metodo que se encarga de validar las imagenes que se quieres guardar en la galeria

        if($erroresImagenes != ''){//verifico si hubo imagenes con errores de formato
            $respuesta['mensaje'] = str_replace('%1', $erroresImagenes, $textos->id('ERROR_FORMATO_IMAGEN_GALERIA'));
            
        } elseif (empty($datos['titulo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TITULO');

        } elseif (empty($datos['contenido'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CONTENIDO');

        }elseif ($datos['visibilidad']=='privado' && empty($datos['perfiles'])  ) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_SELECCIONAR_PERFILES');

        } else {
            
            $idBlog = $blog->adicionar($datos);

            if ($idBlog) {
                
            /**************** Creo el nuevo blog que se insertara via ajax ****************/
                $nuevoBlog  = new Blog($idBlog);
                $comentario = new Comentario();
                $item = '';
                $botonModificar = HTML::nuevoBotonModificarItem($nuevoBlog->id, $nuevoBlog->urlBase);
                $botonEliminar  = HTML::nuevoBotonEliminarItem($nuevoBlog->id, $nuevoBlog->urlBase);
                $item          .= HTML::contenedor($botonEliminar.$botonModificar, 'botonesLista', 'botonesLista');

                $contenedorComentarios = $comentario->mostrarComentarios($nuevoBlog->idModulo, $nuevoBlog->id);
                $contenedorMeGusta     = Recursos::mostrarContadorMeGusta($nuevoBlog->idModulo, $nuevoBlog->id);
                $comentarios           = HTML::contenedor($contenedorComentarios.$contenedorMeGusta, 'mostrarPosted');
                //seleccionar el genero de una persona 
                $persona   =  new Persona($nuevoBlog->idAutor);
                $item     .= HTML::enlace(HTML::imagen($nuevoBlog->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('USUARIOS', $nuevoBlog->usuarioAutor));
                $item     .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].$persona->idGenero.'.png').preg_replace('/\%1/', HTML::enlace($nuevoBlog->autor, HTML::urlInterna('USUARIOS', $nuevoBlog->usuarioAutor)).$comentarios, $textos->id('PUBLICADO_POR')));                                
                $item2     = HTML::enlace(HTML::parrafo($nuevoBlog->titulo, 'negrilla'), $nuevoBlog->url, 'verMas');
                $item2    .= '<br/><br/>'.HTML::parrafo(date('D, d M Y h:i:s A', $nuevoBlog->fechaPublicacion), 'pequenia cursiva negrilla');
                $item     .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris
                $contenidoBlog = '<li class = "botonesOcultos" style="border-top: 1px dotted #E0E0E0;">'.HTML::contenedor($item, 'contenedorListaBlogs', 'contenedorListaBlogs'.$nuevoBlog->id).'</li>';


                $respuesta['error']                = false;
                $respuesta['accion']               = 'insertar';
                $respuesta['contenido']            = $contenidoBlog;
                $respuesta['idContenedor']         = '#contenedorListaBlogs'.$idBlog;
                $respuesta['insertarAjax']         = true;
                $respuesta['destino']              = '#listaBlogs';

            } else {
                $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
            }

        }
    }

    Servidor::enviarJSON($respuesta);
} //Fin del metodo de adicionar blogs


/**
 *
 * Metodo que se encarga de modificar la información de un blog desde adentro del blog,
 * es decir viendo su informacion completa haciendo uso de la tecnologia ajax
 * 
 * @global type $textos array-> arreglo que contiene los textos que aparecen dentro del sitio web
 * @global type $sql object -> objeto de la clase SQL que se encarga de la interaccion con la BD
 * @global type $configuracion array -> archivo que contiene parametros de configuracion general de la aplicacion
 * @param type entero $id -> El identificador del blog que va a modificar
 * @param type array $datos ->los datos que se van a modificar
 */
function modificarBlogInternamente($id, $datos = array()) {
    global $textos, $configuracion, $sesion_usuarioSesion, $sql;

    if(!is_numeric($id) || !$sql->existeItem('blogs', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $blog      = new Blog($id);
    $destino   = '/ajax'.$blog->urlBase.'/edit';
    $respuesta = array();


    if (empty($datos)) {
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo]', 50, 255, $blog->titulo);
        $codigo .= HTML::parrafo($textos->id('CONTENIDO'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[contenido]', 10, 60, $blog->contenido, 'editor');
        $codigo .= HTML::parrafo($textos->id('PALABRAS_CLAVES'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[palabrasClaves]', 50, 255, $blog->palabrasClaves);
        $codigo .= HTML::parrafo($textos->id('SELECCIONE_CATEGORIA'), 'negrilla margenSuperior');
        $codigo .= Categoria::mostrarSelectCategorias($blog->idModulo, $blog->idCategoria);//este metodo devuelve el select con las categorias pertenecientes a Blogs
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', $blog->activo).$textos->id('ACTIVO'), 'margenSuperior');
        $codigo .= Perfil::mostrarChecks($id, $blog->idModulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), 'botonOk', 'botonOk', 'botonOk').HTML::frase('     '.$textos->id('REGISTRO_MODIFICADO'), 'textoExitoso', 'textoExitoso'), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
         $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('MODIFICAR_BLOG'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 700;
        $respuesta['alto']    = 600;

    } else {
        $respuesta['error']   = true;

        if (empty($datos['titulo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TITULO');

        } elseif (empty($datos['contenido'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CONTENIDO');

        } elseif ($datos['visibilidad']=='privado' && empty($datos['perfiles'])  ) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_SELECCIONAR_PERFILES');

        }else {

              if ($blog->modificar($datos)) {                  
            /** Creo el blog con su contenido ya modificado y lo muestro nuevamente via ajax  *****/  
                    $blog     = new Blog($id);
                    $botones  = '';
                    $botones .= HTML::nuevoBotonEliminarItemInterno($blog->id, $blog->urlBase);
                    $botones .= HTML::nuevoBotonModificarItemInterno($blog->id, $blog->urlBase);            
                    $botones  = HTML::contenedor($botones, 'botonesInternos', 'botonesInternos');

                    $comentario  = new Comentario();
                    $comentarios = $comentario->contar('BLOGS', $blog->id);
                    $meGusta = Recursos::cargarMegusta($blog->idModulo, $blog->id, $sesion_usuarioSesion->id);

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
                    $persona =  new Persona($blog->idAutor);
                    $contenidoBlog  = $botones;
                    $contenidoBlog .= HTML::parrafo(date('D, d M Y h:i:s A', $blog->fechaPublicacion), 'pequenia cursiva negrilla derecha');
                    $contenidoBlog .= HTML::contenedor($blog->contenido, 'contenido justificado');
                    $contenidoBlog .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].$persona->idGenero.'.png').preg_replace('/\%1/', HTML::enlace($blog->autor, HTML::urlInterna('USUARIOS', $blog->usuarioAutor)), $textos->id('PUBLICADO_POR')).$comentarios, 'margenInferior');
                    $contenidoBlog .= HTML::contenedor(HTML::nuevosBotonesCompartir());
                    $contenidoBlog  = HTML::bloque('blog_'.$blog->id, $blog->titulo, $contenidoBlog, '', 'botonesOcultos');


                    $respuesta['error']                 = false;
                    $respuesta['accion']                = 'insertar';
                    $respuesta['contenido']             = $contenidoBlog;
                    $respuesta['idContenedor']          = '#bloqueComentariosBlog'.$id;                        
                    $respuesta['modificarBlogAjax']     = true; 


              }else{
                $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');

              }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * Metodo que se encarga de modificar la información de un blog directamente desde la lista
 * haciendo uso de la tecnologia ajax
 * 
 * @global type $textos array-> arreglo que contiene los textos que aparecen dentro del sitio web
 * @global type $sql object -> objeto de la clase SQL que se encarga de la interaccion con la BD
 * @global type $configuracion array -> archivo que contiene parametros de configuracion general de la aplicacion
 * @param type entero $id -> El identificador del blog que va a modificar
 * @param type array $datos ->los datos que se van a modificar
 */
function modificarBlogDesdeLista($id, $datos = array()) {
    global $textos, $configuracion, $sql;

    if(!is_numeric($id) || !$sql->existeItem('blogs', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $blog      = new Blog($id);
    $destino   = '/ajax'.$blog->urlBase.'/editRegister';
    $respuesta = array();


    if (empty($datos)) {
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo]', 50, 255, $blog->titulo);
        $codigo .= HTML::parrafo($textos->id('CONTENIDO'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[contenido]', 10, 60, $blog->contenido, 'editor');
        $codigo .= HTML::parrafo($textos->id('PALABRAS_CLAVES'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[palabrasClaves]', 50, 255, $blog->palabrasClaves);
        $codigo .= HTML::parrafo($textos->id('SELECCIONE_CATEGORIA'), 'negrilla margenSuperior');
        $codigo .= Categoria::mostrarSelectCategorias($blog->idModulo, $blog->idCategoria);//este metodo devuelve el select con las categorias pertenecientes a Blogs
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', $blog->activo).$textos->id('ACTIVO'), 'margenSuperior');
        $codigo .= Perfil::mostrarChecks($id, $blog->idModulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), 'botonOk', 'botonOk', 'botonOk').HTML::frase('     '.$textos->id('REGISTRO_MODIFICADO'), 'textoExitoso', 'textoExitoso'), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('MODIFICAR_BLOG'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 700;
        $respuesta['alto']    = 600;

    } else {
        $respuesta['error']   = true;

        if (empty($datos['titulo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TITULO');

        } elseif (empty($datos['contenido'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CONTENIDO');

        } elseif ($datos['visibilidad']=='privado' && empty($datos['perfiles'])  ) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_SELECCIONAR_PERFILES');

        }else {

              if ($blog->modificar($datos)) {                  
           /**************** Armo el contenido del blog ya modificado ****************/
                $nuevoBlog  = new Blog($id);
                $comentario = new Comentario();
                $item = '';
                $botonModificar = HTML::nuevoBotonModificarItem($nuevoBlog->id, $nuevoBlog->urlBase);
                $botonEliminar  = HTML::nuevoBotonEliminarItem($nuevoBlog->id, $nuevoBlog->urlBase);
                $item          .= HTML::contenedor($botonEliminar.$botonModificar, 'botonesLista', 'botonesLista');

                $contenedorComentarios = $comentario->mostrarComentarios($nuevoBlog->idModulo, $nuevoBlog->id);
                $contenedorMeGusta     = Recursos::mostrarContadorMeGusta($nuevoBlog->idModulo, $nuevoBlog->id);
                $comentarios           = HTML::contenedor($contenedorComentarios.$contenedorMeGusta, 'mostrarPosted');
                //seleccionar el genero de una persona 
                $persona   =  new Persona($nuevoBlog->idAutor);
                $item     .= HTML::enlace(HTML::imagen($nuevoBlog->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('USUARIOS', $nuevoBlog->usuarioAutor));
                $item     .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].$persona->idGenero.'.png').preg_replace('/\%1/', HTML::enlace($nuevoBlog->autor, HTML::urlInterna('USUARIOS', $nuevoBlog->usuarioAutor)).$comentarios, $textos->id('PUBLICADO_POR')));                                
                $item2     = HTML::enlace(HTML::parrafo($nuevoBlog->titulo, 'negrilla'), $nuevoBlog->url, 'verMas');
                $item2    .= '<br/><br/>'.HTML::parrafo(date('D, d M Y h:i:s A', $nuevoBlog->fechaPublicacion), 'pequenia cursiva negrilla');
                $item     .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris
                $contenidoBlog = $item;


                 $respuesta['error']              = false;
                 $respuesta['accion']             = 'insertar';
                 $respuesta['contenido']          = $contenidoBlog;
                 $respuesta['idContenedor']       = '#contenedorListaBlogs'.$id;
                 $respuesta['modificarAjaxLista'] = true;

              }else{
                $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');

              }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function eliminarBlog($id, $confirmado) {
    global $textos, $sql;

    if(!is_numeric($id) || !$sql->existeItem('blogs', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $blog      = new Blog($id);
    $destino   = '/ajax'.$blog->urlBase.'/delete';
    $respuesta = array();

    if (!$confirmado) {
        $titulo  = HTML::frase($blog->titulo, 'negrilla');
        $titulo  = preg_replace('/\%1/', $titulo, $textos->id('CONFIRMAR_ELIMINACION'));
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), '', 'botonOk', 'botonOk'), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id('REGISTRO_ELIMINADO'), 'textoExitoso', 'textoExitoso');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ELIMINAR_BLOG'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 380;
        $respuesta['alto']    = 170;
    } else {

        if ($blog->eliminar()) {
            $respuesta['error']                     = false;
            $respuesta['accion']                    = 'insertar';
            $respuesta['idContenedor']              = '#blog_'.$id;
            $respuesta['idContenedorComentarios']   = '#bloqueComentariosBlog'.$id;
            $respuesta['masBlogs']                  = '#masBlogs'.$id;
            $respuesta['eliminarAjaxInterno']       = true;
            

        } else {
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $id = Entero -- identificador del blog que quiero eliminar
 * @param type $confirmado = Booleano -- confirmacion por parte del usuario de que desea eliminar el blog
 */
function eliminarBlogDesdeLista($id, $confirmado) {
    global $textos, $sql;

    if(!is_numeric($id) || !$sql->existeItem('blogs', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $blog       = new Blog($id);
    $destino    = '/ajax'.$blog->urlBase.'/deleteRegister';
    $respuesta  = array();

    if (!$confirmado) {
        $titulo  = HTML::frase($blog->titulo, 'negrilla');
        $titulo  = preg_replace('/\%1/', $titulo, $textos->id('CONFIRMAR_ELIMINACION'));
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), '', 'botonOk', 'botonOk'), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id('REGISTRO_ELIMINADO'), 'textoExitoso', 'textoExitoso');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ELIMINAR_BLOG'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 380;
        $respuesta['alto']    = 170;
    } else {

        if ($blog->eliminar()) {
               $respuesta['error']   = false;
               $respuesta['accion']  = 'insertar';
               $respuesta['idContenedor'] = '#contenedorListaBlogs'.$id;
               $respuesta['eliminarAjaxLista'] = true;

        } else {
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }
    }

    Servidor::enviarJSON($respuesta);
}
  
/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function buscarBlogs($datos) {
    global $textos, $sql, $configuracion, $sesion_usuarioSesion;

    $blog      = new Blog();
    $destino   = '/ajax'.$blog->urlBase.'/searchBlogs';
    $respuesta = array();

    if (empty($datos)) {

        $forma    = HTML::campoOculto('datos[criterio]', 'titulo');
        $forma   .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $forma   .= HTML::parrafo(HTML::campoTexto('datos[patron]', 30, 255).HTML::boton('buscar', $textos->id('BUSCAR')), 'margenSuperior');
        $codigo   = HTML::forma($destino, $forma);
        $codigo   = HTML::contenedor($codigo, 'bloqueBorde');
        $codigo  .= HTML::contenedor('','margenSuperior', 'resultadosBuscarBlogs');

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('BUSCAR_BLOGS'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 500;
        $respuesta['alto']    = 400;

    } else {

     if (!empty($datos['criterio']) && !empty($datos['patron'])) {
/***** Identificar el tipo de perfil del ususario  ************/
            if(isset($sesion_usuarioSesion)){
            $idTipo  = $sesion_usuarioSesion->idTipo;
            }else{
                $idTipo = 99; 
            }
/***** fin de identificar el tipo de perfil del ususario  ****/
           if ($datos['criterio'] == 'titulo') {

                $palabras = explode(' ', htmlspecialchars($datos['patron']));

                foreach ($palabras as $palabra) {
                    $palabrasResaltadas[] =  HTML::frase($palabra, 'resaltado');
                    $palabrasMarcadas[]   =  '%'.$palabra.'%';
                }              
                
            }

           $condicion = "(titulo REGEXP '(".implode("|", $palabras).")' OR palabrasClave REGEXP '(".implode("|", $palabras).")' OR autor REGEXP '(".implode("|", $palabras).")')";

           if(isset($sesion_usuarioSesion)){            
              $condicion .= "OR (titulo REGEXP '(".implode("|", $palabras).")' OR palabrasClave REGEXP '(".implode("|", $palabras).")' OR autor REGEXP '(".implode("|", $palabras).")' )";// OR idUsuario = '$sesion_usuarioSesion->id')";
           }

//////////////Sacar los id's de los blogs que tiene permiso el usuario actual//////////////
            $cond = '';
            if($idTipo != 0){
                $cond .= 'id_perfil = '.$idTipo.' OR id_perfil = 99';
            }

            $permisosBlogs = $sql->seleccionar(array('permisos_blogs'), array('id_item', 'id_perfil'), $cond);
            $permisos = array();

            while($permiso= $sql->filaEnObjeto($permisosBlogs)){
                $permisos[] = $permiso->id_item;

            }

            $consulta = $sql->seleccionar(array('lista_blogs'), array('id', 'titulo', 'palabrasClave', 'fecha', 'autor', 'idUsuario'), $condicion);
             
            if ($sql->filasDevueltas) {
                $listaBlogs = array();
                while ($fila = $sql->filaEnObjeto($consulta)) {
                    if (in_array($fila->id, $permisos) || $fila->idUsuario == $sesion_usuarioSesion->id) {

                        $titulo = str_ireplace($palabras, $palabrasMarcadas, $fila->titulo);
                        $autor = str_ireplace($palabras, $palabrasMarcadas, $fila->autor);

                        $item3 = HTML::parrafo(str_replace('%1', str_ireplace($palabrasMarcadas, $palabrasResaltadas, $autor), $textos->id('CREADO_POR')), 'negrilla');
                        $item3 .= HTML::parrafo(HTML::enlace(str_ireplace($palabrasMarcadas, $palabrasResaltadas, $titulo) . ' ' . ' ' . HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'goButton.png'), HTML::urlInterna('BLOGS', $fila->id)), 'negrilla');
                        $item3 .= HTML::parrafo(str_replace('%1', $fila->fecha, $textos->id('PUBLICADO_EN')), 'negrilla cursiva pequenia');

                        $item = HTML::contenedor($item3, 'fondoBuscadorBlogs'); //barra del contenedor gris
                        $listaBlogs[] = $item;
                    }
                }
            }
            if (sizeof($listaBlogs) == 0) {
                $listaBlogs[] = HTML::frase($textos->id('SIN_REGISTROS'));
            }

            $listaBlogs = HTML::lista($listaBlogs, 'listaVertical listaConIconos bordeSuperiorLista');


            $respuesta['accion']            = 'insertar';
            $respuesta['contenido']         = $listaBlogs;
            $respuesta['destino']           = '#resultadosBuscarBlogs';
            $respuesta['limpiaDestino']     = true;

        } else {
            $respuesta['error']   = true;
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CADENA_BUSQUEDA');
        }

    }

    Servidor::enviarJSON($respuesta);
}

?>