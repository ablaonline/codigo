<?php
/**
 * @package     FOLCS
 * @subpackage  Cursos
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 **/

global $url_accion, $forma_procesar, $forma_id, $forma_datos;

if (isset($url_accion)) {
    switch ($url_accion) {
        case 'add'           :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                 adicionarCurso($datos);
                                 break;
        case 'edit'          :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                 modificarCurso($forma_id, $datos);
                                 break;
        case 'editRegister' :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                 modificarCursoDesdeLista($forma_id, $datos);
                                 break;
        case 'delete'        :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                 eliminarCurso($forma_id, $confirmado);
                                 break;
        case 'deleteRegister' :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                 eliminarCursoDesdeLista($forma_id, $confirmado);
                                  break;
        case 'follow'        :   seguirCurso($forma_id);
                                 break;
        case 'leave'         :   abandonarCurso($forma_id);
                                 break;
        case 'searchCourses' :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                 buscarCursos($forma_datos);
                                 break;
        case 'deleteFollowers' :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                   eliminarSeguidores($forma_datos, $confirmado);
                                   break;
        case "listCoursesFromInput" :   listarCursosDesdeCampo($url_cadena);
                                    break;  
    }
}

/**
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @param type $datos 
 */
function adicionarCurso($datos = array()) {
    global $textos;

    $curso     = new Curso();
    $destino   = '/ajax'.$curso->urlBase.'/add';
    $respuesta = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::parrafo($textos->id('NOMBRE'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[nombre]', 50, 255);
        $codigo .= HTML::parrafo($textos->id('DESCRIPCION'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[descripcion]', 3, 65);
        $codigo .= HTML::parrafo($textos->id('CONTENIDO'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[contenido]', 10, 60, '', 'editor');
        $codigo .= HTML::parrafo($textos->id('SELECCIONE_CATEGORIA'), 'negrilla margenSuperior');
        $codigo .= Categoria::mostrarSelectCategorias($curso->idModulo);//este metodo devuelve el select con las categorias pertenecientes a Blogs
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', true).$textos->id('ACTIVO'), 'margenSuperior');
        $codigo .= Perfil::mostrarChecks('');//metodo que devuelve los checks para escoger los perfiles       
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo, 'P', true);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['nombre']  = $textos->id('ADICIONAR_CURSO');
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ADICIONAR_CURSO'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['ancho']   = 750;
        $respuesta['alto']    = 540;

    } else {
        $respuesta['error']   = true;

        if (empty($datos['nombre'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_NOMBRE');

        } elseif (empty($datos['descripcion'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_DESCRIPCION');

        } elseif (empty($datos['contenido'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CONTENIDO');

        } else {

            if ($curso->adicionar($datos)) {
                $respuesta['error']   = false;
                $respuesta['accion']  = 'recargar';

            } else {
                $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
            }

        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * @global type $textos
 * @param type $id
 * @param type $datos 
 */
function modificarCurso($id, $datos = array()) {
    global $textos, $sql;

    if(!is_numeric($id) || !$sql->existeItem('cursos', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $curso     = new Curso($id);
    $destino   = '/ajax'.$curso->urlBase.'/edit';
    $respuesta = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($textos->id('NOMBRE'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[nombre]', 50, 255, $curso->nombre);
        $codigo .= HTML::parrafo($textos->id('DESCRIPCION'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[descripcion]', 3, 65, $curso->descripcion);
        $codigo .= HTML::parrafo($textos->id('CONTENIDO'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[contenido]', 10, 60, $curso->contenido, 'editor');
        $codigo .= HTML::parrafo($textos->id('SELECCIONE_CATEGORIA'), 'negrilla margenSuperior');
        $codigo .= Categoria::mostrarSelectCategorias($curso->idModulo, $curso->idCategoria);//este metodo devuelve el select con las categorias pertenecientes a Blogs
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', $curso->activo).$textos->id('ACTIVO'), 'margenSuperior');
        $codigo .= Perfil::mostrarChecks($id, $curso->idModulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('MODIFICAR_CURSO'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 700;
        $respuesta['alto']    = 600;

    } else {
        $respuesta['error']   = true;

        if (empty($datos['nombre'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_NOMBRE');

        } elseif (empty($datos['descripcion'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_DESCRIPCION');

        } elseif (empty($datos['contenido'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CONTENIDO');

        } else {

            if ($curso->modificar($datos)) {
                $respuesta['error']   = false;
                $respuesta['accion']  = 'recargar';

            } else {
                $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Funcion que se encarga de mostrar el formulario para modificar un curso via Ajax
 * directamente desde la lista
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @param type $id
 * @param type $datos 
 */
function modificarCursoDesdeLista($id, $datos = array()) {
    global $textos, $configuracion, $sql;

    if(!is_numeric($id) || !$sql->existeItem('cursos', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $curso     = new Curso($id);
    $destino   = '/ajax'.$curso->urlBase.'/editRegister';
    $respuesta = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($textos->id('NOMBRE'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[nombre]', 50, 255, $curso->nombre);
        $codigo .= HTML::parrafo($textos->id('DESCRIPCION'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[descripcion]', 50, 255, $curso->descripcion);
        $codigo .= HTML::parrafo($textos->id('CONTENIDO'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[contenido]', 10, 60, $curso->contenido, 'editor');
        $codigo .= HTML::parrafo($textos->id('SELECCIONE_CATEGORIA'), 'negrilla margenSuperior');
        $codigo .= Categoria::mostrarSelectCategorias($curso->idModulo, $curso->idCategoria);//este metodo devuelve el select con las categorias pertenecientes a Blogs
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', $curso->activo).$textos->id('ACTIVO'), 'margenSuperior');
        $codigo .= Perfil::mostrarChecks($id, $curso->idModulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), 'botonOk', 'botonOk', 'botonOk').HTML::frase('     '.$textos->id('REGISTRO_MODIFICADO'), 'textoExitoso', 'textoExitoso'), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('MODIFICAR_CURSO'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 700;
        $respuesta['alto']    = 600;

    } else {
        $respuesta['error']   = true;

        if (empty($datos['nombre'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_NOMBRE');

        } elseif (empty($datos['descripcion'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_DESCRIPCION');

        } elseif (empty($datos['contenido'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CONTENIDO');

        } elseif ($datos['visibilidad']=='privado' && empty($datos['perfiles'])  ) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_SELECCIONAR_PERFILES');

        } else {

            if ($curso->modificar($datos)) {
       /**************** Armo el contenido del Curso ya modificado y lo devuelvo via Ajax ****************/
                $elemento  = new Curso($id);                
                $item = '';
                
                $botonModificar = HTML::nuevoBotonModificarItem($elemento->id, $elemento->urlBase);
                $botonEliminar  = HTML::nuevoBotonEliminarItem($elemento->id, $elemento->urlBase);
                $item          .= HTML::contenedor($botonEliminar.$botonModificar, 'botonesLista', 'botonesLista');
                
                $persona  = new Persona($elemento->idAutor);
                
                $item    .= HTML::enlace(HTML::imagen($elemento->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('USUARIOS', $elemento->usuarioAutor));
                $item    .= HTML::parrafo(HTML::enlace($elemento->nombre, $elemento->url).' '.HTML::frase(str_replace('%1', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)), HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].$persona->idGenero.'.png').$textos->id('CREADO_POR2')), 'flotanteCentro'));
                $item2    = HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion), 'pequenia cursiva negrilla margenInferior');
                $item2   .= HTML::parrafo($elemento->descripcion, 'margenInferior');
                $item    .= HTML::contenedor($item2, 'fondoUltimos5GrisL');//barra del contenedor gris
                $contenidoCurso = $item;
                                
                $respuesta['error']              = false;
                $respuesta['accion']             = 'insertar';
                $respuesta['contenido']          = $contenidoCurso;
                $respuesta['idContenedor']       = '#contenedorListaCursos'.$id;
                $respuesta['modificarAjaxLista'] = true;           
                

            } else {
                $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function eliminarCurso($id, $confirmado) {
    global $textos, $sql;

    if(!is_numeric($id) || !$sql->existeItem('cursos', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $curso      = new Curso($id);
    $destino   = '/ajax'.$curso->urlBase.'/delete';
    $respuesta = array();

    if (!$confirmado) {
        $nombre  = HTML::frase($curso->nombre, 'negrilla');
        $nombre  = preg_replace('/\%1/', $nombre, $textos->id('CONFIRMAR_ELIMINACION'));
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ELIMINAR_CURSO'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 380;
        $respuesta['alto']    = 180;
    } else {

        if ($curso->eliminar()) {
            $respuesta['error']   = false;
            $respuesta['accion']  = 'recargar';

        } else {
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * @global type $textos
 * @param type $id
 * @param type $confirmado 
 */
function eliminarCursoDesdeLista($id, $confirmado) {
    global $textos, $sql;

    if(!is_numeric($id) || !$sql->existeItem('cursos', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $curso     = new Curso($id);
    $destino   = '/ajax'.$curso->urlBase.'/deleteRegister';
    $respuesta = array();

    if (!$confirmado) {
        $nombre  = HTML::frase($curso->nombre, 'negrilla');
        $nombre  = preg_replace('/\%1/', $nombre, $textos->id('CONFIRMAR_ELIMINACION'));
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), '', 'botonOk'), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id('REGISTRO_ELIMINADO'), 'textoExitoso', 'textoExitoso');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ELIMINAR_CURSO'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 380;
        $respuesta['alto']    = 180;
    } else {

        if ($curso->eliminar()) {
               $respuesta['error']   = false;
               $respuesta['accion']  = 'insertar';
               $respuesta['idContenedor'] = '#contenedorListaCursos'.$id;
               $respuesta['eliminarAjaxLista'] = true;

        } else {
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * @global type $textos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $id 
 */
function seguirCurso($id) {
    global $textos, $sesion_usuarioSesion, $sql;

    if(!is_numeric($id) || !$sql->existeItem('cursos', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $curso     = new Curso($id);
    $respuesta = array();

        if ($curso->seguir()) {
            $respuesta['error']   = false;
            $respuesta['accion']  = 'recargar';

        if(Recursos::recibirNotificacionesAlCorreo($curso->idAutor)){
            $contacto             = new Usuario($curso->idAutor);
            $mensaje              = str_replace('%1', $sesion_usuarioSesion->persona->nombreCompleto, $textos->id('CURSO_SEGUIDO'));
            $mensaje              = str_replace('%2', $curso->nombre, $mensaje);
            Servidor::enviarCorreo($contacto->persona->correo, $mensaje, $mensaje);
        }
            $notificacion         = str_replace('%1', HTML::enlace($sesion_usuarioSesion->usuario, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('CURSO_SEGUIDO'));
            $notificacion         = str_replace('%2', HTML::enlace($curso->nombre, HTML::urlInterna('CURSOS', $curso->id)), $notificacion);
            Servidor::notificar($curso->idAutor, $notificacion, array(), '4');

        } else {
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }

    Servidor::enviarJSON($respuesta);
}

/**
 * @global type $textos
 * @param type $id 
 */
function abandonarCurso($id) {
    global $textos, $sql;

    if(!is_numeric($id) || !$sql->existeItem('cursos', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $curso     = new Curso($id);
    $respuesta = array();

        if ($curso->abandonar()) {
            $respuesta['error']   = false;
            $respuesta['accion']  = 'recargar';

        } else {
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }

    Servidor::enviarJSON($respuesta);
}



/**
 *
 * Función que se encarga de listar los contactos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $cadena 
 */
function listarCursosDesdeCampo($cadena) {
    global $sesion_usuarioSesion, $configuracion;


     if(isset($sesion_usuarioSesion)){
       $idTipo  = $sesion_usuarioSesion->idTipo;

     }else{
         $idTipo = 99;

     }

    $curso = new Curso();    
    $respuesta = array();
    $excluidas = array(0);
    $arregloCursos  = $curso->listar(0, 10, '', " (c.nombre LIKE '%$cadena%' || u.sobrenombre LIKE '%$cadena%')", $idTipo, $curso->idModulo);
    
    if( sizeof($arregloCursos) > 0 ) {
        foreach ($arregloCursos as $fila) {
	   $respuesta1 = array();

	   $iconoGenero = HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].$fila->genero.'.png');
	   $autor = HTML::contenedor($fila->autor.' '.$iconoGenero, 'flotanteDerecha margenSuperiorNegativo');
	   $texto = HTML::imagen($fila->fotoAutor, 'imagenListarContactos').HTML::contenedor($fila->nombre, 'margenIzquierdaTriple margenSuperiorNegativoDoble').$autor;

           $respuesta1['label'] = $texto;
           $respuesta1['value'] = $fila->id;
           $respuesta[] = $respuesta1;
           
        }
    }

    Servidor::enviarJSON($respuesta);
}




/**
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function buscarCursos($datos) {
    global $textos, $sql, $configuracion, $sesion_usuarioSesion;

    $curso     = new Curso();
    $destino   = '/ajax'.$curso->urlBase.'/searchCourses';
    $respuesta = array();

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
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('BUSCAR_CURSOS'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
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

         $condicion = "(c.nombre REGEXP '(".implode("|", $palabras).")' OR c.descripcion REGEXP '(".implode("|", $palabras).")' OR c.contenido REGEXP '(".implode("|", $palabras).")')";

         if(isset($sesion_usuarioSesion)){
            
           $condicion .= "OR (c.nombre REGEXP '(".implode("|", $palabras).")' OR c.descripcion REGEXP '(".implode("|", $palabras).")' OR c.contenido REGEXP '(".implode("|", $palabras).")' OR c.id_usuario = '$sesion_usuarioSesion->id')";
          }
//////////////Sacar los id's de los blogs que tiene permiso el usuario actual//////////////
            $cond = '';
            if($idTipo != 0){
                $cond .= 'id_perfil = '.$idTipo.' OR id_perfil = 99';
            }

            $permisosCursos = $sql->seleccionar(array('permisos_cursos'), array('id_item', 'id_perfil'), $cond);
            $permisos = array();

              while($permiso= $sql->filaEnObjeto($permisosCursos)){
              $permisos[] = $permiso->id_item;

               }
///////////////////////////////////////////////////////////////////////////////////////////                      
            $tablas = array(
                        'c'  => 'cursos'
                            );

            $columnas = array(                               
                        'id'                =>  'c.id',
                        'nombre'            =>  'c.nombre',
                        'descripcion'       =>  'c.descripcion',
                        'contenido'         =>  'c.contenido',
                        'fecha_publicacion' =>  'c.fecha_publicacion',
                        'id_usuario'        =>  'c.id_usuario'
                            );
        
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
                             
                if ($sql->filasDevueltas) {
                   $listaCursos = array();
                   while ($fila = $sql->filaEnObjeto($consulta)) {
                        if(in_array($fila->id, $permisos) || $fila->id_usuario == $sesion_usuarioSesion->id){

                                $autor = $sql->obtenerValor('usuarios', 'sobrenombre', 'id = "'.$fila->id_usuario.'"');                            
                                $nombre = str_ireplace($palabras, $palabrasMarcadas, $fila->nombre);

                                $item3   = HTML::parrafo(str_replace('%1', $autor, $textos->id('CREADO_POR')), 'negrilla');
                                $item3  .= HTML::parrafo(HTML::enlace(str_ireplace($palabrasMarcadas, $palabrasResaltadas, $nombre).' '.' '.HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].'goButton.png'), HTML::urlInterna('CURSOS', $fila->id)), 'negrilla');
                                $item3  .= HTML::parrafo(str_replace('%1', $fila->fecha_publicacion, $textos->id('PUBLICADO_EN')), 'negrilla cursiva pequenia');                  

                                $item    = HTML::contenedor($item3, 'fondoBuscadorNoticias');//barra del contenedor gris
                                $listaCursos[] = $item;     

                          }
                     }
                }
                 if(sizeof($listaCursos) == 0){
                    $listaCursos[] = HTML::frase($textos->id('SIN_REGISTROS'));
                }  

                $listaCursos = HTML::lista($listaCursos, 'listaVertical listaConIconos bordeSuperiorLista');
            
            $respuesta['accion']           = 'insertar';
            $respuesta['contenido']        = $listaCursos;
            $respuesta['destino']          = '#resultadosBuscarNoticias';
            $respuesta['limpiaDestino']    = true;

        } else {
            $respuesta['error']   = true;
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CADENA_BUSQUEDA');
        }

    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Función que se encarga de recibir un arreglo con identificadores de mensajes
 * y de eliminar los mismos de la base de datos de la tabla mensajes
 * @global type $textos
 * @global type $sql
 * @param type $datos
 * @param type $confirmado 
 */
function eliminarSeguidores($datos, $confirmado) {
    global $textos;
    
    $destino   = '/ajax/courses/deleteFollowers';
    $respuesta = array();

    if (!$confirmado) {
        
        $numMensajes = sizeof(explode(',', $datos));
        
        $nombre  = str_replace('%2', $textos->id('SEGUIDORES'), $textos->id('CONFIRMAR_ELIMINACION_CON_CANTIDAD'));
        $nombre  = str_replace('%1', $numMensajes, $nombre);
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('datos', $datos);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), 'botonOk', 'botonOk', 'botonOk'), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id('REGISTRO_ELIMINADO'), 'textoExitoso', 'textoExitoso');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ELIMINAR').' '.$textos->id('SEGUIDORES'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 350;
        $respuesta['alto']    = 130;

    } else {

	$curso = new Curso();
        
        if ( $curso->eliminarSeguidores($datos) ) {
            $respuesta['error']             = false;
            $respuesta['accion']            = 'recargar';
            
        } else {
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }
    }

    Servidor::enviarJSON($respuesta);
}//Fin de la funcion eliminar varios mensajes

?>
