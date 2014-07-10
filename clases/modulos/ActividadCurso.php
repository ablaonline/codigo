<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Actividades poesteadas en los cursos
 * @author      Pablo Andrés Vélez Vidal
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 COLOMBO AMERICANO
 * @version     0.1
 *
 * */
class ActividadCurso {

    /**
     * Código interno o identificador  de la actibvidad en la base de datos
     * @var entero
     */
    public $id;
    
    /**
     * URL relativa del módulo de actividades
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de una actividad específica
     * @var cadena
     */
    public $url;    

    /**
     * Identificador del curso al cual pertenece la actividad
     * @var entero
     */
    public $idCurso;

    /**
     * objeto curso al cual pertenece la actividad
     * @var cadena
     */
    public $curso;

    /**
     * Código interno o identificador del usuario propietario de la actividad
     * @var entero
     */
    public $idUsuario;

    /**
     * Titulo que lleva la actividad
     * @var entero
     */
    public $titulo;
    
     /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;    

    /**
     * Descripcion de la actividad
     * @var cadena
     */
    public $descripcion;

    /**
     * Fecha en la que se publica la actividad
     * @var cadena
     */
    public $fechaPublicacion;
    
    /**
     * Icono para mostrar en el listado de actividades
     * @var cadena
     */
    public $icono;    

    /**
     * Fecha limite que tiene la actividad para ser resuelta
     * @var cadena
     */
    public $fechaLimite;
    
    /**
     * Dias restantes para que la actividad sea resuelta
     * @var cadena
     */
    public $diasRestantes;    

    /**
     * Archivo numero 1 relacionado a la actividad
     * @var cadena
     */
    public $archivoActividad1;
    
    /**
     * enlace al archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $enlaceArchivoActividad1;    

    /**
     * ruta al Archivo numero 1 relacionado a la respuesta
     * @var cadena
     */
    public $rutaArchivoActividad1;

    /**
     * Icono que representa el tipo de archivo del Archivo numero 1 relacionado a la respuesta
     * @var cadena
     */
    public $icono1;

    /**
     * Archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $archivoActividad2;
    
    /**
     * enlace al archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $enlaceArchivoActividad2;    

    /**
     * ruta al Archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $rutaArchivoActividad2;

    /**
     * Icono que representa el tipo de archivo del Archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $icono2;
    
    /**
     * Representa que tipo de calificacion se usara para esta actividad
     * 1 = calificacion con palabras, ej: excellent, very good, acceptable, etc
     * 2 = calificacion tipo universidad, de 1 a 5
     * 3 = calificacion entre 0 y 100, siendo 100 el score mas alto
     * @var cadena
     */
    public $tipoCalificacion;    

    /**
     * Inicializar el usuario administrador del centro
     * @param entero $id Código interno o identificador de la actividaden la base de datos
     */
    public function __construct($id = NULL) {//recibo el id de la actividad
        
        $modulo         = new Modulo("ACTIVIDADES");
        $this->urlBase  = "/".$modulo->url;
        $this->url      = $modulo->url;
        $this->idModulo = $modulo->id;        

        if (isset($id) && $id != NULL) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos de la actividad
     * @param entero $id Código interno o identificador de la  en la base de datos
     */
    public function cargar($id = NULL) {
        global $sql, $configuracion, $textos;

        if (!empty($id) && $sql->existeItem('actividades_curso', 'id', intval($id))) {
            $this->id = $id;

            $tablas = array(
                'ac' => 'actividades_curso'
            );

            $columnas = array(
                'idCurso' => 'ac.id_curso',
                'idUsuario' => 'ac.id_usuario',
                'titulo' => 'ac.titulo',
                'descripcion' => 'ac.descripcion',
                'fechaPublicacion' => 'ac.fecha_publicacion',
                'fechaLimite' => 'ac.fecha_limite',
                'archivoActividad1' => 'ac.archivo',
                'archivoActividad2' => 'ac.archivo_2',
                'tipoCalificacion' => 'ac.tipo_calificacion',
                'diasRestantes' => 'DATEDIFF(ac.fecha_limite, NOW())'
            );

            $condicion = 'ac.id = "' . $id . '"';
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                if($this->archivoActividad1 != ''){
                    $tipo = Recursos::getTipoArchivo($this->archivoActividad1);
                    $this->icono1 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'icono_' . $tipo . '.png';
                    if ($tipo == 'video') {
                        $this->rutaArchivoActividad1 = $this->archivoActividad1;
                        $this->enlaceArchivoActividad1 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono1, 'margenIzquierda'), $this->rutaArchivoActividad1, '', '', array('rel' => 'prettyPhoto'), true);
                    } elseif ($tipo == 'imagen') {
                        $this->rutaArchivoActividad1 =  $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad1;
                        $this->enlaceArchivoActividad1 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono1, 'margenIzquierda'), $this->rutaArchivoActividad1, '', '', array("rel" => "prettyPhoto['']"), true);
                    } elseif ($tipo == 'audio'){
                        $reproductor1 = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["reproductor"]."?file=";
                        $ruta1 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad1;
                        $this->enlaceArchivoActividad1 = HTML::enlace('', $reproductor1.$ruta1, 'recursoAudio recursoAudioActividad');
                    } else {
                        $this->rutaArchivoActividad1 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad1;
                        $this->enlaceArchivoActividad1 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono1, 'margenIzquierda'), $this->rutaArchivoActividad1);
                    }                    
                    
                }

                if($this->archivoActividad2 != ''){
                    $tipo1 = Recursos::getTipoArchivo($this->archivoActividad2);
                    $this->icono2 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'icono_' . $tipo1 . '.png';
                    if ($tipo1 == 'video') {
                        $this->rutaArchivoActividad2 = $this->archivoActividad2;
                        $this->enlaceArchivoActividad2 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono2, 'margenIzquierda'), $this->rutaArchivoActividad2, '', '', array('rel' => 'prettyPhoto'), true);
                    } elseif ($tipo1 == 'imagen') {
                        $this->rutaArchivoActividad2 =  $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad2;
                        $this->enlaceArchivoActividad2 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono2, 'margenIzquierda'), $this->rutaArchivoActividad2, '', '', array("rel" => "prettyPhoto['']"), true);
                    } elseif ($tipo1 == 'audio'){
                        $reproductor = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["reproductor"]."?file=";
                        $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad2;
                        $this->enlaceArchivoActividad2 = HTML::enlace('', $reproductor.$ruta, 'recursoAudio recursoAudioActividad');
                    } else {
                        $this->rutaArchivoActividad2 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad2;
                        $this->enlaceArchivoActividad2 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD2').HTML::imagen($this->icono2, 'margenIzquierda'), $this->rutaArchivoActividad2);
                    }
                }
                
                
                
                $this->icono = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'activity.png';
                
                $this->curso = new Curso($this->idCurso);
                
            }
        }
    }

    /**
     *
     * Adicionar una actividad
     *
     * @param  arreglo $datos       Datos de la actividad a adicionar
     * @return entero               Código interno o identificador de la actividad en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $configuracion, $sesion_usuarioSesion, $archivo_recurso_1, $archivo_recurso_2, $textos;

        if (!isset($sesion_usuarioSesion)) {
            return NULL;
        }

        $datosActividad = array(
            'id_curso' => $datos['id_curso'],
            'id_usuario' => $sesion_usuarioSesion->id,
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => Variable::filtrarTagsInseguros($datos['descripcion']),
            'fecha_publicacion' => date('Y-m-d H:i:s'),
            'fecha_limite' => htmlspecialchars($datos['fecha_limite']),
            'archivo' => htmlspecialchars($datos['recurso_1']),
            'archivo_2' => htmlspecialchars($datos['recurso_2']),
            'tipo_calificacion' => htmlspecialchars($datos['tipo_calificacion']),
        );

        
        if (isset($archivo_recurso_1) && !empty($archivo_recurso_1['tmp_name'])) {
            
            $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["archivosActividades"];
            $formato = strtolower(substr($archivo_recurso_1["name"], strrpos($archivo_recurso_1["name"], ".") + 1));

            if (in_array($formato, array("jpg", "png", "gif", "jpeg"))) {
                $area = getimagesize($archivo_recurso_1["tmp_name"]);
                $ancho = $area[0];
                $alto = $area[1];

                while ($ancho > 800 || $alto > 600) {
                    $ancho = ($ancho * 90) / 100;
                    $alto = ($alto * 90) / 100;
                }

                $dimensiones = array($ancho, $alto, 80, 90);

                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta, $dimensiones);
                
            } elseif(in_array($formato, array("mp3", "wma", "wav", "ogg", "3gp", "3gpp"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta);
            } elseif(in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta);
            }
            
           $datosActividad['archivo'] = $recurso; 
        }
        
        if (isset($archivo_recurso_2) && !empty($archivo_recurso_2['tmp_name'])) {
            
            $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["archivosActividades"];
            $formato = strtolower(substr($archivo_recurso_2["name"], strrpos($archivo_recurso_2["name"], ".") + 1));

            if (in_array($formato, array("jpg", "png", "gif", "jpeg"))) {
                $area = getimagesize($archivo_recurso_2["tmp_name"]);
                $ancho = $area[0];
                $alto = $area[1];

                while ($ancho > 800 || $alto > 600) {
                    $ancho = ($ancho * 90) / 100;
                    $alto = ($alto * 90) / 100;
                }

                $dimensiones = array($ancho, $alto, 80, 90);

                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta, $dimensiones);
                
            } elseif(in_array($formato, array("mp3", "wma", "wav", "ogg", "3gp", "3gpp"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta);
            } elseif(in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta);
            }
            
           $datosActividad['archivo_2'] = $recurso; 
        }        

        $consulta = $sql->insertar('actividades_curso', $datosActividad);

        if ($consulta) {
            
            if (isset($datos['notificar_estudiantes'])) {//determina si se escogio notificar a los estudiantes de haber subido la imagen
                $idCurso = $datos['id_curso'];
                $objetoCurso = new Curso($idCurso);
                $consultaSeguidores = $sql->seleccionar(array('cursos_seguidos'), array('id', 'id_usuario'), 'id_curso = "' . $idCurso . '"', '');
                if ($sql->filasDevueltas) {
                    $nombreItem = $datos['titulo'];
                    while ($seguidor = $sql->filaEnObjeto($consultaSeguidores)) {

                        $notificacion1 = str_replace('%1', HTML::enlace($objetoCurso->autor, HTML::urlInterna('CURSOS', $idCurso)), $textos->id('MENSAJE_ADICION_ACTIVIDAD'));
                        $notificacion2 = str_replace('%2', HTML::enlace($nombreItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion1);
                        $notificacion3 = str_replace('%3', HTML::enlace($objetoCurso->nombre, HTML::urlInterna('CURSOS', $idCurso)), $notificacion2);
                        $notificacion = str_replace('%4', HTML::enlace($datos['fecha_limite'], HTML::urlInterna('CURSOS', $idCurso)), $notificacion3);
                        

                        Servidor::notificar($seguidor->id_usuario, $notificacion, array(), '6');
                    }
                }
            }            
            
            return $sql->ultimoId;
        } else {
            return false;
        }
    }

    /**
     * Modificar la información de una actividad
     * @param  arreglo $datos       Datos de la actividad a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $configuracion, $sesion_usuarioSesion, $archivo_recurso_1, $archivo_recurso_2;

        if (empty($this->id)) {
            return NULL;
        }
        
        $archivo1 = $this->archivoActividad1;

        if(!empty($datos['recurso_1'])){
            $archivo1 = $datos['recurso_1'];
        }
        $archivo2 = $this->archivoActividad2;
        if(!empty($datos['recurso_2'])){
            $archivo2 = $datos['recurso_2'];
        }        

        $datosActividad = array(
            'id_curso' => $datos['id_curso'],
            'id_usuario' => $sesion_usuarioSesion->id,
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => Variable::filtrarTagsInseguros($datos['descripcion']),
            'fecha_publicacion' => date('Y-m-d H:i:s'),
            'fecha_limite' => htmlspecialchars($datos['fecha_limite']),
            'archivo' => $archivo1,
            'archivo_2' => $archivo2,
            'tipo_calificacion' => htmlspecialchars($datos['tipo_calificacion']),
        );

        
        if (isset($archivo_recurso_1) && !empty($archivo_recurso_1['tmp_name'])) {
            
            $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["archivosActividades"];
            $formato = strtolower(substr($archivo_recurso_1["name"], strrpos($archivo_recurso_1["name"], ".") + 1));

            if (in_array($formato, array("jpg", "png", "gif", "jpeg"))) {
                $area = getimagesize($archivo_recurso_1["tmp_name"]);
                $ancho = $area[0];
                $alto = $area[1];

                while ($ancho > 800 || $alto > 600) {
                    $ancho = ($ancho * 90) / 100;
                    $alto = ($alto * 90) / 100;
                }

                $dimensiones = array($ancho, $alto, 80, 90);

                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta, $dimensiones);
                
            } elseif(in_array($formato, array("mp3", "wma", "wav", "ogg", "3gp", "3gpp"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta);
            } elseif(in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta);
            }
            
           $datosActividad['archivo'] = $recurso; 
        }
        
        if (isset($archivo_recurso_2) && !empty($archivo_recurso_2['tmp_name'])) {
            
            $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["archivosActividades"];
            $formato = strtolower(substr($archivo_recurso_2["name"], strrpos($archivo_recurso_2["name"], ".") + 1));

            if (in_array($formato, array("jpg", "png", "gif", "jpeg"))) {
                $area = getimagesize($archivo_recurso_2["tmp_name"]);
                $ancho = $area[0];
                $alto = $area[1];

                while ($ancho > 800 || $alto > 600) {
                    $ancho = ($ancho * 90) / 100;
                    $alto = ($alto * 90) / 100;
                }

                $dimensiones = array($ancho, $alto, 80, 90);

                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta, $dimensiones);
                
            } elseif(in_array($formato, array("mp3", "wma", "wav", "ogg", "3gp", "3gpp"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta);
            } elseif(in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta);
            }
            
           $datosActividad['archivo_2'] = $recurso; 
        }   
        
        $consulta = $sql->modificar('actividades_curso', $datosActividad, 'id = "' . $this->id . '"');

        if ($consulta) {
            return $this->id;
        } else {
            return false;
        }
    }

    /**
     * Eliminar una actividad
     * @param entero $id    Código interno o identificador de la actividad en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql, $configuracion;

        if (!isset($this->id)) {
            return NULL;
        }

        //eliminar cada uno de los archivos que tenga la  actividad
        if (Recursos::getTipoArchivo($this->archivoActividad1) != 'video') {
            $archivo1 = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad1;
            Archivo::eliminarArchivoDelServidor(array($archivo1));
        }

        if (Recursos::getTipoArchivo($this->archivoActividad2) != 'video') {
            $archivo2 = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad2;
            Archivo::eliminarArchivoDelServidor(array($archivo2));
        }

        //eliminar cada una de las respuestas que haya tenido cada una de las actividades
        $tablas = array('respuestas_actividades');
        $columnas = array('id' => 'id');
        $condicion = 'id_actividad = ' . $this->id . '';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($respuesta = $sql->filaEnObjeto($consulta)) {

                $respuestaActividad = new RespuestaActividad($respuesta->id);
                $respuestaActividad->eliminar();
            }
        }


        $sql->eliminar('actividades_curso', 'id = "' . $this->id . '"');

        return $consulta;
    }

    /**
     * Listar las actividades del curso
     * @global type $sql
     * @param int $inicio
     * @param int $cantidad
     * @param type $excepcion
     * @param type $condicion
     * @return type 
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idCurso = NULL) {
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
            $condicion .= 'ac.id NOT IN (' . $excepcion . ') AND ';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'ac.fecha_publicacion ASC';
        } else {
            $orden = 'ac.fecha_publicacion DESC';
        }

        $tablas = array(
            'ac' => 'actividades_curso'
        );

        $columnas = array(
            'id' => 'ac.id',
            'idCurso' => 'ac.id_curso',
            'idUsuario' => 'ac.id_usuario',
            'titulo' => 'ac.titulo',
            'descripcion' => 'ac.descripcion',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(ac.fecha_publicacion)',
            'fechaLimite' => 'UNIX_TIMESTAMP(ac.fecha_limite)',
            'diasRestantes' => 'DATEDIFF(ac.fecha_limite, NOW())'
        );

        $condicion .= 'ac.id_curso = "'.$idCurso.'"';

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($actividad = $sql->filaEnObjeto($consulta)) {
                $actividad->icono = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'activity.png';
                $lista[] = $actividad;
            }
        }

        return $lista;
    }
    
    /**
     *
     * @global type $sql
     * @param type $idCurso
     * @return type 
     */
    public function contar($idCurso){
        global $sql;       
        
        $cantidad = $sql->obtenerValor('actividades_curso', 'COUNT(id)', 'id_curso = "'.$idCurso.'"');
        
        return $cantidad;
    }
    
      
    
    

}

