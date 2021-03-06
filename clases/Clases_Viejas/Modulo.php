<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 * */
class Modulo {

    /**
     * C�digo interno o identificador del m�dulo en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Clase a la cual pertenece el m�dulo
     * - 1: Configuraci�n del sitio
     * - 2: Configuraci�n personal
     * - 3: Uso global
     * - 4: e-learning
     * @var entero
     */
    public $clase;

    /**
     * Texto que identifica el m�dulo en los archivos de idiomas
     * @var cadena
     */
    public $nombre;

    /**
     * Texto que identifica un registro espec�co del m�dulo a cargar o enlazar en una URL (Ej: 'news' en http://servidor/news/123)
     * @var cadena
     */
    public $url;

    /**
     * Carpeta en la que residen los archivos propios del m�dulo
     * @var cadena
     */
    public $carpeta;

    /**
     * El m�dulo aparece en los men�s o listas de componentes
     * @var l�gico
     */
    public $visible;

    /**
     * El m�dulo puede ser cargado sin verificar permisos
     * @var l�gico
     */
    public $global;

    /**
     * Tabla principal con la que se relaciona el m�dulo
     * @var cadena
     */
    public $tabla;

    /**
     * Inicializar el m�dulo especificado
     * @param cadena $modulo Nombre �nico del m�dulo en la base de datos
     */
    public function __construct($modulo) {
        global $sql, $peticionAJAX, $configuracion, $textos, $parametros;

        /*         * * Hacer globales las variables procedentes de formularios y/o peticiones ** */
        foreach ($GLOBALS as $variable => $valor) {

            if (is_string($variable) && preg_match("/(^sesion_|forma_|^url_|^cookies_|^archivo_)/", $variable)) {
                global $$variable;
            }
        }

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
            /*             * * Cargar archivo manejador de peticiones com�nes ** */
            $archivo = $this->carpeta . "/" . $configuracion["MODULOS"]["principal"];
        }


        if (file_exists($archivo) && is_readable($archivo)) {
            require_once $archivo;
        }
    }

}

?>