<?php

/**
 * @package     FOLCS
 * @subpackage  Bitacora
 * @author      Pablo A. Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
class Bitacora {

    /**
     * Código interno o identificador del registro en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * URL relativa del módulo de registros
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un registro específica
     * @var cadena
     */
    public $url;

    /**
     * Número de registros activos de la lista de foros
     * @var entero
     */
    public $registrosConsulta = NULL;

    /**
     * Orden predeterminado para organizar los listados
     * @var entero
     */
    public $ordenInicial = 'fecha';

    /**
     * Inicializar el objeto
     * @param entero $id Código interno o identificador del objeto en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('BITACORA');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        $consulta = $sql->obtenerValor('registro', 'COUNT(id)', '');
        $this->registros = $consulta;


        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Listar las registros
     * @param entero  $cantidad    Número de ciudadesa incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de ciudades
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicionGlobal = NULL, $orden = NULL) {
        global $sql;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicionGlobal)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion = 'b.id NOT IN (' . $excepcion . ') ';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if (!isset($orden)) {
            $orden = $this->ordenInicial;
        }
        if ($this->listaAscendente) {
            $orden = $orden . ' ASC';
        } else {
            $orden = $orden . ' DESC';
        }


        $tablas = array(
            'b' => 'bitacora'
        );

        $columnas = array(
            'id' => 'b.id',
            'usuario' => 'b.usuario',
            'ip' => 'b.ip',
            'consulta' => 'b.consulta',
            'fecha' => 'b.fecha'
        );

        if (!empty($condicionGlobal)) {
            if ($condicion != '') {
                $condicion .= ' AND ';
            }
            $condicion .= $condicionGlobal;
        }

        if (is_null($this->registrosConsulta)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registrosConsulta = $sql->filasDevueltas;
        }
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'b.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($bitacora = $sql->filaEnObjeto($consulta)) {
                $lista[] = $bitacora;
            }
        }

        return $lista;
    }

    /**
     * MEtodo que se encargar de generar la tabla que lista los usuarios
     * @global type $textos
     * @param type $arregloRegistros
     * @param type $datosPaginacion
     * @return type 
     */
    public function generarTabla($arregloRegistros, $datosPaginacion = NULL) {
        global $textos;
        //Declaracion de las columnas que se van a mostrar en la tabla
        $datosTabla = array(
            HTML::contenedor($textos->id('USUARIO'), 'columnaCabeceraTabla') => 'usuario|b.usuario',
            HTML::contenedor($textos->id('IP'), 'columnaCabeceraTabla') => 'ip|b.ip',
            HTML::contenedor($textos->id('CONSULTA'), 'columnaCabeceraTabla') => 'consulta|b.consulta',
            HTML::contenedor($textos->id('FECHA'), 'columnaCabeceraTabla') => 'fecha|b.fecha'
        );

        //ruta a donde se mandara la accion del doble click
        $rutaPaginador = '/ajax' . $this->urlBase . '/move';

        return Recursos::generarTablaRegistros($arregloRegistros, $datosTabla, $rutaPaginador, $datosPaginacion) . HTML::crearMenuBotonDerecho('BITACORA');
    }

}

