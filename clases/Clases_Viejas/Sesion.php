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

//fin del metodo limpiar
}

//fin de la clase sesion
?>
