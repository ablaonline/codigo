<?php
/**
 *
 * @package     FOLCS
 * @subpackage  respuestas a las actividades poesteadas en los cursos
 * @author      Pablo Andrés Vélez Vidal
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 COLOMBO AMERICANO
 * @version     0.1
 *
 * */
class RespuestaActividad {

    /**
     * Código interno o identificador de la respuesta en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Código interno o identificador del centro binacional en la base de datos al cual pertenece usuario
     * @var entero
     */
    public $idActividad;

    /**
     * Objeto Actividad a la cual pertenece la respuesta
     * @var cadena
     */
    public $actividad;

    /**
     * Código interno o identificador del usuario que publica la respuesta
     * @var entero
     */
    public $idUsuario;
    
    /**
     * usuario que publica la respuesta
     * @var entero
     */
    public $usuario;    
    
    /**
     * imagen miniatura del usuario que publico la repuesta
     * @var entero
     */
    public $imagenAutor;      
        

    /**
     * titulo de la respuesta a la actividad
     * @var entero
     */
    public $titulo;

    /**
     * Descripcion de la respuesta a la actividad
     * @var cadena
     */
    public $descripcion;

    /**
     * Fecha en la cual se publica la respuesta de la actividad
     * @var cadena
     */
    public $fechaPublicacion;
    
    /**
     * Nota de la la respuesta de la actividad
     * @var cadena
     */
    public $nota;   
    
    /**
     * retroalimentacion de la la respuesta de la actividad
     * @var cadena
     */
    public $retroalimentacion;     

    /**
     * Archivo numero 1 relacionado a la respuesta
     * @var cadena
     */
    public $archivoRespuesta1;
    
    /**
     * enlace al archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $enlaceArchivoRespuesta1;      
    
    /**
     * ruta al Archivo numero 1 relacionado a la respuesta
     * @var cadena
     */
    public $rutaArchivoRespuesta1;    

    /**
     * Icono que representa el tipo de archivo del Archivo numero 1 relacionado a la respuesta
     * @var cadena
     */
    public $icono1;

    /**
     * Archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $archivoRespuesta2;
    
    /**
     * enlace al archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $enlaceArchivoRespuesta2;      
    
    /**
     * ruta al Archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $rutaArchivoRespuesta2;      

    /**
     * Icono que representa el tipo de archivo del Archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $icono2;
    
    /**
     * Icono que representa las respuestas a las actividades
     * @var cadena
     */
    public $icono;    

    /**
     * Inicializar el objeto que representa una respuesta de actividad
     * @param entero $id Código interno o identificador de la actividaden la base de datos
     */
    public function __construct($id = NULL) {//recibo el id de la actividad
        global $sql, $configuracion;

        if (!empty($id) && !$sql->existeItem('respuestas_actividades', 'id', $id)) {
            return NULL;
        }

        $this->icono = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'response_activity2.png';
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

        if (!empty($id) && $sql->existeItem('respuestas_actividades', 'id', intval($id))) {
            $this->id = $id;

            $tablas = array(
                'ra' => 'respuestas_actividades',
                'u' => 'usuarios',
                'p' => 'personas',
                'i' => 'imagenes'
            );

            $columnas = array(
                'idActividad' => 'ra.id_actividad',
                'idUsuario' => 'ra.id_usuario',
                'usuario'   => 'u.usuario',
                'imagenAutor' => 'i.ruta',
                'titulo' => 'ra.titulo',
                'descripcion' => 'ra.descripcion',
                'fechaPublicacion' => 'UNIX_TIMESTAMP(ra.fecha_publicacion)',
                'archivoRespuesta1' => 'ra.archivo',
                'archivoRespuesta2' => 'ra.archivo_2',
                'nota'              => 'ra.nota',
                'retroalimentacion' => 'ra.retroalimentacion'
            );

            $condicion = 'ra.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND ra.id = "' . $id . '"';
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $tipo = Recursos::getTipoArchivo($this->archivoRespuesta1);
                $this->icono1 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'icono_' . $tipo . '.png';
                if ($tipo == 'video') {
                    $this->rutaArchivoRespuesta1 = $this->archivoRespuesta1;
                    $this->enlaceArchivoRespuesta1 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono1, 'margenIzquierda'), $this->rutaArchivoRespuesta1, '', '', array('rel' => 'prettyPhoto'), true);
                } elseif ($tipo == 'imagen') {
                    $this->rutaArchivoRespuesta1 =  $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta1;
                    $this->enlaceArchivoRespuesta1 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono1, 'margenIzquierda'), $this->rutaArchivoRespuesta1, '', '', array("rel" => "prettyPhoto['']"), true);
                } elseif ($tipo == 'audio'){
                    $reproductor1 = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["reproductor"]."?file=";
                    $ruta1 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta1;
                    $this->enlaceArchivoRespuesta1 = HTML::enlace('', $reproductor1.$ruta1, 'recursoAudio recursoAudioActividad');
                } else {
                    $this->rutaArchivoRespuesta1 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta1;
                    $this->enlaceArchivoRespuesta1 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono1, 'margenIzquierda'), $this->rutaArchivoRespuesta1);
                }
                


                $tipo1 = Recursos::getTipoArchivo($this->archivoRespuesta2);
                $this->icono2 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'icono_' . $tipo1 . '.png';
                if ($tipo1 == 'video') {
                    $this->rutaArchivoRespuesta2 = $this->archivoRespuesta2;
                    $this->enlaceArchivoRespuesta2 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono2, 'margenIzquierda'), $this->rutaArchivoRespuesta2, '', '', array('rel' => 'prettyPhoto'), true);
                } elseif ($tipo1 == 'imagen') {
                    $this->rutaArchivoRespuesta2 =  $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta2;
                    $this->enlaceArchivoRespuesta2 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono2, 'margenIzquierda'), $this->rutaArchivoRespuesta2, '', '', array("rel" => "prettyPhoto['']"), true);
                } elseif ($tipo1 == 'audio'){
                    $reproductor = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["reproductor"]."?file=";
                    $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta2;
                    $this->enlaceArchivoRespuesta2 = HTML::enlace('', $reproductor.$ruta, 'recursoAudio recursoAudioActividad');
                } else {
                    $this->rutaArchivoRespuesta2 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta2;
                    $this->enlaceArchivoRespuesta2 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD2').HTML::imagen($this->icono2, 'margenIzquierda'), $this->rutaArchivoRespuesta2);
                }
                
                $this->actividad = new ActividadCurso($this->idActividad);
                
                $this->imagenAutor = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesMiniaturas"]."/".$this->imagenAutor;
                
                
            }
        }
    }

    /**
     *
     * Adicionar una respuesta a una actividad
     *
     * @param  arreglo $datos       Datos de la respuesta a adicionar
     * @return entero               Código interno o identificador de la respuesta en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $configuracion, $textos, $sesion_usuarioSesion, $archivo_recurso_1, $archivo_recurso_2;
        
        if( !isset($sesion_usuarioSesion) ){
            return NULL;
        }

        $datosRespuestaActividad = array(
            'id_actividad' => $datos['id_actividad'],
            'id_usuario' => $sesion_usuarioSesion->id,
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => Variable::filtrarTagsInseguros($datos['descripcion']),
            'fecha_publicacion' => date('Y-m-d H:i:s'),
            'archivo' => htmlspecialchars($datos['recurso_1']),
            'archivo_2' => htmlspecialchars($datos['recurso_2']),
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
            
           $datosRespuestaActividad['archivo'] = $recurso; 
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
            
           $datosRespuestaActividad['archivo_2'] = $recurso; 
        }        

        
        $consulta = $sql->insertar('respuestas_actividades', $datosRespuestaActividad);

        if ($consulta) {
            
                $idActividad = $datos['id_actividad'];
                $objeto = new ActividadCurso($idActividad);

                $notificacion1 = str_replace('%1', HTML::enlace($sesion_usuarioSesion->persona->nombreCompleto, HTML::urlInterna('CURSOS', $objeto->curso->id)), $textos->id('MENSAJE_RESPUESTA_ACTIVIDAD'));
                $notificacion = str_replace('%2', HTML::enlace($objeto->titulo, HTML::urlInterna('CURSOS', $objeto->curso->id)), $notificacion1);

                Servidor::notificar($objeto->curso->idAutor, $notificacion, array(), '7');
            
            
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
        
        $archivo1 = $this->archivoRespuesta1;
        if(isset($datos['recurso_1'])){
            $archivo1 = $datos['recurso_1'];
        }
        $archivo2 = $this->archivoRespuesta2;
        if(isset($datos['recurso_2'])){
            $archivo2 = $datos['recurso_2'];
        }

        $datosRespuestaActividad = array(
            'id_actividad' => $datos['id_actividad'],
            'id_usuario' => $sesion_usuarioSesion->id,
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => Variable::filtrarTagsInseguros($datos['descripcion']),
            'fecha_publicacion' => date('Y-m-d H:i:s'),
            'archivo' => $archivo1,
            'archivo_2' => $archivo2,
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
            
           $datosRespuestaActividad['archivo'] = $recurso; 
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
            
           $datosRespuestaActividad['archivo_2'] = $recurso; 
        }        

        
        $consulta = $sql->modificar('respuestas_actividades', $datosRespuestaActividad, 'id = "' . $this->id . '"');

        if ($consulta) {
            return $this->id;
        } else {
            return false;
        }
    }

    /**
     * Eliminar una actividad
     *
     * @param entero $id    Código interno o identificador de la actividad en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql, $configuracion;

        if (!isset($this->id)) {
            return NULL;
        }

        //eliminar cada uno de los archivos que tenga la  actividad
        if (Recursos::getTipoArchivo($this->archivoRespuesta1) != 'video') {
            $archivo1 = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta1;
            Archivo::eliminarArchivoDelServidor(array($archivo1));
        }

        if (Recursos::getTipoArchivo($this->archivoRespuesta2) != 'video') {
            $archivo2 = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta2;
            Archivo::eliminarArchivoDelServidor(array($archivo2));
        }     

        $consulta1 = $sql->eliminar('respuestas_actividades', 'id = "' . $this->id . '"');

        return $consulta1;
    }

    /**
     * @global type $sql
     * @param int $inicio
     * @param int $cantidad
     * @param type $excepcion
     * @param type $condicion
     * @return type 
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
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
            $condicion .= 'ra.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'ra.fecha_publicacion ASC';
        } else {
            $orden = 'ra.fecha_publicacion DESC';
        }

        $tablas = array(
            'ra' => 'respuestas_actividades',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id'    => 'ra.id',
            'idActividad' => 'ra.id_actividad',
            'idUsuario' => 'ra.id_usuario',
            'usuario'   => 'u.usuario',
            'titulo' => 'ra.titulo',
            'nota' => 'ra.nota',            
            'descripcion' => 'ra.descripcion',
            'fechaPublicacion' => 'ra.fecha_publicacion',
            'imagenAutor' => 'i.ruta',
            'nombreAutor' => 'CONCAT(p.nombre, " ", p.apellidos)'
        );

        $condicion .= 'ra.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND ra.id != 0';

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($respuesta = $sql->filaEnObjeto($consulta)) {
                $respuesta->imagenAutor = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesMiniaturas"]."/".$respuesta->imagenAutor;
                $respuesta->actividad = new ActividadCurso($respuesta->idActividad);
                $lista[] = $respuesta;
            }
        }

        return $lista;
    }
    
     /**
     * Modificar la información de una respuesta para agregar la calificacion a dicha respuesta a la actividad
     * @param  arreglo $datos       Datos de la calificacion y retroaliemtacion a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificarCalificacion($datos) {
        global $sql, $textos;

        if (empty($this->id)) {
            return NULL;
        }
        
//        print_r($datos);

        $datosCalificacion = array(
            'nota' => $datos['nota'],
            'retroalimentacion' => Variable::filtrarTagsInseguros($datos['retroalimentacion']),
            'fecha_publicacion_nota' => date('Y-m-d H:i:s')
        );
        $sql->depurar = true;
        $consulta = $sql->modificar('respuestas_actividades', $datosCalificacion, 'id = "' . $this->id . '"');

        if ($consulta) {
            
                $notificacion1 = str_replace('%1', HTML::enlace($this->actividad->curso->usuarioAutor, HTML::urlInterna('CURSOS', $this->actividad->curso->id)), $textos->id('MENSAJE_CALIFICACION_RESPUESTA'));
                $notificacion2 = str_replace('%2', HTML::enlace($this->actividad->titulo, HTML::urlInterna('CURSOS', $this->actividad->curso->id)), $notificacion1);
                $notificacion3 = str_replace('%3', HTML::enlace($this->actividad->curso->nombre, HTML::urlInterna('CURSOS', $this->actividad->curso->id)), $notificacion2);
                $notificacion = str_replace('%4', HTML::frase($textos->id('VER_CALIFICACION'), 'estiloEnlace subtitulo consultarCalificacion', '', array('idRespuesta' => $this->id, 'ruta' => '/ajax/activities/seeGrade')), $notificacion3);

                Servidor::notificar($this->idUsuario, $notificacion, array(), '8');            
            return $this->id;
        } else {
            return false;
        }
    }
    
    /**
     * Contar el numero de respuestas de una determinada actividad
     *
     * @global type $sql
     * @param type $idCurso
     * @return type 
     */
    public function contar($idActividad){
        global $sql;       
        
        $cantidad = $sql->obtenerValor('respuestas_actividades', 'COUNT(id)', 'id_actividad = "'.$idActividad.'"');
        
        return $cantidad;
    }    
    
    

}

