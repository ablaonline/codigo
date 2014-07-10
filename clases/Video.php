<?php

/**
 * @package     FOLCS
 * @subpackage  Base
 * @author      Pablo Andrés Vélez Vidal. <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 Colombo Americano Cali
 * @version     0.1
 * */
class Video {

    /**
     * Código interno o identificador del video en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa de un video específico
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del módulo al cual pertenece el video en la base de datos
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador del registro del módulo al cual pertenece el video en la base de datos
     * @var entero
     */
    public $idRegistro;

    /**
     * Código interno o identificador del usuario creador del video en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Nombre de usuario (login) del usuario creador del video
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del video
     * @var cadena
     */
    public $autor;

    /**
     * UrlBase del modulo actual en el que estamos
     */
    public $urlBase;

    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;

    /**
     * Título del video
     * @var cadena
     */
    public $titulo;

    /**
     * Descripción corta del video
     * @var cadena
     */
    public $descripcion;

    /**
     * Indicador del estado del video
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de temas
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Inicializar el video
     * @param entero $id Código interno o identificador del video en la base de datos
     */
    public function __construct($id = NULL) {

        $modulo = new Modulo('VIDEO');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos del video
     * @param entero $id Código interno o identificador del video en la base de datos
     */
    public function cargar($id) {
        global $sql;

        if (isset($id) && $sql->existeItem('videos', 'id', intval($id))) {

            $tablas = array(
                'a' => 'videos',
                'u' => 'usuarios',
                'p' => 'personas',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id' => 'a.id',
                'idAutor' => 'a.id_usuario',
                'usuarioAutor' => 'u.usuario',
                'autor' => 'u.sobrenombre',
                'fotoAutor' => 'i.ruta',
                'titulo' => 'a.titulo',
                'descripcion' => 'a.descripcion',
                'ruta' => 'a.ruta'
            );

            $condicion = 'a.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND a.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->fotoAutor;
            }
        }
    }

    /**
     * Adicionar un video
     * @param  arreglo $datos       Datos del video a adicionar
     * @return entero               Código interno o identificador del video en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $textos;

        $datosRecurso = array(
            'id_modulo' => htmlspecialchars($datos['idModulo']),
            'id_registro' => htmlspecialchars($datos['idRegistro']),
            'id_usuario' => $sesion_usuarioSesion->id,
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => htmlspecialchars($datos['descripcion']),
            'ruta' => ' ',
            'fecha' => date('Y-m-d H:i:s'),
            'enlace' => htmlspecialchars($datos['enlace'])
        );
        $consulta = $sql->insertar('videos', $datosRecurso);

        if ($consulta) {

            $mod = $sql->obtenerValor('modulos', 'nombre', 'id = "' . $datos['idModulo'] . '"');
            if ($mod == 'CURSOS' && isset($datos['notificar_estudiantes'])) {
                $idCurso = $datos['idRegistro'];
                $objetoCurso = new Curso($idCurso);
                $consultaSeguidores = $sql->seleccionar(array('cursos_seguidos'), array('id', 'id_usuario'), 'id_curso = "' . $idCurso . '"', '');
                if ($sql->filasDevueltas) {
                    $tipoItem = $textos->id('VIDEO');
                    $nombreItem = $datos['titulo'];
                    while ($seguidor = $sql->filaEnObjeto($consultaSeguidores)) {
                        $notificacion = str_replace('%1', HTML::enlace($objetoCurso->autor, HTML::urlInterna('CURSOS', $idCurso)), $textos->id('MENSAJE_ADICION_ITEM_CURSO'));
                        $notificacion = str_replace('%2', HTML::enlace($tipoItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion);
                        $notificacion = str_replace('%3', HTML::enlace($objetoCurso->nombre, HTML::urlInterna('CURSOS', $idCurso)), $notificacion);
                        $notificacion = str_replace('%4', HTML::enlace($nombreItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion);

                        Servidor::notificar($seguidor->id_usuario, $notificacion, array(), '10');
                    }
                }
            }
            return $sql->ultimoId;
        } else {
            return NULL;
        }
    }

    /**
     * Eliminar un video
     * @param entero $id    Código interno o identificador del video en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('videos', 'id = "' . $this->id . '"');
        return $consulta;
    }

    /**
     * Contar la cantidad de videos de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return entero              Número de videos hechos al registro del módulo
     */
    public function contar($modulo, $registro) {
        global $sql;

        $tablas = array(
            'a' => 'videos',
            'm' => 'modulos'
        );

        $columnas = array(
            'registros' => 'COUNT(a.id)'
        );

        $condicion = 'a.id_modulo = m.id AND a.id_registro = "' . htmlspecialchars($registro) . '" AND m.nombre = "' . htmlspecialchars($modulo) . '"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $video = $sql->filaEnObjeto($consulta);
            return $video->registros;
        } else {
            return NULL;
        }
    }

    /**
     * Listar los videos de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de videos hechos al registro del módulo
     */
    public function listar($inicio = 0, $cantidad = 0, $modulo = NULL, $registro = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        $tablas = array(
            'a' => 'videos',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes',
            'm' => 'modulos'
        );

        $columnas = array(
            'id' => 'a.id',
            'idAutor' => 'a.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'titulo' => 'a.titulo',
            'descripcion' => 'a.descripcion',
            'ruta' => 'a.ruta',
            'fecha' => 'a.fecha',
            'enlace' => 'a.enlace'
        );

        $condicion = 'a.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND a.id_modulo = m.id AND a.id_registro = "' . htmlspecialchars($registro) . '" AND m.nombre = "' . ($modulo) . '"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', 'fecha DESC', $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($video = $sql->filaEnObjeto($consulta)) {
                $video->url = $this->urlBase . '/' . $video->id;
                $video->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $video->fotoAutor;
                $video->ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['video'] . '/' . $video->ruta;
                $lista[] = $video;
            }
        }

        return $lista;
    }

}

