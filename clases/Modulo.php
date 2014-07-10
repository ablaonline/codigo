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

