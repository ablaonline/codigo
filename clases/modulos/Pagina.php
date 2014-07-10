<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Paginas
 * @author      Francisco J. Lozano c. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

class Pagina {

    /**
     * Código interno o identificador de la página en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Código interno o identificador del menú al cual pertenece la página en la base de datos
     * @var entero
     */
    public $idMenu;

    /**
     * Nombre del menú al cual pertenece la página
     * @var cadena
     */
    public $menu;

    /**
     * Valor numérico que determina el orden o la posición de la página en la base de datos
     * @var entero
     */
    public $orden;

    /**
     * URL relativa del módulo de páginas
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de una página específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador de la página en la base de datos
     * @var entero
     */
    public $idAutor;
    
     /**
     * Código interno o identificador del usuario creador de la página en la base de datos
     * @var entero
     */
    public $idModulo;

    /**
     * Sobrenombre o apodo del usuario creador de la página
     * @var cadena
     */
    public $autor;

    /**
     * Ruta de la imagen (miniatura) que representa al usuario creador de la página
     * @var cadena
     */
    public $fotoAutor;

    /**
     * Título de la página
     * @var cadena
     */
    public $titulo;

    /**
     * Contenido completo de la página
     * @var cadena
     */
    public $contenido;

    /**
     * Fecha de creación de la página
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación de la página
     * @var fecha
     */
    public $fechaPublicacion;

    /**
     * Fecha de la última modificación de la página
     * @var fecha
     */
    public $fechaActualizacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador de disponibilidad de bloque multimedia
     * @var lógico
     */
    public $multimedia;

    /**
     * Indicador del orden cronológio de la lista de páginas
     * @var lógico
     */
    public $listaAscendente = true;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     *
     * Inicializar el página
     *
     * @param entero $id Código interno o identificador de la página en la base de datos
     *
     */
    public function __construct($id = NULL) {

        $modulo         = new Modulo("PAGINAS");
        $this->urlBase  = "/".$modulo->url;
        $this->url      = $modulo->url;
        $this->idModulo = $modulo->id;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos de un página
     *
     * @param entero $id Código interno o identificador de la página en la base de datos
     *
     */
    public function cargar($id) {
        global $sql;

        if (isset($id) && $sql->existeItem("paginas", "id", intval($id))) {

            $tablas = array(
                "g" => "paginas",
                "m" => "menus",
                "u" => "usuarios",
                "p" => "personas",
                "i" => "imagenes"
            );

            $columnas = array(
                "id"                 => "g.id",
                "idMenu"             => "g.id_menu",
                "menu"               => "m.nombre",
                "orden"              => "g.orden",
                "idAutor"            => "g.id_usuario",
                "autor"              => "u.sobrenombre",
                "fotoAutor"          => "i.ruta",
                "titulo"             => "g.titulo",
                "destino"            => "m.destino",
                "contenido"          => "g.contenido",
                "fechaCreacion"      => "UNIX_TIMESTAMP(g.fecha_creacion)",
                "fechaPublicacion"   => "UNIX_TIMESTAMP(g.fecha_publicacion)",
                "fechaActualizacion" => "UNIX_TIMESTAMP(g.fecha_actualizacion)",
                "activo"             => "g.activo",
		"multimedia"         => "g.multimedia"
            );

            $condicion = "g.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND g.id_menu = m.id AND g.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase."/".$this->destino;
            }
        }
    }

    /**
     *
     * Adicionar un página
     *
     * @param  arreglo $datos       Datos de la página a adicionar
     * @return entero               Código interno o identificador de la página en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion;

        $paginas = $sql->seleccionar(array("paginas"), array("orden" => "MAX(orden)"), "id_menu = '".$datos["menu"]."'");
        $orden   = 0;

        if ($sql->filasDevueltas == 1) {

            $pagina = $sql->filaEnObjeto($paginas);
            $orden  = $pagina->orden + 50000;
            $orden /= 2;

        } elseif ($sql->filasDevueltas == 0) {
            $orden = 500000;
        }

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";

        } else {
            $datos["activo"] = "0";
        }

        if (isset($datos["multimedia"])) {
            $datos["multimedia"] = "1";

        } else {
            $datos["multimedia"] = "0";
        }

        $datos = array(
            "orden"                 => $orden,
            "titulo"                => $datos["titulo"],
            "contenido"             => $datos["contenido"],
            "id_menu"               => $datos["menu"],
            "id_usuario"            => $sesion_usuarioSesion->id,
            "fecha_creacion"        => date("Y-m-d H:i:s"),
            "fecha_publicacion"     => date("Y-m-d H:i:s"),
            "fecha_actualizacion"   => date("Y-m-d H:i:s"),
            "activo"                => $datos["activo"]
        );

        $consulta = $sql->insertar("paginas", $datos);

        if ($consulta) {
            return $sql->ultimoId;

        } else {
            return NULL;
        }
    }

    /**
     *
     * Modificar un página
     *
     * @param  arreglo $datos       Datos de la página a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";

        } else {
            $datos["activo"] = "0";
        }


        if (isset($datos["multimedia"])) {
            $datos["multimedia"] = "1";

        } else {
            $datos["multimedia"] = "0";
        }

        $datos = array(
            "titulo"              => $datos["titulo"],
            "contenido"           => $datos["contenido"],
            "id_menu"             => $datos["menu"],
            "fecha_actualizacion" => date("Y-m-d H:i:s"),
            "activo"              => $datos["activo"]
        );


        $consulta = $sql->modificar("paginas", $datos, "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Eliminar un página
     *
     * @param entero $id    Código interno o identificador de la página en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("paginas", "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Subir de nivel una página
     *
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function subir() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array("paginas"), array("id", "orden"), "orden < '".$this->orden."'", "id", "orden DESC", 0, 1);

        if ($sql->filasDevueltas) {
            $pagina      = $sql->filaEnObjeto($consulta);
            $temporal  = ($this->orden + $pagina->orden)/2;
            $sql->modificar("paginas",array("orden" => $temporal), "id = '".$this->id."'");
            $sql->modificar("paginas",array("orden" => $this->orden), "id = '".$pagina->id."'");
            $sql->modificar("paginas",array("orden" => $pagina->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     *
     * Bajar de nivel una página
     *
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function bajar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array("paginas"), array("id", "orden"), "orden > '".$this->orden."'", "id", "orden ASC", 0, 1);

        if ($sql->filasDevueltas) {
            $pagina      = $sql->filaEnObjeto($consulta);
            $temporal  = ($this->orden + $pagina->orden)/2;
            $sql->modificar("paginas",array("orden" => $temporal), "id = '".$this->id."'");
            $sql->modificar("paginas",array("orden" => $this->orden), "id = '".$pagina->id."'");
            $sql->modificar("paginas",array("orden" => $pagina->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     *
     * Listar las páginas
     *
     * @param entero  $cantidad    Número de páginas a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de páginas
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

        /*** Validar que la condición sea una cadena de texto ***/
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*** Validar que la excepción sea un arreglo y contenga elementos ***/
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "g.id NOT IN ($excepcion)";
        }

        /*** Definir el orden de presentación de los datos ***/
        if ($this->listaAscendente) {
            $orden = "m.nombre ASC, g.orden ASC";
        } else {
            $orden = "m.nombre ASC, g.orden DESC";
        }

        $tablas = array(
            "g" => "paginas",
            "m" => "menus",
            "u" => "usuarios",
            "p" => "personas",
            "i" => "imagenes"
        );

        $columnas = array(
            "id"                 => "g.id",
            "idMenu"             => "g.id_menu",
            "menu"               => "m.nombre",
            "orden"              => "g.orden",
            "idAutor"            => "g.id_usuario",
            "autor"              => "u.sobrenombre",
            "fotoAutor"          => "i.ruta",
            "titulo"             => "g.titulo",
            "contenido"          => "g.contenido",
            "fechaCreacion"      => "UNIX_TIMESTAMP(g.fecha_creacion)",
            "fechaPublicacion"   => "UNIX_TIMESTAMP(g.fecha_publicacion)",
            "fechaActualizacion" => "UNIX_TIMESTAMP(g.fecha_actualizacion)",
            "activo"             => "g.activo",
	    "multimedia"         => "g.multimedia"
        );

        if (!empty($condicion)) {
            $condicion .= " AND ";
        }

        $condicion .= "g.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND g.id_menu = m.id";

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($pagina = $sql->filaEnObjeto($consulta)) {
                $pagina->url = $this->urlBase."/".$pagina->id;
                $lista[]   = $pagina;
            }
        }

        return $lista;

    }
}

