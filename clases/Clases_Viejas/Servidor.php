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

/**
 *
 * Clase para la gestión del comportamiento del servidor web
 *
 **/

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

    private function __construct() {}

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
    ***/



    /*** Codificar una cadena o arreglo de cadenas para enviar en formato JSON ***/
    public static function exportarVariables() {

        if (empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            self::$cliente = $_SERVER["REMOTE_ADDR"];
            self::$proxy = "";
        } else {
            self::$cliente = $_SERVER["HTTP_X_FORWARDED_FOR"];
            self::$proxy   = $_SERVER["REMOTE_ADDR"];
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

                $nombre  = "forma_$variable";
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

                $nombre  = "url_$variable";
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

                $nombre  = "archivo_$variable";
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

                $nombre  = "cookie_$variable";
                global $$nombre;
                $$nombre = $valor;
            }
        }
    }




    /*** Codificar una cadena o arreglo de cadenas para enviar en formato JSON ***/
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




    /*** Codificar una cadena o arreglo de cadenas para enviar en formato JSON ***/
    public static function enviarHTML() {
        Plantilla::generarCodigo();
        echo Plantilla::$contenido;
    }

    /*** Enviar mensaje por correo electrónico ***/
    public static function enviarCorreo($destino, $asunto, $contenido, $nombre = NULL) {
        global $configuracion;

        $envio = NULL;

        self::$nombreRemitenteCorreo    = $configuracion["SERVIDOR"]["nombreRemitente"];
        self::$direccionRemitenteCorreo = $configuracion["SERVIDOR"]["correoRemitente"];

        if (isset($destino) && filter_var($destino, FILTER_VALIDATE_EMAIL) && isset($asunto) && isset($contenido)) {

            if (isset($nombre)) {
                $destino = trim($nombre)." <".$destino.">\r\n";
            }

            $cabecera .= "MIME-Version: 1.0\r\n";
            $cabecera .= "Content-type: text/html; charset=".$configuracion["SERVIDOR"]["codificacion"]."\r\n";
            $cabecera  = "From: ".self::$nombreRemitenteCorreo." <".self::$direccionRemitenteCorreo.">\r\n";
            $cabecera .= "To: $destino\r\n";
            $envio     = mail("", trim($asunto), $contenido, $cabecera, "-f".self::$direccionRemitenteCorreo);
        }

        return $envio;
    }

    /*** Generar notificación para un usuario  ***/
    public static function notificar($usuario, $mensaje, $variables = array(), $correo = false) {
        global $sql, $configuracion;

        foreach ($variables as $variable => $valor) {
            $mensaje = preg_replace("/$variable/", $valor, $mensaje);
        }

        $datos = array(
            "id_usuario" => $usuario,
            "fecha"      => date("Y-m-d H:i:s"),
            "contenido"  => $mensaje,
            "activo"     => "1"
        );
	$sql->guardarBitacora = false;
        $sql->insertar("notificaciones", $datos);

        return $envio;
    }

}
?>