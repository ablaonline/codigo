<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Anuncio
 * @author      Pablo A. Vélez <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 *
 * */
class Anuncio {

    /**
     * Código interno o identificador del anuncio en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de anuncio
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un anuncio específico
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador de la categoria a la cual pertenece el anuncio
     * @var entero
     */
    public $idCategoria;

    /**
     * Título del anuncio
     * @var cadena
     */
    public $titulo;

    /**
     * Título del anuncio
     * @var cadena
     */
    public $descripcion;

    /**
     * Código interno o identificador en la base de datos de la imagen relacionada con el anuncio
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen del anuncio en tamaño normal
     * @var cadena
     */
    public $imagenPrincipal;

    /**
     * Fecha de creación del anuncio
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación inicial del anuncio
     * @var fecha
     */
    public $fechaInicial;

    /**
     * Fecha de publicación final del anuncio
     * @var fecha
     */
    public $fechaFinal;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de Anuncios
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
     * Inicializar el Anuncio
     * @param entero $id Código interno o identificador del anuncio en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('ANUNCIOS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;


        $consulta = $sql->filaEnObjeto($sql->seleccionar(array('anuncios'), array('registros' => 'COUNT(id)')));
        $this->registros = $consulta->registros;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos del anuncio
     *
     * @param entero $id Código interno o identificador del anuncio en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem('anuncios', 'id', intval($id))) {

            $tablas = array(
                'a' => 'anuncios',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id' => 'a.id',
                'idImagen' => 'a.id_imagen',
                'imagen' => 'i.ruta',
                'titulo' => 'a.titulo',
                'descripcion' => 'a.descripcion',
                'vinculo' => 'a.vinculo',
                'fechaCreacion' => 'UNIX_TIMESTAMP(a.fecha_creacion)',
                'fechaInicial' => 'UNIX_TIMESTAMP(a.fecha_inicial)',
                'fechaFinal' => 'UNIX_TIMESTAMP(a.fecha_final)',
                'activo' => 'a.activo'
            );

            $condicion = 'a.id_imagen = i.id AND a.id = "'.$id.'"';
            //$sql->depurar = true;
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->usuario;
                $this->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $this->imagen;
            }
        }
    }

    /**
     *
     * Adicionar un anuncio
     *
     * @param  arreglo $datos       Datos del anuncio a adicionar
     * @return entero               Código interno o identificador del anuncio en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $archivo_imagen;

        if (isset($datos['activo'])) {
            $datos['activo'] = '1';
            $datos['fecha_inicial'] = date('Y-m-d H:i:s');

            $activo = array('activo' => '0');
            // $sql->depurar = true;
            $consulta = $sql->modificar('anuncios', $activo, 'id IS NOT NULL');
        } else {
            $datos['activo'] = '0';
            $datos['fecha_publicacion'] = NULL;
        }

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            $objetoImagen = new Imagen();

            $datosImagen = array(
                'idRegistro' => '',
                'modulo' => 'ANUNCIOS',
                'descripcion' => 'Image of' . htmlspecialchars($datos['titulo']),
                'titulo' => 'Image of' . htmlspecialchars($datos['titulo'])
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }

        $datosAnuncio = array(
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => htmlspecialchars($datos['descripcion']),
            'vinculo' => htmlspecialchars($datos['vinculo']),
            'id_imagen' => $idImagen,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'fecha_inicial' => htmlspecialchars($datos['fecha_inicial']),
            'activo' => $datos['activo']
        );

        $consulta = $sql->insertar('anuncios', $datosAnuncio);

        if ($consulta) {
            return $sql->ultimoId;
        } else {
            return FALSE;
        }
    }


    /**
     * Modificar un anuncio
     * @param  arreglo $datos       Datos del anuncio  a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
        }

        if (isset($datos['activo'])) {
            $datos['activo'] = '1';
            $datos['fecha_inicial'] = date('Y-m-d H:i:s');

            $fechaFinal = array('fecha_final' => date('Y-m-d H:i:s'));
            $consulta = $sql->modificar('anuncios', $fechaFinal, 'activo = "1"');

            $activo = array('activo' => '0');
            $consulta = $sql->modificar('anuncios', $activo, 'id IS NOT NULL');
        } else {
            $datos['activo'] = '0';
            $datos['fecha_inicial'] = NULL;
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
                'modulo' => 'ANUNCIOS',
                'titulo' => 'Image of ' . htmlspecialchars($datos['titulo']),
                'descripcion' => 'Image of ' . htmlspecialchars($datos['titulo'])
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }

        $datos = array(
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => htmlspecialchars($datos['descripcion']),
            'vinculo' => htmlspecialchars($datos['vinculo']),
            'fecha_inicial' => htmlspecialchars($datos['fecha_inicial']),
            'id_imagen' => $idImagen,
            'activo' => $datos['activo']
        );


        $consulta = $sql->modificar('anuncios', $datos, 'id = "' . $this->id . '"');

        if ($consulta) {
            return TRUE;
        } else {
            return FALSE;
        }
    }


    /**
     * Eliminar un anuncio
     * @param entero $id    Código interno o identificador del anuncio  en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('anuncios', 'id = "' . $this->id . '"');

        if (!($consulta)) {
            return false;
        } else {
            $objetoImagen = new Imagen($this->idImagen);
            $objetoImagen->eliminar();
            return true;
        }
    }

    /**
     * Listar los anuncios
     * @param entero  $cantidad    Número de anuncios a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de anuncios
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
            $condicion .= 'a.id NOT IN ('.$excepcion.')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'a.fecha_creacion ASC';
        } else {
            $orden = 'a.fecha_creacion ASC';
        }


        $tablas = array(
            'a' => 'anuncios',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'a.id',
            'idImagen' => 'a.id_imagen',
            'imagen' => 'i.ruta',
            'titulo' => 'a.titulo',
            'descripcion' => 'a.descripcion',
            'vinculo' => 'a.vinculo',
            'fechaCreacion' => 'UNIX_TIMESTAMP(a.fecha_creacion)',
            'fechaInicial' => 'UNIX_TIMESTAMP(a.fecha_inicial)',
            'fechaFinal' => 'UNIX_TIMESTAMP(a.fecha_final)',
            'activo' => 'a.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }
        $condicion .= 'a.id_imagen = i.id';


        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($anuncio = $sql->filaEnObjeto($consulta)) {
                $anuncio->url = $this->urlBase . '/' . $anuncio->id;
                $anuncio->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $anuncio->imagen;

                $lista[] = $anuncio;
            }
        }

        return $lista;
    }


    /**
     * Metodo que retorna un div con el anuncio que esta establecido como activo
     * */
    public static function mostrarAnuncio() {
        global $sql, $configuracion;

        $codigo = '';
        $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/';
        $rutaAlterna = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/00000001.gif';

        $tablas = array(
            'a' => 'anuncios',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'a.id',
            'idImg' => 'a.id_imagen',
            'vinculo' => 'a.vinculo',
            'idImagen' => 'i.id',
            'ruta' => 'i.ruta',
            'activo' => 'a.activo'
        );

        $condicion = 'a.id_imagen = i.id AND a.activo = "1"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
        $anuncio = $sql->filaEnObjeto($consulta);

        if ($sql->filasDevueltas) {
            $imagen = HTML::imagen($ruta . $anuncio->ruta, 'claseBanner');
            $vinculo = $anuncio->vinculo;
        } else {
            $imagen = HTML::imagen($rutaAlterna, 'claseBanner');
            $vinculo = 'http://www.colomboamericano.edu.co';
        }

        $enlace = HTML::enlace($imagen, $vinculo);
        $codigo .= HTML::contenedor($enlace, 'claseAnuncio');
        
        return $codigo;
    }

}

?>
