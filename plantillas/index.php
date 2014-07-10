<?php

/**
 *
 * @package     FOLCS
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

/**
 * Nombre del directorio que almacena los archivos de configuraci�n
 * @var cadena
 */
$directorioConfiguracion = "configuracion";


/**
 * Efectuar la carga de los archivos contenidos en el directorio de configuraci�n
 */
if ($directorio = opendir($directorioConfiguracion)) {
    $configuracion = array();

    while (false !== ($archivo = readdir($directorio))) {

        if (($archivo != ".") && ($archivo != "..") && (substr($archivo, -4) == ".php")) {
            $ruta = $directorioConfiguracion."/".$archivo;
            require_once $ruta;
        }
    }

    closedir($directorio);
    unset($directorio, $archivo);
}


/**
 * Efectuar la carga de los archivos de definici�n de clases b�sicas
 */
if ($directorio = opendir($configuracion["RUTAS"]["clases"])) {

    while (false !== ($archivo = readdir($directorio))) {

        if (($archivo != ".") && ($archivo != "..") && (substr($archivo, -4) == ".php")) {
            $ruta = $configuracion["RUTAS"]["clases"]."/".$archivo;
            require_once $ruta;
        }

    }

    closedir($directorio);
    unset($directorio, $archivo);
}


/**
 * Efectuar la carga de los archivos de definici�n de clases de los m�dulos
 */
if ($directorio = opendir($configuracion["MODULOS"]["clases"])) {

    while (false !== ($archivo = readdir($directorio))) {

        if (($archivo != ".") && ($archivo != "..") && (substr($archivo, -4) == ".php")) {
            $ruta = $configuracion["MODULOS"]["clases"]."/".$archivo;
            require_once $ruta;
        }
    }

    closedir($directorio);
    unset($directorio, $archivo);
}

/**
 * Redefinir los nombres de las variables para hacerlas globales
 */
Servidor::exportarVariables();

/**
 * Crear un objeto de conexi�n a la base de datos
 */
$sql = new SQL();

/**
 * Iniciar la gesti�n de la sesi�n
 */
Sesion::iniciar();

/**
 * Definir y registrar el idioma a utilizar durante la sesi�n
 */
if (!isset($sesion_idioma)) {
    Sesion::registrar("idioma", $configuracion["GENERAL"]["idioma"]);
}

/**
 * Definir y registrar el tema a utilizar durante la sesi�n
 */
if (!isset($sesion_tema)) {
    Sesion::registrar("tema", $configuracion["GENERAL"]["tema"]);
}

/**
 * Obtener el nombre del m�dulo a partir de la URL dada para iniciarlo
 */
if (isset($url_modulo)) { 
    $consulta = $sql->seleccionar(array("modulos"), array("nombre"), "url = '$url_modulo'");

    if ($sql->filasDevueltas) {
        $modulo = $sql->filaEnObjeto($consulta);
    }

} else {
    $modulo = NULL;
}

/**
 * Procesar las peticiones recibidas v�a AJAX
 */
if (isset($url_via) && $url_via == "ajax" && !is_null($modulo)) {
    $peticionAJAX = true;
    $modulo       = new Modulo($modulo->nombre);
    $modulo->procesar();

/**
 * Procesar las peticiones recibidas normalmente
 */
} else {
    $peticionAJAX = false;

    /**
     * Verificar si se ha solicitado un m�dulo e iniciarlo
     */
    if (!is_null($modulo)) {
        $modulo = new Modulo($modulo->nombre);

        /**
         * Redireccionar al m�dulo de gesti�n de errores cuando el m�dulo solicitado no existe
         */
        if (!isset($modulo->id)) {
            $modulo = new Modulo("ERROR");
        }

    /**
     * Redireccionar al m�dulo de inicio cuando no se ha especificado alg�n m�dulo
     */
    } else {
        Plantilla::$principal = true;
        $modulo = new Modulo("INICIO");
    }

    /**
     * Enviar al cliente el contenido generado despu�s de procesar la solicitud
     */
    Plantilla::iniciar($modulo);
    $modulo->procesar();
    Servidor::enviarHTML();
}

?>