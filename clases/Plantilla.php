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
 * Gestión automática de plantillas de código HTML
 * */
class Plantilla {

    /**
     * Determina si se trata de la página principal
     * @var lógico
     */
    public static $principal = false;

    /**
     * Contenido de la página solicitada
     * @var cadena
     */
    public static $contenido = '';

    /**
     * Etiquetas reemplazables de la plantilla
     * @var arreglo
     */
    public static $etiquetas = array();

    /**
     * Inicializar la plantilla
     */
    public static function iniciar($modulo) {
        global $configuracion, $textos, $sesion_tituloPagina, $sesion_descripcionPagina, $sesion_palabrasClavePagina, $sesion_codificacionPagina, $sesion_iconoPagina, $sesion_pieDePagina;

        if (self::$principal) {
            $plantilla = $configuracion['RUTAS']['plantillas'] . '/' . $configuracion['PLANTILLAS']['principal'];
        } else {
            $plantilla = $configuracion['RUTAS']['plantillas'] . '/' . $configuracion['PLANTILLAS']['interna'];
        }

        if (file_exists($plantilla) && is_readable($plantilla)) {
            self::$contenido = file_get_contents($plantilla);
        }

        preg_match_all('/\{\%(.*)\%\}/', self::$contenido, $etiquetas);

        foreach ($etiquetas[0] as $etiqueta) {
            $nombre = preg_replace('/(\{\%)|(\%\})/', '', $etiqueta);
            self::$etiquetas[$nombre] = '';
        }

        /*         * * Definir el texto para la barra de título del navegador ** */
        (!isset($sesion_tituloPagina)) ? self::$etiquetas['TITULO_PAGINA'] = $configuracion['PAGINA']['titulo'] : self::$etiquetas['TITULO_PAGINA'] = $sesion_tituloPagina;

        /*         * * Definir el texto con la descripción de la página ** */
        (!isset($sesion_descripcionPagina)) ? self::$etiquetas['DESCRIPCION'] = $configuracion['PAGINA']['descripcion'] : self::$etiquetas['DESCRIPCION'] = $sesion_descripcionPagina;

        /*         * * Definir la lista de palabras clave de la página ** */
        (!isset($sesion_palabrasClavePagina)) ? self::$etiquetas['PALABRAS_CLAVE'] = $configuracion['PAGINA']['palabrasClave'] : self::$etiquetas['PALABRAS_CLAVE'] = $sesion_palabrasClavePagina;

        /*         * * Definir el ícono de la página ** */
        (!isset($sesion_codificacionPagina)) ? self::$etiquetas['CODIFICACION'] = $configuracion['PAGINA']['codificacion'] : self::$etiquetas['CODIFICACION'] = $sesion_codificacionPagina;

        /*         * * Definir el ícono de la página ** */
        (!isset($sesion_iconoPagina)) ? self::$etiquetas['ICONO'] = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstaticas'] . '/' . $configuracion['PAGINA']['icono'] : self::$etiquetas['ICONO'] = $sesion_iconoPagina;

        /*         * * Definir el texto del pie de página ** */
        //(!isset($sesion_pieDePagina)) ? self::$etiquetas['PIE_PAGINA'] = $configuracion['PAGINA']['pieDePagina'] : self::$etiquetas['PIE_PAGINA'] = $sesion_pieDePagina;


        $piePagina = '';
        $piePagina .= "<p style='margin-top: -7px; color:#fff !important; font-size:1.1em;' class='negrilla'>&copy; " . date("Y") . " ABLA - All Rights Reserved :: <a href= \"mailto:webmaster@ablaonline.org\" class='letraBlanca subrayado'>webmaster@ablaonline.org</a><p><br /><br/>Developed by <a href=\"http://www.colomboamericano.edu.co\">CENTRO CULTURAL COLOMBO AMERICANO CALI - COLOMBIA</a>";
        $piePagina = HTML::contenedor($piePagina, 'contenidoPiePagina');
        //$piePagina .= HTML::bloquePublicidadInferior();
        self::$etiquetas["PIE_PAGINA"] = $piePagina;


        //self::$etiquetas["BLOQUE_PUBLICIDAD_SUPERIOR"] = HTML::bloquePublicidadSuperior();


        self::$etiquetas['TEXTO_BUSCADOR'] = $textos->id('TEXTO_BUSCADOR');
        self::$etiquetas['TEXTO_ESPERA'] = $textos->id('TEXTO_ESPERA');

        self::cargarEstilos($modulo);
        self::cargarJavaScript($modulo);
        self::cargarAudio();
        self::cargarMenus();
        self::cargarUsuarioSesion();
        //self::cargarCalendarioEventos();
        //self::cargarPauta();
        //self::cargarBotonesRedes();
        self::cargarAsociados();
    }

    /*     * * Incluir referencias a archivos de hojas de estilos (CSS) ** */

    protected static function cargarEstilos($modulo) {
        global $configuracion;

        $estilos = '';

        foreach ($configuracion['ESTILOS']['GENERAL'] as $archivo) {
            $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['estilos'] . '/' . $archivo;
            $estilos .= '   <link href="' . $ruta . '" rel=\'stylesheet\' type=\'text/css\' media=\'screen\' />';
        }

        if (isset($configuracion['ESTILOS'][$modulo->nombre])) {
            foreach ($configuracion['ESTILOS'][$modulo->nombre] as $archivo) {
                $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['estilos'] . '/' . $archivo;
                $estilos .= '   <link href="' . $ruta . '" rel=\'stylesheet\' type=\'text/css\' media=\'screen\' />';
            }
        }

        self::$etiquetas['HOJAS_ESTILOS'] = $estilos;
    }

    /**
     * Metodo que se encargar de llamar los archivos javascript generales 
     * y los que va a usar determinado modulo
     * @global type $configuracion
     * @global type $sesion_usuarioSesion
     * @param type $modulo 
     */
    protected static function cargarJavaScript($modulo) {
        global $configuracion, $sesion_usuarioSesion;

        $JavaScript = '';

        foreach ($configuracion['JAVASCRIPT']['GENERAL'] as $archivo) {

            if (preg_match('|^https?\:\/\/|', $archivo)) {
                $JavaScript .= '  <script type=\'text/javascript\' src="' . $archivo . '"></script>';
            } else {
                $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['javascript'] . '/' . $archivo;
                $JavaScript .= '  <script type=\'text/javascript\' src="' . $ruta . '"></script>';
            }
        }


        if (isset($sesion_usuarioSesion) && !empty($sesion_usuarioSesion->id)) {
            $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['javascript'] . '/chat.js';
            $JavaScript .= '  <script type=\'text/javascript\' src="' . $ruta . '"></script>';
        }

        if (isset($configuracion['JAVASCRIPT'][$modulo->nombre])) {
            foreach ($configuracion['JAVASCRIPT'][$modulo->nombre] as $archivo) {

                if (preg_match('|^https?\:\/\/|', $archivo)) {
                    $JavaScript .= '  <script type=\'text/javascript\' src="' . $archivo . '"></script>';
                } else {
                    $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['javascript'] . '/' . $archivo;
                    $JavaScript .= '  <script type=\'text/javascript\' src="' . $ruta . '"></script>';
                }
            }
        }

        self::$etiquetas['JAVASCRIPT'] = $JavaScript;
    }

    /*     * * Insertar código HTML para mostrar buscador    'style' => 'background-image: url('http://media.ablaonline.local/imagen/estaticas/fondo_input.png')',** */

    protected static function cargarBuscador() {
//        global $textos;
//
//        $buscador = new Modulo('BUSCADOR');
//
//        $opciones = array('onFocus' => '$(this).val("")', 'width' => '100');
//
//        $formaBuscador = HTML::campoTexto('campoBuscador', 18, 255, $textos->id('BUSCAR'), NULL, 'campoBuscador', $opciones);
//        $formaBuscador .= HTML::boton('buscar', $textos->id('BUSCAR'), 'directo');
        // self::$etiquetas['BLOQUE_BUSCADOR'] = HTML::forma($buscador->url, $formaBuscador, 'P', false, 'formaBuscador');
    }

    /*     * * Insertar código HTML para la barra de enlaces corporativos ** */

    protected static function cargarMenus() {
        global $textos, $sql, $sesion_usuarioSesion;

        $enlaces = '';

        /*         * * Adicionar enlace a la página principal cuando no se esté en ella ** */
        if (!empty($_SERVER['REDIRECT_URL'])) {
            $listaInicio[] = HTML::enlace($textos->id('INICIO'), '/');
        }
        //$sql->depurar = true;
        $menus = $sql->seleccionar(array('menus'), array('id', 'nombre', 'destino'), 'id > 0 AND activo = "1"', 'id', 'orden ASC');
        $columna = 0;
        $celdas = array();

        while ($menu = $sql->filaEnObjeto($menus)) {

            $paginas = $sql->seleccionar(array('paginas'), array('id', 'titulo'), 'id > 0 AND activo = "1" AND id_menu = "' . $menu->id . '"', 'id', 'orden ASC');

            $subMenu = array();
            $celda = '';

            while ($fila = $sql->filaEnObjeto($paginas)) {
                $subMenu[] = HTML::enlace($fila->titulo, '/pages/' . $fila->id);
                $celda .= HTML::parrafo(HTML::enlace(HTML::icono('puntaDerecha') . $fila->titulo, '/pages/' . $fila->id));
            }

            if ($menu->destino) {
                $listaInicio[] = HTML::enlace($menu->nombre, $menu->destino);//, 'menuAjax'); //. HTML::lista($subMenu, 'subMenu', 'ui-state-default'); //MENU PRINCIPAL
            } else {
                $columnas[] = HTML::frase(HTML::icono('puntaAbajo') . $menu->nombre);
            }

            $clasesColumnas[] = 'izquierda arriba bordeInferior';
            $clasesFilas[] = 'izquierda arriba';
            $celdas[0][$columna] = $celda;
            $columna++;
        }

        /*         * * Adicionar enlace a la página del perfil del usuario actual cuando no se esté en ella ** */
        if (isset($_SERVER['REQUEST_URI']) && isset($sesion_usuarioSesion->url) && ($_SERVER['REQUEST_URI'] != $sesion_usuarioSesion->url)) {
            $listaInicio[] = HTML::enlace($textos->id('MI_PERFIL'), $sesion_usuarioSesion->url);
        }


        $enlaces .= '<div class="menu-button">Menu</div><nav class="nav_main_menu ">'.HTML::lista($listaInicio, 'flexnav', '', '', array('data-breakpoint' => '800')).'</nav>';
//        $menuAlterno = HTML::tabla($columnas, $celdas, 'centrado', 'tablaMenuAlternativo', $clasesColumnas, $clasesFilas);
//        $menuAlterno .= HTML::contenedor('', 'sombraInferior');

        self::$etiquetas['ENLACES_CORPORATIVOS'] = $enlaces;
        //self::$etiquetas['MENU_ALTERNATIVO'] = HTML::contenedor($menuAlterno, 'ui-state-default');
    }

    /*     * * Insertar código HTML con las opciones para el inicio de sesión del usuario ** */

    protected static function cargarUsuarioSesion() {
        global $sesion_usuarioSesion, $textos, $sql, $configuracion;

        /*         * * El usuario no ha iniciado sesión ** */
        if (!isset($sesion_usuarioSesion)) {

            /*             * * Formulario para el inicio de sesión de usuarios existentes ** */
            $formaUsuarioExistente = HTML::etiqueta($textos->id('USUARIO'));
            $formaUsuarioExistente .= HTML::campoTexto('usuario', 18, 12, '', '', 'campoUsuario');

            $formaUsuarioExistente .= HTML::etiqueta($textos->id('CONTRASENA'));
            $formaUsuarioExistente .= HTML::campoClave('contrasena', '18', 12);
            $claseSlider = 'oculto estiloSlider ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all';
            $formaUsuarioExistente .= HTML::parrafo($textos->id('DESLICE_LA_BARRA'), 'oculto negrilla margenSuperior', 'parrafoMensajeSlider');
            $formaUsuarioExistente .= HTML::parrafo('', 'negrilla margenSuperior', 'parrafoSlider');
            $formaUsuarioExistente .= HTML::contenedor('', $claseSlider, 'sliderInicio');

            $formaUsuarioExistente .= HTML::boton('usuario', $textos->id('INICIAR_SESION'));
            $formaUsuarioExistente = HTML::forma('/ajax/users/validate', $formaUsuarioExistente);
            $formaUsuarioExistente .= '<br>';
            $formaUsuarioExistente .= HTML::enlace($textos->id('RECORDAR_CONTRASENA'), '#', 'enlaceAjax margenSuperior', 'recordarContrasena', array('alt' => '/ajax/users/remind'));
            $formaUsuarioExistente = HTML::contenedor($formaUsuarioExistente, '', 'contenedorCamposLogin');


            $signup = HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'signup.png', 'margenIzquierda');

            $enlaceRegistro = HTML::parrafo(HTML::enlace($textos->id('REGISTRARSE') . $signup, '#', 'enlaceAjax titulo', 'registrarse', array('alt' => '/ajax/users/register')), 'parrafoSignUp');
            $enlaceRegistro = HTML::contenedor($enlaceRegistro, 'enlaceRegistro');

            $login = HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'key_login.png', 'margenIzquierda');

            $enlaceIniciarSesion = HTML::parrafo($textos->id('INICIAR_SESION') . $login, 'titulo parrafoLogin estiloEnlace', 'textoIniciarSesion');
            $enlaceIniciarSesion = HTML::contenedor($enlaceIniciarSesion, 'enlaceRegistro');


            $enlaceRegistroPequeño = HTML::enlace($textos->id('REGISTRARSE'), '#', 'enlaceAjax', 'registrarse', array('alt' => '/ajax/users/register'));
            $enlaceLoginPequeño = HTML::frase($textos->id('INICIAR_SESION'), 'estiloEnlace', 'textoIniciarSesionPequeno');

            $textoUsuarioLogin = str_replace('%1', $enlaceLoginPequeño, $textos->id('TEXTO_USUARIO_LOGIN'));
            $textoUsuarioLogin = str_replace('%2', $enlaceRegistroPequeño, $textoUsuarioLogin);



            $textoANDayahoculto .= HTML::parrafo($textoUsuarioLogin, 'textoUsuarioLogin margenSuperior margenInferior');


            self::$etiquetas['BLOQUE_USUARIO'] = HTML::contenedor($textoANDayahoculto . $enlaceRegistro . $enlaceIniciarSesion . $formaUsuarioExistente, 'bloqueLogueo');


            /*             * * El usuario ya se encuentra autenticado (ha iniciado sesión) ** */
        } else {
            $modulos = $sql->seleccionar(array('modulos'), array('id', 'clase', 'nombre', 'url'), 'visible = "1"', '', 'clase ASC, orden ASC');


            while ($modulo = $sql->filaEnObjeto($modulos)) {
                if (($sesion_usuarioSesion->id != 0 && $modulo->clase != 1) || ($sesion_usuarioSesion->id == 0)) {
                    if ((isset($sesion_usuarioSesion) && Perfil::verificarPermisosConsulta($modulo->id)) || ($sesion_usuarioSesion->id == 0)) {
                        $indice = $textos->id('MODULOS_CLASE_' . $modulo->clase);
                        $textoEnlace = HTML::icono('anguloDerecha');
                        $textoEnlace .= $textos->id($modulo->nombre);
                        if (!isset($lista[$indice])) {
                            $lista[$indice] = '';
                        }
                        $lista[$indice] .= HTML::enlace($textoEnlace, '/' . $modulo->url, 'opcionMenu ui-state-default');
                    }
                }
            }

            $visible = $sql->obtenerValor('usuarios_conectados', 'visible', 'id_usuario = "' . $sesion_usuarioSesion->id . '"');

            if ($visible == '1') {
                $clase = 'imagenMenuChat';
                $texto = $textos->id('DESCONECTAR_CHAT');
            } else {
                $clase = 'imagenMenuChatOff';
                $texto = $textos->id('CONECTAR_CHAT');
            }

            $imagen = HTML::contenedor('', $clase, 'botonActivarChat');
            $imagen .= HTML::frase($texto, 'ayudaEstadoConexionChat', 'ayudaEstadoConexionChat');
            $amigosConectados = HTML::frase(' ' . Contacto::cantidadAmigosConectados(), 'numeroAmigosConectadosMenu', 'numeroAmigosConectadosMenu');
            $nombrePestana = $textos->id('CHAT') . $amigosConectados;
            $lista[$nombrePestana] = HTML::contenedor(Contacto::amigosConectados(), '', 'bloqueContactosConectados_' . $sesion_usuarioSesion->id);

            $amigosConectados1 = HTML::frase(' ' . Contacto::cantidadUsuariosConectados(), 'numeroAmigosConectadosMenu', 'numeroUsuariosConectadosMenu');
            $nombrePestana1 = $textos->id('USUARIOS_CONECTADOS') . $amigosConectados1 . HTML::contenedor('', 'caracteristicaNueva', '', array('ayuda' => $textos->id('NUEVA_CARACTERISTICA_USUARIOS_ONLINE')));
            $lista[$nombrePestana1] = HTML::contenedor(Contacto::usuariosConectados(), 'bloqueUsuariosConectados', 'bloqueUsuariosConectados_' . $sesion_usuarioSesion->id);

            $contenido = HTML::enlace(HTML::imagen($sesion_usuarioSesion->persona->imagenPrincipal, 'imagenPrincipalUsuario margenInferior'), $sesion_usuarioSesion->url);
            $contenido = HTML::contenedor($contenido, 'imagenUsuarioBloqueDerecho', 'imagenUsuarioBloqueDerecho_' . $sesion_usuarioSesion->id);
            $contenido .= HTML::contenedor(HTML::botonModificarItem($sesion_usuarioSesion->id, $sesion_usuarioSesion->urlBase), 'oculto flotanteDerecha');
            $contenido .= HTML::parrafo($sesion_usuarioSesion->centro, 'centrado negrilla margenInferior');
            $contenido .= HTML::forma('/ajax/users/logout', HTML::parrafo(HTML::boton('encender', $textos->id('FINALIZAR')), 'centrado'));

            self::$etiquetas['BLOQUE_USUARIO'] = HTML::bloque('datosUsuarioActual', $sesion_usuarioSesion->sobrenombre, $contenido, '', NULL, '-IS'); //Aqui debo cambiar creo...

            $opciones = array(
                'imagen' => $imagen,
                'nombre' => $nombrePestana
            );

            self::$etiquetas['BLOQUE_USUARIO'] .= HTML::acordeon(array_keys($lista), array_values($lista), 'menuOpcionesUsuario', 'margenInferior izquierda', '', '', '', $opciones);
        }
    }

    /*     * * Insertar código HTML para mostrar calendario de eventos ** */

    protected static function cargarCalendarioEventos() {

        $calendario = HTML::contenedor('', '', 'calendarioEventos');
        $calendario .= HTML::contenedor('', 'sombraInferior');
        $botonCierre = HTML::contenedor('X', 'botonCerrarTooltip', 'botonCerrarTooltip');
        $contenedor = HTML::contenedor('', 'contenedorDiaEventoInterno', 'contenedorDiaEventoInterno');
        $calendario .= HTML::contenedor($botonCierre . $contenedor, 'contenedorDiaEvento oculto', 'contenedorDiaEvento');
        $calendario = HTML::contenedor($calendario, 'calendarioEventos');

        self::$etiquetas['BLOQUE_CALENDARIO_EVENTOS'] = $calendario;
    }

    /*     * * Insertar código HTML para mostrar calendario de eventos ** */

    protected static function cargarPauta() {
        //$banner = Anuncio::mostrarAnuncio();
        //self::$etiquetas['BLOQUE_PAUTA'] = $banner;
    }

    /*     * * Insertar código HTML para mostrar calendario de eventos ** */

    protected static function cargarEncuesta() {
        global $textos;

        $contenido = '<p>¿Are you ready?</p>';
        $contenido .= '<p class=\'margenSuperior\'><input type=\'radio\' name=\'encuesta\'> Yes, I\'m ready</p>';
        $contenido .= '<p class=\'margenSuperior\'><input type=\'radio\' name=\'encuesta\'> No, I\'m not ready</p>';
        $contenido .= '<p class=\'margen\'><input type=\'radio\' name=\'encuesta\'> I don\'t understand</p>';
        $contenido .= HTML::boton('botonVotar', 'botonVotar', 'chequeo', $textos->id('BOTON_VOTAR'));
    }

    protected static function cargarBotonesRedes() {
        global $configuracion;

        $botonRSS = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstaticas'] . '/' . $configuracion['RUTAS']['botonRSS'];
        $botonFacebook = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstaticas'] . '/' . $configuracion['RUTAS']['botonFacebook'];
        $botonTwitter = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstaticas'] . '/' . $configuracion['RUTAS']['botonTwitter'];

        $botones = '<ul id=\'botonesRedes\'>';
        $botones .= '<li><a href=\'/rss.php\'><img alt=\'RSS\' src="' . $botonRSS . '" width="25" height="25"/></a></li>';
        $botones .= '<li><a href=\'http://www.facebook.com/pages/ABLAOnline/347715389619\' target=\'_blank\'><img alt=\'Find us on facebook\' src="' . $botonFacebook . '" width="25" height="25"/></a></li>';
        $botones .= '<li><a href=\'http://www.twitter.com/ablaonline\' target=\'_blank\'><img alt=\'Follow us on twitter\' src="' . $botonTwitter . '" width="25" height="25"/></a></li>';
        $botones .= '</ul>';

        self::$etiquetas['BLOQUE_REDES2'] = HTML::contenedor($botones, 'socialNet');
    }

    protected static function cargarAudio() {
        global $sesion_usuarioSesion;
        $codigo = '';
        if (isset($sesion_usuarioSesion)) {
            $codigo = '<div style="display:none">';

            $codigo .= '<audio id="sonido">';
            $codigo .= '<source src="http://media.ablaonline.org/javascript1/click.wav" type="audio/wav"/>';
            $codigo .= 'Your browser does not support the audio element.';
            $codigo .= '</audio> </div>';

            self::$etiquetas['CODIGO_AUDIO'] = $codigo;
        }
    }

    /**
     * Metodo que retorna un div con el anuncio que esta establecido como activo
     * */
    public static function cargarAsociados() {
        global $sql, $configuracion;

        $codigo = '';
        $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/';

        $tablas = array(
            'a' => 'asociados',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'a.id',
            'idImg' => 'a.id_imagen',
            'vinculo' => 'a.vinculo',
            'idImagen' => 'i.id',
            'ruta' => 'i.ruta',
            'activo' => 'a.activo'
        );

        $condicion = 'a.id_imagen = i.id AND a.activo = "1"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $codigo = '<ul id="listaAsociados">';

            while ($asociado = $sql->filaEnObjeto($consulta)) {
                $codigo .= '
                          <li class="flotanteIzquierda margenIzquierda">
                            
                                <a href = "' . $asociado->vinculo . '" target="_blank">
                                    <img src="' . $ruta . $asociado->ruta . '" class="imagenesAsociados" id="imagenesAsociados"/>
                                </a>
                          
                          </li>
                        ';
            }
            $codigo .= '</ul>';
        }
        self::$etiquetas['ASOCIADOS'] = $codigo;
    }

    /*     * * Enviar código HTML generado al cliente ** */

    public static function generarCodigo() {

        foreach (self::$etiquetas as $etiqueta => $valor) {
            self::$contenido = preg_replace('/\{\%' . $etiqueta . '\%\}/', rtrim($valor), self::$contenido);
        }
    }

}

