<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

$contenido = "";

/*** Tinychat ***/
$parametros = array(
    "room"          => "ablaonline_public",
    "colorbk"       => "0x2A2D85",
    "api"           => "none",
    "langdefault"   => "en",
    "join"          => "auto",
    "owner"         => "none",
    "oper"          => "none",
    "nick"          => "Guest"
);

if (isset($sesion_usuarioSesion)) {
    $parametros["nick"] = $sesion_usuarioSesion->usuario;
}

$contenido  .= "<div class=\"encabezadoBloque\"><span class=\"bloqueTitulo ui-helper-clearfix ui-widget-header\">".$textos->id("CHAT")."</span></div>\n";
$contenido  .= "
<script type=\"text/javascript\">
  var tinychat = ".json_encode($parametros).";
</script>
<script src=\"http://tinychat.com/js/embed.js\"></script>
<div id=\"client\"></div>
";


Plantilla::$etiquetas["BLOQUE_IZQUIERDO"] = $contenido;


?>
