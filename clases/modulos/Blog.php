<?php

/**
 * @package     FOLCS
 * @subpackage  Blogs
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * */
class Blog {

    /**
     * Código interno o identificador del blog en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de blogs
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un blog específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador del blog en la base de datos
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
     * Nombre de usuario (login) del usuario creador del blog en la base de datos
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del blog
     * @var cadena
     */
    public $autor;

    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;

    /**
     * Título del blog
     * @var cadena
     */
    public $titulo;

    /**
     * Contenido completo del blog
     * @var cadena
     */
    public $contenido;

    /**
     * Palabras claves del blog para las búsquedas
     * @var cadena
     */
    public $palabrasClaves;

    /**
     * Calificación obtenida por el blog
     * @var entero
     */
    public $calificacion;

    /**
     * Fecha de creación del blog
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación del blog
     * @var fecha
     */
    public $fechaPublicacion;

    /**
     * Fecha de la última modificación del blog
     * @var fecha
     */
    public $fechaActualizacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de blogs
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

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
     * Número de me galerias fotograficas que tiene este item
     * @var entero
     */
    public $cantidadGalerias = NULL;

    /**
     * Número de registros activos de la lista de foros
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * Codigos de los perfiles de usuario con los cuales es compartido un Blog
     * @var array
     */
    public $permisosPerfiles = array();

    /**
     * Inicializar el blog
     * @param entero $id Código interno o identificador del blog en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('BLOGS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;
        //Saber el numero de registros
        $this->registros =  $sql->obtenerValor('blogs', 'count(id)', 'id != "0"');
        
        $this->registrosActivos = $sql->obtenerValor('blogs', 'count(id)', 'activo = "1"');

        if (isset($id)) {
            $this->cargar($id);
            $this->permisosPerfiles = PermisosItem::cargarPerfiles($id, $modulo->id);
            //Saber la cantidad de comentarios que tiene este blog
            $this->cantidadComentarios = $sql->obtenerValor('comentarios', 'COUNT(id)', 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
           
            //Saber la cantidad de me Gusta que tiene este blog
            $this->cantidadMeGusta = $sql->obtenerValor('destacados', 'COUNT(*)', 'id_modulo = "' . $this->idModulo . '" AND id_item = "' . $this->id . '"');
          
            //Saber la cantidad de galerias que tiene este blog
            $this->cantidadGalerias = $sql->obtenerValor('galerias', 'COUNT(id)', 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
            
        }
    }

    /**
     * Cargar los datos de un blog
     * @param entero $id Código interno o identificador del blog en la base de datos
     */
    private function cargar($id) {
        global $sql, $configuracion;

        if (is_numeric($id) && $sql->existeItem('blogs', 'id', intval($id))) {

            $tablas = array(
                'b' => 'blogs',
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
                'titulo' => 'b.titulo',
                'contenido' => 'b.contenido',
                'idCategoria' => 'b.id_categoria',
                'calificacion' => 'b.calificacion',
                'palabrasClaves' => 'b.palabras_claves',
                'fechaCreacion' => 'UNIX_TIMESTAMP(b.fecha_creacion)',
                'fechaPublicacion' => 'UNIX_TIMESTAMP(b.fecha_publicacion)',
                'fechaActualizacion' => 'UNIX_TIMESTAMP(b.fecha_actualizacion)',
                'activo' => 'b.activo'
            );

            $condicion = 'b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND b.id = "' . $id . '" ';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->id;
                $this->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->fotoAutor;
                //sumar una visita al blog
                $this->sumarVisita();
            }
        }
    }

    /**
     * Adicionar un blog
     * @param  arreglo $datos       Datos del blog a adicionar
     * @return entero               Código interno o identificador del blog en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo;

        $datosBlog = array();

        $datosBlog['titulo'] = htmlspecialchars($datos['titulo']);
        $datosBlog['contenido'] = $datos['contenido'];
        $datosBlog['palabras_claves'] = htmlspecialchars($datos['palabrasClaves']);
        $datosBlog['id_categoria'] = htmlspecialchars($datos['categorias']);
        $datosBlog['id_usuario'] = $sesion_usuarioSesion->id;
        $datosBlog['fecha_creacion'] = date('Y-m-d H:i:s');
        $datosBlog['fecha_actualizacion'] = date('Y-m-d H:i:s');

        //nuevos datos que recibo sobre los perfiles con los que comparto y la visibilidad
        $datosPerfiles = $datos['perfiles'];
        $datosVisibilidad = $datos['visibilidad'];

        if (isset($datos['activo'])) {
            $datosBlog['activo'] = '1';
            $datosBlog['fecha_publicacion'] = date('Y-m-d H:i:s');
        } else {
            $datosBlog['activo'] = '0';
            $datosBlog['fecha_publicacion'] = NULL;
        }

        $consulta = $sql->insertar('blogs', $datosBlog);
        $idItem = $sql->ultimoId;
        if ($consulta) {

            if ($datos['cantCampoImagenGaleria']) {//si viene alguna imagen se crea la galeria             
                $galeria = new Galeria();
                $datos['id_modulo_actual'] = $this->idModulo;
                $datos['id_registro_actual'] = $idItem;
                $galeria->adicionar($datos);
            }

            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;
            $permisosItem->insertarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);
            return $idItem;
        } else {
            return NULL;
        }//fin del if($consulta)
    }

    /**
     * Modificar un blog
     * @param  arreglo $datos       Datos de la blog a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $modulo;

        if (!isset($this->id)) {
            return NULL;
        }

        $datosBlog = array();

        $datosBlog['titulo'] = htmlspecialchars($datos['titulo']);
        $datosBlog['contenido'] = $datos['contenido'];
        $datosBlog['palabras_claves'] = htmlspecialchars($datos['palabrasClaves']);
        $datosBlog['id_categoria'] = htmlspecialchars($datos['categorias']);
        $datosBlog['fecha_actualizacion'] = date('Y-m-d H:i:s');

        //nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfiles = $datos['perfiles'];
        $datosVisibilidad = $datos['visibilidad'];

        if (isset($datos['activo'])) {
            $datosBlog['activo'] = '1';
            $datosBlog['fecha_publicacion'] = date('Y-m-d H:i:s');
        } else {
            $datosBlog['activo'] = '0';
            $datosBlog['fecha_publicacion'] = NULL;
        }

        $consulta = $sql->modificar('blogs', $datosBlog, 'id = "' . $this->id . '" ');

        if ($consulta) {
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;
            $idItem = $this->id;

            $permisosItem->modificarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);

            return 1;
        } else {

            return NULL;
        }
    }

    /**
     * Eliminar un blog
     * @param entero $id    Código interno o identificador del blog en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        if (!($consulta = $sql->eliminar('blogs', 'id = "' . $this->id . '" '))) {
            return false;
        } else {
            /* Eliminar todos los comentarios que pueda tener el Blog */
            if ($this->cantidadComentarios > 0) {
                $comentario = new Comentario();
                $comentario->eliminarComentarios($this->id, $this->idModulo);
            }
            /* Eliminar todos los "me gusta" que pueda tener el Blog */
            if ($this->cantidadMeGusta > 0) {
                $destacado = new Destacado();
                $destacado->eliminarTodosDestacados($this->idModulo, $this->id);
            }
            /* Eliminar todas las galerias que pueda tener el Blog */
            if ($this->cantidadGalerias > 0) {
                $galeria = new Galeria();
                $galeria->eliminarGalerias($this->idModulo, $this->id);
            }

            $permisosItem = new PermisosItem();

            if (!($permisosItem->eliminar($this->id, $this->idModulo))) {
                return false;
            } else {
                return true;
            }

            return true;
        }//fin del si funciono eliminar
    }

    /**
     * Listar los blogs filtrando el perfil con el cual es compartido, es decir
     * que blogs puede ver un usuario segun su perfil
     * @param entero  $cantidad    Número de blogs a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de blogs
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfil = NULL, $idModulo = NULL, $filtroCategoria = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

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
            'b' => 'blogs',
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
            'titulo' => 'b.titulo',
            'contenido' => 'b.contenido',
            'calificacion' => 'b.calificacion',
            'palabrasClaves' => 'b.palabras_claves',
            'fechaCreacion' => 'UNIX_TIMESTAMP(b.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(b.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(b.fecha_actualizacion)',
            'activo' => 'b.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= ' b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id';
        //filtro de categoria
        if (!empty($filtroCategoria)) {

            if ($filtroCategoria == 'my_item') {
                $filtroCategoria = htmlspecialchars($filtroCategoria);
                $condicion .= ' AND b.id_usuario = "' . $sesion_usuarioSesion->id . '" '; //filtro de categoria
            } else {
                $condicion .= ' AND id_categoria = "' . $filtroCategoria . '" '; //filtro de categoria
            }
        }

        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {
                //codigo para perfiles que pueden ver items de varios perfiles
                $condicion2 = '';
                $tienePrivilegios = $sql->obtenerValor('tipos_usuario', 'otros_perfiles', 'id = "' . $sesion_usuarioSesion->idTipo . '" ');
                if (isset($sesion_usuarioSesion) && $tienePrivilegios) {
                    $otrosPerfiles = implode(',', Perfil::verOtrosPerfiles());
                    $condicion2 = ', ' . $otrosPerfiles . ' '; //condicion extra para ver items de los otros perfiles sobre los cuales tiene privilegios                  
                }

                $tablas['pi'] = 'permisos_item';
                $columnas['idItem'] = 'pi.id_item';
                $columnas['idPerfil'] = 'pi.id_perfil';
                $columnas['idModulo'] = 'pi.id_modulo';

                $condicion .= ' AND pi.id_item = b.id AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" , "' . $idPerfil . ' ' . $condicion2 . '") ';
                $condicion .= ' OR (b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND b.id_usuario = "' . $sesion_usuarioSesion->id . '" AND pi.id_item = b.id AND pi.id_modulo = "' . $idModulo . '" ';
                if (!empty($filtroCategoria)) {
                    if ($filtroCategoria == 'my_item') {
                        $condicion .= ' AND b.id_usuario = "' . $sesion_usuarioSesion->id . '" '; //filtro de categoria
                    } else {
                        $condicion .= ' AND id_categoria = "' . $filtroCategoria . '" '; //filtro de categoria
                    }
                }
                $condicion .= ')';
            }
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= ' AND pi.id_item = b.id AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" )';
        }

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'b.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($blog = $sql->filaEnObjeto($consulta)) {
                $blog->url = $this->urlBase . '/' . $blog->id;
                $blog->idModulo = $this->idModulo;
                $blog->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $blog->fotoAutor;
                $lista[] = $blog;
            }
        }

        return $lista;
    }

    /**
     * Listar los blogs filtrando el perfil con el cual es compartido, es decir
     * que blogs puede ver un usuario segun su perfil
     * @param entero  $cantidad    Número de blogs a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de blogs
     */
    public function listarMasBlogs($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfil = NULL, $idModulo = NULL, $filtroCategoria = NULL, $idUsuarioPropietario = NULL, $idBlog = NULL) {
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
            'b' => 'blogs',
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
            'titulo' => 'b.titulo',
            'contenido' => 'b.contenido',
            'calificacion' => 'b.calificacion',
            'palabrasClaves' => 'b.palabras_claves',
            'fechaCreacion' => 'UNIX_TIMESTAMP(b.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(b.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(b.fecha_actualizacion)',
            'activo' => 'b.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'b.id_usuario = "' . $idUsuarioPropietario . '" AND b.id != "' . $idBlog . '" AND b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id';
        //filtro de categoria
        if (!empty($filtroCategoria)) {
            $filtroCategoria = htmlspecialchars($filtroCategoria);
            if ($filtroCategoria == 'my_item') {
                $condicion .= ' AND b.id_usuario = "' . $sesion_usuarioSesion->id . '"'; //filtro de categoria
            } else {
                $condicion .= ' AND id_categoria = "' . $filtroCategoria . '" '; //filtro de categoria
            }
        }
        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {

                $tablas['pi'] = 'permisos_item';
                $columnas['idItem'] = 'pi.id_item';
                $columnas['idPerfil'] = 'pi.id_perfil';
                $columnas['idModulo'] = 'pi.id_modulo';

                $condicion .= ' AND pi.id_item = b.id AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" , "' . $idPerfil . '")';
                $condicion .= 'OR (b.id_usuario = "' . $idUsuarioPropietario . '" AND b.id != "' . $idBlog . '" AND b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND b.id_usuario = "' . $sesion_usuarioSesion->id . '" AND pi.id_item = b.id AND pi.id_modulo = "' . $idModulo . '"';
                if (!empty($filtroCategoria)) {
                    if ($filtroCategoria == 'my_item') {
                        $condicion .= ' AND b.id_usuario = "' . $sesion_usuarioSesion->id . '" '; //filtro de categoria
                    } else {
                        $condicion .= ' AND id_categoria = "' . $filtroCategoria . '" '; //filtro de categoria
                    }
                }
                $condicion .= ')';
            }
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= ' AND pi.id_item = b.id AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" )';
        }
       
        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
       
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'b.id', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            while ($blog = $sql->filaEnObjeto($consulta)) {
                $blog->url = $this->urlBase . '/' . $blog->id;
                $blog->idModulo = $this->idModulo;
                $blog->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $blog->fotoAutor;
                $lista[] = $blog;
            }
        }

        return $lista;
    }

    /**
     * Listar los blogs que le gustan al usuario que ha iniciado sesion
     * @param entero  $cantidad    Número de blogs a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de blogs
     */
    public function listarMeGusta($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

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
            'b' => 'blogs',
            'd' => 'destacados',
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
            'titulo' => 'b.titulo',
            'contenido' => 'b.contenido',
            'calificacion' => 'b.calificacion',
            'palabrasClaves' => 'b.palabras_claves',
            'fechaCreacion' => 'UNIX_TIMESTAMP(b.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(b.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(b.fecha_actualizacion)',
            'activo' => 'b.activo',
            'blog' => 'd.id_item',
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= ' b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND b.id= d.id_item AND d.id_modulo = "' . $this->idModulo . '" AND d.id_usuario = "' . $sesion_usuarioSesion->id . '" ';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'b.id', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($blog = $sql->filaEnObjeto($consulta)) {
                $blog->url = $this->urlBase . '/' . $blog->id;
                $blog->idModulo = $this->idModulo;
                $blog->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $blog->fotoAutor;
                $lista[] = $blog;
            }
        }

        return $lista;
    }

    /**
     * Metodo que muestra y lista los Blogs de el ususario que ha iniciado sesion
     * */
    public function misBlogs() {
        global $configuracion, $textos, $sesion_usuarioSesion;

        $bloqueBlogs = '';
        $arregloBlogs = self::listar(0, 5, '', 'b.id_usuario = "' . $sesion_usuarioSesion->id . '"', $sesion_usuarioSesion->idTipo, 20, '');

        if (sizeof($arregloBlogs) > 0) {
            foreach ($arregloBlogs as $elemento) {
                $item = '';

                if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->id == $elemento->idAutor)) {
                    $botones = '';
                    $botones .= HTML::botonEliminarItemAjax($elemento->id, $this->urlBase);
                    $botones .= HTML::botonModificarItemAjax($elemento->id, $this->urlBase);
                    $item .= HTML::contenedor($botones, 'botonesLista', 'botonesLista');
                }

                if ($elemento->activo) {
                    $comentario = new Comentario();

                    $contenedorComentarios = $comentario->mostrarComentarios($elemento->idModulo, $elemento->id);
                    $contenedorMeGusta = Recursos::mostrarContadorMeGusta($elemento->idModulo, $elemento->id);
                    $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, 'mostrarPosted');
                    //seleccionar el genero de una persona 
                    $persona = new Persona($elemento->idAutor);

                    $item .= HTML::enlace(HTML::imagen($elemento->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $elemento->url);
                    $item .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $persona->idGenero . '.png') . str_replace('%1', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)) . $comentarios, $textos->id('PUBLICADO_POR')));

                    $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                    $item2 .= HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion), 'pequenia cursiva negrilla');
                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris
                    $item = HTML::contenedor($item, 'contenedorListaBlogs', 'contenedorListaBlogs' . $elemento->id);
                    $listaBlogs[] = $item;
                }//fin del  SI Blog es activo
            }//fin del foreach

            $listaBlogs[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('BLOGS', '', '', '', 'my_item'), 'flotanteCentro margenSuperior');
        } else {
            $listaBlogs[] = $textos->id('NO_TIENES_BLOGS');
        }
        $bloqueBlogs .= HTML::lista($listaBlogs, 'listaVertical bordeSuperiorLista', 'botonesOcultos', '');

        return $bloqueBlogs;
    }

    /**
     * Metodo que muestra y lista los Blogs de el ususario que ha iniciado sesion a los cuales ha dado click en meGusta
     * */
    public function blogsQueMeGustan() {
        global $configuracion, $textos, $sesion_usuarioSesion;

        $bloqueBlogs = '';
        $arregloBlogs = self::listarMeGusta(0, 0, '', '');

        if (sizeof($arregloBlogs) > 0) {
            foreach ($arregloBlogs as $elemento) {
                $item = '';

                if ($elemento->activo) {

                    if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->id == $elemento->idAutor)) {
                        $botones = '';
                        $botones .= HTML::botonEliminarItemAjax($elemento->id, $this->urlBase);
                        $botones .= HTML::botonModificarItemAjax($elemento->id, $this->urlBase);
                        $item .= HTML::contenedor($botones, 'botonesLista', 'botonesLista');
                    }

                    $comentario = new Comentario();

                    $contenedorComentarios = $comentario->mostrarComentarios($elemento->idModulo, $elemento->id);
                    $contenedorMeGusta = Recursos::mostrarContadorMeGusta($elemento->idModulo, $elemento->id);
                    $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, 'mostrarPosted');
                    //seleccionar el genero de una persona 
                    $persona = new Persona($elemento->idAutor);
                    $item .= HTML::enlace(HTML::imagen($elemento->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $elemento->url);
                    $item .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $persona->idGenero . '.png') . str_replace('%1', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)) . $comentarios, $textos->id('PUBLICADO_POR')));

                    $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                    $item2 .= HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion), 'pequenia cursiva negrilla');
                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris
                    $item = HTML::contenedor($item, 'contenedorListaBlogs', 'contenedorListaBlogs' . $elemento->id);
                    $listaBlogs[] = $item;
                }//fin del  SI Blog es activo
            }//fin del foreach

            $listaBlogs[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('BLOGS', '', '', '', 'i_like'), 'flotanteCentro margenSuperior');
        } else {
            $listaBlogs[] = $textos->id('NO_TIENES_BLOGS_QUE_TE_GUSTEN');
        }

        $bloqueBlogs .= HTML::lista($listaBlogs, 'listaVertical bordeSuperiorLista', 'botonesOcultos', '');

        return $bloqueBlogs;
    }

	/**
	 * Método sumar visita, llamado cada vez que alguien visita un blog y encargado
	 *como su nombre lo indica de sumar uno al número de visitas
	 *
	 **/
    private function sumarVisita() {
        global $sql;
        //$sql = new SQL();
        if (!isset($this->id)) {
            return NULL;
        }

        $numVisitas = $sql->obtenerValor('blogs', 'visitas', 'id = "' . $this->id . '"');

        $datosBlog['visitas'] = $numVisitas + 1;
	$sql->guardarBitacora = false;	
        $sumVisita = $sql->modificar('blogs', $datosBlog, 'id = "' . $this->id . '"');

        if ($sumVisita) {
            return true;
        } else {
            return false;
        }
    }

}


