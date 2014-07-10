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
 **/

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
            $archivo = $configuracion["RUTAS"]["idiomas"]."/".$sesion_idioma."/".$configuracion["RUTAS"]["archivoGeneral"].".php";

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
                $archivo = $configuracion["RUTAS"]["idiomas"]."/".$sesion_idioma."/".strtolower($modulo).".php";

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