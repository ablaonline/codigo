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