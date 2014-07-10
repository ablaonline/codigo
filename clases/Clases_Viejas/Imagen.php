<?php

/**
 * @package     FOLCS
 * @subpackage  Base
 * @author      Pablo A. Vélez Vidal. <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
class Imagen {

    /**
     * Código interno o identificador del archivo en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa de un archivo específico
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del módulo al cual pertenece el archivo en la base de datos
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador del registro del módulo al cual pertenece el archivo en la base de datos
     * @var entero
     */
    public $idRegistro;

    /**
     * Código interno o identificador del usuario creador del archivo en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Nombre de usuario (login) del usuario creador del archivo
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del archivo
     * @var cadena
     */
    public $autor;

    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;

    /**
     * identificador del modulo al cual pertenece la imagen
     * @var cadena
     */
    public $moduloImagen;

    /**
     * Título de la imagen
     * @var cadena
     */
    public $titulo;

    /**
     * Descripción corta del archivo
     * @var cadena
     */
    public $descripcion;

    /**
     * Indicador del estado del archivo
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
     * Ruta relativa a la imagen
     * @var entero
     */
    public $ruta;

    /**
     * Ruta absoluta a la imagen
     * @var entero
     */
    public $enlace;

    /**
     * Inicializar el archivo
     * @param entero $id Código interno o identificador del archivo en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql, $configuracion;

        $modulo = new Modulo('IMAGENES');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos del archivo
     * @param entero $id Código interno o identificador del archivo en la base de datos
     */
    public function cargar($id, $idModulo = NULL) {
        global $sql, $configuracion;

        if (is_numeric($id) && $sql->existeItem('imagenes', 'id', intval($id))) {

            $tablas = array(
                'a' => 'imagenes',
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
                'moduloImagen' => 'a.id_modulo',
                'ruta' => 'a.ruta'
            );

            $condicion = 'a.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND a.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $archivo->id;
                $this->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->fotoAutor;
                $this->miniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->ruta;
                $this->enlace = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $this->ruta;
            }
        }
    }

    /**
     * Metodo que se encarga de agregar una Imagen y usa el metodo subirArchivoAlServidor para subirla al servidor,
     * ingrsa los datos de esta imagen a la tabla imagenes en la BD, y dependiendo del modulo, ingresara en la tabla relacion
     * correspondiente, ej: si la imagen es del modulo usuarios, ingresa en imagenes, y en imagenes_usuarios.
     * @param  arreglo $datos       Datos del archivo a adicionar
     * @return entero               Código interno o identificador del archivo en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos, $archivo_archivo = NULL) {//archivo_archivo en caso tal de que tenga que pasar el archivo directamente desde otro metodo
        global $sql, $configuracion, $sesion_usuarioSesion, $archivo_imagen, $archivo_recurso, $textos;

        if (empty($archivo_imagen['tmp_name']) && empty($archivo_recurso['tmp_name']) && empty($archivo_archivo['tmp_name'])) {
            return NULL;
        }

        if (isset($archivo_archivo)) {
            $archivo = $archivo_archivo;
        } elseif (isset($archivo_imagen)) {
            $archivo = $archivo_imagen;
        } else {
            $archivo = $archivo_recurso;
        }

        $idRegistro = htmlspecialchars($datos['idRegistro']);
        $modulo = htmlspecialchars($datos['modulo']);
        $descripcion = htmlspecialchars($datos['descripcion']);
        $titulo = htmlspecialchars($datos['titulo']);

        $moduloNuevo = new Modulo(htmlspecialchars($modulo));
        $idModulo = $moduloNuevo->id;

        $ruta = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['imagenesDinamicas'];

        $area = getimagesize($archivo['tmp_name']);
        $ancho = $area[0];
        $alto = $area[1];

        while ($ancho > 800 || $alto > 600) {
            $ancho = ($ancho * 90) / 100;
            $alto = ($alto * 90) / 100;
        }

        $dimensiones = array($ancho, $alto, 80, 90);

        $recurso = Archivo::subirArchivoAlServidor($archivo, $ruta, $dimensiones);
        if ($recurso) {
            $datosRecurso = array(
                'id_modulo' => $moduloNuevo->id,
                'id_registro' => $idRegistro,
                'id_usuario' => $sesion_usuarioSesion->id,
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'fecha' => date('Y-m-d H:i:s'),
                'ruta' => $recurso
            );

            $consulta = $sql->insertar('imagenes', $datosRecurso);
            $idImagen = $sql->ultimoId;

            if ($consulta) {


                if ($datos['modulo'] == 'CURSOS') {

                    if (isset($datos['notificar_estudiantes'])) {//determina si se escogio notificar a los estudiantes de haber subido la imagen
                        $idCurso = $idRegistro;
                        $objetoCurso = new Curso($idCurso);
                        $consultaSeguidores = $sql->seleccionar(array('cursos_seguidos'), array('id', 'id_usuario'), 'id_curso = "' . $idCurso . '"', '');
                        if ($sql->filasDevueltas) {
                            $tipoItem = $textos->id('IMAGEN');
                            $nombreItem = $datos['titulo'];
                            while ($seguidor = $sql->filaEnObjeto($consultaSeguidores)) {

                                $notificacion = str_replace('%1', HTML::enlace($objetoCurso->autor, HTML::urlInterna('CURSOS', $idCurso)), $textos->id('MENSAJE_ADICION_ITEM_CURSO'));
                                $notificacion = str_replace('%2', HTML::enlace($tipoItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion);
                                $notificacion = str_replace('%3', HTML::enlace($objetoCurso->nombre, HTML::urlInterna('CURSOS', $idCurso)), $notificacion);
                                $notificacion = str_replace('%4', HTML::enlace($nombreItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion);

                                Servidor::notificar($seguidor->id_usuario, $notificacion);
                            }
                        }
                    }
                }

                return $idImagen;
            } else {
		Recursos::escribirTxt("fallo el insertar en la tabla imagenes-> id imagen: ".$idImagen);
                return NULL;
            }
        } else {
	    Recursos::escribirTxt("fallo el subir el archivo al servidor ->recurso: ".$recurso);
            return NULL;
        }
    }

//fin del metodo adicionar imagen

    /**
     * Eliminar un archivo
     * @param entero $id    Código interno o identificador del archivo en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql, $configuracion;

        if (!isset($this->id) || $this->ruta == '00000001.png') {
            return NULL;
        }

        $ruta = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $this->ruta;
        $miniatura = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->ruta;

        if (!empty($this->id) && $this->id != 772 && $this->id != 8) { //si no es ninguna de las imagenes base
            if (Archivo::eliminarArchivoDelServidor(array($ruta, $miniatura))) {

                $consulta = $sql->eliminar('imagenes', 'id = "' . $this->id . '"');

                return $consulta;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Contar la cantidad de imagenes de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return entero              Número de imagenes hechos al registro del módulo
     */
    public function contar($modulo, $registro) {
        global $sql;

        $tablas = array(
            'a' => 'imagenes',
            'm' => 'modulos'
        );

        $columnas = array(
            'registros' => 'COUNT(a.id)'
        );

        $condicion = 'a.id_modulo = m.id AND a.id_registro = "' . $registro . '" AND m.nombre = "' . $modulo . '"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $archivo = $sql->filaEnObjeto($consulta);
            return $archivo->registros;
        } else {
            return NULL;
        }
    }

    /**
     * Listar los imagenes de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de imagenes hechos al registro del módulo
     */
    public function listar($inicio = 0, $cantidad = 0, $modulo, $registro) {
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
            'a' => 'imagenes',
            'u' => 'usuarios',
            'm' => 'modulos'
        );

        $columnas = array(
            'id' => 'a.id',
            'idAutor' => 'a.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'titulo' => 'a.titulo',
            'fecha' => 'a.fecha',
            'descripcion' => 'a.descripcion',
            'ruta' => 'a.ruta'
        );

        $modulo = $sql->obtenerValor('modulos', 'id', 'nombre = "' . $modulo . '"');


        $condicion = 'a.id_usuario = u.id AND a.id_modulo = "' . $modulo . '" AND a.id_registro = "' . $registro . '" ';


        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'a.id', 'fecha DESC', $inicio, $cantidad);
        $lista = array();
        if ($sql->filasDevueltas) {

            while ($archivo = $sql->filaEnObjeto($consulta)) {
                $archivo->url = $this->urlBase . '/' . $archivo->id;
                $archivo->miniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $archivo->ruta;
                $archivo->ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $archivo->ruta;
                $lista[] = $archivo;
            }
        }

        return $lista;
    }

    public function modificarInfoImagen($datos) {
        global $sql;

        $id = htmlspecialchars($datos['id']);
        $datosImagen['titulo'] = htmlspecialchars($datos['titulo']);
        $datosImagen['descripcion'] = htmlspecialchars($datos['descripcion']);

        $consulta = $sql->modificar('imagenes', $datosImagen, 'id = "' . $id . '"');

        if ($consulta) {
            return 1;
        } else {
            return 0;
        }
    }

}

?>