<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Paises
 * @author      Francisco J. Lozano c. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

class Pais {

    /**
     * C�digo interno o identificador del pa�s en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del m�dulo de paises
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un pais espec�fico
     * @var cadena
     */
    public $url;

    /**
     * Nombre del pa�s
     * @var cadena
     */
    public $nombre;

    /**
     * C�digo ISO del pa�s
     * @var cadena
     */
    public $codigo;

    /**
     * Indicador del orden cronol�gio de la lista de paises
     * @var l�gico
     */
    public $listaAscendente = true;

    /**
     * N�mero de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     *
     * Inicializar el pais
     *
     * @param entero $id C�digo interno o identificador del pa�s en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $configuracion, $sql;

        $modulo        = new Modulo("PAISES");
        $this->urlBase = "/".$modulo->url;
        $this->url     = $modulo->url;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos de un pais
     *
     * @param entero $id C�digo interno o identificador del pa�s en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("paises", "id", intval($id))) {

            $tablas = array(
                "p" => "paises"
            );

            $columnas = array(
                "id"     => "p.id",
                "nombre" => "p.nombre",
                "codigo" => "p.codigo_iso"
            );

            $condicion = "p.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase."/".$this->usuario;
            }
        }
    }

    /**
     *
     * Adicionar un pais
     *
     * @param  arreglo $datos       Datos del pa�s a adicionar
     * @return entero               C�digo interno o identificador del pa�s en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion;

        $consulta = $sql->insertar("paises", $datos);

        if ($consulta) {
            return $sql->ultimoId;

        } else {
            return NULL;
        }
    }

    /**
     *
     * Modificar un pais
     *
     * @param  arreglo $datos       Datos del pa�s a modificar
     * @return l�gico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->modificar("paises", $datos, "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Eliminar un pais
     *
     * @param entero $id    C�digo interno o identificador del pa�s en la base de datos
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("paises", "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Listar los paises
     *
     * @param entero  $cantidad    N�mero de paises a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los c�digos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condici�n adicional (SQL)
     * @return arreglo             Lista de paises
     *
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion, $modulo;

        /*** Validar la fila inicial de la consulta ***/
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*** Validar la cantidad de registros requeridos en la consulta ***/
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*** Validar que la condici�n sea una cadena de texto ***/
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*** Validar que la excepci�n sea un arreglo y contenga elementos ***/
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "p.id NOT IN ($excepcion)";
        }

        /*** Definir el orden de presentaci�n de los datos ***/
        if ($this->listaAscendente) {
            $orden = "p.nombre ASC";
        } else {
            $orden = "p.nombre DESC";
        }

        $tablas = array(
            "p" => "paises",
        );

        $columnas = array(
            "id"     => "p.id",
            "nombre" => "p.nombre",
            "codigo" => "p.codigo_iso",
        );

        if (is_null($this->registros)) {
            $conteo = $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($pais = $sql->filaEnObjeto($consulta)) {
                $pais->url = $this->urlBase."/".$pais->id;
                
                $lista[]   = $pais;
            }
        }

        return $lista;

    }
}
?>
