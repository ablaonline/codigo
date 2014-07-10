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
class Mensaje {

    /**
     * Código interno o identificador del mensaje en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Código interno o identificador del usuario creador del mensaje en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Nombre de usuario (login) del usuario creador del mensaje
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del mensaje
     * @var cadena
     */
    public $autor;

    /**
     * Sobrenombre o apodo del usuario creador del mensaje
     * @var cadena
     */
    public $genero;

    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;

    /**
     * Título del mensaje
     * @var cadena
     */
    public $titulo;

    /**
     * Contenido completo del mensaje
     * @var cadena
     */
    public $contenido;

    /**
     * Fecha de publicación del mensaje
     * @var fecha
     */
    public $fecha;

    /**
     * Estado de lectura del mensaje
     * @var lógico
     */
    public $leido;

    /**
     *
     * Inicializar el mensaje
     *
     * @param entero $id Código interno o identificador del mensaje en la base de datos
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
     * Cargar los datos del mensaje
     *
     * @param entero $id Código interno o identificador del mensaje en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("mensajes", "id", intval($id))) {

            $tablas = array(
                "m" => "mensajes",
                "u" => "usuarios",
                "p" => "personas",
                "i" => "imagenes"
            );

            $columnas = array(
                "id" => "m.id",
                "idAutor" => "m.id_usuario_remitente",
                "usuarioAutor" => "u.usuario",
                "autor" => "u.sobrenombre",
                "fotoAutor" => "i.ruta",
                "titulo" => "m.titulo",
                "contenido" => "m.contenido",
                "fecha" => "UNIX_TIMESTAMP(m.fecha)",
                "leido" => "m.leido",
                "genero" => "p.genero",
                "fecha" => "m.fecha"
            );

            $condicion = "m.id_usuario_remitente = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND m.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->fotoAutor = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $this->fotoAutor;
            }
        }
    }

    /**
     *
     * Adicionar un mensaje
     *
     * @param  arreglo $datos       Datos del mensaje a adicionar
     * @return entero               Código interno o identificador del mensaje en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion;

        $datos["id_usuario_remitente"] = $sesion_usuarioSesion->id;
        $datos["fecha"] = date("Y-m-d G:i:s");
        $datos["leido"] = '0';

        $datos = array(
            "id_usuario" => $sesion_usuarioSesion->id,
            "contenido" => $datos["contenido"],
            "fecha" => date("Y-m-d H:i:s"),
            "activo" => "1"
        );

        $consulta = $sql->insertar("mensajes", $datos);
        $idConsulta = $sql->ultimoId;

        if ($consulta) {
            return $idConsulta;
        } else {
            return NULL;
        }
    }

    /**
     *
     * Eliminar un mensaje
     *
     * @param entero $id    Código interno o identificador del mensaje en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("mensajes", "id = '" . $this->id . "'");
        return $consulta;
    }

    /**
     *
     * Eliminar varios mensajes
     *
     * @param array datos   Códigos internos o identificadores delos mensajes en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminarVariosMensajes($datos) {
        global $sql;

        if (empty($datos)) {//datos me esta llegando como un string concatenado con comas
            return NULL;
        }

        $ids = explode(",", $datos);

//        foreach($datos as $dato){
//          $dato2 .= $dato; 
//          $consulta = $sql->eliminar("mensajes", "id = '".$dato."'");
//        }
//        Recursos::escribirTxt("dato: ".$dato2);


        for ($i = 0; $i < sizeof($ids); $i++) {
            $consulta = $sql->eliminar("mensajes", "id = '" . $ids[$i] . "'");
        }

        return $consulta;
    }

    /**
     *
     * Listar los mensajes de un registro en un módulo
     *
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de mensajes hechos al registro del módulo
     *
     */
    public function listar($inicio = 0, $cantidad = 0, $idUsuario) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        $tablas = array(
            "m" => "mensajes",
            "u" => "usuarios",
            "p" => "personas",
            "i" => "imagenes",
        );

        $columnas = array(
            "id" => "m.id",
            "idAutor" => "m.id_usuario_remitente",
            "usuario" => "u.usuario",
            "autor" => "u.sobrenombre",
            "fotoAutor" => "i.ruta",
            "titulo" => "m.titulo",
            "contenido" => "m.contenido",
            "fecha" => "UNIX_TIMESTAMP(m.fecha)",
            "leido" => "m.leido"
        );

        //$sql->depurar = true;
        $condicion = "m.id_usuario_remitente = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND m.id_usuario_destinatario = '" . $idUsuario . "'";

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", "fecha DESC", $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($mensaje = $sql->filaEnObjeto($consulta)) {
                $mensaje->fotoAutor = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $mensaje->fotoAutor;
                $lista[] = $mensaje;
            }
        }

        return $lista;
    }

//fin del metodo listar

    public function contarMensajesUsuario() {
        global $sql, $configuracion, $textos, $sesion_usuarioSesion;
        $sql->depurar = true;
        $cantidad = $sql->obtenerValor("mensajes", "COUNT(id)", "id_usuario_destinatario = " . $sesion_usuarioSesion->id);

        return $cantidad;
    }

}

