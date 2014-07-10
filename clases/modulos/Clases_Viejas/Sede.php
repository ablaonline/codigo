<?php

/**
 * @package     FOLCS
 * @subpackage  Sedes
 * @author      Pablo A. Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
class Sede {

    /**
     * Código interno o identificador de la sede en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Nombre de la sede
     * @var cadena
     */
    public $nombre;

    /**
     * Código interno o identificador en la base de datos de la ciudad de la sede binacional
     * @var entero
     */
    public $idCiudad;

    /**
     * Nombre de la ciudad de la sede binacional al que pertenece persona
     * @var cadena
     */
    public $ciudad;

    /**
     * Código interno o identificador en la base de datos del estado de la sede binacional
     * @var entero
     */
    public $idEstado;

    /**
     * Nombre del estado de la sede binacional al que pertenece persona
     * @var cadena
     */
    public $estado;

    /**
     * Código interno o identificador en la base de datos del país de la sede binacional
     * @var entero
     */
    public $idPais;

    /**
     * Nombre del país de la sede binacional
     * @var cadena
     */
    public $pais;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Inicializar la sede
     * @param entero $id Código interno o identificador de la sede en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('SEDES');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;

        $consulta = $sql->filaEnObjeto($sql->seleccionar(array('sedes'), array('registros' => 'COUNT(id)')));
        $this->registros = $consulta->registros;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos de una sede
     * @param entero $id Código interno o identificador de la sede en la base de datos
     */
    public function cargar($id) {
        global $sql;

        if (isset($id) && $sql->existeItem('sedes', 'id', intval($id))) {

            $tablas = array(
                's' => 'sedes',
                'c' => 'ciudades',
                'e' => 'estados',
                'p' => 'paises'
            );

            $columnas = array(
                'id' => 's.id',
                'nombre' => 's.nombre',
                'direccion' => 's.direccion',
                'telefono1' => 's.telefono_1',
                'telefono2' => 's.telefono_2',
                'celular' => 's.celular',
                'correo' => 's.correo',
                'idCiudad' => 's.id_ciudad',
                'ciudad' => 'c.nombre',
                'idEstado' => 'c.id_estado',
                'estado' => 'e.nombre',
                'idPais' => 'e.id_pais',
                'pais' => 'p.nombre',
                'activo' => 's.activo'
            );

            $condicion = 's.id_ciudad = c.id AND c.id_estado = e.id AND e.id_pais = p.id AND s.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }
            }
        }
    }

    /**
     * Adicionar una sede
     * @param  arreglo $datos       Datos de la sede a adicionar
     * @return entero               Código interno o identificador de la sede en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql;

        $datos = array(
            'id_centro' => htmlspecialchars($datos['id_centro']),
            'id_ciudad' => $datos['id_ciudad'],
            'nombre' => htmlspecialchars($datos['nombre']),
            'direccion' => htmlspecialchars($datos['direccion']),
            'telefono_1' => htmlspecialchars($datos['telefono_1']),
            'telefono_2' => htmlspecialchars($datos['telefono_2']),
            'celular' => htmlspecialchars($datos['celular']),
            'correo' => strip_tags($datos['correo'], '@')
        );

        if (isset($datos['activo'])) {
            $datos['activo'] = '1';
        } else {
            $datos['activo'] = '0';
        }

        $consulta = $sql->insertar('sedes', $datos);

        if ($consulta) {
            return $sql->ultimoId;
        } else {
            return NULL;
        }
    }

    /**
     * Modificar una sede
     * @param  arreglo $datos       Datos de la sede a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $datos = array(
            'id_ciudad' => $datos['id_ciudad'],
            'nombre' => htmlspecialchars($datos['nombre']),
            'direccion' => htmlspecialchars($datos['direccion']),
            'telefono_1' => htmlspecialchars($datos['telefono_1']),
            'telefono_2' => htmlspecialchars($datos['telefono_2']),
            'celular' => htmlspecialchars($datos['celular']),
            'correo' => strip_tags($datos['correo'], '@')
        );

        if (isset($datos['activo'])) {
            $datos['activo'] = '1';
        } else {
            $datos['activo'] = '0';
        }

        $consulta = $sql->modificar('sedes', $datos, 'id = "' . $this->id . '"');

        return $consulta;
    }

    /**
     * Eliminar una sede
     * @param entero $id    Código interno o identificador de la sede en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('sedes', 'id = "' . $this->id . '"');
        return $consulta;
    }

    /**
     * Listar las sedes
     * @param entero  $cantidad    Número de sedes a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de sedes
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= ' AND s.id NOT IN (' . $excepcion . ') ';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'p.nombre ASC, e.nombre ASC, c.nombre ASC, s.nombre ASC';
        } else {
            $orden = 'p.nombre ASC, e.nombre ASC, c.nombre ASC, s.nombre DESC';
        }

        $tablas = array(
            's' => 'sedes',
            'c' => 'ciudades',
            'e' => 'estados',
            'p' => 'paises'
        );

        $columnas = array(
            'id' => 's.id',
            'nombre' => 's.nombre',
            'direccion' => 's.direccion',
            'telefono1' => 's.telefono_1',
            'telefono2' => 's.telefono_2',
            'celular' => 's.celular',
            'correo' => 's.correo',
            'idCiudad' => 's.id_ciudad',
            'ciudad' => 'c.nombre',
            'idEstado' => 'c.id_estado',
            'estado' => 'e.nombre',
            'idPais' => 'e.id_pais',
            'pais' => 'p.nombre',
            'activo' => 's.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 's.id_ciudad = c.id AND c.id_estado = e.id AND e.id_pais = p.id AND s.id > 0';

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($sede = $sql->filaEnObjeto($consulta)) {
                $sede->url = $this->urlBase . '/' . $sede->id;
                $sede->logo = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $sede->logo;
                $lista[] = $sede;
            }
        }

        return $lista;
    }

}

?>