<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano n. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

/**
 *
 * Rutas de los programas (binarios ejecutables del sistema operativo) utilizados
 *
 */
$configuracion["PROGRAMAS"] = array(
    "convert"   => "/usr/bin/convert -resize %1 %2 %3",
    "ffmpeg"    => "/usr/local/bin/ffmpeg -y -i %1 -f mp3 -ab 192 %2",
    "ffmpeg2"   => "/usr/local/bin/ffmpeg -y -i %1 -ac 1 -acodec libmp3lame -ar 22050 -f wav %2"
);

?>