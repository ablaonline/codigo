<?php

/**
*
* Copyright (C) 2009 FELINUX LTDA
*
* Autores:
* Francisco J. Lozano B. <fjlozano@felinux.com.co>
* Julián Mondragón <jmondragon@felinux.com.co>
*
* Este archivo es parte de:
* FOLCS :: FELINUX online community system
*
* Este programa es software libre: usted puede redistribuirlo y/o
* modificarlo  bajo los términos de la Licencia Pública General GNU
* publicada por la Fundación para el Software Libre, ya sea la versión 3
* de la Licencia, o (a su elección) cualquier versión posterior.
*
* Este programa se distribuye con la esperanza de que sea útil, pero
* SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita MERCANTIL o
* de APTITUD PARA UN PROPÓSITO DETERMINADO. Consulte los detalles de
* la Licencia Pública General GNU para obtener una información más
* detallada.
*
* Debería haber recibido una copia de la Licencia Pública General GNU
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