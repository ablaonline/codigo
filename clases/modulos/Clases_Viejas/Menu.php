<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Menus
 * @author      Francisco J. Lozano c. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

class Menu {

    /**
     * C�digo interno o identificador del men� en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Valor num�rico que determina el orden o la posici�n del men� en la base de datos
     * @var entero
     */
    public $orden;

    /**
     * URL relativa del m�dulo de men�s
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un menu espec�fico
     * @var cadena
     */
    public $url;

    /**
     * Nombre del men�
     * @var cadena
     */
    public $nombre;

    /**
     * Direcci�n (URL) a la cual conduce el men� al hacer clic
     * @var cadena
     */
    public $destino;

    /**
     * N�mero de p�ginas que contiene el men�
     * @var entero
     */
    public $paginas;

    /**
     * Indicador de disponibilidad del registro
     * @var l�gico
     */
    public $activo;

    /**
     * Indicador del orden cronol�gio de la lista de men�s
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
     * Inicializar el menu
     *
     * @param entero $id C�digo interno o identificador del men� en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $configuracion, $sql;

        $modulo        = new Modulo("MENUS");
        $this->urlBase = "/".$modulo->url;
        $this->url     = $modulo->url;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos de un menu
     *
     * @param entero $id C�digo interno o identificador del men� en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("menus", "id", intval($id))) {

            $tablas = array(
                "m" => "menus"
            );

            $columnas = array(
                "id"      => "m.id",
                "nombre"  => "m.nombre",
                "orden"   => "m.orden",
                "destino" => "m.destino",
                "activo"  => "m.activo"
            );

            $condicion = "m.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url     = $this->urlBase."/".$this->usuario;
                $paginas       = $sql->filaEnObjeto($sql->seleccionar(array("paginas"), array("paginas" => "COUNT(*)"), "id_menu = '".$this->id."'"));
                $this->paginas = $paginas->paginas;
            }
        }
    }

    /**
     *
     * Adicionar un menu
     *
     * @param  arreglo $datos       Datos del men� a adicionar
     * @return entero               C�digo interno o identificador del men� en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion;

        $orden = $datos["orden"];
        $menus = $sql->seleccionar(array("menus"), array("orden"), "orden >= '$orden'", "id", "orden ASC", 0, 2);
        $orden = $items = 0;

        if ($sql->filasDevueltas == 2) {
            while ($menu = $sql->filaEnObjeto($menus)) {
                $items++;
                $orden += $menu->orden;
            }

            $orden /= $items;

        } elseif ($sql->filasDevueltas == 1) {
            $menu  = $sql->filaEnObjeto($menus);
            $orden = ($menu->orden + 10000) / 2;
        }

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";

        } else {
            $datos["activo"] = "0";
        }

        $datos = array(
            "orden"   => $orden,
            "nombre"  => $datos["nombre"],
            "destino" => $datos["destino"],
            "activo"  => $datos["activo"]
        );

        $consulta = $sql->insertar("menus", $datos);

        if ($consulta) {
            return $sql->ultimoId;

        } else {
            return NULL;
        }
    }

    /**
     *
     * Modificar un menu
     *
     * @param  arreglo $datos       Datos del men� a modificar
     * @return l�gico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion;

        if (!isset($this->id)) {
            return NULL;
        }

        $orden = $datos["orden"];
        $menus = $sql->seleccionar(array("menus"), array("orden"), "orden >= '$orden'", "id", "orden ASC", 0, 2);
        $orden = $items = 0;

        if ($sql->filasDevueltas == 2) {
            while ($menu = $sql->filaEnObjeto($menus)) {
                $items++;
                $orden += $menu->orden;
            }

            $orden /= $items;

        } elseif ($sql->filasDevueltas == 1) {
            $menu  = $sql->filaEnObjeto($menus);
            $orden = ($menu->orden + 10000) / 2;
        }

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";

        } else {
            $datos["activo"] = "0";
        }

        $datos = array(
            "orden"   => $orden,
            "nombre"  => $datos["nombre"],
            "destino" => $datos["destino"],
            "activo"  => $datos["activo"]
        );

        $consulta = $sql->modificar("menus", $datos, "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Eliminar un menu
     *
     * @param entero $id    C�digo interno o identificador del men� en la base de datos
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("menus", "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Subir de nivel un men�
     *
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function subir() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array("menus"), array("id", "orden"), "orden < '".$this->orden."'", "id", "orden DESC", 0, 1);

        if ($sql->filasDevueltas) {
            $menu      = $sql->filaEnObjeto($consulta);
            $temporal  = ($this->orden + $menu->orden)/2;
            $abajo     = $sql->modificar("menus",array("orden" => $temporal), "id = '".$this->id."'");
            $arriba    = $sql->modificar("menus",array("orden" => $this->orden), "id = '".$menu->id."'");
            $abajo     = $sql->modificar("menus",array("orden" => $menu->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     *
     * Bajar de nivel un men�
     *
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function bajar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array("menus"), array("id", "orden"), "orden > '".$this->orden."'", "id", "orden ASC", 0, 1);

        if ($sql->filasDevueltas) {
            $menu      = $sql->filaEnObjeto($consulta);
            $temporal  = ($this->orden + $menu->orden)/2;
            $arriba    = $sql->modificar("menus",array("orden" => $temporal), "id = '".$this->id."'");
            $abajo     = $sql->modificar("menus",array("orden" => $this->orden), "id = '".$menu->id."'");
            $arriba    = $sql->modificar("menus",array("orden" => $menu->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     *
     * Listar los men�s
     *
     * @param entero  $cantidad    N�mero de men�s a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los c�digos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condici�n adicional (SQL)
     * @return arreglo             Lista de men�s
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
            $condicion .= "m.id NOT IN ($excepcion)";
        }

        /*** Definir el orden de presentaci�n de los datos ***/
        if ($this->listaAscendente) {
            $orden = "m.orden ASC";
        } else {
            $orden = "m.orden DESC";
        }

        $tablas = array(
            "m" => "menus",
        );

        $columnas = array(
            "id"      => "m.id",
            "nombre"  => "m.nombre",
            "orden"   => "m.orden",
            "destino" => "m.destino",
            "activo"  => "m.activo"
        );

        if (is_null($this->registros)) {
            $conteo          = $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($menu = $sql->filaEnObjeto($consulta)) {
                $menu->url     = $this->urlBase."/".$menu->id;
                $paginas       = $sql->filaEnObjeto($sql->seleccionar(array("paginas"), array("paginas" => "COUNT(*)"), "id_menu = '".$menu->id."'"));
                $menu->paginas = $paginas->paginas;
                $lista[]       = $menu;
            }
        }

        return $lista;

    }
}
?>
