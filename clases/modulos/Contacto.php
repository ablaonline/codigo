<?php
/**
 *
 * @package     FOLCS
 * @subpackage  Usuarios
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * */
class Contacto {

    /**
     * Código interno o identificador del usuario en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de usuarios
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un usuario específico
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del tipo de usuario en la base de datos
     * @var entero
     */
    public $id_usuario;

    /**
     * Nombre del tipo de usuario
     * @var cadena
     */
    public $id_contacto;

    /**
     * Estado del usuario
     * @var cadena
     */
    public $estado;

    /**
     * id de la persona que es un usuario
     * @var entero
     * */
    public $idPersona;

    /**
     * Usuario del contacto
     * @var cadena
     */
    public $usuario;

    /**
     * Nombre del contacto
     * @var cadena
     */
    public $sobrenombre;

    /**
     * Persona contacto
     * @var cadena
     */
    public $persona;

    /**
     * Código interno o identificador en la base de datos de la persona con la cual está relacionada el usuario
     * @var entero
     */
    public $idModulo;

    /**
     * Indicador del orden cronológio de la lista de usuarios
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     *
     * Inicializar del contacto
     *
     * @param entero $id Código interno o identificador del usuario en la base de datos
     *
     */
    public function __construct($id_contacto = NULL) {

        $modulo = new Modulo("CONTACTOS");
        $this->urlBase = "/" . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        if (isset($id_contacto) && !empty($id_contacto)) {
            $this->cargar($id_contacto);
        }
    }

    /**
     * Cargar los datos del usuario
     * @param entero $id Código interno o identificador del usuario en la base de datos
     */
    public function cargar($id = NULL) {
        global $sql, $sesion_usuarioSesion;

        if (!isset($id)) {
            return NULL;
        }

        $tablas = array(
            "c" => "lista_contactos"
        );

        $columnas = array(
            "id_usuario" => "c.id_usuario",
            "id_contacto" => "c.id_contacto",
            "id_persona" => "c.id_persona",
            "estado" => "c.estado",
            "usuario" => "c.usuario",
            "sobrenombre" => "c.sobrenombre"
        );


        $condicion = "c.id_usuario = '" . $sesion_usuarioSesion->id . "' AND c.id_contacto = '" . $id . "'";

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $fila = $sql->filaEnObjeto($consulta);

            foreach ($fila as $propiedad => $valor) {
                $this->$propiedad = $valor;
            }

            $this->url = $this->urlBase . "/" . $this->usuario;
            $this->persona = new Persona($this->id_persona);
        }
    }

    /**
     * Metodo que ingresa a la BD la informacion sobre una solicitud de amistad y a su vez
     * notifica a la persona en el sistema ablaonline y en el correo en caso de querer recibir notificaciones al correo
     * sobre dichas solicitudes
     * */
    public function solicitarAmistad($idContacto) {
        global $sql, $sesion_usuarioSesion, $textos;

        if (!isset($idContacto)) {
            return NULL;
        }

        $datos = array(
            "id_usuario_solicitante" => $sesion_usuarioSesion->id,
            "id_usuario_solicitado" => $idContacto,
            "estado" => "0"
        );

        $consulta = $sql->insertar("contactos", $datos);
        if ($consulta) {
            //desea recibir notificacion al correo???
            if (Recursos::recibirNotificacionesAlCorreo($idContacto)) {
                $contacto = new Usuario($idContacto);
                $mensaje = str_replace("%1", $sesion_usuarioSesion->persona->nombreCompleto, $textos->id("QUIERE_SER_TU_AMIGO"));
                Servidor::enviarCorreo($contacto->persona->correo, $mensaje, $mensaje);
            }

            $notificacion = str_replace("%1", HTML::enlace($sesion_usuarioSesion->persona->nombreCompleto, HTML::urlInterna("USUARIOS", $sesion_usuarioSesion->usuario)), $textos->id("QUIERE_SER_TU_AMIGO"));
            Servidor::notificar($idContacto, $notificacion, array(), '1');

            return true;
        } else {
            return false;
        }//fin del if     
    }

    /**
     * Metodo aceptar amistad, modifica  una solicitud de amistad que le hayan hecho al usuario
     * */
    public function aceptarAmistad($idContacto) {
        global $sql, $sesion_usuarioSesion, $textos;

        if (empty($idContacto) || !$sql->existeItem('usuarios', 'id', $idContacto)) {
            return NULL;
        }

        $datos = array(
            "estado" => "1"
        );

        $consulta = $sql->modificar("contactos", $datos, "id_usuario_solicitado = '" . $sesion_usuarioSesion->id . "' AND id_usuario_solicitante = '" . $idContacto . "'");
        if ($consulta) {


            if (Recursos::recibirNotificacionesAlCorreo($idContacto)) {
                $contacto = new Usuario($idContacto);
                $mensaje = str_replace("%1", $sesion_usuarioSesion->persona->nombreCompleto, $textos->id("TE_HA_ACEPTADO_COMO_AMIGO"));
                Servidor::enviarCorreo($contacto->persona->correo, $mensaje, $mensaje);
            }


            $contacto_usuario = $sql->obtenerValor("usuarios", "usuario", "id = '$idContacto'");
            $notificacion1 = str_replace("%1", HTML::enlace($sesion_usuarioSesion->usuario, HTML::urlInterna("USUARIOS", $sesion_usuarioSesion->usuario)), $textos->id("MENSAJE_ADICION_CONTACTO"));
            $notificacion = str_replace("%2", HTML::enlace($contacto_usuario, HTML::urlInterna("USUARIOS", $contacto_usuario)), $notificacion1);

            //Consulto los contactos del usuario con la sesion actual donde él ha sido el usuario solicitante
            $contactos1 = $sql->seleccionar(array("contactos"), array("id_usuario_solicitado"), "id_usuario_solicitante = '" . $sesion_usuarioSesion->id . "' AND id_usuario_solicitado != '" . $idContacto . "' AND estado = '1'", "", "");
            if ($sql->filasDevueltas) {
                while ($contacto_lista = $sql->filaEnObjeto($contactos1)) {
                    Servidor::notificar($contacto_lista->id_usuario_solicitado, $notificacion, array(), '5');
                }
            }

            //Consulto los contactos del usuario con la sesion actual donde él ha sido el usuario solicitado
            $contactos2 = $sql->seleccionar(array("contactos"), array("id_usuario_solicitante"), "id_usuario_solicitante != '" . $idContacto . "' AND id_usuario_solicitado = '" . $sesion_usuarioSesion->id . "' AND estado = '1'", "", "");
            if ($sql->filasDevueltas) {
                while ($contacto_lista = $sql->filaEnObjeto($contactos2)) {
                    Servidor::notificar($contacto_lista->id_usuario_solicitante, $notificacion, array(), '5');
                }
            }

            //Consulto los contactos del usuario que solicitó la amistad donde él ha sido el usuario solicitante
            $contactos3 = $sql->seleccionar(array("contactos"), array("id_usuario_solicitado"), "id_usuario_solicitante = '" . $idContacto . "' AND id_usuario_solicitado != '" . $sesion_usuarioSesion->id . "' AND estado = '1'", "", "");
            if ($sql->filasDevueltas) {
                while ($contacto_lista = $sql->filaEnObjeto($contactos3)) {
                    Servidor::notificar($contacto_lista->id_usuario_solicitado, $notificacion, array(), '5');
                }
            }

            //Consulto los contactos del usuario que solicitó la amistad donde él ha sido el usuario solicitado
            $contactos4 = $sql->seleccionar(array("contactos"), array("id_usuario_solicitante"), "id_usuario_solicitante != '" . $sesion_usuarioSesion->id . "' AND id_usuario_solicitado = '" . $idContacto . "' AND estado = '1'", "", "");
            if ($sql->filasDevueltas) {
                while ($contacto_lista = $sql->filaEnObjeto($contactos4)) {
                    Servidor::notificar($contacto_lista->id_usuario_solicitante, $notificacion, array(), '5');
                }
            }

            return true;
        } else {
            return false;
        }//fin del if        
    }

    /**
     * Metodo rechazar amistad, elimina un registro en la BD contactos donde el usuario solicitado es el usuario actual
     * */
    public function rechazarAmistad($idContacto) {
        global $sql, $sesion_usuarioSesion;

        if (empty($idContacto) || !$sql->existeItem('usuarios', 'id', $idContacto)) {
            return NULL;
        }

        $condicion = "id_usuario_solicitante = '$idContacto' AND id_usuario_solicitado = '$sesion_usuarioSesion->id'";

        $borrar = $sql->eliminar("contactos", $condicion);

        if ($borrar) {
            return true;
        } else {
            return false;
        }//fin del if        
    }

    /**
     * Metodo Eliminar--> Elimina una relacion de amistad entre dos usuarios, borra un registro de la BD de la tabla contactos donde el 
     * id_usuario_solicitante puede ser tanto el usuario de la sesion activa, como otro usuario que haya solicitado su amistad.
     * */
    public function eliminarAmistad($idContacto) {
        global $sql, $sesion_usuarioSesion;

        if (empty($idContacto) || !$sql->existeItem('usuarios', 'id', $idContacto)) {
            return NULL;
        }

        $condicion = "(id_usuario_solicitante = '$idContacto' AND id_usuario_solicitado = '$sesion_usuarioSesion->id') OR (id_usuario_solicitante = '$sesion_usuarioSesion->id' AND id_usuario_solicitado = '$idContacto')";
        $borrar = $sql->eliminar("contactos", $condicion);

        if ($borrar) {
            return true;
        } else {
            return false;
        }//fin del if        
    }

    /**
     * Lista las amistades de el usuario
     * @param entero  $cantidad    Número de usuarios a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de usuarios
     *
     */
    public function listarContactos($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idUsuario = NULL, $condicion2 = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }


        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "lu.id NOT IN ($excepcion)";
        }

        $tablas = array(
            "c" => "contactos",
            "lu" => "lista_usuarios"
        );


        $columnas = array(
            "id_usuario" => "c.id_usuario_solicitante",
            "id_contacto" => "c.id_usuario_solicitado",
            "estado" => "c.estado",
            "id" => "lu.id",
            "usuario" => "lu.usuario",
            "genero" => "lu.genero",
            "codigoIsoPais" => "lu.codigo_iso_pais",
            "nombre" => "lu.nombre",
            "imagen" => "lu.imagen",
            "ciudad" => "lu.ciudad",
            "pais" => "lu.pais",
            "correo" => "lu.correo",
            "centro" => "lu.centro",
            "tipo_usuario" => "lu.tipo_usuario"
        );

        if (!empty($condicion)) {
            $condicion .= " AND ";
        }
        $condicion3 = "";
        if (!empty($condicion2)) {
            $condicion3 = " AND " . $condicion2;
        }

        $condicion .= "(c.id_usuario_solicitante = '" . $idUsuario . "' AND c.estado = '1' AND c.id_usuario_solicitado = lu.id $condicion3) OR (c.id_usuario_solicitado = '" . $idUsuario . "' AND c.estado = '1' AND c.id_usuario_solicitante = lu.id $condicion3)";

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        //$sql->depurar = true;        
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", "nombre ASC", $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($contacto = $sql->filaEnObjeto($consulta)) {
                $contacto->url = $this->urlBase . "/" . $contacto->usuario;
                $contacto->icono = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $contacto->imagen;
                $lista[] = $contacto;
            }
        }

        return $lista;
    }

    /**
     * Lista las amistades de el usuario
     * @param entero  $cantidad    Número de usuarios a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de usuarios
     */
    public function listarSolicitudesAmistad($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idUsuario = NULL) {
        global $sql;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }


        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "lu.id NOT IN ($excepcion)";
        }

        $tablas = array(
            "c" => "contactos",
            "lu" => "lista_usuarios"
        );


        $columnas = array(
            "id_usuario" => "c.id_usuario_solicitante",
            "id_contacto" => "c.id_usuario_solicitado",
            "estado" => "c.estado",
            "id" => "lu.id",
            "usuario" => "lu.usuario",
            "genero" => "lu.genero",
            "codigoIsoPais" => "lu.codigo_iso_pais",
            "nombre" => "lu.nombre",
            "imagen" => "lu.imagen",
            "ciudad" => "lu.ciudad",
            "pais" => "lu.pais",
            "correo" => "lu.correo",
            "centro" => "lu.centro",
            "tipo_usuario" => "lu.tipo_usuario"
        );

        if (!empty($condicion)) {
            $condicion .= " AND ";
        }

        $condicion .= "c.id_usuario_solicitado = '" . $idUsuario . "' AND c.estado = '0' AND c.id_usuario_solicitante = lu.id";

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", "", $inicio, $cantidad);

        $lista = array();

        if ($sql->filasDevueltas) {

            while ($contacto = $sql->filaEnObjeto($consulta)) {
                $contacto->url = $this->urlBase . "/" . $contacto->usuario;
                $lista[] = $contacto;
            }
        }

        return $lista;
    }

    /**
     * Metodo que cuenta todos los contactos de un usuario dterminado
     */
    public static function contarContactos($idUsuario) {
        global $sql;

        if (empty($idUsuario)) {
            return NULL;
        }
        //$sql->depurar = true;
        $consulta = $sql->obtenerValor("contactos", "COUNT(id)", "(id_usuario_solicitante = " . $idUsuario . " AND estado = '1') OR (id_usuario_solicitado = " . $idUsuario . " AND estado = '1')");
        //$consulta = HTML::frase($consulta, "", "cantidadContactos");

        return $consulta;
    }

    /**
     * Metodo para cargar el formulario aceptar amistad
     * */
    public static function formaAceptarAmistad($idContacto) {

        $cod = "";
        $datos = array("id_contacto" => $idContacto);


        $opciones = array(
            "onClick" => "$('#contactosPendientes" . $idContacto . "').fadeOut(500); 
                                        $('#contactosPendientes" . $idContacto . "').remove();
                                        $('#contactosNuevosPendientes" . $idContacto . "').show('drop', {}, 300);
                                        $('#sinContactos').fadeOut(500);
                                        aceptarAmistadJS();",
            "onMouseOver" => "$('#ayudaAmistad" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#ayudaAmistad" . $idContacto . "').hide('drop', {}, 300)"
        );



        $url = HTML::urlInterna("CONTACTOS", "", true, "acceptFriendRequest");

        $ayuda = HTML::contenedor("Click to Accept...", "ayudaAmistad", "ayudaAmistad" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "aceptarAmistad", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");

        $cod .= HTML::contenedor($boton, "aceptarAmistadInterno", "aceptarAmistadInterno");

        return $cod;
    }

    /**
     * Metodo para cargar el formulario aceptar amistad que aparece en el formulario de la ventana modal del buscador
     * cuando se busca a un usuario y este previamente nos ha enviado una solicitud de amistad, esta aparece directamente en el buscador
     * y nos aparece este formulario para aceptar la mistad, o el de rechazar la amistad
     * */
    public static function formaAceptarAmistad2($idContacto) {
        global $textos;

        $cod = "";
        $datos = array("id_contacto" => $idContacto);


        $opciones = array(
            "onClick" => "$('#contactosPendientes" . $idContacto . "').fadeOut(500);
                                      setTimeout(function(){ $('#contactosPendientes" . $idContacto . "').remove(); }, 550);
                                      $('#contactosNuevosPendientes" . $idContacto . "').show('drop', {}, 300);
                                      $('#sinContactos').fadeOut(500);
                                      $(this).fadeOut(500);
                                      $('#textoContactoAceptado').fadeIn(500);
                                      aceptarAmistadJS();",
            "onMouseOver" => "$('#ayudaAmistad2" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#ayudaAmistad2" . $idContacto . "').hide('drop', {}, 300)"
        );


        $url = HTML::urlInterna("CONTACTOS", "", true, "acceptFriendRequest");

        $ayuda = HTML::contenedor($textos->id("CLICK_PARA_ACEPTAR"), "ayudaAmistad", "ayudaAmistad2" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "aceptarAmistad", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");
        $boton .= HTML::frase($textos->id("SOLICITUD_ACEPTADA"), "oculto negrilla", "textoContactoAceptado");
        //$contenido =  HTML::frase("Accept...??", "cantidadDestacados").$boton;

        $cod .= HTML::contenedor($boton, "aceptarAmistadInterno2", "aceptarAmistadInterno");

        return $cod;
    }

    /**
     * Metodo para cargar el formulario aceptar amistad que aparece en el formulario de la ventana modal del buscador
     * */
    public static function formaSolicitudEnviada($idContacto) {
        global $textos;

        $opciones = array(
            "onMouseOver" => "$('#ayudaAmistad" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#ayudaAmistad" . $idContacto . "').hide('drop', {}, 300)"
        );

        $ayuda = HTML::contenedor($textos->id("SOLICITUD_YA_ENVIADA"), "ayudaAmistadM", "ayudaAmistad" . $idContacto, array("style" => "display: none"));

        $cod = HTML::contenedor($ayuda, "solicitudEnviada", "solicitudEnviada", $opciones);

        return $cod;
    }

    /**
     * Metodo para cargar el formulario rechazar amistad
     *
     * */
    public static function formaRechazarAmistad($idContacto) {
        global $textos;

        $cod = "";
        $datos = array("id_contacto" => $idContacto);

        $opciones = array(
            "onClick" => "$('#contactosPendientes" . $idContacto . "').fadeOut(500);
                                      $('#contactosPendientes" . $idContacto . "').remove(500);
                                      rechazarAmistadJS();", //voy a llamar funcion javascript que verifica contactos pendientes, si no queda ninguno elimino el bloque completo
            "onMouseOver" => "$('#rechazaAmistad" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#rechazaAmistad" . $idContacto . "').hide('drop', {}, 300)"
        );


        $url = HTML::urlInterna("CONTACTOS", "", true, "rejectFriendRequest");

        $ayuda = HTML::frase($textos->id("CLICK_PARA_RECHAZAR_SOLICITUD"), "ayudaAmistad", "rechazaAmistad" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "rechazarAmistad", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");

        $cod .= HTML::contenedor($boton, "rechazarAmistadInterno", "rechazarAmistadInterno");

        return $cod;
    }

    /**
     * Metodo para cargar el formulario para eliminar una amistad
     * */
    public static function formaEliminarAmistad($idContacto) {
        global $textos;

        $cod = "";
        $datos = array("id_contacto" => $idContacto);

        $opciones = array(
            "onMouseOver" => "$('#eliminaAmistad" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#eliminaAmistad" . $idContacto . "').hide('drop', {}, 300)"
        );


        $url = HTML::urlInterna("CONTACTOS", "", true, "deleteFriend");

        $ayuda = HTML::contenedor($textos->id("CLICK_PARA_BORRAR_AMISTAD"), "ayudaAmistadL", "eliminaAmistad" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "eliminarAmistad", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");

        $cod .= HTML::contenedor($boton, "eliminarAmistadInterno", "eliminarAmistadInterno");

        return $cod;
    }

    /**
     * Metodo para cargar el formulario para solicitar una amistad
     * */
    public static function formaSolicitarAmistad($idContacto) {
        global $textos;

        $cod = "";
        $datos = array("id_contacto" => $idContacto);

        $opciones = array(
            "onMouseOver" => "$('#solicitaAmistad" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#solicitaAmistad" . $idContacto . "').hide('drop', {}, 300)"
        );


        $url = HTML::urlInterna("CONTACTOS", "", true, "requestFriendship");

        $ayuda = HTML::contenedor($textos->id("CLICK_PARA_SOLICITAR_AMISTAD"), "ayudaAmistadL", "solicitaAmistad" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "solicitarAmistad", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");

        $cod .= HTML::contenedor($boton, "solicitarAmistadInterno", "solicitarAmistadInterno" . $idContacto);


        return $cod;
    }

    /**
     * Metodo para cargar el formulario para enviar un Mensaje
     * Directamente desde la lista de contactos 
     * */
    public static function formaEnviarMensaje($idContacto) {
        global $textos;

        $cod = "";
        $datos = array("id_usuario_destinatario" => $idContacto);

        $opciones = array(
            "onMouseOver" => "$('#enviaMensaje" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#enviaMensaje" . $idContacto . "').hide('drop', {}, 300)"
        );


        $url = HTML::urlInterna("CONTACTOS", "", true, "sendMessage");

        $ayuda = HTML::contenedor($textos->id("CLICK_PARA_ENVIAR_UN_MENSAJE"), "ayudaAmistadL", "enviaMensaje" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "formaEnviarMensaje", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");

        $cod .= HTML::contenedor($boton, "formaEnviarMensajeInterno", "solicitarAmistadInterno" . $idContacto);

        return $cod;
    }

    /**
     * Metodo para cargar el formulario para enviar un Mensaje
     * */
    public static function formaEnviarMensajeDesdeBuscador($idContacto) {
        global $textos;

        $cod = "";
        $datos = array("id_usuario_destinatario" => $idContacto);

        $opciones = array(
            "onMouseOver" => "$('#enviaMensaje2" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#enviaMensaje2" . $idContacto . "').hide('drop', {}, 300)"
        );

        $url = HTML::urlInterna("CONTACTOS", "", true, "sendMessage");

        $ayuda = HTML::contenedor($textos->id("CLICK_PARA_ENVIAR_UN_MENSAJE"), "ayudaAmistadL", "enviaMensaje2" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "formaEnviarMensaje", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");

        $cod .= HTML::contenedor($boton, "formaEnviarMensajeInterno", "solicitarAmistadInterno" . $idContacto);

        return $cod;
    }

    /**
     * Metodo que se encarga de verificar si dos personas tienen establecida una relacion de amistad
     */
    public static function verificarAmistad($idUsuario) {
        global $sql, $sesion_usuarioSesion;

        if (empty($idUsuario) || !isset($sesion_usuarioSesion)) {
            return NULL;
        }

        $usuarioSolicitante = $sql->existeItem("contactos", "id_usuario_solicitante", $idUsuario, "id_usuario_solicitado = '" . $sesion_usuarioSesion->id . "' AND estado = '1'");

        $usuarioSolicitado = $sql->existeItem("contactos", "id_usuario_solicitante", $sesion_usuarioSesion->id, "id_usuario_solicitado = '" . $idUsuario . "' AND estado = '1'");

        if ($usuarioSolicitante || $usuarioSolicitado) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Metodo que se encarga de verificar si el usuario que tiene la sesion ha ENVIADO una solicitud de amistad
     * al usuario que esta viendo
     * @global type $sql
     * @global type $sesion_usuarioSesion
     * @param type $idUsuario entero ->identificador del usuario que se esta observando
     * @return type boolean
     */
    public static function verificarEstadoSolicitudEnviada($idUsuario) {
        global $sql, $sesion_usuarioSesion;

        if (empty($idUsuario) || !isset($sesion_usuarioSesion)) {
            return NULL;
        }

        $usuarioSolicitado = $sql->existeItem("contactos", "id_usuario_solicitante", $sesion_usuarioSesion->id, "id_usuario_solicitado = '" . $idUsuario . "' AND estado = '0'");

        if ($usuarioSolicitado) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Metodo que se encarga de verificar si el usuario que tiene la sesion ha RECIBIDO una solicitud de amistad
     * al usuario que esta viendo
     * @global type $sql
     * @global type $configuracion
     * @global type $sesion_usuarioSesion
     * @param type $idUsuario entero ->identificador del usuario que se esta observando
     * @return type boolean
     */
    public static function verificarEstadoSolicitudRecibida($idUsuario) {
        global $sql, $sesion_usuarioSesion;

        if (empty($idUsuario) || !isset($sesion_usuarioSesion)) {
            return NULL;
        }

        $usuarioSolicitante = $sql->existeItem("contactos", "id_usuario_solicitante", $idUsuario, "id_usuario_solicitado = '" . $sesion_usuarioSesion->id . "' AND estado = '0'");

        if ($usuarioSolicitante) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Metodo que sen encarga de mostrar la cantidad de amigos
     * conectados de la persona que ha iniciado la sesion
     */
    public static function cantidadAmigosConectados() {
        global $sql, $sesion_usuarioSesion;

        $tablas = array(
            "c" => "contactos",
            "uc" => "usuarios_conectados"
        );

        $columnas = array(
            "id_contacto1" => "c.id_usuario_solicitante",
            "id_contacto2" => "c.id_usuario_solicitado",
            "estado" => "c.estado",
            "id_usuario" => "uc.id_usuario",
            "visible" => "uc.visible"
        );

        $condicion = "(c.id_usuario_solicitante = " . $sesion_usuarioSesion->id . " AND c.id_usuario_solicitado = uc.id_usuario AND c.estado = '1' AND uc.visible = '1') OR (c.id_usuario_solicitado = " . $sesion_usuarioSesion->id . " AND c.id_usuario_solicitante = uc.id_usuario AND c.estado = '1' AND uc.visible = '1')";

        //$sql->depurar = true;
        $sql->seleccionar($tablas, $columnas, $condicion);
        if ($sql->filasDevueltas) {
            return $sql->filasDevueltas;
        } else {
            return "0 ";
        }
    }

    /**
     * Metodo que sen encarga de mostrar los amigos
     * conectados de la persona que ha iniciado la sesion,
     * mostrando su foto en miniatura
     */
    public static function amigosConectados() {
        global $sql, $sesion_usuarioSesion, $textos, $configuracion;

        $tablas = array(
            "c" => "contactos",
            "uc" => "usuarios_conectados",
            "u" => "usuarios",
            "p" => "personas",
            "i" => "imagenes"
        );

        $columnas = array(
            "id_contacto1" => "c.id_usuario_solicitante",
            "id_contacto2" => "c.id_usuario_solicitado",
            "estado" => "c.estado",
            "id_usuario" => "uc.id_usuario",
            "visible" => "uc.visible",
            "sobrenombre" => "u.sobrenombre",
            "usuario" => "u.usuario",
            "imagen" => "i.ruta"
        );

        $condicion = "(c.id_usuario_solicitante = " . $sesion_usuarioSesion->id . " AND c.id_usuario_solicitado = uc.id_usuario AND c.estado = '1' AND uc.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND uc.visible = '1') OR (c.id_usuario_solicitado = " . $sesion_usuarioSesion->id . " AND c.id_usuario_solicitante = uc.id_usuario AND c.estado = '1' AND uc.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND uc.visible = '1')";

        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
        if ($sql->filasDevueltas) {
            while ($contacto = $sql->filaEnObjeto($consulta)) {
                $contacto->foto = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $contacto->imagen;
                $lista[] = $contacto;
            }

            foreach ($lista as $elemento) {
                $item = HTML::enlace(HTML::imagen($elemento->foto, "flotanteIzquierda  margenDerecha miniaturaListaChat"), '/users/' . $elemento->usuario);
                $opciones = array("onClick" => "javascript:chatWith('" . $elemento->usuario . "')");
                $item .= HTML::enlace(HTML::frase($elemento->sobrenombre, "claseUsuariosConectados margenSuperior", "usuarioChat_" . $elemento->usuario), "javascript:void(0)", 'margenSuperior', "", $opciones);
                $listaContactos[] = $item;
            }

            $listaContactos = HTML::lista($listaContactos, "listaVertical listaConIconos bordeSuperiorLista", "", "");
            $codigo = HTML::contenedor($listaContactos, "contenedorChat");

            return $codigo;
        } else {
            return $textos->id("NO_HAY_CONTACTOS_CONECTADOS");
        }
    }

    /**
     * Metodo que sen encarga de mostrar la cantidad de usuarios conectados en ABLAOnline
     */
    public static function cantidadUsuariosConectados() {
        global $sql, $sesion_usuarioSesion;

        $tablas = array(
            "uc" => "usuarios_conectados"
        );

        $columnas = array(
            "id_usuario" => "uc.id_usuario",
            "visible" => "uc.visible"
        );

        $condicion = "uc.visible = '1' AND uc.id_usuario != '" . $sesion_usuarioSesion->id . "'";

        //$sql->depurar = true;
        $sql->seleccionar($tablas, $columnas, $condicion);
        if ($sql->filasDevueltas) {
            return $sql->filasDevueltas;
        } else {
            return "0 ";
        }
    }

    /**
     * Metodo que sen encarga de mostrar los usuarios
     * conectados en toda la red social
     */
    public static function usuariosConectados() {
        global $sql, $sesion_usuarioSesion, $textos, $configuracion;

        $tablas = array(
            "uc" => "usuarios_conectados",
            "u" => "usuarios",
            "p" => "personas",
            "i" => "imagenes"
        );

        $columnas = array(
            "id_usuario" => "uc.id_usuario",
            "visible" => "uc.visible",
            "sobrenombre" => "u.sobrenombre",
            "usuario" => "u.usuario",
            "imagen" => "i.ruta"
        );

        $condicion = "uc.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND uc.visible = '1' AND uc.id_usuario != '" . $sesion_usuarioSesion->id . "'";

        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
        if ($sql->filasDevueltas) {
            while ($contacto = $sql->filaEnObjeto($consulta)) {
                $contacto->foto = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $contacto->imagen;
                $lista[] = $contacto;
            }

            foreach ($lista as $elemento) {
                $item = HTML::enlace(HTML::imagen($elemento->foto, "flotanteIzquierda  margenDerecha miniaturaListaChat"), '/users/' . $elemento->usuario);
                $opciones = array("onClick" => "javascript:chatWith('" . $elemento->usuario . "')");
                $item .= HTML::enlace(HTML::frase($elemento->sobrenombre, "claseUsuariosConectados margenSuperior", "usuarioChat_" . $elemento->usuario), "javascript:void(0)", 'margenSuperior', "", $opciones);
                $listaContactos[] = $item;
            }

            $listaContactos = HTML::lista($listaContactos, "listaVertical listaConIconos bordeSuperiorLista", "", "");
            $codigo = HTML::contenedor($listaContactos, "contenedorChat");

            return $codigo;
        } else {
            return $textos->id("NO_HAY_USUARIOS_CONECTADOS");
        }
    }

    /**
     * Metodo que se encarga de mostrar un div con scroll con un listado de tus contactos y checkboxes para enviar un mensaje a multiples
     * contactos a la vez
     * */
    public static function mostrarChecksConMisContactos() {
        global $textos, $sql, $sesion_usuarioSesion;
        $cod = "";
        $codigo = "";

        $tablas = array(
            "c" => "contactos",
            "u" => "usuarios",
            "p" => "personas"
        );

        $columnas = array(
            "id_contacto1" => "c.id_usuario_solicitante",
            "id_contacto2" => "c.id_usuario_solicitado",
            "estado" => "c.estado",
            "usuario" => "u.usuario",
            "nombre" => "CONCAT(p.nombre, ' ', p.apellidos)",
            "id" => "u.id"
        );

        $condicion = "(u.id_persona = p.id AND c.id_usuario_solicitante = " . $sesion_usuarioSesion->id . " AND c.id_usuario_solicitado = u.id AND c.estado = '1') OR (u.id_persona = p.id AND c.id_usuario_solicitado = " . $sesion_usuarioSesion->id . " AND c.id_usuario_solicitante = u.id AND c.estado = '1')";

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $arreglo = array();

            while ($contacto = $sql->filaEnObjeto($consulta)) {
                $arreglo[] = $contacto;
            }
        }

        $cod .= HTML::campoChequeo("", "", "", "marcarTodosLosChecks") . $textos->id("SELECCIONAR_TODOS") . "<br/><br/>";

        foreach ($arreglo as $elemento) {
            $cod .= HTML::campoChequeo("datos[varios_contactos][$elemento->id]", "", "checksContactos") . $elemento->nombre . "<br>";
        }//fin del foreach 


        $codigo .= HTML::contenedor(HTML::parrafo($textos->id("SELECCIONAR_CONTACTOS"), "centrado negrilla") . "<br>" . $cod, "mostrarChecksConMisContactos", "mostrarChecksConMisContactos");

        return $codigo;
    }

}

