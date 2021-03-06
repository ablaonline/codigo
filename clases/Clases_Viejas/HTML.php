<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 * */
class HTML {
    /*     * * Generar c�digo HTML con bot�n y formulario para ejecutar un comando via AJAX ** */

    static function botonAjax($icono, $texto, $destino, $datos = array(), $clase = NULL) {
        global $textos;

        $codigo = HTML::boton($icono, $textos->id($texto), $clase, "", "botonOk");

        foreach ($datos as $nombre => $valor) {
            $codigo .= HTML::campoOculto($nombre, $valor);
        }
        $codigo = HTML::forma($destino, $codigo);

        return $codigo;
    }

    /*     * * Generar c�digo HTML para insertar un icono en l�nea en un <span> ** */

    static function ayuda($texto) {
        global $configuracion;

        $ruta = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "/ayuda.png";
        $clase = "imagenAyudaTooltip";
        $id = "imagenAyuda";
        $opciones = array("alt" => $texto);
        $codigo = HTML::imagen($ruta, $clase, $id, $opciones);

        return $codigo;
    }

    /*     * * Generar c�digo HTML con bot�n y formulario para ejecutar un comando via AJAX ** */

    static function botonImagenAjax($contenido, $clase, $id, $opciones, $destino, $datos = array(), $idForma) {
        global $textos;

        $codigo = HTML::contenedor($contenido, $clase, $id, $opciones);

        foreach ($datos as $nombre => $valor) {
            $codigo .= HTML::campoOculto($nombre, $valor);
        }
        $codigo .= "";
        $codigo = HTML::forma($destino, $codigo, "", "", "", "", $idForma);

        return $codigo;
    }

    /*     * * Generar c�digo HTML con bot�n y formulario para ejecutar un comando via AJAX con cualquier tipo de contenido, ya sea texto, imagen o ambos** */

    static function formaAjax($contenido, $clase, $id, $opciones, $destino, $datos = array(), $idForma = NULL) {

        $codigo = HTML::contenedor(HTML::contenedor($contenido, $clase, $id, $opciones), "enviarAjax", "");
        foreach ($datos as $nombre => $valor) {
            $codigo .= HTML::campoOculto($nombre, $valor);
        }
        $codigo .= ""; //HTML::campoOculto("idQuemado", "", "idQuemado");
        $codigo = HTML::forma($destino, $codigo, "", "", "", "", $idForma);

        return $codigo;
    }

    /*     * * Generar c�digo HTML con bot�n y formulario para adicionar un item ** */

    static function botonAdicionarItem($url, $titulo) {

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("masGrueso", $titulo);
        $codigo = HTML::forma("/ajax/$url/add", $codigo);

        return $codigo;
    }

    /*     * * Generar c�digo HTML con bot�n y formulario para modificar un item ** */

    static function botonModificarItem($id, $url) {
        global $textos;

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("lapiz", $textos->id("MODIFICAR"));
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/edit", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    /*     * * Generar c�digo HTML con bot�n y formulario para eliminar un item desde el listado principal haciendo uso de ajax ** */

    static function botonModificarItemAjax($id, $url) {
        global $textos;

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("lapiz", $textos->id("MODIFICAR"));
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/editRegister", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    static function botonModificarItemDesdeBuscador($id, $url) {
        $url = preg_replace("|^\/|", "", $url);

        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-pencil'></span>
                            <span class='ui-button-text'>
                                Edit
                            </span>
                            </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/edit", $codigo);
        $codigo = HTML::contenedor($codigo, "botonModificarItemBuscador flotanteDerecha medioMargenDerecha", "botonModificarItemBuscador");

        return $codigo;
    }

    /*     * * Generar c�digo HTML con bot�n y formulario para eliminar un item ** */

    static function botonEliminarItem($id, $url) {
        global $textos;

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("basura", $textos->id("ELIMINAR"));
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/delete", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    /*     * * Generar c�digo HTML con bot�n y formulario para eliminar un item desde el listado principal haciendo uso de ajax ** */

    static function botonEliminarItemAjax($id, $url) {
        global $textos;

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("basura", $textos->id("ELIMINAR"), "", "", "nuevoBoton");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/deleteRegister", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");
        return $codigo;
    }

    /*     * * Generar c�digo HTML con bot�n y formulario para eliminar un item desde el listado principal haciendo uso de ajax** */

    static function botonEliminarItemDesdeBuscador($id, $url) {
        $url = preg_replace("|^\/|", "", $url);
        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Delete
                            </span>
                    </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/delete", $codigo);
        $codigo = HTML::contenedor($codigo, "botonEliminarItemBuscador flotanteDerecha", "botonEliminarItemBuscador");

        return $codigo;
    }

    /*     * * Generar c�digo HTML con bot�n y formulario para aprobar un item ** */

    static function botonAprobarItem($id, $url) {

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("chequeo", $textos->id("APROBAR"));
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/approve", $codigo);


        return $codigo;
    }

    /*     * * Generar c�digo HTML con bot�n y formulario para subir un item un nivel ** */

    static function botonSubirItem($id, $url) {
        global $textos;

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("flechaGruesaArriba", $textos->id("SUBIR"));
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/up", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    /*     * * Generar c�digo HTML con bot�n y formulario para bajar un item un nivel ** */

    static function botonBajarItem($id, $url) {
        global $textos;

        $url = preg_replace("|^\/|", "", $url);
        $codigo = HTML::boton("flechaGruesaAbajo", $textos->id("BAJAR"));
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/down", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    /*     * * Generar c�digo HTML para insertar un icono en l�nea en un <span> ** */

    static function icono($icono) {
        global $configuracion;

        if (array_key_exists($icono, $configuracion["ICONOS"])) {
            $icono = "ui-icon-" . $configuracion["ICONOS"][$icono];
        }
        $codigo = "<span class=\"ui-icon $icono icono\" style=\"display: inline-block;\"></span>";

        return $codigo;
    }

    /*     * * Generar c�digo HTML para insertar un icono en l�nea en un <span> ** */

    static function icono2($icono) {
        global $configuracion;

        $codigo = "<span class=\" $icono \" style=\"display: inline-block;\"></span>";

        return $codigo;
    }

    /*     * * Generar c�digo HTML para resaltar una frase con <span> ** */

    static function frase($contenido, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = ' <span';

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = " ' . $valor . ' "  ';
            }
        }

        $codigo .= '>';

        if (!empty($contenido) && is_string($contenido)) {
            $codigo .= '     ' . $contenido . ' ';
        }

        $codigo .= '</span>';

        return $codigo;
    }

    /*     * * Generar c�digo HTML para un contenedor (div) ** */

    static function contenedor($contenido = NULL, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <div';

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id = ' . "$id" . ' ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '"';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . '="' . $valor . '" ';
            }
        }

        $codigo .= '>';

        if (!empty($contenido) && is_string($contenido)) {
            $codigo .= '     ' . $contenido . '';
        }

        $codigo .= '    </div>';

        return $codigo;
    }

    /*     * * Generar c�digo HTML para insertar un enlace ** */

    static function enlace($texto, $destino = NULL, $clase = NULL, $id = NULL, $opciones = NULL) {
        global $configuracion;

        if (empty($destino)) {
            $destino = $texto;
        }

        $codigo = '     <a href="' . $destino . '" ';

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        $servidor = addslashes($configuracion["SERVIDOR"]["principal"]);

        if (preg_match("|^(https?\:\/\/)|", $destino) && !preg_match("|(^" . $servidor . ")|", $destino)) {
            $codigo .= ' target="_blank"';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . '="' . $valor . '" ';
            }
        }

        $codigo .= '>';

        if (!empty($texto) && is_string($texto)) {
            $codigo .= $texto;
        }

        $codigo .= '</a>';

        return $codigo;
    }

    /*     * * Generar c�digo HTML para insertar un enlace con la Estrella de Fondo** */

    static function enlaceEstrella($texto, $destino = NULL, $clase = "claseEstrella", $id = "claseEstrella", $opciones = NULL) {
        global $configuracion;

        if (empty($destino)) {
            $destino = $texto;
        }

        $codigo = "     <a href=\"$destino\"";

        if (!empty($clase) && is_string($clase)) {
            $codigo .= " class=\"claseEstrella\"";
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= " id=\"claseEstrella\"";
        }

        $servidor = addslashes($configuracion["SERVIDOR"]["principal"]);

        if (preg_match("|^(https?\:\/\/)|", $destino) && !preg_match("|(^" . $servidor . ")|", $destino)) {
            $codigo .= " target=\"_blank\"";
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= " $atributo=\"$valor\"";
            }
        }

        $codigo .= ">";

        if (!empty($texto) && is_string($texto)) {
            $codigo .= $texto;
        }

        $codigo .= "</a>";

        return $codigo;
    }

    /*     * * Generar c�digo HTML para insertar un p�rrafo ** */

    static function parrafo($contenido = NULL, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <p';

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= '>';

        if (!empty($contenido) && is_string($contenido)) {
            $codigo .= '     ' . $contenido . ' ';
        }

        $codigo .= '     </p> ';

        return $codigo;
    }

    /*     * * Generar c�digo HTML para insertar una lista ** */

    static function lista($contenido = NULL, $claseLista = NULL, $claseItems = NULL, $id = NULL, $opciones = NULL) {

        if (!is_array($contenido) || !count($contenido)) {
            return NULL;
        }

        $codigo = '     <ul';

        if (!empty($claseLista) && is_string($claseLista)) {
            $codigo .= ' class="' . $claseLista . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . '="' . $valor . '" ';
            }
        }

        $codigo .= '>';

        foreach ($contenido as $item) {
            $codigo .= '      <li';

            if (!empty($claseItems) && is_string($claseItems)) {
                $codigo .= ' class="' . $claseItems . '" ';
            }

            $codigo .= '>' . $item . '</li>';
        }

        $codigo .= '     </ul>';

        return $codigo;
    }

    /*     * * Generar c�digo HTML para insertar una lista ** */

    static function listaEstrella($contenido = NULL, $claseLista = NULL, $claseItems = NULL, $id = NULL, $opciones = NULL) {

        if (!is_array($contenido) || !count($contenido)) {
            return NULL;
        }

        $codigo = '     <ul';

        if (!empty($claseLista) && is_string($claseLista)) {
            $codigo .= ' class="' . $claseLista . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . '="' . $valor . '" ';
            }
        }

        $codigo .= '>';

        foreach ($contenido as $item) {
            $codigo .= '      <li';

            if (!empty($claseItems) && is_string($claseItems)) {
                $codigo .= ' class="' . $claseItems . '" ';
            }

            $codigo .= '><div class = "listaEstrella">' . $item . '</div></li>';
        }

        $codigo .= '     </ul>';

        return $codigo;
    }

    /*     * * Generar c�digo HTML para insertar una imagen ** */

    static function imagen($ruta, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <img src="' . $ruta . '" ';

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            if (!array_key_exists("alt", $opciones)) {
                $codigo .= ' alt="" ';
            }

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        } else {
            $codigo .= ' alt="" ';
        }

        $codigo .= ' />';

        return $codigo;
    }

    /*     * *************************************** Generar c�digo HTML para insertar un bloque *********************************************** */

    static function bloque($id, $titulo, $contenido, $claseTitulo = NULL, $claseContenido = NULL, $smaller = NULL) {//le agregue el parametro smalle para saber cuando debe ser un encabezado mas peque�o
        $codigo = '     <div id="' . $id . '" class="bloque ui-widget ui-corner-all">';

        /* En esta parte verifico que estilo debe de llevar el titulo del bloque que voy a trabajar, es condicionado por el valor del sexto parametro de la funcion */
        if ($smaller == "-IS") {
            $codigo .= '     <div class="encabezadoBloque-IS ' . $claseTitulo . ' "><span class = "bloqueTitulo-IS ui-helper-clearfix ui-widget-header">' . $titulo . '</span></div>';
        }

        if ($smaller == "-DS") {
            $codigo .= '      <div class= "encabezadoBloque-DS ' . $claseTitulo . ' "><span class ="bloqueTitulo-DS ui-helper-clearfix ui-widget-header">' . $titulo . '</span></div> ';
        }

        if ($smaller == NULL) {
            $codigo .= '     <div class= "encabezadoBloque ' . $claseTitulo . ' "><span class ="bloqueTitulo ui-helper-clearfix ui-widget-header">' . $titulo . '</span></div>';

            //$codigo .= HTML::boton("anterior", "Go Back", "botonVolver");
        }

        /*         * ******************************************************************************************************************************************** */
        //$codigo  = "     <div clas =\"divTituloBloque\">$codigo2</div>";
        $codigo .= '     <div class= "contenidoBloque ' . $claseContenido . ' "> ';
        $codigo .= '     ' . $contenido . '';
        $codigo .= '     </div>';
        $codigo .= '     <div class = "sombraInferior"></div>';
        $codigo .= '     </div>';
        return $codigo;
    }

    /*     * * Generar c�digo HTML para insertar el bloque de las noticias** */

    static function bloqueNoticias($id, $contenido, $claseContenido = NULL) {
        $codigo = '     <div id="' . $id . '" class="bloque ui-widget ui-corner-all">';

        $codigo .= '     <div class= "bloqueResumenNoticias ' . $claseContenido . '"> ';
        $codigo .= '     ' . $contenido . '';
        $codigo .= '     </div>';
        $codigo .= '     <div class= "sombraInferior"></div>';
        $codigo .= '     </div>';
        return $codigo;
    }

    static function bloqueNoticiasCultural() {
        global $sql, $configuracion, $sesion_usuarioSesion;
        //$sql->depurar = true;
        $consulta = $sql->seleccionar(array("noticias"), array("id"), "id_categoria = '10'", "", "", 0, 3);

        $codigo = '<div id="contenedorNotiCultural"><ol>';

        while ($not = $sql->filaEnObjeto($consulta)) {//muestro las noticias culturales
            $noticia = new Noticia($not->id);

            $codigo .= "
                          <li>
                            <h2><span>" . substr($noticia->titulo, 0, 25) . "...</span></h2>
                            <div>
                                <figure>
                                   <a href='" . $noticia->url . "'> <img src=" . $noticia->imagenPrincipal . " alt='pruebas ' /> </a>
                                    <figcaption>" . $noticia->resumen . "</figcaption>
                                </figure>
                            </div>
                          </li>
                        ";
        }

        $consulta = $sql->seleccionar(array("eventos"), array("id"), "id_categoria = '10'", "", "", 0, 3);
        while ($eve = $sql->filaEnObjeto($consulta)) {//muestro los eventos culturales                       
            $evento = new Evento($eve->id);

            $codigo .= "
                          <li>
                            <h2><span>" . substr($evento->titulo, 0, 25) . "...</span></h2>
                            <div>
                                <figure>
                                   <a href='" . $evento->url . "'> <img src=" . $evento->imagenPrincipal . " alt='pruebas ' /> </a>
                                    <figcaption>" . $evento->resumen . "</figcaption>
                                </figure>
                            </div>
                          </li>
                        ";
        }

        $codigo .= "<noscript>
                <p>Please enable JavaScript to get the full experience.</p>
            </noscript>";
        $codigo .= "</ol></div>";

        return $codigo;
    }

    /*     * * Generar c�digo HTML para formulario ** */

    static function forma($destino, $contenido, $metodo = "P", $incluyeArchivos = false, $id = NULL, $opciones = NULL, $name = NULL) {
        global $configuracion;

        $codigo = '     <form action="' . $destino . '" ';

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($name) && is_string($name)) {
            $codigo .= ' name="' . $name . '"';
        }

        if (strtoupper($metodo) == "P") {
            $codigo .= ' method="post"';
        } elseif (strtoupper($metodo) == "G") {
            $codigo .= " method=\"get\"";
        } else {
            $codigo .= ' method="post"';
        }

        if ($incluyeArchivos) {
            $codigo .= ' enctype="multipart/form-data"';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= '>';
        $codigo .= '     <fieldset>';

        if ($incluyeArchivos) {
            $codigo .= '     <input type="hidden" name="MAX_FILE_SIZE" value="' . $configuracion["DIMENSIONES"]["maximoPesoArchivo"] . ' " />';
        }

        $codigo .= '     ' . $contenido . '';
        $codigo .= '     </fieldset>';
        $codigo .= '     </form>';
        return $codigo;
    }

    /*     * * Generar c�digo HTML para campo de captura de texto de una l�nea ** */

    static function campoTexto($nombre, $longitud, $limite = NULL, $valorInicial = NULL, $clase = NULL, $id = NULL, $opciones = NULL, $ayuda = NULL) {
        $codigo = '     <input type="text" name="' . $nombre . '" size="' . $longitud . '" ';

        if (!empty($limite) && is_int($limite)) {
            $codigo .= ' maxlength="' . $limite . '" ';
        }

        if (!empty($valorInicial) && is_string($valorInicial)) {
            $codigo .= ' value="' . $valorInicial . '" ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= ' />';

        if (!empty($ayuda) && is_string($ayuda)) {
            $codigo .= HTML::ayuda($ayuda);
        }

        return $codigo;
    }

    static function cargarIconoAyuda($texto) {
        global $textos;
        $codigo = "";
        $codigo .= HTML::campoOculto("textoAyudaModulo", $texto, "textoAyudaModulo");
        $codigo = HTML::contenedor($codigo, "contenedorImagenAyuda", "contenedorImagenAyuda");

        return $codigo;
    }

    /*     * * Generar c�digo HTML para campo de texto oculto ** */

    static function campoOculto($nombre, $valorInicial = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <input type="hidden" name="' . $nombre . '" value="' . $valorInicial . '" ';

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= ' />';
        return $codigo;
    }

    /*     * * Generar c�digo HTML para la selecci�n de un archivo ** */

    static function campoArchivo($nombre, $valorInicial = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <input type="file" name="' . $nombre . '" value="' . $valorInicial . '" ';

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= ' />';
        return $codigo;
    }

    /*     * * Generar c�digo HTML para campo de chequeo (checkbox) ** */

    static function campoChequeo($nombre, $chequeado = false, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <input type="checkbox" name="' . $nombre . '" ';

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if ($chequeado) {
            $codigo .= ' checked="true" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . '="' . $valor . '" ';
            }
        }

        $codigo .= ' />';
        return $codigo;
    }

    /*     * * Generar c�digo HTML para un Radio Button** */

    static function radioBoton($nombre, $chequeado = NULL, $clase = NULL, $valor = NULL, $opciones = NULL, $id = NULL) {
        $codigo = '     <input type = "radio" name = "' . $nombre . '" ';

        if (!empty($valor) && is_string($valor)) {
            $codigo .= ' value = "' . $valor . '" ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class = "' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id = "' . $id . '" ';
        }

        if ($chequeado) {
            $codigo .= ' checked ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= ' />';
        return $codigo;
    }

    /*     * * Generar c�digo HTML para campo de captura de texto de m�ltiples l�nea ** */

    static function areaTexto($nombre, $filas, $columnas, $valorInicial = NULL, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <textarea name="' . $nombre . '" rows="' . $filas . '" cols="' . $columnas . '"';

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '"';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '"';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' ="' . $valor . '" ';
            }
        }

        $codigo .= '>' . $valorInicial . '</textarea>';
        return $codigo;
    }

    /*     * * Generar c�digo HTML para presentar nombres de los campos (etiquetas) ** */

    static function etiqueta($texto, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <span';

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="etiqueta ' . $clase . '"';
        } else {
            $codigo .= ' class="etiqueta" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= '>' . $texto . ':</span>';

        return $codigo;
    }

    /*     * * Generar c�digo HTML para campo de captura de palabra clave ** */

    static function campoClave($nombre, $longitud, $limite = NULL, $valorInicial = NULL, $clase = NULL, $id = NULL, $opciones = NULL) {
        $codigo = '     <input type="password" name="' . $nombre . '" size="' . $longitud . '"';

        if (!empty($limite) && is_int($limite)) {
            $codigo .= ' maxlength="' . $limite . '" ';
        }

        if (!empty($valorInicial) && is_string($valorInicial)) {
            $codigo .= ' value="' . $valorInicial . '" ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' $atributo="' . $valor . '" ';
            }
        }

        $codigo .= ' />';
        return $codigo;
    }

    /*     * * Generar lista desplegable ** */

    static function listaDesplegable($nombre, $contenido, $valorInicial = NULL, $clase = NULL, $id = NULL, $primerItem = NULL, $opciones = NULL) {
        global $sql;

        $codigo = '     <select name="' . $nombre . '" ';

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $clase . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= '>';

        if (!empty($primerItem) && is_string($primerItem)) {
            $codigo .= '     <option>' . $primerItem . '</option> ';
        }

        /*         * * La lista debe ser generada a partir del resultado de una consulta ** */
        if (is_resource($contenido)) {
            while ($datos = $sql->filaEnArreglo($contenido)) {
                $codigo .= '     <option ' . $elegido . '>' . $datos[1] . '</option>';
            }

            /*             * * La lista debe ser generada a partir de un arreglo ** */
        } elseif (is_array($contenido)) {

            foreach ($contenido as $valor => $texto) {

                if ($valor == $valorInicial) {
                    $elegido = 'selected';
                } else {
                    $elegido = '';
                }

                $codigo .= '     <option ' . $elegido . ' value="' . $valor . '">' . $texto . '</option>';
            }
        }

        $codigo .= '     </select>';

        return $codigo;
    }

    /*     * * Generar c�digo HTML para visualizar un bot�n ** */

    static function boton($icono = NULL, $texto = NULL, $clase = NULL, $nombre = NULL, $id = NULL, $accion = NULL, $opciones = NULL) {
        global $configuracion;

        $codigo = '     <button ';

        if (empty($texto)) {
            $claseBoton = 'botonIcono';
        } else {

            if (empty($icono)) {
                $claseBoton = 'botonTexto';
            } else {
                $claseBoton = 'botonTextoIcono';
            }
        }

        if (!empty($nombre) && is_string($nombre)) {
            $codigo .= ' name="' . $nombre . '" ';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($accion) && is_string($accion)) {
            $codigo .= ' onclick="' . $accion . '" ';
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= ' class="' . $claseBoton . ' ' . $clase . '" ';
        } else {
            $codigo .= ' class="' . $claseBoton . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        if (!empty($icono)) {

            if (array_key_exists($icono, $configuracion["ICONOS"])) {
                $icono = 'ui-icon-' . $configuracion["ICONOS"][$icono];
                $codigo .= ' title="' . $icono . '" ';
            }
        }

        $codigo .= '>';

        if (!empty($texto)) {
            $codigo .= $texto;
        }

        $codigo .= '</button>';

        return $codigo;
    }

    /*     * * Generar c�digo HTML para visualizar un bot�n solamente con una imagen** */

    static function botonImagen($ruta = NULL, $title) {
        global $configuracion;

        $codigo = "     <button ";

        $codigo .= " onclick=\"submit\"";

        $codigo .= " title=\"$title\"";

        $codigo .= " class=\"\"";

        $codigo .= ">\n";

        $codigo .= HTML::imagen("$ruta");

        $codigo .= "</button>\n";

        return $codigo;
    }

    /*     * * Generar Bot�n personalizado por mi ** */

    static function botonEstrella($icono = NULL, $texto = NULL, $clase = NULL, $nombre = NULL, $id = NULL, $accion = NULL, $opciones = NULL) {
        global $configuracion;

        $codigo = "     <button ";

        if (empty($texto)) {
            $claseBoton = "botonIcono";
        } else {

            if (empty($icono)) {
                $claseBoton = "botonTexto";
            } else {
                $claseBoton = "botonTextoIcono";
            }
        }

        if (!empty($nombre) && is_string($nombre)) {
            $codigo .= " name=\"$nombre\"";
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= " id=\"$id\"";
        }

        if (!empty($accion) && is_string($accion)) {
            $codigo .= " onclick=\"$accion\"";
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= " class=\"$claseBoton $clase\"";
        } else {
            $codigo .= " class=\"$claseBoton\"";
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= " $atributo=\"$valor\"";
            }
        }

        if (!empty($icono)) {

            if (array_key_exists($icono, $configuracion["ICONOS"])) {
                $icono = "ui-icon-" . $configuracion["ICONOS"][$icono];
                $codigo .= " title=\"$icono\"";
            }
        }

        $codigo .= ">\n";

        if (!empty($texto)) {
            $codigo .= $texto;
        }

        $codigo .= "</button>\n";

        return $codigo;
    }

    /*     * * Generar c�digo HTML para insertar juego de pesta�as de altura variable ** */

    static function pestanas($id, $pestanas) {
        $codigo = '     <div id="' . $id . '" class="pestanas margenInferior">';

        if (is_array($pestanas)) {
            $contador = 0;
            $titulos = '';
            $contenidos = '';

            foreach ($pestanas as $titulo => $contenido) {
                $contador++;
                $titulos .= '      <li id= "li_'.$id.'_'.$contador.'"><a href= "#' . $id . '_' . $contador . '">' . $titulo . '</a></li> ';
                $contenidos .= '      <div id="' . $id . '_' . $contador . '" class= "contenidoPestana"> ';
                $contenidos .= '      ' . $contenido . ' ';
                $contenidos .= '      </div> ';
            }

            $codigo .= '     <ul class="listaPestanas"> ';
            $codigo .= $titulos;
            $codigo .= '     </ul> ';
            $codigo .= $contenidos;
        }

        $codigo .= '     <div class="sombraInferior"></div>';
        $codigo .= '     </div>';

        return $codigo;
    }

    /*     * *  .....Pesta�as modificadas por pablo.....  ** */

    static function pestanas2($id, $pestanas) {
        $codigo = '     <div id="' . $id . '" class="pestanas margenInferior">';


        if (is_array($pestanas)) {
            $contador = 0;
            $titulos = '';
            $contenidos = '';

            foreach ($pestanas as $titulo => $contenido) {
                $contador++;
                $titulos .= '      <li id= "li_'.$id.'_'.$contador.'"><a href="#' . $id . '_' . $contador . '">' . $titulo . '</a></li>';
                $contenidos .= '      <div id="' . $id . '_' . $contador . '" class="contenidoPestana">';
                $contenidos .= '      ' . $contenido . '';
                $contenidos .= '      </div>';
            }

            $codigo .= '     <ul class="listaPestanas">';
            $codigo .= $titulos;
            $codigo .= '     <div class="ladoDerechoRojo"></div></ul>';
            //$codigo .= "     ";
            $codigo .= $contenidos;
        }


        $codigo .= '     </div>';

        return $codigo;
    }

    static function acordeon($titulos, $contenidos, $id = NULL, $claseContenedor = NULL, $claseTitulo = NULL, $claseContenido = NULL, $opciones = NULL, $imagen = NULL) {

        $codigo = '     <div ';

        if (!empty($claseContenedor) && is_string($claseContenedor)) {
            $codigo .= ' class="acordeon ' . $claseContenedor . '"';
        } else {
            $codigo .= ' class="acordeon"';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '"';
            }
        }

        $codigo .= '>';

        for ($i = 0; $i < count($titulos); $i++) {
            //si el titulo es el del chat, entonces si
            if ($imagen["nombre"] == $titulos[$i]) {
                $imagenChat = $imagen["imagen"];
            } else {
                $imagenChat = '';
            }

            $codigo2 = '      <h4';

            if (!empty($clase) && is_string($clase)) {
                $codigo2 .= ' class="' . $clase . '" ';
            }

            $codigo2 .= '><a href="#">' . $titulos[$i] . ' ';
            $codigo2 .= '<div class = "borde"></div></a></h4>' . $imagenChat . ' ';
            $codigo .= '<div class ="acordion">' . $codigo2 . '</div> ';

            $codigo .= '      <div class="contenidoAcordeon">';
            $codigo .= '      ' . $contenidos[$i];
            $codigo .= '      </div> ';
        }

        $codigo .= '     </div>';  //http://cms.template-help.com/prestashop_29958/index.php

        $codigo .= "";
        return $codigo;
    }

    static function acordeonLargo($titulos, $contenidos, $id = NULL, $claseContenedor = NULL, $claseTitulo = NULL, $claseContenido = NULL, $opciones = NULL) {

        $codigo = '     <div ';

        if (!empty($claseContenedor) && is_string($claseContenedor)) {
            $codigo .= ' class="acordeon ' . $claseContenedor . '" ';
        } else {
            $codigo .= ' class="acordeon"';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '" ';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= ' ' . $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= '>';

        for ($i = 0; $i < count($titulos); $i++) {

            $codigo2 = '      <h4 ';

            if (!empty($clase) && is_string($clase)) {
                $codigo2 .= ' class="' . $clase . '" ';
            }

            $codigo2 .= '><a href="#">' . $titulos[$i] . '';
            $codigo2 .= '<div class = "borde"></div></a></h4>';
            $codigo .= '<div class ="acordionLargo">' . $codigo2 . '</div>';

            $codigo .= '      <div class="contenidoAcordeon">';
            $codigo .= '      ' . $contenidos[$i];
            $codigo .= '      </div>';
        }

        $codigo .= '     </div>';

        $codigo .= '';
        return $codigo;
    }

    /**
     *
     * Metodo que muestra un acordeon y lista su contenido con base al tama�o del arreglo del contenido y no de los titulos
     * a diferencia del metodo acordeon
     *
     * */
    static function acordeonLargo2($titulos, $contenidos, $id = NULL, $claseContenedor = NULL, $claseTitulo = NULL, $claseContenido = NULL, $opciones = NULL) {

        $codigo = '     <div ';

        if (!empty($claseContenedor) && is_string($claseContenedor)) {
            $codigo .= ' class="acordeon ' . $claseContenedor . '" ';
        } else {
            $codigo .= ' class="acordeon"';
        }

        if (!empty($id) && is_string($id)) {
            $codigo .= ' id="' . $id . '"';
        }

        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= $atributo . ' = "' . $valor . '" ';
            }
        }

        $codigo .= '>';

        for ($i = 0; $i < count($titulos); $i++) {

            $codigo2 = '      <h4';

            if (!empty($clase) && is_string($clase)) {
                $codigo2 .= ' class= "' . $clase . '" ';
            }

            $codigo2 .= '><a href="#">' . $titulos[$i] . '';
            $codigo2 .= '<div class = "borde"></div></a></h4>';
            $codigo .= '<div class ="acordionLargo">' . $codigo2 . '</div>';

            $codigo .= '      <div class="contenidoAcordeon">';
            for ($j = 0; $j < count($contenidos); $j++) {
                $codigo .= '      ' . $contenidos[$j];
            }
            $codigo .= '      </div>';
        }

        $codigo .= '     </div>';

        $codigo .= '';
        return $codigo;
    }

    static function contenedorCampos($campo1, $campo2) {
        $codigo = '';
        $codigo1 = HTML::contenedor($campo1, 'ancho50Por100 alineadoIzquierda');
        $codigo2 = HTML::contenedor($campo2, 'ancho50Por100 alineadoIzquierda');

        $codigo .= HTML::contenedor($codigo1 . $codigo2, 'contenedorCampos');

        return $codigo;
    }

    static function tabla($columnas, $filas, $clase = NULL, $id = NULL, $claseColumnas = NULL, $claseFilas = NULL, $opciones = NULL) {
        $codigo = "     <table ";

        if (!empty($id) && is_string($id)) {
            $codigo .= " id=\"$id\"";
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= " class=\"$clase\"";
        }
        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= " $atributo=\"$valor\"";
            }
        }

        $codigo .= ">\n";

        if (!empty($columnas)) {
            $codigo .= "     <tr>\n";
            $contador = 0;

            foreach ($columnas as $id => $columna) {
                $codigo .= "     <th";

                if (!empty($id) && is_string($id)) {
                    $codigo .= " id=\"$id\"";
                }

                if (!empty($claseColumnas) && is_array($claseColumnas)) {
                    $codigo .= " class=\"" . $claseColumnas[$contador] . "\"";
                }

                $codigo .= ">\n";
                $codigo .= "$columna</th>\n";
                $contador++;
            }
            $codigo .= "     </tr>\n";
        }

        if (!empty($filas)) {

            foreach ($filas as $fila => $celdas) {
                $codigo .= "     <tr>\n";
                $contador = 0;

                foreach ($celdas as $id => $celda) {
                    $codigo .= "     <td";

                    if (!empty($id) && is_string($id)) {
                        $codigo .= " id=\"$id\"";
                    }

                    if (!empty($claseFilas) && is_array($claseFilas)) {
                        $codigo .= " class=\"" . $claseFilas[$contador] . "\"";
                    }

                    $codigo .= ">\n";
                    $codigo .= "$celda</td>\n";
                    $contador++;
                }

                $codigo .= "     </tr>\n";
            }
        }

        $codigo .= "     </table>";
        return $codigo;
    }

    static function sombra() {
        return "     <div class=\"sombra\"></div>";
    }

    static function mapa($datos, $destino, $ipo = "satellite") {
        $codigo = "
  <script type=\"text/javascript\">
      google.load(\"visualization\", \"1\", {packages:[\"map\"]});
      google.setOnLoadCallback(generarMapa);
      function generarMapa() {
        var datos = new google.visualization.DataTable();
        datos.addColumn(\"number\", \"Lat\");
        datos.addColumn(\"number\", \"Long\");
        datos.addColumn(\"string\", \"Name\");
        datos.addRows([
            $datos
        ]);
        var mapa = new google.visualization.Map(document.getElementById(\"$destino\"));
        mapa.draw(datos, {showTip: true, zoomLevel: 3, mapType: \"$tipo\"});
      }
    </script>
        ";

        return $codigo;
    }

    static function mapaCiudades($datos, $destino, $selectorPais = "") {

        $datos = "[" . implode(",\n", $datos) . "]";
        $codigo = "
  <script type=\"text/javascript\">
    var geocoder;
    var map;

    function iniciarMapas() {
        var latlng      = new google.maps.LatLng(-5, -50);
        var sedes       = $datos;
        var myOptions   = {
          zoom: 2,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById(\"$destino\"), myOptions);

        var companyLogo = new google.maps.MarkerImage('http://media.ablaonline.org/imagen/estaticas/marker.png',
            new google.maps.Size(35,50),
            new google.maps.Point(0,0),
            new google.maps.Point(10,50)
        );

        var companyShadow = new google.maps.MarkerImage('http://media.ablaonline.org/imagen/estaticas/shadow-marker.png',
            new google.maps.Size(50,50),
            new google.maps.Point(0,0),
            new google.maps.Point(10,50)
        );

        for (i=0; i<sedes.length; i++) {

            var myLatlng    = new google.maps.LatLng(sedes[i][0], sedes[i][1]);
            var marker      = new google.maps.Marker({
                map: map,
                position: myLatlng,
                title: sedes[i][2],
                icon: companyLogo,
                shadow: companyShadow
            });
        }
    }


    function createMarker(latlng, html) {
        var contentString = html;
        var marker = new google.maps.Marker({
            position: latlng,
            map: map,
            zIndex: Math.round(latlng.lat()*-100000)<<5
            });

        google.maps.event.addListener(marker, 'click', function() {
            infowindow.setContent(contentString);
            infowindow.open(map,marker);
            });
    }

    function ubicarPais(selector) {
        var pais = document.getElementById(selector).value;

        geocoder.getLocations(pais, function(response) {

            if ((response.Status.code == 200) && (response.Placemark.length > 0)) {
                var box    = response.Placemark[0].ExtendedData.LatLonBox;
                var sw     = new GLatLng(box.south,box.west);
                var ne     = new GLatLng(box.north,box.east);
                var bounds = new GLatLngBounds(sw,ne);
                centerAndZoomOnBounds(bounds);
            }
        });
    }

    function centerAndZoomOnBounds(bounds) {
        var center = bounds.getCenter();
        var newZoom = map.getBoundsZoomLevel(bounds);
            if (map.getZoom() != newZoom) {
            map.setCenter(center, newZoom);
        }   else {
            map.panTo(center);
        }
    }


    </script>
";

        return $codigo;
    }

    static function botonesCompartir() {
        $codigo = "
    <div class=\"addthis_toolbox addthis_default_style\">
     <a class=\"addthis_button_facebook\"></a>
     <a class=\"addthis_button_twitter\"></a>     
     <a class=\"addthis_button_google\"></a>
     <a class=\"addthis_button_email\"></a>
     <a class=\"addthis_button_favorites\"></a>
     <a class=\"addthis_button_print\"></a>
    </div>
        ";

        return $codigo;
    }

    /*   Este es el original
      static function botonesCompartir() {
      $codigo ="
      <div class=\"addthis_toolbox addthis_default_style\">
      <a class=\"addthis_button_facebook\"></a>
      <a class=\"addthis_button_twitter\"></a>
      <a class=\"addthis_button_delicious\"></a>
      <a class=\"addthis_button_google\"></a>
      <a class=\"addthis_button_myspace\"></a>
      <a class=\"addthis_button_email\"></a>
      <a class=\"addthis_button_favorites\"></a>
      <a class=\"addthis_button_print\"></a>
      </div>
      ";

      return $codigo;
      } */




    /*     * * Generar c�digo HTML para insertar un enlace hacia un elemento especifico ** */

    static function urlInterna($modulo, $registro = "", $ajax = false, $accion = "", $categoria = "") {
        global $sql;

        if (empty($modulo)) {
            return NULL;
        }

        $modulo = new Modulo($modulo);

        if (empty($registro) && empty($ajax) && !empty($categoria)) {
            return "/" . $modulo->url . "/category/" . $categoria;
        }

        if (empty($registro) && empty($ajax)) {
            return "/" . $modulo->url;
        }


        if ($registro) {
            return "/" . $modulo->url . "/" . $registro;
        }

        if ($ajax && $accion) {
            return "/ajax/" . $modulo->url . "/" . $accion;
        }
    }

    /**
     *
     * Metodo que se encarga de armar un boton de eliminar Ajax, asignando las clases css
     * "quemadas en el codigo" que le asignar�a el jquery automaticamente si se recargara la p�gina
     *
     * */
    /*     * * Generar c�digo HTML con bot�n y formulario para eliminar un item desde el listado principal haciendo uso de ajax** */
    static function nuevoBotonEliminarItem($id, $url) {
        global $textos;
        $url = preg_replace("|^\/|", "", $url);
        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Delete
                            </span>
                    </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/deleteRegister", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    /**
     *
     * Metodo que se encarga de armar un boton de Modificar Ajax, asignando las clases css
     * "quemadas en el codigo" que le asignar�a el jquery automaticamente si se recargara la p�gina
     *
     * */
    static function nuevoBotonModificarItem($id, $url) {
        global $textos;
        $url = preg_replace("|^\/|", "", $url);

        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Edit
                            </span>
                            </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/editRegister", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    /**
     * Metodos para mostrar los botones ediatr y eliminar que aparecen despues de haber modificado el contenido via Ajax
     */
    static function nuevoBotonEliminarItemInterno($id, $url) {
        global $textos;
        $url = preg_replace("|^\/|", "", $url);
        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Delete
                            </span>
                    </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/delete", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    static function nuevoBotonModificarItemInterno($id, $url) {
        global $textos;
        $url = preg_replace("|^\/|", "", $url);

        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Edit
                            </span>
                            </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/edit", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    static function nuevoBotonModificarUsuarioInterno($id, $url) {

        $url = preg_replace("|^\/|", "", $url);

        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Modify Profile
                            </span>
                            </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/edit", $codigo);
        $codigo = HTML::contenedor($codigo, "alineadoDerecha", "alineadoDerecha");

        return $codigo;
    }

    static function botonConsultarItemDesdeBuscador($id, $url) {
        $url = preg_replace("|^\/|", "", $url);

        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-info'></span>
                            <span class='ui-button-text'>
                                Consult
                            </span>
                            </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url/see", $codigo);
        $codigo = HTML::contenedor($codigo, "botonModificarItemBuscador flotanteDerecha medioMargenDerecha", "botonModificarItemBuscador");

        return $codigo;
    }

    /**
     * 
     * Metodo para mostrar los botones compartir despues de una carga de contenido via Ajax
     * 
     * */
    static function nuevosBotonesCompartir() {

        $codigo = "<div class='botonesCompartir'>     
                     <div class='addthis_toolbox addthis_default_style'>
                         <a class='addthis_button_facebook at300b' title='Send to Facebook' href='#'><span class='at300bs at15nc at15t_facebook'></span></a>
                         <a class='addthis_button_twitter at300b' title='Tweet This' href='#'><span class='at300bs at15nc at15t_twitter'></span></a>     
                         <a class='addthis_button_google at300b' href='http://www.addthis.com/bookmark.php?v=250&amp;winname=addthis&amp;pub=ablaonline&amp;source=tbx-250&amp;lng=en-US&amp;s=google&amp;url=http%3A%2F%2Flocalhost%2Fgames%2F129&amp;title=ABLAOnline%20%3A%3A%20Games&amp;ate=AT-ablaonline/-/-/4ee24d737457e6d4/1&amp;frommenu=1&amp;uid=4ee24d739c11d556&amp;ct=1&amp;pre=http%3A%2F%2Flocalhost%2Fgames&amp;tt=0' target='_blank' title='Send to Google'><span class='at300bs at15nc at15t_google'></span></a>
                         <a class='addthis_button_email at300b' title='Email' href='#'><span class='at300bs at15nc at15t_email'></span></a>
                         <a class='addthis_button_favorites at300b' title='Save to Favorites' href='#'><span class='at300bs at15nc at15t_favorites'></span></a>
                         <a class='addthis_button_print at300b' title='Print' href='#'><span class='at300bs at15nc at15t_print'></span></a>
                     <div class='atclear'></div></div>        
                 </div>";

        return $codigo;
    }

    /**
     * Metodos para mostrar los botones ediatr y eliminar que aparecen despues de haber modificado el contenido via Ajax
     */
    static function nuevoBotonEliminarRegistro($id, $url) {
        global $textos;

        $codigo = "<button id ='nuevoBoton' class='botonTextoIcono ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon' role='button' aria-disabled='false'>
                            <span class='ui-button-icon-primary ui-icon ui-icon-trash'></span>
                            <span class='ui-button-text'>
                                Delete
                            </span>
                    </button>";
        $codigo .= HTML::campoOculto("id", $id);
        $codigo = HTML::forma("/ajax/$url", $codigo);
        $codigo = HTML::contenedor($codigo, "contenedorBotonesLista", "contenedorBotonesLista");

        return $codigo;
    }

    static function armarReproductorAudio() {

        $codigo = "<div id='jquery_jplayer_1' class='jp-jplayer'></div>                    

		<div id='jp_container_1' class='jp-audio'>
                        <div id='logoAblaAudios'></div>
			<div class='jp-type-playlist'>
				<div class='jp-gui jp-interface'>
					<ul class='jp-controls'>
						<li><a href='javascript:;' class='jp-previous' tabindex='1'>previous</a></li>
						<li><a href='javascript:;' class='jp-play' tabindex='1'>play</a></li>
						<li><a href='javascript:;' class='jp-pause' tabindex='1'>pause</a></li>
						<li><a href='javascript:;' class='jp-next' tabindex='1'>next</a></li>
						<li><a href='javascript:;' class='jp-stop' tabindex='1'>stop</a></li>
						<li><a href='javascript:;' class='jp-mute' tabindex='1' title='mute'>mute</a></li>
						<li><a href='javascript:;' class='jp-unmute' tabindex='1' title='unmute'>unmute</a></li>
						<li><a href='javascript:;' class='jp-volume-max' tabindex='1' title='max volume'>max volume</a></li>
					</ul>
					<div class='jp-progress'>
						<div class='jp-seek-bar'>
							<div class='jp-play-bar'></div>
						</div>
					</div>
					<div class='jp-volume-bar'>
						<div class='jp-volume-bar-value'></div>
					</div>
					<div class='jp-time-holder'>
						<div class='jp-current-time'></div>
						<div class='jp-duration'></div>
					</div>
					<ul class='jp-toggles'>
						<li><a href='javascript:;' class='jp-shuffle' tabindex='1' title='shuffle'>shuffle</a></li>
						<li><a href='javascript:;' class='jp-shuffle-off' tabindex='1' title='shuffle off'>shuffle off</a></li>
						<li><a href='javascript:;' class='jp-repeat' tabindex='1' title='repeat'>repeat</a></li>
						<li><a href='javascript:;' class='jp-repeat-off' tabindex='1' title='repeat off'>repeat off</a></li>
					</ul>
				</div>
				<div class='jp-playlist'>
					<ul>
						<li></li>
					</ul>
				</div>
				<div class='jp-no-solution'>
					<span>Update Required</span>
					To play the media you will need to either update your browser to a recent version or update your <a href='http://get.adobe.com/flashplayer/' target='_blank'>Flash plugin</a>.
				</div>
			</div>
		</div>";

        return $codigo;
    }

    static function crearGaleriaFotos($galerias) {
        global $sesion_usuarioSesion, $textos;

        if (!isset($galerias) && !is_array($galerias)) {
            return NULL;
        }

        $codigo = "";

        $codigo .= " <div id='contenedorGalerias' class='contenedorGalerias'>";
        $contador = 0;

        foreach ($galerias as $galeria) {
            if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $galeria->id_usuario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) {
                $datos = array("id" => $galeria->id, "idModulo" => $galeria->id_modulo, "idRegistro" => $galeria->id_registro);
                $codigo .= HTML::botonAjax("masGrueso", $textos->id("AGREGAR_GALERIA"), "/ajax/galeries/add", $datos);
            }
            $codigo .= HTML::parrafo($galeria->titulo, "negrilla");
            $codigo .= HTML::parrafo($galeria->descripcion);
            if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $galeria->id_usuario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) {
                $datos = array("id" => $galeria->id);
                $modificar = HTML::contenedor(HTML::botonAjax("lapiz", $textos->id("MODIFICAR_GALERIA"), "/ajax/galeries/edit", $datos, ""), "alineadoDerecha", "");
                $eliminar = HTML::contenedor(HTML::botonAjax("basura", $textos->id("ELIMINAR_GALERIA"), "/ajax/galeries/delete", $datos, ""), "alineadoDerecha", "");
                $codigo .= HTML::contenedor($eliminar . $modificar, "alineadoDerecha margenSuperiorNegativoTriple", "botonesInternos");
            }
            $codigo .= "<div id = 'galeria_" . $contador . "' class = 'contenedorGaleria'>";

            foreach ($galeria->imagenes as $imagen) {

                $codigo .= "<a href='" . $imagen->imagenPrincipal . "'>
                            <img src='" . $imagen->imagenMiniatura . "' title='" . $imagen->titulo . "' alt='" . $imagen->descripcion . "' />
                        </a>";
            }
            $codigo .= "</div>";

            $contador++;
        }

        $codigo .= "</div>";

        return $codigo;
    }

    static function crearNuevaFila($arregloDatos, $clase = NULL, $id = NULL) {
        if (!isset($arregloDatos) || !is_array($arregloDatos)) {
            return NULL;
        }

        $codigo = "";
        $codigo .= "<tr class='$clase oculto' id='tr_$id' onmouseover=resaltarFila('#tr_$id'); onmouseout=resaltarFila('#tr_$id'); ondblclick=interactuar('#tr_$id');>";

        foreach ($arregloDatos as $valor) {
            $codigo .= "<td class='centrado'> $valor </td>";
        }

        $codigo .= "</tr>";

        return $codigo;
    }

    static function crearFilaAModificar($arregloDatos) {
        if (!isset($arregloDatos) || !is_array($arregloDatos)) {
            return NULL;
        }

        $codigo = "";

        foreach ($arregloDatos as $valor) {
            $codigo .= "<td class='centrado'> $valor </td>";
        }


        return $codigo;
    }

    static function tablaGrilla($columnas, $filas, $clase = NULL, $id = NULL, $claseColumnas = NULL, $claseFilas = NULL, $opciones = NULL, $idFila = NULL, $celdas = NULL) {
        $codigo = "     <table ";

        if (!empty($id) && is_string($id)) {
            $codigo .= " id=\"$id\"";
        }

        if (!empty($clase) && is_string($clase)) {
            $codigo .= " class=\"$clase\"";
        }
        if (!empty($opciones) && is_array($opciones)) {

            foreach ($opciones as $atributo => $valor) {
                $codigo .= " $atributo=\"$valor\"";
            }
        }

        $codigo .= ">\n";

        if (!empty($columnas)) {
            $codigo .= "     <tr class='cabeceraTabla noSeleccionable'>\n";
            $contador = 0;

            foreach ($columnas as $id => $columna) {
                $codigo .= "     <th";

                if (!empty($id) && is_string($id)) {
                    $codigo .= " id=\"$id\"";
                }

                $check = "";
                $organizadores = "";
                $columnaPequena = "columnaPequena";

                if (!empty($celdas) && is_array($celdas)) {//aqui recibo una cadena que trae el nombre del objeto y el nombre para hacer la consulta                        
                    $data = explode("|", $celdas[$contador]);
                    $codigo .= " nombreOrden=\"" . $data[0] . "\""; //en la posicion 0 traigo el nombre del objeto ej: nombreGrupo
                    if ($data[0] != "estado" && $data[0] != "imagen") {
                        $check = HTML::campoChequeo($data[1], false, "checkPatronBusqueda", "checkPatronBusqueda" . ($contador + 1)); //en la posicion 1 traigo el nombre para la consulta
                        $organizadores = "<div id='ascendente'></div> <div id ='descendente'></div>";
                        $columnaPequena = "";
                    }
                }

                if (!empty($claseColumnas) && is_array($claseColumnas)) {
                    $codigo .= " class=\"columnaTabla $columnaPequena " . $claseColumnas[$contador] . "\"";
                }

                $codigo .= ">\n";
                $codigo .= "$organizadores $columna  $check</th>\n";
                $contador++;
            }
            $codigo .= "  </tr>\n";
        }

        if (!empty($filas)) {
            $contador1 = 0;
            foreach ($filas as $fila => $celdas) {
                $codigo .= "     <tr";
                if (!empty($idFila) && is_array($idFila)) {
                    $codigo .= " id=\"" . $idFila[$contador1] . "\"";
                }
                if (!empty($claseFilas)) {
                    $codigo .= " class=\"" . $claseFilas . "\"";
                }
                $codigo .= ">\n";
                $contador = 0;

                foreach ($celdas as $id => $celda) {
                    $codigo .= "     <td";

                    if (!empty($id) && is_string($id)) {
                        $codigo .= " id=\"$id\"";
                    }


                    $codigo .= ">\n";
                    $codigo .= "$celda</td>\n";
                    $contador++;
                }

                $codigo .= "     </tr>\n";
                $contador1++;
            }
        }

        $codigo .= "     </table>";
        return $codigo;
    }

    /**
     *
     * @global type $textos
     * @global type $sql
     * @param type $modulo
     * @param type $permisos
     * @return string 
     */
    static function crearMenuBotonDerecho($modulo, $botones = NULL) {
        global $textos;

        $objeto = new Modulo($modulo);

        $codigo = $consultar = $editar = $borrar = "";
        $ruta = "/ajax/" . $objeto->url;
        $datos = array("id" => "");

        //declaracion de los botones del menu boton derecho
        $consultar = "";
        $editar = "";
        $borrar = "";

        //Verificacion de permisos sobre el boton
//        $puedeEditar  = Perfil::verificarPermisosBoton("botonEditar".ucwords(strtolower($objeto->nombre)));            
//        $puedeBorrar  = Perfil::verificarPermisosBoton("botonBorrar".ucwords(strtolower($objeto->nombre)));  

        $codigo .= "<div id='contenedorBotonDerecho' class='oculto'>";

        $consultar = HTML::formaAjax($textos->id("CONSULTAR"), "contenedorMenuConsultar", "consultar" . ucwords(strtolower($objeto->nombre)), "", $ruta . "/see", $datos);
        $consultar = HTML::contenedor($consultar, "", "botonConsultar" . ucwords(strtolower($objeto->nombre)));


//        if($puedeEditar){
        $editar = HTML::formaAjax($textos->id("MODIFICAR"), "contenedorMenuEditar botonAccion", "editar" . ucwords(strtolower($objeto->nombre)), "", $ruta . "/edit", $datos);
        $editar = HTML::contenedor($editar, "", "botonEditar" . ucwords(strtolower($objeto->nombre)));
//        }
//        if($puedeBorrar){ 
        $borrar = HTML::formaAjax($textos->id("ELIMINAR"), "contenedorMenuEliminar botonAccion", "eliminar" . ucwords(strtolower($objeto->nombre)), "", $ruta . "/delete", $datos);
        $borrar = HTML::contenedor($borrar, "", "botonBorrar" . ucwords(strtolower($objeto->nombre)));
//        }

        $codigo .= $consultar . $editar . $borrar;

        if (isset($botones) && is_array($botones)) {
            foreach ($botones as $boton) {
                $codigo .= $boton;
            }
        }

        $codigo .= "</div>";

        return $codigo;
    }

    static function mapaNuevo() {

        $codigo = "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0' width='670' height='600'>
		 <param name='movie' value='http://media.ablaonline.org/flash/imapbuilder/loader.swf' /><param name='base' value='http://media.ablaonline.org/flash/imapbuilder/' /><param name='flashvars' value='datasource=mapa_v1.xml' />
		 <param name='loop' value='false' /><param name='menu' value='true' /><param name='quality' value='best' /><param name='wmode' value='transparent' />
		 <param name='bgcolor' value='#ffffff' /><param name='allowScriptAccess' value='always' />
		 <object type='application/x-shockwave-flash' data='http://media.ablaonline.org/flash/imapbuilder/loader.swf' width='670' height='600'><param name='movie' value='http://media.ablaonline.org/flash/imapbuilder/loader.swf' />
		 <param name='base' value='http://media.ablaonline.org/flash/imapbuilder/' /><param name='flashvars' value='datasource=mapa_v1.xml' /><param name='loop' value='false' /><param name='menu' value='true' />
		 <param name='quality' value='best' /><param name='wmode' value='transparent' /><param name='bgcolor' value='#ffffff' /><param name='allowScriptAccess' value='always' />
		 </object>
		  </object>";

        return $codigo;
    }






    static function agregarIframe($ruta, $alto, $ancho){
        
        $codigo  = '<iframe src="'.$ruta.'" width="'.$alto.'" height="'.$ancho.'">';
        $codigo .= $textoAlternativo;
        $codigo .= '</iframe>';
        
        return $codigo;
    }



}

?>
