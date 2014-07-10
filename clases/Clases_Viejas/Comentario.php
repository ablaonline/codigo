<?php

/** 13433376795
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 * */
class Comentario {

    /**
     * Código interno o identificador del comentario en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Código interno o identificador del módulo al cual pertenece el comentario en la base de datos
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador del registro del módulo al cual pertenece el comentario en la base de datos
     * @var entero
     */
    public $idRegistro;

    /**
     * Código interno o identificador del usuario creador del comentario en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Nombre de usuario (login) del usuario creador del comentario
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del comentario
     * @var cadena
     */
    public $autor;

    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;

    /**
     * Contenido completo del comentario
     * @var cadena
     */
    public $contenido;

    /**
     * Fecha de publicación del comentario
     * @var fecha
     */
    public $fecha;

    /**
     * Indicador del estado del comentario
     * @var lógico
     */
    public $activo;

    /**
     * Inicializar el comentario
     * @param entero $id Código interno o identificador del comentario en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos del comentario
     * @param entero $id Código interno o identificador del comentario en la base de datos
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (is_numeric($id) && $sql->existeItem('comentarios', 'id', intval($id))) {

            $tablas = array(
                'c' => 'comentarios',
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
                'contenido' => 'c.contenido',
                'fecha' => 'UNIX_TIMESTAMP(c.fecha)'
            );

            $condicion = 'c.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND c.id = "' . $id . '"';

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
     * Adicionar un comentario
     * @param  arreglo $datos       Datos del comentario a adicionar
     * @return entero               Código interno o identificador del comentario en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $textos;

	$idModulo = htmlspecialchars($datos['idModulo']);
	$idRegistro = htmlspecialchars($datos['idRegistro']);

        $datos = array(
            'id_modulo' => htmlspecialchars($idModulo),
            'id_registro' => htmlspecialchars($idRegistro),
            'id_usuario' => $sesion_usuarioSesion->id,
            'contenido' => htmlspecialchars($datos['contenido']),
            'fecha' => date('Y-m-d H:i:s'),
            'activo' => '1'
        );

        $consulta = $sql->insertar('comentarios', $datos);
        $idConsulta = $sql->ultimoId;

        if ($consulta) {

	   if($idModulo == '32'){//el item sobre el que se comenta es un audio
		$nombreModulo = $textos->id('AUDIO');

		$consulta = $sql->seleccionar(array('audios'), array('id_registro', 'id_modulo', 'id_usuario', 'titulo'), 'id = "'.$idRegistro.'"' );
		$arreglo = $sql->filaEnObjeto($consulta);

		$idModulo2 = $arreglo->id_modulo;
		$idAutor = $arreglo->id_usuario;
		$idItem = $arreglo->id_registro;
		$titulo = $arreglo->titulo;

		if($idModulo2 == '4'){//el item se encuentra en el perfil de usuario

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_PERFIL'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		} elseif($idModulo2 == '26'){//el item se encuentra en un curso
		  $nombreCurso = $sql->obtenerValor('cursos', 'nombre', 'id = "'.$idItem.'"');

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CURSO'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);
		  $notificacion = str_replace('%4', HTML::enlace($nombreCurso, HTML::urlInterna('CURSOS', $idItem)), $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		} elseif($idModulo2 == '5'){//el item se encuentra en un centro
		  $nombreCentro = $sql->obtenerValor('centros', 'nombre', 'id = "'.$idItem.'"');

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CENTRO'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);
		  $notificacion = str_replace('%4', $nombreCentro, $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		}

	    } elseif($idModulo == '19'){//el item sobre el que se comenta es un video
		$nombreModulo = $textos->id('VIDEO');

		$consulta = $sql->seleccionar(array('videos'), array('id_registro', 'id_modulo', 'id_usuario', 'titulo'), 'id = "'.$idRegistro.'"' );
		$arreglo = $sql->filaEnObjeto($consulta);

		$idModulo2 = $arreglo->id_modulo;
		$idAutor = $arreglo->id_usuario;
		$idItem = $arreglo->id_registro;
		$titulo = $arreglo->titulo;

		if($idModulo2 == '4'){//el item se encuentra en el perfil de usuario

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_PERFIL'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		} elseif($idModulo2 == '26'){//el item se encuentra en un curso
		  $nombreCurso = $sql->obtenerValor('cursos', 'nombre', 'id = "'.$idItem.'"');

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CURSO'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);
		  $notificacion = str_replace('%4', HTML::enlace($nombreCurso, HTML::urlInterna('CURSOS', $idItem)), $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		} elseif($idModulo2 == '5'){//el item se encuentra en un centro
		  $nombreCentro = $sql->obtenerValor('centros', 'nombre', 'id = "'.$idItem.'"');

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CENTRO'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);
		  $notificacion = str_replace('%4', $nombreCentro, $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		}

	    } elseif($idModulo == '18'){//el item sobre el que se comenta es una imagen
		$nombreModulo = $textos->id('IMAGEN');

		$consulta = $sql->seleccionar(array('imagenes'), array('id_registro', 'id_modulo', 'id_usuario', 'titulo'), 'id = "'.$idRegistro.'"' );
		$arreglo = $sql->filaEnObjeto($consulta);

		$idModulo2 = $arreglo->id_modulo;
		$idAutor = $arreglo->id_usuario;
		$idItem = $arreglo->id_registro;
		$titulo = $arreglo->titulo;

		if($idModulo2 == '4'){//el item se encuentra en el perfil de usuario

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_PERFIL'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		} elseif($idModulo2 == '26'){//el item se encuentra en un curso
		  $nombreCurso = $sql->obtenerValor('cursos', 'nombre', 'id = "'.$idItem.'"');

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CURSO'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);
		  $notificacion = str_replace('%4', HTML::enlace($nombreCurso, HTML::urlInterna('CURSOS', $idItem)), $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		} elseif($idModulo2 == '5'){//el item se encuentra en un centro
		  $nombreCentro = $sql->obtenerValor('centros', 'nombre', 'id = "'.$idItem.'"');

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CENTRO'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);
		  $notificacion = str_replace('%4', $nombreCentro, $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		}

	    } elseif($idModulo == '40'){//el item sobre el que se comenta es una galeria
		$nombreModulo = $textos->id('GALERIA');

		$consulta = $sql->seleccionar(array('galerias'), array('id_registro', 'id_modulo', 'id_usuario', 'titulo'), 'id = "'.$idRegistro.'"' );
		$arreglo = $sql->filaEnObjeto($consulta);

		$idModulo2 = $arreglo->id_modulo;
		$idAutor = $arreglo->id_usuario;
		$idItem = $arreglo->id_registro;
		$titulo = $arreglo->titulo;

		if($idModulo2 == '4'){//el item se encuentra en el perfil de usuario

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_PERFIL'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		} elseif($idModulo2 == '26'){//el item se encuentra en un curso
		  $nombreCurso = $sql->obtenerValor('cursos', 'nombre', 'id = "'.$idItem.'"');

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CURSO'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);
		  $notificacion = str_replace('%4', HTML::enlace($nombreCurso, HTML::urlInterna('CURSOS', $idItem)), $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		} elseif($idModulo2 == '5'){//el item se encuentra en un centro
		  $nombreCentro = $sql->obtenerValor('centros', 'nombre', 'id = "'.$idItem.'"');

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CENTRO'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);
		  $notificacion = str_replace('%4', $nombreCentro, $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		}

	    } elseif($idModulo == '17'){//el item sobre el que se comenta es un documento
		$nombreModulo = $textos->id('DOCUMENTO');

		$consulta = $sql->seleccionar(array('documentos'), array('id_registro', 'id_modulo', 'id_usuario', 'titulo'), 'id = "'.$idRegistro.'"' );
		$arreglo = $sql->filaEnObjeto($consulta);

		$idModulo2 = $arreglo->id_modulo;
		$idAutor = $arreglo->id_usuario;
		$idItem = $arreglo->id_registro;
		$titulo = $arreglo->titulo;

		if($idModulo2 == '4'){//el item se encuentra en el perfil de usuario

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_PERFIL'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		} elseif($idModulo2 == '26'){//el item se encuentra en un curso
		  $nombreCurso = $sql->obtenerValor('cursos', 'nombre', 'id = "'.$idItem.'"');

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CURSO'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);
		  $notificacion = str_replace('%4', HTML::enlace($nombreCurso, HTML::urlInterna('CURSOS', $idItem)), $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		} elseif($idModulo2 == '5'){//el item se encuentra en un centro
		  $nombreCentro = $sql->obtenerValor('centros', 'nombre', 'id = "'.$idItem.'"');

		  $notificacion = '';
                  $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CENTRO'));
                  $notificacion = str_replace('%2', $nombreModulo, $notificacion);
		  $notificacion = str_replace('%3', $titulo, $notificacion);
		  $notificacion = str_replace('%4', $nombreCentro, $notificacion);

                  Servidor::notificar($idAutor, $notificacion);

		}

	    } elseif($idModulo == '4'){//se comenta es sobre el perfil de un usuario

		$notificacion = '';
                $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_PERFIL'));

                Servidor::notificar($idRegistro, $notificacion);

	    }elseif($idModulo == '20'){//se comenta es sobre algún blog

		$consulta = $sql->seleccionar(array('blogs'), array('id_usuario', 'titulo'), 'id = "'.$idRegistro.'"' );
		$arreglo = $sql->filaEnObjeto($consulta);

		$titulo = $arreglo->titulo;
		$idAutor = $arreglo->id_usuario;

		$notificacion = '';
                $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_BLOG'));
                $notificacion = str_replace('%2', HTML::enlace($titulo, HTML::urlInterna('BLOGS', $idRegistro)), $notificacion);

                Servidor::notificar($idAutor, $notificacion);

	    }elseif($idModulo == '9'){//se comenta es sobre alguna noticia

		$consulta = $sql->seleccionar(array('noticias'), array('id_usuario', 'titulo'), 'id = "'.$idRegistro.'"' );
		$arreglo = $sql->filaEnObjeto($consulta);

		$titulo = $arreglo->titulo;
		$idAutor = $arreglo->id_usuario;

		$notificacion = '';
                $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_NOTICIA'));
                $notificacion = str_replace('%2', HTML::enlace($titulo, HTML::urlInterna('NOTICIAS', $idRegistro)), $notificacion);

                Servidor::notificar($idAutor, $notificacion);

	    } 	        

            return $idConsulta;
        } else {
            return NULL;
        }
    }

    /**
     * Eliminar un comentario
     * @param entero $id    Código interno o identificador del comentario en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('comentarios', 'id = "' . $this->id . '"');
        return $consulta;
    }

    /**
     * Eliminar una cantidad de comentarios que pertenecen a determinado item
     * en caso de que este item sea eliminado
     * @param entero $id    Código interno o identificador del comentario en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminarComentarios($idRegistro, $idModulo) {
        global $sql;
        // 
        if (!isset($idRegistro)) {
            return NULL;
        }
        $consulta = $sql->eliminar('comentarios', 'id_modulo = "' . $idModulo . '" AND id_registro = "' . $idRegistro . '"');
        return $consulta;
    }

    /**
     * Contar la cantidad de comentarios de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return entero              Número de comentarios hechos al registro del módulo
     */
    public function contar($modulo, $registro) {
        global $sql;

        $tablas = array(
            'c' => 'comentarios',
            'm' => 'modulos'
        );

        $columnas = array(
            'registros' => 'COUNT(c.id)'
        );

        $condicion = 'c.id_modulo = m.id AND c.id_registro = "' . $registro . '" AND m.nombre = "' . $modulo . '" AND c.activo = "1"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $comentario = $sql->filaEnObjeto($consulta);
            return $comentario->registros;
        } else {
            return NULL;
        }
    }

    /**
     * Listar los comentarios de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de comentarios hechos al registro del módulo
     */
    public function listar($modulo, $registro) {
        global $sql, $configuracion;


        $tablas = array(
            'c' => 'comentarios',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes',
            'm' => 'modulos'
        );

        $columnas = array(
            'id' => 'c.id',
            'idAutor' => 'c.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'contenido' => 'c.contenido',
            'fecha' => 'UNIX_TIMESTAMP(c.fecha)'
        );

        $condicion = 'c.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND c.id_modulo = m.id AND c.id_registro = "' . htmlspecialchars($registro) . '" AND m.nombre = "' . htmlspecialchars($modulo) . '" AND c.activo = "1"';
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', 'fecha ASC');

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($comentario = $sql->filaEnObjeto($consulta)) {
                $comentario->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $comentario->fotoAutor;
                $lista[] = $comentario;
            }
        }

        return $lista;
    }

    /**
     * Metodo para contar los Comentarios que tiene un determinado Item
     * */
    public function contarComentarios($idModulo, $idItem) {
        global $sql;

        $tablas = array(
            'c' => 'comentarios'
        );

        $columnas = array(
            'registros' => 'COUNT(c.id)'
        );

        $condicion = 'c.id_modulo = "' . $idModulo . '" AND c.id_registro = "' . $idItem . '" ';
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $comentarios = $sql->filaEnObjeto($consulta);
            return $comentarios->registros;
        } else {
            return NULL;
        }
    }

    /**
     * Metodo que muestra los div de la pagina principal con los comentarios
     * */
    public static function mostrarComentarios($idModulo, $idItem) {
        global $configuracion;

        $cantidad = self::contarComentarios($idModulo, $idItem);

        if ($cantidad <= 0) {
            $codigo = HTML::contenedor(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'posted.png', 'imgCommPosted') . HTML::contenedor(' 0', 'mostrarDivNums'), 'mostrarPostedSup');
        } else {
            $codigo = HTML::contenedor(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'postedOn.png', 'imgCommPosted') . HTML::contenedor($cantidad, 'mostrarDivNums'), 'mostrarPostedSup');
        }

        return $codigo;
    }

//mostrar Contador me gusta
}

?>