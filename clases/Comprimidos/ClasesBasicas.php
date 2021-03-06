<?php

/**
 * Clase Archivo: encargada de interactuar con los archivos y el servidor
 * @package     FOLCS
 * @subpackage  Base
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 * */
class Archivo {

    /**
     *
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @param objeto $archivo = archivo a ser cargado al servidor
     * @param string $configuracionRuta = ruta a la cual se va a cargar el archivo
     * @param array $identificador = arreglo con los datos de los tamaños miniatura en caso de que el archivo a cargar sea una imagen
     * @return string|boolean   = ruta completa del archivo que se subio al servidor o en caso de error devuelve "false"
     */
    public static function subirArchivoAlServidor($archivo, $configuracionRuta, $identificador = NULL) {
        global $configuracion;

        if (!isset($archivo) && empty($archivo['tmp_name'])) {
            Recursos::escribirTxt("NO venia ningun archivo");
            return NULL;
        }

        $formato = strtolower(substr($archivo['name'], strrpos($archivo['name'], '.') + 1));
        $nombre = substr(md5(uniqid(rand(), true)), 0, 8);
        $subcarpeta = substr($nombre, 0, 2);
        $ruta = $configuracionRuta . '/' . $subcarpeta . '/' . $nombre . '.' . $formato;

        $rutaAdiciona = $subcarpeta . '/' . $nombre . '.' . $formato;

        while (file_exists($ruta)) {
            $nombre = substr(md5(uniqid(rand(), true)), 0, 8);
            $subcarpeta = substr($nombre, 0, 2);
            $ruta = $configuracionRuta . '/' . $subcarpeta . '/' . $nombre . $formato;
        }

        $ruta_carpeta = $configuracionRuta . '/' . $subcarpeta;

        if (!file_exists($ruta_carpeta)) {
            mkdir($configuracionRuta . '/' . $subcarpeta, 0777, true);
        }


        do {
            $copiar = move_uploaded_file($archivo['tmp_name'], $ruta);
        } while (!is_file($ruta));

        if (!$copiar) {
            Recursos::escribirTxt("--**** fallo al copiar la imagen ***----");
            return false;
        } else {

            chmod($ruta, 0777);

            if (in_array($formato, array('wma', 'wav'))) {//se agrego un audio           
                $comando = str_replace('%1', $ruta, $configuracion['PROGRAMAS']['ffmpeg']);
                $comando = str_replace('%2', $configuracionRuta . '/' . $subcarpeta . '/' . $nombre . '.mp3', $comando);
                $convertir = exec($comando);
                $borrado = unlink($ruta);
                $formato = 'mp3';
            } elseif (in_array($formato, array('3gp', 'ogg', '3gpp', 'mp4'))) {//se agrego un audio 
                $comando = str_replace('%1', $ruta, $configuracion['PROGRAMAS']['ffmpeg2']);
                $comando = str_replace('%2', $configuracionRuta . '/' . $subcarpeta . '/' . $nombre . '.mp3', $comando);
                $convertir = exec($comando);
                $borrado = unlink($ruta);
                $formato = 'mp3';
            }
            $rutaAdiciona = $subcarpeta . '/' . $nombre . '.' . $formato; 

            if ($identificador != '' && is_array($identificador)) { //en caso de que el archivo sea una imagen
                $anchoMaximo = $identificador[0];
                $altoMaximo = $identificador[1];
                $anchoMinimo = $identificador[2];
                $altoMinimo = $identificador[3];
                $datos_imagen = getimagesize($ruta);
                $ancho = $datos_imagen[0];
                $alto = $datos_imagen[1];

                $configuracionRutaMini = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['imagenesMiniaturas'];

                if ($anchoMinimo != '' && $altoMinimo != '') {

                    $nombreMini = $nombre; //nombre de la miniatura                
                    $rutaMini = $configuracionRutaMini . '/' . $subcarpeta . '/' . $nombreMini . '.' . $formato;

                    $ruta_carpeta_mini = $configuracionRutaMini . '/' . $subcarpeta;

                    if (!file_exists($ruta_carpeta_mini)) {
                        mkdir($configuracionRutaMini . '/' . $subcarpeta, 0777, true);
                    }


                    $copiarMini = copy($ruta, $rutaMini);


                    do {
                        if (!is_file($rutaMini)) {
                            $copiarMini = copy($ruta, $rutaMini);
                            if ($copiarMini) {
                                chmod($rutaMini, 0777);
                            } else {
                                Recursos::escribirTxt("fallo al copiar la miniatura -> nueva");
                                return false;
                            }
                        }
                    } while (!is_file($rutaMini));




                    if ((($ancho / $alto) > ($anchoMinimo / $altoMinimo)) && ($ancho > $anchoMinimo)) {
                        $dimensiones_min[0] = $anchoMinimo;
                        $dimensiones_min[1] = ($anchoMinimo / $ancho) * $alto;
                    } elseif ($alto > $altoMinimo) {
                        $dimensiones_min[0] = ($altoMinimo / $alto) * $ancho;
                        $dimensiones_min[1] = $altoMinimo;
                    } else {
                        $dimensiones_min[0] = $anchoMinimo;
                        $dimensiones_min[1] = $altoMinimo;
                    }

                    $lienzo = imagecreatetruecolor($dimensiones_min[0], $dimensiones_min[1]);

                    switch ($formato) {
                        case 'png' : $imagen = imagecreatefrompng($rutaMini);
                            $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones_min[0], $dimensiones_min[1], $ancho, $alto);
                            $guardar = imagepng($lienzo, $rutaMini);
                            break;


                        case 'jpg' : $imagen = imagecreatefromjpeg($rutaMini);
                            $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones_min[0], $dimensiones_min[1], $ancho, $alto);
                            $guardar = imagejpeg($lienzo, $rutaMini);
                            break;

                        case 'jpeg' : $imagen = imagecreatefromjpeg($rutaMini);
                            $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones_min[0], $dimensiones_min[1], $ancho, $alto);
                            $guardar = imagejpeg($lienzo, $rutaMini);
                            break;

                        /* case 'gif' : $imagen = imagecreatefromgif($rutaMini);
                          $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones_min[0], $dimensiones_min[1], $ancho, $alto);
                          $guardar = imagegif($lienzo, $rutaMini);
                          break; */
                    }

                    if ($formato != 'gif') {
                        imagedestroy($lienzo);
                        imagedestroy($imagen);
                    }
                }

                if ((($ancho / $alto) > ($anchoMaximo / $altoMaximo)) && ($ancho > $anchoMaximo)) {
                    $dimensiones[0] = $anchoMaximo;
                    $dimensiones[1] = ($anchoMaximo / $ancho) * $alto;
                } elseif ($alto > $altoMaximo) {
                    $dimensiones[0] = ($altoMaximo / $alto) * $ancho;
                    $dimensiones[1] = $altoMaximo;
                } else {
                    $dimensiones[0] = $anchoMaximo;
                    $dimensiones[1] = $altoMaximo;
                }

                $lienzo = imagecreatetruecolor($dimensiones[0], $dimensiones[1]);

                switch ($formato) {
                    case 'png' : $imagen = imagecreatefrompng($ruta);
                        $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones[0], $dimensiones[1], $ancho, $alto);
                        $guardar = imagepng($lienzo, $ruta);
                        break;

                    case 'jpg' : $imagen = imagecreatefromjpeg($ruta);
                        $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones[0], $dimensiones[1], $ancho, $alto);
                        $guardar = imagejpeg($lienzo, $ruta);
                        break;

                    case 'jpeg' : $imagen = imagecreatefromjpeg($ruta);
                        $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones[0], $dimensiones[1], $ancho, $alto);
                        $guardar = imagejpeg($lienzo, $ruta);
                        break;

                    /* case 'gif' : $imagen = imagecreatefromgif($ruta);
                      $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones[0], $dimensiones[1], $ancho, $alto);
                      $guardar = imagegif($lienzo, $ruta);
                      break; */
                }
                if ($formato != 'gif') {
                    imagedestroy($lienzo);
                    imagedestroy($imagen);
                }
            }
        }//fin de si el archivo es una imagen 

        return $rutaAdiciona;
    }

    /**
     * Funcion encargada de borrar un archivo del servidor
     * param string $ruta  ruta del archivo a ser eliminado
     */
    public static function eliminarArchivoDelServidor($ruta) {

        if (!isset($ruta) && !is_array($ruta)) {
            return false;
        }

        $exito = true;

        foreach ($ruta as $archivo) {

            if (!unlink($archivo)) {
                $exito = false;
            }
        }

        return $exito;
    }

    /**
     * Metodo que valida las extensiones de un archivo, devuelve true si tiene una extension valida
     *
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)  = arreglo con datos de configuracion del sistema
     * @param objeto $archivo       = archivo al cual se le va validar la extension
     * @param type $extensiones     = arreglo con las extensiones que se van a validar
     * @return boolean = en caso de exito en la eliminacion del archivo retorna "true"
     */
    public static function validarArchivo($archivo, $extensiones) {

        if (!empty($archivo['name'])) {
            $existe = true;

            $extension_archivo = strtolower(substr($archivo['name'], (strrpos($archivo['name'], '.') - strlen($archivo['name'])) + 1));

            if (!empty($extensiones) && is_array($extensiones)) {
                foreach ($extensiones as $extension) {
                    if ($extension_archivo == $extension) {
                        $existe = false;
                    }
                }
            }
            return $existe;
        } else {
            return false;
        }
    }

}

?>
<?php

/**
 * Class Audio = clase encargada de la gestion de los archivos de audio en la aplicacion.
 *
 * @package     FOLCS 
 * @subpackage  Base
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * */
class Audio {

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
     * icono que representa al modulo
     * @var entero
     */
    public $icono;

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
     * Título del archivo de audio
     * @var cadena
     */
    public $titulo;

    /**
     * Descripción corta del archivo de audio
     * @var cadena
     */
    public $descripcion;

    /**
     * Indicador del estado del archivo de audio
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
     * ruta del archivo de audio
     * @var entero
     */
    public $ruta;

    /**
     * ruta del archivo de audio
     * @var entero
     */
    public $enlace;

    /**
     * Inicializar el archivo de audio
     * @param entero $id Código interno o identificador del archivo en la base de datos
     */
    public function __construct($id = NULL) {

        $modulo = new Modulo('AUDIOS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos del archivo de audio
     * @param entero $id Código interno o identificador del archivo en la base de datos
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem('audios', 'id', intval($id))) {

            $tablas = array(
                'a' => 'audios',
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
                $this->enlace = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['audios'] . '/' . $this->ruta;
                $this->icono = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . '/audio.png';
            }
        }
    }

    /**
     * Adicionar un archivo de audio
     * @param  arreglo $datos       Datos del archivo a adicionar
     * @return entero               Código interno o identificador del archivo de audio en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $configuracion, $sesion_usuarioSesion, $archivo_recurso, $textos;

        if (empty($archivo_recurso['tmp_name'])) {
            return NULL;
        }

        $configuracionRuta = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['audio'];
        $recurso = Archivo::subirArchivoAlServidor($archivo_recurso, $configuracionRuta);
        $ruta = $configuracionRuta . $recurso;

        $datosRecurso = array(
            'id_modulo' => htmlspecialchars($datos['idModulo']),
            'id_registro' => htmlspecialchars($datos['idRegistro']),
            'id_usuario' => $sesion_usuarioSesion->id,
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => htmlspecialchars($datos['descripcion']),
            'fecha' => date('Y-m-d H:i:s'),
            'ruta' => $recurso
        );

        $consulta = $sql->insertar('audios', $datosRecurso);

        if ($consulta) {
            $mod = $sql->obtenerValor('modulos', 'nombre', 'id = "' . $datos['idModulo'] . '"');
            if ($mod == 'CURSOS' && isset($datos['notificar_estudiantes'])) {
                $idCurso = $datos['idRegistro'];
                $objetoCurso = new Curso($idCurso);
                $consultaSeguidores = $sql->seleccionar(array('cursos_seguidos'), array('id', 'id_usuario'), 'id_curso = "' . $idCurso . '"', '');
                if ($sql->filasDevueltas) {
                    $tipoItem = $textos->id('AUDIO');
                    $nombreItem = $datos['titulo'];
                    while ($seguidor = $sql->filaEnObjeto($consultaSeguidores)) {

                        $notificacion = str_replace('%1', HTML::enlace($objetoCurso->autor, HTML::urlInterna('CURSOS', $idCurso)), $textos->id('MENSAJE_ADICION_ITEM_CURSO'));
                        $notificacion = str_replace('%2', HTML::enlace($tipoItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion);
                        $notificacion = str_replace('%3', HTML::enlace($objetoCurso->nombre, HTML::urlInterna('CURSOS', $idCurso)), $notificacion);
                        $notificacion = str_replace('%4', HTML::enlace($nombreItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion);

                        Servidor::notificar($seguidor->id_usuario, $notificacion, array(), '9');
                    }
                }
            }
            return $sql->ultimoId;
        } else {
            return NULL;
        }
    }

    /**
     * Eliminar un archivo de audio
     * @param entero $id    Código interno o identificador del archivo en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql, $configuracion;

        if (!isset($this->id)) {
            return NULL;
        }
        $ruta = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['audio'] . '/' . $this->ruta;
        if (Archivo::eliminarArchivoDelServidor(array($ruta))) {
            $consulta = $sql->eliminar('audios', 'id = "' . $this->id . '"');
            return $consulta;
        } else {
            return false;
        }
    }

    /**
     * Contar la cantidad de archivos de audio de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return entero              Número de archivos hechos al registro del módulo
     */
    public function contar($modulo, $registro) {
        global $sql;

        $tablas = array(
            'a' => 'audios',
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
     * Listar los archivos de audio de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de archivos hechos al registro del módulo
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

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        $tablas = array(
            'a' => 'audios',
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
            'ruta' => 'a.ruta'
        );

        $modulo = htmlspecialchars($modulo);
        $registro = htmlspecialchars($registro);

        $condicion = 'a.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND a.id_modulo = m.id AND a.id_registro = "' . $registro . '" AND m.nombre = "' . $modulo . '"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', 'descripcion ASC', $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            $icono = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . '/audio.png';

            while ($audio = $sql->filaEnObjeto($consulta)) {
                $audio->url = $this->urlBase . '/' . $audio->id;
                $audio->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $audio->fotoAutor;
                $audio->enlace = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['audio'] . '/' . $audio->ruta;
                $audio->icono = $icono;
                $lista[] = $audio;
            }
        }

        return $lista;
    }

}

?>
<?php

/** Clase Comentario = encargada de gestionar la informacion de los comentarios que se realizan a cada uno de los item
 * dentro de la aplicacion
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

            if ($idModulo == '32') {//el item sobre el que se comenta es un audio
                $nombreModulo = $textos->id('AUDIO');

                $consulta = $sql->seleccionar(array('audios'), array('id_registro', 'id_modulo', 'id_usuario', 'titulo'), 'id = "' . $idRegistro . '"');
                $arreglo = $sql->filaEnObjeto($consulta);

                $idModulo2 = $arreglo->id_modulo;
                $idAutor = $arreglo->id_usuario;
                $idItem = $arreglo->id_registro;
                $titulo = $arreglo->titulo;

                if ($idModulo2 == '4') {//el item se encuentra en el perfil de usuario
                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_PERFIL'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                } elseif ($idModulo2 == '26') {//el item se encuentra en un curso
                    $nombreCurso = $sql->obtenerValor('cursos', 'nombre', 'id = "' . $idItem . '"');

                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CURSO'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);
                    $notificacion = str_replace('%4', HTML::enlace($nombreCurso, HTML::urlInterna('CURSOS', $idItem)), $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                } elseif ($idModulo2 == '5') {//el item se encuentra en un centro
                    $nombreCentro = $sql->obtenerValor('centros', 'nombre', 'id = "' . $idItem . '"');

                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CENTRO'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);
                    $notificacion = str_replace('%4', $nombreCentro, $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                }
            } elseif ($idModulo == '19') {//el item sobre el que se comenta es un video
                $nombreModulo = $textos->id('VIDEO');

                $consulta = $sql->seleccionar(array('videos'), array('id_registro', 'id_modulo', 'id_usuario', 'titulo'), 'id = "' . $idRegistro . '"');
                $arreglo = $sql->filaEnObjeto($consulta);

                $idModulo2 = $arreglo->id_modulo;
                $idAutor = $arreglo->id_usuario;
                $idItem = $arreglo->id_registro;
                $titulo = $arreglo->titulo;

                if ($idModulo2 == '4') {//el item se encuentra en el perfil de usuario
                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_PERFIL'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                } elseif ($idModulo2 == '26') {//el item se encuentra en un curso
                    $nombreCurso = $sql->obtenerValor('cursos', 'nombre', 'id = "' . $idItem . '"');

                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CURSO'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);
                    $notificacion = str_replace('%4', HTML::enlace($nombreCurso, HTML::urlInterna('CURSOS', $idItem)), $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                } elseif ($idModulo2 == '5') {//el item se encuentra en un centro
                    $nombreCentro = $sql->obtenerValor('centros', 'nombre', 'id = "' . $idItem . '"');

                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CENTRO'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);
                    $notificacion = str_replace('%4', $nombreCentro, $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                }
            } elseif ($idModulo == '18') {//el item sobre el que se comenta es una imagen
                $nombreModulo = $textos->id('IMAGEN');

                $consulta = $sql->seleccionar(array('imagenes'), array('id_registro', 'id_modulo', 'id_usuario', 'titulo'), 'id = "' . $idRegistro . '"');
                $arreglo = $sql->filaEnObjeto($consulta);

                $idModulo2 = $arreglo->id_modulo;
                $idAutor = $arreglo->id_usuario;
                $idItem = $arreglo->id_registro;
                $titulo = $arreglo->titulo;

                if ($idModulo2 == '4') {//el item se encuentra en el perfil de usuario
                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_PERFIL'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                } elseif ($idModulo2 == '26') {//el item se encuentra en un curso
                    $nombreCurso = $sql->obtenerValor('cursos', 'nombre', 'id = "' . $idItem . '"');

                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CURSO'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);
                    $notificacion = str_replace('%4', HTML::enlace($nombreCurso, HTML::urlInterna('CURSOS', $idItem)), $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                } elseif ($idModulo2 == '5') {//el item se encuentra en un centro
                    $nombreCentro = $sql->obtenerValor('centros', 'nombre', 'id = "' . $idItem . '"');

                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CENTRO'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);
                    $notificacion = str_replace('%4', $nombreCentro, $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                }
            } elseif ($idModulo == '40') {//el item sobre el que se comenta es una galeria
                $nombreModulo = $textos->id('GALERIA');

                $consulta = $sql->seleccionar(array('galerias'), array('id_registro', 'id_modulo', 'id_usuario', 'titulo'), 'id = "' . $idRegistro . '"');
                $arreglo = $sql->filaEnObjeto($consulta);

                $idModulo2 = $arreglo->id_modulo;
                $idAutor = $arreglo->id_usuario;
                $idItem = $arreglo->id_registro;
                $titulo = $arreglo->titulo;

                if ($idModulo2 == '4') {//el item se encuentra en el perfil de usuario
                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_PERFIL'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                } elseif ($idModulo2 == '26') {//el item se encuentra en un curso
                    $nombreCurso = $sql->obtenerValor('cursos', 'nombre', 'id = "' . $idItem . '"');

                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CURSO'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);
                    $notificacion = str_replace('%4', HTML::enlace($nombreCurso, HTML::urlInterna('CURSOS', $idItem)), $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                } elseif ($idModulo2 == '5') {//el item se encuentra en un centro
                    $nombreCentro = $sql->obtenerValor('centros', 'nombre', 'id = "' . $idItem . '"');

                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CENTRO'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);
                    $notificacion = str_replace('%4', $nombreCentro, $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                }
            } elseif ($idModulo == '17') {//el item sobre el que se comenta es un documento
                $nombreModulo = $textos->id('DOCUMENTO');

                $consulta = $sql->seleccionar(array('documentos'), array('id_registro', 'id_modulo', 'id_usuario', 'titulo'), 'id = "' . $idRegistro . '"');
                $arreglo = $sql->filaEnObjeto($consulta);

                $idModulo2 = $arreglo->id_modulo;
                $idAutor = $arreglo->id_usuario;
                $idItem = $arreglo->id_registro;
                $titulo = $arreglo->titulo;

                if ($idModulo2 == '4') {//el item se encuentra en el perfil de usuario
                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_PERFIL'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                } elseif ($idModulo2 == '26') {//el item se encuentra en un curso
                    $nombreCurso = $sql->obtenerValor('cursos', 'nombre', 'id = "' . $idItem . '"');

                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CURSO'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);
                    $notificacion = str_replace('%4', HTML::enlace($nombreCurso, HTML::urlInterna('CURSOS', $idItem)), $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                } elseif ($idModulo2 == '5') {//el item se encuentra en un centro
                    $nombreCentro = $sql->obtenerValor('centros', 'nombre', 'id = "' . $idItem . '"');

                    $notificacion = '';
                    $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_ITEM_CENTRO'));
                    $notificacion = str_replace('%2', $nombreModulo, $notificacion);
                    $notificacion = str_replace('%3', $titulo, $notificacion);
                    $notificacion = str_replace('%4', $nombreCentro, $notificacion);

                    Servidor::notificar($idAutor, $notificacion, array(), '3');
                }
            } elseif ($idModulo == '4') {//se comenta es sobre el perfil de un usuario
                $notificacion = '';
                $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_PERFIL'));

                Servidor::notificar($idRegistro, $notificacion, array(), '3');
            } elseif ($idModulo == '20') {//se comenta es sobre algún blog
                $consulta = $sql->seleccionar(array('blogs'), array('id_usuario', 'titulo'), 'id = "' . $idRegistro . '"');
                $arreglo = $sql->filaEnObjeto($consulta);

                $titulo = $arreglo->titulo;
                $idAutor = $arreglo->id_usuario;

                $notificacion = '';
                $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_BLOG'));
                $notificacion = str_replace('%2', HTML::enlace($titulo, HTML::urlInterna('BLOGS', $idRegistro)), $notificacion);

                Servidor::notificar($idAutor, $notificacion, array(), '3');
            } elseif ($idModulo == '9') {//se comenta es sobre alguna noticia
                $consulta = $sql->seleccionar(array('noticias'), array('id_usuario', 'titulo'), 'id = "' . $idRegistro . '"');
                $arreglo = $sql->filaEnObjeto($consulta);

                $titulo = $arreglo->titulo;
                $idAutor = $arreglo->id_usuario;

                $notificacion = '';
                $notificacion = str_replace('%1', HTML::enlace($sesion_usuarioSesion->sobrenombre, HTML::urlInterna('USUARIOS', $sesion_usuarioSesion->usuario)), $textos->id('COMENTARIO_NOTICIA'));
                $notificacion = str_replace('%2', HTML::enlace($titulo, HTML::urlInterna('NOTICIAS', $idRegistro)), $notificacion);

                Servidor::notificar($idAutor, $notificacion, array(), '3');
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
<?php

/**
 * clase Destacados = clase encargada de gestionar la informacion de los "me gusta" a los item de la aplicacion
 * @package     FOLCS
 * @subpackage  Permisos Item
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 COLOMBO-AMERICANO
 * @version     0.1
 *
 * */
class Destacado {

    /**
     *
     * Metodo Insertar--> ingresa a la base de datos a la tabla destacados los 'me gusta'
     *
     * */
    public function insertarDestacados($idModulo, $idItem, $idUsuario) {
        global $sql;

        $datos = array(
            'id_modulo' => htmlspecialchars($idModulo),
            'id_item' => htmlspecialchars($idItem),
            'id_usuario' => htmlspecialchars($idUsuario)
        );


        $consulta = $sql->insertar('destacados', $datos);


        if ($consulta) {

            return true;
        } else {

            return false;
        }       
    }

    /**
     *
     * Metodo Eliminar--> Es llamado cuando se requiere eliminar un punto o un 'me gusta' de destacados
     * */
    public function eliminarDestacados($idModulo, $idItem, $idUsuario) {
        global $sql;

        $condicion = 'id_item = "' . htmlspecialchars($idItem) . '" AND id_modulo = "' . htmlspecialchars($idModulo) . '" AND id_usuario = "' . htmlspecialchars($idUsuario) . '"';

        $borrar = $sql->eliminar('destacados', $condicion);

        if ($borrar) {

            return true;
        } else {

            return false;
        }
    }

    /**
     * Metodo que se encarga de eliminar todos los registros de me gusta de un determinado item de determinado
     * modulo en caso de que este sea eliminado
     * 
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @param type $idModulo
     * @param type $idItem
     * @return type boolean
     */
    public function eliminarTodosDestacados($idModulo, $idItem) {
        global $sql;

        $condicion = 'id_item = "' . $idItem . '" AND id_modulo = "' . $idModulo . '"';

        $borrar = $sql->eliminar('destacados', $condicion);
        if ($borrar) {
            return true;
        } else {
            return false;
        }//fin del if    
    }

    /**
     *
     * Cantidad de destacados que tiene este item
     *
     * @param entero $id 
     *
     */
    public function cantidadDestacados($idModulo, $idItem) {
        global $sql;


        $registros = $sql->obtenerValor('destacados', 'count(*)', 'id_modulo = "' . $idModulo . '" AND id_item = "' . $idItem . '"');

        return $registros;
    }

    /**
     *
     * Saber si a un usuario determinado le gusta un Item
     *
     * @param entero $id 
     *
     */
    public function meGusta($idModulo, $idItem, $idUsuario) {
        global $sql;


        $registros = $sql->obtenerValor('destacados', 'count(*)', 'id_modulo = "' . $idModulo . '" AND id_item = "' . $idItem . '" AND id_usuario = "' . $idUsuario . '"');

        return $registros;
    }

}

?>
<?php

/**
 * Clase Documento = clase encargada de gestionar la informacion de los documentos almacenados en la aplicacion.
 * por documentos hablamos de los archivos .doc, .pdf, .txt, etc. para mas informacion ver los filtros de validacion en documentos/ajax.php
 *
 * @package     FOLCS
 * @subpackage  Base
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 * */
class Documento {

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
     * icono que representa al modulo
     * @var entero
     */
    public $icono;

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
     * Título del archivo
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
     * ruta del archivo
     * @var entero
     */
    public $ruta;

    /**
     * ruta del archivo
     * @var entero
     */
    public $enlace;

    /**
     *
     * Inicializar el archivo
     *
     * @param entero $id Código interno o identificador del archivo en la base de datos
     *
     */
    public function __construct($id = NULL) {

        $modulo = new Modulo("DOCUMENTOS");
        $this->urlBase = "/" . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos del archivo
     *
     * @param entero $id Código interno o identificador del archivo en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("documentos", "id", intval($id))) {

            $tablas = array(
                "d" => "documentos",
                "u" => "usuarios",
                "p" => "personas",
                "i" => "imagenes"
            );

            $columnas = array(
                "id" => "d.id",
                "idAutor" => "d.id_usuario",
                "usuarioAutor" => "u.usuario",
                "autor" => "u.sobrenombre",
                "fotoAutor" => "i.ruta",
                "titulo" => "d.titulo",
                "descripcion" => "d.descripcion",
                "ruta" => "d.ruta"
            );

            $condicion = "d.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND d.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->fotoAutor = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $this->fotoAutor;
                $this->enlace = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["documentos"] . "/" . $this->ruta;
                $this->icono = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "/docs.png";
            }
        }
    }

    /**
     *
     * Adicionar un Documento
     *
     * @param  arreglo $datos       Datos del archivo a adicionar
     * @return entero               Código interno o identificador del archivo en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $textos, $configuracion, $sesion_usuarioSesion, $archivo_recurso;

        if (empty($archivo_recurso["tmp_name"])) {
            return NULL;
        }

        $configuracionRuta = $configuracion["RUTAS"]["media"] . "/" . $configuracion["RUTAS"]["documentos"];
        $recurso = Archivo::subirArchivoAlServidor($archivo_recurso, $configuracionRuta);

        if (!$recurso) {
            echo "Error subiendo el archivo";
            return false;
        }

        $datosRecurso = array(
            "id_modulo" => $datos["idModulo"],
            "id_registro" => $datos["idRegistro"],
            "id_usuario" => $sesion_usuarioSesion->id,
            "titulo" => htmlspecialchars($datos["titulo"]),
            "descripcion" => htmlspecialchars($datos["descripcion"]),
            "fecha" => date("Y-m-d H:i:s"),
            "ruta" => $recurso
        );

        $consulta = $sql->insertar("documentos", $datosRecurso);
        $idDocumento = $sql->ultimoId;

        if ($consulta) {

            $mod = $sql->obtenerValor("modulos", "nombre", "id = '" . $datos["idModulo"] . "'");
            if ($mod == "CURSOS" && isset($datos["notificar_estudiantes"])) {
                $idCurso = $datos["idRegistro"];
                $objetoCurso = new Curso($idCurso);
                $consultaSeguidores = $sql->seleccionar(array("cursos_seguidos"), array("id", "id_usuario"), "id_curso = '$idCurso'", "");
                if ($sql->filasDevueltas) {
                    $tipoItem = $textos->id("DOCUMENTO");
                    $nombreItem = $datos["titulo"];
                    while ($seguidor = $sql->filaEnObjeto($consultaSeguidores)) {
//			$usuarioSeguidor   = new Usuario();

                        $notificacion = str_replace("%1", HTML::enlace($objetoCurso->autor, HTML::urlInterna("CURSOS", $idCurso)), $textos->id("MENSAJE_ADICION_ITEM_CURSO"));
                        $notificacion = str_replace("%2", HTML::enlace($tipoItem, HTML::urlInterna("CURSOS", $idCurso)), $notificacion);
                        $notificacion = str_replace("%3", HTML::enlace($objetoCurso->nombre, HTML::urlInterna("CURSOS", $idCurso)), $notificacion);
                        $notificacion = str_replace("%4", HTML::enlace($nombreItem, HTML::urlInterna("CURSOS", $idCurso)), $notificacion);

                        Servidor::notificar($seguidor->id_usuario, $notificacion, array(), '12');
                    }
                }
            }

            return $idDocumento;
        } else {
            return NULL;
        }
    }

    /**
     *
     * Eliminar un archivo
     *
     * @param entero $id    Código interno o identificador del archivo en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql, $configuracion;

        if (!isset($this->id)) {
            return NULL;
        }
        $ruta = $configuracion["RUTAS"]["media"] . "/" . $configuracion["RUTAS"]["documentos"] . "/" . $this->ruta;
        if (Archivo::eliminarArchivoDelServidor(array($ruta))) {
            $consulta = $sql->eliminar("documentos", "id = '" . $this->id . "'");
            return $consulta;
        } else {
            return false;
        }
    }

    /**
     *
     * Contar la cantidad de archivos de un registro en un módulo
     *
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return entero              Número de archivos hechos al registro del módulo
     *
     */
    public function contar($modulo, $registro) {
        global $sql;

        $tablas = array(
            "d" => "documentos",
            "m" => "modulos"
        );

        $columnas = array(
            "registros" => "COUNT(d.id)"
        );

        $condicion = "d.id_modulo = m.id AND d.id_registro = '$registro' AND m.nombre = '$modulo'";

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $archivo = $sql->filaEnObjeto($consulta);
            return $archivo->registros;
        } else {
            return NULL;
        }
    }

    /**
     *
     * Listar los archivos de un registro en un módulo
     *
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de archivos hechos al registro del módulo
     *
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
            "d" => "documentos",
            "u" => "usuarios",
            "p" => "personas",
            "i" => "imagenes",
            "m" => "modulos"
        );

        $columnas = array(
            "id" => "d.id",
            "idAutor" => "d.id_usuario",
            "usuarioAutor" => "u.usuario",
            "autor" => "u.sobrenombre",
            "fotoAutor" => "i.ruta",
            "titulo" => "d.titulo",
            "descripcion" => "d.descripcion",
            "ruta" => "d.ruta"
        );

        $condicion = "d.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND d.id_modulo = m.id AND d.id_registro = '$registro' AND m.nombre = '$modulo'";

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", "descripcion ASC", $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($documento = $sql->filaEnObjeto($consulta)) {
                $documento->url = $this->urlBase . "/" . $documento->id;
                $documento->fotoAutor = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $documento->fotoAutor;
                $documento->enlace = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["documentos"] . "/" . $documento->ruta;
                $documento->icono = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "/docs.png";
                $lista[] = $documento;
            }
        }

        return $lista;
    }

}

?>
<?php

/* * *****************************************************************************
 * FPDF                                                                         *
 *                                                                              *
 * Version: 1.6                                                                 *
 * Date:    2008-08-03                                                          *
 * Author:  Olivier PLATHEY                                                     *
 * ***************************************************************************** */

define('FPDF_VERSION', '1.6');

class FPDF {

    var $page;               //current page number
    var $n;                  //current object number
    var $offsets;            //array of object offsets
    var $buffer;             //buffer holding in-memory PDF
    var $pages;              //array containing pages
    var $state;              //current document state
    var $compress;           //compression flag
    var $k;                  //scale factor (number of points in user unit)
    var $DefOrientation;     //default orientation
    var $CurOrientation;     //current orientation
    var $PageFormats;        //available page formats
    var $DefPageFormat;      //default page format
    var $CurPageFormat;      //current page format
    var $PageSizes;          //array storing non-default page sizes
    var $wPt, $hPt;           //dimensions of current page in points
    var $w, $h;               //dimensions of current page in user unit
    var $lMargin;            //left margin
    var $tMargin;            //top margin
    var $rMargin;            //right margin
    var $bMargin;            //page break margin
    var $cMargin;            //cell margin
    var $x, $y;               //current position in user unit
    var $lasth;              //height of last printed cell
    var $LineWidth;          //line width in user unit
    var $CoreFonts;          //array of standard font names
    var $fonts;              //array of used fonts
    var $FontFiles;          //array of font files
    var $diffs;              //array of encoding differences
    var $FontFamily;         //current font family
    var $FontStyle;          //current font style
    var $underline;          //underlining flag
    var $CurrentFont;        //current font info
    var $FontSizePt;         //current font size in points
    var $FontSize;           //current font size in user unit
    var $DrawColor;          //commands for drawing color
    var $FillColor;          //commands for filling color
    var $TextColor;          //commands for text color
    var $ColorFlag;          //indicates whether fill and text colors are different
    var $ws;                 //word spacing
    var $images;             //array of used images
    var $PageLinks;          //array of links in pages
    var $links;              //array of internal links
    var $AutoPageBreak;      //automatic page breaking
    var $PageBreakTrigger;   //threshold used to trigger page breaks
    var $InHeader;           //flag set when processing header
    var $InFooter;           //flag set when processing footer
    var $ZoomMode;           //zoom display mode
    var $LayoutMode;         //layout display mode
    var $title;              //title
    var $subject;            //subject
    var $author;             //author
    var $keywords;           //keywords
    var $creator;            //creator
    var $AliasNbPages;       //alias for total number of pages
    var $PDFVersion;         //PDF version number

    /*     * *****************************************************************************
     *                                                                              *
     *                               Public methods                                 *
     *                                                                              *
     * ***************************************************************************** */

    function FPDF($orientation = 'P', $unit = 'mm', $format = 'A4') {
        //Some checks
        $this->_dochecks();
        //Initialization of properties
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->pages = array();
        $this->PageSizes = array();
        $this->state = 0;
        $this->fonts = array();
        $this->FontFiles = array();
        $this->diffs = array();
        $this->images = array();
        $this->links = array();
        $this->InHeader = false;
        $this->InFooter = false;
        $this->lasth = 0;
        $this->FontFamily = '';
        $this->FontStyle = '';
        $this->FontSizePt = 12;
        $this->underline = false;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->ws = 0;
        //Standard fonts
        $this->CoreFonts = array('courier' => 'Courier', 'courierB' => 'Courier-Bold', 'courierI' => 'Courier-Oblique', 'courierBI' => 'Courier-BoldOblique',
            'helvetica' => 'Helvetica', 'helveticaB' => 'Helvetica-Bold', 'helveticaI' => 'Helvetica-Oblique', 'helveticaBI' => 'Helvetica-BoldOblique',
            'times' => 'Times-Roman', 'timesB' => 'Times-Bold', 'timesI' => 'Times-Italic', 'timesBI' => 'Times-BoldItalic',
            'symbol' => 'Symbol', 'zapfdingbats' => 'ZapfDingbats');
        //Scale factor
        if ($unit == 'pt')
            $this->k = 1;
        elseif ($unit == 'mm')
            $this->k = 72 / 25.4;
        elseif ($unit == 'cm')
            $this->k = 72 / 2.54;
        elseif ($unit == 'in')
            $this->k = 72;
        else
            $this->Error('Incorrect unit: ' . $unit);
        //Page format
        $this->PageFormats = array('a3' => array(841.89, 1190.55), 'a4' => array(595.28, 841.89), 'a5' => array(420.94, 595.28),
            'letter' => array(612, 792), 'legal' => array(612, 1008));
        if (is_string($format))
            $format = $this->_getpageformat($format);
        $this->DefPageFormat = $format;
        $this->CurPageFormat = $format;
        //Page orientation
        $orientation = strtolower($orientation);
        if ($orientation == 'p' || $orientation == 'portrait') {
            $this->DefOrientation = 'P';
            $this->w = $this->DefPageFormat[0];
            $this->h = $this->DefPageFormat[1];
        } elseif ($orientation == 'l' || $orientation == 'landscape') {
            $this->DefOrientation = 'L';
            $this->w = $this->DefPageFormat[1];
            $this->h = $this->DefPageFormat[0];
        }
        else
            $this->Error('Incorrect orientation: ' . $orientation);
        $this->CurOrientation = $this->DefOrientation;
        $this->wPt = $this->w * $this->k;
        $this->hPt = $this->h * $this->k;
        //Page margins (1 cm)
        $margin = 28.35 / $this->k;
        $this->SetMargins($margin, $margin);
        //Interior cell margin (1 mm)
        $this->cMargin = $margin / 10;
        //Line width (0.2 mm)
        $this->LineWidth = .567 / $this->k;
        //Automatic page break
        $this->SetAutoPageBreak(true, 2 * $margin);
        //Full width display mode
        $this->SetDisplayMode('fullwidth');
        //Enable compression
        $this->SetCompression(true);
        //Set default PDF version number
        $this->PDFVersion = '1.3';
    }

    function SetMargins($left, $top, $right = null) {
        //Set left, top and right margins
        $this->lMargin = $left;
        $this->tMargin = $top;
        if ($right === null)
            $right = $left;
        $this->rMargin = $right;
    }

    function SetLeftMargin($margin) {
        //Set left margin
        $this->lMargin = $margin;
        if ($this->page > 0 && $this->x < $margin)
            $this->x = $margin;
    }

    function SetTopMargin($margin) {
        //Set top margin
        $this->tMargin = $margin;
    }

    function SetRightMargin($margin) {
        //Set right margin
        $this->rMargin = $margin;
    }

    function SetAutoPageBreak($auto, $margin = 0) {
        //Set auto page break mode and triggering margin
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
        $this->PageBreakTrigger = $this->h - $margin;
    }

    function SetDisplayMode($zoom, $layout = 'continuous') {
        //Set display mode in viewer
        if ($zoom == 'fullpage' || $zoom == 'fullwidth' || $zoom == 'real' || $zoom == 'default' || !is_string($zoom))
            $this->ZoomMode = $zoom;
        else
            $this->Error('Incorrect zoom display mode: ' . $zoom);
        if ($layout == 'single' || $layout == 'continuous' || $layout == 'two' || $layout == 'default')
            $this->LayoutMode = $layout;
        else
            $this->Error('Incorrect layout display mode: ' . $layout);
    }

    function SetCompression($compress) {
        //Set page compression
        if (function_exists('gzcompress'))
            $this->compress = $compress;
        else
            $this->compress = false;
    }

    function SetTitle($title, $isUTF8 = false) {
        //Title of document
        if ($isUTF8)
            $title = $this->_UTF8toUTF16($title);
        $this->title = $title;
    }

    function SetSubject($subject, $isUTF8 = false) {
        //Subject of document
        if ($isUTF8)
            $subject = $this->_UTF8toUTF16($subject);
        $this->subject = $subject;
    }

    function SetAuthor($author, $isUTF8 = false) {
        //Author of document
        if ($isUTF8)
            $author = $this->_UTF8toUTF16($author);
        $this->author = $author;
    }

    function SetKeywords($keywords, $isUTF8 = false) {
        //Keywords of document
        if ($isUTF8)
            $keywords = $this->_UTF8toUTF16($keywords);
        $this->keywords = $keywords;
    }

    function SetCreator($creator, $isUTF8 = false) {
        //Creator of document
        if ($isUTF8)
            $creator = $this->_UTF8toUTF16($creator);
        $this->creator = $creator;
    }

    function AliasNbPages($alias = '{nb}') {
        //Define an alias for total number of pages
        $this->AliasNbPages = $alias;
    }

    function Error($msg) {
        //Fatal error
        die('<b>FPDF error:</b> ' . $msg);
    }

    function Open() {
        //Begin document
        $this->state = 1;
    }

    function Close() {
        //Terminate document
        if ($this->state == 3)
            return;
        if ($this->page == 0)
            $this->AddPage();
        //Page footer
        $this->InFooter = true;
        $this->Footer();
        $this->InFooter = false;
        //Close page
        $this->_endpage();
        //Close document
        $this->_enddoc();
    }

    function AddPage($orientation = '', $format = '') {
        //Start a new page
        if ($this->state == 0)
            $this->Open();
        $family = $this->FontFamily;
        $style = $this->FontStyle . ($this->underline ? 'U' : '');
        $size = $this->FontSizePt;
        $lw = $this->LineWidth;
        $dc = $this->DrawColor;
        $fc = $this->FillColor;
        $tc = $this->TextColor;
        $cf = $this->ColorFlag;
        if ($this->page > 0) {
            //Page footer
            $this->InFooter = true;
            $this->Footer();
            $this->InFooter = false;
            //Close page
            $this->_endpage();
        }
        //Start new page
        $this->_beginpage($orientation, $format);
        //Set line cap style to square
        $this->_out('2 J');
        //Set line width
        $this->LineWidth = $lw;
        $this->_out(sprintf('%.2F w', $lw * $this->k));
        //Set font
        if ($family)
            $this->SetFont($family, $style, $size);
        //Set colors
        $this->DrawColor = $dc;
        if ($dc != '0 G')
            $this->_out($dc);
        $this->FillColor = $fc;
        if ($fc != '0 g')
            $this->_out($fc);
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;
        //Page header
        $this->InHeader = true;
        $this->Header();
        $this->InHeader = false;
        //Restore line width
        if ($this->LineWidth != $lw) {
            $this->LineWidth = $lw;
            $this->_out(sprintf('%.2F w', $lw * $this->k));
        }
        //Restore font
        if ($family)
            $this->SetFont($family, $style, $size);
        //Restore colors
        if ($this->DrawColor != $dc) {
            $this->DrawColor = $dc;
            $this->_out($dc);
        }
        if ($this->FillColor != $fc) {
            $this->FillColor = $fc;
            $this->_out($fc);
        }
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;
    }

    function Header() {
        //To be implemented in your own inherited class
    }

    function Footer() {
        //To be implemented in your own inherited class
    }

    function PageNo() {
        //Get current page number
        return $this->page;
    }

    function SetDrawColor($r, $g = null, $b = null) {
        //Set color for all stroking operations
        if (($r == 0 && $g == 0 && $b == 0) || $g === null)
            $this->DrawColor = sprintf('%.3F G', $r / 255);
        else
            $this->DrawColor = sprintf('%.3F %.3F %.3F RG', $r / 255, $g / 255, $b / 255);
        if ($this->page > 0)
            $this->_out($this->DrawColor);
    }

    function SetFillColor($r, $g = null, $b = null) {
        //Set color for all filling operations
        if (($r == 0 && $g == 0 && $b == 0) || $g === null)
            $this->FillColor = sprintf('%.3F g', $r / 255);
        else
            $this->FillColor = sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
        $this->ColorFlag = ($this->FillColor != $this->TextColor);
        if ($this->page > 0)
            $this->_out($this->FillColor);
    }

    function SetTextColor($r, $g = null, $b = null) {
        //Set color for text
        if (($r == 0 && $g == 0 && $b == 0) || $g === null)
            $this->TextColor = sprintf('%.3F g', $r / 255);
        else
            $this->TextColor = sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
        $this->ColorFlag = ($this->FillColor != $this->TextColor);
    }

    function GetStringWidth($s) {
        //Get width of a string in the current font
        $s = (string) $s;
        $cw = &$this->CurrentFont['cw'];
        $w = 0;
        $l = strlen($s);
        for ($i = 0; $i < $l; $i++)
            $w+=$cw[$s[$i]];
        return $w * $this->FontSize / 1000;
    }

    function SetLineWidth($width) {
        //Set line width
        $this->LineWidth = $width;
        if ($this->page > 0)
            $this->_out(sprintf('%.2F w', $width * $this->k));
    }

    function Line($x1, $y1, $x2, $y2) {
        //Draw a line
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F l S', $x1 * $this->k, ($this->h - $y1) * $this->k, $x2 * $this->k, ($this->h - $y2) * $this->k));
    }

    function Rect($x, $y, $w, $h, $style = '') {
        //Draw a rectangle
        if ($style == 'F')
            $op = 'f';
        elseif ($style == 'FD' || $style == 'DF')
            $op = 'B';
        else
            $op = 'S';
        $this->_out(sprintf('%.2F %.2F %.2F %.2F re %s', $x * $this->k, ($this->h - $y) * $this->k, $w * $this->k, -$h * $this->k, $op));
    }

    function AddFont($family, $style = '', $file = '') {
        //Add a TrueType or Type1 font
        $family = strtolower($family);
        if ($file == '')
            $file = str_replace(' ', '', $family) . strtolower($style) . '.php';
        if ($family == 'arial')
            $family = 'helvetica';
        $style = strtoupper($style);
        if ($style == 'IB')
            $style = 'BI';
        $fontkey = $family . $style;
        if (isset($this->fonts[$fontkey]))
            return;
        include($this->_getfontpath() . $file);
        if (!isset($name))
            $this->Error('Could not include font definition file');
        $i = count($this->fonts) + 1;
        $this->fonts[$fontkey] = array('i' => $i, 'type' => $type, 'name' => $name, 'desc' => $desc, 'up' => $up, 'ut' => $ut, 'cw' => $cw, 'enc' => $enc, 'file' => $file);
        if ($diff) {
            //Search existing encodings
            $d = 0;
            $nb = count($this->diffs);
            for ($i = 1; $i <= $nb; $i++) {
                if ($this->diffs[$i] == $diff) {
                    $d = $i;
                    break;
                }
            }
            if ($d == 0) {
                $d = $nb + 1;
                $this->diffs[$d] = $diff;
            }
            $this->fonts[$fontkey]['diff'] = $d;
        }
        if ($file) {
            if ($type == 'TrueType')
                $this->FontFiles[$file] = array('length1' => $originalsize);
            else
                $this->FontFiles[$file] = array('length1' => $size1, 'length2' => $size2);
        }
    }

    function SetFont($family, $style = '', $size = 0) {
        //Select a font; size given in points
        global $fpdf_charwidths;

        $family = strtolower($family);
        if ($family == '')
            $family = $this->FontFamily;
        if ($family == 'arial')
            $family = 'helvetica';
        elseif ($family == 'symbol' || $family == 'zapfdingbats')
            $style = '';
        $style = strtoupper($style);
        if (strpos($style, 'U') !== false) {
            $this->underline = true;
            $style = str_replace('U', '', $style);
        }
        else
            $this->underline = false;
        if ($style == 'IB')
            $style = 'BI';
        if ($size == 0)
            $size = $this->FontSizePt;
        //Test if font is already selected
        if ($this->FontFamily == $family && $this->FontStyle == $style && $this->FontSizePt == $size)
            return;
        //Test if used for the first time
        $fontkey = $family . $style;
        if (!isset($this->fonts[$fontkey])) {
            //Check if one of the standard fonts
            if (isset($this->CoreFonts[$fontkey])) {
                if (!isset($fpdf_charwidths[$fontkey])) {
                    //Load metric file
                    $file = $family;
                    if ($family == 'times' || $family == 'helvetica')
                        $file.=strtolower($style);
                    include($this->_getfontpath() . $file . '.php');
                    if (!isset($fpdf_charwidths[$fontkey]))
                        $this->Error('Could not include font metric file');
                }
                $i = count($this->fonts) + 1;
                $name = $this->CoreFonts[$fontkey];
                $cw = $fpdf_charwidths[$fontkey];
                $this->fonts[$fontkey] = array('i' => $i, 'type' => 'core', 'name' => $name, 'up' => -100, 'ut' => 50, 'cw' => $cw);
            }
            else
                $this->Error('Undefined font: ' . $family . ' ' . $style);
        }
        //Select it
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
        $this->CurrentFont = &$this->fonts[$fontkey];
        if ($this->page > 0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
    }

    function SetFontSize($size) {
        //Set font size in points
        if ($this->FontSizePt == $size)
            return;
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
        if ($this->page > 0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
    }

    function AddLink() {
        //Create a new internal link
        $n = count($this->links) + 1;
        $this->links[$n] = array(0, 0);
        return $n;
    }

    function SetLink($link, $y = 0, $page = -1) {
        //Set destination of internal link
        if ($y == -1)
            $y = $this->y;
        if ($page == -1)
            $page = $this->page;
        $this->links[$link] = array($page, $y);
    }

    function Link($x, $y, $w, $h, $link) {
        //Put a link on the page
        $this->PageLinks[$this->page][] = array($x * $this->k, $this->hPt - $y * $this->k, $w * $this->k, $h * $this->k, $link);
    }

    function Text($x, $y, $txt) {
        //Output a string
        $s = sprintf('BT %.2F %.2F Td (%s) Tj ET', $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        if ($this->underline && $txt != '')
            $s.=' ' . $this->_dounderline($x, $y, $txt);
        if ($this->ColorFlag)
            $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
        $this->_out($s);
    }

    function AcceptPageBreak() {
        //Accept automatic page break or not
        return $this->AutoPageBreak;
    }

    function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        //Output a cell
        $k = $this->k;
        if ($this->y + $h > $this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) {
            //Automatic page break
            $x = $this->x;
            $ws = $this->ws;
            if ($ws > 0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation, $this->CurPageFormat);
            $this->x = $x;
            if ($ws > 0) {
                $this->ws = $ws;
                $this->_out(sprintf('%.3F Tw', $ws * $k));
            }
        }
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $s = '';
        if ($fill || $border == 1) {
            if ($fill)
                $op = ($border == 1) ? 'B' : 'f';
            else
                $op = 'S';
            $s = sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x * $k, ($this->h - $this->y) * $k, $w * $k, -$h * $k, $op);
        }
        if (is_string($border)) {
            $x = $this->x;
            $y = $this->y;
            if (strpos($border, 'L') !== false)
                $s.=sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - $y) * $k, $x * $k, ($this->h - ($y + $h)) * $k);
            if (strpos($border, 'T') !== false)
                $s.=sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - $y) * $k);
            if (strpos($border, 'R') !== false)
                $s.=sprintf('%.2F %.2F m %.2F %.2F l S ', ($x + $w) * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
            if (strpos($border, 'B') !== false)
                $s.=sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - ($y + $h)) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
        }
        if ($txt !== '') {
            if ($align == 'R')
                $dx = $w - $this->cMargin - $this->GetStringWidth($txt);
            elseif ($align == 'C')
                $dx = ($w - $this->GetStringWidth($txt)) / 2;
            else
                $dx = $this->cMargin;
            if ($this->ColorFlag)
                $s.='q ' . $this->TextColor . ' ';
            $txt2 = str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
            $s.=sprintf('BT %.2F %.2F Td (%s) Tj ET', ($this->x + $dx) * $k, ($this->h - ($this->y + .5 * $h + .3 * $this->FontSize)) * $k, $txt2);
            if ($this->underline)
                $s.=' ' . $this->_dounderline($this->x + $dx, $this->y + .5 * $h + .3 * $this->FontSize, $txt);
            if ($this->ColorFlag)
                $s.=' Q';
            if ($link)
                $this->Link($this->x + $dx, $this->y + .5 * $h - .5 * $this->FontSize, $this->GetStringWidth($txt), $this->FontSize, $link);
        }
        if ($s)
            $this->_out($s);
        $this->lasth = $h;
        if ($ln > 0) {
            //Go to next line
            $this->y+=$h;
            if ($ln == 1)
                $this->x = $this->lMargin;
        }
        else
            $this->x+=$w;
    }

    function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false) {
        //Output text with automatic or explicit line breaks
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $b = 0;
        if ($border) {
            if ($border == 1) {
                $border = 'LTRB';
                $b = 'LRT';
                $b2 = 'LR';
            } else {
                $b2 = '';
                if (strpos($border, 'L') !== false)
                    $b2.='L';
                if (strpos($border, 'R') !== false)
                    $b2.='R';
                $b = (strpos($border, 'T') !== false) ? $b2 . 'T' : $b2;
            }
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $ns = 0;
        $nl = 1;
        while ($i < $nb) {
            //Get next character
            $c = $s[$i];
            if ($c == "\n") {
                //Explicit line break
                if ($this->ws > 0) {
                    $this->ws = 0;
                    $this->_out('0 Tw');
                }
                $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if ($border && $nl == 2)
                    $b = $b2;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
                $ls = $l;
                $ns++;
            }
            $l+=$cw[$c];
            if ($l > $wmax) {
                //Automatic line break
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                    if ($this->ws > 0) {
                        $this->ws = 0;
                        $this->_out('0 Tw');
                    }
                    $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
                } else {
                    if ($align == 'J') {
                        $this->ws = ($ns > 1) ? ($wmax - $ls) / 1000 * $this->FontSize / ($ns - 1) : 0;
                        $this->_out(sprintf('%.3F Tw', $this->ws * $this->k));
                    }
                    $this->Cell($w, $h, substr($s, $j, $sep - $j), $b, 2, $align, $fill);
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if ($border && $nl == 2)
                    $b = $b2;
            }
            else
                $i++;
        }
        //Last chunk
        if ($this->ws > 0) {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        if ($border && strpos($border, 'B') !== false)
            $b.='B';
        $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
        $this->x = $this->lMargin;
    }

    function Write($h, $txt, $link = '') {
        //Output text in flowing mode
        $cw = &$this->CurrentFont['cw'];
        $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            //Get next character
            $c = $s[$i];
            if ($c == "\n") {
                //Explicit line break
                $this->Cell($w, $h, substr($s, $j, $i - $j), 0, 2, '', 0, $link);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                if ($nl == 1) {
                    $this->x = $this->lMargin;
                    $w = $this->w - $this->rMargin - $this->x;
                    $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
                }
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l+=$cw[$c];
            if ($l > $wmax) {
                //Automatic line break
                if ($sep == -1) {
                    if ($this->x > $this->lMargin) {
                        //Move to next line
                        $this->x = $this->lMargin;
                        $this->y+=$h;
                        $w = $this->w - $this->rMargin - $this->x;
                        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
                        $i++;
                        $nl++;
                        continue;
                    }
                    if ($i == $j)
                        $i++;
                    $this->Cell($w, $h, substr($s, $j, $i - $j), 0, 2, '', 0, $link);
                }
                else {
                    $this->Cell($w, $h, substr($s, $j, $sep - $j), 0, 2, '', 0, $link);
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                if ($nl == 1) {
                    $this->x = $this->lMargin;
                    $w = $this->w - $this->rMargin - $this->x;
                    $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
                }
                $nl++;
            }
            else
                $i++;
        }
        //Last chunk
        if ($i != $j)
            $this->Cell($l / 1000 * $this->FontSize, $h, substr($s, $j), 0, 0, '', 0, $link);
    }

    function Ln($h = null) {
        //Line feed; default value is last cell height
        $this->x = $this->lMargin;
        if ($h === null)
            $this->y+=$this->lasth;
        else
            $this->y+=$h;
    }

    function Image($file, $x = null, $y = null, $w = 0, $h = 0, $type = '', $link = '') {
        //Put an image on the page
        if (!isset($this->images[$file])) {
            //First use of this image, get info
            if ($type == '') {
                $pos = strrpos($file, '.');
                if (!$pos)
                    $this->Error('Image file has no extension and no type was specified: ' . $file);
                $type = substr($file, $pos + 1);
            }
            $type = strtolower($type);
            if ($type == 'jpeg')
                $type = 'jpg';
            $mtd = '_parse' . $type;
            if (!method_exists($this, $mtd))
                $this->Error('Unsupported image type: ' . $type);
            $info = $this->$mtd($file);
            $info['i'] = count($this->images) + 1;
            $this->images[$file] = $info;
        }
        else
            $info = $this->images[$file];
        //Automatic width and height calculation if needed
        if ($w == 0 && $h == 0) {
            //Put image at 72 dpi
            $w = $info['w'] / $this->k;
            $h = $info['h'] / $this->k;
        } elseif ($w == 0)
            $w = $h * $info['w'] / $info['h'];
        elseif ($h == 0)
            $h = $w * $info['h'] / $info['w'];
        //Flowing mode
        if ($y === null) {
            if ($this->y + $h > $this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) {
                //Automatic page break
                $x2 = $this->x;
                $this->AddPage($this->CurOrientation, $this->CurPageFormat);
                $this->x = $x2;
            }
            $y = $this->y;
            $this->y+=$h;
        }
        if ($x === null)
            $x = $this->x;
        $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q', $w * $this->k, $h * $this->k, $x * $this->k, ($this->h - ($y + $h)) * $this->k, $info['i']));
        if ($link)
            $this->Link($x, $y, $w, $h, $link);
    }

    function GetX() {
        //Get x position
        return $this->x;
    }

    function SetX($x) {
        //Set x position
        if ($x >= 0)
            $this->x = $x;
        else
            $this->x = $this->w + $x;
    }

    function GetY() {
        //Get y position
        return $this->y;
    }

    function SetY($y) {
        //Set y position and reset x
        $this->x = $this->lMargin;
        if ($y >= 0)
            $this->y = $y;
        else
            $this->y = $this->h + $y;
    }

    function SetXY($x, $y) {
        //Set x and y positions
        $this->SetY($y);
        $this->SetX($x);
    }

    function Output($name = '', $dest = '') {
        //Output PDF to some destination
        if ($this->state < 3)
            $this->Close();
        $dest = strtoupper($dest);
        if ($dest == '') {
            if ($name == '') {
                $name = 'doc.pdf';
                $dest = 'I';
            }
            else
                $dest = 'F';
        }
        switch ($dest) {
            case 'I':
                //Send to standard output
                if (ob_get_length())
                    $this->Error('Some data has already been output, can\'t send PDF file');
                if (php_sapi_name() != 'cli') {
                    //We send to a browser
                    header('Content-Type: application/pdf');
                    if (headers_sent())
                        $this->Error('Some data has already been output, can\'t send PDF file');
                    header('Content-Length: ' . strlen($this->buffer));
                    header('Content-Disposition: inline; filename="' . $name . '"');
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    ini_set('zlib.output_compression', '0');
                }
                echo $this->buffer;
                break;
            case 'D':
                //Download file
                if (ob_get_length())
                    $this->Error('Some data has already been output, can\'t send PDF file');
                header('Content-Type: application/x-download');
                if (headers_sent())
                    $this->Error('Some data has already been output, can\'t send PDF file');
                header('Content-Length: ' . strlen($this->buffer));
                header('Content-Disposition: attachment; filename="' . $name . '"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                ini_set('zlib.output_compression', '0');
                echo $this->buffer;
                break;
            case 'F':
                //Save to local file
                $f = fopen($name, 'wb');
                if (!$f)
                    $this->Error('Unable to create output file: ' . $name);
                fwrite($f, $this->buffer, strlen($this->buffer));
                fclose($f);
                break;
            case 'S':
                //Return as a string
                return $this->buffer;
            default:
                $this->Error('Incorrect output destination: ' . $dest);
        }
        return '';
    }

    /*     * *****************************************************************************
     *                                                                              *
     *                              Protected methods                               *
     *                                                                              *
     * ***************************************************************************** */

    function _dochecks() {
        //Check availability of %F
        if (sprintf('%.1F', 1.0) != '1.0')
            $this->Error('This version of PHP is not supported');
        //Check mbstring overloading
        if (ini_get('mbstring.func_overload') & 2)
            $this->Error('mbstring overloading must be disabled');
        //Disable runtime magic quotes
        if (get_magic_quotes_runtime())
            @set_magic_quotes_runtime(0);
    }

    function _getpageformat($format) {
        $format = strtolower($format);
        if (!isset($this->PageFormats[$format]))
            $this->Error('Unknown page format: ' . $format);
        $a = $this->PageFormats[$format];
        return array($a[0] / $this->k, $a[1] / $this->k);
    }

    function _getfontpath() {
        if (!defined('FPDF_FONTPATH') && is_dir(dirname(__FILE__) . '/font'))
            define('FPDF_FONTPATH', dirname(__FILE__) . '/font/');
        return defined('FPDF_FONTPATH') ? FPDF_FONTPATH : '';
    }

    function _beginpage($orientation, $format) {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';
        //Check page size
        if ($orientation == '')
            $orientation = $this->DefOrientation;
        else
            $orientation = strtoupper($orientation[0]);
        if ($format == '')
            $format = $this->DefPageFormat;
        else {
            if (is_string($format))
                $format = $this->_getpageformat($format);
        }
        if ($orientation != $this->CurOrientation || $format[0] != $this->CurPageFormat[0] || $format[1] != $this->CurPageFormat[1]) {
            //New size
            if ($orientation == 'P') {
                $this->w = $format[0];
                $this->h = $format[1];
            } else {
                $this->w = $format[1];
                $this->h = $format[0];
            }
            $this->wPt = $this->w * $this->k;
            $this->hPt = $this->h * $this->k;
            $this->PageBreakTrigger = $this->h - $this->bMargin;
            $this->CurOrientation = $orientation;
            $this->CurPageFormat = $format;
        }
        if ($orientation != $this->DefOrientation || $format[0] != $this->DefPageFormat[0] || $format[1] != $this->DefPageFormat[1])
            $this->PageSizes[$this->page] = array($this->wPt, $this->hPt);
    }

    function _endpage() {
        $this->state = 1;
    }

    function _escape($s) {
        //Escape special characters in strings
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace('(', '\\(', $s);
        $s = str_replace(')', '\\)', $s);
        $s = str_replace("\r", '\\r', $s);
        return $s;
    }

    function _textstring($s) {
        //Format a text string
        return '(' . $this->_escape($s) . ')';
    }

    function _UTF8toUTF16($s) {
        //Convert UTF-8 to UTF-16BE with BOM
        $res = "\xFE\xFF";
        $nb = strlen($s);
        $i = 0;
        while ($i < $nb) {
            $c1 = ord($s[$i++]);
            if ($c1 >= 224) {
                //3-byte character
                $c2 = ord($s[$i++]);
                $c3 = ord($s[$i++]);
                $res.=chr((($c1 & 0x0F) << 4) + (($c2 & 0x3C) >> 2));
                $res.=chr((($c2 & 0x03) << 6) + ($c3 & 0x3F));
            } elseif ($c1 >= 192) {
                //2-byte character
                $c2 = ord($s[$i++]);
                $res.=chr(($c1 & 0x1C) >> 2);
                $res.=chr((($c1 & 0x03) << 6) + ($c2 & 0x3F));
            } else {
                //Single-byte character
                $res.="\0" . chr($c1);
            }
        }
        return $res;
    }

    function _dounderline($x, $y, $txt) {
        //Underline text
        $up = $this->CurrentFont['up'];
        $ut = $this->CurrentFont['ut'];
        $w = $this->GetStringWidth($txt) + $this->ws * substr_count($txt, ' ');
        return sprintf('%.2F %.2F %.2F %.2F re f', $x * $this->k, ($this->h - ($y - $up / 1000 * $this->FontSize)) * $this->k, $w * $this->k, -$ut / 1000 * $this->FontSizePt);
    }

    function _parsejpg($file) {
        //Extract info from a JPEG file
        $a = GetImageSize($file);
        if (!$a)
            $this->Error('Missing or incorrect image file: ' . $file);
        if ($a[2] != 2)
            $this->Error('Not a JPEG file: ' . $file);
        if (!isset($a['channels']) || $a['channels'] == 3)
            $colspace = 'DeviceRGB';
        elseif ($a['channels'] == 4)
            $colspace = 'DeviceCMYK';
        else
            $colspace = 'DeviceGray';
        $bpc = isset($a['bits']) ? $a['bits'] : 8;
        //Read whole file
        $f = fopen($file, 'rb');
        $data = '';
        while (!feof($f))
            $data.=fread($f, 8192);
        fclose($f);
        return array('w' => $a[0], 'h' => $a[1], 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'DCTDecode', 'data' => $data);
    }

    function _parsepng($file) {
        //Extract info from a PNG file
        $f = fopen($file, 'rb');
        if (!$f)
            $this->Error('Can\'t open image file: ' . $file);
        //Check signature
        if ($this->_readstream($f, 8) != chr(137) . 'PNG' . chr(13) . chr(10) . chr(26) . chr(10))
            $this->Error('Not a PNG file: ' . $file);
        //Read header chunk
        $this->_readstream($f, 4);
        if ($this->_readstream($f, 4) != 'IHDR')
            $this->Error('Incorrect PNG file: ' . $file);
        $w = $this->_readint($f);
        $h = $this->_readint($f);
        $bpc = ord($this->_readstream($f, 1));
        if ($bpc > 8)
            $this->Error('16-bit depth not supported: ' . $file);
        $ct = ord($this->_readstream($f, 1));
        if ($ct == 0)
            $colspace = 'DeviceGray';
        elseif ($ct == 2)
            $colspace = 'DeviceRGB';
        elseif ($ct == 3)
            $colspace = 'Indexed';
        else
            $this->Error('Alpha channel not supported: ' . $file);
        if (ord($this->_readstream($f, 1)) != 0)
            $this->Error('Unknown compression method: ' . $file);
        if (ord($this->_readstream($f, 1)) != 0)
            $this->Error('Unknown filter method: ' . $file);
        if (ord($this->_readstream($f, 1)) != 0)
            $this->Error('Interlacing not supported: ' . $file);
        $this->_readstream($f, 4);
        $parms = '/DecodeParms <</Predictor 15 /Colors ' . ($ct == 2 ? 3 : 1) . ' /BitsPerComponent ' . $bpc . ' /Columns ' . $w . '>>';
        //Scan chunks looking for palette, transparency and image data
        $pal = '';
        $trns = '';
        $data = '';
        do {
            $n = $this->_readint($f);
            $type = $this->_readstream($f, 4);
            if ($type == 'PLTE') {
                //Read palette
                $pal = $this->_readstream($f, $n);
                $this->_readstream($f, 4);
            } elseif ($type == 'tRNS') {
                //Read transparency info
                $t = $this->_readstream($f, $n);
                if ($ct == 0)
                    $trns = array(ord(substr($t, 1, 1)));
                elseif ($ct == 2)
                    $trns = array(ord(substr($t, 1, 1)), ord(substr($t, 3, 1)), ord(substr($t, 5, 1)));
                else {
                    $pos = strpos($t, chr(0));
                    if ($pos !== false)
                        $trns = array($pos);
                }
                $this->_readstream($f, 4);
            }
            elseif ($type == 'IDAT') {
                //Read image data block
                $data.=$this->_readstream($f, $n);
                $this->_readstream($f, 4);
            } elseif ($type == 'IEND')
                break;
            else
                $this->_readstream($f, $n + 4);
        }
        while ($n);
        if ($colspace == 'Indexed' && empty($pal))
            $this->Error('Missing palette in ' . $file);
        fclose($f);
        return array('w' => $w, 'h' => $h, 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'FlateDecode', 'parms' => $parms, 'pal' => $pal, 'trns' => $trns, 'data' => $data);
    }

    function _readstream($f, $n) {
        //Read n bytes from stream
        $res = '';
        while ($n > 0 && !feof($f)) {
            $s = fread($f, $n);
            if ($s === false)
                $this->Error('Error while reading stream');
            $n-=strlen($s);
            $res.=$s;
        }
        if ($n > 0)
            $this->Error('Unexpected end of stream');
        return $res;
    }

    function _readint($f) {
        //Read a 4-byte integer from stream
        $a = unpack('Ni', $this->_readstream($f, 4));
        return $a['i'];
    }

    function _parsegif($file) {
        //Extract info from a GIF file (via PNG conversion)
        if (!function_exists('imagepng'))
            $this->Error('GD extension is required for GIF support');
        if (!function_exists('imagecreatefromgif'))
            $this->Error('GD has no GIF read support');
        $im = imagecreatefromgif($file);
        if (!$im)
            $this->Error('Missing or incorrect image file: ' . $file);
        imageinterlace($im, 0);
        $tmp = tempnam('.', 'gif');
        if (!$tmp)
            $this->Error('Unable to create a temporary file');
        if (!imagepng($im, $tmp))
            $this->Error('Error while saving to temporary file');
        imagedestroy($im);
        $info = $this->_parsepng($tmp);
        unlink($tmp);
        return $info;
    }

    function _newobj() {
        //Begin a new object
        $this->n++;
        $this->offsets[$this->n] = strlen($this->buffer);
        $this->_out($this->n . ' 0 obj');
    }

    function _putstream($s) {
        $this->_out('stream');
        $this->_out($s);
        $this->_out('endstream');
    }

    function _out($s) {
        //Add a line to the document
        if ($this->state == 2)
            $this->pages[$this->page].=$s . "\n";
        else
            $this->buffer.=$s . "\n";
    }

    function _putpages() {
        $nb = $this->page;
        if (!empty($this->AliasNbPages)) {
            //Replace number of pages
            for ($n = 1; $n <= $nb; $n++)
                $this->pages[$n] = str_replace($this->AliasNbPages, $nb, $this->pages[$n]);
        }
        if ($this->DefOrientation == 'P') {
            $wPt = $this->DefPageFormat[0] * $this->k;
            $hPt = $this->DefPageFormat[1] * $this->k;
        } else {
            $wPt = $this->DefPageFormat[1] * $this->k;
            $hPt = $this->DefPageFormat[0] * $this->k;
        }
        $filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
        for ($n = 1; $n <= $nb; $n++) {
            //Page
            $this->_newobj();
            $this->_out('<</Type /Page');
            $this->_out('/Parent 1 0 R');
            if (isset($this->PageSizes[$n]))
                $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]', $this->PageSizes[$n][0], $this->PageSizes[$n][1]));
            $this->_out('/Resources 2 0 R');
            if (isset($this->PageLinks[$n])) {
                //Links
                $annots = '/Annots [';
                foreach ($this->PageLinks[$n] as $pl) {
                    $rect = sprintf('%.2F %.2F %.2F %.2F', $pl[0], $pl[1], $pl[0] + $pl[2], $pl[1] - $pl[3]);
                    $annots.='<</Type /Annot /Subtype /Link /Rect [' . $rect . '] /Border [0 0 0] ';
                    if (is_string($pl[4]))
                        $annots.='/A <</S /URI /URI ' . $this->_textstring($pl[4]) . '>>>>';
                    else {
                        $l = $this->links[$pl[4]];
                        $h = isset($this->PageSizes[$l[0]]) ? $this->PageSizes[$l[0]][1] : $hPt;
                        $annots.=sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>', 1 + 2 * $l[0], $h - $l[1] * $this->k);
                    }
                }
                $this->_out($annots . ']');
            }
            $this->_out('/Contents ' . ($this->n + 1) . ' 0 R>>');
            $this->_out('endobj');
            //Page content
            $p = ($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
            $this->_newobj();
            $this->_out('<<' . $filter . '/Length ' . strlen($p) . '>>');
            $this->_putstream($p);
            $this->_out('endobj');
        }
        //Pages root
        $this->offsets[1] = strlen($this->buffer);
        $this->_out('1 0 obj');
        $this->_out('<</Type /Pages');
        $kids = '/Kids [';
        for ($i = 0; $i < $nb; $i++)
            $kids.=(3 + 2 * $i) . ' 0 R ';
        $this->_out($kids . ']');
        $this->_out('/Count ' . $nb);
        $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]', $wPt, $hPt));
        $this->_out('>>');
        $this->_out('endobj');
    }

    function _putfonts() {
        $nf = $this->n;
        foreach ($this->diffs as $diff) {
            //Encodings
            $this->_newobj();
            $this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences [' . $diff . ']>>');
            $this->_out('endobj');
        }
        foreach ($this->FontFiles as $file => $info) {
            //Font file embedding
            $this->_newobj();
            $this->FontFiles[$file]['n'] = $this->n;
            $font = '';
            $f = fopen($this->_getfontpath() . $file, 'rb', 1);
            if (!$f)
                $this->Error('Font file not found');
            while (!feof($f))
                $font.=fread($f, 8192);
            fclose($f);
            $compressed = (substr($file, -2) == '.z');
            if (!$compressed && isset($info['length2'])) {
                $header = (ord($font[0]) == 128);
                if ($header) {
                    //Strip first binary header
                    $font = substr($font, 6);
                }
                if ($header && ord($font[$info['length1']]) == 128) {
                    //Strip second binary header
                    $font = substr($font, 0, $info['length1']) . substr($font, $info['length1'] + 6);
                }
            }
            $this->_out('<</Length ' . strlen($font));
            if ($compressed)
                $this->_out('/Filter /FlateDecode');
            $this->_out('/Length1 ' . $info['length1']);
            if (isset($info['length2']))
                $this->_out('/Length2 ' . $info['length2'] . ' /Length3 0');
            $this->_out('>>');
            $this->_putstream($font);
            $this->_out('endobj');
        }
        foreach ($this->fonts as $k => $font) {
            //Font objects
            $this->fonts[$k]['n'] = $this->n + 1;
            $type = $font['type'];
            $name = $font['name'];
            if ($type == 'core') {
                //Standard font
                $this->_newobj();
                $this->_out('<</Type /Font');
                $this->_out('/BaseFont /' . $name);
                $this->_out('/Subtype /Type1');
                if ($name != 'Symbol' && $name != 'ZapfDingbats')
                    $this->_out('/Encoding /WinAnsiEncoding');
                $this->_out('>>');
                $this->_out('endobj');
            }
            elseif ($type == 'Type1' || $type == 'TrueType') {
                //Additional Type1 or TrueType font
                $this->_newobj();
                $this->_out('<</Type /Font');
                $this->_out('/BaseFont /' . $name);
                $this->_out('/Subtype /' . $type);
                $this->_out('/FirstChar 32 /LastChar 255');
                $this->_out('/Widths ' . ($this->n + 1) . ' 0 R');
                $this->_out('/FontDescriptor ' . ($this->n + 2) . ' 0 R');
                if ($font['enc']) {
                    if (isset($font['diff']))
                        $this->_out('/Encoding ' . ($nf + $font['diff']) . ' 0 R');
                    else
                        $this->_out('/Encoding /WinAnsiEncoding');
                }
                $this->_out('>>');
                $this->_out('endobj');
                //Widths
                $this->_newobj();
                $cw = &$font['cw'];
                $s = '[';
                for ($i = 32; $i <= 255; $i++)
                    $s.=$cw[chr($i)] . ' ';
                $this->_out($s . ']');
                $this->_out('endobj');
                //Descriptor
                $this->_newobj();
                $s = '<</Type /FontDescriptor /FontName /' . $name;
                foreach ($font['desc'] as $k => $v)
                    $s.=' /' . $k . ' ' . $v;
                $file = $font['file'];
                if ($file)
                    $s.=' /FontFile' . ($type == 'Type1' ? '' : '2') . ' ' . $this->FontFiles[$file]['n'] . ' 0 R';
                $this->_out($s . '>>');
                $this->_out('endobj');
            }
            else {
                //Allow for additional types
                $mtd = '_put' . strtolower($type);
                if (!method_exists($this, $mtd))
                    $this->Error('Unsupported font type: ' . $type);
                $this->$mtd($font);
            }
        }
    }

    function _putimages() {
        $filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
        reset($this->images);
        while (list($file, $info) = each($this->images)) {
            $this->_newobj();
            $this->images[$file]['n'] = $this->n;
            $this->_out('<</Type /XObject');
            $this->_out('/Subtype /Image');
            $this->_out('/Width ' . $info['w']);
            $this->_out('/Height ' . $info['h']);
            if ($info['cs'] == 'Indexed')
                $this->_out('/ColorSpace [/Indexed /DeviceRGB ' . (strlen($info['pal']) / 3 - 1) . ' ' . ($this->n + 1) . ' 0 R]');
            else {
                $this->_out('/ColorSpace /' . $info['cs']);
                if ($info['cs'] == 'DeviceCMYK')
                    $this->_out('/Decode [1 0 1 0 1 0 1 0]');
            }
            $this->_out('/BitsPerComponent ' . $info['bpc']);
            if (isset($info['f']))
                $this->_out('/Filter /' . $info['f']);
            if (isset($info['parms']))
                $this->_out($info['parms']);
            if (isset($info['trns']) && is_array($info['trns'])) {
                $trns = '';
                for ($i = 0; $i < count($info['trns']); $i++)
                    $trns.=$info['trns'][$i] . ' ' . $info['trns'][$i] . ' ';
                $this->_out('/Mask [' . $trns . ']');
            }
            $this->_out('/Length ' . strlen($info['data']) . '>>');
            $this->_putstream($info['data']);
            unset($this->images[$file]['data']);
            $this->_out('endobj');
            //Palette
            if ($info['cs'] == 'Indexed') {
                $this->_newobj();
                $pal = ($this->compress) ? gzcompress($info['pal']) : $info['pal'];
                $this->_out('<<' . $filter . '/Length ' . strlen($pal) . '>>');
                $this->_putstream($pal);
                $this->_out('endobj');
            }
        }
    }

    function _putxobjectdict() {
        foreach ($this->images as $image)
            $this->_out('/I' . $image['i'] . ' ' . $image['n'] . ' 0 R');
    }

    function _putresourcedict() {
        $this->_out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
        $this->_out('/Font <<');
        foreach ($this->fonts as $font)
            $this->_out('/F' . $font['i'] . ' ' . $font['n'] . ' 0 R');
        $this->_out('>>');
        $this->_out('/XObject <<');
        $this->_putxobjectdict();
        $this->_out('>>');
    }

    function _putresources() {
        $this->_putfonts();
        $this->_putimages();
        //Resource dictionary
        $this->offsets[2] = strlen($this->buffer);
        $this->_out('2 0 obj');
        $this->_out('<<');
        $this->_putresourcedict();
        $this->_out('>>');
        $this->_out('endobj');
    }

    function _putinfo() {
        $this->_out('/Producer ' . $this->_textstring('FPDF ' . FPDF_VERSION));
        if (!empty($this->title))
            $this->_out('/Title ' . $this->_textstring($this->title));
        if (!empty($this->subject))
            $this->_out('/Subject ' . $this->_textstring($this->subject));
        if (!empty($this->author))
            $this->_out('/Author ' . $this->_textstring($this->author));
        if (!empty($this->keywords))
            $this->_out('/Keywords ' . $this->_textstring($this->keywords));
        if (!empty($this->creator))
            $this->_out('/Creator ' . $this->_textstring($this->creator));
        $this->_out('/CreationDate ' . $this->_textstring('D:' . @date('YmdHis')));
    }

    function _putcatalog() {
        $this->_out('/Type /Catalog');
        $this->_out('/Pages 1 0 R');
        if ($this->ZoomMode == 'fullpage')
            $this->_out('/OpenAction [3 0 R /Fit]');
        elseif ($this->ZoomMode == 'fullwidth')
            $this->_out('/OpenAction [3 0 R /FitH null]');
        elseif ($this->ZoomMode == 'real')
            $this->_out('/OpenAction [3 0 R /XYZ null null 1]');
        elseif (!is_string($this->ZoomMode))
            $this->_out('/OpenAction [3 0 R /XYZ null null ' . ($this->ZoomMode / 100) . ']');
        if ($this->LayoutMode == 'single')
            $this->_out('/PageLayout /SinglePage');
        elseif ($this->LayoutMode == 'continuous')
            $this->_out('/PageLayout /OneColumn');
        elseif ($this->LayoutMode == 'two')
            $this->_out('/PageLayout /TwoColumnLeft');
    }

    function _putheader() {
        $this->_out('%PDF-' . $this->PDFVersion);
    }

    function _puttrailer() {
        $this->_out('/Size ' . ($this->n + 1));
        $this->_out('/Root ' . $this->n . ' 0 R');
        $this->_out('/Info ' . ($this->n - 1) . ' 0 R');
    }

    function _enddoc() {
        $this->_putheader();
        $this->_putpages();
        $this->_putresources();
        //Info
        $this->_newobj();
        $this->_out('<<');
        $this->_putinfo();
        $this->_out('>>');
        $this->_out('endobj');
        //Catalog
        $this->_newobj();
        $this->_out('<<');
        $this->_putcatalog();
        $this->_out('>>');
        $this->_out('endobj');
        //Cross-ref
        $o = strlen($this->buffer);
        $this->_out('xref');
        $this->_out('0 ' . ($this->n + 1));
        $this->_out('0000000000 65535 f ');
        for ($i = 1; $i <= $this->n; $i++)
            $this->_out(sprintf('%010d 00000 n ', $this->offsets[$i]));
        //Trailer
        $this->_out('trailer');
        $this->_out('<<');
        $this->_puttrailer();
        $this->_out('>>');
        $this->_out('startxref');
        $this->_out($o);
        $this->_out('%%EOF');
        $this->state = 3;
    }

//End of class
}

//Handle special IE contype request
if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == 'contype') {
    header('Content-Type: application/pdf');
    exit;
}
?>
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
class HTML {
    /*     * * Generar código HTML con botón y formulario para ejecutar un comando via AJAX ** */

    static function botonAjax($icono, $texto, $destino, $datos = array(), $clase = NULL) {
        global $textos;

        $codigo = HTML::boton($icono, $textos->id($texto), $clase, "", "botonOk");

        foreach ($datos as $nombre => $valor) {
            $codigo .= HTML::campoOculto($nombre, $valor);
        }
        $codigo = HTML::forma($destino, $codigo);

        return $codigo;
    }

    /*     * * Generar código HTML para insertar un icono en línea en un <span> ** */

    static function ayuda($texto) {
        global $configuracion;

        $ruta = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "/ayuda.png";
        $clase = "imagenAyudaTooltip";
        $id = "imagenAyuda";
        $opciones = array("alt" => $texto);
        $codigo = HTML::imagen($ruta, $clase, $id, $opciones);

        return $codigo;
    }

    /*     * * Generar código HTML con botón y formulario para ejecutar un comando via AJAX ** */

    static function botonImagenAjax($contenido, $clase, $id, $opciones, $destino, $datos = array(), $idForma) {
        global $textos;

        $codigo = HTML::contenedor($contenido, $clase, $id, $opciones);

        foreach ($datos as $nombre => $valor) {
            $codigo .= HTML::campoOculto($nombre, $valor);
        }
        $codigo .= "";
        $codigo = HTML::forma($destino, $codigo, "", "", "", "", $idForma);

        return $codigo;
    }

    /*     * * Generar código HTML con botón y formulario para ejecutar un comando via AJAX con cualquier tipo de contenido, ya sea texto, imagen o ambos** */

    static function formaAjax($contenido, $clase, $id, $opciones, $destino, $datos = array(), $idForma = NULL) {

        $codigo = HTML::contenedor(HTML::contenedor($contenido, $clase, $id, $opciones), "enviarAjax", "");
        foreach ($datos as $nombre => $valor) {
            $codigo .= HTML::campoOculto($nombre, $valor);
        }
        $codigo .= ""; //HTML::campoOculto("idQuemado", "", "idQuemado");
        $codigo = HTML::forma($destino, $codigo, "", "", "", "", $idForma);

        return $codigo;
    }

    /*     * * Generar código HTML con botón y formulario para adicionar un item ** */

    static function botonAdicionarItem($url, $titulo) {

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("masGrueso", $titulo);
        $codigo = HTML::forma("/ajax/$url/add", $codigo);

        return $codigo;
    }

    /*     * * Generar código HTML con botón y formulario para modificar un item ** */

    static function botonModificarItem($id, $url) {
        global $textos;

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("lapiz", $textos->id("MODIFICAR"));
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/edit", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    /*     * * Generar código HTML con botón y formulario para eliminar un item desde el listado principal haciendo uso de ajax ** */

    static function botonModificarItemAjax($id, $url, $idContenedor = NULL) {
        global $textos;

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("lapiz", $textos->id("MODIFICAR"));
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/editRegister", $codigo);

        if ($idContenedor == '') {
            $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");
        } else {
            $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", $idContenedor);
        }

        return $codigo;
    }
    //genera el boton editar que se puede ver desde el buscador, funcional solo para el admin
    static function botonModificarItemDesdeBuscador($id, $url) {
        $url = preg_replace("|^\/|", "", $url);

        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-pencil'></span>
                            <span class='ui-button-text'>
                                Edit
                            </span>
                            </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/edit", $codigo);
        $codigo = HTML::contenedor($codigo, "botonModificarItemBuscador flotanteDerecha medioMargenDerecha", "botonModificarItemBuscador");

        return $codigo;
    }

    /*     * * Generar código HTML con botón y formulario para eliminar un item ** */

    static function botonEliminarItem($id, $url, $idContenedor = NULL) {
        global $textos;

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("basura", $textos->id("ELIMINAR"));
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/delete", $codigo);

        if ($idContenedor == '') {
            $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");
        } else {
            $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", $idContenedor);
        }

        return $codigo;
    }

    /*     * * Generar código HTML con botón y formulario para eliminar un item desde el listado principal haciendo uso de ajax ** */

    static function botonEliminarItemAjax($id, $url, $idContenedor = NULL) {
        global $textos;

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("basura", $textos->id("ELIMINAR"), "", "", "nuevoBoton");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/deleteRegister", $codigo);
        if ($idContenedor == '') {
            $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");
        } else {
            $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", $idContenedor);
        }

        return $codigo;
    }

    /*     * * Generar código HTML con botón y formulario para eliminar un item desde el listado principal haciendo uso de ajax** */

    static function botonEliminarItemDesdeBuscador($id, $url) {
        $url = preg_replace("|^\/|", "", $url);
        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Delete
                            </span>
                    </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/delete", $codigo);
        $codigo = HTML::contenedor($codigo, "botonEliminarItemBuscador flotanteDerecha", "botonEliminarItemBuscador");

        return $codigo;
    }

    /*     * * Generar código HTML con botón y formulario para aprobar un item ** */

    static function botonAprobarItem($id, $url) {

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("chequeo", $textos->id("APROBAR"));
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/approve", $codigo);


        return $codigo;
    }

    /*     * * Generar código HTML con botón y formulario para subir un item un nivel ** */

    static function botonSubirItem($id, $url) {
        global $textos;

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("flechaGruesaArriba", $textos->id("SUBIR"));
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/up", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    /*     * * Generar código HTML con botón y formulario para bajar un item un nivel ** */

    static function botonBajarItem($id, $url) {
        global $textos;

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("flechaGruesaAbajo", $textos->id("BAJAR"));
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/down", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    /*     * * Generar código HTML para insertar un icono en línea en un <span> ** */

    static function icono($icono) {
        global $configuracion;

        if (array_key_exists($icono, $configuracion["ICONOS"])) {
            $icono = "ui-icon-" . $configuracion["ICONOS"][$icono];
        }
        $codigo = "<span class=\"ui-icon $icono icono\" style=\"display: inline-block;\"></span>";

        return $codigo;
    }

    /*     * * Generar código HTML para insertar un icono en línea en un <span> ** */

    static function icono2($icono) {
        global $configuracion;

        $codigo = "<span class=\" $icono \" style=\"display: inline-block;\"></span>";

        return $codigo;
    }

    /*     * * Generar código HTML para resaltar una frase con <span> ** */

    static function frase($contenido, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = ' <span';

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = " ' . $valor . ' "  ';
            }
        }

        $codigo .= '>';

        if (!empty($contenido) && is_string($contenido)) {
            $codigo .= '     ' . $contenido . ' ';
        }

        $codigo .= '</span>';

        return $codigo;
    }

    /*     * * Generar código HTML para un contenedor (div) ** */

    static function contenedor($contenido = NULL, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <div';

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id = ' . "$id" . ' ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '"';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . '="' . $valor . '" ';
            }
        }

        $codigo .= '>';

        if (!empty($contenido) && is_string($contenido)) {
            $codigo .= '     ' . $contenido . '';
        }

        $codigo .= '    </div>';

        return $codigo;
    }

    /*     * * Generar código HTML para insertar un enlace ** */

    static function enlace($texto, $destino = NULL, $clase = NULL, $id = NULL, $opciones = NULL) {
        global $configuracion;

        if (empty($destino)) {
            $destino = $texto;
        }

        $codigo = '     <a href="' . $destino . '" ';

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        $servidor = addslashes($configuracion["SERVIDOR"]["principal"]);

        if (preg_match("|^(https?\:\/\/)|", $destino) && !preg_match("|(^" . $servidor . ")|", $destino)) {
            $codigo .= ' target="_blank"';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . '="' . $valor . '" ';
            }
        }

        $codigo .= '>';

        if (!empty($texto) && is_string($texto)) {
            $codigo .= $texto;
        }

        $codigo .= '</a>';

        return $codigo;
    }

    /*     * * Generar código HTML para insertar un enlace con la Estrella de Fondo** */

    static function enlaceEstrella($texto, $destino = NULL, $clase = "claseEstrella", $id = "claseEstrella", $opciones = NULL) {
        global $configuracion;

        if (empty($destino)) {
            $destino = $texto;
        }

        $codigo = "     <a href=\"$destino\"";

        if (!empty($clase) && is_string($clase)) {
            $codigo .= " class=\"claseEstrella\"";
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= " id=\"claseEstrella\"";
        }

        $servidor = addslashes($configuracion["SERVIDOR"]["principal"]);

        if (preg_match("|^(https?\:\/\/)|", $destino) && !preg_match("|(^" . $servidor . ")|", $destino)) {
            $codigo .= " target=\"_blank\"";
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= " $atributo=\"$valor\"";
            }
        }

        $codigo .= ">";

        if (!empty($texto) && is_string($texto)) {
            $codigo .= $texto;
        }

        $codigo .= "</a>";

        return $codigo;
    }

    /*     * * Generar código HTML para insertar un párrafo ** */

    static function parrafo($contenido = NULL, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <p';

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= '>';

        if (!empty($contenido) && is_string($contenido)) {
            $codigo .= '     ' . $contenido . ' ';
        }

        $codigo .= '     </p> ';

        return $codigo;
    }

    /*     * * Generar código HTML para insertar una lista ** */

    static function lista($contenido = NULL, $claseLista = NULL, $claseItems = NULL, $id = NULL, $opciones = NULL) {

        if (!is_array($contenido) || !count($contenido)) {
            return NULL;
        }

        $codigo = '     <ul';

        if (!empty($claseLista) && is_string($claseLista)) {
            $codigo .= ' class="' . $claseLista . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . '="' . $valor . '" ';
            }
        }

        $codigo .= '>';

        foreach ($contenido as $item) {
            $codigo .= '      <li';

            if (!empty($claseItems) && is_string($claseItems)) {
                $codigo .= ' class="' . $claseItems . '" ';
            }

            $codigo .= '>' . $item . '</li>';
        }

        $codigo .= '     </ul>';

        return $codigo;
    }

    /*     * * Generar código HTML para insertar una lista ** */

    static function listaEstrella($contenido = NULL, $claseLista = NULL, $claseItems = NULL, $id = NULL, $opciones = NULL) {

        if (!is_array($contenido) || !count($contenido)) {
            return NULL;
        }

        $codigo = '     <ul';

        if (!empty($claseLista) && is_string($claseLista)) {
            $codigo .= ' class="' . $claseLista . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . '="' . $valor . '" ';
            }
        }

        $codigo .= '>';

        foreach ($contenido as $item) {
            $codigo .= '      <li';

            if (!empty($claseItems) && is_string($claseItems)) {
                $codigo .= ' class="' . $claseItems . '" ';
            }

            $codigo .= '><div class = "listaEstrella">' . $item . '</div></li>';
        }

        $codigo .= '     </ul>';

        return $codigo;
    }

    /*     * * Generar código HTML para insertar una imagen ** */

    static function imagen($ruta, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <img src="' . $ruta . '" ';

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            if (!array_key_exists("alt", $opciones)) {
                $codigo .= ' alt="" ';
            }

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        } else {
            $codigo .= ' alt="" ';
        }

        $codigo .= ' />';

        return $codigo;
    }

    /*     * *************************************** Generar código HTML para insertar un bloque *********************************************** */

    static function bloque($id, $titulo, $contenido, $claseTitulo = NULL, $claseContenido = NULL, $smaller = NULL) {//le agregue el parametro smalle para saber cuando debe ser un encabezado mas pequeño
        $codigo = '     <div id="' . $id . '" class="bloque ui-widget ui-corner-all">';

        /* En esta parte verifico que estilo debe de llevar el titulo del bloque que voy a trabajar, es condicionado por el valor del sexto parametro de la funcion */
        if ($smaller == "-IS") {
            $codigo .= '     <div class="encabezadoBloque-IS ' . $claseTitulo . ' "><span class = "bloqueTitulo-IS ui-helper-clearfix ui-widget-header">' . $titulo . '</span></div>';
        }

        if ($smaller == "-DS") {
            $codigo .= '      <div class= "encabezadoBloque-DS ' . $claseTitulo . ' "><span class ="bloqueTitulo-DS ui-helper-clearfix ui-widget-header">' . $titulo . '</span></div> ';
        }

        if ($smaller == NULL) {
            $codigo .= '     <div class= "encabezadoBloque ' . $claseTitulo . ' "><span class ="bloqueTitulo ui-helper-clearfix ui-widget-header">' . $titulo . '</span></div>';

            //$codigo .= HTML::boton("anterior", "Go Back", "botonVolver");
        }

        /*         * ******************************************************************************************************************************************** */
        //$codigo  = "     <div clas =\"divTituloBloque\">$codigo2</div>";
        $codigo .= '     <div class= "contenidoBloque ' . $claseContenido . ' "> ';
        $codigo .= '     ' . $contenido . '';
        $codigo .= '     </div>';
        $codigo .= '     <div class = "sombraInferior"></div>';
        $codigo .= '     </div>';
        return $codigo;
    }

    /*     * * Generar código HTML para insertar el bloque de las noticias** */

//    static function bloqueNoticias($id, $contenido, $claseContenido = NULL) {
//        $codigo = '     <div id="' . $id . '" class="bloque ui-widget ui-corner-all">';
//
//        $codigo .= '     <div class= "bloqueResumenNoticias ' . $claseContenido . '"> ';
//        $codigo .= '     ' . $contenido . '';
//        $codigo .= '     </div>';
//        $codigo .= '     <div class= "sombraInferior"></div>';
//        $codigo .= '     </div>';
//        return $codigo;
//    }


    static function bloqueNoticias($arregloNoticias) {


      if(!preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT']) ){


        $codigo = '     <div id="da-slider" class="da-slider">';

        foreach ($arregloNoticias as $noticia) {

            $codigo .= '     <div class="da-slide"> ';
            $codigo .= '        <h2>'.$noticia["titulo"].'</h2>';
            $codigo .= '         <p>'.$noticia["resumen"].'</p>';
            $codigo .= '         <a href="'.$noticia["ruta"].'" class="da-link">Read more</a>';
            $codigo .= '         <div class="da-img">';
            $codigo .=              $noticia['imagen'];
            $codigo .= '         </div>';
            $codigo .= '     </div>';
            
        }

        $codigo .= '<nav class="da-arrows">';
        $codigo .= '    <span class="da-arrows-prev"></span>';
        $codigo .= '    <span class="da-arrows-next"></span>';
        $codigo .= '</nav>';
        $codigo .= '     </div>';


        

      } else {

        $codigo = '<div id="contenedorNotiCultural"><ol>';

       foreach ($arregloNoticias as $noticia) {

            $codigo .= "
                          <li>
                            <h2><span>" . substr($noticia["titulo"], 0, 25) . "...</span></h2>
                            <div>
                                <figure>
                                   <a href='" . $noticia["ruta"] . "'>". $noticia['imagen']." </a>
                                    <figcaption>" . $noticia["resumen"] . "</figcaption>
                                </figure>
                            </div>
                          </li>
                        ";
        }

        $codigo .= "<noscript>
                <p>Please enable JavaScript to get the full experience.</p>
            </noscript>";
        $codigo .= "</ol></div>";
	$codigo  = HTML::contenedor($codigo, 'sliderInicio');



      }
    return $codigo;

    }
    //esta funcion genera el bloque de codigo para el resumen de noticias en el modulo cultural
    static function bloqueNoticiasCultural() {


        $codigo = '<div id="contenedorNotiCultural"><ol>';

       foreach ($arregloNoticias as $noticia) {

            $codigo .= "
                          <li>
                            <h2><span>" . substr($noticia["titulo"], 0, 25) . "...</span></h2>
                            <div>
                                <figure>
                                   <a href='" . $noticia["ruta"] . "'>". $noticia['imagen']." </a>
                                    <figcaption>" . $noticia["resumen"] . "</figcaption>
                                </figure>
                            </div>
                          </li>
                        ";
        }

        $codigo .= "<noscript>
                <p>Please enable JavaScript to get the full experience.</p>
            </noscript>";
        $codigo .= "</ol></div>";

        return $codigo;
    }

    static function bloquePublicidadSuperior() {
        $codigo = '';
        $codigo = '<div class="slider-wrapper">
                        <ul id="sliderPublicidadSuperior" class="sliderPublicidad">
                            <li>
                            <img src="http://media.ablaonline.org/imagen/publicidad/publi_2.jpg" alt="" />
                            </li>
                            <li>
                            <img src="http://media.ablaonline.org/imagen/publicidad/publi_1.jpg" alt="" />
                            </li>
                            <li>
                            <img src="http://media.ablaonline.org/imagen/publicidad/publi_3.jpg" alt="" />
                            </li>
                            <li>
                            <img src="http://media.ablaonline.org/imagen/publicidad/publi_4.jpg" alt="" />
                            </li>
                        </ul>
                    </div>';
        return $codigo;
    }

    static function bloquePublicidadInferior() {
        $codigo = '';
        $codigo = '<div class="slider-wrapper-inferior">
                        <ul id="sliderPublicidadInferior" class="sliderPublicidad">
                            <li>
                            <img src="http://media.ablaonline.org/imagen/publicidad/publi_2.jpg" alt="" />
                            </li>
                            <li>
                            <img src="http://media.ablaonline.org/imagen/publicidad/publi_1.jpg" alt="" />
                            </li>
                            <li>
                            <img src="http://media.ablaonline.org/imagen/publicidad/publi_3.jpg" alt="" />
                            </li>
                            <li>
                            <img src="http://media.ablaonline.org/imagen/publicidad/publi_4.jpg" alt="" />
                            </li>
                        </ul>
                    </div>';
        return $codigo;
    }

    /*     * * Generar código HTML para formulario ** */

    static function forma($destino, $contenido, $metodo = "P", $incluyeArchivos = false, $id = NULL, $opciones = NULL, $name = NULL) {
        global $configuracion;

        $codigo = '     <form action="' . $destino . '" ';

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($name) && is_string($name)) {
            $codigo .= ' name="' . $name . '"';
        }

        if (strtoupper($metodo) == "P") {
            $codigo .= ' method="post"';
        } elseif (strtoupper($metodo) == "G") {
            $codigo .= " method=\"get\"";
        } else {
            $codigo .= ' method="post"';
        }

        if ($incluyeArchivos) {
            $codigo .= ' enctype="multipart/form-data"';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= '>';
        $codigo .= '     <fieldset>';

        if ($incluyeArchivos) {
            //$codigo .= '     <input type="hidden" name="MAX_FILE_SIZE" value="' . $configuracion["DIMENSIONES"]["maximoPesoArchivo"] . ' " />';
        }

        $codigo .= '     ' . $contenido . '';
        $codigo .= '     </fieldset>';
        $codigo .= '     </form>';
        return $codigo;
    }

    /*     * * Generar código HTML para campo de captura de texto de una línea ** */

    static function campoTexto($nombre, $longitud, $limite = NULL, $valorInicial = NULL, $clase = NULL, $id = NULL, $opciones = NULL, $ayuda = NULL) {
        $codigo = '     <input type="text" name="' . $nombre . '" size="' . $longitud . '" ';

        if (!empty($limite) && is_int($limite)) {
            $codigo .= ' maxlength="' . $limite . '" ';
        }

        if (!empty($valorInicial) && is_string($valorInicial)) {
            $codigo .= ' value="' . $valorInicial . '" ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= ' />';

        if (!empty($ayuda) && is_string($ayuda)) {
            $codigo .= HTML::ayuda($ayuda);
        }

        return $codigo;
    }

    static function cargarIconoAyuda($texto) {
        global $textos;
        $codigo = "";
        $codigo .= HTML::campoOculto("textoAyudaModulo", $texto, "textoAyudaModulo");
        $codigo = HTML::contenedor($codigo, "contenedorImagenAyuda", "contenedorImagenAyuda");

        return $codigo;
    }

    /*     * * Generar código HTML para campo de texto oculto ** */

    static function campoOculto($nombre, $valorInicial = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <input type="hidden" name="' . $nombre . '" value="' . $valorInicial . '" ';

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= ' />';
        return $codigo;
    }

    /*     * * Generar código HTML para la selección de un archivo ** */

    static function campoArchivo($nombre, $valorInicial = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <input type="file" name="' . $nombre . '" value="' . $valorInicial . '" ';

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= ' />';
        return $codigo;
    }

    /*     * * Generar código HTML para campo de chequeo (checkbox) ** */

    static function campoChequeo($nombre, $chequeado = false, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <input type="checkbox" name="' . $nombre . '" ';

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if ($chequeado) {
            $codigo .= ' checked="true" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . '="' . $valor . '" ';
            }
        }

        $codigo .= ' />';
        return $codigo;
    }

    /*     * * Generar código HTML para un Radio Button** */

    static function radioBoton($nombre, $chequeado = NULL, $clase = NULL, $valor = NULL, $opciones = NULL, $id = NULL) {
        $codigo = '     <input type = "radio" name = "' . $nombre . '" ';

        if (!empty($valor) && is_string($valor)) {
            $codigo .= ' value = "' . $valor . '" ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class = "' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id = "' . $id . '" ';
        }

        if ($chequeado) {
            $codigo .= ' checked ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= ' />';
        return $codigo;
    }

    /*     * * Generar código HTML para campo de captura de texto de múltiples línea ** */

    static function areaTexto($nombre, $filas, $columnas, $valorInicial = NULL, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <textarea name="' . $nombre . '" rows="' . $filas . '" cols="' . $columnas . '"';

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '"';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '"';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' ="' . $valor . '" ';
            }
        }

        $codigo .= '>' . $valorInicial . '</textarea>';
        return $codigo;
    }

    /*     * * Generar código HTML para presentar nombres de los campos (etiquetas) ** */

    static function etiqueta($texto, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <span';

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="etiqueta ' . $clase . '"';
        } else {
            $codigo .= ' class="etiqueta" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= '>' . $texto . ':</span>';

        return $codigo;
    }

    /*     * * Generar código HTML para campo de captura de palabra clave ** */

    static function campoClave($nombre, $longitud, $limite = NULL, $valorInicial = NULL, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <input type="password" name="' . $nombre . '" size="' . $longitud . '"';

        if (!empty($limite) && is_int($limite)) {
            $codigo .= ' maxlength="' . $limite . '" ';
        }

        if (!empty($valorInicial) && is_string($valorInicial)) {
            $codigo .= ' value="' . $valorInicial . '" ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' $atributo="' . $valor . '" ';
            }
        }

        $codigo .= ' />';
        return $codigo;
    }

    /*     * * Generar lista desplegable ** */

    static function listaDesplegable($nombre, $contenido, $valorInicial = NULL, $clase = NULL, $id = NULL, $primerItem = NULL, $opciones = NULL) {
        global $sql;

        $codigo = '     <select name="' . $nombre . '" ';

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= '>';

        if (!empty($primerItem) && is_string($primerItem)) {
            $codigo .= '     <option>' . $primerItem . '</option> ';
        }

        /*         * * La lista debe ser generada a partir del resultado de una consulta ** */
        if (is_resource($contenido)) {
            while ($datos = $sql->filaEnArreglo($contenido)) {
                $codigo .= '     <option ' . $elegido . '>' . $datos[1] . '</option>';
            }

            /*             * * La lista debe ser generada a partir de un arreglo ** */
        } elseif (is_array($contenido)) {

            foreach ($contenido as $valor => $texto) {

                if ($valor == $valorInicial) {
                    $elegido = 'selected';
                } else {
                    $elegido = '';
                }

                $codigo .= '     <option ' . $elegido . ' value="' . $valor . '">' . $texto . '</option>';
            }
        }

        $codigo .= '     </select>';

        return $codigo;
    }

    /*     * * Generar código HTML para visualizar un botón ** */

    static function boton($icono = NULL, $texto = NULL, $clase = NULL, $nombre = NULL, $id = NULL, $accion = NULL, $opciones = NULL) {
        global $configuracion;

        $codigo = '     <button ';

        if (empty($texto)) {
            $claseBoton = 'botonIcono';
        } else {

            if (empty($icono)) {
                $claseBoton = 'botonTexto';
            } else {
                $claseBoton = 'botonTextoIcono';
            }
        }

        if (!empty($nombre) && is_string($nombre)) {
            $codigo .= ' name="' . $nombre . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($accion) && is_string($accion)) {
            $codigo .= ' onclick="' . $accion . '" ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $claseBoton . ' ' . $clase . '" ';
        } else {
            $codigo .= ' class="' . $claseBoton . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        if (!empty($icono)) {

            if (array_key_exists($icono, $configuracion["ICONOS"])) {
                $icono = 'ui-icon-' . $configuracion["ICONOS"][$icono];
                $codigo .= ' title="' . $icono . '" ';
            }
        }

        $codigo .= '>';

        if (!empty($texto)) {
            $codigo .= $texto;
        }

        $codigo .= '</button>';

        return $codigo;
    }

    /*     * * Generar código HTML para visualizar un botón solamente con una imagen** */

    static function botonImagen($ruta = NULL, $title) {
        global $configuracion;

        $codigo = "     <button ";

        $codigo .= " onclick=\"submit\"";

        $codigo .= " title=\"$title\"";

        $codigo .= " class=\"\"";

        $codigo .= ">\n";

        $codigo .= HTML::imagen("$ruta");

        $codigo .= "</button>\n";

        return $codigo;
    }

    /*     * * Generar Botón personalizado por mi ** */

    static function botonEstrella($icono = NULL, $texto = NULL, $clase = NULL, $nombre = NULL, $id = NULL, $accion = NULL, $opciones = NULL) {
        global $configuracion;

        $codigo = "     <button ";

        if (empty($texto)) {
            $claseBoton = "botonIcono";
        } else {

            if (empty($icono)) {
                $claseBoton = "botonTexto";
            } else {
                $claseBoton = "botonTextoIcono";
            }
        }

        if (!empty($nombre) && is_string($nombre)) {
            $codigo .= " name=\"$nombre\"";
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= " id=\"$id\"";
        }

        if (!empty($accion) && is_string($accion)) {
            $codigo .= " onclick=\"$accion\"";
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= " class=\"$claseBoton $clase\"";
        } else {
            $codigo .= " class=\"$claseBoton\"";
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= " $atributo=\"$valor\"";
            }
        }

        if (!empty($icono)) {

            if (array_key_exists($icono, $configuracion["ICONOS"])) {
                $icono = "ui-icon-" . $configuracion["ICONOS"][$icono];
                $codigo .= " title=\"$icono\"";
            }
        }

        $codigo .= ">\n";

        if (!empty($texto)) {
            $codigo .= $texto;
        }

        $codigo .= "</button>\n";

        return $codigo;
    }

    /*     * * Generar código HTML para insertar juego de pestañas de altura variable ** */

    static function pestanas($id, $pestanas) {
        $codigo = '     <div id="' . $id . '" class="pestanas margenInferior">';

        if (is_array($pestanas)) {
            $contador = 0;
            $titulos = '';
            $contenidos = '';

            foreach ($pestanas as $titulo => $contenido) {
                $contador++;
                $titulos .= '      <li id= "li_' . $id . '_' . $contador . '"><a href= "#' . $id . '_' . $contador . '">' . $titulo . '</a></li> ';
                $contenidos .= '      <div id="' . $id . '_' . $contador . '" class= "contenidoPestana"> ';
                $contenidos .= '      ' . $contenido . ' ';
                $contenidos .= '      </div> ';
            }

            $codigo .= '     <ul class="listaPestanas"> ';
            $codigo .= $titulos;
            $codigo .= '     </ul> ';
            $codigo .= $contenidos;
        }

        $codigo .= '     <div class="sombraInferior"></div>';
        $codigo .= '     </div>';

        return $codigo;
    }

    /*     * *  .....Pestañas modificadas por pablo.....  ** */

    static function pestanas2($id, $pestanas) {
        $codigo = '     <div id="' . $id . '" class="pestanas margenInferior">';


        if (is_array($pestanas)) {
            $contador = 0;
            $titulos = '';
            $contenidos = '';

            foreach ($pestanas as $titulo => $contenido) {
                $contador++;
                $titulos .= '      <li id= "li_' . $id . '_' . $contador . '"><a href="#' . $id . '_' . $contador . '">' . $titulo . '</a></li>';
                $contenidos .= '      <div id="' . $id . '_' . $contador . '" class="contenidoPestana">';
                $contenidos .= '      ' . $contenido . '';
                $contenidos .= '      </div>';
            }

            $codigo .= '     <ul class="listaPestanas">';
            $codigo .= $titulos;
            $codigo .= '     </ul>';
            //$codigo .= "     ";
            $codigo .= $contenidos;
        }

        $codigo .= '     <div class="sombraInferior"></div>';
        $codigo .= '     </div>';

        return $codigo;
    }

    static function acordeon($titulos, $contenidos, $id = NULL, $claseContenedor = NULL, $claseTitulo = NULL, $claseContenido = NULL, $opciones = NULL, $imagen = NULL) {

        $codigo = '     <div ';

        if (!empty($claseContenedor) && is_string($claseContenedor)) {
            $codigo .= ' class="acordeon ' . $claseContenedor . '"';
        } else {
            $codigo .= ' class="acordeon"';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '"';
            }
        }

        $codigo .= '>';

        for ($i = 0; $i < count($titulos); $i++) {
            //si el titulo es el del chat, entonces si
            if ($imagen["nombre"] == $titulos[$i]) {
                $imagenChat = $imagen["imagen"];
            } else {
                $imagenChat = '';
            }

            $codigo2 = '      <h4';

            if (!empty($clase) && is_string($clase)) {
                $codigo2 .= ' class="' . $clase . '" ';
            }

            $codigo2 .= '><a href="#">' . $titulos[$i] . ' ';
            $codigo2 .= '<div class = "borde"></div></a></h4>' . $imagenChat . ' ';
            $codigo .= '<div class ="acordion">' . $codigo2 . '</div> ';

            $codigo .= '      <div class="contenidoAcordeon">';
            $codigo .= '      ' . $contenidos[$i];
            $codigo .= '      </div> ';
        }

        $codigo .= '     </div>';  //http://cms.template-help.com/prestashop_29958/index.php

        $codigo .= "";
        return $codigo;
    }

    static function acordeonLargo($titulos, $contenidos, $id = NULL, $claseContenedor = NULL, $claseTitulo = NULL, $claseContenido = NULL, $opciones = NULL) {

        $codigo = '     <div ';

        if (!empty($claseContenedor) && is_string($claseContenedor)) {
            $codigo .= ' class="acordeon ' . $claseContenedor . '" ';
        } else {
            $codigo .= ' class="acordeon"';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= '>';

        for ($i = 0; $i < count($titulos); $i++) {

            $codigo2 = '      <h4 ';

            if (!empty($clase) && is_string($clase)) {
                $codigo2 .= ' class="' . $clase . '" ';
            }

            $codigo2 .= '><a href="#">' . $titulos[$i] . '';
            $codigo2 .= '<div class = "borde"></div></a></h4>';
            $codigo .= '<div class ="acordionLargo">' . $codigo2 . '</div>';

            $codigo .= '      <div class="contenidoAcordeon">';
            $codigo .= '      ' . $contenidos[$i];
            $codigo .= '      </div>';
        }

        $codigo .= '     </div>';

        $codigo .= '';
        return $codigo;
    }

    /**
     *
     * Metodo que muestra un acordeon y lista su contenido con base al tamaño del arreglo del contenido y no de los titulos
     * a diferencia del metodo acordeon
     *
     * */
    static function acordeonLargo2($titulos, $contenidos, $id = NULL, $claseContenedor = NULL, $claseTitulo = NULL, $claseContenido = NULL, $opciones = NULL) {

        $codigo = '     <div ';

        if (!empty($claseContenedor) && is_string($claseContenedor)) {
            $codigo .= ' class="acordeon ' . $claseContenedor . '" ';
        } else {
            $codigo .= ' class="acordeon"';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '"';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= '>';

        for ($i = 0; $i < count($titulos); $i++) {

            $codigo2 = '      <h4';

            if (!empty($clase) && is_string($clase)) {
                $codigo2 .= ' class= "' . $clase . '" ';
            }

            $codigo2 .= '><a href="#">' . $titulos[$i] . '';
            $codigo2 .= '<div class = "borde"></div></a></h4>';
            $codigo .= '<div class ="acordionLargo">' . $codigo2 . '</div>';

            $codigo .= '      <div class="contenidoAcordeon">';
            for ($j = 0; $j < count($contenidos); $j++) {
                $codigo .= '      ' . $contenidos[$j];
            }
            $codigo .= '      </div>';
        }

        $codigo .= '     </div>';

        $codigo .= '';
        return $codigo;
    }

    static function contenedorCampos($campo1, $campo2) {
        $codigo = '';
        $codigo1 = HTML::contenedor($campo1, 'ancho50Por100 alineadoIzquierda');
        $codigo2 = HTML::contenedor($campo2, 'ancho50Por100 alineadoIzquierda');

        $codigo .= HTML::contenedor($codigo1 . $codigo2, 'contenedorCampos');

        return $codigo;
    }

    static function tabla($columnas, $filas, $clase = NULL, $id = NULL, $claseColumnas = NULL, $claseFilas = NULL, $opciones = NULL) {
        $codigo = "     <table ";

        if (!empty($id) && is_string($id)) {
            $codigo .= " id=\"$id\"";
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= " class=\"$clase\"";
        }
        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= " $atributo=\"$valor\"";
            }
        }

        $codigo .= ">\n";

        if (!empty($columnas)) {
            $codigo .= "     <tr>\n";
            $contador = 0;

            foreach ($columnas as $id => $columna) {
                $codigo .= "     <th";

                if (!empty($id) && is_string($id)) {
                    $codigo .= " id=\"$id\"";
                }

                if (!empty($claseColumnas) && is_array($claseColumnas)) {
                    $codigo .= " class=\"" . $claseColumnas[$contador] . "\"";
                }

                $codigo .= ">\n";
                $codigo .= "$columna</th>\n";
                $contador++;
            }
            $codigo .= "     </tr>\n";
        }

        if (!empty($filas)) {

            foreach ($filas as $fila => $celdas) {
                $codigo .= "     <tr>\n";
                $contador = 0;

                foreach ($celdas as $id => $celda) {
                    $codigo .= "     <td";

                    if (!empty($id) && is_string($id)) {
                        $codigo .= " id=\"$id\"";
                    }

                    if (!empty($claseFilas) && is_array($claseFilas)) {
                        $codigo .= " class=\"" . $claseFilas[$contador] . "\"";
                    }

                    $codigo .= ">\n";
                    $codigo .= "$celda</td>\n";
                    $contador++;
                }

                $codigo .= "     </tr>\n";
            }
        }

        $codigo .= "     </table>";
        return $codigo;
    }

    static function sombra() {
        return "     <div class=\"sombra\"></div>";
    }

    static function mapa($datos, $destino, $ipo = "satellite") {
        $codigo = "
  <script type=\"text/javascript\">
      google.load(\"visualization\", \"1\", {packages:[\"map\"]});
      google.setOnLoadCallback(generarMapa);
      function generarMapa() {
        var datos = new google.visualization.DataTable();
        datos.addColumn(\"number\", \"Lat\");
        datos.addColumn(\"number\", \"Long\");
        datos.addColumn(\"string\", \"Name\");
        datos.addRows([
            $datos
        ]);
        var mapa = new google.visualization.Map(document.getElementById(\"$destino\"));
        mapa.draw(datos, {showTip: true, zoomLevel: 3, mapType: \"$tipo\"});
      }
    </script>
        ";

        return $codigo;
    }

    static function mapaCiudades($datos, $destino, $selectorPais = "") {

        $datos = "[" . implode(",\n", $datos) . "]";
        $codigo = "
  <script type=\"text/javascript\">
    var geocoder;
    var map;

    function iniciarMapas() {
        var latlng      = new google.maps.LatLng(-5, -50);
        var sedes       = $datos;
        var myOptions   = {
          zoom: 2,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById(\"$destino\"), myOptions);

        var companyLogo = new google.maps.MarkerImage('http://media.ablaonline.org/imagen/estaticas/marker.png',
            new google.maps.Size(35,50),
            new google.maps.Point(0,0),
            new google.maps.Point(10,50)
        );

        var companyShadow = new google.maps.MarkerImage('http://media.ablaonline.org/imagen/estaticas/shadow-marker.png',
            new google.maps.Size(50,50),
            new google.maps.Point(0,0),
            new google.maps.Point(10,50)
        );

        for (i=0; i<sedes.length; i++) {

            var myLatlng    = new google.maps.LatLng(sedes[i][0], sedes[i][1]);
            var marker      = new google.maps.Marker({
                map: map,
                position: myLatlng,
                title: sedes[i][2],
                icon: companyLogo,
                shadow: companyShadow
            });
        }
    }


    function createMarker(latlng, html) {
        var contentString = html;
        var marker = new google.maps.Marker({
            position: latlng,
            map: map,
            zIndex: Math.round(latlng.lat()*-100000)<<5
            });

        google.maps.event.addListener(marker, 'click', function() {
            infowindow.setContent(contentString);
            infowindow.open(map,marker);
            });
    }

    function ubicarPais(selector) {
        var pais = document.getElementById(selector).value;

        geocoder.getLocations(pais, function(response) {

            if ((response.Status.code == 200) && (response.Placemark.length > 0)) {
                var box    = response.Placemark[0].ExtendedData.LatLonBox;
                var sw     = new GLatLng(box.south,box.west);
                var ne     = new GLatLng(box.north,box.east);
                var bounds = new GLatLngBounds(sw,ne);
                centerAndZoomOnBounds(bounds);
            }
        });
    }

    function centerAndZoomOnBounds(bounds) {
        var center = bounds.getCenter();
        var newZoom = map.getBoundsZoomLevel(bounds);
            if (map.getZoom() != newZoom) {
            map.setCenter(center, newZoom);
        }   else {
            map.panTo(center);
        }
    }


    </script>
";

        return $codigo;
    }

    static function botonesCompartir() {
        $codigo = "
    <div class=\"addthis_toolbox addthis_default_style\">
     <a class=\"addthis_button_facebook\"></a>
     <a class=\"addthis_button_twitter\"></a>     
     <a class=\"addthis_button_google\"></a>
     <a class=\"addthis_button_email\"></a>
     <a class=\"addthis_button_favorites\"></a>
     <a class=\"addthis_button_print\"></a>
    </div>
        ";

        return $codigo;
    }

    /*   Este es el original
      static function botonesCompartir() {
      $codigo ="
      <div class=\"addthis_toolbox addthis_default_style\">
      <a class=\"addthis_button_facebook\"></a>
      <a class=\"addthis_button_twitter\"></a>
      <a class=\"addthis_button_delicious\"></a>
      <a class=\"addthis_button_google\"></a>
      <a class=\"addthis_button_myspace\"></a>
      <a class=\"addthis_button_email\"></a>
      <a class=\"addthis_button_favorites\"></a>
      <a class=\"addthis_button_print\"></a>
      </div>
      ";

      return $codigo;
      } */




    /*     * * Generar código HTML para insertar un enlace hacia un elemento especifico ** */

    static function urlInterna($modulo, $registro = "", $ajax = false, $accion = "", $categoria = "") {
        global $sql;

        if (empty($modulo)) {
            return NULL;
        }

        $modulo = new Modulo($modulo);

        if (empty($registro) && empty($ajax) && !empty($categoria)) {
            return "/" . $modulo->url . "/category/" . $categoria;
        }

        if (empty($registro) && empty($ajax)) {
            return "/" . $modulo->url;
        }


        if ($registro) {
            return "/" . $modulo->url . "/" . $registro;
        }

        if ($ajax && $accion) {
            return "/ajax/" . $modulo->url . "/" . $accion;
        }
    }

    /**
     *
     * Metodo que se encarga de armar un boton de eliminar Ajax, asignando las clases css
     * "quemadas en el codigo" que le asignaría el jquery automaticamente si se recargara la página
     *
     * */
    /*     * * Generar código HTML con botón y formulario para eliminar un item desde el listado principal haciendo uso de ajax** */
    static function nuevoBotonEliminarItem($id, $url) {
        global $textos;
        $url = preg_replace("|^\/|", "", $url);
        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Delete
                            </span>
                    </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/deleteRegister", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    /**
     *
     * Metodo que se encarga de armar un boton de Modificar Ajax, asignando las clases css
     * "quemadas en el codigo" que le asignaría el jquery automaticamente si se recargara la página
     *
     * */
    static function nuevoBotonModificarItem($id, $url) {
        global $textos;
        $url = preg_replace("|^\/|", "", $url);

        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Edit
                            </span>
                            </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/editRegister", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    /**
     * Metodos para mostrar los botones ediatr y eliminar que aparecen despues de haber modificado el contenido via Ajax
     */
    static function nuevoBotonEliminarItemInterno($id, $url) {
        global $textos;
        $url = preg_replace("|^\/|", "", $url);
        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Delete
                            </span>
                    </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/delete", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    static function nuevoBotonModificarItemInterno($id, $url) {
        global $textos;
        $url = preg_replace("|^\/|", "", $url);

        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Edit
                            </span>
                            </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/edit", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    static function nuevoBotonModificarUsuarioInterno($id, $url) {

        $url = preg_replace("|^\/|", "", $url);

        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Modify Profile
                            </span>
                            </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/edit", $codigo);
        $codigo = HTML::contenedor($codigo, "alineadoDerecha", "alineadoDerecha");

        return $codigo;
    }

    static function botonConsultarItemDesdeBuscador($id, $url) {
        $url = preg_replace("|^\/|", "", $url);

        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-info'></span>
                            <span class='ui-button-text'>
                                Consult
                            </span>
                            </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/see", $codigo);
        $codigo = HTML::contenedor($codigo, "botonModificarItemBuscador flotanteDerecha medioMargenDerecha", "botonModificarItemBuscador");

        return $codigo;
    }

    /**
     * 
     * Metodo para mostrar los botones compartir despues de una carga de contenido via Ajax
     * 
     * */
    static function nuevosBotonesCompartir() {

        $codigo = "<div class='botonesCompartir'>     
                     <div class='addthis_toolbox addthis_default_style'>
                         <a class='addthis_button_facebook at300b' title='Send to Facebook' href='#'><span class='at300bs at15nc at15t_facebook'></span></a>
                         <a class='addthis_button_twitter at300b' title='Tweet This' href='#'><span class='at300bs at15nc at15t_twitter'></span></a>     
                         <a class='addthis_button_google at300b' href='http://www.addthis.com/bookmark.php?v=250&amp;winname=addthis&amp;pub=ablaonline&amp;source=tbx-250&amp;lng=en-US&amp;s=google&amp;url=http%3A%2F%2Flocalhost%2Fgames%2F129&amp;title=ABLAOnline%20%3A%3A%20Games&amp;ate=AT-ablaonline/-/-/4ee24d737457e6d4/1&amp;frommenu=1&amp;uid=4ee24d739c11d556&amp;ct=1&amp;pre=http%3A%2F%2Flocalhost%2Fgames&amp;tt=0' target='_blank' title='Send to Google'><span class='at300bs at15nc at15t_google'></span></a>
                         <a class='addthis_button_email at300b' title='Email' href='#'><span class='at300bs at15nc at15t_email'></span></a>
                         <a class='addthis_button_favorites at300b' title='Save to Favorites' href='#'><span class='at300bs at15nc at15t_favorites'></span></a>
                         <a class='addthis_button_print at300b' title='Print' href='#'><span class='at300bs at15nc at15t_print'></span></a>
                     <div class='atclear'></div></div>        
                 </div>";

        return $codigo;
    }

    /**
     * Metodos para mostrar los botones ediatr y eliminar que aparecen despues de haber modificado el contenido via Ajax
     */
    static function nuevoBotonEliminarRegistro($id, $url) {
        global $textos;

        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Delete
                            </span>
                    </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    static function armarReproductorAudio() {

        $codigo = "<div id='jquery_jplayer_1' class='jp-jplayer'></div>                    

		<div id='jp_container_1' class='jp-audio'>
                        <div id='logoAblaAudios'></div>
			<div class='jp-type-playlist'>
				<div class='jp-gui jp-interface'>
					<ul class='jp-controls'>
						<li><a href='javascript:;' class='jp-previous' tabindex='1'>previous</a></li>
						<li><a href='javascript:;' class='jp-play' tabindex='1'>play</a></li>
						<li><a href='javascript:;' class='jp-pause' tabindex='1'>pause</a></li>
						<li><a href='javascript:;' class='jp-next' tabindex='1'>next</a></li>
						<li><a href='javascript:;' class='jp-stop' tabindex='1'>stop</a></li>
						<li><a href='javascript:;' class='jp-mute' tabindex='1' title='mute'>mute</a></li>
						<li><a href='javascript:;' class='jp-unmute' tabindex='1' title='unmute'>unmute</a></li>
						<li><a href='javascript:;' class='jp-volume-max' tabindex='1' title='max volume'>max volume</a></li>
					</ul>
					<div class='jp-progress'>
						<div class='jp-seek-bar'>
							<div class='jp-play-bar'></div>
						</div>
					</div>
					<div class='jp-volume-bar'>
						<div class='jp-volume-bar-value'></div>
					</div>
					<div class='jp-time-holder'>
						<div class='jp-current-time'></div>
						<div class='jp-duration'></div>
					</div>
					<ul class='jp-toggles'>
						<li><a href='javascript:;' class='jp-shuffle' tabindex='1' title='shuffle'>shuffle</a></li>
						<li><a href='javascript:;' class='jp-shuffle-off' tabindex='1' title='shuffle off'>shuffle off</a></li>
						<li><a href='javascript:;' class='jp-repeat' tabindex='1' title='repeat'>repeat</a></li>
						<li><a href='javascript:;' class='jp-repeat-off' tabindex='1' title='repeat off'>repeat off</a></li>
					</ul>
				</div>
				<div class='jp-playlist'>
					<ul>
						<li></li>
					</ul>
				</div>
				<div class='jp-no-solution'>
					<span>Update Required</span>
					To play the media you will need to either update your browser to a recent version or update your <a href='http://get.adobe.com/flashplayer/' target='_blank'>Flash plugin</a>.
				</div>
			</div>
		</div>";

        return $codigo;
    }
    //funcion que genera el bloque de codigo para mostrar las galerias fotograficas
    static function crearGaleriaFotos($galerias) {
        global $sesion_usuarioSesion, $textos;

        if (!isset($galerias) && !is_array($galerias)) {
            return NULL;
        }

        $codigo = "";

        $codigo .= " <div id='contenedorGalerias' class='contenedorGalerias'>";
        $contador = 0;

        foreach ($galerias as $galeria) {
            if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $galeria->id_usuario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) {
                $datos = array("id" => $galeria->id, "idModulo" => $galeria->id_modulo, "idRegistro" => $galeria->id_registro);
                $codigo .= HTML::botonAjax("masGrueso", $textos->id("AGREGAR_GALERIA"), "/ajax/galeries/add", $datos);
            }
            $codigo .= HTML::parrafo($galeria->titulo, "negrilla");
            $codigo .= HTML::parrafo($galeria->descripcion);
            if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $galeria->id_usuario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) {
                $datos = array("id" => $galeria->id);
                $modificar = HTML::contenedor(HTML::botonAjax("lapiz", $textos->id("MODIFICAR_GALERIA"), "/ajax/galeries/edit", $datos, ""), "alineadoDerecha", "");
                $eliminar = HTML::contenedor(HTML::botonAjax("basura", $textos->id("ELIMINAR_GALERIA"), "/ajax/galeries/delete", $datos, ""), "alineadoDerecha", "");
                $codigo .= HTML::contenedor($eliminar . $modificar, "alineadoDerecha margenSuperiorNegativoTriple", "botonesInternos");
            }
            $codigo .= "<div id = 'galeria_" . $contador . "' class = 'contenedorGaleria'>";

            foreach ($galeria->imagenes as $imagen) {

                $codigo .= "<a href='" . $imagen->imagenPrincipal . "'>
                            <img src='" . $imagen->imagenMiniatura . "' title='" . $imagen->titulo . "' alt='" . $imagen->descripcion . "' />
                        </a>";
            }
            $codigo .= "</div>";

            $contador++;
        }

        $codigo .= "</div>";

        return $codigo;
    }

    static function crearNuevaFila($arregloDatos, $clase = NULL, $id = NULL) {
        if (!isset($arregloDatos) || !is_array($arregloDatos)) {
            return NULL;
        }

        $codigo = "";
        $codigo .= "<tr class='$clase oculto' id='tr_$id' onmouseover=resaltarFila('#tr_$id'); onmouseout=resaltarFila('#tr_$id'); ondblclick=interactuar('#tr_$id');>";

        foreach ($arregloDatos as $valor) {
            $codigo .= "<td class='centrado'> $valor </td>";
        }

        $codigo .= "</tr>";

        return $codigo;
    }

    static function crearFilaAModificar($arregloDatos) {
        if (!isset($arregloDatos) || !is_array($arregloDatos)) {
            return NULL;
        }

        $codigo = "";

        foreach ($arregloDatos as $valor) {
            $codigo .= "<td class='centrado'> $valor </td>";
        }


        return $codigo;
    }

    static function tablaGrilla($columnas, $filas, $clase = NULL, $id = NULL, $claseColumnas = NULL, $claseFilas = NULL, $opciones = NULL, $idFila = NULL, $celdas = NULL) {
        $codigo = "     <table ";

        if (!empty($id) && is_string($id)) {
            $codigo .= " id=\"$id\"";
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= " class=\"$clase\"";
        }
        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= " $atributo=\"$valor\"";
            }
        }

        $codigo .= ">\n";

        if (!empty($columnas)) {
            $codigo .= "     <tr class='cabeceraTabla noSeleccionable'>\n";
            $contador = 0;

            foreach ($columnas as $id => $columna) {
                $codigo .= "     <th";

                if (!empty($id) && is_string($id)) {
                    $codigo .= " id=\"$id\"";
                }

                $check = "";
                $organizadores = "";
                $columnaPequena = "columnaPequena";

                if (!empty($celdas) && is_array($celdas)) {//aqui recibo una cadena que trae el nombre del objeto y el nombre para hacer la consulta                        
                    $data = explode("|", $celdas[$contador]);
                    $codigo .= " nombreOrden=\"" . $data[0] . "\""; //en la posicion 0 traigo el nombre del objeto ej: nombreGrupo
                    if ($data[0] != "estado" && $data[0] != "imagen") {
                        $check = HTML::campoChequeo($data[1], false, "checkPatronBusqueda", "checkPatronBusqueda" . ($contador + 1)); //en la posicion 1 traigo el nombre para la consulta
                        $organizadores = "<div id='ascendente'></div> <div id ='descendente'></div>";
                        $columnaPequena = "";
                    }
                }

                if (!empty($claseColumnas) && is_array($claseColumnas)) {
                    $codigo .= " class=\"columnaTabla $columnaPequena " . $claseColumnas[$contador] . "\"";
                }

                $codigo .= ">\n";
                $codigo .= "$organizadores $columna  $check</th>\n";
                $contador++;
            }
            $codigo .= "  </tr>\n";
        }

        if (!empty($filas)) {
            $contador1 = 0;
            foreach ($filas as $fila => $celdas) {
                $codigo .= "     <tr";
                if (!empty($idFila) && is_array($idFila)) {
                    $codigo .= " id=\"" . $idFila[$contador1] . "\"";
                }
                if (!empty($claseFilas)) {
                    $codigo .= " class=\"" . $claseFilas . "\"";
                }
                $codigo .= ">\n";
                $contador = 0;

                foreach ($celdas as $id => $celda) {
                    $codigo .= "     <td";

                    if (!empty($id) && is_string($id)) {
                        $codigo .= " id=\"$id\"";
                    }


                    $codigo .= ">\n";
                    $codigo .= "$celda</td>\n";
                    $contador++;
                }

                $codigo .= "     </tr>\n";
                $contador1++;
            }
        }

        $codigo .= "     </table>";
        return $codigo;
    }

    /**
     *
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @param string|entero $modulo string=representa el nombre del modulo, entero=representa el id del modulo
     * @param type $permisos
     * @return string 
     */
    static function crearMenuBotonDerecho($modulo, $botones = NULL) {
        global $textos;

        $objeto = new Modulo($modulo);

        $codigo = $consultar = $editar = $borrar = "";
        $ruta = "/ajax/" . $objeto->url;
        $datos = array("id" => "");

        //declaracion de los botones del menu boton derecho
        $consultar = "";
        $editar = "";
        $borrar = "";

        //Verificacion de permisos sobre el boton
//        $puedeEditar  = Perfil::verificarPermisosBoton("botonEditar".ucwords(strtolower($objeto->nombre)));            
//        $puedeBorrar  = Perfil::verificarPermisosBoton("botonBorrar".ucwords(strtolower($objeto->nombre)));  

        $codigo .= "<div id='contenedorBotonDerecho' class='oculto'>";

        $consultar = HTML::formaAjax($textos->id("CONSULTAR"), "contenedorMenuConsultar", "consultar" . ucwords(strtolower($objeto->nombre)), "", $ruta . "/see", $datos);
        $consultar = HTML::contenedor($consultar, "", "botonConsultar" . ucwords(strtolower($objeto->nombre)));


//        if($puedeEditar){
        $editar = HTML::formaAjax($textos->id("MODIFICAR"), "contenedorMenuEditar botonAccion", "editar" . ucwords(strtolower($objeto->nombre)), "", $ruta . "/edit", $datos);
        $editar = HTML::contenedor($editar, "", "botonEditar" . ucwords(strtolower($objeto->nombre)));
//        }
//        if($puedeBorrar){ 
        $borrar = HTML::formaAjax($textos->id("ELIMINAR"), "contenedorMenuEliminar botonAccion", "eliminar" . ucwords(strtolower($objeto->nombre)), "", $ruta . "/delete", $datos);
        $borrar = HTML::contenedor($borrar, "", "botonBorrar" . ucwords(strtolower($objeto->nombre)));
//        }

        $codigo .= $consultar . $editar . $borrar;

        if (isset($botones) && is_array($botones)) {
            foreach ($botones as $boton) {
                $codigo .= $boton;
            }
        }

        $codigo .= "</div>";

        return $codigo;
    }

    static function mapaNuevo() {

        $codigo = "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0' width='670' height='600'>
		 <param name='movie' value='http://media.ablaonline.org/flash/imapbuilder/loader.swf' /><param name='base' value='http://media.ablaonline.org/flash/imapbuilder/' /><param name='flashvars' value='datasource=mapa_v1.xml' />
		 <param name='loop' value='false' /><param name='menu' value='true' /><param name='quality' value='best' /><param name='wmode' value='transparent' />
		 <param name='bgcolor' value='#ffffff' /><param name='allowScriptAccess' value='always' />
		 <object type='application/x-shockwave-flash' data='http://media.ablaonline.org/flash/imapbuilder/loader.swf' width='670' height='600'><param name='movie' value='http://media.ablaonline.org/flash/imapbuilder/loader.swf' />
		 <param name='base' value='http://media.ablaonline.org/flash/imapbuilder/' /><param name='flashvars' value='datasource=mapa_v1.xml' /><param name='loop' value='false' /><param name='menu' value='true' />
		 <param name='quality' value='best' /><param name='wmode' value='transparent' /><param name='bgcolor' value='#ffffff' /><param name='allowScriptAccess' value='always' />
		 </object>
		  </object>";

        return $codigo;
    }

    static function agregarIframe($ruta, $alto, $ancho) {

        $codigo = '<iframe src="' . $ruta . '" width="' . $alto . '" height="' . $ancho . '">';
        $codigo .= $textoAlternativo;
        $codigo .= '</iframe>';

        return $codigo;
    }

}

?>
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

                                Servidor::notificar($seguidor->id_usuario, $notificacion, array(), '11');
                            }
                        }
                    }
                }

                return $idImagen;
            } else {
                Recursos::escribirTxt("fallo el insertar en la tabla imagenes-> id imagen: " . $idImagen);
                return NULL;
            }
        } else {
            Recursos::escribirTxt("fallo el subir el archivo al servidor ->recurso: " . $recurso);
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
class Mensaje {

    /**
     * Código interno o identificador del mensaje en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Código interno o identificador del usuario creador del mensaje en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Nombre de usuario (login) del usuario creador del mensaje
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del mensaje
     * @var cadena
     */
    public $autor;

    /**
     * Sobrenombre o apodo del usuario creador del mensaje
     * @var cadena
     */
    public $genero;

    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;

    /**
     * Título del mensaje
     * @var cadena
     */
    public $titulo;

    /**
     * Contenido completo del mensaje
     * @var cadena
     */
    public $contenido;

    /**
     * Fecha de publicación del mensaje
     * @var fecha
     */
    public $fecha;

    /**
     * Estado de lectura del mensaje
     * @var lógico
     */
    public $leido;

    /**
     *
     * Inicializar el mensaje
     *
     * @param entero $id Código interno o identificador del mensaje en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $sql;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos del mensaje
     *
     * @param entero $id Código interno o identificador del mensaje en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("mensajes", "id", intval($id))) {

            $tablas = array(
                "m" => "mensajes",
                "u" => "usuarios",
                "p" => "personas",
                "i" => "imagenes"
            );

            $columnas = array(
                "id" => "m.id",
                "idAutor" => "m.id_usuario_remitente",
                "usuarioAutor" => "u.usuario",
                "autor" => "u.sobrenombre",
                "fotoAutor" => "i.ruta",
                "titulo" => "m.titulo",
                "contenido" => "m.contenido",
                "fecha" => "UNIX_TIMESTAMP(m.fecha)",
                "leido" => "m.leido",
                "genero" => "p.genero",
                "fecha" => "m.fecha"
            );

            $condicion = "m.id_usuario_remitente = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND m.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->fotoAutor = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $this->fotoAutor;
            }
        }
    }

    /**
     *
     * Adicionar un mensaje
     *
     * @param  arreglo $datos       Datos del mensaje a adicionar
     * @return entero               Código interno o identificador del mensaje en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion;

        $datos["id_usuario_remitente"] = $sesion_usuarioSesion->id;
        $datos["fecha"] = date("Y-m-d G:i:s");
        $datos["leido"] = '0';

        $datos = array(
            "id_usuario" => $sesion_usuarioSesion->id,
            "contenido" => $datos["contenido"],
            "fecha" => date("Y-m-d H:i:s"),
            "activo" => "1"
        );

        $consulta = $sql->insertar("mensajes", $datos);
        $idConsulta = $sql->ultimoId;

        if ($consulta) {
            return $idConsulta;
        } else {
            return NULL;
        }
    }

    /**
     *
     * Eliminar un mensaje
     *
     * @param entero $id    Código interno o identificador del mensaje en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("mensajes", "id = '" . $this->id . "'");
        return $consulta;
    }

    /**
     *
     * Eliminar varios mensajes
     *
     * @param array datos   Códigos internos o identificadores delos mensajes en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminarVariosMensajes($datos) {
        global $sql;

        if (empty($datos)) {//datos me esta llegando como un string concatenado con comas
            return NULL;
        }

        $ids = explode(",", $datos);

//        foreach($datos as $dato){
//          $dato2 .= $dato; 
//          $consulta = $sql->eliminar("mensajes", "id = '".$dato."'");
//        }
//        Recursos::escribirTxt("dato: ".$dato2);


        for ($i = 0; $i < sizeof($ids); $i++) {
            $consulta = $sql->eliminar("mensajes", "id = '" . $ids[$i] . "'");
        }

        return $consulta;
    }

    /**
     *
     * Listar los mensajes de un registro en un módulo
     *
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de mensajes hechos al registro del módulo
     *
     */
    public function listar($inicio = 0, $cantidad = 0, $idUsuario) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        $tablas = array(
            "m" => "mensajes",
            "u" => "usuarios",
            "p" => "personas",
            "i" => "imagenes",
        );

        $columnas = array(
            "id" => "m.id",
            "idAutor" => "m.id_usuario_remitente",
            "usuario" => "u.usuario",
            "autor" => "u.sobrenombre",
            "fotoAutor" => "i.ruta",
            "titulo" => "m.titulo",
            "contenido" => "m.contenido",
            "fecha" => "UNIX_TIMESTAMP(m.fecha)",
            "leido" => "m.leido"
        );

        //$sql->depurar = true;
        $condicion = "m.id_usuario_remitente = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND m.id_usuario_destinatario = '" . $idUsuario . "'";

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", "fecha DESC", $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($mensaje = $sql->filaEnObjeto($consulta)) {
                $mensaje->fotoAutor = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $mensaje->fotoAutor;
                $lista[] = $mensaje;
            }
        }

        return $lista;
    }

//fin del metodo listar

    public function contarMensajesUsuario() {
        global $sql, $configuracion, $textos, $sesion_usuarioSesion;
        $sql->depurar = true;
        $cantidad = $sql->obtenerValor("mensajes", "COUNT(id)", "id_usuario_destinatario = " . $sesion_usuarioSesion->id);

        return $cantidad;
    }

}

?>
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
class Modulo {

    /**
     * Código interno o identificador del módulo en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Clase a la cual pertenece el módulo
     * - 1: Configuración del sitio
     * - 2: Configuración personal
     * - 3: Uso global
     * - 4: e-learning
     * @var entero
     */
    public $clase;

    /**
     * Texto que identifica el módulo en los archivos de idiomas
     * @var cadena
     */
    public $nombre;

    /**
     * Texto que identifica un registro especíco del módulo a cargar o enlazar en una URL (Ej: 'news' en http://servidor/news/123)
     * @var cadena
     */
    public $url;

    /**
     * Carpeta en la que residen los archivos propios del módulo
     * @var cadena
     */
    public $carpeta;

    /**
     * El módulo aparece en los menús o listas de componentes
     * @var lógico
     */
    public $visible;

    /**
     * El módulo puede ser cargado sin verificar permisos
     * @var lógico
     */
    public $global;

    /**
     * Tabla principal con la que se relaciona el módulo
     * @var cadena
     */
    public $tabla;

    /**
     * Inicializar el módulo especificado
     * @param cadena $modulo Nombre único del módulo en la base de datos
     */
    public function __construct($modulo) {
        global $sql, $peticionAJAX, $configuracion, $textos, $parametros;

        /*         * * Hacer globales las variables procedentes de formularios y/o peticiones ** 
          foreach ($GLOBALS as $variable => $valor) {

          if (is_string($variable) && preg_match("/(^sesion_|forma_|^url_|^cookies_|^archivo_)/", $variable)) {
          global $$variable;
          }
          } */

        $columnas = array(
            "id" => "id",
            "clase" => "clase",
            "orden" => "orden",
            "nombre" => "nombre",
            "url" => "url",
            "carpeta" => "carpeta",
            "visible" => "visible",
            "global" => "global",
            "tabla" => "tabla_principal",
            "validar" => "valida_usuario"
        );

        $consulta = $sql->seleccionar(array("modulos"), $columnas, "BINARY nombre = '$modulo'");

        if ($sql->filasDevueltas) {

            $fila = $sql->filaEnObjeto($consulta);

            foreach ($fila as $propiedad => $valor) {
                $this->$propiedad = $valor;
            }

            $this->carpeta = $configuracion["RUTAS"]["modulos"] . "/" . $this->carpeta;


            if (empty($textos)) {
                $textos = new Texto($modulo);
            }
        } else {

            if (empty($textos)) {
                $textos = new Texto();
            }
        }
    }

    public function procesar() {
        global $sql, $peticionAJAX, $configuracion, $textos, $parametros;

        /*         * * Hacer globales las variables procedentes de formularios y/o peticiones ** */
        foreach ($GLOBALS as $variable => $valor) {

            if (is_string($variable) && preg_match("/(^sesion_|forma_|^url_|^cookies_|^archivo_)/", $variable)) {
                global $$variable;
            }
        }

        if ($peticionAJAX) {
            /*             * * Cargar archivo manejador de peticiones AJAX ** */
            $archivo = $this->carpeta . "/" . $configuracion["MODULOS"]["ajax"];
        } else {
            /*             * * Cargar archivo manejador de peticiones comúnes ** */
            $archivo = $this->carpeta . "/" . $configuracion["MODULOS"]["principal"];
        }


        //if (file_exists($archivo) && is_readable($archivo)) {
        require_once $archivo;
        //}
    }

}

?>
<?php

/**
 *
 * Copyright (C) 2009 FELINUX Ltda
 * Francisco J. Lozano B. <fjlozano@felinux.com.co>
 *
 * Este archivo es parte de:
 * PANCE :: Plataforma para la Administración del Nexo Cliente-Empresa
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo  bajo los términos de la Licencia Pública General GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión 3
 * de la Licencia, o (a su elección) cualquier versión posterior.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita MERCANTIL o
 * de APTITUD PARA UN PROPÓSITO DETERMINADO. Consulte los detalles de
 * la Licencia Pública General GNU para obtener una información más
 * detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General GNU
 * junto a este programa. En caso contrario, consulte:
 * <http://www.gnu.org/licenses/>.
 *
 * */
/* * * Requiere libreria de terceros (FPDF - www.fpdf.org) ** */
//require "fpdf.php";

class PDF extends FPDF {

    var $textoTipo;
    var $textoNombre;
    var $textoCodigo;
    var $textoFecha;
    var $textoVersion;
    var $textoDependencia;
    var $PiePagina;

    /*     * * Generar tabla ** */

    function generarCabeceraTabla($columnas, $anchoColumnas) {
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(0);
        $this->SetDrawColor(0);
        $this->SetLineWidth(.1);
        $this->SetFont("", "B", "");

        for ($i = 0; $i < count($columnas); $i++) {
            $this->Cell($anchoColumnas[$i], 4, $columnas[$i], 1, 0, "C", true);
        }
    }

    /*     * * Generar tabla ** */

    function generarContenidoTabla($filas, $anchoColumnas, $alineacionColumnas = "", $formatoColumnas = "") {
        $this->Ln(0);
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont("");

        $rellenar = true;

        foreach ($filas as $fila) {
            $celdas = 0;

            foreach ($fila as $celda) {
                switch (strtoupper($alineacionColumnas[$celdas])) {
                    case "I" :
                        $alineacion = "L";
                        break;
                    case "D" :
                        $alineacion = "R";
                        break;
                    case "C" :
                        $alineacion = "C";
                        break;
                    default :
                        $alineacion = "L";
                        break;
                }

                $this->Cell($anchoColumnas[$celdas], 3, htmlspecialchars_decode($celda), "LRT", 0, $alineacion, $rellenar);
                $celdas++;
            }

            $this->Ln();
            $rellenar = !$rellenar;
        }

        $this->Cell(array_sum($anchoColumnas), 0, "", "T");
    }

    /*     * * Encabezado ** */

    function Header() {
        global $pance, $imagenesGlobales, $noImprimirEncabezado;

        $this->AliasNbPages();

        if (!$noImprimirEncabezado) {
            if ($this->pdfHorizontal == '') {

                $this->SetFont("Arial", "B", 7);
                $this->SetXY(10, 10);
                $this->Cell(52, 24, "", 1, 0);
                $this->Image($imagenesGlobales["logoClienteReportes"], 11, 15, 50);
                $this->SetXY(62, 10);
                $this->Cell(103, 12, $this->textoTipo, 1, 0, "C");
                $this->SetXY(62, 22);
                $this->Cell(103, 12, $this->textoNombre, 1, 0, "C");
                $this->SetXY(165, 10);
                $this->Cell(40, 6, $this->textoCodigo, "LTR", 0, "L");
                $this->SetXY(165, 16);
                $this->Cell(40, 6, $this->textoFecha, "LR", 0, "L");
                $this->SetXY(165, 22);
                $this->Cell(40, 6, $this->textoVersion, "LR", 0, "L");
                $this->SetXY(165, 28);
                $this->Cell(40, 6, $this->textoDependencia, "LBR", 0, "L");
                $this->SetXY(150, 35);
            } else if ($this->pdfHorizontal == '1') {

                $this->SetFont("Arial", "B", 7);
                $this->SetXY(10, 10);
                $this->Cell(52, 24, "", 1, 0);
                $this->Image($imagenesGlobales["logoClienteReportes"], 11, 15, 50);
                $this->SetXY(62, 10);
                $this->Cell(140, 12, $this->textoTipo, 1, 0, "C");
                $this->SetXY(62, 22);
                $this->Cell(140, 12, $this->textoNombre, 1, 0, "C");
                $this->SetXY(202, 10);
                $this->Cell(60, 6, $this->textoCodigo, "LTR", 0, "L");
                $this->SetXY(202, 16);
                $this->Cell(60, 6, $this->textoFecha, "LR", 0, "L");
                $this->SetXY(202, 22);
                $this->Cell(60, 6, $this->textoVersion, "LR", 0, "L");
                $this->SetXY(202, 28);
                $this->Cell(60, 6, $this->textoDependencia, "LBR", 0, "L");
                $this->SetXY(150, 35);
            }
        }
    }

    /*     * * Pie de página ** */

    function Footer() {
        global $textos, $noImprimirPiePagina, $imprimirPiePagina;

        if (!$noImprimirPiePagina) {
            if (!$imprimirPiePagina) {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 7);
                $paginas = str_replace("%n", $this->PageNo(), $textos["PAGINAS"]);
                $paginas = str_replace("%t", "{nb}", $paginas);
                $this->Cell(0, 10, $paginas, 0, 0, 'C');
            } else {
                $this->SetY(-15);
                $this->SetFont('Times', 'I', 12);
                $this->Cell(0, 10, $this->PiePagina, 0, 0, 'C');
            }
        }
    }

}

?>
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Permisos Item
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 COLOMBO-AMERICANO
 * @version     0.1
 *
 * */
class PermisosItem {

    /**
     *
     * Metodo InsertarPerfilesCompartidos--> ingresa en la tabla permisos_item los perfiles que tienen un determinado item compartido
     * utiliza el metodo insertar de esta misma clase
     *
     * */
    public function insertarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles) {


        if ($datosVisibilidad == "privado") {//verifico si el item es privado, en caso de serlo ingreso los perfiles con los cuales se debe compartir
            foreach ($datosPerfiles as $idPerfil => $valor) {
                //$sql->depurar   = true;
                $this->insertar($idPerfil, $idModulo, $idItem);
            }//fin del foreach
        } else {//si viene publico se comparte con el perfil 99
            $idPerfil = 99;
            //$sql->depurar   = true;
            $this->insertar($idPerfil, $idModulo, $idItem);
        }//fin del if

        return true;
    }


    /**
     *
     * Metodo Insertar--> ingresa a la base de datos a la tabla permisos item registros que informa de un item (por ejemplo BLOG, COURSES)
     * con que perfiles puede ser compartido
     *
     * */
    public function insertar($idPerfil, $idModulo, $idItem) {
        global $sql, $configuracion;


        $datos = array(
            "id_modulo" => $idModulo,
            "id_item" => $idItem,
            "id_perfil" => $idPerfil
        );

        $sql->guardarBitacora = false;
        $consulta = $sql->insertar("permisos_item", $datos);


        if ($consulta) {

            return $sql->ultimoId;
        } else {

            return NULL;
        }//fin del if
    }

    /**
     *
     * Metodo modificarPerfilesCompartidos--> ingresa en la tabla permisos_item los perfiles que tienen un determinado item compartido
     * utiliza el metodo insertar de esta misma clase
     *
     * */
    public function modificarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles) {


        if ($datosVisibilidad == "privado") {//verifico si el item es privado, en caso de serlo ingreso los perfiles con los cuales se debe compartir
            //$sql->depurar = true;
            if (!($this->eliminar($idItem, $idModulo))) {
                return false;
            } else {
                foreach ($datosPerfiles as $idPerfil => $valor) {
                    $sql->depurar = true;
                    $this->insertar($idPerfil, $idModulo, $idItem);
                }//fin del foreach
            }
        } else {//si viene publico se comparte con el perfil 99 y solo se ingresa un registro a la BD
            $idPerfil = 99;

            //primero elimino todos los permisos que hayan para determinado item en la tabla
            //permisos item
            if (!($this->eliminar($idItem, $idModulo))) {
                return false;
            } else {
                //luego inserto los nuevos permisos
                $this->insertar($idPerfil, $idModulo, $idItem);
            }
        }//fin del if datosVisibilidad = privado

        return true;
    }

    /**
     *
     * Metodo Modificar--> modifica de la base de datos la tabla permisos_item los registros que informa de un item (por ejemplo BLOG, COURSES)
     * con que perfiles puede ser compartido
     *
     * */
    public function modificar($idPerfil, $idModulo, $idItem) {
        global $sql, $configuracion;

        /* Primero debe de borrar todos los registros que encuentre de dicho modulo
          y despues debe volver a insertarlos */

        $datos = array(
            "id_modulo" => $idModulo,
            "id_item" => $idItem,
            "id_perfil" => $idPerfil
        );
        $sql->guardarBitacora = false;
        $consulta = $sql->insertar("permisos_item", $datos);

        if ($consulta) {

            return $sql->ultimoId;
        } else {

            return NULL;
        }//fin del if       
    }

    /**
     *
     * Metodo Eliminar--> Es llamado cuando se requiere modificar los permisos-perfiles de un determinado blog para que primero elimine todos
     * los permisos existentes antes de volver a insertar los nuevos.    Tambien es llamado cuando se elimina determinado item de determinado modulo
     * para que borre todos los permisos relacionados a el item que se ha eliminado
     * */
    public function eliminar($idItem, $idModulo) {
        global $sql, $configuracion;

        $condicion = "id_item = '" . $idItem . "' AND id_modulo = '" . $idModulo . "'";

        $borrar = $sql->eliminar("permisos_item", $condicion);

        if ($borrar) {

            return true;
        } else {

            return false;
        }//fin del if    
    }

    /**
     *
     * Cargar en la variable de tipo array Perfiles, los perfiles con los cuales es compartido determinado item
     * esta información es cargada desde la BD de la tabla permisos_item
     *
     * @param entero $id Código interno o identificador del blog en la base de datos
     *
     */
    public static function cargarPerfiles($idItem, $idModulo) {
        global $configuracion, $sql;

        $perfiles = array();
        $tabla = array("permisos_item");
        $condicion = "id_modulo = '" . $idModulo . "' AND id_item = '" . $idItem . "'";

        $consulta = $sql->seleccionar($tabla, array("id_modulo", "id_item", "id_perfil"), $condicion);

        while ($perfil = $sql->filaEnObjeto($consulta)) {

            $perfiles[] = $perfil->id_perfil;
        }

        return $perfiles;
    }

    //Inicio de metodos para ingresar los perfiles de adicion

    /**
     *
     * Metodo InsertarPerfilesCompartidos--> ingresa en la tabla permisos_adicion_item los perfiles que tienen un determinado item compartido
     * utiliza el metodo insertar de esta misma clase
     *
     * */
    public function insertarPerfilesCompartidosPA($datosVisibilidad, $idModulo, $idItem, $datosPerfiles) {


        if ($datosVisibilidad == "privado") {//verifico si el item es privado, en caso de serlo ingreso los perfiles con los cuales se debe compartir
            foreach ($datosPerfiles as $idPerfil => $valor) {
                //$sql->depurar   = true;
                $this->insertarPA($idPerfil, $idModulo, $idItem);
            }//fin del foreach
        } else {//si viene publico se comparte con el perfil 99
            $idPerfil = 99;
            //$sql->depurar   = true;
            $this->insertarPA($idPerfil, $idModulo, $idItem);
        }//fin del if

        return true;
    }


    /**
     *
     * Metodo Insertar--> ingresa a la base de datos a la tabla permisos item registros que informa de un item (por ejemplo BLOG, COURSES)
     * con que perfiles puede ser compartido
     *
     * */
    public function insertarPA($idPerfil, $idModulo, $idItem) {
        global $sql, $configuracion;


        $datos = array(
            "id_modulo" => $idModulo,
            "id_item" => $idItem,
            "id_perfil" => $idPerfil
        );

        $sql->guardarBitacora = false;
        $consulta = $sql->insertar("permisos_adicion_item", $datos);


        if ($consulta) {

            return $sql->ultimoId;
        } else {

            return NULL;
        }//fin del if
    }

    /**
     *
     * Metodo modificarPerfilesCompartidos--> ingresa en la tabla permisos_adicion_item los perfiles que tienen un determinado item compartido
     * utiliza el metodo insertar de esta misma clase
     *
     * */
    public function modificarPerfilesCompartidosPA($datosVisibilidad, $idModulo, $idItem, $datosPerfiles) {


        if ($datosVisibilidad == "privado") {//verifico si el item es privado, en caso de serlo ingreso los perfiles con los cuales se debe compartir
            //$sql->depurar = true;
            if (!($this->eliminarPA($idItem, $idModulo))) {
                return false;
            } else {
                foreach ($datosPerfiles as $idPerfil => $valor) {
                    //$sql->depurar = true;
                    $this->insertarPA($idPerfil, $idModulo, $idItem);
                }//fin del foreach
            }
        } else {//si viene publico se comparte con el perfil 99 y solo se ingresa un registro a la BD
            $idPerfil = 99;

            //primero elimino todos los permisos que hayan para determinado item en la tabla
            //permisos item
            if (!($this->eliminarPA($idItem, $idModulo))) {
                return false;
            } else {
                //luego inserto los nuevos permisos
                $this->insertarPA($idPerfil, $idModulo, $idItem);
            }
        }//fin del if datosVisibilidad = privado

        return true;
    }

    /**
     *
     * Metodo Modificar--> modifica de la base de datos la tabla permisos_adicion_item los registros que informa de un item (por ejemplo BLOG, COURSES)
     * con que perfiles puede ser compartido
     *
     * */
    public function modificarPA($idPerfil, $idModulo, $idItem) {
        global $sql, $configuracion;

        /* Primero debe de borrar todos los registros que encuentre de dicho modulo
          y despues debe volver a insertarlos */

        $datos = array(
            "id_modulo" => $idModulo,
            "id_item" => $idItem,
            "id_perfil" => $idPerfil
        );
        $sql->guardarBitacora = false;
        $consulta = $sql->insertarPA("permisos_adicion_item", $datos);

        if ($consulta) {

            return $sql->ultimoId;
        } else {

            return NULL;
        }//fin del if       
    }

    /**
     *
     * Metodo Eliminar--> Es llamado cuando se requiere modificar los permisos-perfiles de un determinado blog para que primero elimine todos
     * los permisos existentes antes de volver a insertar los nuevos.    Tambien es llamado cuando se elimina determinado item de determinado modulo
     * para que borre todos los permisos relacionados a el item que se ha eliminado
     * */
    public function eliminarPA($idItem, $idModulo) {
        global $sql, $configuracion;

        $condicion = "id_item = '" . $idItem . "' AND id_modulo = '" . $idModulo . "'";

        $borrar = $sql->eliminar("permisos_adicion_item", $condicion);

        if ($borrar) {

            return true;
        } else {

            return false;
        }//fin del if    
    }

    /**
     *
     * Cargar en la variable de tipo array Perfiles, los perfiles con los cuales es compartido determinado item
     * esta información es cargada desde la BD de la tabla permisos_adicion_item
     *
     * @param entero $id Código interno o identificador del blog en la base de datos
     *
     */
    public static function cargarPerfilesPA($idItem, $idModulo) {
        global $configuracion, $sql;

        $perfiles = array();
        $tabla = array("permisos_adicion_item");
        $condicion = "id_modulo = '" . $idModulo . "' AND id_item = '" . $idItem . "'";

        $consulta = $sql->seleccionar($tabla, array("id_modulo", "id_item", "id_perfil"), $condicion);

        while ($perfil = $sql->filaEnObjeto($consulta)) {

            $perfiles[] = $perfil->id_perfil;
        }

        return $perfiles;
    }    

}

//fin de la clase permisos item
?>
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
class Persona {

    /**
     * Código interno o identificador de la persona en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Nombre de la persona
     * @var cadena
     */
    public $nombre;

    /**
     * Apellidos de la persona
     * @var cadena
     */
    public $apellidos;

    /**
     * Nombre completo de la persona
     * @var cadena
     */
    public $nombreCompleto;

    /**
     * Descripción corta de su personalidad
     * @var cadena
     */
    public $descripcion;

    /**
     * Código interno o identificador en la base de datos de la imagen de la persona
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen de la persona en tamaño normal
     * @var cadena
     */
    public $imagenPrincipal;

    /**
     * Ruta de la imagen de la persona en miniatura
     * @var cadena
     */
    public $imagenMiniatura;

    /**
     * Género de la persona ('M' o 'F')
     * @var caracter
     */
    public $idGenero;

    /**
     * Género de la persona (palabra completa)
     * @var cadena
     */
    public $genero;

    /**
     * Fecha de nacimiento de la persona
     * @var fecha
     */
    public $fechaNacimiento;

    /**
     * Código interno o identificador en la base de datos de la ciudad de nacimiento de la persona
     * @var entero
     */
    public $idCiudadNatal;

    /**
     * Nombre de la ciudad de nacimiento de la persona
     * @var cadena
     */
    public $ciudadNatal;

    /**
     * Código interno o identificador en la base de datos del estado de nacimiento de la persona
     * @var entero
     */
    public $idEstadoNatal;

    /**
     * Nombre del estado de nacimiento de la persona
     * @var cadena
     */
    public $estadoNatal;

    /**
     * Código interno o identificador en la base de datos de la persona de nacimiento de la persona
     * @var entero
     */
    public $idPaisNatal;

    /**
     * Nombre de la persona de nacimiento de la persona
     * @var cadena
     */
    public $paisNatal;

    /**
     * Código interno o identificador en la base de datos de la ciudad de residencia de la persona
     * @var entero
     */
    public $idCiudadResidencia;

    /**
     * Nombre de la ciudad de residencia de la persona
     * @var cadena
     */
    public $ciudadResidencia;

    /**
     * Código interno o identificador en la base de datos del estado de residencia de la persona
     * @var entero
     */
    public $idEstadoResidencia;

    /**
     * Nombre del estado de residencia de la persona
     * @var cadena
     */
    public $estadoResidencia;

    /**
     * Código interno o identificador en la base de datos de la persona de residencia de la persona
     * @var entero
     */
    public $idPaisResidencia;

    /**
     * Nombre de la persona de residencia de la persona
     * @var cadena
     */
    public $paisResidencia;

    /**
     * Dirección de correo electrónico de la persona
     * @var cadena
     */
    public $correo;

    /**
     * Dirección de la página web de la persona
     * @var cadena
     */
    public $paginaWeb;

    /**
     * Codigo Iso del Pais al cual Pertenece el usuario
     * @var cadena
     */
    public $codigoIsoPais;

    /**
     *
     * Inicializar la persona
     *
     * @param entero $id Código interno o identificador de la persona en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $sql;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos de la persona
     *
     * @param entero $id Código interno o identificador de la persona en la base de datos
     *
     */
    public function cargar($id = NULL) {
        global $sql, $configuracion, $textos;

        if (isset($id) && $sql->existeItem("personas", "id", intval($id))) {
            $this->id = $id;

            $tablas = array(
                "p" => "personas",
                "c1" => "ciudades",
                "e1" => "estados",
                "p1" => "paises",
                "c2" => "ciudades",
                "e2" => "estados",
                "p2" => "paises",
                "i" => "imagenes"
            );

            $columnas = array(
                "nombre" => "p.nombre",
                "apellidos" => "p.apellidos",
                "idGenero" => "p.genero",
                "idImagen" => "p.id_imagen",
                "imagen" => "i.ruta",
                "fechaNacimiento" => "p.fecha_nacimiento",
                "idCiudadNatal" => "p.id_ciudad_nacimiento",
                "ciudadNatal" => "c1.nombre",
                "idEstadoNatal" => "c1.id_estado",
                "estadoNatal" => "e1.nombre",
                "idPaisNatal" => "e1.id_pais",
                "paisNatal" => "p1.nombre",
                "codigoIsoPais" => "p1.codigo_iso",
                "idCiudadResidencia" => "p.id_ciudad_residencia",
                "ciudadResidencia" => "c2.nombre",
                "idEstadoResidencia" => "c2.id_estado",
                "estadoResidencia" => "e2.nombre",
                "idPaisResidencia" => "e2.id_pais",
                "paisResidencia" => "p2.nombre",
                "descripcion" => "p.descripcion",
                "correo" => "p.correo",
                "paginaWeb" => "p.pagina_web"
            );

            $condicion = "p.id_ciudad_nacimiento = c1.id AND c1.id_estado = e1.id AND e1.id_pais = p1.id
                          AND p.id_ciudad_residencia = c2.id AND c2.id_estado = e2.id AND e2.id_pais = p2.id
                          AND p.id_imagen = i.id AND p.id = '$id'";
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);



            if ($sql->filasDevueltas) {
                $datos = $sql->filaEnObjeto($consulta);

                foreach ($datos as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->nombreCompleto = $this->nombre . " " . $this->apellidos;
                $this->genero = $textos->id("GENERO_" . $this->idGenero);
                $this->imagenPrincipal = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesDinamicas"] . "/" . $datos->imagen;
                $this->imagenMiniatura = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $datos->imagen;
            }
        }
    }

    /**
     *
     * Adicionar una persona
     *
     * @param  arreglo $datos       Datos de la persona a adicionar
     * @return entero               Código interno o identificador de la persona en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion;

        $consulta = $sql->insertar("personas", $datos);

        if ($consulta) {
            return $sql->ultimoId;
        } else {
            return NULL;
        }
    }

    /**
     *
     * Modificar una persona
     *
     * @param  arreglo $datos       Datos de la persona a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->modificar("personas", $datos, "id = '" . $this->id . "'");
        return $consulta;
    }

    /**
     *
     * Eliminar una persona
     *
     * @param entero $id    Código interno o identificador de la persona en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("personas", "id = '" . $this->id . "'");
        return $consulta;
    }

}

?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 * */

/**
 * Gestión automática de plantillas de código HTML
 * */
class Plantilla {

    /**
     * Determina si se trata de la página principal
     * @var lógico
     */
    public static $principal = false;

    /**
     * Contenido de la página solicitada
     * @var cadena
     */
    public static $contenido = '';

    /**
     * Etiquetas reemplazables de la plantilla
     * @var arreglo
     */
    public static $etiquetas = array();

    /**
     * Inicializar la plantilla
     */
    public static function iniciar($modulo) {
        global $configuracion, $textos, $sesion_tituloPagina, $sesion_descripcionPagina, $sesion_palabrasClavePagina, $sesion_codificacionPagina, $sesion_iconoPagina, $sesion_pieDePagina;

        if (self::$principal) {
            $plantilla = $configuracion['RUTAS']['plantillas'] . '/' . $configuracion['PLANTILLAS']['principal'];
        } else {
            $plantilla = $configuracion['RUTAS']['plantillas'] . '/' . $configuracion['PLANTILLAS']['interna'];
        }

        if (file_exists($plantilla) && is_readable($plantilla)) {
            self::$contenido = file_get_contents($plantilla);
        }

        preg_match_all('/\{\%(.*)\%\}/', self::$contenido, $etiquetas);

        foreach ($etiquetas[0] as $etiqueta) {
            $nombre = preg_replace('/(\{\%)|(\%\})/', '', $etiqueta);
            self::$etiquetas[$nombre] = '';
        }

        /*         * * Definir el texto para la barra de título del navegador ** */
        (!isset($sesion_tituloPagina)) ? self::$etiquetas['TITULO_PAGINA'] = $configuracion['PAGINA']['titulo'] : self::$etiquetas['TITULO_PAGINA'] = $sesion_tituloPagina;

        /*         * * Definir el texto con la descripción de la página ** */
        (!isset($sesion_descripcionPagina)) ? self::$etiquetas['DESCRIPCION'] = $configuracion['PAGINA']['descripcion'] : self::$etiquetas['DESCRIPCION'] = $sesion_descripcionPagina;

        /*         * * Definir la lista de palabras clave de la página ** */
        (!isset($sesion_palabrasClavePagina)) ? self::$etiquetas['PALABRAS_CLAVE'] = $configuracion['PAGINA']['palabrasClave'] : self::$etiquetas['PALABRAS_CLAVE'] = $sesion_palabrasClavePagina;

        /*         * * Definir el ícono de la página ** */
        //(!isset($sesion_codificacionPagina)) ? self::$etiquetas['CODIFICACION'] = $configuracion['PAGINA']['codificacion'] : self::$etiquetas['CODIFICACION'] = $sesion_codificacionPagina;
        self::$etiquetas['CODIFICACION'] = $configuracion['PAGINA']['codificacion'];
        /*         * * Definir el ícono de la página ** */
        (!isset($sesion_iconoPagina)) ? self::$etiquetas['ICONO'] = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstaticas'] . '/' . $configuracion['PAGINA']['icono'] : self::$etiquetas['ICONO'] = $sesion_iconoPagina;

        /*         * * Definir el texto del pie de página ** */
        //(!isset($sesion_pieDePagina)) ? self::$etiquetas['PIE_PAGINA'] = $configuracion['PAGINA']['pieDePagina'] : self::$etiquetas['PIE_PAGINA'] = $sesion_pieDePagina;


        $piePagina = '';
        $piePagina .= "<p style='margin-top: -7px; color:#fff !important; font-size:1.1em;' class='negrilla'>&copy; " . date("Y") . " ABLA - All Rights Reserved :: <a href= \"mailto:webmaster@ablaonline.org\" class='letraBlanca subrayado'>webmaster@ablaonline.org</a><p><br /><br/>Developed by <a href=\"http://www.colomboamericano.edu.co\">CENTRO CULTURAL COLOMBO AMERICANO CALI - COLOMBIA</a>";
        $piePagina = HTML::contenedor($piePagina, 'contenidoPiePagina');
        //$piePagina .= HTML::bloquePublicidadInferior();
        self::$etiquetas["PIE_PAGINA"] = $piePagina;


        //self::$etiquetas["BLOQUE_PUBLICIDAD_SUPERIOR"] = HTML::bloquePublicidadSuperior();


        self::$etiquetas['TEXTO_BUSCADOR'] = $textos->id('TEXTO_BUSCADOR');
        self::$etiquetas['TEXTO_ESPERA'] = $textos->id('TEXTO_ESPERA');

        self::cargarEstilos($modulo);
        self::cargarJavaScript($modulo);
        self::cargarAudio();
        self::cargarMenus();
        self::cargarUsuarioSesion();
        //self::cargarCalendarioEventos();
        //self::cargarPauta();
        //self::cargarBotonesRedes();
        self::cargarAsociados();
    }

    /*     * * Incluir referencias a archivos de hojas de estilos (CSS) ** */

    protected static function cargarEstilos($modulo) {
        global $configuracion;

        $estilos = '';

        foreach ($configuracion['ESTILOS']['GENERAL'] as $archivo) {
            $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['estilos'] . '/' . $archivo;
            $estilos .= '   <link href="' . $ruta . '" rel=\'stylesheet\' type=\'text/css\' media=\'screen\' />';
        }

        if (isset($configuracion['ESTILOS'][$modulo->nombre])) {
            foreach ($configuracion['ESTILOS'][$modulo->nombre] as $archivo) {
                $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['estilos'] . '/' . $archivo;
                $estilos .= '   <link href="' . $ruta . '" rel=\'stylesheet\' type=\'text/css\' media=\'screen\' />';
            }
        }

        self::$etiquetas['HOJAS_ESTILOS'] = $estilos;
    }

    /**
     * Metodo que se encargar de llamar los archivos javascript generales 
     * y los que va a usar determinado modulo
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @param string|entero $modulo string=representa el nombre del modulo, entero=representa el id del modulo 
     */
    protected static function cargarJavaScript($modulo) {
        global $configuracion, $sesion_usuarioSesion;

        $JavaScript = '';

        foreach ($configuracion['JAVASCRIPT']['GENERAL'] as $archivo) {

            if (preg_match('|^https?\:\/\/|', $archivo)) {
                $JavaScript .= '  <script type=\'text/javascript\' src="' . $archivo . '"></script>';
            } else {
                $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['javascript'] . '/' . $archivo;
                $JavaScript .= '  <script type=\'text/javascript\' src="' . $ruta . '"></script>';
            }
        }


        if (isset($sesion_usuarioSesion) && !empty($sesion_usuarioSesion->id)) {
            $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['javascript'] . '/chat.js';
            $JavaScript .= '  <script type=\'text/javascript\' src="' . $ruta . '"></script>';
        }

        if (isset($configuracion['JAVASCRIPT'][$modulo->nombre])) {
            foreach ($configuracion['JAVASCRIPT'][$modulo->nombre] as $archivo) {

                if (preg_match('|^https?\:\/\/|', $archivo)) {
                    $JavaScript .= '  <script type=\'text/javascript\' src="' . $archivo . '"></script>';
                } else {
                    $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['javascript'] . '/' . $archivo;
                    $JavaScript .= '  <script type=\'text/javascript\' src="' . $ruta . '"></script>';
                }
            }
        }

        self::$etiquetas['JAVASCRIPT'] = $JavaScript;
    }

    /*     * * Insertar código HTML para mostrar buscador    'style' => 'background-image: url('http://media.ablaonline.local/imagen/estaticas/fondo_input.png')',** */

    protected static function cargarBuscador() {
//        global $textos;
//
//        $buscador = new Modulo('BUSCADOR');
//
//        $opciones = array('onFocus' => '$(this).val("")', 'width' => '100');
//
//        $formaBuscador = HTML::campoTexto('campoBuscador', 18, 255, $textos->id('BUSCAR'), NULL, 'campoBuscador', $opciones);
//        $formaBuscador .= HTML::boton('buscar', $textos->id('BUSCAR'), 'directo');
        // self::$etiquetas['BLOQUE_BUSCADOR'] = HTML::forma($buscador->url, $formaBuscador, 'P', false, 'formaBuscador');
    }

    /*     * * Insertar código HTML para la barra de enlaces corporativos ** */

    protected static function cargarMenus() {
        global $textos, $sql, $sesion_usuarioSesion;

        $enlaces = '';

        /*         * * Adicionar enlace a la página principal cuando no se esté en ella ** */
        if (!empty($_SERVER['REDIRECT_URL'])) {
            $listaInicio[] = HTML::enlace($textos->id('INICIO'), '/');
        }
        //$sql->depurar = true;
        $menus = $sql->seleccionar(array('menus'), array('id', 'nombre', 'destino'), 'id > 0 AND activo = "1"', 'id', 'orden ASC');
        $columna = 0;
        $celdas = array();

        while ($menu = $sql->filaEnObjeto($menus)) {

            $paginas = $sql->seleccionar(array('paginas'), array('id', 'titulo'), 'id > 0 AND activo = "1" AND id_menu = "' . $menu->id . '"', 'id', 'orden ASC');

            $subMenu = array();
            $celda = '';

            while ($fila = $sql->filaEnObjeto($paginas)) {
                $subMenu[] = HTML::enlace($fila->titulo, '/pages/' . $fila->id);
                $celda .= HTML::parrafo(HTML::enlace(HTML::icono('puntaDerecha') . $fila->titulo, '/pages/' . $fila->id));
            }

            if ($menu->destino) {
                $listaInicio[] = HTML::enlace($menu->nombre, $menu->destino);//, 'menuAjax'); //. HTML::lista($subMenu, 'subMenu', 'ui-state-default'); //MENU PRINCIPAL
            } else {
                $columnas[] = HTML::frase(HTML::icono('puntaAbajo') . $menu->nombre);
            }

            $clasesColumnas[] = 'izquierda arriba bordeInferior';
            $clasesFilas[] = 'izquierda arriba';
            $celdas[0][$columna] = $celda;
            $columna++;
        }

        /*         * * Adicionar enlace a la página del perfil del usuario actual cuando no se esté en ella ** */
        if (isset($_SERVER['REQUEST_URI']) && isset($sesion_usuarioSesion->url) && ($_SERVER['REQUEST_URI'] != $sesion_usuarioSesion->url)) {
            $listaInicio[] = HTML::enlace($textos->id('MI_PERFIL'), $sesion_usuarioSesion->url);
        }


        $enlaces .= '<div class="menu-button">Menu</div><nav class="nav_main_menu ">'.HTML::lista($listaInicio, 'flexnav', '', '', array('data-breakpoint' => '800')).'</nav>';
//        $menuAlterno = HTML::tabla($columnas, $celdas, 'centrado', 'tablaMenuAlternativo', $clasesColumnas, $clasesFilas);
//        $menuAlterno .= HTML::contenedor('', 'sombraInferior');

        self::$etiquetas['ENLACES_CORPORATIVOS'] = $enlaces;
        //self::$etiquetas['MENU_ALTERNATIVO'] = HTML::contenedor($menuAlterno, 'ui-state-default');
    }

    /*     * * Insertar código HTML con las opciones para el inicio de sesión del usuario ** */

    protected static function cargarUsuarioSesion() {
        global $sesion_usuarioSesion, $textos, $sql, $configuracion;

        /*         * * El usuario no ha iniciado sesión ** */
        if (!isset($sesion_usuarioSesion)) {

            /*             * * Formulario para el inicio de sesión de usuarios existentes ** */
            $formaUsuarioExistente = HTML::etiqueta($textos->id('USUARIO'));
            $formaUsuarioExistente .= HTML::campoTexto('usuario', 18, 12, '', '', 'campoUsuario');

            $formaUsuarioExistente .= HTML::etiqueta($textos->id('CONTRASENA'));
            $formaUsuarioExistente .= HTML::campoClave('contrasena', '18', 12);
            $claseSlider = 'oculto estiloSlider ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all';
            $formaUsuarioExistente .= HTML::parrafo($textos->id('DESLICE_LA_BARRA'), 'oculto negrilla margenSuperior', 'parrafoMensajeSlider');
            $formaUsuarioExistente .= HTML::parrafo('', 'negrilla margenSuperior', 'parrafoSlider');
            $formaUsuarioExistente .= HTML::contenedor('', $claseSlider, 'sliderInicio');

            $formaUsuarioExistente .= HTML::boton('usuario', $textos->id('INICIAR_SESION'));
            $formaUsuarioExistente = HTML::forma('/ajax/users/validate', $formaUsuarioExistente);
            $formaUsuarioExistente .= '<br>';
            $formaUsuarioExistente .= HTML::enlace($textos->id('RECORDAR_CONTRASENA'), '#', 'enlaceAjax margenSuperior', 'recordarContrasena', array('alt' => '/ajax/users/remind'));
            $formaUsuarioExistente = HTML::contenedor($formaUsuarioExistente, '', 'contenedorCamposLogin');


            $signup = HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'signup.png', 'margenIzquierda');

            $enlaceRegistro = HTML::parrafo(HTML::enlace($textos->id('REGISTRARSE') . $signup, '#', 'enlaceAjax titulo', 'registrarse', array('alt' => '/ajax/users/register')), 'parrafoSignUp');
            $enlaceRegistro = HTML::contenedor($enlaceRegistro, 'enlaceRegistro');

            $login = HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'key_login.png', 'margenIzquierda');

            $enlaceIniciarSesion = HTML::parrafo($textos->id('INICIAR_SESION') . $login, 'titulo parrafoLogin estiloEnlace', 'textoIniciarSesion');
            $enlaceIniciarSesion = HTML::contenedor($enlaceIniciarSesion, 'enlaceRegistro');


            $enlaceRegistroPequeño = HTML::enlace($textos->id('REGISTRARSE'), '#', 'enlaceAjax', 'registrarse', array('alt' => '/ajax/users/register'));
            $enlaceLoginPequeño = HTML::frase($textos->id('INICIAR_SESION'), 'estiloEnlace', 'textoIniciarSesionPequeno');

            $textoUsuarioLogin = str_replace('%1', $enlaceLoginPequeño, $textos->id('TEXTO_USUARIO_LOGIN'));
            $textoUsuarioLogin = str_replace('%2', $enlaceRegistroPequeño, $textoUsuarioLogin);



            $textoANDayahoculto .= HTML::parrafo($textoUsuarioLogin, 'textoUsuarioLogin margenSuperior margenInferior');


            self::$etiquetas['BLOQUE_USUARIO'] = HTML::contenedor($textoANDayahoculto . $enlaceRegistro . $enlaceIniciarSesion . $formaUsuarioExistente, 'bloqueLogueo');


            /*             * * El usuario ya se encuentra autenticado (ha iniciado sesión) ** */
        } else {
            $modulos = $sql->seleccionar(array('modulos'), array('id', 'clase', 'nombre', 'url'), 'visible = "1"', '', 'clase ASC, orden ASC');


            while ($modulo = $sql->filaEnObjeto($modulos)) {
                if (($sesion_usuarioSesion->id != 0 && $modulo->clase != 1) || ($sesion_usuarioSesion->id == 0)) {
                    if ((isset($sesion_usuarioSesion) && Perfil::verificarPermisosConsulta($modulo->id)) || ($sesion_usuarioSesion->id == 0)) {
                        $indice = $textos->id('MODULOS_CLASE_' . $modulo->clase);
                        $textoEnlace = HTML::icono('anguloDerecha');
                        $textoEnlace .= $textos->id($modulo->nombre);
                        if (!isset($lista[$indice])) {
                            $lista[$indice] = '';
                        }
                        $lista[$indice] .= HTML::enlace($textoEnlace, '/' . $modulo->url, 'opcionMenu ui-state-default');
                    }
                }
            }

            $visible = $sql->obtenerValor('usuarios_conectados', 'visible', 'id_usuario = "' . $sesion_usuarioSesion->id . '"');

            if ($visible == '1') {
                $clase = 'imagenMenuChat';
                $texto = $textos->id('DESCONECTAR_CHAT');
            } else {
                $clase = 'imagenMenuChatOff';
                $texto = $textos->id('CONECTAR_CHAT');
            }

            $imagen = HTML::contenedor('', $clase, 'botonActivarChat');
            $imagen .= HTML::frase($texto, 'ayudaEstadoConexionChat', 'ayudaEstadoConexionChat');
            $amigosConectados = HTML::frase(' ' . Contacto::cantidadAmigosConectados(), 'numeroAmigosConectadosMenu', 'numeroAmigosConectadosMenu');
            $nombrePestana = $textos->id('CHAT') . $amigosConectados;
            $lista[$nombrePestana] = HTML::contenedor(Contacto::amigosConectados(), '', 'bloqueContactosConectados_' . $sesion_usuarioSesion->id);

            $amigosConectados1 = HTML::frase(' ' . Contacto::cantidadUsuariosConectados(), 'numeroAmigosConectadosMenu', 'numeroUsuariosConectadosMenu');
            $nombrePestana1 = $textos->id('USUARIOS_CONECTADOS') . $amigosConectados1;
            $lista[$nombrePestana1] = HTML::contenedor(Contacto::usuariosConectados(), 'bloqueUsuariosConectados', 'bloqueUsuariosConectados_' . $sesion_usuarioSesion->id);

            $contenido = HTML::enlace(HTML::imagen($sesion_usuarioSesion->persona->imagenPrincipal, 'imagenPrincipalUsuario margenInferior'), $sesion_usuarioSesion->url);
            $contenido = HTML::contenedor($contenido, 'imagenUsuarioBloqueDerecho', 'imagenUsuarioBloqueDerecho_' . $sesion_usuarioSesion->id);
            $contenido .= HTML::contenedor(HTML::botonModificarItem($sesion_usuarioSesion->id, $sesion_usuarioSesion->urlBase), 'oculto flotanteDerecha');
            $contenido .= HTML::parrafo($sesion_usuarioSesion->centro, 'centrado negrilla margenInferior');
            $contenido .= HTML::forma('/ajax/users/logout', HTML::parrafo(HTML::boton('encender', $textos->id('FINALIZAR')), 'centrado'));

            self::$etiquetas['BLOQUE_USUARIO'] = HTML::bloque('datosUsuarioActual', $sesion_usuarioSesion->sobrenombre, $contenido, '', NULL, '-IS'); //Aqui debo cambiar creo...

            $opciones = array(
                'imagen' => $imagen,
                'nombre' => $nombrePestana
            );

            self::$etiquetas['BLOQUE_USUARIO'] .= HTML::acordeon(array_keys($lista), array_values($lista), 'menuOpcionesUsuario', 'margenInferior izquierda', '', '', '', $opciones);
        }
    }

    /*     * * Insertar código HTML para mostrar calendario de eventos ** */

    protected static function cargarCalendarioEventos() {

        $calendario = HTML::contenedor('', '', 'calendarioEventos');
        $calendario .= HTML::contenedor('', 'sombraInferior');
        $botonCierre = HTML::contenedor('X', 'botonCerrarTooltip', 'botonCerrarTooltip');
        $contenedor = HTML::contenedor('', 'contenedorDiaEventoInterno', 'contenedorDiaEventoInterno');
        $calendario .= HTML::contenedor($botonCierre . $contenedor, 'contenedorDiaEvento oculto', 'contenedorDiaEvento');
        $calendario = HTML::contenedor($calendario, 'calendarioEventos');

        self::$etiquetas['BLOQUE_CALENDARIO_EVENTOS'] = $calendario;
    }

    /*     * * Insertar código HTML para mostrar calendario de eventos ** */

    protected static function cargarPauta() {
        //$banner = Anuncio::mostrarAnuncio();
        //self::$etiquetas['BLOQUE_PAUTA'] = $banner;
    }

    /*     * * Insertar código HTML para mostrar calendario de eventos ** */

    protected static function cargarEncuesta() {
        global $textos;

        $contenido = '<p>¿Are you ready?</p>';
        $contenido .= '<p class=\'margenSuperior\'><input type=\'radio\' name=\'encuesta\'> Yes, I\'m ready</p>';
        $contenido .= '<p class=\'margenSuperior\'><input type=\'radio\' name=\'encuesta\'> No, I\'m not ready</p>';
        $contenido .= '<p class=\'margen\'><input type=\'radio\' name=\'encuesta\'> I don\'t understand</p>';
        $contenido .= HTML::boton('botonVotar', 'botonVotar', 'chequeo', $textos->id('BOTON_VOTAR'));
    }

    protected static function cargarBotonesRedes() {
        global $configuracion;

        $botonRSS = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstaticas'] . '/' . $configuracion['RUTAS']['botonRSS'];
        $botonFacebook = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstaticas'] . '/' . $configuracion['RUTAS']['botonFacebook'];
        $botonTwitter = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstaticas'] . '/' . $configuracion['RUTAS']['botonTwitter'];

        $botones = '<ul id=\'botonesRedes\'>';
        $botones .= '<li><a href=\'/rss.php\'><img alt=\'RSS\' src="' . $botonRSS . '" width="25" height="25"/></a></li>';
        $botones .= '<li><a href=\'http://www.facebook.com/pages/ABLAOnline/347715389619\' target=\'_blank\'><img alt=\'Find us on facebook\' src="' . $botonFacebook . '" width="25" height="25"/></a></li>';
        $botones .= '<li><a href=\'http://www.twitter.com/ablaonline\' target=\'_blank\'><img alt=\'Follow us on twitter\' src="' . $botonTwitter . '" width="25" height="25"/></a></li>';
        $botones .= '</ul>';

        self::$etiquetas['BLOQUE_REDES2'] = HTML::contenedor($botones, 'socialNet');
    }

    protected static function cargarAudio() {
        global $sesion_usuarioSesion;
        $codigo = '';
        if (isset($sesion_usuarioSesion)) {
            $codigo = '<div style="display:none">';

            $codigo .= '<audio id="sonido">';
            $codigo .= '<source src="http://media.ablaonline.org/javascript1/click.wav" type="audio/wav"/>';
            $codigo .= 'Your browser does not support the audio element.';
            $codigo .= '</audio> </div>';

            self::$etiquetas['CODIGO_AUDIO'] = $codigo;
        }
    }

    /**
     * Metodo que retorna un div con el anuncio que esta establecido como activo
     * */
    public static function cargarAsociados() {
        global $sql, $configuracion;

        $codigo = '';
        $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/';

        $tablas = array(
            'a' => 'asociados',
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

        if ($sql->filasDevueltas) {
            $codigo = '<ul id="listaAsociados">';

            while ($asociado = $sql->filaEnObjeto($consulta)) {
                $codigo .= '
                          <li class="flotanteIzquierda margenIzquierda">
                            
                                <a href = "' . $asociado->vinculo . '" target="_blank">
                                    <img src="' . $ruta . $asociado->ruta . '" class="imagenesAsociados" id="imagenesAsociados"/>
                                </a>
                          
                          </li>
                        ';
            }
            $codigo .= '</ul>';
        }
        self::$etiquetas['ASOCIADOS'] = $codigo;
    }

    /*     * * Enviar código HTML generado al cliente ** */

    public static function generarCodigo() {

        foreach (self::$etiquetas as $etiqueta => $valor) {
            self::$contenido = preg_replace('/\{\%' . $etiqueta . '\%\}/', rtrim($valor), self::$contenido);
        }
    }

}

?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Base
 * @author       Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 * Clase Recursos: clase compuesta principalmente por metodos estaticos los cuales son utilizados a lo largo de la aplicación
 * para diversas funciones, entre ellas validación de información, pero principalmente para el renderizado de bloques de código
 * que serán reutilizados a todo lo largo de la aplicación, por ejemplo generar el bloque de codigo multimedia o de archivos.
 *
 * */
class Recursos {

    /**
     *
     * Funcion que carga el bloque con los comentarios realizados a un determinado item de un determinado modulo
     *
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @param  type $modulo
     * @param  type $registro
     * @param  type $propietario
     * @return type Bloque de codigo HTML cuyo contenido dependera del modulo en el que se encuentre y del registro
     * 
     */
    static function bloqueComentarios($modulo, $registro, $propietario) {
        global $sql, $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }

        if (isset($sesion_usuarioSesion)) {
            $moduloActual = new Modulo($modulo);
            $bloqueComentarios = HTML::campoOculto('idModulo', $moduloActual->id);
            $bloqueComentarios .= HTML::campoOculto('idRegistro', $registro);
            $bloqueComentarios .= HTML::boton('comentario', $textos->id('COMENTAR'), 'flotanteDerecha margenInferior');
            $bloqueComentarios = HTML::forma(HTML::urlInterna('INICIO', '', true, 'addComment'), $bloqueComentarios);

        } else {
            $bloqueComentarios = HTML::parrafo($textos->id('ERROR_COMENTARIO_SESION'), 'margenInferior');

        }

        $comentarios = new Comentario();
        $listaComentarios = array();

        if ($comentarios->contar($modulo, $registro)) {

            foreach ($comentarios->listar($modulo, $registro) as $comentario) {

                $botonEliminar = '';
                if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $comentario->idAutor) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) || $modulo == 'CENTROS' && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 2 && $sesion_usuarioSesion->idCentro == $registro) {
                    $botonEliminar = HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('INICIO', '', true, 'deleteComment'), array('id' => $comentario->id));
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista', 'botonesLista');
                }

                $contenidoComentario = '';
                $contenidoComentario .= $botonEliminar;
                $contenidoComentario .= HTML::enlace(HTML::imagen($comentario->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('USUARIOS', $comentario->usuarioAutor));
                $contenidoComentario .= HTML::parrafo(HTML::enlace($comentario->autor, HTML::urlInterna('USUARIOS', $comentario->usuarioAutor)) . $textos->id('USUARIO_DIJO'), 'negrilla margenInferior');
                $contenidoComentario .= HTML::parrafo(nl2br($comentario->contenido));
                $contenidoComentario .= HTML::parrafo(date('D, d M Y h:i:s A', $comentario->fecha), 'pequenia cursiva negrilla margenSuperior margenInferior');

                $listaComentarios[] = HTML::contenedor($contenidoComentario, 'contenedorListaComentarios', 'contenedorComentario' . $comentario->id);

            }

        } else {
            $listaComentarios[] = HTML::frase($textos->id('SIN_COMENTARIOS'), 'margenInferior', 'sinRegistros');

        }

        $bloqueComentarios .= HTML::lista($listaComentarios, 'listaVertical listaConIconos bordeSuperiorLista', 'botonesOcultos', 'listaComentarios');

        return $bloqueComentarios;

    }

    /**
     *
     * Metodo que se encarga de armar el bloque donde se muestran los foros 
     * pertenecientes a un determinado item de un determinado modulo
     *
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @param string|entero $modulo string=representa el nombre del modulo, entero=representa el id del modulo
     * @param entero $registro representa el id del registro
     * @param entero $propietario representa el id del usuario propietario del registro
     * @return type 
     */
    static function bloqueForos($modulo, $registro, $propietario) {
        global $sql, $textos, $sesion_usuarioSesion, $configuracion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }

        if (isset($sesion_usuarioSesion)) {
            $moduloActual = new Modulo($modulo);

            if (Perfil::verificarPermisosAdicion(23) || $sesion_usuarioSesion->idTipo == 0 || $modulo == 'CENTROS' && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 2 && $sesion_usuarioSesion->idCentro == $registro) {
                $bloqueForos = HTML::campoOculto('idModulo', $moduloActual->id);
                $bloqueForos .= HTML::campoOculto('idRegistro', $registro);
                $bloqueForos .= HTML::boton('comentario', $textos->id('INICIAR_TEMA_FORO'), 'flotanteDerecha margenInferior');
                $bloqueForos = HTML::forma(HTML::urlInterna('FOROS', '', true, 'addTopic'), $bloqueForos);
            }
        } else {
            $bloqueForos = HTML::parrafo($textos->id('ERROR_FORO_SESION'), 'margenInferior');
        }


        $foros = new Foro();
        $listaForos = array();
        $botonEliminar = '';

        if ($foros->contar($modulo, $registro)) {

            foreach ($foros->listar($modulo, $registro) as $foro) {

                if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario) || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $foro->idAutor) {
                    $botonEliminar = HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('FOROS', '', true, 'deleteRegister'), array('id' => $foro->id));
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista flotanteDerecha', 'botonesLista');
                }

                $contenidoForo = $botonEliminar;
                //seleccionar el genero de una persona 
                $persona = new Persona($foro->idAutor);
                $contenidoForo .= HTML::enlace(HTML::imagen($foro->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $foro->url);
                $contenidoForo .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $persona->idGenero . '.png') . str_replace('%1', HTML::enlace($foro->autor, HTML::urlInterna('USUARIOS', $foro->usuarioAutor)), $textos->id('PUBLICADO_POR')));
                $contenidoForo2 = HTML::enlace(HTML::parrafo($foro->titulo, 'negrilla'), $foro->url);
                $contenidoForo2 .= HTML::parrafo(date('D, d M Y h:i:s A', $foro->fecha), 'pequenia cursiva negrilla');
                $contenidoForo2 .= HTML::parrafo('Responses: ' . $foros->contarMensajes($foro->id), 'cursiva negrilla flotanteDerecha');
                $contenidoForo .= HTML::contenedor($contenidoForo2, 'fondoUltimos5GrisB'); //barra del contenedor gris


                $listaForos[] = HTML::contenedor($contenidoForo, 'contenedorListaForos', 'contenedorForo' . $foro->id);
            }
        } else {
            $listaForos[] = HTML::frase($textos->id('SIN_TEMAS'), 'margenInferior');
        }


        $bloqueForos .= HTML::lista($listaForos, 'listaVertical listaConIconos bordeSuperiorLista', 'botonesOcultos', 'listaForos');

        return $bloqueForos;
    }

    /**
     * Validar archivo
     * */
    public function getTipoArchivo($archivo) {

        if (!empty($archivo)) {

            $extension_archivo = strtolower(substr($archivo, (strrpos($archivo, '.') - strlen($archivo)) + 1));

            $formatosImagen = array("png", "gif", "jpg", "jpeg");
            $formatosAudio = array("wma", "wav", "ogg", "mp3", "3gp", "3gpp");
            $formatoPdf = array("pdf");
            $formatoDoc = array("doc", "docx", "odt", "sxw");
            $formatoXls = array("xls", "xlsx", "ods", "sxc");
            $formatoPpt = array("ppt", "pps", "odp", "sxi");
            $formatoTxt = array("txt");
            $formatoDocumento = array("pdf", "doc", "odt", "sxw", "xls", "xlsx", "ods", "sxc", "ppt", "pps", "odp", "sxi", "txt");

            $tipoArchivo = '';
            if (preg_match("/\byoutube\b/i", $archivo) && !in_array($extension_archivo, $formatoDocumento) && !in_array($extension_archivo, $formatosAudio) && !in_array($extension_archivo, $formatosImagen)) {
                $tipoArchivo = 'video';
            } else {
                if (in_array($extension_archivo, $formatoPdf)) {
                    $tipoArchivo = 'pdf';
                } elseif (in_array($extension_archivo, $formatoDoc)) {
                    $tipoArchivo = 'doc';
                } elseif (in_array($extension_archivo, $formatoXls)) {
                    $tipoArchivo = 'xls';
                } elseif (in_array($extension_archivo, $formatoPpt)) {
                    $tipoArchivo = 'ppt';
                } elseif (in_array($extension_archivo, $formatoTxt)) {
                    $tipoArchivo = 'txt';
                } elseif (in_array($extension_archivo, $formatosAudio)) {
                    $tipoArchivo = 'audio';
                } elseif (in_array($extension_archivo, $formatosImagen)) {
                    $tipoArchivo = 'imagen';
                }
            }

            return $tipoArchivo;
        } else {
            return false;
        }
    }

    /**
     * Devuelve el bloque de videos dependiendo del modulo de donde se llame
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @param string|entero $modulo string=representa el nombre del modulo, entero=representa el id del modulo
     * @param entero $registro representa el id del registro
     * @param entero $propietario representa el id del usuario propietario del registro
     * @return null 
     */
    static function bloqueVideos($modulo, $registro, $propietario, $tienePermisos = NULL) {
        global $sql, $textos, $configuracion, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem("modulos", "nombre", $modulo)) {
            return NULL;
        }

        $moduloActual = new Modulo($modulo);
        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || $tienePermisos) {

            $bloqueVideos = HTML::campoOculto("idModulo", $moduloActual->id);
            $bloqueVideos .= HTML::campoOculto("idRegistro", $registro);
            $bloqueVideos .= HTML::boton("video", $textos->id("ADICIONAR_VIDEO"), "flotanteDerecha margenInferior");
            $bloqueVideos = HTML::forma(HTML::urlInterna("INICIO", "", true, "addVideo"), $bloqueVideos);
        } elseif (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id != $propietario) {
            $bloqueVideos = HTML::parrafo($textos->id("ERROR_VIDEO_PROPIETARIO"), "margenInferior");
        } else {
            $bloqueVideos = HTML::parrafo($textos->id("ERROR_VIDEO_SESION"), "margenInferior");
        }

        $videos = new Video();
        $listaVideos = array();
        $listaVideos2 = array();
        $botonEliminar = "";

        $cantidadVideos = $videos->contar($modulo, $registro);

        if ($cantidadVideos) {
            $contador = 0; // contador que determina si en el listado hay videos de youtube
            $contador1 = 0; // contador que determina si en el listado hay videos subidos al servidor

            $comentario = new Comentario();

            foreach ($videos->listar(0, 0, $modulo, $registro) as $video) {//recorro el listado de videos
                $comentarios = $comentario->contar("VIDEOS", $video->id);
                if (!$comentarios) {
                    $comentarios = ' 0';
                }
                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $video->idAutor) {// codigo para crear el boton para eliminar un video
                    $botonEliminar = HTML::botonAjax("basura", "x", HTML::urlInterna("INICIO", "", true, "deleteNewVideo"), array("id" => $video->id));
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, "contenedorBotonesLista", "contenedorBotonesLista"), "botonesLista flotanteDerecha margenInferior", "botonesLista");
                }

                $contenedorComentarios = '';
                if (isset($sesion_usuarioSesion)) {
                    $contenedorComentarios = HTML::contenedor($comentarios, "contenedorBotonComentariosItems botonComentariosItemsGris", "contenedorBotonComentariosItems", array("ruta" => "/ajax/users/comment", "modulo" => "VIDEOS", "registro" => $video->id, "propietarioItem" => $video->idAutor, "ayuda" => $textos->id("HACER_COMENTARIO")));
                    $contenedorComentarios = HTML::contenedor($contenedorComentarios, "contenedorComentariosListaVideos");
                }

                $contenidoVideo = ""; //declaro la variable que almacenara el contenido de videos de youtube
                $contenidoVideo2 = ""; //declaro la variable que almacenara el contenido de videos subidos al servidor

                if ($video->enlace != "--") {//determina de que si es un video de youtube                    
                    if (preg_match("/\byoutube\b/i", $video->enlace)) {
                        //Aqui entraria toda la validacion de si viene de youtube
                        $codigo = explode("=", $video->enlace);
                        $codigo = explode("&", $codigo[1]);

                        if (!preg_match("/http/i", $video->enlace)) {
                            $video->enlace = "http://" . $video->enlace;
                        }

                        $descripcion = HTML::campoOculto("descripcion", $video->descripcion . "<p><span class='negrilla margenSuperior'>" . $textos->id("ENLACE") . ": </span></p>" . "<a href='" . $video->enlace . "' rel='prettyPhoto[]'>$video->enlace</a>", "descripcion");
                        $contenidoVideo .= $descripcion . $botonEliminar . $contenedorComentarios . HTML::enlace($video->titulo . "¬" . $video->descripcion, $video->enlace, "enlaceVideo", "");
                    }
                    $contador++;
                } else {//determina de que si es un video de los de ablaonline v1, subidos al servidor
                    $reproductor = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["reproductor"] . "?file=";
                    $contenidoVideo2 .= HTML::enlace("", $reproductor . $video->ruta, "recursoVideo");
                    $contador1++;
                }

                $listaVideos[] .= $contenidoVideo;
                $listaVideos2[] .= $botonEliminar . $contenidoVideo2;
            }

            if ($contador > 0) {
                $contenedor = HTML::contenedor("", "", "ytvideo") . HTML::contenedor("", "", "descripcionVideo");
                $listaVideos = HTML::lista($listaVideos, "listaVideos listaVertical bordeSuperiorLista", "botonesOcultos", "listaVideos");
                $contenedorSuperior = HTML::contenedor($contenedor . $listaVideos, "yt_holder", "");
                $bloqueVideos .= $contenedorSuperior;
            }

            if ($contador1 > 0) {
                $listaVideos2 = HTML::lista($listaVideos2, "listaVideos2 listaVertical bordeSuperiorLista", "botonesOcultos", "listaVideos2");
                $bloqueVideos .= $listaVideos2;
            }
        } else {
            $listaVideos[] = HTML::frase($textos->id("SIN_VIDEOS"), "margenInferior");
            $listaVideos = HTML::lista($listaVideos, "listaVideos listaVertical bordeSuperiorLista", "botonesOcultos", "listaVideos");
            $bloqueVideos .= $listaVideos;
        }

        return $bloqueVideos;
    }

    /**
     * Funcion que permite mostrar un listado con los audios pertenecientes
     * a determinado item en determinado modulo
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @param string|entero $modulo string=representa el nombre del modulo, entero=representa el id del modulo
     * @param entero $registro representa el id del registro
     * @param entero $propietario representa el id del usuario propietario del registro
     * @return type 
     */
    static function bloqueAudios($modulo, $registro, $propietario, $tienePermisos = NULL) {
        global $sql, $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }

        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || $tienePermisos) {
            $moduloActual = new Modulo($modulo);
            $bloqueAudios = HTML::campoOculto('idModulo', $moduloActual->id);
            $bloqueAudios .= HTML::campoOculto('idRegistro', $registro);
            $bloqueAudios .= HTML::boton('conVolumen', $textos->id('ADICIONAR_AUDIO'), 'flotanteDerecha margenInferior');
            $bloqueAudios = HTML::forma(HTML::urlInterna('AUDIOS', '', true, 'addAudio'), $bloqueAudios);
        } elseif (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id != $propietario) {
            $bloqueAudios = HTML::parrafo($textos->id('ERROR_AUDIO_PROPIETARIO'), 'margenInferior');
        } else {
            $bloqueAudios = HTML::parrafo($textos->id('ERROR_AUDIO_SESION'), 'margenInferior');
        }

        $audios = new Audio();
        $listaAudios = array();
        $botonEliminar = '';

        $cantidadAudios = $audios->contar($modulo, $registro);

        if ($cantidadAudios) {

            $comentario = new Comentario();
            foreach ($audios->listar(0, 0, $modulo, $registro) as $audio) {

                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $audio->idAutor) {
                    $botonEliminar = 'verdadero'; //se manda esta palabra al javascript a ver si se muestra el botonsito de eliminar
                }

                $comentarios = $comentario->contar('AUDIOS', $audio->id);
                if (!$comentarios) {
                    $comentarios = ' 0';
                }

                $arreglo = $audio->enlace . '¬' . $audio->titulo . '¬' . (int)$audio->id . '¬' . $botonEliminar . '¬' . $propietario . '¬' . $comentarios;

                $listaAudios[] .= $arreglo;
            }

            $listaAudios = implode('|', $listaAudios);
            $bloqueAudios .= HTML::campoOculto('audios', $listaAudios, 'listadoAudios');
            $bloqueAudios .= HTML::armarReproductorAudio();
        } else {
            $listaAudios[] = HTML::frase($textos->id('SIN_AUDIOS'), 'margenInferior');
            $listaAudios = HTML::lista($listaAudios, 'listaAudios listaVertical bordeSuperiorLista', 'botonesOcultos', 'listaAudios');
            $bloqueAudios .= HTML::campoOculto('audios', '', 'listadoAudios'); //para que el javascript que busca la lista de audios siempre encuentre el campo oculto y no de error
            $bloqueAudios .= $listaAudios;
        }

        return $bloqueAudios;
    }

    /**
     * Funcion que arma y devuelve un bloque de codigo HTML con el listado
     * de documentos que pertenecen a un determinado item de un determinado modulo
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @param string|entero $modulo string=representa el nombre del modulo, entero=representa el id del modulo
     * @param entero $registro representa el id del registro
     * @param entero $propietario representa el id del usuario propietario del registro
     * @return type bloque de codigo html con el listado de documentos
     */
    static function bloqueArchivos($modulo, $registro, $propietario, $tienePermisos = NULL) {
        global $sql, $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }
        $moduloActual = new Modulo($modulo);
        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || $tienePermisos) {

            $bloqueArchivos = HTML::campoOculto('idModulo', $moduloActual->id);
            $bloqueArchivos .= HTML::campoOculto('idRegistro', $registro);
            $bloqueArchivos .= HTML::boton('documentoNuevo', $textos->id('ADICIONAR_ARCHIVO'), 'flotanteDerecha margenInferior');
            $bloqueArchivos = HTML::forma(HTML::urlInterna('DOCUMENTOS', '', true, 'addDocument'), $bloqueArchivos);
        } elseif (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id != $propietario) {
            $bloqueArchivos = HTML::parrafo($textos->id('ERROR_ARCHIVO_PROPIETARIO'), 'margenInferior');
        } else {
            $bloqueArchivos = HTML::parrafo($textos->id('ERROR_ARCHIVO_SESION'), 'margenInferior');
        }


        $archivos = new Documento();
        $listaArchivos = array();
        $botonEliminar = '';
        //$usuarioActual    = new Usuario($propietario);

        $cantidadArchivos = $archivos->contar($modulo, $registro);

        if ($cantidadArchivos) {

            $comentario = new Comentario();

            foreach ($archivos->listar(0, 8, $modulo, $registro) as $archivo) {

                $comentarios = $comentario->contar('DOCUMENTOS', $archivo->id);
                if (!$comentarios) {
                    $comentarios = ' 0';
                }

                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $archivo->idAutor) {
                    $botonEliminar = HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('DOCUMENTOS', '', true, 'deleteDocument'), array('id' => $archivo->id));
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista flotanteDerecha', 'botonesLista');
                }

                $contenedorComentarios = '';
                if (isset($sesion_usuarioSesion)) {
                    $contenedorComentarios = HTML::contenedor($comentarios, 'contenedorBotonComentariosItems botonComentariosItemsGris', 'contenedorBotonComentariosItems', array('ruta' => '/ajax/users/comment', 'modulo' => 'DOCUMENTOS', 'registro' => $archivo->id, 'propietarioItem' => $archivo->idAutor, 'ayuda' => $textos->id('HACER_COMENTARIO')));
                    $contenedorComentarios = HTML::contenedor($contenedorComentarios, 'contenedorComentariosListaDocumentos');
                }


                $contenidoArchivo = $botonEliminar . $contenedorComentarios;
                $contenidoArchivo .= HTML::enlace(HTML::imagen($archivo->icono, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $archivo->enlace);
                $contenidoArchivo .= HTML::parrafo(HTML::enlace($archivo->titulo, $archivo->enlace));
                $contenidoArchivo2 = HTML::parrafo($archivo->descripcion);
                $contenidoArchivo2 .= HTML::parrafo(HTML::frase($textos->id('ENLACE') . ': ', 'negrilla') . $archivo->enlace, 'margenSuperior');
                $contenidoArchivo .= HTML::contenedor($contenidoArchivo2, 'contenedorGrisLargo');

                $listaArchivos[] = HTML::contenedor($contenidoArchivo, 'contenedorListaDocumentos', 'contenedorDocumento' . $archivo->id);
            }//fin del foreach

            if ($cantidadArchivos >= 8) {
                $listaArchivos[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('DOCUMENTOS', $moduloActual->url . '/' . $registro), 'flotanteCentro margenSuperior') . '</BR></BR>';
            }
        } else {
            $listaArchivos[] = HTML::frase(HTML::parrafo($textos->id('SIN_ARCHIVOS'), 'sinRegistros', 'sinRegistros'), 'margenInferior');
        }


        $bloqueArchivos .= HTML::lista($listaArchivos, 'listaVertical bordeSuperiorLista', '', 'listaDocumentos');

        return $bloqueArchivos;
    }
    /**
     * Funcion que arma y devuelve un bloque de codigo HTML con el listado
     * de imagenes que pertenecen a un determinado item de un determinado modulo
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @param string|entero $modulo string=representa el nombre del modulo, entero=representa el id del modulo
     * @param entero $registro representa el id del registro
     * @param entero $propietario representa el id del usuario propietario del registro
     * @return type bloque de codigo html con el listado de documentos
     */
    static function bloqueImagenes($modulo, $registro, $propietario, $tienePermisos = NULL) {
        global $sql, $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }

        $moduloActual = new Modulo($modulo);

        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || $tienePermisos) {

            $bloqueImagens = HTML::campoOculto('idModulo', $moduloActual->id);
            $bloqueImagens = HTML::campoOculto('modulo', $moduloActual->nombre);
            $bloqueImagens .= HTML::campoOculto('idRegistro', $registro);
            $bloqueImagens .= HTML::boton('imagen', $textos->id('ADICIONAR_IMAGEN'), 'flotanteDerecha margenInferior');
            $bloqueImagens = HTML::forma(HTML::urlInterna('INICIO', '', true, 'addImage'), $bloqueImagens);
            //$bloqueImagens  = HTML::forma(HTML::urlInterna('INICIO', '', true, 'callScript'), $bloqueImagens);
            // Recursos::escribirTxt('id_modulo::::: '.$modulo, 5);
        } elseif (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id != $propietario) {
            $bloqueImagens = HTML::parrafo($textos->id('ERROR_IMAGEN_PROPIETARIO'), 'margenInferior');
        } else {
            $bloqueImagens = HTML::parrafo($textos->id('ERROR_IMAGEN_SESION'), 'margenInferior');
        }


        $imagenes = new Imagen();
        $listaImagens = array();
        $botonEliminar = '';
        $usuarioActual = new Usuario($propietario);
        $imagenUsuario = 0;
        if (isset($usuarioActual->persona)) {
            $imagenUsuario = $usuarioActual->persona->idImagen;
        }

        $arregloImagenes = $imagenes->listar(0, 8, $modulo, $registro);

        if (sizeof($arregloImagenes) > 0) {

            $comentario = new Comentario();

            foreach ($arregloImagenes as $imagen) {

                $comentarios = $comentario->contar('IMAGENES', $imagen->id);
                if (!$comentarios) {
                    $comentarios = ' 0';
                }

                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $imagen->idAutor) {
                    $botonEliminar = HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('INICIO', '', true, 'deleteImage'), array('id' => $imagen->id));
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista flotanteDerecha', 'botonesLista');
                }

                $contenedorComentarios = '';
                if (isset($sesion_usuarioSesion)) {
                    $contenedorComentarios = HTML::contenedor($comentarios, 'contenedorBotonComentariosItems botonComentariosItemsGris', 'contenedorBotonComentariosItems', array('ruta' => '/ajax/users/comment', 'modulo' => 'IMAGENES', 'registro' => $imagen->id, 'propietarioItem' => $imagen->idAutor, 'ayuda' => $textos->id('HACER_COMENTARIO')));
                    $contenedorComentarios = HTML::contenedor($contenedorComentarios, 'contenedorComentariosListaImagenes');
                }


                if (($imagenUsuario != $imagen->id) && ($imagen->id != 0)) {
                    $contenidoImagen = $botonEliminar . $contenedorComentarios;
                    $img = HTML::imagen($imagen->miniatura, 'listaImagenes recursoImagen', '', array('title' => $imagen->titulo));
                    $contenidoImagen .= HTML::enlace($img, $imagen->ruta, '', '', array('rel' => 'prettyPhoto[' . $sesion_usuarioSesion->id . ' ]'));
                    if ($imagen->titulo) {
                        $contenidoImagen .= HTML::parrafo($imagen->titulo, 'negrilla');
                    } else {
                        $contenidoImagen .= HTML::parrafo('No title', 'negrilla');
                    }
                    if ($imagen->descripcion) {
                        $contenidoImagen .= HTML::parrafo($imagen->descripcion);
                    } else {
                        $contenidoImagen .= HTML::parrafo('No description');
                    }
                    $contenidoImagen .= HTML::parrafo(HTML::frase($textos->id('ENLACE') . ': ', 'negrilla') . $imagen->ruta, 'margenSuperior');
                    $contenidoImagen = HTML::contenedor($contenidoImagen, 'contenedorImagen', 'contenedorImagen' . $imagen->id);
                    $listaImagens[] .= $contenidoImagen;
                }//fin de si el usuario va a ver su foto de perfil en el listado
            }
            //if (sizeof($listaImagens) >= 8) {
            $listaImagens[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('IMAGENES', $moduloActual->url . '/' . $registro), 'flotanteCentro margenSuperior') . '</BR></BR>';
            // }
        } else {
            $listaImagens[] = HTML::frase($textos->id('SIN_IMAGENES'), 'margenInferior');
        }


        $bloqueImagens .= HTML::lista($listaImagens, 'listaVertical listaConImagenes bordeSuperiorLista', '', 'listaImagenes');

        return $bloqueImagens;
    }

    /**
     * Funcion que arma y devuelve un bloque de codigo HTML con el listado
     * de galerias que pertenecen a un determinado item de un determinado modulo
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @param string|entero $modulo string=representa el nombre del modulo, entero=representa el id del modulo
     * @param entero $registro representa el id del registro
     * @param entero $propietario representa el id del usuario propietario del registro
     * @return type bloque de codigo html con el listado de documentos
     */
    static function bloqueGalerias($modulo, $registro, $propietario, $tienePermisos = NULL) {
        global $sql, $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }

        $moduloActual = new Modulo($modulo);

        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || $tienePermisos) {

            $bloqueGalerias = HTML::campoOculto('idModulo', $moduloActual->id);
            $bloqueGalerias .= HTML::campoOculto('idRegistro', $registro);
            $bloqueGalerias .= HTML::boton('imagen', $textos->id('AGREGAR_GALERIA'), 'flotanteDerecha margenInferior');
            $bloqueGalerias = HTML::forma(HTML::urlInterna('GALERIAS', '', true, 'add'), $bloqueGalerias);
            //$bloqueGalerias  = HTML::forma(HTML::urlInterna('INICIO', '', true, 'callScript'), $bloqueGalerias);
            // Recursos::escribirTxt('id_modulo::::: '.$modulo, 5);
        } elseif (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id != $propietario) {
            $bloqueGalerias = HTML::parrafo($textos->id('ERROR_IMAGEN_PROPIETARIO'), 'margenInferior');
        } else {
            $bloqueGalerias = HTML::parrafo($textos->id('ERROR_IMAGEN_SESION'), 'margenInferior');
        }


        $galerias = new Galeria();
        $listaGalleries = array();
        $botonEliminar = '';


        $arregloGalerias = $galerias->listar(0, 8, $modulo, $registro);

        if (sizeof($arregloGalerias) > 0) {

            $comentario = new Comentario();

            foreach ($arregloGalerias as $galeria) {

                $comentarios = $comentario->contar('GALERIAS', $galeria->id);
                if (!$comentarios) {
                    $comentarios = ' 0';
                }

                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $galeria->idAutor) {
                    $botonEliminar = HTML::contenedor(HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('GALERIAS', '', true, 'delete'), array('id' => $galeria->id)), 'alineadoDerecha');
                    $botonEliminar .= HTML::contenedor(HTML::botonAjax('lapiz', 'MODIFICAR', HTML::urlInterna('GALERIAS', '', true, 'edit'), array('id' => $galeria->id)), 'alineadoDerecha');
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista flotanteDerecha', 'botonesLista');
                }

                $contenedorComentarios = '';
                if (isset($sesion_usuarioSesion)) {
                    $contenedorComentarios = HTML::contenedor($comentarios, 'contenedorBotonComentariosItems botonComentariosItemsGris', 'contenedorBotonComentariosItems', array('ruta' => '/ajax/users/comment', 'modulo' => 'GALERIAS', 'registro' => $galeria->id, 'propietarioItem' => $galeria->idAutor, 'ayuda' => $textos->id('HACER_COMENTARIO')));
                    $contenedorComentarios = HTML::contenedor($contenedorComentarios, 'contenedorComentariosListaImagenes');
                }

                $contenidoGalerias = $botonEliminar . $contenedorComentarios;

                $contenidoGalerias .= HTML::imagen($galeria->icono, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5 estiloEnlace enlaceGaleria', $galeria->id);
                $contenidoGalerias .= HTML::parrafo($galeria->titulo, 'estiloEnlace enlaceGaleria', $galeria->id);
                $contenidoGalerias2 = HTML::parrafo($galeria->descripcion);
                $contenidoGalerias .= HTML::contenedor($contenidoGalerias2, 'fondoUltimos5Gris');

                $contenidoGalerias = HTML::contenedor($contenidoGalerias, 'contenedorListaGalerias', 'contenedorGaleria' . $galeria->id);
                $listaGalleries[] .= $contenidoGalerias;
            }
        } else {
            $listaGalleries[] = HTML::frase($textos->id('SIN_GALERIAS'), 'margenInferior');
        }


        $bloqueGalerias .= HTML::lista($listaGalleries, 'listaVertical listaConGalerias bordeSuperiorLista', '', 'listaGalerias');

        return $bloqueGalerias;
    }

    /**
     * Funcion que arma y devuelve un bloque de codigo HTML con el listado
     * de enlaces que pertenecen a un determinado item de un determinado modulo
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @param string|entero $modulo string=representa el nombre del modulo, entero=representa el id del modulo
     * @param entero $registro representa el id del registro
     * @param entero $propietario representa el id del usuario propietario del registro
     * @return type bloque de codigo html con el listado de documentos
     */
    static function bloqueEnlaces($modulo, $registro, $propietario, $tienePermisos = NULL) {
        global $sql, $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }

        $moduloActual = new Modulo($modulo);
        $enlaces = new Enlace();
        $bloqueEnlaces = '';
        //Recursos::escribirTxt('permiso: '.Perfil::verificarPermisosAdicion($enlaces->idModulo));

        if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) || (isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicion($enlaces->idModulo) && $sesion_usuarioSesion->id == $propietario ) || $tienePermisos) {

            $bloqueEnlaces = HTML::campoOculto('idModulo', $moduloActual->id);
            $bloqueEnlaces .= HTML::campoOculto('idRegistro', $registro);
            $bloqueEnlaces .= HTML::boton('enlaceNuevo', $textos->id('ADICIONAR_ENLACE'), 'flotanteDerecha margenInferior');
            $bloqueEnlaces = HTML::forma(HTML::urlInterna('ENLACES', '', true, 'addLink'), $bloqueEnlaces);
        }



        $listaEnlaces = array();
        $botonEliminar = '';
        //$usuarioActual   = new Usuario($propietario);

        $cantidadEnlaces = $enlaces->listar(0, 8, $modulo, $registro);
        $cantidadEnlaces = sizeof($cantidadEnlaces);

        if ($cantidadEnlaces) {

            foreach ($enlaces->listar(0, 8, $modulo, $registro) as $enlace) {

                if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $enlace->idAutor)) {
                    $botonEliminar = HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('ENLACES', '', true, 'deleteLink'), array('id' => $enlace->id));
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista flotanteDerecha', 'botonesLista');
                }
                $contenidoEnlace = $botonEliminar;
                if (!preg_match('/http/i', $enlace->enlace)) {
                    $enlace->enlace = 'http://' . $enlace->enlace;
                }
                $contenidoEnlace .= HTML::enlace(HTML::imagen($enlace->icono, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $enlace->enlace);
                $contenidoEnlace .= HTML::parrafo(HTML::enlace($enlace->titulo, $enlace->enlace));
                $contenidoEnlace2 = HTML::parrafo($enlace->descripcion);
                $contenidoEnlace2 .= HTML::parrafo(HTML::frase($textos->id('ENLACE') . ': ', 'negrilla') . $enlace->enlace, 'margenSuperior');
                $contenidoEnlace .= HTML::contenedor($contenidoEnlace2, 'contenedorGrisLargo');

                $listaEnlaces[] = HTML::contenedor($contenidoEnlace, 'contenedorListaDocumentos', 'contenedorEnlace' . $enlace->id);
            }//fin del foreach

            if ($cantidadEnlaces >= 8) {
                $listaEnlaces[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('ENLACES', $moduloActual->url . '/' . $registro), 'flotanteCentro margenSuperior') . '</BR></BR>';
            }
        } else {
            $listaEnlaces[] = HTML::frase(HTML::parrafo($textos->id('NO_HAY_ENLACES_REGISTRADOS'), 'sinRegistros', 'sinRegistros'), 'margenInferior');
        }


        $bloqueEnlaces .= HTML::lista($listaEnlaces, 'listaVertical bordeSuperiorLista', 'botonesOcultos', 'listaEnlaces');

        return $bloqueEnlaces;
    }

    /**
     * Metodo estatico que se encarga de mostrar cuantos registros totales existen en la consulta
     * y tambien muestra al usuario cuantos registros de cuantos esta viendo. Recibe  parametros
     * de tipo numerico
     * */
    public static function contarPaginacion($totalRegistros, $registroInicial, $registroPorPagina, $pagina, $totalPaginas) {
        global $textos;

        $registroMaximo = $registroInicial + $registroPorPagina;

        if ($pagina == $totalPaginas) {

            //codigo para reemplazar los valores que aparecen con el %1 con la variable que se le pasa, y en el texto que se le pasa.
            $texto = str_replace('%1', ($registroInicial + 1), $textos->id('PAGINACION'));
            $texto = str_replace('%2', $totalRegistros, $texto);
            $texto = str_replace('%3', $totalRegistros, $texto);
        } else {

            $texto = str_replace('%1', ($registroInicial + 1), $textos->id('PAGINACION'));
            $texto = str_replace('%2', $registroMaximo, $texto);
            $texto = str_replace('%3', $totalRegistros, $texto);
        }//fin del if


        $response = HTML::parrafo($texto, 'negrita');
        return $response;
    }

/**
 * Metodo encargado de devolver informacion sobre el navegador que esta utilizando el cliente
 *
 **/
static function obtenerNavegador($user_agent) {
     $navegadores = array(
          'Opera' => 'Opera',
          'Mozilla Firefox'=> '(Firebird)|(Firefox)',
          'Galeon' => 'Galeon',
          'Mozilla'=>'Gecko',
          'MyIE'=>'MyIE',
          'Lynx' => 'Lynx',
          'Netscape' => '(Mozilla/4\.75)|(Netscape6)|(Mozilla/4\.08)|(Mozilla/4\.5)|(Mozilla/4\.6)|(Mozilla/4\.79)',
          'Konqueror'=>'Konqueror',
          'Internet Explorer 7' => '(MSIE 7\.[0-9]+)',
          'Internet Explorer 6' => '(MSIE 6\.[0-9]+)',
          'Internet Explorer 5' => '(MSIE 5\.[0-9]+)',
          'Internet Explorer 4' => '(MSIE 4\.[0-9]+)',
);

foreach($navegadores as $navegador=>$pattern){
       if (eregi($pattern, $user_agent))
       return $navegador;
    }

return 'Desconocido';

}





    /**
     * Escribir errores en un archivo txt
     * */
    public static function escribirTxt($texto) {

        $fecha = date("d/m/y H:i:s");
        file_put_contents("/home/ablito/ablaonline/codigo/errores.txt", $fecha." - ".$texto);
    }

    /**
     * Validar archivo
     * */
    public function validarArchivo($archivo, $extensiones) {
        if (!empty($archivo['name'])) {
            $existe = true;
            $extension_archivo = strtolower(substr($archivo['name'], (strrpos($archivo['name'], '.') - strlen($archivo['name'])) + 1));
            if (!empty($extensiones) && is_array($extensiones)) {
                foreach ($extensiones as $extension) {
                    if ($extension_archivo == $extension) {
                        $existe = false;
                    }
                }
            }
            return $existe;
        } else {
            return true;
        }
    }

    /**
     * Metodo para cargar los formularios ('estrellitas')'me Gusta' una vez la persona ha iniciado la sesion
     * */
    public static function cargarMegusta($idModulo, $idItem, $idUsuario) {
        $des = new Destacado();
        $cod = '';
        $datos = array(
            'id_modulo' => $idModulo,
            'id_item' => $idItem,
            'id_usuario' => $idUsuario
        );

        $opciones = array(
            'onMouseOver' => "$('#ayuda').show('drop', {}, 300)",
            'onMouseOut' => "$('#ayuda').hide('drop', {}, 300)",
        );


        $cantidad = $des->cantidadDestacados($idModulo, $idItem); //saber la cantidad de destacados del item
        $meGusta = $des->meGusta($idModulo, $idItem, $idUsuario); //saber si a mi me gusta el item


        if ($cantidad > 0) {
            if ($meGusta > 0) {
                $txt = '';

                if ($cantidad > 1 && $cantidad != 2) {
                    $txt .= ' and ' . ($cantidad - 1) . ' people';
                }
                if ($cantidad > 1 && $cantidad == 2) {
                    $txt .= ' and ' . ($cantidad - 1) . ' person';
                }

                $url = HTML::urlInterna('DESTACADOS', '', true, 'delHighLight');
                $boton = HTML::botonImagenAjax('', 'estrellaAzul', 'iLikeIt', $opciones, $url, $datos, 'formaMeGusta');
                $frase = HTML::frase('You' . $txt . ' like this', 'cantidadDestacados');
                $ayuda = HTML::contenedor('Click on the Star if you Don\'t Like', 'ayudaMeGusta', 'ayuda', array('style' => 'display: none'));

                $cod .= HTML::contenedor($ayuda . HTML::contenedor($frase . $boton, ''), 'meGustaInterno', 'meGustaInterno');
            } else {
                $txt = '';
                if ($cantidad == 1) {
                    $txt .= ' person  likes this...';
                } else {
                    $txt .= ' people  like this...';
                }

                $url = HTML::urlInterna('DESTACADOS', '', true, 'addHighLight');
                $boton = HTML::botonImagenAjax('', 'estrellaGris', 'iLikeIt', $opciones, $url, $datos, 'formaMeGusta');
                $frase = HTML::frase($cantidad . $txt . ' Do you Like it?', 'cantidadDestacados');
                $ayuda = HTML::contenedor('Click on the Star if you Like', 'ayudaMeGusta', 'ayuda', array('style' => 'display: none'));

                $cod .= HTML::contenedor($ayuda . HTML::contenedor($frase . $boton, ''), 'meGustaInterno', 'meGustaInterno');
            }
        } else {
            $cod .= HTML::contenedor(HTML::frase('Do You Like This?', 'cantidadDestacados') . HTML::botonImagenAjax('', 'estrellaGris', 'iLikeIt', $opciones, HTML::urlInterna('DESTACADOS', '', true, 'addHighLight'), $datos, 'formaMeGusta') . HTML::frase('Click on the Star if you Like', 'ayuda', 'ayuda', array('style' => 'display: none')), 'meGustaInterno', 'meGustaInterno');
        }//fin del if    


        return $cod;
    }

    /**
     * Metodo para mostrar los 'me Gusta' si no ha iniciado sesion
     *
     * */
    public static function mostrarMegusta($idModulo, $idItem) {

        $des = new Destacado();
        $cod = '';

        $opciones = array(
            'onMouseOver' => "$('#ayuda').show('drop', {}, 300)",
            'onMouseOut' => "$('#ayuda').hide('drop', {}, 300)",);

        $cantidad = $des->cantidadDestacados($idModulo, $idItem);


        if ($cantidad > 0) {
            $txt = '';
            if ($cantidad == 1) {
                $txt .= ' person likes this';
            } else {
                $txt .= ' people like this';
            }

            $boton = HTML::contenedor('', 'estrellaAzul', '', $opciones);
            $frase = HTML::frase($cantidad . $txt, 'cantidadDestacados');
            $ayuda = HTML::frase('You must  be logged in to rate', 'ayuda', 'ayuda', array('style' => 'display: none'));

            $cod .= HTML::contenedor($frase . $boton . $ayuda, 'meGustaInterno', 'meGustaInterno');
        } else {
            $ayuda = HTML::frase('You must to be logged in to rate', 'ayuda', 'ayuda', array('style' => 'display: none'));
            $boton = HTML::contenedor('', 'estrellaGris', '', $opciones);
            $frase = HTML::frase('Do You Like This?', 'cantidadDestacados');
            $cod .= HTML::contenedor($frase . $boton . $ayuda, 'meGustaInterno', 'meGustaInterno');
        }//fin del if    


        return $cod;
    }

    /**
     * Metodo para contar los 'me Gusta' que tiene un determinado Item
     * */
    public function contarMeGusta($idModulo, $idItem) {
        global $sql;

        $tablas = array(
            'd' => 'destacados'
        );

        $columnas = array(
            'registros' => 'COUNT(d.id_modulo)'
        );

        $condicion = 'd.id_modulo = "' . $idModulo . '" AND d.id_item = "' . $idItem . '" ';
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $meGusta = $sql->filaEnObjeto($consulta);
            return $meGusta->registros;
        } else {
            return NULL;
        }
    }

    /**
     * Metodo que muestra los 'me Gusta' que tiene un determinado Item el el contenedor de la página principal
     * junto a los primeros 5 items
     * */
    public static function mostrarContadorMeGusta($idModulo, $idItem) {
        global $configuracion;

        $cantidad = self::contarMeGusta($idModulo, $idItem);

        if ($cantidad <= 0) {
            $codigo = HTML::contenedor(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'awardOff.png', 'imgCommPosted') . HTML::contenedor(' 0', 'mostrarDivNums'), 'mostrarPostedInf');
        } else {
            $codigo = HTML::contenedor(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'awardOn.png', 'imgCommPosted') . HTML::contenedor($cantidad, 'mostrarDivNums'), 'mostrarPostedInf');
        }

        return $codigo;
    }

    /**
     * Metodo encargado de generar el paginador para los listados de registros dentro de los modulos
     *
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @param type $totalRegistrosActivos   = total de registros activos que tiene determinado modulo
     * @param entero $registro representa el id del registroInicial         = Registro inicial desde el cual debe empezar la consulta         
     * @param entero $registro representa el id del registros               = Total de registros que se deben mostrar por página, este dato se toma desde el archivo de configuracion
     * @param type $pagina                  = Pagina actual en la que se debe empezar la consulta
     * @return type 
     */
    public static function mostrarPaginador($totalRegistrosActivos, $registroInicial, $registros, $pagina, $datos = NULL) {
        global $textos;
        $botonPrimera = $botonUltima = $botonAnterior = $botonSiguiente = $infoPaginacion = '';

        $camposDatos = '';
        if ($datos != NULL && is_array($datos) && count($datos) > 0) {
            foreach ($datos as $nombre => $valor) {
                $camposDatos .= HTML::campoOculto($nombre, $valor);
            }            
        }

        if ($totalRegistrosActivos > $registros) {
            $totalPaginas = ceil($totalRegistrosActivos / $registros);

            if ($pagina > 1) {
                $botonPrimera = HTML::campoOculto('pagina', 1);
                $botonPrimera .= HTML::boton('primero', $textos->id('PRIMERA_PAGINA'), 'directo');
                $botonPrimera = HTML::forma('', $botonPrimera.$camposDatos);
                $botonAnterior = HTML::campoOculto('pagina', $pagina - 1);
                $botonAnterior .= HTML::boton('anterior', $textos->id('PAGINA_ANTERIOR'), 'directo');
                $botonAnterior = HTML::forma('', $botonAnterior.$camposDatos);
            }

            if ($pagina < $totalPaginas) {
                $botonSiguiente = HTML::campoOculto('pagina', $pagina + 1);
                $botonSiguiente .= HTML::boton('siguiente', $textos->id('PAGINA_SIGUIENTE'), 'directo');
                $botonSiguiente = HTML::forma('', $botonSiguiente.$camposDatos);
                $botonUltima = HTML::campoOculto('pagina', $totalPaginas);
                $botonUltima .= HTML::boton('ultimo', $textos->id('ULTIMA_PAGINA'), 'directo');
                $botonUltima = HTML::forma('', $botonUltima.$camposDatos);
            }

            $infoPaginacion = self::contarPaginacion($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas);
        }


        return HTML::contenedor($botonPrimera . $botonAnterior . $botonSiguiente . $botonUltima . $infoPaginacion, 'centrado');
    }

    /**
     * Metodo por si acaso me toca mostrar dos paginadores en una misma pagina
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @param type $totalRegistrosActivos   = total de registros activos que tiene determinado modulo
     * @param entero $registro representa el id del registroInicial         = Registro inicial desde el cual debe empezar la consulta         
     * @param entero $registro representa el id del registros               = Total de registros que se deben mostrar por página, este dato se toma desde el archivo de configuracion
     * @param type $pagina                  = Pagina actual en la que se debe empezar la consulta
     * @return type 
     */
    public static function mostrarPaginador2($totalRegistrosActivos, $registroInicial, $registros, $pagina/* , $totalPaginas */) {
        global $textos;
        $botonPrimera = $botonUltima = $botonAnterior = $botonSiguiente = $infoPaginacion = '';

        if ($totalRegistrosActivos > $registros) {
            $totalPaginas = ceil($totalRegistrosActivos / $registros);

            if ($pagina > 1) {
                $botonPrimera = HTML::campoOculto('pagina1', 1);
                $botonPrimera .= HTML::boton('primero', $textos->id('PRIMERA_PAGINA'), 'directo');
                $botonPrimera = HTML::forma('', $botonPrimera);
                $botonAnterior = HTML::campoOculto('pagina1', $pagina - 1);
                $botonAnterior .= HTML::boton('anterior', $textos->id('PAGINA_ANTERIOR'), 'directo');
                $botonAnterior = HTML::forma('', $botonAnterior);
            }

            if ($pagina < $totalPaginas) {
                $botonSiguiente = HTML::campoOculto('pagina1', $pagina + 1);
                $botonSiguiente .= HTML::boton('siguiente', $textos->id('PAGINA_SIGUIENTE'), 'directo');
                $botonSiguiente = HTML::forma('', $botonSiguiente);
                $botonUltima = HTML::campoOculto('pagina1', $totalPaginas);
                $botonUltima .= HTML::boton('ultimo', $textos->id('ULTIMA_PAGINA'), 'directo');
                $botonUltima = HTML::forma('', $botonUltima);
            }

            $infoPaginacion = self::contarPaginacion($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas);
        }


        return HTML::contenedor($botonPrimera . $botonAnterior . $botonSiguiente . $botonUltima . $infoPaginacion, 'centrado');
    }

    /**
     * Metodo que verifica si un determinado usuario desea recibir notificaciones a su correo
     * */
    public static function recibirNotificacionesAlCorreo($idUsuario) {
        global $sql;

        $notificacion = $sql->obtenerValor('usuarios', 'notificaciones', 'id = "' . $idUsuario . '" ');

        if ($notificacion == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * Metodo para capturar la direccion ip real del cliente
     * 
     */
    public static function getRealIP() {

        if ($_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $client_ip =
                    (!empty($_SERVER['REMOTE_ADDR']) ) ?
                    $_SERVER['REMOTE_ADDR'] :
                    ( (!empty($_ENV['REMOTE_ADDR']) ) ?
                            $_ENV['REMOTE_ADDR'] :
                            'unknown' );

            // los proxys van añadiendo al final de esta cabecera
            // las direcciones ip que van 'ocultando'. Para localizar la ip real
            // del usuario se comienza a mirar por el principio hasta encontrar 
            // una dirección ip que no sea del rango privado. En caso de no 
            // encontrarse ninguna se toma como valor el REMOTE_ADDR

            $entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);

            reset($entries);
            while (list(, $entry) = each($entries)) {
                $entry = trim($entry);
                if (preg_match('/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $entry, $ip_list)) {
                    // http://www.faqs.org/rfcs/rfc1918.html
                    $private_ip = array(
                        '/^0\./',
                        '/^127\.0\.0\.1/',
                        '/^192\.168\..*/',
                        '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                        '/^10\..*/');

                    $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

                    if ($client_ip != $found_ip) {
                        $client_ip = $found_ip;
                        break;
                    }
                }
            }
        } else {
            $client_ip =
                    (!empty($_SERVER['REMOTE_ADDR']) ) ?
                    $_SERVER['REMOTE_ADDR'] :
                    ( (!empty($_ENV['REMOTE_ADDR']) ) ?
                            $_ENV['REMOTE_ADDR'] :
                            'unknown' );
        }

        return $client_ip;
    }

    /**
     * Metodo que se encarga de registrar un error cuando el usuario se intenta logear
     */
    public function registrarError() {

        $varIp = self::getRealIP();

        Sesion::registrar('ipUsuario', $varIp);
    }

    /**
     * cada vez que un usuario intenta un acceso de login fallido este metodo es llamado
     * y se encarga de ir llevando un registro en sesiones php de cuantos intentos fallidos se
     * han realizado por un determinado usuario
     *
     **/
    public function registrarErrorDeUsuario($usuario) {
        global $sql, $sesion_errorUsuario, $textos, $configuracion;

        if ($sql->existeItem('usuarios', 'usuario', $usuario)) {

            $varIp = self::getRealIP();
            $datos = array();
            $datos['ip'] = $varIp;
            $datos['usuario'] = $usuario;
            if (isset($sesion_errorUsuario) && $sesion_errorUsuario['usuario'] == $usuario) {
                $datos['intentos'] = $sesion_errorUsuario['intentos'] + 1;
            } else {
                $datos['intentos'] = 1;
            }

            Sesion::registrar('errorUsuario', $datos);

            if (isset($sesion_errorUsuario) && $sesion_errorUsuario['intentos'] >= 3 && $sesion_errorUsuario['usuario'] == $usuario) {

                $datosUser['bloqueado'] = '1';
                $consulta = $sql->modificar('usuarios', $datosUser, 'usuario = "' . $usuario . '"');

                $contrasena = substr(md5(uniqid(rand(), true)), 0, 8);
                $datosContrasena['contrasena'] = md5($contrasena);
                $consulta = $sql->modificar('usuarios', $datosContrasena, 'usuario = "' . $usuario . '"');

                if ($consulta) {
                    $user = new Usuario($usuario);
                    $url = $configuracion['SERVIDOR']['principal'];
                    $mensaje = str_replace('%1', $user->persona->nombre, $textos->id('CONTENIDO_MENSAJE_USUARIO_BLOQUEADO'));
                    $mensaje = str_replace('%2', $url, $mensaje);
                    $mensaje = str_replace('%3', $contrasena, $mensaje);
                    Servidor::enviarCorreo($user->persona->correo, $textos->id('ASUNTO_MENSAJE_USUARIO_BLOQUEADO'), $mensaje, $user->persona->nombreCompleto);
                }
            }
        }//fin del if existe el usuario
    }

    /**
     * Funcion que crea en codigo html el bloque izquierdo con la informacion del usuario (de la pagina principal del perfil del usuario), esto con el fin
     * de devolverlo como respuesta via ajax en el caso de que el usuario actualize su información
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @param type $id
     * @return type 
     * 
     */
    public static function modificarUsuarioAjax($id) {
        global $configuracion, $sql, $textos, $sesion_usuarioSesion;

        if (!isset($id)) {
            return NULL;
        }

        $usuario = new Usuario($id);

        $botones = HTML::nuevoBotonModificarUsuarioInterno($id, $usuario->urlBase);
        $botones = HTML::contenedor($botones, 'botonesInternos', 'botonesInternos');
        $contenidoUsuario = $botones;
        $img = HTML::imagen($usuario->persona->imagenPrincipal, 'imagenUsuario');
        $contenidoUsuario .= HTML::enlace($img, $usuario->persona->imagenPrincipal, '', '', array('rel' => 'prettyPhoto[""]'));
        $contenidoUsuario .= HTML::parrafo($textos->id('NOMBRE_COMPLETO'), 'negrilla');
        $contenidoUsuario .= HTML::parrafo($usuario->persona->nombreCompleto, 'justificado margenInferior ancho200px');
        $imagen = HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'warning_blue.png');

        if ($usuario->persona->ciudadResidencia) {
            $contenidoUsuario .= HTML::parrafo($textos->id('CIUDAD'), 'negrilla');
            $contenidoUsuario .= HTML::parrafo($usuario->persona->ciudadResidencia . ', ' . $usuario->persona->paisResidencia, 'justificado margenInferior ancho250px');
        } else {
            if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) {
                $contenidoUsuario .= HTML::parrafo($textos->id('SIN_CIUDAD') . $imagen, 'negrilla');
                $contenidoUsuario .= HTML::botonImagenAjax($textos->id('POR_FAVOR_ESCOJA_CIUDAD'), 'estiloEnlace justificado margenInferior ancho250px', 'iLikeIt', '', '/ajax/users/edit', array('id' => $usuario->id), '');
            }
        }

        if ($usuario->centro) {
            $contenidoUsuario .= HTML::parrafo($textos->id('CENTRO_BINACIONAL'), 'negrilla');
            $contenidoUsuario .= HTML::parrafo($usuario->centro, 'justificado margenInferior ancho250px');
        } else {
            if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) {
                $contenidoUsuario .= HTML::parrafo($textos->id('SIN_CENTRO_BINACIONAL') . $imagen, 'negrilla');
                $contenidoUsuario .= HTML::botonImagenAjax($textos->id('POR_FAVOR_ESCOJA_CENTRO'), 'estiloEnlace justificado margenInferior ancho250px', 'iLikeIt', '', '/ajax/users/edit', array('id' => $usuario->id), '');
            }
        }

        if ($usuario->persona->descripcion) {
            $contenidoUsuario .= HTML::parrafo($textos->id('ACERCA_DE_USUARIO'), 'negrilla');
            $contenidoUsuario .= HTML::parrafo($usuario->persona->descripcion, 'justificado margenInferior ancho250px');
        }

        if ($usuario->tipo) {
            $centroAdmin = '';
            if ($usuario->idTipo == 2) {
                $adminCentro = $sql->obtenerValor("admin_centro", "id_centro", "id_usuario = '" . $usuario->id . "'");
                $clase = "";
                if ($adminCentro) {
                    $centroAdmin = $sql->obtenerValor("lista_centros", "nombre", "id = '" . $adminCentro . "'");
                    $centroAdmin = ' of ' . $centroAdmin;
                }
            }


            $contenidoUsuario .= HTML::parrafo($textos->id('PERFIL'), 'negrilla');
            $contenidoUsuario .= HTML::parrafo($usuario->tipo . $centroAdmin, 'justificado margenInferior ancho250px');
        }

        if ($usuario->idTipo == 105) {
            $contenidoUsuario .= HTML::parrafo(HTML::enlace('Educational advisors profile page', 'http://www.ablaonline.org/bnc/34'), 'medioMargenIzquierda bordeInferior mitadEspacioInferior');
        }

        if ($usuario->idTipo == 101) {
            $contenidoUsuario .= HTML::parrafo(HTML::enlace('Information Resource Center profile page', 'http://www.ablaonline.org/bnc/33'), 'medioMargenIzquierda bordeInferior mitadEspacioInferior');
        }

        if ($usuario->idCentro == 0) {
            $contenidoUsuario .= HTML::parrafo($textos->id("ESCOGER_CENTRO_PARA_CONTACTAR_WEBMASTER"), 'negrita margenSuperior bordeInferior mitadEspacioInferior');
        } else {
            $enlaceContacto = HTML::frase($textos->id('AQUI'), 'estiloEnlace enlaceAjax', '', array('alt' => '/ajax/users/contactBncWebmaster'));
            $contenidoUsuario .= HTML::parrafo(str_replace('%1', $enlaceContacto, $textos->id("TEXTO_EXPLICACION_CONTACTAR_WEBMASTER")), ' margenSuperior bordeInferior mitadEspacioInferior');
        }

        $contenidoUsuario = HTML::contenedor($contenidoUsuario, '', 'contenidoUsuario');
        $contenido = HTML::bloque('usuario_' . $usuario->id, $usuario->sobrenombre, $contenidoUsuario);

        return $contenido;
    }

    public static function modificarImagenDerechaUsuario() {
        global $sesion_usuarioSesion;

        $contenido = HTML::enlace(HTML::imagen($sesion_usuarioSesion->persona->imagenPrincipal, 'imagenPrincipalUsuario margenInferior'), $sesion_usuarioSesion->url);

        return $contenido;
    }

    /**
     * Metodo llamado por los metodos generar tabla de las clases, el cual se encarga de generar el codigo html de 
     * una tabla incluyendo la informacion que le fue suministrada en los parametros. 
     * Este metodo es llamado en los modulos  introducidos recientemente y genera la grilla con 
     * el paginador, el buscador, los botones de ordenamiento por columnas, el boton derecho de opciones y el icono
     *  para la informacion de ayuda. Se debe de tener en cuenta que cuando se llama este metodo pasandole los parametros
     * adecuados el genera la tabla pero sin ninguna funcionalidad (busqueda, ordenamiento, paginacion), el codigo para
     * estas funcionalidades se debe agregar en el archivo ajax del modulo, la ventaja es que de un modulo a otro estos
     * metodos son casi exactamente iguales, asi que solo seria copiar, pegar y reemplazar algunos alias de tablas y 
     * nombres de clases.
     *
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global type $modulo
     * @param type $arregloItems
     * @param type $datosTabla
     * @param type $rutaPaginador
     * @param type $datosPaginacion
     * @return type 
     */
    static function generarTablaRegistros($arregloItems, $datosTabla, $rutaPaginador, $datosPaginacion = NULL, $rutaConsulta = NULL) {
        global $textos;

        $fila = 0;
        $columnas = array(); //columnas que va a tener la tabla
        $celdas = array(); //celdas que va a tener la tabla  

        $ids = array(); //identificador de cada uno de los registros
        $arregloCeldas = array();
        $item = ''; //codigo html final a devolver por el metodo    

        if (isset($datosTabla) && is_array($datosTabla)) {//verifico que llegue un arreglo con los nombres de las columnas y con que celdas(posiciones del objeto en el array devuelto por el listar) se van a recorrer
            foreach ($datosTabla as $columna => $celda) {//recorro el arreglo
                $columnas[] = $columna; //agrego a cada uno su valor correspondiente            
                $celdas[] = $celda;
            }

            foreach ($arregloItems as $elemento) {//recorro el arreglo de registros que me envian
                if ($elemento->id != 0) {
                    $fila++;

                    $filas = array(); //filas que va a aparecer en la tabla

                    foreach ($celdas as $registro) {//armo las celdas que se van a pasar a la tabla para ser generada
                        $registro = explode('|', $registro); //en celdas viene el nombre usado en el objeto, y el nombre del mismo usado para la consulta ej: nombreItem | i.nombre
                        $filas[] = HTML::parrafo($elemento->$registro[0], 'centrado'); //por eso accedo a la posicion 0 que es donde viene el nombre del objeto
                    }

                    $arregloCeldas[$fila] = $filas;
                    $ids[] = 'tr_' . $elemento->id;
                }
            }//fin del foreach
//        print_r($arregloItems);
        }//fin del if(isset($datosTabla) && is_array($datosTabla))

        $paginador = '';
        $pag = '';
        if (isset($datosPaginacion) && is_array($datosPaginacion)) {
            //$datosPaginacion =                  0=>totalRegistrosActivos  1=>registroInicial   2=>registros         3=>pagina
            $paginador = Recursos::mostrarPaginadorPeque($datosPaginacion[0], $datosPaginacion[1], $datosPaginacion[2], $datosPaginacion[3]);
            $pag = $datosPaginacion[3];
        }

        $estilosColumnas = array('ancho25porCiento', 'ancho25porCiento', 'ancho25porCiento', 'ancho25porCiento');
        $opciones = array('cellpadding' => '3', 'border' => '2', 'cellspacing' => '1', 'ruta_paginador' => $rutaPaginador, 'ruta' => $rutaConsulta, 'pagina' => $pag);
        $item .= HTML::tablaGrilla($columnas, $arregloCeldas, 'tablaRegistros', 'tablaRegistros', $estilosColumnas, 'filasTabla', $opciones, $ids, $celdas);

        $ayuda = HTML::cargarIconoAyuda($textos->id('AYUDA_MODULO'));


        $item .= HTML::contenedor($ayuda . $paginador, 'contenedorInferiorTabla', 'contenedorInferiorTabla');

        $item = HTML::contenedor($item, 'contenedorTablaRegistros', 'contenedorTablaRegistros');


        return $item;
    }

    /**
     * metodo encargado de generar el codigo html de un paginador, pero mas pequeño que el paginador normal
     * utilizado en los listados de los metodos
     *
     */
    public static function mostrarPaginadorPeque($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas = NULL) {
        global $textos;

        if ($totalRegistrosActivos > $registros) {
            $totalPaginas = ceil($totalRegistrosActivos / $registros);
            $botonPrimera = $botonUltima = $botonAnterior = $botonSiguiente = '';

            if ($pagina > 1) {
                $botonPrimera = ''; //HTML::campoOculto('pagina', 1);
                $botonPrimera .= HTML::contenedor($textos->id('PRIMERA_PAGINA'), 'botonPrimeraPagina botonPaginacion', 'botonPrimeraPagina', array('pagina' => (1)));

                $botonAnterior = ''; //HTML::campoOculto('pagina', $pagina-1);
                $botonAnterior .= HTML::contenedor($textos->id('PAGINA_ANTERIOR'), 'botonAtrasPagina botonPaginacion', 'botonAtrasPagina', array('pagina' => ($pagina - 1)));
            }

            if ($pagina < $totalPaginas) {
                $botonSiguiente = ''; //HTML::campoOculto('pagina', $pagina+1);
                $botonSiguiente .= HTML::contenedor($textos->id('PAGINA_SIGUIENTE'), 'botonSiguientePagina botonPaginacion', 'botonSiguientePagina', array('pagina' => ($pagina + 1)));

                $botonUltima = ''; //HTML::campoOculto('pagina', $totalPaginas);
                $botonUltima .= HTML::contenedor($textos->id('ULTIMA_PAGINA'), 'botonUltimaPagina botonPaginacion', 'botonUltimaPagina', array('pagina' => ($totalPaginas)));
            }

            $infoPaginacion = self::contarPaginacion($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas);
        }

        $paginador = HTML::contenedor($botonPrimera . $botonAnterior . $botonSiguiente . $botonUltima, 'paginadorTabla', 'paginadorTabla');
        $paginador .= HTML::contenedor($infoPaginacion, 'informacionPaginacion');

        return $paginador;
    }

//fin del metodo mostrar paginador
}

?>
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

/**
 *
 * Clase para la gestión del comportamiento del servidor web
 *
 * */
class Servidor {

    private static $instancia = NULL;
    public static $cliente;
    public static $proxy;

    /**
     * Nombre completo para mostrar como remitente del correo electrónico
     * @var cadena
     */
    private static $nombreRemitenteCorreo;

    /**
     * Dirección para mostrar como remitente del correo electrónico
     * @var cadena
     */
    private static $direccionRemitenteCorreo;

    private function __construct() {
        
    }

    /**
     *
     * Determinar si una cadena de texto representa una dirección IP válida
     *
     * @param cadena $cadena    Dirección IP a validar
     * @return                  lógico
     *
     */
    public static function iniciar() {

        if (self::$instancia == NULL) {
            self::$instancia = new Servidor;
        }

        return self::$instancia;
    }

    /* Enviar código necesario para que la pagina no sea almacenada en caché por el cliente o por un servidor proxy 
      public static function evitarCache() {
      header("Expires: ".date("D, d M Y H:i:s", 0)." GMT");
      header("Last-Modified: ".date("D, d M Y H:i:s")." GMT");
      header("Cache-Control: no-store, no-cache, must-revalidate");
      header("Cache-Control: post-check=0, pre-check=0", FALSE);
      header("Pragma: no-cache");
      }
     * * */



    /*     * * Codificar una cadena o arreglo de cadenas para enviar en formato JSON ** */

    public static function exportarVariables() {

        if (empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            self::$cliente = $_SERVER["REMOTE_ADDR"];
            self::$proxy = "";
        } else {
            self::$cliente = $_SERVER["HTTP_X_FORWARDED_FOR"];
            self::$proxy = $_SERVER["REMOTE_ADDR"];
        }

        if (isset($_POST)) {

            foreach ($_POST as $variable => $valor) {

                if (!get_magic_quotes_gpc()) {

                    if (!is_array($valor)) {
                        $valor = Variable::codificarCadena($valor);
                    } else {
                        $valor = Variable::codificarArreglo($valor);
                    }
                }

                $nombre = "forma_$variable";
                global $$nombre;
                $$nombre = $valor;
            }
        }

        if (isset($_GET)) {

            foreach ($_GET as $variable => $valor) {

                if (!get_magic_quotes_gpc()) {

                    if (!is_array($valor)) {
                        $valor = addslashes(Variable::codificarCadena($valor));
                    } else {
                        $valor = Variable::codificarArreglo($valor);
                    }
                }

                $nombre = "url_$variable";
                global $$nombre;
                $$nombre = $valor;
            }
        }



        if (isset($_FILES)) {

            foreach ($_FILES as $variable => $valor) {

                if (!get_magic_quotes_gpc()) {

                    if (!is_array($valor)) {
                        $valor = addslashes(Variable::codificarCadena($valor));
                    } else {
                        $valor = Variable::codificarArreglo($valor);
                    }
                }

                $nombre = "archivo_$variable";
                global $$nombre;
                $$nombre = $valor;
            }
        }



        if (isset($_COOKIES)) {

            foreach ($_COOKIES as $variable => $valor) {

                if (!get_magic_quotes_gpc()) {

                    if (!is_array($valor)) {
                        $valor = addslashes(Variable::codificarCadena($valor));
                    } else {
                        $valor = Variable::codificarArreglo($valor);
                    }
                }

                $nombre = "cookie_$variable";
                global $$nombre;
                $$nombre = $valor;
            }
        }
    }

    /*     * * Codificar una cadena o arreglo de cadenas para enviar en formato JSON ** */

    public static function enviarJSON($datos) {

        if (is_array($datos)) {
            foreach ($datos as $id => $value) {
                if (is_array($value)) {
                    $datos[$id] = array_map("utf8_encode", $datos[$id]);
                } else {
                    $datos[$id] = utf8_encode($datos[$id]);
                }
            }
        } else {
            $datos = utf8_encode($datos);
        }

        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: " . date("r", 0));
        header("Content-type: text/html");
        echo json_encode($datos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /*     * * Codificar una cadena o arreglo de cadenas para enviar en formato JSON ** */

    public static function enviarHTML() {
        Plantilla::generarCodigo();
        header("Cache-control: public, max age=10800");
        $contenido = str_replace(chr(194), " ", Plantilla::$contenido);
        echo $contenido;
    }

    /*     * * Enviar mensaje por correo electrónico ** */

    public static function enviarCorreo($destino, $asunto, $contenido, $nombre = NULL) {
        global $configuracion;

        $envio = NULL;

        self::$nombreRemitenteCorreo = $configuracion["SERVIDOR"]["nombreRemitente"];
        self::$direccionRemitenteCorreo = $configuracion["SERVIDOR"]["correoRemitente"];

        if (isset($destino) && filter_var($destino, FILTER_VALIDATE_EMAIL) && isset($asunto) && isset($contenido)) {

            if (isset($nombre)) {
                $destino = trim($nombre) . " <" . $destino . ">\r\n";
            }

            $cabecera .= "MIME-Version: 1.0\r\n";
            $cabecera .= "Content-type: text/html; charset=" . $configuracion["SERVIDOR"]["codificacion"] . "\r\n";
            $cabecera = "From: " . self::$nombreRemitenteCorreo . " <" . self::$direccionRemitenteCorreo . ">\r\n";
            $cabecera .= "To: $destino\r\n";
            $envio = mail("", trim($asunto), $contenido, $cabecera, "-f" . self::$direccionRemitenteCorreo);
        }

        return $envio;
    }

    /*     * * Generar notificación para un usuario  ** */

    public static function notificar($usuario, $mensaje, $variables = array(), $tipo = '') {
        global $sql;

        foreach ($variables as $variable => $valor) {
            $mensaje = preg_replace("/$variable/", $valor, $mensaje);
        }

        $datos = array(
            "id_usuario" => $usuario,
            "fecha" => date("Y-m-d H:i:s"),
            "contenido" => $mensaje,
            "activo" => "1",
            'tipo_notificacion' => $tipo
        );
        $sql->guardarBitacora = false;
        $envio = $sql->insertar("notificaciones", $datos);

        return $envio;
    }

}

?>
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
class Sesion {

    public static $id;
    private static $sql;

    /*     * * Iniciar la sesión ** */

    public static function iniciar() {

        self::$sql = new SQL();

        Sesion::limpiar();

        if (self::$id == "") {
            session_start();
        }

        self::$id = session_id();

        foreach ($_SESSION as $variable => $valor) {
            $nombre = "sesion_" . $variable;
            global $$nombre;
            $$nombre = $valor;
        }
    }

    /*     * * Finalizar la sesión ** */

    public static function terminar() {
        self::destruir(self::$id);
    }

    /*     * * Abrir una sesión ** */

    public static function abrir() {
        return TRUE;
    }

    /*     * * Cerrar una sesión ** */

    public static function cerrar() {
        return TRUE;
    }

    /*     * * Registrar una variable en la sesión ** */

    public static function registrar($variable, $valor = "") {
        global $$variable;

        if (isset($valor)) {
            $$variable = $valor;
        }

        $nombre = "sesion_" . $variable;

        if (isset($$variable)) {
            global $$nombre;

            $$nombre = $$variable;
            $_SESSION["$variable"] = $$variable;
        }
    }

    /*     * * Eliminar una variable de sesión ** */

    public static function borrar($variable) {
        $nombre = "sesion_" . $variable;

        global $$nombre;

        if (isset($$nombre)) {
            unset($$nombre);
            unset($_SESSION["$variable"]);
        }
    }

    /*     * * Leer los datos una sesión ** */

    public static function leer($id) {

        return true;
    }

    /*     * * Escribir los datos de una sesión ** */

    public static function escribir($id, $contenido) {
        global $sesion_usuarioSesion;

        $expiracion = time() + get_cfg_var("session.gc_maxlifetime");

        return true;
    }

    /*     * * Destruir una sesión ** */

    public static function destruir($id) {

        foreach ($_SESSION as $variable => $valor) {
            unset($_SESSION[$variable]);
        }

        unset($_SESSION);

        return true;
    }

    /**
     * Eliminar las sesiones expiradas 
     *
     * */
    public static function limpiar() {

        return true;
    }

}

?>
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.1
 *
 * */
class SQL {

    /**
     * Nombre o dirección IP del servidor de bases de datos MySQL
     * @var cadena
     */
    public $servidor;

    /**
     * Nombre de usuario para la conexión al servidor de bases de datos MySQL
     * @var cadena
     */
    public $usuario;

    /**
     * Contraseña del usuario para la conexión al servidor de bases de datos MySQL
     * @var cadena
     */
    public $contrasena;

    /**
     * Nombre de la base datos para la conexión al servidor de bases de datos MySQL
     * @var cadena
     */
    public $baseDatos;

    /**
     * Prefijo para las tablas y vistas del proyecto en la base de datos MySQL
     * @var cadena
     */
    public $prefijo;

    /**
     * Gestor de la conexión a la base de datos MySQL
     * @var recurso
     */
    public $conexion;

    /**
     * Objeto resultado devuelto al ejecutar un query
     * @var recurso
     */
    public $resultado;

    /**
     * Objeto resultado devuelto al ejecutar un query
     * @var recurso
     */
    public $prefijoDepuracion = '';

    /**
     * Número asignado para el último registro adicionado mediante incremento automático
     * @var entero
     */
    public $ultimoId;

    /**
     * Número de filas devueltas por una consulta
     * @var entero
     */
    public $filasDevueltas;

    /**
     * Número de filas afectadas por una consulta
     * @var recurso
     */
    public $filasAfectadas;

    /**
     * Número de consultas realizadas en cada página generada
     * @var entero
     */
    public $consultas;

    /**
     * Tiempo total empleado para las consultas realizadas (en segundos)
     * @var flotante
     */
    public $tiempo;

    /**
     * Texto de la sentencia ejecutada 
     * @var flotante
     */
    public $sentenciaSql;

    /**
     * Depurar las consultas realizadas en la base de datos MySQL mediante los archivos de registro (logs)
     * @var lógico
     */
    public $depurar = false;

    /**
     * Determina si algunos parametros de la sentencia deben ser guardados en la bitacora
     * @var lógico
     */
    public $guardarBitacora = true;

    /**
     *
     * Inicializar la clase estableciendo una conexión con el servidor de bases de datos MySQL
     *
     * @param cadena $servidor      Nombre o dirección IP del servidor de bases de datos MySQL
     * @param cadena $usuario       Nombre de usuario para la conexión al servidor de bases de datos MySQL
     * @param cadena $contrasena    Contraseña del usuario para la conexión al servidor de bases de datos MySQL
     * @param cadena $nombre        Nombre de la base datos para la conexión al servidor de bases de datos MySQL
     * @return                      recurso
     *
     */
    function __construct($servidor = "", $usuario = "", $contrasena = "", $nombre = "") {
        global $configuracion;

        if (empty($servidor) && empty($usuario) && empty($usuario) && empty($usuario)) {
            $this->servidor = $configuracion["BASEDATOS"]["servidor"];
            $this->usuario = $configuracion["BASEDATOS"]["usuario"];
            $this->contrasena = $configuracion["BASEDATOS"]["contrasena"];
            $this->baseDatos = $configuracion["BASEDATOS"]["nombre"];
            $this->prefijo = $configuracion["BASEDATOS"]["prefijo"];
        } else {
            $this->servidor = $servidor;
            $this->usuario = $usuario;
            $this->contrasena = $contrasena;
            $this->baseDatos = $nombre;
            $this->prefijo = "";
        }

        $this->conectar();
    }

    /**
     *
     * Establecer una conexión con el servidor de bases de datos MySQL
     *
     * @param cadena $servidor      Nombre o dirección IP del servidor de bases de datos MySQL
     * @param cadena $usuario       Nombre de usuario para la conexión al servidor de bases de datos MySQL
     * @param cadena $contrasena    Contraseña del usuario para la conexión al servidor de bases de datos MySQL
     * @param cadena $nombre        Nombre de la base datos para la conexión al servidor de bases de datos MySQL
     * @return                      recurso
     *
     */
    public function conectar() {
        $this->conexion = new mysqli($this->servidor, $this->usuario, $this->contrasena, $this->baseDatos);
        if ($this->conexion->connect_errno) {
            echo "Fallo al conectar a MySQL: (" . $this->conexion->connect_errno . ") " . $this->conexion->connect_error;
        }
        //$this->conexion->set_charset("utf8");

    }

    /**
     *
     * Finalizar una conexión con el servidor de bases de datos MySQL
     *
     * @param recurso $conexion     Gestor de la conexión a la base de datos MySQL
     * @return                      lógico
     *
     */
    public function desconectar($conexion = "") {

        if (empty($conexion)) {
            $cierre = $this->conexion->close();
        } else {
            $cierre = mysqli_close($conexion);
        }
    }

    /**
     *
     * Ejecutar una consulta en el servidor de bases de datos MySQL
     *
     * @param cadena $consulta      Instrucción SQL a ejecutar
     * @return                      recurso
     *
     */
    public function ejecutar($consulta) {
        global $modulo, $sesion_usuarioSesion;

        $this->consultas++;
        $this->filasDevueltas = NULL;
        $this->filasAfectadas = NULL;
        $horaInicio = microtime(true);
        $this->resultado = $this->conexion->query($consulta);
        $horaFinalizacion = microtime(true);
        $this->tiempo += round($horaFinalizacion - $horaInicio, 4);

        if ((!empty($this->conexion->error)) || $this->depurar) {
            openlog("FOLCS", LOG_PID, LOG_LOCAL0);
            $log = syslog(LOG_DEBUG, $this->prefijoDepuracion . ' ' . $consulta);

            if (!empty($this->conexion->error)) {
                $log = syslog(LOG_DEBUG, "Error de mierda: " . $this->conexion->error);
            }

            $this->depurar = false;
        }


        if (preg_match("/^(SELECT|SHOW)/", $consulta) && !$this->conexion->errno) {
            $this->filasDevueltas = $this->resultado->num_rows;
        } else {
            $this->filasAfectadas = $this->conexion->affected_rows;

            //Funciones para guardar registro de actividades en la bitacora
            if ($this->guardarBitacora) {
                $tipo = '';


                if (isset($sesion_usuarioSesion) && !empty($sesion_usuarioSesion->usuario)) {
                    $username = $sesion_usuarioSesion->usuario;
                } else {
                    $username = 'sin sesion';
                }
                if (preg_match("/INSERT/", $consulta)) {
                    $tipo = 'INSERT';
                    $this->ultimoId = $this->conexion->insert_id;
                } else if (preg_match("/DELETE/", $consulta)) {
                    $tipo = 'DELETE';
                } else if (preg_match("/UPDATE/", $consulta)) {
                    $tipo = 'UPDATE';
                }

                $sentencia = "INSERT INTO folcs_bitacora (usuario, ip, tipo, consulta, fecha, modulo) VALUES ('$username', '" . Recursos::getRealIP() . "', '$tipo', '" . addslashes($consulta) . "', '" . date('Y-m-d H:i:s') . "', '$modulo->nombre')";
                $this->conexion->query($sentencia);
            }
            //se pone nuevamente en true, poque pudo haber sido puesto en false en algun metodo de alguna clase
            $this->guardarBitacora = true;
            $this->prefijoDepuracion = '';
        }

        return $this->resultado;
    }

    /**
     *
     * Convertir el recurso resultante de una consulta en un objeto
     *
     * @param recurso $resultado    Recurso resultante de una consulta
     * @return                      objeto
     *
     */
    public function filaEnObjeto($resultado = NULL) {
        if (empty($resultado)) {
            $fila = $this->resultado->fetch_object();
        } else {
            $fila = mysqli_fetch_object($resultado);
        }

        return $fila;
    }

    /**
     *
     * Convertir el recurso resultante de una consulta en un arreglo
     *
     * @param recurso $resultado    Recurso resultante de una consulta
     * @return                      arreglo
     *
     */
    public function filaEnArreglo($resultado = NULL) {
        if (empty($resultado)) {
            $fila = $this->resultado->fetch_array();
        } else {
            $fila = mysqli_fetch_array($resultado);
        }
        return $fila;
    }

    /**
     *
     * Convertir el recurso resultante de una consulta en un arreglo ASOCIATIVO
     *
     * @param recurso $resultado    Recurso resultante de una consulta
     * @return                      arreglo
     *
     */
    public function filaEnArregloAsoc($resultado = NULL) {
        if (empty($resultado)) {
            $fila = $this->resultado->fetch_assoc();
        } else {
            $fila = mysqli_fetch_assoc($resultado);
        }
        return $fila;
    }

    /**
     *
     * Obtener una lista con los nombres de las columnas o campos de una tabla
     *
     * @param cadena $tabla         Nombre de la tabla
     * @return                      arreglo
     *
     */
    public function obtenerColumnas($tabla) {
        $tabla = $this->prefijo . $tabla;
        $columnas = array();
        $resultado = $this->ejecutar("SHOW COLUMNS FROM $tabla");

        while ($datos = $this->filaEnArreglo($resultado)) {
            $columnas[] = $datos[0];
        }

        return $columnas;
    }

    /**
     *
     * Seleccionar datos de una o varias tablas del servidor de bases de datos MySQL
     *
     * @return recurso
     *
     */
    public function seleccionar($tablas, $columnas, $condicion = "", $agrupamiento = "", $ordenamiento = "", $filaInicial = NULL, $numeroFilas = NULL) {
        $listaColumnas = array();
        $listaTablas = array();
        $limite = "";

        foreach ($columnas as $alias => $columna) {

            if (preg_match("/(^[a-zA-z]+[a-zA-Z0-9]*)/", $alias)) {
                $alias = " AS $alias";
            } else {
                $alias = "";
            }

            $listaColumnas[] = $columna . $alias;
        }

        $columnas = implode(", ", $listaColumnas);

        foreach ($tablas as $alias => $tabla) {

            if (preg_match("/(^[a-zA-z]+[a-zA-Z0-9]*)/", $alias)) {
                $alias = " AS $alias";
            } else {
                $alias = "";
            }

            $tabla = $this->prefijo . $tabla;
            $listaTablas[] = $tabla . $alias;
        }

        $tablas = implode(", ", $listaTablas);

        if (!empty($condicion)) {
            $condicion = " WHERE $condicion";
        }

        if (!empty($agrupamiento)) {
            $agrupamiento = " GROUP BY $agrupamiento";
        }

        if (!empty($ordenamiento)) {
            $ordenamiento = " ORDER BY $ordenamiento";
        }

        if (is_int($numeroFilas) && $numeroFilas > 0) {
            $limite = " LIMIT ";

            if (is_int($filaInicial) && $filaInicial >= 0) {
                $limite .= "$filaInicial, ";
            }

            $limite .= $numeroFilas;
        }

        $tablas = implode(", ", $listaTablas);
        $sentencia = "SELECT $columnas FROM $tablas" . $condicion . $agrupamiento . $ordenamiento . $limite;

        $this->sentenciaSql = $sentencia;

        return $this->ejecutar($sentencia);
    }

    /*     * * Insertar datos en la tabla ** */

    public function insertar($tabla, $datos) {
        $tabla = $this->prefijo . $tabla;

        if (is_array($datos) && count($datos) > 0) {

            $campos = array();
            $valores = array();

            foreach ($datos as $campo => $valor) {

                if ($valor != "") {
                    $campos[] = $campo;

                    if (Variable::contieneUTF8($valor)) {
                        $valor = Variable::codificarCadena($valor);
                    }

                    $valores[] = "'$valor'";
                }
            }

            $campos = implode(",", $campos);
            $valores = implode(",", $valores);
            $sentencia = "INSERT INTO $tabla ($campos) VALUES ($valores)";
        }

        $resultado = $this->ejecutar($sentencia);


        return $resultado;
    }

    /*     * * Reemplazar datos existentes en la tabla o insertarlos si no existen ** */

    public function reemplazar($tabla, $datos) {

        $tabla = $this->prefijo . $tabla;

        if (is_array($datos) && count($datos) > 0) {
            $campos = array();
            $valores = array();

            foreach ($datos as $campo => $valor) {
                $campos[] = $campo;

                if (Variable::contieneUTF8($valor)) {
                    $valor = Variable::codificarCadena($valor);
                }

                $valores[] = "'$valor'";
            }

            $campos = implode(", ", $campos);
            $valores = implode(", ", $valores);
            $sentencia = "REPLACE INTO $tabla ($campos) VALUES ($valores)";
        }

        $resultado = $this->ejecutar($sentencia);

        return $resultado;
    }

    /*     * * Modificar datos existentes en la tabla de acuerdo con una condición ** */

    public function modificar($tabla, $datos, $condicion) {
        $tabla = $this->prefijo . $tabla;

        if (is_array($datos) && count($datos) > 0) {
            $campos = array();
            $valores = array();

            foreach ($datos as $campo => $valor) {

                if ($valor != "") {

                    if (Variable::contieneUTF8($valor)) {
                        $valor = Variable::codificarCadena($valor);
                    }

                    $valores[] = "$campo='$valor'";
                    $campos["$campo"] = "'$valor'";
                } else {
                    $valores[] = "$campo=NULL";
                    $campos["$campo"] = "NULL";
                }
            }

            $valores = implode(", ", $valores);
            $sentencia = "UPDATE $tabla SET $valores WHERE $condicion";
        }

        $resultado = $this->ejecutar($sentencia);

        return $resultado;
    }

    /*     * * Eliminar datos de una tabla que coincidan con una condición  ** */

    public function eliminar($tabla, $condicion) {
        $tabla = $this->prefijo . $tabla;
        $sentencia = "DELETE FROM $tabla WHERE $condicion";

        $resultado = $this->ejecutar($sentencia);

        return $resultado;
    }

    /*     * * Insertar datos en la tabla de imágenes o de archivos adjuntos ** */

    public function insertarArchivo($tabla, $datos) {

        $tabla = $this->prefijo . $tabla;

        if (is_array($datos) && count($datos) > 0) {

            foreach ($datos as $campo => $valor) {

                if ($valor != "") {
                    $campos[] = $campo;
                    $valores[] = "'" . mysql_real_escape_string($valor) . "'";
                }
            }

            $campos = implode(",", $campos);
            $valores = implode(",", $valores);
            $sentencia = "INSERT INTO $tabla ($campos) VALUES ($valores)";
        }

        $resultado = $this->ejecutar($sentencia);

        return $resultado;
    }

    /*     * * Verificar si un registro con un valor específico existe en una tabla ** */

    /**
     *
     * @param type $tabla
     * @param type $columna
     * @param type $valor
     * @param type $condicionExtra
     * @return type boolean
     */
    public function existeItem($tabla, $columna, $valor, $condicionExtra = "") {
        $tablas = array($tabla);
        $columnas = array($columna);
        $condicion = "$columna = '$valor'";

        if (!empty($condicionExtra)) {
            $condicion .= " AND $condicionExtra";
        }

        $this->seleccionar($tablas, $columnas, $condicion);

        if ($this->filasDevueltas) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*     * * Obtener el valor de un campo en una tabla cuyo registro (único) coincida con una condición dada ** */

    public function obtenerValor($tabla, $columna, $condicion) {
        $tablas = array($tabla);
        $columnas = array($columna);
        //$this->depurar = true;
        $this->seleccionar($tablas, $columnas, $condicion);

        if ($this->filasDevueltas == 1) {
            $datos = $this->filaEnObjeto();
            $valor = $datos->$columna;
            return $valor;
        } else {
            return FALSE;
        }
    }

    /*     * * Realizar búsqueda y devolver filas coincidentes ???** */

    public function evaluarBusqueda($vistaBuscador, $vistaMenu) {
        global $componente, $url_buscar, $url_expresion, $sesion_expresion, $sesion_origenExpresion;

        $tabla = $this->prefijo . $vistaBuscador;
        $camposBuscador = $this->obtenerColumnas($vistaBuscador);
        $camposMenu = $this->obtenerColumnas($vistaMenu);
        $campoClave = $camposMenu[0];
        $condicionFinal = "$campoClave IS NOT NULL";

        /*         * * Verificar si la solicitud proviene del formulario de búsqueda ** */
        if (isset($url_buscar)) {
            if (!empty($url_expresion)) {
                Sesion::registrar("expresion", $url_expresion);
                Sesion::registrar("origenExpresion", $componente->id);
            } else {
                Sesion::borrar("expresion");
                unset($sesion_expresion);
                Sesion::borrar("origenExpresion");
                unset($sesion_origenExpresion);
            }
        } else {
            $condicion = "";
        }

        /*         * * Verificar si se está en medio de de una búusqueda ** */
        if (!empty($sesion_expresion) && ($sesion_origenExpresion == $componente->id)) {
            $expresion = Texto::expresionRegular($sesion_expresion);
            $campoInicial = true;
            $listaCampos = array();

            foreach ($camposBuscador as $campo) {
                if (!$campoInicial) {
                    $listaCampos[] = "$tabla.$campo REGEXP '$expresion'";
                }

                $campoInicial = false;
            }

            $condicion = "(" . implode(" OR ", $listaCampos) . ")";
            $tablas = array($vistaBuscador);
            $columnas = array($camposBuscador[0]);
            $consulta = $this->seleccionar($tablas, $columnas, $condicion);

            if ($this->filasDevueltas) {
                $lista = array();

                while ($datos = $this->filaEnObjeto($consulta)) {
                    $lista[] = $datos->id;
                }

                $condicionFinal = "$campoClave IN (" . implode(",", $lista) . ")";
            } else {
                $condicionFinal = "$campoClave IN (NULL)";
            }
        } else {
            Sesion::borrar("expresion");
            unset($sesion_expresion);
            Sesion::borrar("origenExpresion");
            unset($sesion_origenExpresion);
        }

        return $condicionFinal;
    }

    /*     * * Devolver lista de elementos que coincidan con la búsqueda parcial del usuario para autocompletar ** */

    public function datosAutoCompletar($tabla, $patron) {
        $columnas = $this->obtenerColumnas($tabla);
        $primera = true;
        $lista = array();
        $patron = Texto::expresionRegular($patron, false);

        foreach ($columnas as $columna) {

            if ($primera) {
                $primera = false;
                continue;
            }

            $consulta = $this->seleccionar(array($tabla), array($columna), "CAST($columna AS CHAR) REGEXP '$patron'");

            while ($datos = $this->filaEnArreglo($consulta)) {
                $lista[] = $datos[0];
            }
        }
        natsort($lista);
        $lista = implode("\n", array_unique($lista));
        return $lista;
    }

    /*     * * Devuelve una condicion para el orden de presentacion de los datos ** */

    public function ordenColumnas($columna = "") {
        global $url_orden, $sesion_columnaOrdenamiento, $sesion_origenOrdenamiento, $sesion_sentidoOrdenamiento, $componente;

        if (empty($columna)) {
            $columna = "id";
        }

        $ordenamiento = "";

        if (!empty($url_orden)) {

            if (empty($sesion_origenOrdenamiento) || ($sesion_origenOrdenamiento != $componente->id)) {
                Sesion::registrar("origenOrdenamiento", $componente->id);
            }

            if (empty($sesion_sentidoOrdenamiento)) {
                Sesion::registrar("sentidoOrdenamiento", "DESC");
            }

            if ($sesion_sentidoOrdenamiento == "DESC") {
                Sesion::registrar("sentidoOrdenamiento", "ASC");
            } else {
                Sesion::registrar("sentidoOrdenamiento", "DESC");
            }

            Sesion::registrar("columnaOrdenamiento", $url_orden);
            $ordenamiento = "$sesion_columnaOrdenamiento $sesion_sentidoOrdenamiento";
        } else {
            if (empty($sesion_origenOrdenamiento) || ($sesion_origenOrdenamiento != $componente->id)) {
                $ordenamiento = "$columna";
            } else {
                if (empty($sesion_columnaOrdenamiento)) {
                    $ordenamiento = "$columna";
                } else {
                    $ordenamiento = "$sesion_columnaOrdenamiento $sesion_sentidoOrdenamiento";
                }
            }
        }

        return $ordenamiento;
    }

}

?>
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
class Texto {

    /**
     * Indicador del estado de carga de los textos generales
     * @var lógico
     */
    public $generales;

    /**
     * Lista de módulos para los cuales ya se han cargado los textos
     * @var arreglo
     */
    public $modulos;

    /**
     *
     * Inicializar el objeto con el contenido de los textos para el módulo especificado
     *
     * @param cadena $modulo    Nombre único del módulo en la base de datos
     *
     */
    function __construct($modulo = NULL) {
        global $configuracion, $sesion_idioma, $textos;

        if (empty($textos)) {
            $textos = array();
        }

        if (!$this->generales) {
            $archivo = $configuracion["RUTAS"]["idiomas"] . "/" . $sesion_idioma . "/" . $configuracion["RUTAS"]["archivoGeneral"] . ".php";

            if (file_exists($archivo) && is_readable($archivo)) {
                require_once $archivo;
            }

            foreach ($textos as $llave => $texto) {
                $this->{$llave} = $texto;
            }

            $this->generales = true;
        }

        if (!$this->modulos[$modulo]) {
            if (!empty($modulo)) {
                $archivo = $configuracion["RUTAS"]["idiomas"] . "/" . $sesion_idioma . "/" . strtolower($modulo) . ".php";

                if (file_exists($archivo) && is_readable($archivo)) {
                    require_once $archivo;
                }

                foreach ($textos as $llave => $texto) {
                    $this->{$llave} = $texto;
                }
            }

            $this->modulos[$modulo] = true;
        }
    }

    /**
     *
     * Devuelve el texto asociado a la llave indicada
     *
     * @param  cadena $llave    Llave asociada al texto que se debe mostrar
     * @return cadena
     *
     */
    function id($llave) {

        if (isset($this->{$llave})) {
            return $this->{$llave};
        } else {
            return $llave;
        }
    }

}

?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 * */

/**
 * Gestión de variables para su validación y/o conversión
 * */
class Variable {

    /**
     * Determinar si una cadena de texto representa una dirección IP válida
     * @param cadena $cadena    Dirección IP a validar
     * @return                  lógico
     */
    public static function IPValida($cadena) {
        return filter_var($cadena, FILTER_VALIDATE_IP);
    }

    /**
     * Determinar si una cadena de texto representa una dirección de Internet (URL) válida
     * @param cadena $cadena    Dirección (URL) a validar
     * @return                  lógico
     */
    public static function URLValida($cadena) {
        return filter_var($cadena, FILTER_VALIDATE_URL);
    }

    /**
     * Determinar si una cadena de texto representa una dirección de correo electrónico válida
     * @param cadena $cadena    Dirección de correo electrónico a validar
     * @return                  lógico
     */
    public static function correoValido($cadena) {
        return filter_var($cadena, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Determinar si una cadena de texto contiene caracteres en codificación UTF-8
     * @param cadena $cadena    Cadena de texto a validar
     * @return                  lógico
     */
    public static function contieneUTF8($cadena) {

        $limite = 1000;

        if (is_string($cadena) && strlen($cadena) > $limite) {
            $subcadenas = ceil(strlen($cadena) / $limite);

            for ($i = 0; $i < $subcadenas; $i++) {
                $subcadena = substr($cadena, $i * $limite, $limite - 1);
                $busqueda = preg_match('%^(?:
                    [\x09\x0A\x0D\x20-\x7E]              # ASCII
                    | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                    |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
                    | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                    |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
                    |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
                    | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                    |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
                )*$%xs', $subcadena);

                if ($busqueda) {
                    return true;
                }
            }
        } else {
            // Basada en http://w3.org/International/questions/qa-forms-utf-8.html
            return preg_match('%^(?:
                [\x09\x0A\x0D\x20-\x7E]              # ASCII
                | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
                | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
                |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
                | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
            )*$%xs', $cadena);
        }


        return false;
    }

    /**
     * Convertir una cadena con los caracteres codificados ISO-8859-1 con UTF-8 a ISO-8859-1
     * @param cadena $cadena    Cadena a convertir
     * @return                  Cadena convertida
     */
    public static function codificarCadena($cadena) {
        global $configuracion;

        if (!preg_match("/UTF/i", $configuracion['SERVIDOR']['codificacion']) && self::contieneUTF8($cadena)) {
            $cadena = utf8_decode($cadena);
        }

        return $cadena;
    }

    /**
     * Convertir los elementos de un arreglo con los caracteres codificados ISO-8859-1 con UTF-8 a ISO-8859-1
     * @param cadena $cadena    Cadena a convertir
     * @return                  Cadena convertida
     */
    public static function codificarArreglo($arreglo) {
        global $configuracion;

        $respuesta = array();

        if (!preg_match("/UTF/i", $configuracion['SERVIDOR']['codificacion'])) {

            foreach ($arreglo as $indice => $valor) {

                if (!is_array($valor)) {
                    $respuesta[$indice] = self::codificarCadena($valor);
                } else {
                    $respuesta[$indice] = self::codificarArreglo($valor);
                }
            }
        } else {
            $respuesta = $arreglo;
        }

        return $respuesta;
    }

    /* Funcion que se encarga de filtrar cadenas de caracteres */

    public static function filtrarTagsInseguros($texto) {
        $arreglo = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus',
            'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect',
            'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave',
            'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress',
            'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel',
            'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete',
            'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload', 'script', 'iframe');

        $texto = Variable::strip_selected_tags($texto, $arreglo);
        return $texto;
    }

    /**
     * Funciona como la funcion strip_tags,pero solo remueve los tags seleccionados.
     * Example:
     *     strip_selected_tags('<b>Persona:</b> <strong>humano</strong>', 'strong') => <b>Persona:</b> Humano
     */
    public static function strip_selected_tags($text, $tags = array()) {
        $args = func_get_args();
        $text = array_shift($args);
        $tags = func_num_args() > 2 ? array_diff($args, array($text)) : (array) $tags;
        foreach ($tags as $tag) {
            if (preg_match_all('/<' . $tag . '[^>]*>(.*)<\/' . $tag . '>/iU', $text, $found)) {
                $text = str_replace($found[0], $found[1], $text);
            }
        }

        return $text;
    }

}

?>
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

?>
