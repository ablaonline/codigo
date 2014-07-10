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
class Foro {

    /**
     * Código interno o identificador del foro en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de foros
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un foro específico
     * @var cadena
     */
    public $url;

    /**
     * URL relativa de un foro específico
     * @var cadena
     */
    public $enlace;

    /**
     * Código interno o identificador del módulo al cual pertenece el foro en la base de datos
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador del registro del módulo al cual pertenece el foro en la base de datos
     * @var entero
     */
    public $idRegistro;

    /**
     * Código interno o identificador del registro del módulo al cual pertenece el foro en la base de datos
     * @var entero
     */
    public $idModuloActual;

    /**
     * Código interno o identificador del usuario creador del foro en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Código interno o identificador de la categoria del foro en la base de datos
     * @var entero
     */
    public $idCategoria;

    /**
     * Categoria del foro en la base de datos
     * @var entero
     */
    public $categoria;

    /**
     * Nombre de usuario (login) del usuario creador del foro
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del foro
     * @var cadena
     */
    public $autor;

    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;

    /**
     * Título del foro
     * @var cadena
     */
    public $titulo;

    /**
     * Título del foro
     * @var cadena
     */
    public $descripcion;

    /**
     * Fecha de publicación del foro
     * @var fecha
     */
    public $fecha;

    /**
     * Indicador del estado del foro
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
     * Número de registros activos de la lista de foros
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * Número de mensajes de la lista
     * @var entero
     */
    public $mensajes = NULL;

    /**
     * Inicializar el foro
     * @param entero $id Código interno o identificador del foro en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('FOROS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;
        $consulta = $sql->filaEnObjeto($sql->seleccionar(array('foros'), array('registros' => 'COUNT(id)')));
        $this->registros = $consulta->registros;

        $consulta = $sql->filaEnObjeto($sql->seleccionar(array('foros'), array('registros' => 'COUNT(id)'), 'activo = "1"'));
        $this->registrosActivos = $consulta->registros;

        $consulta = $sql->filaEnObjeto($sql->seleccionar(array('mensajes_foro'), array('registros' => 'COUNT(id)'), 'id_foro = "' . $id . '"'));
        $this->mensajes = $consulta->registros;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos del foro
     * @param entero $id Código interno o identificador del foro en la base de datos
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (!empty($id) && $sql->existeItem('foros', 'id', intval($id))) {

            $tablas = array(
                'f' => 'foros',
                'c' => 'categoria',
                'u' => 'usuarios',
                'p' => 'personas',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id' => 'f.id',
                'idAutor' => 'f.id_usuario',
                'descripcion' => 'f.descripcion',
                'idModuloActual' => 'f.id_modulo',
                'idRegistro' => 'f.id_registro',
                'usuarioAutor' => 'u.usuario',
                'autor' => 'u.sobrenombre',
                'fotoAutor' => 'i.ruta',
                'titulo' => 'f.titulo',
                'fecha' => 'UNIX_TIMESTAMP(f.fecha)',
                'idCategoria' => 'c.id',
                'categoria' => 'c.nombre'
            );

            $condicion = 'f.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND f.id_categoria = c.id AND f.id = "' . $id . '"';
            //$sql->depurar = true;
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->enlace = '/' . $this->url . '/' . $id;
                $this->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->fotoAutor;
            }
        }
    }

    /**
     * Adicionar un foro
     * @param  arreglo $datos       Datos del foro a adicionar
     * @return entero               Código interno o identificador del foro en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion;

        if (!isset($sesion_usuarioSesion)) {
            return NULL;
        }
        $datosForo = array(
            'id_modulo' => $datos['idModulo'],
            'id_registro' => htmlspecialchars($datos['idRegistro']),
            'id_usuario' => htmlspecialchars($sesion_usuarioSesion->id),
            'descripcion' => htmlspecialchars($datos['contenido']),
            'titulo' => htmlspecialchars($datos['titulo']),
            'fecha' => date('Y-m-d H:i:s'),
            'activo' => '1'
        );

        $datosForo['tipo'] = '1';

        if (empty($datos['idRegistro'])) {
            $datosForo['tipo'] = '2';
            $datosForo['id_categoria'] = $datos['categorias'];
        }

        $consulta = $sql->insertar('foros', $datosForo);

        if ($consulta) {
            $idForo = $sql->ultimoId;
            // $consulta = $this->adicionarMensaje($idForo, );
            return $idForo;
        } else {
            return NULL;
        }
    }

    /**
     * Modificar un foro
     * @param  arreglo $datos       Datos del foro a modificar
     * @return entero               Código interno o identificador del foro en la base de datos (NULL si hubo error)
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion;

        if (!isset($sesion_usuarioSesion)) {
            return NULL;
        }
        $datosForo = array(
            'id_modulo' => htmlspecialchars($datos['idModulo']),
            'id_registro' => htmlspecialchars($datos['idRegistro']),
            'id_usuario' => $sesion_usuarioSesion->id,
            'descripcion' => htmlspecialchars($datos['contenido']),
            'titulo' => htmlspecialchars($datos['titulo']),
            'fecha' => date('Y-m-d H:i:s'),
            'activo' => '1'
        );

        $datosForo['tipo'] = '1';

        if (empty($datos['idRegistro'])) {
            $datosForo['tipo'] = '2';
            $datosForo['id_categoria'] = $datos['categorias'];
        }
        //$sql->depurar = true;
        $consulta = $sql->modificar('foros', $datosForo, 'id = "' . $datos['id'] . '"');

        if ($consulta) {
            return $consulta;
        } else {
            return NULL;
        }
    }

    /**
     * Adicionar un mensaje o respuesta en un foro
     * @param  entero   $id         Código interno o identificador del foro en la base de datos
     * @param  cadena   $contenido  Contenido del mensaje a adicionar
     * @return entero               Código interno o identificador del foro en la base de datos (NULL si hubo error)
     */
    public function adicionarMensaje($id, $contenido) {
        global $sql, $sesion_usuarioSesion;
        if(!isset($sesion_usuarioSesion)){
            return NULL;
        }
        $datos = array(
            'id_foro' => $id,
            'id_usuario' => $sesion_usuarioSesion->id,
            'contenido' => htmlspecialchars($contenido),
            'fecha' => date('Y-m-d H:i:s')
        );

        $consulta = $sql->insertar('mensajes_foro', $datos);

        if ($consulta) {
            return $sql->ultimoId;
        } else {
            return NULL;
        }
    }

    /**
     * Eliminar un foro
     * @param entero $id    Código interno o identificador del foro en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('mensajes_foro', 'id_foro = "' . $this->id . '"');

        $consulta = $sql->eliminar('foros', 'id = "' . $this->id . '"');

        return $consulta;
    }

    /**
     * Eliminar un mensaje de un foro
     * @param entero $id    Código interno o identificador del foro en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminarMessage($id_mensaje) {
        global $sql;

        if (!isset($id_mensaje)) {
            return NULL;
        }
        //$sql->depurar = true;
        $consulta = $sql->eliminar('mensajes_foro', 'id = "' . $id_mensaje . '"');

        return $consulta;
    }

    /**
     * Contar la cantidad de mensajes que tiene un determinado foro
     * */
    public function contarMensajes($idForo) {
        global $sql;
        if (!isset($idForo)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array('mensajes_foro'), array('registros' => 'COUNT(id)'), 'id_foro = "' . $idForo . '"');
        $arreglo = $sql->filaEnObjeto($consulta);

        if ($arreglo->registros != 0) {
            $numMensajes = $arreglo->registros;
        } else {
            $numMensajes = ' 0 ';
        }

        return $numMensajes;
    }

    /**
     * Contar la cantidad de foros de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return entero              Número de foros hechos al registro del módulo
     */
    public function contar($modulo, $registro) {
        global $sql;

        $tablas = array(
            'f' => 'foros',
            'm' => 'modulos'
        );

        $columnas = array(
            'registros' => 'COUNT(f.id)'
        );

        $condicion = 'f.id_modulo = m.id AND f.id_registro = "' . $registro . '" AND m.nombre = "' . $modulo . '" AND f.activo = "1"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $foro = $sql->filaEnObjeto($consulta);
            return $foro->registros;
        } else {
            return NULL;
        }
    }

    /**
     * Listar los foros de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de foros hechos al registro del módulo
     */
    public function listar($modulo, $registro) {
        global $sql, $configuracion;


        $tablas = array(
            'f' => 'foros',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes',
            'm' => 'modulos'
        );

        $columnas = array(
            'id' => 'f.id',
            'idAutor' => 'f.id_usuario',
            'activo' => 'f.activo',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'titulo' => 'f.titulo',
            'fecha' => 'UNIX_TIMESTAMP(f.fecha)'
        );

        $condicion = 'f.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND f.id_modulo = m.id AND f.id_registro = "' . $registro . '" AND m.nombre = "' . $modulo . '" AND f.activo = "1"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', 'fecha DESC');

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($foro = $sql->filaEnObjeto($consulta)) {
                $foro->url = $this->urlBase . '/' . $foro->id;
                $foro->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $foro->fotoAutor;
                $lista[] = $foro;
            }
        }

        return $lista;
    }

    /**
     * Listar los mensajes de un foro
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de foros hechos al registro del módulo
     */
    public function listarMensajes() {
        global $sql, $configuracion;


        $tablas = array(
            'f' => 'mensajes_foro',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'f.id',
            'idAutor' => 'f.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'contenido' => 'f.contenido',
            'fecha' => 'UNIX_TIMESTAMP(f.fecha)'
        );

        $condicion = 'f.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND f.id_foro = "' . $this->id . '"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', 'fecha ASC');

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($foro = $sql->filaEnObjeto($consulta)) {
                $foro->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $foro->fotoAutor;
                $lista[] = $foro;
            }
        }

        return $lista;
    }

    /**
     *
     * Listar los foros existentes
     *
     * @param  cadena $modulo      Nombre
     * @return arreglo             Lista de foros hechos al registro del módulo
     *
     */
    public function listarForos($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $filtroCategoria = NULL) {
        global $sql, $sesion_usuarioSesion;

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
            $condicion .= 'f.id NOT IN (' . $excepcion . ')';
        }


        $tablas = array(
            'f' => 'foros',
            'c' => 'categoria',
            'u' => 'usuarios',
            'p' => 'personas'
        );

        $columnas = array(
            'id' => 'f.id',
            'idAutor' => 'f.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'activo' => 'f.activo',
            'autor' => 'u.sobrenombre',
            'titulo' => 'f.titulo',
            'fecha' => 'UNIX_TIMESTAMP(f.fecha)',
            'idCategoria' => 'c.id',
            'categoria' => 'c.nombre'
        );

        $condicion = 'f.id_usuario = u.id AND u.id_persona = p.id AND f.id_categoria = c.id AND f.tipo = 2 ';

        if (!empty($filtroCategoria)) {

            if ($filtroCategoria == 'my_item') {
                $condicion .= ' AND f.id_usuario = "' . $sesion_usuarioSesion->id . '"'; //filtro de categoria
            } else {
                $condicion .= ' AND f.id_categoria = "' . $filtroCategoria . '"'; //filtro de categoria
            }
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', 'fecha DESC');

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($foro = $sql->filaEnObjeto($consulta)) {
                $foro->url = $this->urlBase . '/' . $foro->id;

                $lista[] = $foro;
            }
        }

        return $lista;
    }

}

?>