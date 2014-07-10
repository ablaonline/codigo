<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Paginas
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 * */
global $url_accion, $forma_id, $forma_procesar;


if (isset($url_accion)) {
    switch ($url_accion) {
        case 'add' : ($forma_procesar) ? $datos = $forma_datos : $datos = array();
            adicionarPagina($datos);
            break;
        case 'editRegister' : ($forma_procesar) ? $datos = $forma_datos : $datos = array();
            modificarPagina($forma_id, $datos);
            break;
        case 'deleteRegister' : ($forma_procesar) ? $confirmado = true : $confirmado = false;
            eliminarPagina($forma_id, $confirmado);
            break;
        case 'up' : ($forma_procesar) ? $confirmado = true : $confirmado = false;
            subirPagina($forma_id, $confirmado);
            break;
        case 'down' : ($forma_procesar) ? $confirmado = true : $confirmado = false;
            bajarPagina($forma_id, $confirmado);
            break;

        case 'registeredQuantity' : cantidadRegistrados();
            break;
    }
}

/**
 * Metodo para adicionar paginas
 * @global type $textos
 * @global type $sql
 * @param type $datos 
 */
function adicionarPagina($datos = array()) {
    global $textos, $sql;

    $pagina = new Pagina();
    $destino = '/ajax' . $pagina->urlBase . '/add';
    $respuesta = array();

    if (empty($datos)) {
        $codigo = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::parrafo($textos->id('MENU'), 'negrilla margenSuperior');
        $menus = $sql->seleccionar(array('menus'), array('id', 'nombre'), 'id IS NOT NULL', 'id', 'orden ASC');

        while ($fila = $sql->filaEnObjeto($menus)) {
            $ubicacion[$fila->id] = $fila->nombre;
        }

        $codigo .= HTML::listaDesplegable('datos[menu]', $ubicacion);
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo]', 50, 255);
        $codigo .= HTML::parrafo($textos->id('CONTENIDO'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[contenido]', 10, 60, '', 'editor');
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', true) . $textos->id('ACTIVO'), 'margenSuperior');
	$codigo .= HTML::parrafo(HTML::campoChequeo('datos[multimedia]', false) . $textos->id('MULTIMEDIA'), 'margenSuperior');
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo'] = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo'] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ADICIONAR_PAGINA'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho'] = 700;
        $respuesta['alto'] = 500;
    } else {
        $respuesta['error'] = true;

        if (empty($datos['titulo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TITULO');
        } elseif ($sql->existeItem('paginas', 'titulo', $datos['titulo'], 'id_menu = "' . $datos['menu'] . '"')) {
            $respuesta['mensaje'] = $textos->id('ERROR_EXISTE_TITULO');
        } elseif (empty($datos['contenido'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CONTENIDO');
        } else {

            if ($pagina->adicionar($datos)) {
                $respuesta['error'] = false;
                $respuesta['accion'] = 'recargar';
            } else {
                $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Metodo modificar pagina
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $datos 
 */
function modificarPagina($id, $datos = array()) {
    global $textos, $sql;

    $pagina = new Pagina($id);
    $destino = '/ajax' . $pagina->urlBase . '/editRegister';
    $respuesta = array();

    if (empty($datos)) {
        $codigo = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($textos->id('MENU'), 'negrilla margenSuperior');
        $menus = $sql->seleccionar(array('menus'), array('id', 'nombre'), 'id IS NOT NULL', 'id', 'orden ASC');

        while ($menu = $sql->filaEnObjeto($menus)) {
            $ubicacion[$menu->id] = $menu->nombre;
        }

        $codigo .= HTML::listaDesplegable('datos[menu]', $ubicacion, $pagina->idMenu);
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo]', 50, 255, $pagina->titulo);
        $codigo .= HTML::parrafo($textos->id('CONTENIDO'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[contenido]', 10, 60, $pagina->contenido, 'editor');
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', $pagina->activo) . $textos->id('ACTIVO'), 'margenSuperior');
	$codigo .= HTML::parrafo(HTML::campoChequeo('datos[multimedia]', $pagina->multimedia) . $textos->id('MULTIMEDIA'), 'margenSuperior');
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo'] = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo'] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('MODIFICAR_PAGINA'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho'] = 700;
        $respuesta['alto'] = 500;
    } else {
        $respuesta['error'] = true;

        if (empty($datos['titulo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TITULO');
        } elseif ($sql->existeItem('paginas', 'titulo', $datos['titulo'], 'id != "' . $id . '" AND id_menu = "' . $datos['menu'] . '"')) {
            $respuesta['mensaje'] = $textos->id('ERROR_EXISTE_TITULO');
        } elseif (empty($datos['contenido'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CONTENIDO');
        } else {

            if ($pagina->modificar($datos)) {
                $respuesta['error'] = false;
                $respuesta['accion'] = 'recargar';
            } else {
                $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Metodo eliminar Pagina
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function eliminarPagina($id, $confirmado) {
    global $textos;

    $pagina = new Pagina($id);
    $destino = '/ajax' . $pagina->urlBase . '/deleteRegister';
    $respuesta = array();

    if (!$confirmado) {
        $titulo = HTML::frase($pagina->titulo, 'negrilla');
        $titulo = str_replace('%1', $titulo, $textos->id('CONFIRMAR_ELIMINACION'));
        $codigo = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo'] = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo'] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ELIMINAR_PAGINA'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho'] = 300;
        $respuesta['alto'] = 120;
    } else {
        if ($pagina->eliminar()) {
            $respuesta['error'] = false;
            $respuesta['errorExito'] = true;
            $respuesta['mensaje'] = $textos->id('PAGINA_ELIMINADA');
            $respuesta['accion'] = 'recargar';
        } else {
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Metodo subir Pagina
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function subirPagina($id, $confirmado) {
    global $textos;

    $pagina = new Pagina($id);
    $destino = '/ajax' . $pagina->urlBase . '/up';
    $respuesta = array();

    if (!$confirmado) {
        $titulo = HTML::frase($pagina->titulo, 'negrilla');
        $titulo = str_replace('%1', $titulo, $textos->id('CONFIRMAR_MODIFICACION'));
        $codigo = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo'] = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo'] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('MODIFICAR_PAGINA'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho'] = 320;
        $respuesta['alto'] = 120;
    } else {

        if ($pagina->subir()) {
            $respuesta['error'] = false;
            $respuesta['accion'] = 'recargar';
        } else {
            $respuesta['error'] = true;
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Metodo para bajar pagina
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function bajarPagina($id, $confirmado) {
    global $textos;

    $pagina = new Pagina($id);
    $destino = '/ajax' . $pagina->urlBase . '/down';
    $respuesta = array();

    if (!$confirmado) {
        $titulo = HTML::frase($pagina->titulo, 'negrilla');
        $titulo = str_replace('%1', $titulo, $textos->id('CONFIRMAR_MODIFICACION'));
        $codigo = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo'] = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo'] = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('MODIFICAR_PAGINA'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho'] = 270;
        $respuesta['alto'] = 120;
    } else {

        if ($pagina->bajar()) {
            $respuesta['error'] = false;
            $respuesta['accion'] = 'recargar';
        } else {
            $respuesta['error'] = true;
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $sql
 * @global type $configuracion 
 */
function cantidadRegistrados() {
    global $sql, $configuracion;
    $respuesta = array();
    $consulta = $sql->seleccionar(array('registro'), array('pais', 'COUNT(id) as cantidad'), '', 'pais', 'cantidad DESC');
    $lista = '';
    if ($sql->filasDevueltas) {

        while ($cantidad = $sql->filaEnObjeto($consulta)) {

            $cod = $sql->obtenerValor('paises', 'codigo_iso', 'nombre = "' . $cantidad->pais . '"');
            $img = HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['iconosBanderas'] . '/' . strtolower($cod) . '.png', 'miniaturaBanderas');

            $lista .= $cantidad->pais . '|' . $cantidad->cantidad . '|' . $img . '¬';
        }
    }
    $respuesta['contenido'] = $lista;

    Servidor::enviarJSON($respuesta);
}

?>