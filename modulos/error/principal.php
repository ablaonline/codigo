<?php

/**
*
* Copyright (C) 2009 FELINUX LTDA
*
* Autores:
* Francisco J. Lozano B. <fjlozano@felinux.com.co>
* Juli�n Mondrag�n <jmondragon@felinux.com.co>
*
* Este archivo es parte de:
* FOLCS :: FELINUX online community system
*
* Este programa es software libre: usted puede redistribuirlo y/o
* modificarlo  bajo los t�rminos de la Licencia P�blica General GNU
* publicada por la Fundaci�n para el Software Libre, ya sea la versi�n 3
* de la Licencia, o (a su elecci�n) cualquier versi�n posterior.
*
* Este programa se distribuye con la esperanza de que sea �til, pero
* SIN GARANT�A ALGUNA; ni siquiera la garant�a impl�cita MERCANTIL o
* de APTITUD PARA UN PROP�SITO DETERMINADO. Consulte los detalles de
* la Licencia P�blica General GNU para obtener una informaci�n m�s
* detallada.
*
* Deber�a haber recibido una copia de la Licencia P�blica General GNU
* junto a este programa. En caso contrario, consulte:
* <http://www.gnu.org/licenses/>.
*
**/

$texto = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.
          Sed porttitor ligula et ligula eleifend fringilla rutrum nisi varius.
          Phasellus egestas tempor nulla, eleifend varius leo aliquet ac.
          Quisque accumsan sagittis neque et auctor.
          Aliquam neque felis, pellentesque quis lacinia ac, posuere et velit.
          Donec rhoncus convallis neque ac molestie. Aliquam ut feugiat ligula.
          Morbi a purus neque, at posuere nibh.";

Plantilla::$etiquetas["BLOQUE_IZQUIERDO"]  = HTML::bloque("prueba1","Error",$texto);

?>