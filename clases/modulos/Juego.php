<?php

/**
 * @package     FOLCS
 * @subpackage  Juegos
 * @author      Pablo Andrés Vélez Vidal
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 Colombo-Americano
 * @version     0.1
 * */
class Juego {

    /**
     * Código interno o identificador del juego en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de juegos
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un juego específica
     * @var cadena
     */
    public $url;

    /**
     * Nombre del juego
     * @var cadena
     */
    public $nombre;

    /**
     * Script para insertar (embeber) el juego en la página
     * @var cadena
     */
    public $script;

    /**
     * Descripción del juego
     * @var cadena
     */
    public $descripcion;

    /**
     * Código interno o identificador en la base de datos de la imagen relacionada con el juego
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen del juego en tamaño normal
     * @var cadena
     */
    public $imagen;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Número de registros activos de la lista de foros
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * Indicador del orden cronológio de la lista de noticias
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Número de comentarios que tiene este item 
     * @var entero
     */
    public $cantidadComentarios = NULL;

    /**
     * Número de me gusta que tiene este item 
     * @var entero
     */
    public $cantidadMeGusta = NULL;

    /**
     * Inicializar el juego
     * @param entero $id Código interno o identificador del juego en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('JUEGOS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        $this->registros = $sql->obtenerValor('juegos', 'COUNT(id)', 'id != "0"');

        $this->registrosActivos = $sql->obtenerValor('juegos', 'COUNT(id)', 'activo = "1"');

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos de un juego
     * @param entero $id Código interno o identificador del juego en la base de datos
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (!empty($id) && $sql->existeItem('juegos', 'id', intval($id))) {

            $tablas = array(
                'j' => 'juegos',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id' => 'j.id',
                'idImagen' => 'j.id_imagen',
                'imagen' => 'i.ruta',
                'script' => 'j.script',
                'nombre' => 'j.nombre',
                'descripcion' => 'j.descripcion',
                'activo' => 'j.activo',
                'fechaPublicacion' => 'j.fecha_publicacion'
            );

            $condicion = 'j.id_imagen = i.id AND j.id = "'.$id.'"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->id;
                $this->imagen = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $this->imagen;
                //sumar una visita a la noticia
                $this->sumarVisita();
            }
        }
    }

    /**
     * Adicionar un juego
     * @param  arreglo $datos       Datos del juego a adicionar
     * @return entero               Código interno o identificador del juego en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $archivo_imagen;

        if (empty($datos)) {
            return NULL;
        }

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            $objetoImagen = new Imagen();

            $datosImagen = array(
                'idRegistro' => '',
                'modulo' => 'JUEGOS',
                'descripcion' => 'Image of' . htmlspecialchars($datos['nombre']),
                'titulo' => 'Image of' . htmlspecialchars($datos['nombre'])
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }

        $datos = array(
            'nombre' => $datos['nombre'],
            'script' => $datos['script'],
            'descripcion' => $datos['descripcion'],
            'id_imagen' => $idImagen,
            'fecha_publicacion' => date('Y-m-d H:i:s')
        );


        $consulta = $sql->insertar('juegos', $datos);

        if ($consulta) {
            return $sql->ultimoId;
        } else {
            return false;
        }
    }

    /**
     * Modificar un juego
     * @param  arreglo $datos       Datos del juego a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
        }

        $idImagen = $this->idImagen;

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            if ($this->idImagen == '8') {
                $objetoImagen = new Imagen();
            } else {
                $objetoImagen = new Imagen($this->idImagen);
                $objetoImagen->eliminar();
            }

            $datosImagen = array(
                'idRegistro' => $this->id,
                'modulo' => 'JUEGOS',
                'titulo' => 'Image of ' . htmlspecialchars($datos['nombre']),
                'descripcion' => 'Image of ' . htmlspecialchars($datos['nombre'])
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }

        $datos = array(
            'nombre' => $datos['nombre'],
            'script' => $datos['script'],
            'descripcion' => $datos['descripcion'],
            'id_imagen' => $idImagen
        );

        $consulta = $sql->modificar('juegos', $datos, 'id = "' . $this->id . '"');

        return $consulta;
    }

    /**
     * Eliminar un juego
     * @param entero $id    Código interno o identificador del juego en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('juegos', 'id = "' . $this->id . '"');

        if ($consulta) {
            $objetoImagen = new Imagen($this->idImagen);
            $objetoImagen->eliminar();

            $comentario = new Comentario();
            $comentario->eliminarComentarios($this->id, $this->idModulo);
        }

        return $consulta;
    }

    /**
     * Listar las juegos
     * @param entero  $cantidad    Número de juegos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de juegos
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
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'j.id NOT IN ('.$excepcion.')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'j.fecha_publicacion ASC';
        } else {
            $orden = 'j.fecha_publicacion DESC';
        }

        $tablas = array(
            'j' => 'juegos',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'j.id',
            'idImagen' => 'j.id_imagen',
            'imagen' => 'i.ruta',
            'nombre' => 'j.nombre',
            'descripcion' => 'j.descripcion',
            'fechaPublicacion' => 'j.fecha_publicacion'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'j.id_imagen = i.id';

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($juego = $sql->filaEnObjeto($consulta)) {
                $juego->url = $this->urlBase . '/' . $juego->id;
                $juego->imagen = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $juego->imagen;
                $lista[] = $juego;
            }
        }

        return $lista;
    }

    /**
     * Método encargado de sumar en uno el registro de visitas al juego
     * @global type $sql
     * @return null|boolean 
     */
    public function sumarVisita() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $numVisitas = $sql->obtenerValor('juegos', 'visitas', 'id = "' . $this->id . '"');

        $datosJuego['visitas'] = $numVisitas + 1;

        $sql->guardarBitacora = false;
        $sumVisita = $sql->modificar('juegos', $datosJuego, 'id = "' . $this->id . '"');

        if ($sumVisita) {
            return true;
        } else {
            return false;
        }
    }


}

