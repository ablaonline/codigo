<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Asociado
 * @author      Pablo A. Vélez <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 *
 * */
class Asociado {

    /**
     * Código interno o identificador del Asociado en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de Asociado
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un Asociado específico
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador de la categoria a la cual pertenece el Asociado
     * @var entero
     */
    public $idCategoria;

    /**
     * Título del Asociado
     * @var cadena
     */
    public $titulo;

    /**
     * Título del Asociado
     * @var cadena
     */
    public $descripcion;

    /**
     * Código interno o identificador en la base de datos de la imagen relacionada con el Asociado
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen del Asociado en tamaño normal
     * @var cadena
     */
    public $imagenPrincipal;

    /**
     * Fecha de creación del Asociado
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación inicial del Asociado
     * @var fecha
     */
    public $fechaInicial;

    /**
     * Fecha de publicación final del Asociado
     * @var fecha
     */
    public $fechaFinal;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de Asociado
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Vinculo al que dirige el banner
     * @var entero
     */
    public $vinculo = NULL;

    /**
     *
     * Inicializar el Asociado
     *
     * @param entero $id Código interno o identificador del Asociado en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $configuracion, $sql;

        $modulo = new Modulo("ASOCIADOS");
        $this->urlBase   = "/" . $modulo->url;
        $this->url       = $modulo->url;
        $this->idModulo  = $modulo->id;

        $this->registros = $sql->obtenerValor("asociados", "COUNT(id)", "activo = '1'");

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos del Asociado
     *
     * @param entero $id Código interno o identificador del Asociado en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("asociados", "id", intval($id))) {

            $tablas = array(
                "a" => "asociados",
                "i" => "imagenes"
            );

            $columnas = array(
                "id"            => "a.id",
                "idImagen"      => "a.id_imagen",
                "imagen"        => "i.ruta",
                "titulo"        => "a.titulo",
                "descripcion"   => "a.descripcion",
                "vinculo"       => "a.vinculo",
                "fechaCreacion" => "UNIX_TIMESTAMP(a.fecha_creacion)",
                "fechaInicial"  => "UNIX_TIMESTAMP(a.fecha_inicial)",
                "fechaFinal"    => "UNIX_TIMESTAMP(a.fecha_final)",
                "activo"        => "a.activo"
            );

            $condicion = "a.id_imagen = i.id AND a.id = '$id'";
            //$sql->depurar = true;
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . "/" . $this->usuario;
                $this->registros = $sql->obtenerValor("asociados", "COUNT(id)", "activo = '1'");
                $this->imagenPrincipal = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesNormales"] . "/" . $this->imagen;
            }
        }
    }

    /**
     *
     * Adicionar un asociado
     *
     * @param  arreglo $datos       Datos del asociado a adicionar
     * @return entero               Código interno o identificador del asociado en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql,  $sesion_usuarioSesion,  $archivo_imagen;

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";
            $datos["fecha_inicial"] = date("Y-m-d H:i:s");
        } else {
            $datos["activo"] = "0";
            $datos["fecha_publicacion"] = NULL;
        }

//        $imagen = new Imagen();
//        $datosImagen = array(
//            "idRegistro" => "",
//            "modulo"     => "35"
//        );
//        $idImagen = $imagen->adicionar($datosImagen);
        
        $idImagen = '8';
        
        if(isset($archivo_imagen) && !empty($archivo_imagen["tmp_name"])){
            
            $imagen = new Imagen();
            $datosImagen = array(
                "modulo"      => "ASOCIADOS",
                "idRegistro"  => "",
                "titulo"      => "imagen_asociado",
                "descripcion" => "imagen_asociado"
            );
            
            $idImagen = $imagen->adicionar($datosImagen);            
        }

        $datosAsociado = array(
            "titulo"        => $datos["titulo"],
            "descripcion"   => $datos["descripcion"],
            "vinculo"       => $datos["vinculo"],
            "id_imagen"     => $idImagen,
            "fecha_creacion"=> date("Y-m-d H:i:s"),
            "fecha_inicial" => $datos['fecha_inicial'],
            "activo"        => $datos['activo']
        );

        $consulta = $sql->insertar("asociados", $datosAsociado);
        // Recursos::escribirTxt("la consulta: ".$datosNoticia["id_imagen"], 5);

        if ($consulta) {
            return $sql->ultimoId;
            
        } else {
            return FALSE;
        }
    }

//fin del metodo adicionar

    /**
     *
     * Modificar un asociado
     *
     * @param  arreglo $datos       Datos del asociado  a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
        }

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";
            $datos["fecha_inicial"] = date("Y-m-d H:i:s");
        } else {
            $datos["activo"] = "0";
            $datos["fecha_inicial"] = NULL;
        }

        if (isset($archivo_imagen) && !empty($archivo_imagen["tmp_name"])) {
            $imagen = new Imagen($this->idImagen);
            $imagen->eliminar();
            
            $datosImagen = array(
                "idRegistro" => "",
                "modulo"     => "35"
            );
            $idImagen = $imagen->adicionar($datosImagen);
            
        } else {
            $idImagen = $this->idImagen;
        }

        $datos = array(
            "titulo"        => $datos["titulo"],
            "descripcion"   => $datos["descripcion"],
            "vinculo"       => $datos["vinculo"],
            "fecha_inicial" => $datos['fecha_inicial'],
            "id_imagen"     => $idImagen,
            "activo"        => $datos['activo']
        );

        //$sql->depurar = true;
        $consulta = $sql->modificar("asociados", $datos, "id = '" . $this->id . "'");

        if ($consulta) {
            return true;
        } else {
            return false;
        }
    }

//fin del metodo modificar

    /**
     *
     * Eliminar un asociado
     *
     * @param entero $id    Código interno o identificador del asociado  en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        
        $consulta = $sql->eliminar("asociados", "id = '" . $this->id . "'");

        if (!($consulta)) {            
            return false;
        } else {
            $imagen = new Imagen($this->idImagen);
            $imagen->eliminar();
            return true;
        }
    }

    /**
     *
     * Listar los asociados
     *
     * @param entero  $cantidad    Número de asociados a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de asociados
     *
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
            $excepcion = implode(",", $excepcion);
            $condicion .= "a.id NOT IN ($excepcion)";
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = "a.fecha_creacion ASC";
        } else {
            $orden = "a.fecha_creacion ASC";
        }


        $tablas = array(
            "a" => "asociados",
            "i" => "imagenes"
        );

        $columnas = array(
            "id"            => "a.id",
            "idImagen"      => "a.id_imagen",
            "imagen"        => "i.ruta",
            "titulo"        => "a.titulo",
            "descripcion"   => "a.descripcion",
            "vinculo"       => "a.vinculo",
            "fechaCreacion" => "UNIX_TIMESTAMP(a.fecha_creacion)",
            "fechaInicial"  => "UNIX_TIMESTAMP(a.fecha_inicial)",
            "fechaFinal"    => "UNIX_TIMESTAMP(a.fecha_final)",
            "activo"        => "a.activo"
        );

        if (!empty($condicion)) {
            $condicion .= " AND ";
        }
        $condicion .= "a.id_imagen = i.id";

        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ( $asociado = $sql->filaEnObjeto($consulta) ) {
                $asociado->url = $this->urlBase . "/" . $asociado->id;
                $asociado->imagenPrincipal = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesDinamicas"] . "/" . $asociado->imagen;

                $lista[] = $asociado;
            }
        }

        return $lista;
    }

//fin del metodo listar
}

?>