<?php

/**
 * @package     FOLCS
 * @subpackage  Usuarios
 * @author      Pablo A. Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
class Usuario {

    /**
     * Código interno o identificador del usuario en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de usuarios
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un usuario específico
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del tipo de usuario en la base de datos
     * @var entero
     */
    public $idTipo;

    /**
     * Nombre del tipo de usuario
     * @var cadena
     */
    public $tipo;

    /**
     * Nombre de usuario para el inicio de sesión
     * @var cadena
     */
    public $usuario;

    /**
     * Código interno o identificador en la base de datos de la persona con la cual está relacionada el usuario
     * @var entero
     */
    public $idPersona;

    /**
     * Código interno o identificador en la base de datos de la persona con la cual está relacionada el usuario
     * @var entero
     */
    public $idModulo;

    /**
     * Representación (objeto) de la persona con la cual está relacionada el usuario
     * @var objeto
     */
    public $persona;

    /**
     * Sobrenombre del usuario
     * @var cadena
     */
    public $sobrenombre;

    /**
     * Código interno o identificador del centro binacional en la base de datos al cual pertenece usuario
     * @var entero
     */
    public $idCentro;

    /**
     * Nombre del centro binacional al cual pertenece el usuario
     * @var cadena
     */
    public $centro;

    /**
     * Código interno o identificador en la base de datos de la ciudad del centro binacional al que pertenece persona
     * @var entero
     */
    public $idCiudadCentro;

    /**
     * Nombre de la ciudad del centro binacional al que pertenece persona
     * @var cadena
     */
    public $ciudadCentro;

    /**
     * Código interno o identificador en la base de datos del estado del centro binacional al que pertenece persona
     * @var entero
     */
    public $idEstadoCentro;

    /**
     * Nombre del estado del centro binacional al que pertenece persona
     * @var cadena
     */
    public $estadoCentro;

    /**
     * Código interno o identificador en la base de datos del usuario del centro binacional al que pertenece persona
     * @var entero
     */
    public $idPaisCentro;

    /**
     * Nombre del usuario del centro binacional al que pertenece persona
     * @var cadena
     */
    public $paisCentro;

    /**
     * Variable que determina si un usuario desea recibir notificaciones de ablaonline en su correo
     * @var boolean
     */
    public $notificaciones;

    /**
     * Indicador del orden cronológio de la lista de usuarios
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Inicializar el usuario
     * @param entero $id Código interno o identificador del usuario en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('USUARIOS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;

        if (is_string($id) && isset($id) && $sql->existeItem('usuarios', 'usuario', $id)) {
            $usuario = $sql->obtenerValor('usuarios', 'id', 'usuario = "' . $id . '"');
        } elseif (isset($id) && is_numeric($id)) {
            $usuario = $id;
        }

        $consulta = $sql->filaEnObjeto($sql->seleccionar(array('usuarios'), array('registros' => 'COUNT(id)')));
        $this->registros = $consulta->registros;
        $this->idModulo = $modulo->id;
        if (isset($id) && $id != NULL) {
            $this->cargar($usuario);
        }
    }

    /**
     * Metodo que se encarga de consultar y devolver el id de la persona en la base de datos
     * @global type $sql
     * @param type $id = identificador del usuario, ya sea su id, o su nombre de usuario
     * @return type $idPersona = identificador de la persona en la base de datos
     */
    public function getGenero($id = NULL) {
        global $sql;

        if (is_string($id) && isset($id) && $sql->existeItem('usuarios', 'usuario', $id)) {
            $usuario = $sql->obtenerValor('usuarios', 'id', 'usuario = "' . $id . '"');
        } elseif (isset($id) && is_numeric($id)) {
            $usuario = $id;
        }

        $idPersona = $sql->obtenerValor('usuarios', 'id_persona', 'id = "' . $usuario . '"');
        $genero = $sql->obtenerValor('personas', 'genero', 'id = "' . $idPersona . '"');
        return $genero;
    }

    /**
     * Cargar los datos del usuario
     * @param entero $id Código interno o identificador del usuario en la base de datos
     */
    public function cargar($id = NULL) {
        global $sql;

        if (isset($id) && $sql->existeItem('usuarios', 'id', intval($id))) {
            $this->id = $id;

            $tablas = array(
                'u' => 'usuarios',
                't' => 'tipos_usuario',
                'c' => 'centros',
                'c0' => 'ciudades',
                'e0' => 'estados',
                'p0' => 'paises'
            );

            $columnas = array(
                'idTipo' => 'u.id_tipo',
                'tipo' => 't.nombre',
                'usuario' => 'u.usuario',
                'idPersona' => 'u.id_persona',
                'sobrenombre' => 'u.sobrenombre',
                'idCentro' => 'u.id_centro',
                'centro' => 'c.nombre',
                'idCiudadCentro' => 'c.id_ciudad',
                'ciudadCentro' => 'c0.nombre',
                'idEstadoCentro' => 'c0.id_estado',
                'estadoCentro' => 'e0.nombre',
                'idPaisCentro' => 'e0.id_pais',
                'paisCentro' => 'p0.nombre',
                'fechaRegistro' => 'u.fecha_registro',
                'cambiarContrasena' => 'u.cambiar_contrasena',
                'fechaCambioContrasena' => 'u.fecha_cambio_contrasena',
                'cambioContrasenaMinimo' => 'u.cambio_contrasena_minimo',
                'cambioContrasenaMaximo' => 'u.cambio_contrasena_maximo',
                'fechaExpiracion' => 'u.fecha_expiracion',
                'activo' => 'u.activo',
                'notificaciones' => 'u.notificaciones'
            );

            $condicion = 'u.id_centro = c.id AND c.id_ciudad = c0.id AND c0.id_estado = e0.id AND e0.id_pais = p0.id AND u.id_tipo = t.id AND u.id = "' . $id . '"';
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->usuario;
                $this->persona = new Persona($this->idPersona);
            }
        }
    }

    /**
     * Validar un usuario
     * @param  cadena $usuario      Nombre de acceso del usuario a validar
     * @param  cadena $contrasena   Contraseña del usuario a validar
     * @return entero               Código interno o identificador del usuario en la base de datos (-1 si el usuario está inactivo, NULL si hubo error)
     */
    public function validar($usuario, $contrasena) {
        global $sql;

        $usuario = htmlspecialchars($usuario);
        $contrasena = htmlspecialchars($contrasena);

        if (is_string($usuario) && !preg_match('/[^a-z]/', $usuario) && is_string($contrasena) && !preg_match('/[^a-zA-Z0-9]/', $contrasena)) {
            $consulta = $sql->seleccionar(array('usuarios'), array('id', 'activo', 'bloqueado', 'fecha_expiracion'), 'usuario="' . $usuario . '" AND contrasena=MD5("' . $contrasena . '")');

            if ($sql->filasDevueltas) {
                $datos = $sql->filaEnObjeto($consulta);
                /*                 * ********* Verifico si el usuario esta bloqueado y lo desbloqueo porque coinciden el usuario y la contraseña**************** */
                if ($datos->bloqueado) {
                    $datosUser['bloqueado'] = '0';
                    $consulta = $sql->modificar('usuarios', $datosUser, 'usuario = "' . $usuario . '"');
                }
                if ($datos->activo) {
                    return $datos->id;
                } else {
                    return -1;
                }
            }
        }

        return NULL;
    }

    /**
     * Validar si un usuario que trata de ingresar al sistema esta bloqueado
     * @param  cadena $usuario      Nombre de acceso del usuario a validar
     * @param  cadena $contrasena   Contraseña del usuario a validar
     * @return entero               Código interno o identificador del usuario en la base de datos (-1 si el usuario está inactivo, NULL si hubo error)
     */
    public function validarUsuarioBloqueado($usuario) {
        global $sql;

        $usuario = htmlspecialchars($usuario);

        if (is_string($usuario) && !preg_match('/[^a-z]/', $usuario)) {
            $consulta = $sql->seleccionar(array('usuarios'), array('bloqueado'), 'usuario="' . $usuario . '"');

            if ($sql->filasDevueltas) {
                $datos = $sql->filaEnObjeto($consulta);

                if ($datos->bloqueado) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return NULL;
    }

    /**
     * Registrar un usuario con los datos básicos
     * @param  arreglo $datos       Datos del usuario a registrar
     * @return entero               Código interno o identificador del usuario en la base de datos (NULL si hubo error)
     */
    public function registrar($datos) {
        global $sql, $textos;

        $datosPersona = array(
            'nombre' => htmlspecialchars($datos['nombre']),
            'apellidos' => htmlspecialchars($datos['apellidos']),
            'correo' => strip_tags($datos['correo'], '@'),
            'id_imagen' => '0'
        );

        $persona = new Persona();

        if ($persona->adicionar($datosPersona)) {
            $persona = new Persona($sql->ultimoId);
            $codigo = md5(uniqid(rand(), true));
            $datosUsuario = array(
                'usuario' => htmlspecialchars($datos['usuario']),
                'sobrenombre' => htmlspecialchars($datos['nombre']),
                'id_tipo' => '99',
                'id_centro' => $sql->obtenerValor('lista_centros', 'id', 'nombre = "' . htmlspecialchars($datos['id_centro']) . '"'),
                'id_persona' => $persona->id,
                'contrasena' => md5(htmlspecialchars($datos['contrasena1'])),
                'fecha_registro' => date('Y-m-d H:i:s'),
                'confirmacion' => $codigo,
                'activo' => '1'//quitar lo del registro
            );

            $consulta = $sql->insertar('usuarios', $datosUsuario);
            $idUsuario = $sql->ultimoId;
            if ($consulta) {

                $sobrenombre = $datos['nombre'] . ' ' . substr($datos['apellidos'], 0, 1) . '.';
                $sql->modificar('usuarios', array('sobrenombre' => $sobrenombre), 'id = "' . $sql->ultimoId . '"');
                $mensaje = str_replace('%1', $datosPersona['nombre'], $textos->id('CONTENIDO_MENSAJE_REGISTRO'));
                $mensaje = str_replace('%2', $datos['usuario'], $mensaje);
                $mensaje = str_replace('%3', $datos['contrasena1'], $mensaje);
                Servidor::enviarCorreo($datosPersona['correo'], $textos->id('ASUNTO_MENSAJE_REGISTRO'), $mensaje, $datosPersona['nombre'] . ' ' . $datosPersona['apellidos']);
                return $idUsuario;
            } else {
                $persona->eliminar();
            }
        }

        return NULL;
    }

    /**
     * Adicionar un usuario
     * @param  arreglo $datos       Datos del usuario a adicionar
     * @return entero               Código interno o identificador del usuario en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql;

        $datosPersona = array(
            'nombre' => htmlspecialchars($datos['nombre']),
            'apellidos' => htmlspecialchars($datos['apellidos']),
            'correo' => strip_tags($datos['correo'], '@'),
            'id_ciudad_residencia' => htmlspecialchars($datos['id_ciudad'])
        );

        $persona = new Persona();

        if ($persona->adicionar($datosPersona)) {
            $persona = new Persona($sql->ultimoId);
            $datosUsuario = array(
                'usuario' => htmlspecialchars($datos['usuario']),
                'sobrenombre' => htmlspecialchars($datos['nombre']),
                'id_tipo' => '99',
                'id_centro' => '0',
                'id_persona' => $persona->id,
                'contrasena' => md5(htmlspecialchars($datos['contrasena1'])),
                'fecha_registro' => date('Y-m-d H:i:s')
            );

            $consulta = $sql->insertar('usuarios', $datosUsuario);

            if ($consulta) {
                return $sql->ultimoId;
            } else {
                $persona->eliminar();
            }
        }

        return NULL;
    }

    /**
     * Modificar la información básica de un usuario
     * @param  arreglo $datos       Datos del usuario a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
        }

        $datosPersona = array(
            'nombre' => htmlspecialchars($datos['nombre']),
            'apellidos' => htmlspecialchars($datos['apellidos']),
            'correo' => strip_tags($datos['correo'], '@'),
            'pagina_web' => htmlspecialchars($datos['pagina_web']),
            'id_ciudad_nacimiento' => htmlspecialchars($datos['id_ciudad_nacimiento']),
            'id_ciudad_residencia' => htmlspecialchars($datos['id_ciudad_residencia']),
            'genero' => htmlspecialchars($datos['genero']),
            'fecha_nacimiento' => htmlspecialchars($datos['fecha_nacimiento']),
            'descripcion' => htmlspecialchars($datos['descripcion']),
        );

        $idImagen = $this->persona->idImagen;

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            if (empty($this->persona->idImagen)) {
                $objetoImagen = new Imagen();
            } else {
                $objetoImagen = new Imagen($this->persona->idImagen);
                $objetoImagen->eliminar();
            }

            $datosImagen = array(
                'idRegistro' => $this->id,
                'modulo' => 'USUARIOS',
                'descripcion' => 'Profile Image',
                'titulo' => 'Profile Image'
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }

        $datosPersona['id_imagen'] = $idImagen;

        $consulta = $sql->modificar('personas', $datosPersona, 'id = "' . $this->persona->id . '"');

        $datosUsuario = array(
            'id_centro' => $datos['id_centro'],
            'sobrenombre' => htmlspecialchars($datos['sobrenombre'])
        );

        if (!isset($datos['notificaciones'])) {
            $datosUsuario['notificaciones'] = '0';
        } else {
            $datosUsuario['notificaciones'] = '1';
        }

        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 && !isset($datos['activo'])) {
            $datosUsuario['activo'] = '0';
        } else {
            $datosUsuario['activo'] = '1';
        }

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 ) || isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 2 )) {//Aqui deberá hacerse la validacion de si el BNC webmaster puede editar el perfil del usuario
            $datosUsuario['id_tipo'] = htmlspecialchars($datos['id_tipo']);

            if ($datosUsuario['id_tipo'] == 2 && !empty($datos['id_centro_admin'])) {
                //primero borro datos en caso de que ya este administrando un centro
                $val = $sql->obtenerValor('admin_centro', 'id', 'id_usuario = "' . $this->id . '"');
                if ($val) {
                    $sql->eliminar('admin_centro', 'id = "' . $val . '"');
                }

                $datosAdminCentro = array(
                    'id_usuario' => $this->id,
                    'id_centro' =>  $datos['id_centro_admin']
                );

                $consulta = $sql->insertar('admin_centro', $datosAdminCentro);
            }
        }

	//Verificar si antes era un administrador de centro, y deja de serlo, que se borre el registro de la tabla admin centro

	if($this->idTipo == 2 && $datosUsuario['id_tipo'] != 2){
	    $sql->eliminar('admin_centro', 'id_usuario = "' . $this->id . '"');

	}



        if (!empty($datos['contrasena1'])) {
            $datosUsuario['contrasena'] = md5(htmlspecialchars($datos['contrasena1']));
        }

        $consulta = $sql->modificar('usuarios', $datosUsuario, 'id = "' . $this->id . '"');

        return $consulta;
    }

    /**
     * Eliminar un usuario
     * @param entero $id    Código interno o identificador del usuario en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        //Eliminar de la tabla contactos
        $consulta = $sql->eliminar('contactos', 'id_usuario_solicitante = "' . $this->id . '" OR id_usuario_solicitado = "' . $this->id . '"');

        //eliminar los comentarios del usuario
        $consulta = $sql->eliminar('comentarios', 'id_usuario = "' . $this->id . '"');

        //eliminar los videos del usuario
        $consulta = $sql->eliminar('videos', 'id_usuario = "' . $this->id . '"');

        //eliminar las imagenes del usuarios
        $consulta = $sql->eliminar('imagenes_usuarios', 'id_usuario = "' . $this->id . '"');

        //eliminar cada uno de los Blog posteados por el usuario
        $tablas = array('imagenes');
        $columnas = array('id' => 'id');
        $condicion = 'id_usuario = "' . $this->id . '"';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($imagenes = $sql->filaEnObjeto($consulta)) {
                $img = new Imagen($imagenes->id);
                $img->eliminar();
            }
        }

        //eliminar los mensajes de los foros que haya podido hacer el usuario 
        $consulta = $sql->eliminar('mensajes_foro', 'id_usuario = "' . $this->id . '"');

        //eliminar cada uno de los Blog posteados por el usuario
        $tablas = array('documentos');
        $columnas = array('id' => 'id');
        $condicion = 'id_usuario = "' . $this->id . '"';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($docs = $sql->filaEnObjeto($consulta)) {
                $doc = new Documento($docs->id);
                $doc->eliminar();
            }
        }

        //eliminar cada uno de los Blog posteados por el usuario
        $tablas = array('audios');
        $columnas = array('id' => 'id');
        $condicion = 'id_usuario = "' . $this->id . '"';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($audios = $sql->filaEnObjeto($consulta)) {
                $aud = new Audio($audios->id);
                $aud->eliminar();
            }
        }


        //eliminar cada uno de los Blog posteados por el usuario
        $tablas = array('blogs');
        $columnas = array('id' => 'id');
        $condicion = 'id_usuario = "' . $this->id . '"';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($blogs = $sql->filaEnObjeto($consulta)) {
                $blog = new Blog($blogs->id);
                $blog->eliminar();
            }
        }

        //eliminar cada uno de las posibles noticias posteadas por el usuario
        $tablas = array('noticias');
        $columnas = array('id' => 'id');
        $condicion = 'id_usuario = "' . $this->id . '"';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($news = $sql->filaEnObjeto($consulta)) {
                $not = new Noticia($news->id);
                $not->eliminar();
            }
        }

        //eliminar cada uno de los foros posteado por el usuario y los mensajes de dicho foro
        $tablas = array('foros');
        $columnas = array('id' => 'id');
        $condicion = 'id_usuario = "' . $this->id . '"';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($foros = $sql->filaEnObjeto($consulta)) {
                $foro = new Foro($foros->id);
                $foro->eliminar();
            }
        }
        //eliminar cada uno de los cursos posteados por el usuario y los items de dicho foro
        $tablas = array('cursos');
        $columnas = array('id' => 'id');
        $condicion = 'id_usuario = "' . $this->id . '"';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($cursos = $sql->filaEnObjeto($consulta)) {
                $curso = new Curso($cursos->id);
                $curso->eliminar();
            }
        }
        //eliminar los mensajes que tenga el usuario
        $consulta = $sql->eliminar('mensajes', 'id_usuario_remitente = "' . $this->id . '" OR id_usuario_destinatario = "' . $this->id . '"');

        $consulta = $sql->eliminar('personas', 'id = "' . $this->idPersona . '"');
        //$sql->depurar = true;
        $consulta = $sql->eliminar('usuarios', 'id = "' . $this->id . '"');
        return $consulta;
    }

    /**
     * Listar los usuarios
     * @param entero  $cantidad    Número de usuarios a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de usuarios
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql;
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
            $condicion .= 'u.id NOT IN (' . $excepcion . ')';
        }
        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'u.fecha_registro ASC';
        } else {
            $orden = 'u.fecha_registro DESC';
        }

        $tablas = array(
            'u' => 'usuarios',
            't' => 'tipos_usuario',
            'c' => 'centros',
            'c0' => 'ciudades',
            'e0' => 'estados',
            'p0' => 'paises'
        );

        $columnas = array(
            'id' => 'u.id',
            'idTipo' => 'u.id_tipo',
            'tipo' => 't.nombre',
            'usuario' => 'u.usuario',
            'idPersona' => 'u.id_persona',
            'sobrenombre' => 'u.sobrenombre',
            'idCentro' => 'u.id_centro',
            'centro' => 'c.nombre',
            'idCiudadCentro' => 'c.id_ciudad',
            'ciudadCentro' => 'c0.nombre',
            'idEstadoCentro' => 'c0.id_estado',
            'estadoCentro' => 'e0.nombre',
            'idPaisCentro' => 'e0.id_pais',
            'paisCentro' => 'p0.nombre',
            'fechaRegistro' => 'UNIX_TIMESTAMP(u.fecha_registro)',
            'cambiarContrasena' => 'u.cambiar_contrasena',
            'fechaCambioContrasena' => 'u.fecha_cambio_contrasena',
            'cambioContrasenaMinimo' => 'u.cambio_contrasena_minimo',
            'cambioContrasenaMaximo' => 'u.cambio_contrasena_maximo',
            'fechaExpiracion' => 'u.fecha_expiracion',
            'activo' => 'u.activo',
            'notificaciones' => 'u.notificaciones'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'u.id_centro = c.id AND c.id_ciudad = c0.id AND c0.id_estado = e0.id AND e0.id_pais = p0.id AND u.id_tipo = t.id';

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($usuario = $sql->filaEnObjeto($consulta)) {
                $usuario->url = $this->urlBase . '/' . $usuario->usuario;
                $usuario->urlBase = $this->urlBase;
                $usuario->persona = new Persona($usuario->idPersona);
                $lista[] = $usuario;
            }
        }

        return $lista;
    }

//fin del metodo listar

    /**
     * @global type $sql
     * @return null 
     */
    public function contarNuevasSolicitudesAmistad() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $cantidad = $sql->obtenerValor('contactos', 'COUNT(id)', 'id_usuario_solicitado = "' . $this->id . '" AND estado = "0"');

        return $cantidad;
    }

    /**
     * @return null 
     */
    public function mostrarNuevasSolicitudesAmistad() {

        if (!isset($this->id)) {
            return NULL;
        }
        $cantidad = self::contarNuevasSolicitudesAmistad();
        if ($cantidad > 0) {
            $codigo = HTML::contenedor(HTML::frase($cantidad, 'cantidadNuevasSolicitudesAmistad'), 'contenedorNuevasSolicitudesAmistad', 'contenedorSolicitudesAmistad');
        } else {
            $codigo = HTML::contenedor(HTML::frase('  ', 'cantidadNuevasSolicitudesAmistad'), 'contenedorSinSolicitudesAmistad', 'contenedorSolicitudesAmistad');
        }
        return $codigo;
    }

    /**
     * @global type $sql
     * @return null 
     */
    public function contarNuevosMensajes() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $cantidad = $sql->obtenerValor('mensajes', 'COUNT(id)', 'id_usuario_destinatario = "' . $this->id . '" AND leido = "0"');
        //Recursos::escribirTxt($sql->sentenciaSql);

        return $cantidad;
    }

//fin del metodo contarNuevosMensajes

    /**
     * @return null 
     */
    public function mostrarNuevosMensajes() {

        if (!isset($this->id)) {
            return NULL;
        }
        $cantidad = self::contarNuevosMensajes();
        if ($cantidad > 0) {
            $codigo = HTML::contenedor(HTML::frase($cantidad, 'cantidadNuevosMensajes'), 'contenedorNuevosMensajes', 'contenedorMensajes');
        } else {
            $codigo = HTML::contenedor(HTML::frase('  ', 'cantidadNuevosMensajes'), 'contenedorSinMensajes', 'contenedorMensajes');
        }
        return $codigo;
    }

    /**
     * @global type $sql
     * @return null 
     */
    public function contarNuevasNotificaciones() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        $consulta = $sql->obtenerValor('notificaciones', 'COUNT(id)', 'id_usuario = "' . $this->id . '" AND leido = "0"');

        return $consulta;
    }

    /**
     * @return null 
     */
    public function mostrarNuevasNotificaciones() {

        if (!isset($this->id)) {
            return NULL;
        }
        $cantidad = self::contarNuevasNotificaciones();

        if ($cantidad > 0) {
            $codigo = HTML::contenedor(HTML::frase($cantidad, 'cantidadNuevasNotificaciones'), 'contenedorNuevasNotificaciones', 'contenedorNotificaciones');
        } else {
            $codigo = HTML::contenedor(HTML::frase('', 'cantidadNuevasNotificaciones'), 'contenedorSinNotificaciones', 'contenedorNotificaciones');
        }

        return $codigo;
    }

//fin del metodo mostrarNuevasSolicitudesAmistad 

    /**
     * Metodo que se encarga de de conectar un usuario al chat,
     * ingresando sus datos en la tabla usuarios_conectados
     * @global type $sql
     * @global type $sesion_usuarioSesion
     * @return type 
     */
    public static function conectarUsuario() {
        global $sql, $sesion_usuarioSesion;

        $existe = $sql->existeItem('usuarios_conectados', 'id_usuario', $sesion_usuarioSesion->id);

        if (!$existe) {
            $datos = array(
                'id_usuario' => $sesion_usuarioSesion->id,
                'usuario' => $sesion_usuarioSesion->usuario,
                'nombre' => $sesion_usuarioSesion->persona->nombreCompleto,
                'tiempo' => date('Y-m-d H:i:s')
            );

            $consulta = $sql->insertar('usuarios_conectados', $datos);

            if ($consulta) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Metodo que se encarga de desconectar un usuario del chat,
     * eliminando el registro de la tabla usuarios_conectados
     * @global type $sql
     * @global type $sesion_usuarioSesion
     * @return type 
     */
    public static function desconectarUsuario() {
        global $sql, $sesion_usuarioSesion;
        if (!isset($sesion_usuarioSesion)) {
            return NULL;
        }

        $consulta = $sql->eliminar('usuarios_conectados', 'id_usuario = "' . $sesion_usuarioSesion->id . '"');

        if ($consulta) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Funcion que se encarga de verificar si un usuario carga notificaciones dinamicamente 
     * y las muestra
     */
    public static function mostrarNotificacionesDinamicas() {
        global $sql, $sesion_usuarioSesion;
        $existe = $sql->existeItem('notificaciones_dinamicas', 'id_usuario_destinatario', $sesion_usuarioSesion->id, 'leido = "0" AND UNIX_TIMESTAMP(fecha) >=  (UNIX_TIMESTAMP() - 600)');

        if ($existe) {
            $tablas = array(
                'nd' => 'notificaciones_dinamicas'
            );

            $columnas = array(
                'id' => 'nd.id',
                'usuarioDest' => 'nd.id_usuario_destinatario',
                'usuarioRemi' => 'nd.id_usuario_remitente',
                'registro' => 'nd.id_registro',
                'modulo' => 'nd.id_modulo',
                'fecha' => 'nd.fecha',
                'contenido' => 'nd.contenido',
                'leido' => 'nd.leido'
            );

            $condicion = 'nd.id_usuario_destinatario = "' . $sesion_usuarioSesion->id . '" AND nd.leido = "0" AND UNIX_TIMESTAMP(nd.fecha) >=  (UNIX_TIMESTAMP() - 600)';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
            $lista = '';
            if ($sql->filasDevueltas) {

                while ($notificacion = $sql->filaEnObjeto($consulta)) {

                    $idNotificacion = $notificacion->id;
                    $idModulo = $notificacion->modulo;
                    $idRegistro = $notificacion->registro;
                    $idUsuarioRemitente = $notificacion->usuarioRemi;
                    $contenido = $notificacion->contenido;

                    if ($idModulo == '33') {
                        $url = '/ajax/users/readMessage';
                        $datos = array('id' => $idRegistro);
                    } else if ($idModulo == '15') {
                        $url = '/ajax/contacts/acceptFromNotification';
                        $datos = array('id' => $idUsuarioRemitente);
                    }

                    $boton = HTML::botonImagenAjax(HTML::frase($contenido, 'letraBlanca manito peticionAjax', '', ''), '', '', '', $url, $datos, '');
                    $boton = HTML::contenedor($boton, 'cuadroNotificacion', 'cuadroNotificacion_' . $idNotificacion);

                    $datos2 = array(
                        'leido' => '1'
                    );
                    $sql->modificar('notificaciones_dinamicas', $datos2, 'id = ' . $idNotificacion);

                    $lista .= $boton . '|' . '#cuadroNotificacion_' . $idNotificacion . '%';
                }
            }

            return $lista;
        } else {
            return 'sin_notificaciones';
        }
    }

    /**
     * Funcion que termina la sesion de un usuario
     */
    public static function cerrarSesion() {
        self::desconectarUsuario();
        Sesion::terminar();
        $respuesta = array();
        $respuesta['error'] = NULL;
        $respuesta['accion'] = 'redireccionar';
        $respuesta['destino'] = '/';
        Servidor::enviarJSON($respuesta);
    }

}

?>
