<?php
/**
 * @package     FOLCS
 * @subpackage  Noticias
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2013 Colombo Americano Cali
 * @version     0.1
 **/

if (isset($url_accion)) {
    switch ($url_accion) {
        case 'add'        :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              adicionarNoticia($datos);
                               break;
        case 'addCulturalNew' :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              adicionarNoticiaCultural($datos);
                               break;                           
        case 'edit'       :    ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              modificarNoticia($forma_id, $datos);
                               break;
                           
        case 'editRegister':   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              modificarNoticiaDesdeLista($forma_id, $datos);
                               break;
        case 'delete'     :    ($forma_procesar) ? $confirmado = true : $confirmado = false;
                               eliminarNoticia($forma_id, $confirmado);
                               break;
        case 'deleteRegister' :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                               eliminarNoticiaDesdeLista($forma_id, $confirmado);
                               break;
        case 'searchNews' :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                buscarNoticias($forma_datos);
                                break;
    }
}

/**
 * Función con doble comportamiento. La primera llamada (con el arreglo de datos vacio)
 * muestra el formulario para el ingreso del registro. El destino de este formulario es esta 
 * misma función. Una vez viene desde el formulario con el arreglo datos cargado de valores
 * se encarga de validar la información y llamar al metodo adicionar del objeto.
 * 
 * @global recurso $textos  = objeto global de gestion de los textos de idioma
 * @global recurso $sql     = objeto global de interaccion con la BD
 * @global archivo $archivo_imagen = variable global que contiene la posicion del objeto global $_FILES en la posicion "el nombre del archivo en el form"
 * @global arreglo $configuracion = variable global que contiene la informacion general de configuración
 * @global objeto $sesion_usuarioSesion = variable global que contiene el objeto usuario que representa el usuario que inició la sesión  
 * @param array $datos      = arreglo con la informacion a adicionar
 */
function adicionarNoticia($datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen, $sesion_usuarioSesion;

    $noticia   = new Noticia();
    $destino   = '/ajax'.$noticia->urlBase.'/add';
    $respuesta = array();

    if (empty($datos)) {
        
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo]', 50, 255);
        $codigo .= HTML::parrafo($textos->id('RESUMEN'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[resumen]', 50, 255);
        $codigo .= HTML::parrafo($textos->id('CONTENIDO'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[contenido]', 10, 60, '', 'editor');
        $codigo .= HTML::parrafo($textos->id('IMAGEN'), 'negrilla margenSuperior');
        $codigo .= HTML::campoArchivo('imagen', 50, 255); 
        $codigo .= HTML::parrafo($textos->id('SELECCIONE_CATEGORIA'), 'negrilla margenSuperior');
        $codigo .= Categoria::mostrarSelectCategorias($noticia->idModulo);
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', true).$textos->id('ACTIVO'), 'margenSuperior');       
        $codigo .= Perfil::mostrarChecks('');
                
        $pestana1 = $codigo;
        $pestana2 = Galeria::formularioAdicionarGaleria();
        
        $pestanas = array(
            HTML::frase($textos->id('INFORMACION_NOTICIA'), 'letraBlanca') => $pestana1,
            HTML::frase($textos->id('AGREGAR_GALERIA'), 'letraBlanca') => $pestana2            
        );
        
        $codigo = HTML::pestanas2('', $pestanas);
              
        
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), 'botonOk', 'botonOk', 'botonOk').HTML::frase('     '.$textos->id('REGISTRO_AGREGADO'), 'textoExitoso', 'textoExitoso'), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo, 'P', true);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ADICIONAR_NOTICIA'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['ancho']   = 750;
        $respuesta['alto']    = 600;

    } else {
        $respuesta['error']   = true;
        if(!empty($archivo_imagen['tmp_name'])){
            $validarFormato = Recursos::validarArchivo($archivo_imagen, array('jpg','png','gif', 'jpeg'));
            $area    = getimagesize($archivo_imagen['tmp_name']);
         }
         
        $cantImagenes       = $datos['cantCampoImagenGaleria'];//aqui llega el valor del campo oculto en el formulario que determina cuantas imagenes nuevas se van a ingresar
                
        $erroresImagenes = Galeria::validarImagenesGaleria($cantImagenes);//metodo que se encarga de validar las imagenes que se quieres guardar en la galeria

        if($erroresImagenes != ''){//verifico si hubo imagenes con errores de formato
            $respuesta['mensaje'] = str_replace('%1', $erroresImagenes, $textos->id('ERROR_FORMATO_IMAGEN_GALERIA'));
            
        } else if (empty($datos['titulo'])) {
        $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TITULO');

        } elseif (empty($datos['resumen'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_RESUMEN');

        } elseif (empty($datos['contenido'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CONTENIDO');

        } elseif (empty($archivo_imagen['tmp_name'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_IMAGEN');

        } elseif ($datos['visibilidad'] == 'privado' && empty($datos['perfiles'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_SELECCIONAR_PERFILES');

        } elseif ($validarFormato) {
            $respuesta['mensaje'] = $textos->id('ERROR_FORMATO_IMAGEN_NOTICIA');

        } elseif ($area[0] != $configuracion['DIMENSIONES']['anchoNoticiaNormal'] || $area[1] != $configuracion['DIMENSIONES']['altoNoticiaNormal']) {
                    $respuesta['mensaje'] = $textos->id('ERROR_AREA_IMAGEN_NOTICIA');
        }else {
             
                $idNoticia = $noticia->adicionar($datos);
                if ($idNoticia) { 
                    
                  /******* Armo la nueva noticia ya modificada y la devuelvo via Ajax *******/
                       $item = '';
                       $comentario  = new Comentario();
                       $elemento    = new Noticia($idNoticia);
                       $botonModificar = HTML::nuevoBotonModificarItem($elemento->id, $elemento->urlBase);
                       $botonEliminar  = HTML::nuevoBotonEliminarItem($elemento->id, $elemento->urlBase);
                       $item          .= HTML::contenedor($botonEliminar.$botonModificar, 'botonesLista', 'botonesLista');

                        $contenedorComentarios = $comentario->mostrarComentarios($noticia->idModulo, $elemento->id);
                        $contenedorMeGusta     = Recursos::mostrarContadorMeGusta($noticia->idModulo, $elemento->id);
                        $comentarios           = HTML::contenedor($contenedorComentarios.$contenedorMeGusta, 'mostrarPosted');
                         //seleccionar el genero de una persona 
                        $persona =  new Persona($elemento->idAutor);
                        $item     .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('NOTICIAS', $elemento->id));
                        $item     .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].$persona->idGenero.'.png').str_replace('%1', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)).'On '.HTML::frase(date('D, d M Y', $elemento->fechaPublicacion), 'pequenia cursiva negrilla').$comentarios, $textos->id('PUBLICADO_POR')));
                        $item2     = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                        $item2    .= HTML::parrafo($elemento->resumen, 'pequenia cursiva');
                        $item     .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris
                        $contenidoNoticia = '<li class = "botonesOcultos" style="border-top: 1px dotted #E0E0E0;">'.HTML::contenedor($item, 'contenedorListaNoticias', 'contenedorListaNoticias'.$elemento->id).'</li>';
                  /****************************************************************************/
                    
                        $respuesta['error']                = false;
                        $respuesta['accion']               = 'insertar';
                        $respuesta['contenido']            = $contenidoNoticia;
                        $respuesta['idContenedor']         = '#contenedorListaNoticias'.$idNoticia;
                        $respuesta['insertarAjax']         = true;
                        $respuesta['destino']              = '#listaNoticias';

                } else {
                    $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
                }

        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Función con doble comportamiento. La primera llamada (con el arreglo de datos vacio)
 * muestra el formulario con los datos del registro a ser modificado. El destino de este formulario es esta 
 * misma función, pero una vez viene desde el formulario con el arreglo datos cargado de valores
 * se encarga de validar la información y llamar al metodo modificar del objeto.
 * 
 * @global recurso $textos  = objeto global de gestion de los textos de idioma
 * @global recurso $sql     = objeto global de interaccion con la BD
 * @global archivo $archivo_imagen = variable global que contiene la posicion del objeto global $_FILES en la posicion "el nombre del archivo en el form"
 * @global arreglo $configuracion = variable global que contiene la informacion general de configuración
 * @param int $id           = id del registro a modificar
 * @param array $datos      = arreglo con la informacion a adicionar
 */
function modificarNoticiaDesdeLista($id, $datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    if(!is_numeric($id) || !$sql->existeItem('noticias', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $noticia    = new Noticia($id);
    $destino = '/ajax'.$noticia->urlBase.'/editRegister';
    $idArchivo = '';


    if (empty($datos)) {
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo]', 50, 255, $noticia->titulo);
        $codigo .= HTML::parrafo($textos->id('RESUMEN'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[resumen]', 50, 255, $noticia->resumen);
        $codigo .= HTML::parrafo($textos->id('CONTENIDO'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[contenido]', 10, 60, $noticia->contenido, 'editor');
        $codigo .= HTML::parrafo($textos->id('IMAGEN'), 'negrilla margenSuperior');
        $codigo .= HTML::campoArchivo('imagen', 50, 255);
        $codigo .= HTML::parrafo($textos->id('SELECCIONE_CATEGORIA'), 'negrilla margenSuperior');
        $codigo .= Categoria::mostrarSelectCategorias($noticia->idModulo, $noticia->idCategoria);
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', $noticia->activo).$textos->id('ACTIVO'), 'margenSuperior');
        $codigo .= Perfil::mostrarChecks($id, $noticia->idModulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), 'botonOk', 'botonOk', 'botonOk').HTML::frase('     '.$textos->id('REGISTRO_MODIFICADO'), 'textoExitoso', 'textoExitoso'), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo, 'P', true);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('MODIFICAR_NOTICIA'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 700;
        $respuesta['alto']    = 600;

    } else {
        $respuesta['error']   = true;

        if (empty($datos['titulo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TITULO');

        } elseif (empty($datos["resumen"])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_RESUMEN');

        } elseif (empty($datos['resumen'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_RESUMEN');

        }elseif ($datos['visibilidad'] == 'privado' && empty($datos['perfiles'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_SELECCIONAR_PERFILES');

        } else {

            if (!empty($archivo_imagen['tmp_name'])) {

		$validarFormato = Recursos::validarArchivo($archivo_imagen, array('jpg','png','gif', 'jpeg'));

                $area    = getimagesize($archivo_imagen['tmp_name']);
               
                if ($validarFormato) {
                    $respuesta['mensaje'] = $textos->id('ERROR_FORMATO_IMAGEN_NOTICIA');

                } elseif ($area[0] != $configuracion['DIMENSIONES']['anchoNoticiaNormal'] || $area[1] != $configuracion['DIMENSIONES']['altoNoticiaNormal']) {
                    $respuesta['mensaje'] = $textos->id('ERROR_AREA_IMAGEN_NOTICIA');
                }                      

            }

            if (!isset($respuesta['mensaje'])) {           
		$consulta = $noticia->modificar($datos);
                if ($consulta) {
                    
              /******* Armo la nueva noticia ya modificada y la devuelvo via Ajax *******/
                   $item = '';
                   $comentario  = new Comentario();
                   $elemento    = new Noticia($id);
                   $botonModificar = HTML::nuevoBotonModificarItem($elemento->id, $elemento->urlBase);
                   $botonEliminar  = HTML::nuevoBotonEliminarItem($elemento->id, $elemento->urlBase);
                   $item          .= HTML::contenedor($botonEliminar.$botonModificar, 'botonesLista', 'botonesLista');
                    
                    $contenedorComentarios = $comentario->mostrarComentarios($noticia->idModulo, $elemento->id);
                    $contenedorMeGusta     = Recursos::mostrarContadorMeGusta($noticia->idModulo, $elemento->id);
                    $comentarios           = HTML::contenedor($contenedorComentarios.$contenedorMeGusta, 'mostrarPosted');
                     //seleccionar el genero de una persona 
                    $persona =  new Persona($elemento->idAutor);
                    $item     .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('NOTICIAS', $elemento->id));
                    $item     .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].$persona->idGenero.'.png').preg_replace('/\%1/', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)).'On '.HTML::frase(date('D, d M Y', $elemento->fechaPublicacion), 'pequenia cursiva negrilla').$comentarios, $textos->id('PUBLICADO_POR')));
                    $item2     = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                    $item2    .= HTML::parrafo($elemento->resumen, 'pequenia cursiva');
                    $item     .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris
                    $contenidoNoticia = $item;


                     $respuesta['error']              = false;
                     $respuesta['accion']             = 'insertar';
                     $respuesta['contenido']          = $contenidoNoticia;
                     $respuesta['idContenedor']       = '#contenedorListaNoticias'.$id;
                     $respuesta['modificarAjaxLista'] = true;
                    

                } else {
                    $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
                }
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Función con doble comportamiento. La primera llamada (con el arreglo de datos vacio)
 * muestra el formulario con los datos del registro a ser modificado. El destino de este formulario es esta 
 * misma función, pero una vez viene desde el formulario con el arreglo datos cargado de valores
 * se encarga de validar la información y llamar al metodo modificar del objeto.
 * 
 * @global recurso $textos  = objeto global de gestion de los textos de idioma
 * @global recurso $sql     = objeto global de interaccion con la BD
 * @global archivo $archivo_imagen = variable global que contiene la posicion del objeto global $_FILES en la posicion "el nombre del archivo en el form"
 * @global arreglo $configuracion = variable global que contiene la informacion general de configuración
 * @param int $id           = id del registro a modificar
 * @param array $datos      = arreglo con la informacion a adicionar
 */
function modificarNoticia($id, $datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    if(!is_numeric($id) || !$sql->existeItem('noticias', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }


    $noticia    = new Noticia($id);
    $destino = '/ajax'.$noticia->urlBase.'/edit';
    $idArchivo = '';

    if (empty($datos)) {
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo]', 50, 255, $noticia->titulo);
        $codigo .= HTML::parrafo($textos->id('RESUMEN'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[resumen]', 50, 255, $noticia->resumen);
        $codigo .= HTML::parrafo($textos->id('CONTENIDO'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[contenido]', 10, 60, $noticia->contenido, 'editor');
        $codigo .= HTML::parrafo($textos->id('IMAGEN'), 'negrilla margenSuperior');
        $codigo .= HTML::campoArchivo('imagen', 50, 255);
        $codigo .= HTML::parrafo($textos->id('SELECCIONE_CATEGORIA'), 'negrilla margenSuperior');
        $codigo .= Categoria::mostrarSelectCategorias($noticia->idModulo, $noticia->idCategoria);
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', $noticia->activo).$textos->id('ACTIVO'), 'margenSuperior');
        $codigo .= Perfil::mostrarChecks($id, $noticia->idModulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo, 'P', true);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('MODIFICAR_NOTICIA'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 700;
        $respuesta['alto']    = 600;

    } else {
        $respuesta['error']   = true;

        if (empty($datos['titulo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TITULO');

        } elseif (empty($datos['resumen'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_RESUMEN');

        } elseif (empty($datos['resumen'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_RESUMEN');

        }elseif ($datos['visibilidad'] == 'privado' && empty($datos['perfiles'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_SELECCIONAR_PERFILES');

        } else {

            if (!empty($archivo_imagen['tmp_name'])) {
		$validarFormato = Recursos::validarArchivo($archivo_imagen, array('jpg','png','gif', 'jpeg'));
                $area    = getimagesize($archivo_imagen['tmp_name']);
               
                if ($validarFormato) {
                    $respuesta['mensaje'] = $textos->id('ERROR_FORMATO_IMAGEN_NOTICIA');

                } elseif ($area[0] != $configuracion['DIMENSIONES']['anchoNoticiaNormal'] || $area[1] != $configuracion['DIMENSIONES']['altoNoticiaNormal']) {
                    $respuesta['mensaje'] = $textos->id('ERROR_AREA_IMAGEN_NOTICIA');
                }                      

            }

            if (!isset($respuesta['mensaje'])) {           
		$consulta = $noticia->modificar($datos);
                if ($consulta) {
                    //Recursos::escribirTxt('Aqui estoy..: '.$noticia->idImagen, $noticia->idModulo);
                    $respuesta['error']   = false;
                    $respuesta['accion']  = 'recargar';

                } else {
                    $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
                }
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
*Metodo Para eliminar una noticia desde dentro de la noticia
**/
function eliminarNoticia($id, $confirmado) {
    global $textos, $sql;

    if(!is_numeric($id) || !$sql->existeItem('noticias', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $noticia    = new Noticia($id);
    $destino = '/ajax'.$noticia->urlBase.'/delete';

    if (!$confirmado) {
        $titulo  = HTML::frase($noticia->titulo, 'negrilla');
        $titulo  = str_replace('%1', $titulo, $textos->id('CONFIRMAR_ELIMINACION'));
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ELIMINAR_NOTICIA'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 350;
        $respuesta['alto']    = 150;
    } else {


         if ($noticia->eliminar()) {  
               $respuesta['error']   = false;
               $respuesta['accion']  = 'recargar';
          }else{                                
                 
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
         }

    }

    Servidor::enviarJSON($respuesta);
}

/**
*Metodo Para eliminar una noticia desde la lista general
**/
function eliminarNoticiaDesdeLista($id, $confirmado) {
    global $textos, $sql;

    if(!is_numeric($id) || !$sql->existeItem('noticias', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }
    $noticia    = new Noticia($id);
    $destino = '/ajax'.$noticia->urlBase.'/deleteRegister';

    if (!$confirmado) {
        $titulo  = HTML::frase($noticia->titulo, 'negrilla');
        $titulo  = str_replace('%1', $titulo, $textos->id('CONFIRMAR_ELIMINACION'));
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), '', 'botonOk'), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id('REGISTRO_ELIMINADO'), 'textoExitoso', 'textoExitoso');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ELIMINAR_NOTICIA'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 380;
        $respuesta['alto']    = 150;
    } else {


         if ($noticia->eliminar()) {  
               $respuesta['error']   = false;
               $respuesta['accion']  = 'insertar';
               $respuesta['idContenedor'] = '#contenedorListaNoticias'.$id;
               $respuesta['eliminarAjaxLista'] = true;
          }else{                                
                 
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
         }

    }

    Servidor::enviarJSON($respuesta);
}

/**
*Metodo que carga el formulario para buscar y filtrar Noticiass  por contenido
**/
function buscarNoticias($datos) {
    global $textos, $sql, $configuracion, $sesion_usuarioSesion;

    $noticia = new Noticia();
    $destino = '/ajax'.$noticia->urlBase.'/searchNews';

    if (empty($datos)) {


        $forma2  = HTML::campoOculto('datos[criterio]', 'titulo');
        $forma2 .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $forma2 .= HTML::parrafo(HTML::campoTexto('datos[patron]', 30, 255).HTML::boton('buscar', $textos->id('BUSCAR')), 'margenSuperior');

      
        $codigo1  = HTML::forma($destino, $forma2);
        $codigo   = HTML::contenedor($codigo1, 'bloqueBorde');
        $codigo  .= HTML::contenedor('','margenSuperior', 'resultadosBuscarNoticias');

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('BUSCAR_NOTICIAS'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 530;
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

         $condicion = "(n.titulo REGEXP '(".implode("|", $palabras).")' OR n.resumen REGEXP '(".implode("|", $palabras).")' OR n.contenido REGEXP '(".implode("|", $palabras).")')";

         if(isset($sesion_usuarioSesion)){
            
           $condicion .= "OR (n.titulo REGEXP '(".implode("|", $palabras).")' OR n.resumen REGEXP '(".implode("|", $palabras).")' OR n.contenido REGEXP '(".implode("|", $palabras).")' OR n.id_usuario = '$sesion_usuarioSesion->id')";
          }

//////////////Sacar los id's de los blogs que tiene permiso el usuario actual//////////////
            $cond = "";
            if($idTipo != 0){
                $cond .= "id_perfil = ".$idTipo." OR id_perfil = 99";
            }

            //$sql->depurar = true;
            $permisosNoticias = $sql->seleccionar(array('permisos_noticias'), array('id_item', 'id_perfil'), $cond);
            $permisos = array();

              while($permiso= $sql->filaEnObjeto($permisosNoticias)){
              $permisos[] = $permiso->id_item;

               }
///////////////////////////////////////////////////////////////////////////////////////////           
            
            $tablas = array(
                        'n'  => 'noticias'
                            );

            $columnas = array(                               
                        'id'                =>  'n.id',
                        'titulo'            =>  'n.titulo',
                        'resumen'           =>  'n.resumen',
                        'contenido'         =>  'n.contenido',
                        'fecha_publicacion' =>  'n.fecha_publicacion',
                        'id_usuario'        =>  'n.id_usuario'
                            );

          
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

                                 
            if ($sql->filasDevueltas) {
                   while ($fila = $sql->filaEnObjeto($consulta)) {
                        if(in_array($fila->id, $permisos) || $fila->id_usuario == $sesion_usuarioSesion->id){

                                $titulo = str_ireplace($palabras, $palabrasMarcadas, $fila->titulo);

                                $autor = $sql->obtenerValor('usuarios', 'sobrenombre', 'id = "'.$fila->id_usuario.'"');
                                $item3   = HTML::parrafo(str_replace('%1', $autor, $textos->id('CREADO_POR')), 'negrilla');
                                $item3  .= HTML::parrafo(HTML::enlace(str_ireplace($palabrasMarcadas, $palabrasResaltadas, $titulo).' '.' '.HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].'goButton.png'), HTML::urlInterna('NOTICIAS', $fila->id)), 'negrilla');
                                $item3  .= HTML::parrafo(str_replace('%1', $fila->fecha_publicacion, $textos->id('PUBLICADO_EN')), 'negrilla cursiva pequenia');                  

                                $item    = HTML::contenedor($item3, 'fondoBuscadorNoticias');//barra del contenedor gris
                                $listaNoticias[] = $item;     

                          }
                     }
            }  
            if(sizeof($listaNoticias) == 0){
                $listaNoticias[] = HTML::frase($textos->id('SIN_REGISTROS'));
            } 

            $listaNoticias = HTML::lista($listaNoticias, 'listaVertical listaConIconos bordeSuperiorLista');


            $respuesta['accion']        = 'insertar';
            $respuesta['contenido']     = $listaNoticias;
            $respuesta['destino']       = '#resultadosBuscarNoticias';
            $respuesta['limpiaDestino'] = true;

        } else {
            $respuesta['error']   = true;
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CADENA_BUSQUEDA');
        }

    }

    Servidor::enviarJSON($respuesta);
}




?>