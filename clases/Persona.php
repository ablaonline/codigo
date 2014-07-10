<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 * */
class Persona {

    /**
     * Código interno o identificador de la persona en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Nombre de la persona
     * @var cadena
     */
    public $nombre;

    /**
     * Apellidos de la persona
     * @var cadena
     */
    public $apellidos;

    /**
     * Nombre completo de la persona
     * @var cadena
     */
    public $nombreCompleto;

    /**
     * Descripción corta de su personalidad
     * @var cadena
     */
    public $descripcion;

    /**
     * Código interno o identificador en la base de datos de la imagen de la persona
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen de la persona en tamaño normal
     * @var cadena
     */
    public $imagenPrincipal;

    /**
     * Ruta de la imagen de la persona en miniatura
     * @var cadena
     */
    public $imagenMiniatura;

    /**
     * Género de la persona ('M' o 'F')
     * @var caracter
     */
    public $idGenero;

    /**
     * Género de la persona (palabra completa)
     * @var cadena
     */
    public $genero;

    /**
     * Fecha de nacimiento de la persona
     * @var fecha
     */
    public $fechaNacimiento;

    /**
     * Código interno o identificador en la base de datos de la ciudad de nacimiento de la persona
     * @var entero
     */
    public $idCiudadNatal;

    /**
     * Nombre de la ciudad de nacimiento de la persona
     * @var cadena
     */
    public $ciudadNatal;

    /**
     * Código interno o identificador en la base de datos del estado de nacimiento de la persona
     * @var entero
     */
    public $idEstadoNatal;

    /**
     * Nombre del estado de nacimiento de la persona
     * @var cadena
     */
    public $estadoNatal;

    /**
     * Código interno o identificador en la base de datos de la persona de nacimiento de la persona
     * @var entero
     */
    public $idPaisNatal;

    /**
     * Nombre de la persona de nacimiento de la persona
     * @var cadena
     */
    public $paisNatal;

    /**
     * Código interno o identificador en la base de datos de la ciudad de residencia de la persona
     * @var entero
     */
    public $idCiudadResidencia;

    /**
     * Nombre de la ciudad de residencia de la persona
     * @var cadena
     */
    public $ciudadResidencia;

    /**
     * Código interno o identificador en la base de datos del estado de residencia de la persona
     * @var entero
     */
    public $idEstadoResidencia;

    /**
     * Nombre del estado de residencia de la persona
     * @var cadena
     */
    public $estadoResidencia;

    /**
     * Código interno o identificador en la base de datos de la persona de residencia de la persona
     * @var entero
     */
    public $idPaisResidencia;

    /**
     * Nombre de la persona de residencia de la persona
     * @var cadena
     */
    public $paisResidencia;

    /**
     * Dirección de correo electrónico de la persona
     * @var cadena
     */
    public $correo;

    /**
     * Dirección de la página web de la persona
     * @var cadena
     */
    public $paginaWeb;

    /**
     * Codigo Iso del Pais al cual Pertenece el usuario
     * @var cadena
     */
    public $codigoIsoPais;

    /**
     *
     * Inicializar la persona
     *
     * @param entero $id Código interno o identificador de la persona en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $sql;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos de la persona
     *
     * @param entero $id Código interno o identificador de la persona en la base de datos
     *
     */
    public function cargar($id = NULL) {
        global $sql, $configuracion, $textos;

        if (isset($id) && $sql->existeItem("personas", "id", intval($id))) {
            $this->id = $id;

            $tablas = array(
                "p" => "personas",
                "c1" => "ciudades",
                "e1" => "estados",
                "p1" => "paises",
                "c2" => "ciudades",
                "e2" => "estados",
                "p2" => "paises",
                "i" => "imagenes"
            );

            $columnas = array(
                "nombre" => "p.nombre",
                "apellidos" => "p.apellidos",
                "idGenero" => "p.genero",
                "idImagen" => "p.id_imagen",
                "imagen" => "i.ruta",
                "fechaNacimiento" => "p.fecha_nacimiento",
                "idCiudadNatal" => "p.id_ciudad_nacimiento",
                "ciudadNatal" => "c1.nombre",
                "idEstadoNatal" => "c1.id_estado",
                "estadoNatal" => "e1.nombre",
                "idPaisNatal" => "e1.id_pais",
                "paisNatal" => "p1.nombre",
                "codigoIsoPais" => "p1.codigo_iso",
                "idCiudadResidencia" => "p.id_ciudad_residencia",
                "ciudadResidencia" => "c2.nombre",
                "idEstadoResidencia" => "c2.id_estado",
                "estadoResidencia" => "e2.nombre",
                "idPaisResidencia" => "e2.id_pais",
                "paisResidencia" => "p2.nombre",
                "descripcion" => "p.descripcion",
                "correo" => "p.correo",
                "paginaWeb" => "p.pagina_web"
            );

            $condicion = "p.id_ciudad_residencia = c1.id AND c1.id_estado = e1.id AND e1.id_pais = p1.id
                          AND p.id_ciudad_residencia = c2.id AND c2.id_estado = e2.id AND e2.id_pais = p2.id
                          AND p.id_imagen = i.id AND p.id = '$id'";
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);



            if ($sql->filasDevueltas) {
                $datos = $sql->filaEnObjeto($consulta);

                foreach ($datos as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->nombreCompleto = $this->nombre . " " . $this->apellidos;
                $this->genero = $textos->id("GENERO_" . $this->idGenero);
                $this->imagenPrincipal = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesDinamicas"] . "/" . $datos->imagen;
                $this->imagenMiniatura = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $datos->imagen;
            }
        }
    }

    /**
     *
     * Adicionar una persona
     *
     * @param  arreglo $datos       Datos de la persona a adicionar
     * @return entero               Código interno o identificador de la persona en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion;

        $consulta = $sql->insertar("personas", $datos);

        if ($consulta) {
            return $sql->ultimoId;
        } else {
            return NULL;
        }
    }

    /**
     *
     * Modificar una persona
     *
     * @param  arreglo $datos       Datos de la persona a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->modificar("personas", $datos, "id = '" . $this->id . "'");
        return $consulta;
    }

    /**
     *
     * Eliminar una persona
     *
     * @param entero $id    Código interno o identificador de la persona en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("personas", "id = '" . $this->id . "'");
        return $consulta;
    }

}

