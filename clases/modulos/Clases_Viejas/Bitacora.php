<?php

/**
 * @package     FOLCS
 * @subpackage  Registro
 * @author      Pablo A. V�lez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
class Bitacora {

    /**
     * C�digo interno o identificador del registro en la base de datos
     * @var entero
     */
    public $id;

    /**
     * C�digo interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * URL relativa del m�dulo de registros
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un registro espec�fica
     * @var cadena
     */
    public $url;

    /**
     * C�digo interno o identificador del usuario creador de la registro en la base de datos
     * @var entero
     */
    public $nombres;

    /**
     * C�digo interno o identificador del modulo
     * @var entero
     */
    public $apellidos;

    /**
     * Nombre de usuario (login) del usuario creador de la registro
     * @var cadena
     */
    public $institucion;

    /**
     * Sobrenombre o apodo del usuario creador de la registro
     * @var cadena
     */
    public $cargo;

    /**
     * T�tulo de la registro
     * @var cadena
     */
    public $ciudad;

    /**
     * T�tulo de la registro
     * @var cadena
     */
    public $pais;

    /**
     * Indicador de si ya ha realizado el pago
     * @var l�gico
     */
    public $pagado;

    /**
     * Codigo postal del inscrito
     * @var cadena
     */
    public $codigoPostal;

    /**
     * Correo postal del inscrito
     * @var cadena
     */
    public $direccionCorreo;

    /**
     * Correo electronico del inscrito
     * @var cadena
     */
    public $email;

    /**
     * Telefono de la persona
     * @var cadena
     */
    public $telefono;

    /**
     * Numero de fax para contactar la persona
     * @var cadena
     */
    public $fax;

    /**
     * Evento al cual se inscribio la persona
     * @var enum
     */
    public $evento;

    /**
     * Fecha de creaci�n de la registro
     * @var fecha
     */
    public $tituloCarnet;

    /**
     * Fecha de creaci�n de la registro
     * @var fecha
     */
    public $rol;

    /**
     * Fecha de creaci�n de la registro
     * @var fecha
     */
    public $fechaRegistro;

    /**
     * Fecha de publicaci�n de la registro
     * @var fecha
     */
    public $nombreCertificado;

    /**
     * Indicador de disponibilidad del registro
     * @var l�gico
     */
    public $activo;

    /**
     * Indicador del orden cronol�gio de la lista de registros
     * @var l�gico
     */
    public $listaAscendente = false;

    /**
     * N�mero de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * N�mero de registros de la lista
     * @var entero
     */
    public $pagados = NULL;

    /**
     * N�mero de registros activos de la lista de foros
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
     * @param entero $id C�digo interno o identificador del objeto en la base de datos
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
     * @param entero  $cantidad    N�mero de ciudadesa incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los c�digos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condici�n adicional (SQL)
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

        /*         * * Validar que la condici�n sea una cadena de texto ** */
        if (!is_string($condicionGlobal)) {
            $condicion = '';
        }

        /*         * * Validar que la excepci�n sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion = 'b.id NOT IN (' . $excepcion . ') ';
        }

        /*         * * Definir el orden de presentaci�n de los datos ** */
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
                $registro->url = $this->urlBase . '/' . $bitacora->id;
                $lista[] = $bitacora;
            }
        }

        return $lista;
    }

//fin del metodo listar

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

//fin de la clase Registro
?>