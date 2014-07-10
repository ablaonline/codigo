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
//extends Modulo
class Inicio  {

    /**
     * Inicializar la clase
     * @param cadena $modulo Nombre único del módulo en la base de datos
     */
    public function __construct($modulo = NULL) {
    }

    public function resumenNoticias() {
        global $configuracion, $sql, $textos;

        $imagenes  = "<div class=\"oculto\"><a href=\"/noticias/4\"><img src=\"imagenes/fotos/prueba4.jpg\" /><div class=\"titularesNoticias\">Title 4</div></a></div>";
        $imagenes .= "<div class=\"oculto\"><a href=\"/noticias/3\"><img src=\"imagenes/fotos/prueba3.jpg\" /><div class=\"titularesNoticias\">Title 3</div></a></div>";
        $imagenes .= "<div class=\"oculto\"><a href=\"/noticias/2\"><img src=\"imagenes/fotos/prueba2.jpg\" /><div class=\"titularesNoticias\">Title 2</div></a></div>";
        $imagenes .= "<div class=\"oculto\"><a href=\"/noticias/1\"><img src=\"imagenes/fotos/prueba1.jpg\" /><div class=\"titularesNoticias\">Title 1</div></a></div>";

        $miniaturas  = "<ul id=\"miniaturasNoticias\">";
        $miniaturas .= "<li><img src=\"imagenes/fotos/miniaturas/prueba4.jpg\" /></li>\n";
        $miniaturas .= "<li><img src=\"imagenes/fotos/miniaturas/prueba3.jpg\" /></li>\n";
        $miniaturas .= "<li><img src=\"imagenes/fotos/miniaturas/prueba2.jpg\" /></li>\n";
        $miniaturas .= "<li><img src=\"imagenes/fotos/miniaturas/prueba1.jpg\" /></li>\n";
        $miniaturas .= "</ul>\n";


        return $miniaturas.$titulares.HTML::contenedor($imagenes, "", "animacionNoticias");
    } 


    public function resumenUsuarios() {
        global $configuracion, $sql;

        $resumenUsuarios = "";

        $tablas = array(
            "u" => "usuarios",
            "p" => "personas"
        );

        $columnas = array(
            "id"        => "u.id",
            "nombre"    => "p.nombre",
            "apellidos" => "p.apellidos"
        );

        $condicion = "u.id_persona = p.id AND u.activo = '1' AND u.id > 0";
        $consulta  = $sql->seleccionar($tablas, $columnas, $condicion, "", "u.fecha_registro DESC", 0, $configuracion["GENERAL"]["numeroItemsResumen"]);

        if ($sql->filasDevueltas) {
            $contador = 0;
            $lista1   = array();
            $lista2   = array();

            while ($datos = $sql->filaEnObjeto($consulta)) {
                $contador++;
                $nombreCompleto = $datos->nombre." ".$datos->apellidos;
                $imagen         = HTML::imagen($ruta, "imagenPerfil", "", array("title" => $nombreCompleto));

                if ($contador <= 5) {
                    $lista1[] = $imagen.HTML::parrafo($nombreCompleto);

                } else {
                    $lista2[] = $imagen.HTML::parrafo($nombreCompleto);
                }
            }

            $resumenUsuarios .= HTML::parrafo(HTML::lista($lista1, "listaHorizontal resumenUsuarios"));
            $resumenUsuarios .= HTML::parrafo(HTML::lista($lista2, "listaHorizontal resumenUsuarios"));
        }

        return $resumenUsuarios;
    }

    public function resumenBlogs() {
        global $configuracion, $sql, $textos;

        $resumen = "";

        $tablas = array(
            "b" => "blogs",
            "u" => "usuarios",
            "p" => "personas"
        );

        $columnas = array(
            "id"         => "ABS(b.id)",
            "titulo"     => "b.titulo",
            "fecha"      => "UNIX_TIMESTAMP(b.fecha_creacion)",
            "id_usuario" => "ABS(u.id)",
            "autor"      => "CONCAT(p.nombre, ' ', p.apellidos)"
        );

        $condicion = "b.id_usuario = u.id AND u.id_persona = p.id AND b.activo = '1'";
        $consulta  = $sql->seleccionar($tablas, $columnas, $condicion, "", "b.fecha_creacion DESC", 0, $configuracion["GENERAL"]["numeroItemsResumen"]);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($datos = $sql->filaEnObjeto($consulta)) {
                $titulo   = HTML::enlace($datos->titulo, "blogs/".$datos->id);
                $autor    = HTML::enlace($datos->autor, "users/".$datos->id_usuario, "", array("class" => "negrilla"));
                $autor    = preg_replace("/%1/", $autor, $textos->id("PUBLICADO_POR"));
                $autor    = preg_replace("/%2/", date("h:i a", $datos->fecha), $autor);

                $item     = HTML::parrafo($titulo, "", array("class" => "negrilla"));
                $item    .= HTML::parrafo(date("l, F j, Y", $datos->fecha));
                $item    .= HTML::parrafo($autor);
                $lista[]  = $item;
            }

            $resumen = HTML::lista($lista, "resumenVertical");
        }

        return $resumen;
    }

    public function resumenVideos() {

$opciones = "
canvas: {backgroundColor: '#252244'},

plugins: {
   controls: {
      timeBgColor: '#555555',
      tooltipTextColor: '#ffffff',
      backgroundColor: '#252244',
      volumeSliderGradient: 'none',
      timeColor: '#01DAFF',
      progressGradient: 'medium',
      volumeSliderColor: '#000000',
      backgroundGradient: 'medium',
      borderRadius: '0',
      buttonColor: '#1c1362',
      sliderColor: '#4533db',
      durationColor: '#ffffff',
      progressColor: '#1f1e2a',
      bufferColor: '#c70a0d',
      sliderGradient: 'none',
      bufferGradient: 'none',
      buttonOverColor: '#b1add1',
      tooltipColor: '#5F747C',
      height: 20,
      opacity: 1.0
   }
}
";
        $lista[] = HTML::enlace("<img src=\"media/ablaonline.png\">", "media/video4.flv", "video1", array("style" => "display:block;width:120px;height:130px;"))."<script language=\"JavaScript\">flowplayer(\"video1\", \"/reproductor/flowplayer.swf\", {clip: {autoPlay: false}});</script><br>Conversation 1\n";
        $lista[] = HTML::enlace("<img src=\"media/ablaonline.png\">", "media/video2.flv", "video2", array("style" => "display:block;width:120px;height:130px;"))."<script language=\"JavaScript\">flowplayer(\"video2\", \"/reproductor/flowplayer.swf\", {clip: {autoPlay: false}});</script><br>Conversation 2\n";
        $lista[] = HTML::enlace("<img src=\"media/ablaonline.png\">", "media/video3.flv", "video3", array("style" => "display:block;width:120px;height:130px;"))."<script language=\"JavaScript\">flowplayer(\"video3\", \"/reproductor/flowplayer.swf\", {clip: {autoPlay: false}});</script><br>Conversation 3\n";
        $lista[] = HTML::enlace("<img src=\"media/ablaonline.png\">", "media/video6.flv", "video6", array("style" => "display:block;width:120px;height:130px;"))."<script language=\"JavaScript\">flowplayer(\"video6\", \"/reproductor/flowplayer.swf\", {clip: {autoPlay: false}});</script><br>Listening practice 1\n";
        $lista[] = HTML::enlace("<img src=\"media/ablaonline.png\">", "media/video7.flv", "video7", array("style" => "display:block;width:120px;height:130px;"))."<script language=\"JavaScript\">flowplayer(\"video7\", \"/reproductor/flowplayer.swf\", {clip: {autoPlay: false}});</script><br>Listening practice 2\n";
        $lista[] = HTML::enlace("<img src=\"media/ablaonline.png\">", "media/video5.flv", "video5", array("style" => "display:block;width:120px;height:130px;"))."<script language=\"JavaScript\">flowplayer(\"video5\", \"/reproductor/flowplayer.swf\", {clip: {autoPlay: false}});</script><br>Listening practice 3\n";

        return HTML::lista($lista, "listaHorizontal listaVideos");
    }

}
?>