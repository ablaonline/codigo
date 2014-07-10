<?php

/**
 *
 * @package     FOM
 * @subpackage  Registro
 * @author      Pablo Andrés Vélez Vidal <pavelez8@misena.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 GENESYS Corporation.
 * @version     0.2
 *
 * */
global $sesion_usuarioSesion, $modulo;

$tituloBloque   = $textos->id('MODULO_ACTUAL');
$objeto         = new Bitacora();/*creacion del objeto*/
$excluidas      = array('0');//items excluidos en la consulta
$item           = '';
$contenido      = '';

//campo oculto del cual el javascript sacara el nombre del modulo actual ->para??
$item           = HTML::campoOculto('nombreModulo', ucwords(strtolower($modulo->nombre)), 'nombreModulo');

$item          .= HTML::campoOculto('orden'.ucwords(strtolower($modulo->nombre)), 'ascendente|'.$objeto->ordenInicial, 'ordenGlobal');
$item          .= HTML::campoOculto('condicion'.ucwords(strtolower($modulo->nombre)), '', 'condicionGlobal');

/**
 * Datos para la paginacion
 * */
$registros = $configuracion['GENERAL']['registrosPorPaginaTabla'];
$pagina = 1;
$registroInicial = 0;

/**
 * Formulario para adicionar un nuevo elemento
 * */
//verificar si el usuario actual tiene permisos para agregar en este modulo
$puedeAgregar = Perfil::verificarPermisosAdicion($modulo->nombre);

if ((isset($sesion_usuarioSesion) && $puedeAgregar) || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
    $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($objeto->urlBase, $textos->id('ADICIONAR_ITEM')), 'alineadoDerecha margenSuperiorDoble', 'botonAdicionar'.ucwords(strtolower($modulo->nombre)).'');
} else {
    $botonAdicionar = '';
}


/**
 * Boton que carga la ventana modal para realizar la busqueda
 * */
$destino  = HTML::urlInterna($modulo->nombre, 0, true, 'search');
$botonRestaurar = HTML::contenedor('', 'botonRestaurarConsulta', 'botonRestaurarConsulta', array('alt' => HTML::urlInterna($modulo->nombre,0,true,'move'), 'title' => $textos->id('RESTAURAR_CONSULTA')));
$botonBuscador    = HTML::contenedor('', 'botonBuscador', 'botonBuscador', array('alt' => HTML::urlInterna($modulo->nombre,0,true,'search'), 'title' => $textos->id('BUSCAR_ITEM')));
$buscador = HTML::campoTexto('datos[patron]', 17, '', '', 'campoBuscador margenIzquierdaDoble', 'campoBuscador').$botonRestaurar.$botonBuscador;
$buscador = HTML::forma($destino, $buscador);
$buscador = HTML::contenedor($buscador, 'alineadoDerecha margenSuperiorDoble', 'botonBuscar'.ucwords(strtolower($modulo->nombre)).'');
$contenedorNotificaciones = HTML::contenedor('', 'contenedorNotificaciones', 'contenedorNotificaciones');
$botonesSuperiores = HTML::contenedor($buscador.$contenedorNotificaciones, '', 'botonesSuperioresModulo');


/**
 * Verifico que se haya iniciado una sesion y que tenga permisos para ver el modulo
 * */
if ((isset($sesion_usuarioSesion) && Perfil::verificarPermisosConsulta($modulo->id)) || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
    //Declaracion del arreglo lista... y carga de datos en él
    $arregloItems = $objeto->listar($registroInicial, $registros, $excluidas, "");

    if ($objeto->registros) {//si la consulta trajo registros
        $datosPaginacion = array($objeto->registros, $registroInicial, $registros, $pagina);
        $item  .= $objeto->generarTabla($arregloItems, $datosPaginacion);    

    } else {
        $item .= array(HTML::parrafo($textos->id('SIN_REGISTROS'), 'sinRegistros', 'sinRegistros'));
    }

    $codigo  = HTML::contenedor($botonesSuperiores .'<br>'. $item, 'listaItem', 'listaItem');
    $contenido .= HTML::bloque('bloqueContenidoPrincipal', $tituloBloque, $codigo, '', 'overflowVisible');
}else{
    $contenido      = HTML::contenedor($textos->id('SIN_PERMISOS_ACCESO_MODULO'), 'textoError');
}

Plantilla::$etiquetas['BLOQUE_IZQUIERDO'] = $contenido;
?>