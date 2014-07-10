<?php

/**
 * @package     FOLCS
 * @subpackage  Noticias
 * @author      Pablo A. Vélez <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
class Noticia {

    /**
     * Código interno o identificador de la noticia en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de noticias
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de una noticia específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador de la noticia en la base de datos
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
     * Nombre de usuario (login) del usuario creador de la noticia
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador de la noticia
     * @var cadena
     */
    public $autor;

    /**
     * Título de la noticia
     * @var cadena
     */
    public $titulo;

    /**
     * Resumen corto de la noticia
     * @var cadena
     */
    public $resumen;

    /**
     * Contenido completo de la noticia
     * @var cadena
     */
    public $contenido;

    /**
     * Código interno o identificador en la base de datos de la imagen relacionada con la noticia
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen de la noticia en tamaño normal
     * @var cadena
     */
    public $imagenPrincipal;

    /**
     * Ruta de la imagen de la noticia en miniatura
     * @var cadena
     */
    public $imagenMiniatura;

    /**
     * Fecha de creación de la noticia
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación de la noticia
     * @var fecha
     */
    public $fechaPublicacion;

    /**
     * Fecha de la última modificación de la noticia
     * @var fecha
     */
    public $fechaActualizacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de noticias
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * Codigos de los perfiles de usuario con los cuales es compartido un Blog
     * @var array
     */
    public $permisosPerfiles = array();


    /**
     * Número de visitas que tiene este item 
     * @var entero
     */
    public $cantidadVisitas = 0;

    /**
     * Número de comentarios que tiene este item 
     * @var entero
     */
    public $cantidadComentarios = 0;

    /**
     * Número de me gusta que tiene este item 
     * @var entero
     */
    public $cantidadMeGusta = 0;

    /**
     * Número de me galerias fotograficas que tiene este item
     * @var entero
     */
    public $cantidadGalerias = 0;

    /**
     * Inicializar la noticia
     * @param entero $id Código interno o identificador de la noticia en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo                 = new Modulo('NOTICIAS');
        $this->urlBase          = '/' . $modulo->url;
        $this->url              = $modulo->url;
        $this->idModulo         = $modulo->id;

        $this->registros        = $sql->obtenerValor('noticias', 'COUNT(id)', '');
     
        $this->registrosActivos = $sql->obtenerValor('noticias', 'COUNT(id)', 'activo = "1"');
        
        //Saber la cantidad de galerias que tiene este blog
        $this->cantidadGalerias = $sql->obtenerValor('galerias', 'COUNT(id)', 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');

        if (isset($id)) {
            $this->cargar($id);
            $this->permisosPerfiles = PermisosItem::cargarPerfiles($id, $modulo->id);

        }

    }

    /**
     * Cargar los datos de una noticia
     * @param entero $id Código interno o identificador de la noticia en la base de datos
     */
    private function cargar($id) {
        global $sql, $configuracion;

        if (is_numeric($id) && $sql->existeItem('noticias', 'id', intval($id))) {

            $tablas = array(
                'n' => 'noticias',
                'u' => 'usuarios',
                'p' => 'personas',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id'                    => 'n.id',
                'idAutor'               => 'n.id_usuario',
                'usuarioAutor'          => 'u.usuario',
                'autor'                 => 'u.sobrenombre',
                'idImagen'              => 'n.id_imagen',
                'imagen'                => 'i.ruta',
                'resumen'               => 'n.resumen',
                'titulo'                => 'n.titulo',
                'contenido'             => 'n.contenido',
                'fechaCreacion'         => 'UNIX_TIMESTAMP(n.fecha_creacion)',
                'fechaPublicacion'      => 'UNIX_TIMESTAMP(n.fecha_publicacion)',
                'fechaActualizacion'    => 'UNIX_TIMESTAMP(n.fecha_actualizacion)',
                'idCategoria'           => 'id_categoria',
                'activo'                => 'n.activo',
                'cantidadVisitas'       => 'n.visitas'
            );

            $condicion = 'n.id_usuario = u.id AND u.id_persona = p.id AND n.id_imagen = i.id AND n.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->id;
                $this->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $this->imagen;
                $this->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->imagen;
                //sumar una visita al blog
                $this->sumarVisita();

            }

        }

    }

    /**
     * Adicionar una noticia
     * @param  arreglo $datos       Datos de la noticia a adicionar
     * @return entero               Código interno o identificador de la noticia en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo, $archivo_imagen;
        //nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfiles      = $datos['perfiles'];
        $datosVisibilidad   = $datos['visibilidad'];

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {
            $objetoImagen = new Imagen();

            $datosImagen = array(
                                'idRegistro'    => '',
                                'modulo'        => 'NOTICIAS',
                                'descripcion'   => 'Image of'.htmlspecialchars($datos['titulo']),
                                'titulo'        => 'Image of'.htmlspecialchars($datos['titulo'])
                            );

            $idImagen = $objetoImagen->adicionar($datosImagen);

        }
        
        if (!empty($datos['id_imagen_evento'])) {
            $idImagen = $datos['id_imagen_evento'];

        }


        $datosNoticia = array(
                            'titulo'                => htmlspecialchars($datos['titulo']),
                            'resumen'               => htmlspecialchars($datos['resumen']),
                            'contenido'             => Variable::filtrarTagsInseguros($datos['contenido']),
                            'id_categoria'          => htmlspecialchars($datos['categorias']),
                            'id_usuario'            => $sesion_usuarioSesion->id,
                            'id_imagen'             => $idImagen,
                            'fecha_creacion'        => date('Y-m-d H:i:s'),
                            'fecha_actualizacion'   => date('Y-m-d H:i:s'),
                        );

        if (isset($datos['activo'])) {
            $datosNoticia['activo']             = '1';
            $datosNoticia['fecha_publicacion']  = date('Y-m-d H:i:s');

        } else {
            $datosNoticia['activo']             = '0';
            $datosNoticia['fecha_publicacion']  = NULL;

        }

        $consulta   = $sql->insertar('noticias', $datosNoticia);
        $idItem     = $sql->ultimoId;

        if ($consulta) {
            if ($datos['cantCampoImagenGaleria']) {//si viene alguna imagen se crea la galeria             
                $galeria = new Galeria();
                $datos['id_modulo_actual'] = $this->idModulo;
                $datos['id_registro_actual'] = $idItem;
                $galeria->adicionar($datos);

            }
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte la Noticia
            $permisosItem   = new PermisosItem();
            $idModulo       = $modulo->id;

            $permisosItem->insertarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);

            return $idItem;

        } else {
            return false;

        }

    }

    /**
     * Modificar una noticia
     * @param  arreglo $datos       Datos de la noticia a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $modulo, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
        }
        // datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfiles      = $datos['perfiles'];
        $datosVisibilidad   = $datos['visibilidad'];

        if (isset($datos['activo'])) {
            $datos['activo']            = '1';
            $datos['fecha_publicacion'] = date('Y-m-d H:i:s');

        } else {
            $datos['activo']            = '0';
            $datos['fecha_publicacion'] = NULL;

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
                                'idRegistro'    => $this->id,
                                'modulo'        => 'NOTICIAS',
                                'titulo'        => 'Image of '.htmlspecialchars($datos['titulo']),
                                'descripcion'   => 'Image of '.htmlspecialchars($datos['titulo'])
                            );

            $idImagen = $objetoImagen->adicionar($datosImagen);

        }

        $datos = array(
                        'titulo'                => htmlspecialchars($datos['titulo']),
                        'resumen'               => htmlspecialchars($datos['resumen']),
                        'contenido'             => Variable::filtrarTagsInseguros($datos['contenido']),
                        'id_categoria'          => htmlspecialchars($datos['categorias']),
                        'id_imagen'             => $idImagen,
                        'fecha_actualizacion'   => date('Y-m-d H:i:s'),
                    );


        $consulta = $sql->modificar('noticias', $datos, 'id = "' . $this->id . '"');

        if ($consulta) {
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte la noticia
            $permisosItem   = new PermisosItem();
            $idModulo       = $modulo->id;
            $idItem         = $this->id;

            $permisosItem->modificarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);

            return $consulta;

        } else {
            return false;

        }
        
    }

    /**
     * Eliminar una noticia
     * @param entero $id    Código interno o identificador de la noticia en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('noticias', 'id = "' . $this->id . '"');

        if ($consulta) {
            /* Eliminar todos los comentarios que pueda tener la Noticia */
            if ($this->cantidadComentarios > 0) {
                $comentario = new Comentario();
                $comentario->eliminarComentarios($this->id, $this->idModulo);
            }
            /* Eliminar todos los "me gusta" que pueda tener la Noticia */
            if ($this->cantidadMeGusta > 0) {
                $destacado = new Destacado();
                $destacado->eliminarTodosDestacados($this->idModulo, $this->id);
            }

            /* Eliminar todas las galerias que pueda tener el Blog */
            if ($this->cantidadGalerias > 0) {
                $galeria = new Galeria();
                $galeria->eliminarGalerias($this->idModulo, $this->id);
            }

	    $objetoImagen = new Imagen($this->idImagen);
	    $objetoImagen->eliminar();

            $permisosItem = new PermisosItem();
            if (!($permisosItem->eliminar($this->id, $this->idModulo))) {
                return false;
            } else {
                return true;
            }

            return $consulta;
        } else {
            return false;
        }
    }

    /**
     * Listar las noticias
     * @param entero  $cantidad    Número de noticias a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de noticias
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfilUsuario = NULL, $idModulo = NULL, $filtroCategoria = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;
        ;

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
            $condicion .= 'n.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'n.fecha_publicacion DESC';
        } else {
            $orden = 'n.fecha_publicacion DESC';
        }

        //compruebo que se le haya pasado un valor al idPerfil

        $idPerfil = $idPerfilUsuario;

        $tablas = array(
            'n' => 'noticias',
            'u' => 'usuarios',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'n.id',
            'idAutor' => 'n.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'idImagen' => 'n.id_imagen',
            'imagen' => 'i.ruta',
            'titulo' => 'n.titulo',
            'resumen' => 'n.resumen',
            'contenido' => 'n.contenido',
            'fechaCreacion' => 'UNIX_TIMESTAMP(n.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(n.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(n.fecha_actualizacion)',
            'activo' => 'n.activo',
	    'numeroVisitas' => 'n.visitas'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'n.id_usuario = u.id AND n.id_imagen = i.id';

        //filtro de categoria
        if (!empty($filtroCategoria) && $filtroCategoria != 'my_item') {
            $condicion .= ' AND n.id_categoria = "' . $filtroCategoria . '"'; //filtro de categoria
        }

        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {

                if (!empty($filtroCategoria) && $filtroCategoria == "my_item") {
                    $condicion .= ' AND n.id_usuario = "' . $sesion_usuarioSesion->id . '"'; //filtro de categoria
                } else {

                    //codigo para perfiles que pueden ver items de varios perfiles
                    $condicion2 = '';
                    $tienePrivilegios = $sql->obtenerValor('tipos_usuario', 'otros_perfiles', 'id = "' . $sesion_usuarioSesion->idTipo . '"');
                    if (isset($sesion_usuarioSesion) && $tienePrivilegios) {
                        //print_r($otrosPerfiles);
                        $otrosPerfiles = implode(',', Perfil::verOtrosPerfiles());
                        $condicion2 = ', ' . $otrosPerfiles . ' '; //condicion extra para ver items de los otros perfiles sobre los cuales tiene privilegios                  
                    }

                    $tablas['pi'] = 'permisos_item';
                    $columnas['idItem'] = 'pi.id_item';
                    $columnas['idPerfil'] = 'pi.id_perfil';
                    $columnas['idModulo'] = 'pi.id_modulo';

                    $condicion .= " AND ( (n.id  = pi.id_item AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil'$condicion2) )";
                    $condicion .= " OR (n.id_usuario = '$sesion_usuarioSesion->id'";

//                    if (!empty($filtroCategoria)) {
//                        $condicion .= " AND n.id_categoria = '" . $filtroCategoria . "'"; //filtro de categoria
//                    }

                    $condicion .= ') )';
                }//fin del if(!empty($filtroCategoria)..)
            }//fin del if($idPerfil != 0)                
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= ' AND n.id  = pi.id_item AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" )';

            if (!empty($filtroCategoria)) {
                $condicion .= ' AND id_categoria = "' . $filtroCategoria . '"'; //filtro de categoria
            }
        }

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'n.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($noticia = $sql->filaEnObjeto($consulta)) {
                $noticia->url = $this->urlBase . '/' . $noticia->id;
                $noticia->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $noticia->imagen;
                $noticia->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $noticia->imagen;
                $lista[] = $noticia;
            }
        }

        return $lista;
    }

    /**
     * Listar las noticias
     * @param entero  $cantidad    Número de noticias a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de noticias
     */
    public function listarMasVisitadas($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfil = NULL, $idModulo = NULL, $filtroCategoria = NULL) {
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
            $condicion .= 'n.id NOT IN (' . $excepcion . ')';
        }
        /*         * * Definir el orden de presentación de los datos ** */
        $orden = 'n.visitas DESC';

        $tablas = array(
            'n' => 'noticias',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'n.id',
            'idAutor' => 'n.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'idImagen' => 'n.id_imagen',
            'imagen' => 'i.ruta',
            'titulo' => 'n.titulo',
            'resumen' => 'n.resumen',
            'contenido' => 'n.contenido',
            'fechaCreacion' => 'UNIX_TIMESTAMP(n.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(n.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(n.fecha_actualizacion)',
            'activo' => 'n.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'n.id_usuario = u.id AND u.id_persona = p.id AND n.id_imagen = i.id';

        //filtro de categoria
        if (!empty($filtroCategoria) && $filtroCategoria != 'my_item') {
            $condicion .= ' AND id_categoria = "' . $filtroCategoria . '"'; //filtro de categoria
        }

        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {

                if (!empty($filtroCategoria) && $filtroCategoria == 'my_item') {
                    $condicion .= " AND n.id_usuario = '" . $sesion_usuarioSesion->id . "'"; //filtro de categoria
                } else {

                    //codigo para perfiles que pueden ver items de varios perfiles
                    $condicion2 = "";
                    $tienePrivilegios = $sql->obtenerValor('tipos_usuario', 'otros_perfiles', "id = '" . $sesion_usuarioSesion->idTipo . "'");
                    if (isset($sesion_usuarioSesion) && $tienePrivilegios) {
                        //print_r($otrosPerfiles);
                        $otrosPerfiles = implode(",", Perfil::verOtrosPerfiles());
                        $condicion2 = ", $otrosPerfiles "; //condicion extra para ver items de los otros perfiles sobre los cuales tiene privilegios                  
                    }

                    $tablas['pi'] = 'permisos_item';
                    $columnas['idItem'] = 'pi.id_item';
                    $columnas['idPerfil'] = 'pi.id_perfil';
                    $columnas['idModulo'] = 'pi.id_modulo';

                    $condicion .= " AND pi.id_item = n.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil'$condicion2)";
                    $condicion .= "OR (n.id_usuario = u.id AND u.id_persona = p.id AND n.id_imagen = i.id AND n.id_usuario = '$sesion_usuarioSesion->id'";

                    if (!empty($filtroCategoria)) {
                        $condicion .= " AND n.id_categoria = '" . $filtroCategoria . "'"; //filtro de categoria
                    }

                    $condicion .= ")";
                }//fin del if(!empty($filtroCategoria)..)
            }//fin del if($idPerfil != 0)                
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= " AND pi.id_item = n.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' )";

            if (!empty($filtroCategoria)) {
                $condicion .= " AND id_categoria = '" . $filtroCategoria . "'"; //filtro de categoria
            }
        }

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'n.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($noticia = $sql->filaEnObjeto($consulta)) {
                $noticia->url = $this->urlBase . '/' . $noticia->id;
                $noticia->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $noticia->imagen;
                $noticia->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $noticia->imagen;
                $lista[] = $noticia;
            }
        }

        return $lista;
    }

    /**
     * Listar las noticias que le gustan al usuario que ha iniciado la sesion
     * @param entero  $cantidad    Número de noticias a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de noticias que tienen un "me gusta" por parte del usuario que ha iniciado la sesion
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
            $condicion .= 'n.id NOT IN (' . $excepcion . ')';
        }
        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'n.fecha_publicacion ASC';
        } else {
            $orden = 'n.fecha_publicacion ASC';
        }

        $tablas = array(
            'n' => 'noticias',
            'd' => 'destacados',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'n.id',
            'idAutor' => 'n.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'idImagen' => 'n.id_imagen',
            'imagen' => 'i.ruta',
            'titulo' => 'n.titulo',
            'resumen' => 'n.resumen',
            'contenido' => 'n.contenido',
            'fechaCreacion' => 'UNIX_TIMESTAMP(n.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(n.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(n.fecha_actualizacion)',
            'activo' => 'n.activo',
            'noticia' => 'd.id_item'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= "n.id_usuario = u.id AND u.id_persona = p.id AND n.id_imagen = i.id AND n.id = d.id_item AND d.id_modulo = '" . $this->idModulo . "' AND d.id_usuario = '" . $sesion_usuarioSesion->id . "'";


        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);
        $lista = array();
        if ($sql->filasDevueltas) {

            while ($noticia = $sql->filaEnObjeto($consulta)) {
                $noticia->url = $this->urlBase . '/' . $noticia->id;
                $noticia->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $noticia->imagen;
                $noticia->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $noticia->imagen;
                $lista[] = $noticia;
            }
        }

        return $lista;
    }

    /**
     * Metodo que devuelve un listado con las noticias(aquí en el metodo directamente se arman con el html y css) 
     * las cuales el usuario que ha iniciado la sesion ha hecho click en "me gusta"
     * 
     * @global type $configuracion
     * @global type $textos
     * @global type $sesion_usuarioSesion
     * @global type $sql
     * @return type array ->arreglo con el listado de noticias (ya listo para desplegar en el navegador) 
     *                      a las cuales el usuario que tiene la sesion actual ha hecho click en "me gusta"
     */
    public function NoticiasDestacadas() {
        global $configuracion, $textos, $sesion_usuarioSesion;

        $bloqueNoticias = '';
        $arregloNoticias = self::listarMeGusta(0, 5, '', '');

        if (sizeof($arregloNoticias) > 0) {
            foreach ($arregloNoticias as $elemento) {

                $item = '';

                if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $elemento->idAutor)) {
                    $botones = '';
                    $botones .= HTML::botonModificarItem($elemento->id, $this->urlBase);
                    $botones .= HTML::botonEliminarItem($elemento->id, $this->urlBase);
                    $item .= HTML::contenedor($botones, 'oculto flotanteDerecha');
                }

                if ($elemento->activo) {

                    $comentario = new Comentario();

                    $contenedorComentarios = $comentario->mostrarComentarios($this->idModulo, $elemento->id);
                    $contenedorMeGusta = Recursos::mostrarContadorMeGusta($this->idModulo, $elemento->id);
                    $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, 'mostrarPosted');
                    //seleccionar el genero de una persona 
                    $persona = new Persona($elemento->idAutor);
                    $item = HTML::enlace(HTML::imagen($elemento->imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('NOTICIAS', $elemento->id));
                    $item .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $persona->idGenero . '.png') . preg_replace('/\%1/', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)) . 'On ' . HTML::frase(date('D, d M Y', $elemento->fechaPublicacion), 'pequenia cursiva negrilla') . $comentarios, $textos->id('PUBLICADO_POR')));
                    $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                    $item2 .= HTML::parrafo($elemento->resumen, 'pequenia cursiva');
                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris

                    $listaNoticias[] = $item;
                }
            }//fin del foreach

            $listaNoticias[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('NOTICIAS', '', '', '', 'i_like'), 'flotanteCentro margenSuperior');
        } else {
            $listaNoticias[] = $textos->id('NO_TIENES_NOTICIAS_QUE_TE_GUSTEN');
        }

        $bloqueNoticias .= HTML::lista($listaNoticias, 'listaVertical bordeSuperiorLista', 'botonesOcultos', '');

        return $bloqueNoticias;
    }

    /**
     * Listar mas noticias de un usuario determinado
     * @param entero  $cantidad    Número de noticias a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de noticias
     */
    public function listarMasNoticiasUsuario($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfilUsuario = NULL, $idModulo = NULL, $filtroCategoria = NULL, $idUsuarioPropietario = NULL, $idNoticiaActual = NULL) {
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
            $condicion .= 'n.id NOT IN (' . $excepcion . ')';
        }
        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'n.fecha_publicacion DESC';
        } else {
            $orden = 'n.fecha_publicacion DESC';
        }
        //compruebo que se le haya pasado un valor al idPerfil
        $idPerfil = $idPerfilUsuario;

        $tablas = array(
            'n' => 'noticias',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'n.id',
            'idAutor' => 'n.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'idImagen' => 'n.id_imagen',
            'imagen' => 'i.ruta',
            'titulo' => 'n.titulo',
            'resumen' => 'n.resumen',
            'contenido' => 'n.contenido',
            'fechaCreacion' => 'UNIX_TIMESTAMP(n.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(n.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(n.fecha_actualizacion)',
            'activo' => 'n.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'n.id_usuario = "' . $idUsuarioPropietario . '" AND n.id != "' . $idNoticiaActual . '" AND n.id_usuario = u.id AND u.id_persona = p.id AND n.id_imagen = i.id';

        //filtro de categoria
        if (!empty($filtroCategoria) && $filtroCategoria != 'my_item') {
            $condicion .= ' AND id_categoria = "' . htmlspecialchars($filtroCategoria) . '"'; //filtro de categoria
        }
        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {
                if (!empty($filtroCategoria) && $filtroCategoria == 'my_item') {
                    $condicion .= ' AND n.id_usuario = "' . $sesion_usuarioSesion->id . '"'; //filtro de categoria
                } else {
                    //codigo para perfiles que pueden ver items de varios perfiles
                    $condicion2 = '';
                    $tienePrivilegios = $sql->obtenerValor('tipos_usuario', 'otros_perfiles', 'id = "' . $sesion_usuarioSesion->idTipo . '"');
                    if (isset($sesion_usuarioSesion) && $tienePrivilegios) {
                        $otrosPerfiles = implode(',', Perfil::verOtrosPerfiles());
                        $condicion2 = ", $otrosPerfiles "; //condicion extra para ver items de los otros perfiles sobre los cuales tiene privilegios                  
                    }

                    $tablas['pi'] = 'permisos_item';
                    $columnas['idItem'] = 'pi.id_item';
                    $columnas['idPerfil'] = 'pi.id_perfil';
                    $columnas['idModulo'] = 'pi.id_modulo';

                    $condicion .= " AND pi.id_item = n.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil'$condicion2)";
                    $condicion .= "OR (n.id_usuario = '" . $idUsuarioPropietario . "' AND n.id != '" . $idNoticiaActual . "' AND n.id_usuario = u.id AND u.id_persona = p.id AND n.id_imagen = i.id AND n.id_usuario = '$sesion_usuarioSesion->id'";

                    if (!empty($filtroCategoria)) {
                        $condicion .= ' AND n.id_categoria = "' . htmlspecialchars($filtroCategoria) . '"'; //filtro de categoria
                    }

                    $condicion .= ')';
                }//fin del if(!empty($filtroCategoria)..)
            }//fin del if($idPerfil != 0)                
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= ' AND pi.id_item = n.id AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" )';

            if (!empty($filtroCategoria)) {
                $condicion .= ' AND id_categoria = "' . htmlspecialchars($filtroCategoria) . '"'; //filtro de categoria
            }
        }

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'n.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($noticia = $sql->filaEnObjeto($consulta)) {
                $noticia->url = $this->urlBase . '/' . $noticia->id;
                $noticia->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $noticia->imagen;
                $noticia->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $noticia->imagen;
                $lista[] = $noticia;
            }
        }

        return $lista;
    }

    /**
     * Metodo que se encarga de armar el acordeon que aparece al ver una noticia con otras noticias del usuario propietario
     * de la noticia que se esta viendo actualmente
     * @global type $sql
     * @global type $textos
     * @global type $configuracion
     * @global type $sesion_usuarioSesion
     * @param type $idUsuario
     * @param type $idNoticiaActual
     * @return type 
     */
    public function masNoticiasUsuario($idUsuario, $idNoticiaActual) {
        global $textos, $configuracion, $sesion_usuarioSesion;

        if (!isset($idUsuario)) {
            return false;
        }
        /* Capturar el tipo de usuario que tiene el usuario actual */
        if (isset($sesion_usuarioSesion)) {
            $idTipo = $sesion_usuarioSesion->idTipo;
        } else {
            $idTipo = 99;
        }

        $arregloNoticias = $this->listarMasNoticiasUsuario(0, 5, '', '', $idTipo, $this->idModulo, '', $idUsuario, $idNoticiaActual);
        $listaMasNoticias = array($textos->id('MAS_NOTICIAS_DE_ESTE_USUARIO'));
        $listaNoticias = array();

        if (sizeof($arregloNoticias) > 0) {
            foreach ($arregloNoticias as $elemento) {
                $item = '';

                if ($elemento->activo) {
                    $comentario = new Comentario();

                    $contenedorComentarios = $comentario->mostrarComentarios($elemento->idModulo, $elemento->id);
                    $contenedorMeGusta = Recursos::mostrarContadorMeGusta($elemento->idModulo, $elemento->id);
                    $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, 'mostrarPosted');
                    //seleccionar el genero de una persona 
                    $usuario = new Usuario();
                    $item .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('NOTICIAS', $elemento->id));
                    $item .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $usuario->getGenero($elemento->idAutor) . '.png') . preg_replace('/\%1/', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)) . 'On ' . HTML::frase(date('D, d M Y', $elemento->fechaPublicacion), 'pequenia cursiva negrilla') . $comentarios, $textos->id('PUBLICADO_POR')));
                    $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                    $item2 .= HTML::parrafo($elemento->resumen, 'pequenia cursiva');
                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris

                    $item = HTML::contenedor($item, 'contenedorListaMasNoticias', 'contenedorListaNoticias' . $elemento->id);

                    $listaNoticias[] = $item;
                }//fin del  SI Blog es activo
            }//fin del foreach

            $acordeon = HTML::acordeonLargo2($listaMasNoticias, $listaNoticias, 'masNoticias' . $idNoticiaActual, '');
        }//fin del if  
        return $acordeon;
    }

    /**
     * Método encargado de sumnar en uno el registro de visitas
     * @global type $sql object -> objeto sql para interacciones con la BD
     * @return type boolean ->     verdadero si se realizo la actividad sin problema
     */
    public function sumarVisita() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $numVisitas = $sql->obtenerValor('noticias', 'visitas', 'id = "' . $this->id . '"');

        $datosNoticia['visitas'] = $numVisitas + 1;

	$sql->guardarBitacora = false;
        $sumVisita = $sql->modificar('noticias', $datosNoticia, 'id = "' . $this->id . '"');

        if ($sumVisita) {
            return true;
        } else {
            return false;
        }
    }

//fin del metodo sumar visita
}

