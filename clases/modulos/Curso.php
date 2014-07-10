<?php

/**
 * @package     FOLCS
 * @subpackage  Cursos
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * */
class Curso {

    /**
     * Código interno o identificador del curso en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de cursos
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un curso específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador del curso en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador de la categoria a la cual pertenece la noticia
     * @var entero
     */
    public $idCategoria;

    /**
     * Nombre de usuario (login) del usuario creador del curso en la base de datos
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del curso
     * @var cadena
     */
    public $autor;

    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;

    /**
     * Título del curso
     * @var cadena
     */
    public $nombre;

    /**
     * Descripción del curso
     * @var cadena
     */
    public $descripcion;

    /**
     * Contenido completo del curso
     * @var cadena
     */
    public $contenido;

    /**
     * Fecha de creación del curso
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación del curso
     * @var fecha
     */
    public $fechaPublicacion;

    /**
     * Fecha de la última modificación del curso
     * @var fecha
     */
    public $fechaActualizacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de cursos
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Número de registros Activos de la lista
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * Codigos de los perfiles de usuario con los cuales es compartido un Blog
     * @var array
     */
    public $permisosPerfiles = array();

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
     * Inicializar el curso
     * @param entero $id Código interno o identificador del curso en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('CURSOS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        $this->registros = $sql->obtenerValor('cursos', 'count(id)', 'id != "0"');
        
        $this->registrosActivos = $sql->obtenerValor('cursos', 'count(id)', 'activo = "1"');

        if (isset($id)) {
            $this->cargar($id);
            $this->permisosPerfiles = PermisosItem::cargarPerfiles($id, $modulo->id);
        }
    }

    /**
     * Cargar los datos de un curso
     * @param entero $id Código interno o identificador del curso en la base de datos
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (is_numeric($id) && $sql->existeItem('cursos', 'id', intval($id))) {

            $tablas = array(
                'c' => 'cursos',
                'u' => 'usuarios',
                'p' => 'personas',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id' => 'c.id',
                'idAutor' => 'c.id_usuario',
                'usuarioAutor' => 'u.usuario',
                'autor' => 'u.sobrenombre',
                'fotoAutor' => 'i.ruta',
                'nombre' => 'c.nombre',
                'descripcion' => 'c.descripcion',
                'contenido' => 'c.contenido',
                'fechaCreacion' => 'UNIX_TIMESTAMP(c.fecha_creacion)',
                'fechaPublicacion' => 'UNIX_TIMESTAMP(c.fecha_publicacion)',
                'fechaActualizacion' => 'UNIX_TIMESTAMP(c.fecha_actualizacion)',
                'idCategoria' => 'c.id_categoria',
                'activo' => 'c.activo'
            );

            $condicion = 'c.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND c.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->usuario;
                $this->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->fotoAutor;
                //sumar una visita al curso
                $this->sumarVisita();
            }
        }
    }

    /**
     * Adicionar un curso
     * @param  arreglo $datos       Datos del curso a adicionar
     * @return entero               Código interno o identificador del curso en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo;

        $datosCurso = array();

        $datosCurso['nombre'] = htmlspecialchars($datos['nombre']);
        $datosCurso['descripcion'] = htmlspecialchars($datos['descripcion']);
        $datosCurso['contenido'] = Variable::filtrarTagsInseguros($datos['contenido']);
        $datosCurso['id_categoria'] = htmlspecialchars($datos['categorias']);
        $datosCurso['id_usuario'] = $sesion_usuarioSesion->id;
        $datosCurso['fecha_creacion'] = date('Y-m-d H:i:s');
        $datosCurso['fecha_actualizacion'] = date('Y-m-d H:i:s');

        //nuevos datos que recibo sobre los perfiles con los que comparto y la visibilidad
        $datosPerfiles = $datos['perfiles'];
        $datosVisibilidad = $datos['visibilidad'];

        if (isset($datos['activo'])) {
            $datosCurso['activo'] = '1';
            $datosCurso['fecha_publicacion'] = date('Y-m-d H:i:s');
        } else {
            $datosCurso['activo'] = '0';
            $datosCurso['fecha_publicacion'] = NULL;
        }

        $consulta = $sql->insertar('cursos', $datosCurso);

        if ($consulta) {
            $idItem = $sql->ultimoId;
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;
            $permisosItem->insertarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);

            return $idItem;
        } else {
            return NULL;
        }
    }

    /**
     * Modificar un curso
     * @param  arreglo $datos       Datos de la curso a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $modulo;

        if (!isset($this->id)) {
            return NULL;
        }

        $datosCurso = array();

        $datosCurso['nombre'] = htmlspecialchars($datos['nombre']);
        $datosCurso['descripcion'] = htmlspecialchars($datos['descripcion']);
        $datosCurso['contenido'] = Variable::filtrarTagsInseguros($datos['contenido']);
        $datosCurso['id_categoria'] = htmlspecialchars($datos['categorias']);
        $datosCurso['fecha_actualizacion'] = date('Y-m-d H:i:s');
//nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfiles = $datos['perfiles'];
        $datosVisibilidad = $datos['visibilidad'];


        if (isset($datos['activo'])) {
            $datosCurso['activo'] = '1';
            $datosCurso['fecha_publicacion'] = date('Y-m-d H:i:s');
        } else {
            $datosCurso['activo'] = '0';
            $datosCurso['fecha_publicacion'] = NULL;
        }
        //$sql->depurar = true;
        $consulta = $sql->modificar('cursos', $datosCurso, 'id = "' . $this->id . '"');

        if ($consulta) {
//codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;
            $idItem = $this->id;
            $permisosItem->modificarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);

            return 1;
        } else {

            return NULL;
        }//fin del if(consulta)
    }

    /**
     * Eliminar un curso
     * @param entero $id    Código interno o identificador del curso en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        /* Elimino las imagenes que tenga el curso */
        $consultaImg = $sql->seleccionar(array('imagenes'), array('id' => 'id'), 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
        if ($sql->filasDevueltas) {
            while ($imagenes = $sql->filaEnObjeto($consultaImg)) {
                $img = new Imagen($imagenes->id);
                $img->eliminar();
            }
        }

        /* Elimino los documentos que tenga el curso */
        $consultaDoc = $sql->seleccionar(array('documentos'), array('id'), 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
        if ($sql->filasDevueltas) {
            while ($documentos = $sql->filaEnObjeto($consultaDoc)) {
                $doc = new Documento($documentos->id);
                $doc->eliminar();
            }
        }

        /* Elimino los Audios que tenga el curso */
        $audios = $sql->filaEnObjeto($sql->seleccionar(array('audios'), array('id'), 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"'));
        if ($sql->filasDevueltas) {
            foreach ($audios as $audio) {
                $aud = new Audio($audio->id);
                $aud->eliminar();
            }
        }

        /* Elimino los Videos que tenga el curso */
        $videos = $sql->filaEnObjeto($sql->seleccionar(array('videos'), array('id'), 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"'));
        if ($sql->filasDevueltas) {
            foreach ($videos as $video) {
                $vid = new Video($video->id);
                $vid->eliminar();
            }
        }

        /* Elimino los foros y los mensajes que tenga relacionado el curso */
        $consultaForo = $sql->seleccionar(array('foros'), array('id'), 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
        if ($sql->filasDevueltas) {
            while ($foros = $sql->filaEnObjeto($consultaForo)) {
                $consulta = $sql->eliminar('mensajes_foro', 'id_foro = "' . $foros->id . '"');
            }
        }
        $sql->eliminar('foros', 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');

        /* Elimino los seguidores que tenga relacionado el curso */
        $sql->eliminar('cursos_seguidos', ' id_curso= "' . $this->id . '"');


        if (!($consulta = $sql->eliminar('cursos', 'id = "' . $this->id . '"'))) {

            return false;
        } else {
            $permisosItem = new PermisosItem();
            //eliminar los permisos del item
            if (!($permisosItem->eliminar($this->id, $this->idModulo))) {
                return false;
            } else {
                return true;
            }

            return true;
        }//fin del si funciono eliminar       
    }

    /**
     * Seguir un curso
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     * */
    public function seguir() {
        global $sql, $sesion_usuarioSesion;

        if (!isset($this->id)) {
            return NULL;
        }

        $datosCurso = array();
        $datosCurso['id_curso'] = $this->id;
        $datosCurso['id_usuario'] = $sesion_usuarioSesion->id;
        $consulta = $sql->insertar('cursos_seguidos', $datosCurso);
        return $consulta;
    }

    /**
     * Abandonar un curso
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     * */
    public function abandonar() {
        global $sql, $sesion_usuarioSesion;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('cursos_seguidos', 'id_curso = "' . $this->id . '" AND id_usuario = "' . $sesion_usuarioSesion->id . '"');
        return $consulta;
    }

    /**
     * Listar los cursos
     * @param entero  $cantidad    Número de cursos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de cursos
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfil = NULL, $idModulo = NULL, $filtroCategoria = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        $lista = array();

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
            $condicion .= 'c.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'c.fecha_publicacion ASC';
        } else {
            $orden = 'c.fecha_publicacion DESC';
        }

        $tablas = array(
            'c' => 'cursos',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'c.id',
            'idAutor' => 'c.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
	    'genero' => 'p.genero',
            'nombre' => 'c.nombre',
            'descripcion' => 'c.descripcion',
            'fechaCreacion' => 'UNIX_TIMESTAMP(c.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(c.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(c.fecha_actualizacion)',
            'activo' => 'c.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'c.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id';
        //filtro de categoria
        if (!empty($filtroCategoria)) {
            $filtroCategoria = htmlspecialchars($filtroCategoria);
            if ($filtroCategoria == 'my_item') {
                $condicion .= ' AND c.id_usuario = "' . $sesion_usuarioSesion->id . '"'; //filtro de categoria
            } else {
                $condicion .= ' AND c.id_categoria = "' . $filtroCategoria . '"'; //filtro de categoria
            }
        }

        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {

                //codigo para perfiles que pueden ver items de varios perfiles
                $condicion2 = "";
                $tienePrivilegios = $sql->obtenerValor('tipos_usuario', 'otros_perfiles', 'id = "' . $sesion_usuarioSesion->idTipo . '"');
                if (isset($sesion_usuarioSesion) && $tienePrivilegios) {
                    $otrosPerfiles = implode(',', Perfil::verOtrosPerfiles());
                    $condicion2 = ", $otrosPerfiles "; //condicion extra para ver items de los otros perfiles sobre los cuales tiene privilegios                  
                }

                $tablas["pi"] = "permisos_item";
                $columnas["idItem"] = "pi.id_item";
                $columnas["idPerfil"] = "pi.id_perfil";
                $columnas["idModulo"] = "pi.id_modulo";

                $condicion .= " AND pi.id_item = c.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil'$condicion2)";
                $condicion .= "OR ( c.id_usuario = '$sesion_usuarioSesion->id' AND c.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND pi.id_item = c.id AND pi.id_modulo = '" . $idModulo . "'";
                if (!empty($filtroCategoria)) {
                    if ($filtroCategoria == 'my_item') {
                        $condicion .= ' AND c.id_usuario = "' . $sesion_usuarioSesion->id . '"'; //filtro de categoria
                    } else {
                        $condicion .= ' AND c.id_categoria = "' . $filtroCategoria . '"'; //filtro de categoria
                    }
                }
                $condicion .= ')';
            }
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= ' AND pi.id_item = c.id AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" )';
        }

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'c.id', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {

            while ($curso = $sql->filaEnObjeto($consulta)) {
                $curso->url = $this->urlBase . '/' . $curso->id;
                $curso->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $curso->fotoAutor;
                $lista[] = $curso;
            }
        }

        return $lista;
    }

    /**
     * Listar los cursos que sigue el usuario que ha iniciado la sesion
     * @param entero  $cantidad    Número de cursos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de cursos
     */
    public function listarCursosQueSigo($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        $lista = array();

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
            $condicion .= 'b.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'b.fecha_publicacion ASC';
        } else {
            $orden = 'b.fecha_publicacion DESC';
        }

        $tablas = array(
            'b' => 'cursos',
            'c' => 'cursos_seguidos',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'b.id',
            'idAutor' => 'b.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'nombre' => 'b.nombre',
            'descripcion' => 'b.descripcion',
            'fechaCreacion' => 'UNIX_TIMESTAMP(b.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(b.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(b.fecha_actualizacion)',
            'activo' => 'b.activo',
            'cursos_seguidos' => 'c.id_curso'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND b.id = c.id_curso AND c.id_usuario = "' . $sesion_usuarioSesion->id . '"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {

            while ($curso = $sql->filaEnObjeto($consulta)) {
                $curso->url = $this->urlBase . '/' . $curso->id;
                $curso->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $curso->fotoAutor;
                $lista[] = $curso;
            }
        }

        return $lista;
    }

    /**
     * Listar los cursos que dicta el usuario que ha iniciado la sesion
     * @param entero  $cantidad    Número de cursos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de cursos
     */
    public function listarCursosQueDicto($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $usuario = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        $lista = array();

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
            $excepcion = implode(',', $excepcion);
            $condicion .= 'b.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'b.fecha_publicacion ASC';
        } else {
            $orden = 'b.fecha_publicacion DESC';
        }

        if (isset($usuario)) {
            $usuario = $usuario;
        } else {
            $usuario = $sesion_usuarioSesion;
        }

        $tablas = array(
            'b' => 'cursos',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'b.id',
            'idAutor' => 'b.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'nombre' => 'b.nombre',
            'descripcion' => 'b.descripcion',
            'fechaCreacion' => 'UNIX_TIMESTAMP(b.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(b.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(b.fecha_actualizacion)',
            'activo' => 'b.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND b.id_usuario = "' . $usuario->id . '"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {

            while ($curso = $sql->filaEnObjeto($consulta)) {
                $curso->url = $this->urlBase . '/' . $curso->id;
                $curso->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $curso->fotoAutor;
                $lista[] = $curso;
            }
        }

        return $lista;
    }   

    /**
     * Metodo que muestra y lista los cursos que sigue el ususario que ha iniciado sesion 
     * */
    public function cursosQueSigo() {
        global $configuracion, $textos;

        $bloqueCursos = '';
        $arregloCursos = self::listarCursosQueSigo(0, 5, '', '');

        if (sizeof($arregloCursos) > 0) {

            foreach ($arregloCursos as $elemento) {
                $item = '';

                if ($elemento->activo) {

                    $persona = new Persona($elemento->idAutor);
                    $item = HTML::enlace(HTML::imagen($elemento->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $elemento->url);
                    $item .= HTML::parrafo(HTML::enlace($elemento->nombre, $elemento->url) . ' ' . HTML::frase(preg_replace('/\%1/', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)), HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $persona->idGenero . '.png') . $textos->id('CREADO_POR2')), 'flotanteCentro'));

                    $item2 = HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion), 'pequenia cursiva negrilla margenInferior');
                    $item2 .= HTML::parrafo($elemento->descripcion, 'margenInferior');

                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisL'); //barra del contenedor gris

                    $listaCursos[] = $item;
                }
            }//fin del foreach

            $listaCursos[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('CURSOS', '', '', '', 'i_follow'), 'flotanteCentro margenSuperior');
        } else {
            $listaCursos[] = $textos->id('NO_SIGUES_NINGUN_CURSO');
        }

        $bloqueCursos .= HTML::lista($listaCursos, 'listaVertical bordeSuperiorLista', 'botonesOcultos', '');

        return $bloqueCursos;
    }

    /**
     * Metodo que muestra y lista los cursos que dicta el ususario que ha iniciado sesion
     * */
    public function cursosQueDicto($usuario = NULL) {
        global $configuracion, $textos, $sesion_usuarioSesion;

        if (isset($usuario)) {
            $usuario = $usuario;
        } else {
            $usuario = $sesion_usuarioSesion;
        }

        $bloqueCursos = '';
        $arregloCursos = self::listarCursosQueDicto(0, 5, '', '', $usuario);

        if (sizeof($arregloCursos) > 0) {

            foreach ($arregloCursos as $elemento) {
                $item = '';

                if (isset($usuario) && ($usuario->idTipo == 0 || $usuario->id == $elemento->idAutor)) {
                    $botones = '';
                    $botones .= HTML::botonModificarItem($elemento->id, $this->urlBase);
                    $botones .= HTML::botonEliminarItem($elemento->id, $this->urlBase);
                    $item .= HTML::contenedor($botones, 'oculto flotanteDerecha');
                }

                if ($elemento->activo) {

                    $persona = new Persona($elemento->idAutor);
                    $item = HTML::enlace(HTML::imagen($elemento->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $elemento->url);
                    $item .= HTML::parrafo(HTML::enlace($elemento->nombre, $elemento->url) . ' ' . HTML::frase(preg_replace('/\%1/', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)), HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $persona->idGenero . '.png') . $textos->id('CREADO_POR2')), 'flotanteCentro'));

                    $item2 = HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion), 'pequenia cursiva negrilla margenInferior');
                    $item2 .= HTML::parrafo($elemento->descripcion, 'margenInferior');

                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisL'); //barra del contenedor gris

                    $listaCursos[] = $item;
                }
            }//fin del foreach

            $listaCursos[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('CURSOS', '', '', '', 'i_follow'), 'flotanteCentro margenSuperior');
        } else {
            $listaCursos[] = $textos->id('NO_DICTAS_NINGUN_CURSO');
        }

        $bloqueCursos .= HTML::lista($listaCursos, 'listaVertical bordeSuperiorLista', 'botonesOcultos', '');

        return $bloqueCursos;
    }


    /**
     * Eliminar seguidores
     * @param array datos   Códigos internos o identificadores delos seguidores en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminarSeguidores($datos) {
        global $sql;

        if (empty($datos)) {//datos me esta llegando como un string concatenado con comas
            return NULL;
        }

        $ids = explode(',', $datos);

        for ($i = 0; $i < sizeof($ids); $i++) {
            $ids[$i] = htmlspecialchars($ids[$i]);
            $consulta = $sql->eliminar('cursos_seguidos', 'id = "' . $ids[$i] . '"');
        }

        return $consulta;
    }

    public function sumarVisita() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $numVisitas = $sql->obtenerValor('cursos', 'visitas', 'id = "' . $this->id . '"');

        $datosCurso['visitas'] = $numVisitas + 1;

	$sql->guardarBitacora = false;
        $sumVisita = $sql->modificar('cursos', $datosCurso, 'id = "' . $this->id . '"');

        if ($sumVisita) {
            return true;
        } else {
            return false;
        }
    }

}


