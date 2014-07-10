<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Usuarios
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón , William Vargas
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * 
 * Modificado el 20-01-12
 *
 **/
global $url_accion, $forma_usuario, $forma_contrasena, $forma_datos, $forma_procesar, $forma_id, $url_cadena;

if (isset($url_accion)) {
    switch ($url_accion) {
        case "validate"         :   validarUsuario($forma_usuario, $forma_contrasena, $forma_datos);
                                    break;
        case "remind"           :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    recordarContrasena($datos);
                                    break;
        case "register"         :   registrarUsuario($forma_datos);
                                    break;
        case "logout"           :   cerrarSesion();
                                    break;
        case "edit"             :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    modificarUsuario($forma_id, $datos);
                                    break;
        case "delete"           :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarUsuario($forma_id, $confirmado);
                                    break;
        case "searchUsers"      :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    buscarUsuarios($forma_datos);
                                    break;
        case "sendMessage"      :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    enviarMensaje($forma_datos);
                                    break;
	case "replyMessage"     :   ($forma_procesar) ? $datos = $forma_datos : $datos = $forma_id;
                                    responderMensaje($datos);
                                    break;
        case "deleteMessage"    :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarMensaje($forma_id, $confirmado);
                                    break;
        case "deleteAllMessages" :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarVariosMensajes($forma_datos, $confirmado);
                                    break;
        case "deleteNotifications" :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarVariasNotificaciones($forma_datos, $confirmado);
                                    break;                                
        case "readMessage"      :   leerMensaje($forma_id);
                                    break;
        case "listContacts"     :   listarContactos($url_cadena);
                                    break;    
	case "comment"          :   comentariosItem($forma_modulo, $forma_registro, $forma_propietario);
				    break;
	case "addComment"       :   adicionarComentario($forma_modulo, $forma_registro, $forma_contenido);
				    break;
        case "deleteComment"    :   eliminarComentario($forma_id);
                                    break;
        case "listContactsFromInput" :   listarContactosDesdeCampo($url_cadena);
                                    break;  
        case 'contactBncWebmaster'  :    ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              enviarMensajeBncWebmaster($datos);
                               break;
       
    }
}

/**
 *
 * @global type $textos
 * @global type $sesion_ipUsuario
 * @param type $usuario
 * @param type $contrasena
 * @param type $datos 
 */
function validarUsuario($usuario, $contrasena, $datos) {
    global $textos, $sesion_ipUsuario;

    $respuesta = array();
    $respuesta["error"]      = true;
    $respuesta["objetivo"]   = "activarSlider";


    if (empty($usuario)) {
        $respuesta["mensaje"] = $textos->id("ERROR_USUARIO_REQUERIDO");
        Recursos::registrarError();
    } elseif (empty($contrasena)) {
        $respuesta["mensaje"] = $textos->id("ERROR_CONTRASENA_REQUERIDA");
        Recursos::registrarError();
        Recursos::registrarErrorDeUsuario($usuario);
    } else {
        $idExistente      = Usuario::validar($usuario, $contrasena);
        $usuarioBloqueado = Usuario::validarUsuarioBloqueado($usuario);

        if (is_null($idExistente)) {
            $respuesta["mensaje"] = $textos->id("ERROR_USUARIO_INVALIDO");
            Recursos::registrarError();
            Recursos::registrarErrorDeUsuario($usuario);

        } elseif ($usuarioBloqueado) {
            $respuesta["mensaje"] = $textos->id("ERROR_USUARIO_BLOQUEADO_REVISAR_CORREO");

        } elseif ($idExistente === -1) {
            $respuesta["mensaje"] = $textos->id("ERROR_USUARIO_INACTIVO");
            Recursos::registrarError();

        } elseif ( isset($sesion_ipUsuario) && $datos["acumulador"] <= 15 ) {
            $respuesta["mensaje"] = $textos->id("DESLICE_LA_BARRA");

        } else {
            $usuarioSesion        = new Usuario($usuario);
            
            Sesion::registrar("usuarioSesion", $usuarioSesion);
            Sesion::registrar("username", $usuarioSesion->usuario);
            Usuario::conectarUsuario();

            $respuesta["error"]    = NULL;
            $respuesta["accion"]   = "redireccionar";
            $respuesta["destino"]  = $usuarioSesion->url;
            
        }
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $textos
 * @global type $configuracion
 * @param type $datos 
 */
function registrarUsuario($datos = array()) {
    global $textos,  $configuracion, $sql;

    $user      = new Usuario();

    $destino   = "/ajax".$user->urlBase."/register";
    $respuesta = array();

    if (empty($datos)) {        
        
	/*** Formulario para el registro por parte de usuarios ***/
	$formaUsuarioNuevo  = HTML::contenedor("", "oculto", "mensajeUsuarioNuevo");

	$nombres  = HTML::parrafo($textos->id("NOMBRES"), "negrilla");
	$nombres .= HTML::campoTexto("datos[nombre]",30,50, "", "campoFormaRegistro");

	$apellidos  = HTML::parrafo($textos->id("APELLIDOS"), "negrilla");
	$apellidos .= HTML::campoTexto("datos[apellidos]",30,50, "", "campoFormaRegistro");

	$formaUsuarioNuevo .= HTML::contenedorCampos($nombres, $apellidos);

	$correo  = HTML::parrafo($textos->id("CORREO"), "negrilla");
	$correo .= HTML::campoTexto("datos[correo]",30,255, "", "campoFormaRegistro");

	$username  = HTML::parrafo($textos->id("USUARIO"), "negrilla");
	$username .= HTML::campoTexto("datos[usuario]",30,12, "", "campoFormaRegistro");


	$formaUsuarioNuevo .= HTML::contenedorCampos($correo, $username);

	$contrasena  = HTML::parrafo($textos->id("CONTRASENA"), "negrilla");
	$contrasena .= HTML::campoClave("datos[contrasena1]",30,12, "", "campoFormaRegistro");

	$confirmarContrasena  = HTML::parrafo($textos->id("CONFIRMAR_CONTRASENA"), "negrilla");
	$confirmarContrasena .= HTML::campoClave("datos[contrasena2]",30,12, "", "campoFormaRegistro");

	$formaUsuarioNuevo .= HTML::contenedorCampos($contrasena, $confirmarContrasena);

	$formaUsuarioNuevo .= HTML::parrafo($textos->id("CENTRO_BINACIONAL"), "negrilla");
	$formaUsuarioNuevo .= HTML::campoTexto("datos[id_centro]", 35, 255, "", "autocompletable campoFormaRegistro", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCenters"), "alt" => $textos->id("SELECCIONE_CENTRO_BINACIONAL")));


	$formaUsuarioNuevo .= HTML::parrafo($textos->id("VERIFICACION_HUMANO"), "negrilla margenSuperior");
	$formaUsuarioNuevo .= HTML::contenedor('', 'numero5').HTML::contenedor(HTML::frase(' + ', 'titulo').HTML::campoTexto("datos[verificacion_humano]",4,2, "", "campoFormaRegistro").HTML::frase(' = ', 'titulo'), 'contenedorCampoVerificacionHumano').HTML::contenedor('', 'numero9');


	$opciones = array("type" => "submit");
	$formaUsuarioNuevo .= HTML::parrafo(HTML::boton("chequeo", HTML::frase($textos->id("REGISTRARSE"), 'titulo'), "", "", "", "", $opciones), "parrafoBotonInicio");
	
	$formaUsuarioNuevo  = HTML::contenedor(HTML::forma("/ajax/users/register", $formaUsuarioNuevo), "margenIzquierda");

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $formaUsuarioNuevo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("REGISTRAR_USUARIO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 540;
        $respuesta["alto"]    = 500;

    } else {

	$respuesta["error"]   = true;

	// Use the AYAH object to get the score.
	//aqui documento
	//$score = $ayah2->scoreResult();

	if (empty($datos["verificacion_humano"])) {
	    $respuesta["mensaje"] = $textos->id("ERROR_VERIFICACION_HUMANO_VACIO");

	} elseif (!empty($datos["verificacion_humano"]) && $datos["verificacion_humano"] != '4') {
	    $respuesta["mensaje"] = $textos->id("ERROR_CIFRA_VERIFICACION_HUMANO");

	} elseif (empty($datos["nombre"])) {
	    $respuesta["mensaje"] = $textos->id("ERROR_NOMBRE_REQUERIDO");

	} elseif (empty($datos["apellidos"])) {
	    $respuesta["mensaje"] = $textos->id("ERROR_APELLIDOS_REQUERIDOS");

	} elseif (empty($datos["usuario"])) {
	    $respuesta["mensaje"] = $textos->id("ERROR_USUARIO_REQUERIDO");

	} elseif (empty($datos["contrasena1"])) {
	    $respuesta["mensaje"] = $textos->id("ERROR_CONTRASENA_REQUERIDA");

	} elseif (empty($datos["contrasena2"])) {
	    $respuesta["mensaje"] = $textos->id("ERROR_CONTRASENA2_REQUERIDA");

	} elseif (empty($datos["correo"])) {
	    $respuesta["mensaje"] = $textos->id("ERROR_CORREO_REQUERIDO");

	} elseif (isset($datos["usuario"]) && $sql->existeItem("usuarios", "usuario", $datos["usuario"])) {
	    $respuesta["mensaje"] = $textos->id("ERROR_USUARIO_EXISTENTE");

	} elseif ($datos["contrasena1"] != $datos["contrasena2"]) {
	    $respuesta["mensaje"] = $textos->id("ERROR_CONTRASENAS_DIFERENTES");

	} elseif (strlen($datos["nombre"]) < $configuracion["GENERAL"]["longitudMinimaNombre"]) {
	    $respuesta["mensaje"] = $textos->id("ERROR_NOMBRE_CORTO");

	} elseif (strlen($datos["apellidos"]) < $configuracion["GENERAL"]["longitudMinimaApellido"]) {
	    $respuesta["mensaje"] = $textos->id("ERROR_APELLIDO_CORTO");

	} elseif (strlen($datos["usuario"]) < $configuracion["GENERAL"]["longitudMinimaUsuario"]) {
	    $respuesta["mensaje"] = $textos->id("ERROR_USUARIO_CORTO");

	} elseif (strlen($datos["contrasena1"]) < $configuracion["GENERAL"]["longitudMinimaContrasena"]) {
	    $respuesta["mensaje"] = $textos->id("ERROR_CONTRASENA_CORTA");

	} elseif (preg_match("/[^a-z0-9]/", $datos["usuario"])) {
	    $respuesta["mensaje"] = $textos->id("ERROR_SINTAXIS_USUARIO");

	} elseif (!filter_var($datos["correo"], FILTER_VALIDATE_EMAIL)) {
	    $respuesta["mensaje"] = $textos->id("ERROR_SINTAXIS_CORREO");

	} elseif (isset($datos["correo"]) && $sql->existeItem("personas", "correo", $datos["correo"])) {
	    $respuesta["mensaje"] = $textos->id("ERROR_CORREO_EXISTENTE");

	} 
	//aqui documento
	/*elseif (!$score) {
	    $respuesta["mensaje"] = $textos->id("ERROR_DEBES_COMPLETAR_EL_JUEGO");

	}*/

	else {

	    $nuevoUsuario = $user->registrar($datos);

	    if ($nuevoUsuario) {

		$usuarioSesion        = new Usuario($nuevoUsuario);
		
		Sesion::registrar("usuarioSesion", $usuarioSesion);
		Sesion::registrar("username", $usuarioSesion->usuario);
		Usuario::conectarUsuario();
		
		$respuesta["error"]      = false;
		$respuesta["mensaje"]    = HTML::frase($textos->id("REGISTRO_USUARIO_EXITOSO"), "letraVerdeGrande");
		$respuesta["errorExito"] = true;
		$respuesta["accion"]     = "redireccionar";
		$respuesta["destino"]    = $usuarioSesion->url;



	    } else {
		$respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
	    }
	}
    }

    Servidor::enviarJSON($respuesta);
} //Fin del metodo de adicionar blogs




/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $datos 
 */
function recordarContrasena($datos) {
    global $textos, $sql;

    $usuario   = new Usuario();
    $destino   = "/ajax".$usuario->urlBase."/remind";
    $respuesta = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("CORREO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[correo]", 40, 255);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("RECORDAR_CONTRASENA"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 330;
        $respuesta["alto"]    = 150;

    } else {
        $respuesta["error"] = true;

        if (!filter_var($datos["correo"], FILTER_VALIDATE_EMAIL)) {
            $respuesta["mensaje"] = $textos->id("ERROR_SINTAXIS_CORREO");

        } elseif (isset($datos["correo"]) && !$sql->existeItem("personas", "correo", $datos["correo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_USUARIO_INEXISTENTE");

        } else {
            $tablas = array(
                "u" => "usuarios",
                "p" => "personas"
            );

            $columnas = array(
                "id"        => "u.id",
                "usuario"   => "u.usuario",
                "nombre"    => "p.nombre",
                "apellidos" => "p.apellidos"
            );


            $condicion  = "u.id_persona = p.id AND p.correo = '".$datos["correo"]."'";
	    
	    $consulta   = $sql->seleccionar($tablas, $columnas, $condicion);
            $usuario    = $sql->filaEnObjeto($consulta);
            $contrasena = substr(md5(uniqid(rand(), true)), 0, 8);
	    //$sql->depurar = true;
            $sql->modificar("usuarios", array("contrasena" => md5($contrasena)), "id = '".$usuario->id."'");
            $mensaje    = str_replace("%1", $datosPersona["nombre"], $textos->id("CONTENIDO_MENSAJE_CONTRASENA"));
            $mensaje    = str_replace("%2", $usuario->usuario, $mensaje);
            $mensaje    = str_replace("%3", $contrasena, $mensaje);
            Servidor::enviarCorreo($datos["correo"], $textos->id("ASUNTO_MENSAJE_CONTRASENA"), $mensaje, $usuario->nombre." ".$usuario->apellidos);

            $respuesta["error"]   = NULL;
            $respuesta["errorExito"]    = true;
            $respuesta["mensaje"] = $textos->id("CONTRASENA_ENVIADA");
            $respuesta["destino"] = "#cuadroDialogo";
            $respuesta["accion"]  = "cerrar";
        }
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $archivo_imagen
 * @global type $sesion_usuarioSesion
 * @global type $configuracion
 * @param type $id
 * @param type $datos 
 */
function modificarUsuario($id, $datos = array()) {
    global $textos, $sql, $archivo_imagen, $sesion_usuarioSesion, $configuracion;

    $usuario   = new Usuario($id);
    $destino   = "/ajax".$usuario->urlBase."/edit";
    $respuesta = array();

    if (empty($datos)) {
        $imagen    = HTML::imagen($configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."warning_blue.png", "oculto");
        $pestana1  = HTML::campoOculto("procesar", "true");
        $pestana1 .= HTML::campoOculto("id", $id);
        $pestana1 .= HTML::parrafo("*".$textos->id("NOMBRES"), "negrilla margenSuperior");
        $pestana1 .= HTML::campoTexto("datos[nombre]", 30, 50, $usuario->persona->nombre, "", "", array("title" => $textos->id("")));
        $pestana1 .= HTML::parrafo("*".$textos->id("APELLIDOS"), "negrilla margenSuperior");
        $pestana1 .= HTML::campoTexto("datos[apellidos]", 30, 50, $usuario->persona->apellidos);
        $pestana1 .= HTML::parrafo("*".$textos->id("SOBRENOMBRE"), "negrilla margenSuperior");
        $pestana1 .= HTML::campoTexto("datos[sobrenombre]", 30, 50, $usuario->sobrenombre, "sobrenombre", "sobrenombre", array("ayuda" => $textos->id("INGRESE_SU_SOBRENOMBRE")));
        $pestana1 .= HTML::parrafo("*".$textos->id("GENERO"), "negrilla margenSuperior");
        $pestana1 .= HTML::listaDesplegable("datos[genero]", array("M" => $textos->id("GENERO_M"), "F" => $textos->id("GENERO_F")), $usuario->persona->idGenero, "", "", "", array("ayuda" => $textos->id("SELECCIONE_GENERO")));
        $pestana1 .= HTML::parrafo($textos->id("ACERCA_DE_USUARIO"), "negrilla margenSuperior");
        $pestana1 .= HTML::campoTexto("datos[descripcion]", 40, 255, $usuario->persona->descripcion);
        $pestana1 .= HTML::parrafo($textos->id("PAGINA_WEB"), "negrilla margenSuperior");
        $pestana1 .= HTML::campoTexto("datos[pagina_web]", 40, 255, $usuario->persona->paginaWeb);

        if ($usuario->persona->ciudadNatal) {
            $ciudadNatal = $usuario->persona->ciudadNatal.", ".$usuario->persona->estadoNatal.", ".$usuario->persona->paisNatal;
        }

        $pestana2 .= HTML::parrafo($textos->id("FECHA_NACIMIENTO"), "negrilla margenSuperior");
        $pestana2 .= HTML::campoTexto("datos[fecha_nacimiento]", 12, 12, $usuario->persona->fechaNacimiento, "fechaAntigua", "", array("ayuda" => $textos->id("SELECCIONE_FECHA_NACIMIENTO")));
        $pestana2 .= HTML::parrafo($textos->id("CIUDAD_NACIMIENTO"), "negrilla margenSuperior");
        $pestana2 .= HTML::campoTexto("datos[id_ciudad_nacimiento]", 50, 255, $ciudadNatal, "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCities"), "ayuda" => $textos->id("SELECCIONE_CIUDAD_NACIMIENTO")));

        if ($usuario->persona->ciudadResidencia) {
            $ciudadResidencia = $usuario->persona->ciudadResidencia.", ".$usuario->persona->estadoResidencia.", ".$usuario->persona->paisResidencia;
        }

        if ($usuario->centro) {
            $centro = $usuario->centro.", ".$usuario->ciudadCentro;
        }

        $pestana3  = HTML::parrafo("*".$textos->id("CIUDAD_RESIDENCIA"), "negrilla margenSuperior");
        $pestana3 .= HTML::campoTexto("datos[id_ciudad_residencia]", 50, 255, $ciudadResidencia, "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCities"), "ayuda" => $textos->id("SELECCIONE_CIUDAD_RESIDENCIA")));
        $pestana3 .= HTML::parrafo($textos->id("INGRESE_CENTRO_BINACIONAL"), "negrilla margenSuperior");
        $pestana3 .= HTML::campoTexto("datos[id_centro]", 50, 255, $centro, "autocompletable", "", array("title" => HTML::urlInterna("INICIO",0,true,"listCenters"), "ayuda" => $textos->id("SELECCIONE_CENTRO_BINACIONAL")));

        $pestana4  = HTML::parrafo($textos->id("FOTOGRAFIA_USUARIO"), "negrilla margenSuperior");
        $pestana4 .= HTML::campoArchivo("imagen", 40, 255, "");

        $pestana5  = HTML::parrafo("*".$textos->id("CORREO"), "negrilla margenSuperior");
        $pestana5 .= HTML::campoTexto("datos[correo]", 30, 50, $usuario->persona->correo);
        $pestana5 .= HTML::parrafo($textos->id("USUARIO").": ".$usuario->usuario, "negrilla margenSuperior");
        $pestana5 .= HTML::parrafo($textos->id("CONTRASENA"), "negrilla margenSuperior");
        $pestana5 .= HTML::campoClave("datos[contrasena1]", 10, 12, "", "", "contrasena1", array("ayuda" => $textos->id("INGRESE_SU_CONTRASENA")));
        $pestana5 .= HTML::parrafo($textos->id("CONFIRMAR_CONTRASENA"), "negrilla margenSuperior");
        $pestana5 .= HTML::campoClave("datos[contrasena2]", 10, 12, "", "", "contrasena1", array("ayuda" => $textos->id("REPITA_CONTRASENA")));
        $pestana5 .= HTML::parrafo(HTML::campoChequeo("datos[notificaciones]", $usuario->notificaciones).$textos->id("RECIBIR_NOTIFICACIONES_CORREO"), "margenSuperior");

	if($usuario->idCentro == 0){
	  $pestana5 .= HTML::parrafo($textos->id("ESCOGER_CENTRO_PARA_CONTACTAR_WEBMASTER"), 'negrita margenSuperior');
	}else{
	  $enlaceContacto = HTML::frase($textos->id('AQUI'), 'estiloEnlace enlaceAjax', '', array('alt' => '/ajax/users/contactBncWebmaster'));
	  $pestana5 .= HTML::parrafo(str_replace('%1', $enlaceContacto, $textos->id("TEXTO_EXPLICACION_CONTACTAR_WEBMASTER")), 'negrita margenSuperior');
	}


        //verificar si el usuario es un administrador de centro
        $adminCentro = $sql->existeItem("admin_centro", "id_usuario", $sesion_usuarioSesion->id);
        $idCentro = "";
        if($adminCentro){
            $idCentro = $sql->obtenerValor("admin_centro", "id_centro", "id_usuario = '".$sesion_usuarioSesion->id."'");
        }
        
        if (  (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0 ) || ( $usuario->idCentro == $idCentro )  ) {
            //$pestana5 .= HTML::parrafo($textos->id("PERFIL_ACTUAL").": \"".$usuario->tipo."\".<br>", "subrayado negrilla margenSuperior");
            $pestana5 .= HTML::parrafo($textos->id("MODIFICAR_PERFIL"), "negrilla margenSuperior");
            //$pestana5 .= HTML::campoTexto("datos[tipo]", 30, 50, $usuario->tipo);
            $tipos = $sql->seleccionar(array("tipos_usuario"), array("id", "nombre", "visibilidad"), "id IS NOT NULL", "id", "orden DESC");

            //lista desplegable para la seleccion de perfil
            while ($tipo = $sql->filaEnObjeto($tipos)) {
                if($tipo->visibilidad == '0' && $sesion_usuarioSesion->id == 0){
                    $perfil[$tipo->id] = $tipo->nombre;
                }elseif($tipo->visibilidad == '1'){
                    $perfil[$tipo->id] = $tipo->nombre;
                }
            }
            $pestana5 .= HTML::listaDesplegable("datos[id_tipo]", $perfil, $usuario->idTipo, "listaSeleccionaPerfil", "listaSeleccionaPerfil");

            //aqui verifico si el perfil es el de administrador de centro y ejecuto las labores que son
            $centroAdmin = "";
            if ($usuario->idTipo == 2) {
                $adminCentro = $sql->obtenerValor("admin_centro", "id_centro", "id_usuario = '".$usuario->id."'");
                $clase = "";
                if ($adminCentro) {
                    $centroAdmin = $sql->obtenerValor("lista_centros", "nombre", "id = '" . $adminCentro . "'");
                }
            } else {
                $clase = "oculto";
                $centroAdmin = "";
            }

            $cod = HTML::parrafo($textos->id("INGRESE_CENTRO_BINACIONAL_A_ADMINISTRAR"), "negrilla margenSuperior");
            $cod .= HTML::campoTexto("datos[id_centro_admin]", 50, 255, $centroAdmin, "autocompletable", "", array("title" => HTML::urlInterna("INICIO", 0, true, "listCenters"), "ayuda" => $textos->id("SELECCIONE_CENTRO_BINACIONAL")));
            $cod = HTML::contenedor($cod, "$clase margenSuperior margenInferior", "contenedorAdminCentro");
            $pestana5 .= $cod;
            $pestana5 .= HTML::parrafo(HTML::campoChequeo("datos[activo]", $usuario->activo) . $textos->id("ACTIVO"), "margenSuperior");
        }//Fin del if(es el admin)

        $pestanas = array(
            HTML::frase($textos->id("INFORMACION_PERSONAL"), "letraBlanca").$imagen => $pestana1,
            HTML::frase($textos->id("FECHA_NACIMIENTO"), "letraBlanca")     => $pestana2,
            HTML::frase($textos->id("UBICACION"), "letraBlanca")            => $pestana3,
            HTML::frase($textos->id("IMAGEN"), "letraBlanca")               => $pestana4,
            HTML::frase($textos->id("CONTROL_ACCESO"), "letraBlanca")       => $pestana5,
        );

        $codigo .= HTML::pestanas2("", $pestanas);

//        $opciones = array("onClick" => "validarFormaEditarUsuario();");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("GUARDAR_CAMBIOS"), "directo", "", "botonFormaEditarUsuarios", "", $opciones).HTML::frase("     ".$textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso"), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo, "P", true, "formaEditarUsuario", "", "formaEditarUsuario");

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_USUARIO"), "letraNegra negrilla"), "bloqueTitulo"), "encabezadoBloque");
        $respuesta["ancho"]   = 700;
        $respuesta["alto"]    = 550;

    } else {
        $respuesta["error"]   = true;
        
        $existeCiudadResidencia = $sql->existeItem("lista_ciudades", "cadena", $datos["id_ciudad_residencia"]);

        if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif (empty($datos["apellidos"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_APELLIDOS");

        } elseif (empty($datos["sobrenombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_SOBRENOMBRE");

        } elseif (strlen($datos["nombre"]) < $configuracion["GENERAL"]["longitudMinimaNombre"]) {
            $respuesta["mensaje"] = $textos->id("ERROR_NOMBRE_CORTO");

        } elseif (strlen($datos["apellidos"]) < $configuracion["GENERAL"]["longitudMinimaApellido"]) {
            $respuesta["mensaje"] = $textos->id("ERROR_APELLIDO_CORTO");

        } elseif (strlen($datos["nombre"]) < $configuracion["GENERAL"]["longitudMinimaUsuario"]) {
            $respuesta["mensaje"] = $textos->id("ERROR_USUARIO_CORTO");

        } elseif (empty($datos["id_ciudad_residencia"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CIUDAD");

        } elseif (!$existeCiudadResidencia) {
            $respuesta["mensaje"] = $textos->id("ERROR_CIUDAD_INEXISTENTE");

        } elseif (isset($datos["correo"]) && !filter_var($datos["correo"], FILTER_VALIDATE_EMAIL)) {
            $respuesta["mensaje"] = $textos->id("ERROR_SINTAXIS_CORREO");

        } elseif (isset($datos["correo"]) && $sql->existeItem("personas", "correo", $datos["correo"], "id != '".$usuario->persona->id."'")) {
            $respuesta["mensaje"] = $textos->id("ERROR_CORREO_EXISTENTE");

        } else {
            $respuesta["error"]   = false;
            $validarFormato = Recursos::validarArchivo($archivo_imagen, array("jpg","png","gif", "jpeg"));

            if (isset($datos["id_centro"]) && $sql->existeItem("lista_centros", "nombre", utf8_decode($datos["id_centro"]) ) ) {
                $datos["id_centro"] = $sql->obtenerValor("lista_centros", "id", "nombre = '".utf8_decode($datos["id_centro"])."'");
            } else {
                $datos["id_centro"] = "0";
            }

            if (isset($datos["id_ciudad_nacimiento"]) && $sql->existeItem("lista_ciudades", "cadena", $datos["id_ciudad_nacimiento"]) ) {
                $datos["id_ciudad_nacimiento"] = $sql->obtenerValor("lista_ciudades", "id", "cadena = '".$datos["id_ciudad_nacimiento"]."'");
            } else {
                $datos["id_ciudad_nacimiento"] = "0";
            }

            if (!empty($archivo_imagen["tmp_name"])) {
                if ($validarFormato) {
                    $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_IMAGEN_USUARIO");
                    $respuesta["error"]   = true;
                }
            }

            if (!empty($datos["contrasena1"])) {

                if ($datos["contrasena1"] != $datos["contrasena2"]) {
                    $respuesta["mensaje"] = $textos->id("ERROR_CONTRASENAS_DIFERENTES");
                    $respuesta["error"]   = true;

                } elseif (strlen($datos["contrasena1"]) < $configuracion["GENERAL"]["longitudMinimaContrasena"]) {
                    $respuesta["mensaje"] = $textos->id("ERROR_CONTRASENA_CORTA");
                    $respuesta["error"]   = true;

                } elseif (preg_match("/[^a-zA-Z0-9]/", $datos["contrasena1"])) {
                    $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_CONTRASENA");
                    $respuesta["error"]   = true;

                } 
            }

            if (!$respuesta["error"]) {                
                $datos["id_ciudad_residencia"] = $sql->obtenerValor("lista_ciudades", "id", "cadena = '".$datos["id_ciudad_residencia"]."'" );                
                $datos["id_centro_admin"]      = $sql->obtenerValor("lista_centros", "id", "nombre = '".utf8_decode($datos["id_centro_admin"])."'");
                
                //codigo que valida de que si el perfil es administrador de centro ingrese un centro binacional
                if(isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 && $datos["id_tipo"] == 2){
                    if (empty($datos["id_centro_admin"])) {
                        $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CENTRO_A_ADMINISTRAR");
                        $respuesta["error"]   = true;
                    }
                }
             
                if ( $usuario->modificar($datos) ) {
                        if ($usuario->id == $sesion_usuarioSesion->id) {
                            $usuarioActual = new Usuario($usuario->id);
                            Sesion::registrar("usuarioSesion", $usuarioActual);
                        }
                        //armo en codigo html la informacion ya del usuario modificada, es decir el bloque principal del usuario  
                        $usuarioModificado   = Recursos::modificarUsuarioAjax($id); 
                        $imagenBloqueDerecho = Recursos::modificarImagenDerechaUsuario();
                        //Recursos::escribirTxt($usuarioModificado);
                        
                        $respuesta["error"]                = false;
                        $respuesta["accion"]               = "insertar";
                        $respuesta["modificarUsuarioAjax"] = true;
                        $respuesta["contenido"]            = $usuarioModificado;
                        $respuesta["idContenedor"]         = "#imagenesNotificaciones".$id;
                        $respuesta["idImagenDerecha"]      = "#imagenUsuarioBloqueDerecho_".$id;
                        $respuesta["imagenDerecha"]        = $imagenBloqueDerecho;
                        

                    } else {
                        $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                    }//fin del if($usuario->modi....
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function eliminarUsuario($id, $confirmado) {
    global $textos;

    $usuario   = new Usuario($id);
    $destino   = "/ajax".$usuario->urlBase."/delete";
    $respuesta = array();

    if (!$confirmado) {
        $titulo  = HTML::frase($usuario->persona->nombreCompleto, "negrilla");
        $titulo  = preg_replace("/\%1/", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_USUARIO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 380;
        $respuesta["alto"]    = 180;
    } else {

        if ($usuario->eliminar()) {
            $respuesta["error"]                     = false;
            $respuesta["accion"]                    = "insertar";
            $respuesta["idContenedor"]              = "#usuario_".$id;
            $respuesta["eliminarAjaxLista"]         = true;
            

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $id
 * @param type $confirmado 
 */
function adicionarContacto($id, $confirmado) {
    global $textos, $sql, $sesion_usuarioSesion;

    $moduloInicio = new Modulo("USUARIOS");
    $destino      = "/ajax/".$moduloInicio->url."/addContact";
    $respuesta    = array();

    if (!$confirmado) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("CONFIRMAR_ADICION_CONTACTO"));
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = $textos->id("ADICIONAR_CONTACTO");
        $respuesta["ancho"]   = 370;
        $respuesta["alto"]    = 150;

    } else {
        $datos = array(
            "id_usuario_solicitante" => $sesion_usuarioSesion->id,
            "id_usuario_solicitado"  => $id,
            "estado"                 => "1"
        );

        $consulta = $sql->insertar("contactos", $datos);

        if ($consulta) {
            $contacto             = new Persona($id);
            $mensaje              = str_replace("%1", $sesion_usuarioSesion->persona->nombreCompleto, $textos->id("MENSAJE_CONTACTO_ADICIONADO"));
            Servidor::enviarCorreo($contacto->correo, $mensaje, $mensaje);

            $notificacion         = str_replace("%1", HTML::enlace($sesion_usuarioSesion->usuario, HTML::urlInterna("USUARIOS", $sesion_usuarioSesion->usuario)), $textos->id("MENSAJE_CONTACTO_ADICIONADO"));
            Servidor::notificar($id, $notificacion, array(), '2');
            $contacto_usuario     = $sql->obtenerValor("usuarios", "usuario", "id = '$id'");
            $contactos = $sql->seleccionar(array("lista_contactos"), array("id_contacto"), "id_usuario = '".$sesion_usuarioSesion->id."' AND id_contacto != '".$id."'", "", "nombre ASC");
            if ($sql->filasDevueltas) {
                $notificacion     = str_replace("%1", HTML::enlace($sesion_usuarioSesion->usuario, HTML::urlInterna("USUARIOS", $sesion_usuarioSesion->usuario)), $textos->id("MENSAJE_ADICION_CONTACTO"));
                $notificacion     = str_replace("%2", HTML::enlace($contacto_usuario, HTML::urlInterna("USUARIOS", $contacto_usuario)), $notificacion);
                while ($contacto_lista = $sql->filaEnObjeto($contactos)) {
                    Servidor::notificar($contacto_lista->id_contacto, $notificacion, array(), '5');
                }
            }

            $respuesta["error"]   = false;
            $respuesta["accion"]  = "recargar";

        } else {
            $respuesta["error"]   = true;
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $id
 * @param type $confirmado 
 */
function eliminarContacto($id, $confirmado) {
    global $textos, $sql, $sesion_usuarioSesion;

    $moduloInicio = new Modulo("USUARIOS");
    $destino      = "/ajax/".$moduloInicio->url."/deleteContact";
    $respuesta    = array();

    if (!$confirmado) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("CONFIRMAR_ELIMINACION_CONTACTO"));
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_CONTACTO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 370;
        $respuesta["alto"]    = 120;

    } else {
        $consulta = $sql->eliminar("contactos", "id_usuario_solicitante = '".$sesion_usuarioSesion->id."' AND id_usuario_solicitado = '$id'");

        if ($consulta) {
            $respuesta["error"]   = false;
            $respuesta["accion"]  = "recargar";

        } else {
            $respuesta["error"]   = true;
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * Funcion que se encarga de mostrar el formulario para enviar un mensaje a un contacto cuando se hace click en la pestaña
 * mensajes y se da click en "Send Message", este busca el contacto con el autocompletar y carga el nombre del usuario
 * despues con el nombre del usuario se consulta el id para ingresar los datos en la BD en la tabla mensajes
 * @global type $textos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function enviarMensaje($datos = array()) {
    global $textos, $sql, $sesion_usuarioSesion;

    $usuario   = new Usuario();
    $destino   = "/ajax".$usuario->urlBase."/sendMessage";
    $respuesta = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo  = HTML::parrafo(HTML::radioBoton("datos[objetivo]", "si", "", "para_uno", "", "id_mensaje_unico").$textos->id("ENVIAR_MENSAJE_A_UN_CONTACTO")." ".HTML::radioBoton("datos[objetivo]", "", "", "para_varios", "", "id_mensaje_varios").$textos->id("ENVIAR_MENSAJE_A_VARIOS_CONTACTOS"), "margenSuperior");
        
        $cod  = HTML::parrafo($textos->id("CONTACTO"), "negrilla margenSuperior");
        $cod .= HTML::campoTexto("datos[id_usuario_destinatario]", 50, 255, "", "autocompletable", "", array("title" => HTML::urlInterna("USUARIOS",0,true,"listContacts")));
        $campoTextoUnContacto = HTML::contenedor($cod, "campoTextoUnContacto", "campoTextoUnContacto");
        $codigo .= $campoTextoUnContacto;
        $codigo .= HTML::contenedor( Contacto::mostrarChecksConMisContactos(), "campoTextoVariosContacto oculto", "campoTextoVariosContacto");
        $sobre   = HTML::contenedor("", "fondoSobre", "fondoSobre");
        
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[contenido]", 10, 60, "", "", "txtAreaLimitado511");
        $codigo .= HTML::parrafo($textos->id("MAXIMO_TEXTO_511"), "maximoTexto", "maximoTexto");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($sobre."<br>");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");        
        $codigo  = HTML::forma($destino, $codigo, "", "", "formaEnviarMensaje");

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ENVIAR_MENSAJE"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 500;
        $respuesta["alto"]    = 490;

    } else {
        $respuesta["error"]   = true;

        if ( $datos["objetivo"] == "para_uno" && empty($datos["id_usuario_destinatario"]) ) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTACTO");

        } else if ( $datos["objetivo"] == "para_varios" && empty($datos["varios_contactos"]) ) {
            $respuesta["mensaje"] = $textos->id("ERROR_DEBES_SELECCIONAR_AL_MENOS_UN_CONTACTO");

        } else if (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } else if (empty($datos["contenido"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");

        } else {
            
            if($datos["objetivo"] == "para_uno"){
                
                $datosMensaje["id_usuario_destinatario"] = $sql->obtenerValor("lista_usuarios", "id", "nombre = '" . $datos["id_usuario_destinatario"] . "'");

                $contactoSolicitante    = $sql->obtenerValor("contactos", "id", "id_usuario_solicitante = '" . htmlspecialchars($datosMensaje["id_usuario_destinatario"]) . "' AND id_usuario_solicitado = '" . $sesion_usuarioSesion->id . "' ");
                $contactoSolicitado     = $sql->obtenerValor("contactos", "id", "id_usuario_solicitado = '" . htmlspecialchars($datosMensaje["id_usuario_destinatario"]) . "' AND id_usuario_solicitante = '" . $sesion_usuarioSesion->id . "' ");
                
                if ($contactoSolicitante != "" || $contactoSolicitado != "") {

                    $datosMensaje["id_usuario_remitente"]  = $sesion_usuarioSesion->id;
                    $datosMensaje["titulo"]                = strip_tags($datos["titulo"]);
                    $datosMensaje["contenido"]             = strip_tags($datos["contenido"]);
                    $datosMensaje["fecha"]                 = date("Y-m-d G:i:s");
                    $datosMensaje["leido"]                 = 0;
                    
                    $mensaje = $sql->insertar("mensajes", $datosMensaje);

                    if ($mensaje) {
                        $respuesta["error"]         = false;
                        $respuesta["accion"]        = "insertar";
                        $respuesta["insertarAjax"]  = true;
                    } else {
                        $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                    }
                    
                } else {
                    $respuesta["mensaje"] = $textos->id("NO_ES_UNO_DE_TUS_CONTACTOS");
                }
                
                
            }else{
                    $datosMensaje["id_usuario_remitente"]  = $sesion_usuarioSesion->id;
                    $datosMensaje["titulo"]                = strip_tags($datos["titulo"]);
                    $datosMensaje["contenido"]             = strip_tags($datos["contenido"]);
                    $datosMensaje["fecha"]                 = date("Y-m-d G:i:s");
                    $datosMensaje["leido"]                 = 0;
                    
                    foreach($datos["varios_contactos"] as $idUsuarioDestinatario => $valor){//datos2 es el nombre de los checkboxes creados en la clase contactos
                       $datosMensaje["id_usuario_destinatario"] = $idUsuarioDestinatario;
                       $mensaje = $sql->insertar("mensajes", $datosMensaje);
                       
                    }//fin del foreach

                    if ($mensaje) {
                        $respuesta["error"]         = false;
                        $respuesta["accion"]        = "insertar";
                        $respuesta["insertarAjax"]  = true;
                    } else {
                        $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                    }              
                
            }            

        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * Funcion que se encarga de mostrar el formulario para enviar un mensaje a multiples contactos cuando se hace click en la pestaña
 * mensajes y se da click en "Send Message", este busca el contacto con el autocompletar y carga el nombre del usuario
 * despues con el nombre del usuario se consulta el id para ingresar los datos en la BD en la tabla mensajes
 * @global type $textos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function enviarMensajeMultiple($datos = array()) {
    global $textos, $sql, $sesion_usuarioSesion;

    $usuario   = new Usuario();
    $destino   = "/ajax".$usuario->urlBase."/sendMessage";
    $respuesta = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("CONTACTO"), "negrilla margenSuperior");
        
        
        $sobre   = HTML::contenedor("", "fondoSobre", "fondoSobre");
        
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[contenido]", 10, 60, "", "", "txtAreaLimitado511");
        $codigo .= HTML::parrafo($textos->id("MAXIMO_TEXTO_511"), "maximoTexto", "maximoTexto");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($sobre."<br>");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");        
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ENVIAR_MENSAJE"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 500;
        $respuesta["alto"]    = 490;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["id_usuario_destinatario"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTACTO");

        } elseif (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["contenido"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");

        } else {            
           
            $datos["id_usuario_destinatario"] = $sql->obtenerValor("lista_usuarios", "id", "nombre = '".htmlspecialchars($datos["id_usuario_destinatario"])."'");

            $contactoSolicitante = $sql->obtenerValor("contactos", "id", "id_usuario_solicitante = '".htmlspecialchars($datos["id_usuario_destinatario"])."' AND id_usuario_solicitado = '".$sesion_usuarioSesion->id."' ");
            $contactoSolicitado  = $sql->obtenerValor("contactos", "id", "id_usuario_solicitado = '".htmlspecialchars($datos["id_usuario_destinatario"])."' AND id_usuario_solicitante = '".$sesion_usuarioSesion->id."' ");

           if($contactoSolicitante != "" || $contactoSolicitado != ""){
                
                $datos["id_usuario_remitente"] = $sesion_usuarioSesion->id;
                $datos["titulo"]               = strip_tags($datos["titulo"]);
                $datos["contenido"]            = strip_tags($datos["contenido"]);
                $datos["fecha"]                = date("Y-m-d G:i:s");
                $datos["leido"]                = 0;

                $mensaje = $sql->insertar("mensajes", $datos);

                if ($mensaje) {
                    $respuesta["error"]         = false;
                    $respuesta["accion"]        = "insertar";
                    $respuesta["insertarAjax"]  = true;

                } else {
                    $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                }
            }else{
                    $respuesta["mensaje"] = $textos->id("NO_ES_UNO_DE_TUS_CONTACTOS");
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function responderMensaje($datos) {
    global $textos, $sql, $sesion_usuarioSesion;

    $destino      = "/ajax/users/replyMessage";
    $respuesta    = array();

    if (!is_array($datos)) {
		
	$usuario_destinatario	= new Usuario($datos);
	
        $sobre = HTML::contenedor("", "fondoSobre", "fondoSobre");
        
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("CONTACTO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("usuario_destinatario", 50, 255, $usuario_destinatario->persona->nombreCompleto, "", "", array("readOnly" => "true"));
        $codigo .= HTML::campoOculto("datos[id_usuario_destinatario]", $usuario_destinatario->id);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[contenido]", 10, 60, "", "", "txtAreaLimitado511");
        $codigo .= HTML::parrafo($textos->id("MAXIMO_TEXTO_511"), "maximoTexto", "maximoTexto");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($sobre."<br>");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("RESPONDER_MENSAJE"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 500;
        $respuesta["alto"]    = 490;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["id_usuario_destinatario"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTACTO");

        } elseif (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["contenido"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");

        } else {
            $datos["id_usuario_remitente"] = $sesion_usuarioSesion->id;
            $datos["titulo"]               = strip_tags($datos["titulo"]);
            $datos["contenido"]            = strip_tags($datos["contenido"]);
            $datos["fecha"]                = date("Y-m-d G:i:s");
            $datos["leido"]                = 0;

            $mensaje = $sql->insertar("mensajes", $datos);

            if ($mensaje) {
                 $respuesta["error"]         = false;
                 $respuesta["accion"]        = "insertar";
                 $respuesta["insertarAjax"]  = true;

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO").mysql_error();
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @param type $id
 * @param type $confirmado 
 */
function eliminarMensaje($id, $confirmado) {
    global $textos;

    $destino      = "/ajax/users/deleteMessage";
    $respuesta    = array();

    if (!$confirmado) {
        $nombre  = preg_replace("/\%1/", $textos->id("MENSAJE"), $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_MENSAJE"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 370;
        $respuesta["alto"]    = 130;

    } else {
	$mensaje      = new Mensaje($id);

        if ($mensaje->leido == '1') {
            $logico = false;
        } else {
            $logico = true;            
        }
        
        if ($mensaje->eliminar()) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "insertar";
            $respuesta["idContenedor"]      = "#contenedorMensajes".$id;
            $respuesta["eliminarAjaxLista"] = true;
            $respuesta["restarNumMensajes"] = $logico;

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * Función que se encarga de recibir un arreglo con identificadores de mensajes
 * y de eliminar los mismos de la base de datos de la tabla mensajes
 * 
 * @global type $textos
 * @global type $sql
 * @param type $datos
 * @param type $confirmado 
 */
function eliminarVariosMensajes($datos, $confirmado) {
    global $textos;
    
    $destino      = "/ajax/users/deleteAllMessages";
    $respuesta    = array();

    if (!$confirmado) {
        
        $numMensajes = sizeof(explode(",", $datos));
        
        $nombre  = preg_replace("/\%2/", $textos->id("MENSAJES"), $textos->id("CONFIRMAR_ELIMINACION_CON_CANTIDAD"));
        $nombre  = preg_replace("/\%1/", $numMensajes, $nombre);
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos", $datos);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR")." ".$textos->id("MENSAJES"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 370;
        $respuesta["alto"]    = 130;

    } else {

	$mensaje = new Mensaje();
        
        if ( $mensaje->eliminarVariosMensajes($datos) ) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "recargar";
            
        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}//Fin de la funcion eliminar varios mensajes


/**
 *
 * Función que se encarga de recibir un arreglo con identificadores de mensajes
 * y de eliminar los mismos de la base de datos de la tabla mensajes
 * 
 * @global type $textos
 * @global type $sql
 * @param type $datos
 * @param type $confirmado 
 */
function eliminarVariasNotificaciones($datos, $confirmado) {
    global $textos, $sql;
    
    $destino      = "/ajax/users/deleteNotifications";
    $respuesta    = array();

    if (!$confirmado) {
        
        $numMensajes = sizeof(explode(",", substr($datos, 0, -1)));
        
        $nombre  = str_replace("%2", $textos->id("NOTIFICACIONES"), $textos->id("CONFIRMAR_ELIMINACION_CON_CANTIDAD"));
        $nombre  = str_replace("%1", $numMensajes, $nombre);
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos", $datos);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR")." ".$textos->id("NOTIFICACIONES"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 370;
        $respuesta["alto"]    = 130;

    } else {

        $data = substr($datos, 0, -1);
	$sentencia = "DELETE FROM folcs_notificaciones WHERE id IN(".$data.")";
        $consulta = $sql->ejecutar($sentencia);
        
        if ( $consulta ) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "recargar";
            
        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}//Fin de la funcion eliminar varios mensajes

/**
 *
 * FUNCION QUE CARGA LA VENTANA MODAL PARA LEER EL MENSAJE 
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function leerMensaje($id) {
    global $textos, $sql, $configuracion;
    //Recursos::escribirTxt("codigo del mensaje: ".$id);
    $mensaje   = new Mensaje(htmlspecialchars($id));
    $respuesta = array();
    //codigo para verificar si el mensaje ya fue leido, en caso de no haber sido leido
    //resta un numero del numero de mensajes
    if ($mensaje->leido == '1') {
        $logico = false;        
    } else {
        $logico = true;
    }
    
    $imagen = $mensaje->fotoAutor;
    
    $item  = "";
    $item  = HTML::imagen($imagen, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5");
    $item .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . $mensaje->genero . ".png") . $mensaje->autor, "negrilla");
    $item .= HTML::parrafo(HTML::frase($textos->id("ENVIADO_EL"), "letraPequena margenSuperior").$mensaje->fecha, "margenSuperior");
    $item  = HTML::contenedor($item, "contenedorSuperiorMensajes");

    $titulo = HTML::contenedor(HTML::contenedor(HTML::frase($textos->id("MENSAJE"), "letraNegra negrilla"), ""), "encabezadoBloqueMensajesLargo");    
    $codigo  = HTML::contenedor(nl2br($mensaje->contenido), "estiloMensaje margenSuperior");

    $datos = array(
        "leido"  => "1"
    );
    
    $sql->modificar("mensajes", $datos, "id = '".$id."'");

    $respuesta["generar"] = true;
    $respuesta["restarNumMensajes"] = $logico;
    $respuesta["codigo"]  = $item.$titulo.$codigo;
    $respuesta["destino"] = "#cuadroDialogo";
    $respuesta["titulo"]  = HTML::contenedor(HTML::contenedor(HTML::frase($mensaje->titulo, "letraNegra negrilla"), ""), "encabezadoBloqueMensajes margenSuperior");
    $respuesta["ancho"]   = 600;
    $respuesta["alto"]    = 380;

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * Función que se encarga de listar los contactos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $cadena 
 */
function listarContactos($cadena) {
    global $sesion_usuarioSesion;
    $contacto = new Contacto();
    $respuesta = array();
    $arregloContactos  = $contacto->listarContactos(0, 0, "0", "", $sesion_usuarioSesion->id, "lu.nombre LIKE '%$cadena%' AND lu.id != '0'");
    
    if( sizeof($arregloContactos) > 0 ){
        foreach ($arregloContactos as $fila) {
           $respuesta[] = $fila->nombre;
           
        }
    }

    Servidor::enviarJSON($respuesta);
}


/**
 *
 * Función que se encarga de listar los contactos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $cadena 
 */
function listarContactosDesdeCampo($cadena) {
    global $sesion_usuarioSesion;
    $contacto = new Contacto();    
    $respuesta = array();
    $arregloContactos  = $contacto->listarContactos(0, 10, "0", "", $sesion_usuarioSesion->id, "lu.nombre LIKE '%$cadena%' AND lu.id != '0'");
    
    if( sizeof($arregloContactos) > 0 ){
        foreach ($arregloContactos as $fila) {
	   $respuesta1 = array();
           $respuesta1['label'] = HTML::imagen($fila->icono, 'imagenListarContactos').$fila->nombre;
           $respuesta1['value'] = $fila->usuario;
           $respuesta[] = $respuesta1;
           
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Funcion que termina la sesion de un usuario
 */
function cerrarSesion() {
    $respuesta = array();
    Usuario::desconectarUsuario();
    Sesion::terminar();    
    $respuesta["error"]   = NULL;
    $respuesta["accion"]  = "redireccionar";
    $respuesta["destino"] = "/";
    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @param type $datos 
 */
function buscarUsuarios($datos) {
    global $textos, $sql, $configuracion, $sesion_usuarioSesion;

    $usuario   = new Usuario();
    $destino   = "/ajax".$usuario->urlBase."/searchUsers";
    $respuesta = array();

    if (empty($datos)) {
        $forma1  = HTML::parrafo($textos->id("SELECCIONE_PARAMETRO"), "negrilla margenSuperior");

        $parametros = array(
                        "nombre"  => "Name",
                        "email"   => "Email",
                        "centro"  => "Center",
                        "perfil"  => "Profile"
                        );

        $forma1  .= HTML::parrafo(HTML::listaDesplegable("datos[criterio]", $parametros, "", "", "listaBuscarUsuarios"), "margenSuperior");
        $forma1  .= HTML::campoTexto("datos[patron]", 30, 255, "", "margenSuperior", "campoTextoNormal");
        $forma1  .= HTML::campoTexto("datos[patron1]", 50, 255, "", "autocompletable oculto margenSuperior", "campoTextoPerfiles", array("title" => HTML::urlInterna("INICIO",0,true,"listProfiles")));
        $forma1  .= HTML::campoTexto("datos[patron2]", 50, 255, "", "autocompletable oculto margenSuperior", "campoTextoCentros", array("title" => HTML::urlInterna("INICIO",0,true,"listCenters")));
        $forma1  .= HTML::parrafo(HTML::boton("buscar", $textos->id("BUSCAR")), "margenSuperior");
        $forma1   = HTML::parrafo($forma1, "margenSuperior");

        $codigo1  = HTML::forma($destino, $forma1);
        
        $codigo   = HTML::contenedor($codigo1, "bloqueBorde");
        $codigo  .= HTML::contenedor("","margenSuperior", "resultadosBuscarContactos");

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("BUSCAR_CONTACTOS"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 600;
        $respuesta["alto"]    = 630;

    } else {

        if (!empty($datos["criterio"]) && ( !empty($datos["patron"]) || !empty($datos["patron1"]) || !empty($datos["patron2"]) ) ) {
           if(isset($datos['patron'])){  
                $palabras = explode(" ", $datos["patron"]);

                foreach ($palabras as $palabra) {
                    $palabrasResaltadas[] =  HTML::frase($palabra, "resaltado");
                    $palabrasMarcadas[]   =  "%".$palabra."%";
                }
           }

            if($datos["criterio"] == "nombre"){
                $condicion = " (nombre REGEXP '(".implode("|", $palabras).")' OR usuario REGEXP '(".implode("|", $palabras).")') ";

            }else if($datos["criterio"] == "email"){
                $condicion = "correo REGEXP '(".implode("|", $palabras).")' ";

            }else if($datos["criterio"] == "centro"){
                
                $condicion = "centro = '".$datos['patron2']."' ";
                $palabrasMarcadas[]   = $datos['patron2'];

            }else if($datos["criterio"] == "perfil"){
                
                $condicion = "tipo_usuario = '".$datos['patron1']."' ";
                $palabrasMarcadas[]   = $datos['patron1'];
            }
            //$sql->depurar = true;
            $consulta = $sql->seleccionar(array("lista_usuarios"), array("id", "usuario","nombre", "imagen", "idCentro", "ciudad", "pais", "correo", "centro", "tipo_usuario"), $condicion." AND id NOT IN (0)");

            if ($sql->filasDevueltas) {
                $listaContactos = array();
                while ($fila = $sql->filaEnObjeto($consulta)) {
                    
                    $botones = "";
                    //verificar si el usuario es un administrador de centro
                    $adminCentro = $sql->existeItem("admin_centro", "id_usuario", $sesion_usuarioSesion->id);
                    $idCentro = "";
                    if($adminCentro){
                        $idCentro = $sql->obtenerValor("admin_centro", "id_centro", "id_usuario = '".$sesion_usuarioSesion->id."'");
                    }
                    
                    if( (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) ){
                        $botones .= HTML::contenedor(HTML::botonEliminarItemDesdeBuscador($fila->id, $usuario->urlBase), "alineadoDerecha margenDerechaTriple");
                        $botones .= HTML::contenedor(HTML::botonModificarItemDesdeBuscador($fila->id, $usuario->urlBase), "alineadoDerecha");
                    }elseif($idCentro == $fila->idCentro){
                        $botones .= HTML::contenedor(HTML::botonModificarItemDesdeBuscador($fila->id, $usuario->urlBase), "alineadoDerecha");
                    }
                    
                    
                    
                    $nombre  = str_ireplace($palabras, $palabrasMarcadas, $fila->nombre);
                    $imagen  = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesMiniaturas"]."/".$fila->imagen;
                    $item    = HTML::contenedor($botones, "", "");
                    $item   .= HTML::enlace(HTML::imagen($imagen, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5Mini"), HTML::urlInterna("USUARIOS", $fila->usuario));
                    
		    $item3   = "";
		    $item3  .= HTML::parrafo(HTML::enlace(str_ireplace($palabrasMarcadas, $palabrasResaltadas, $nombre), HTML::urlInterna("USUARIOS", $fila->usuario)), "negrilla");
                    
                    if ($fila->ciudad){
                        $item3 .= HTML::parrafo($fila->ciudad.", ".$fila->pais);
                    }
                     if ($fila->centro){
                        $item3 .= HTML::parrafo("Center: ".$fila->centro);
                    }
                    $item      .=HTML::contenedor($item3, "fondoUltimos5GrisBMini");//barra del contenedor gris
                    $listaContactos[] = $item;
                }

            }
            
            if(sizeof($listaContactos) == 0){
                $listaContactos[] = HTML::contenedor(HTML::frase($textos->id("SIN_REGISTROS")), "bloqueError", "textoError");
            }
            $listaContactos = HTML::lista($listaContactos, "listaVertical listaConIconos bordeSuperiorLista");
 
            $respuesta["accion"]    = "insertar";
            $respuesta["contenido"] = $listaContactos;
            $respuesta["destino"]   = "#resultadosBuscarContactos";

        } else {
            $listaContactos[]       = HTML::contenedor(HTML::frase($textos->id("ERROR_FALTA_CADENA_DE_BUSQUEDA")), "bloqueError", "textoError");
            $listaContactos         = HTML::lista($listaContactos, "listaVertical listaConIconos bordeSuperiorLista");
            $respuesta["accion"]    = "insertar";
            $respuesta["contenido"] = $listaContactos;
            $respuesta["destino"]   = "#resultadosBuscarContactos";
        }

    }

    Servidor::enviarJSON($respuesta);
}


  /**
   * Funcion que muestra el bloque de comentarios de la ventana modal.
   */
  function comentariosItem($modulo, $registro, $propietario) {
	global $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem("modulos", "nombre", $modulo)) {
            return NULL;
        }
	$codigo   = "";
        $comentarios         = new Comentario();
        $listaComentarios    = array();

        if ($comentarios->contar($modulo, $registro)) {

            foreach ($comentarios->listar($modulo, $registro) as $comentario) {

                    $botonEliminar     = "";
                    if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $comentario->idAutor) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) || $modulo == "CENTROS" && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 2 && $sesion_usuarioSesion->idCentro == $registro ) {
                        $botonEliminar = HTML::boton("basura", $textos->id("ELIMINAR"), "botonEliminarComentarioModal directo", "", "botonEliminarComentarioModal", "", array("id_comentario" => $comentario->id, "ruta" => "/ajax/users/deleteComment"));
                        $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, "contenedorBotonesLista", "contenedorBotonesLista"), "botonesLista", "botonesLista");
                    }
                    $contenidoComentario = "";                
                    $contenidoComentario .= $botonEliminar;
                    $contenidoComentario .= HTML::enlace(HTML::imagen($comentario->fotoAutor, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("USUARIOS", $comentario->usuarioAutor));
                    $contenidoComentario .= HTML::parrafo(HTML::enlace($comentario->autor, HTML::urlInterna("USUARIOS", $comentario->usuarioAutor)).$textos->id("USUARIO_DIJO"), "negrilla margenInferior");
                    $contenidoComentario .= HTML::parrafo(nl2br($comentario->contenido));
                    $contenidoComentario .= HTML::parrafo(date("D, d M Y h:i:s A", $comentario->fecha), "pequenia cursiva negrilla margenSuperior margenInferior");
               
                    $listaComentarios[]   = HTML::contenedor($contenidoComentario, "contenedorListaComentariosModal", "contenedorComentarioModal".$comentario->id);
               }
               
           

        } else {
            $listaComentarios[] = HTML::frase($textos->id("SIN_COMENTARIOS"), "margenInferior", "sinRegistros");
        }

        $bloqueComentarios = HTML::lista($listaComentarios, "listaVertical listaConIconos bordeSuperiorLista", "botonesOcultos", "listaComentariosModal");

	$codigo  .= HTML::contenedor($bloqueComentarios, "contenedorComentariosModal");
	$codigo .= HTML::areaTexto("comentario_modal", 3, 80, "", "textareaComentarioModal", "txtAreaLimitado511");

	if (isset($sesion_usuarioSesion)) {
            $moduloActual       = new Modulo($modulo);  
	    $opciones = array("id_modulo" => $moduloActual->id, "id_registro" => $registro, "ruta" => "/ajax/users/addComment");
	    $codigo  .= HTML::boton("libreta", $textos->id("COMENTAR"), "botonComentarModal margenIzquierdaDoble directo", "", "botonComentarModal", "", $opciones);

        }
	
        $codigo .= HTML::parrafo($textos->id("MAXIMO_TEXTO_511"), "maximoTexto", "maximoTexto");
	
	$codigo   = HTML::contenedor($codigo, "contenedorBloqueComentariosModal");


        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_COMENTARIO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["ancho"]   = 770;
        $respuesta["alto"]    = 595;

    Servidor::enviarJSON($respuesta);

  }




  /*
  * Funcion que inserta el comentario desde la ventana modal y da la respuesta via Ajax
  */
  function adicionarComentario($modulo, $registro, $contenidoComentario){
      global $sesion_usuarioSesion, $textos;

	    $respuesta  = array();
            $comentario = new Comentario();
            $datos = array(
	      "idModulo"     => $modulo,
	      "idRegistro"   => $registro,
	      "contenido"    => $contenidoComentario     

	    );
            $idComentario = $comentario->adicionar($datos);

            if ($idComentario) {
                
            /******** CONTENIDO QUE SE VA A DEVOLVER VIA AJAX **********************************/
                $coment = new Comentario($idComentario);
                $botones       = HTML::nuevoBotonEliminarRegistro($idComentario, "users/deleteComment");
                $botonEliminar = HTML::contenedor($botones, "botonesLista", "botonesLista");            
                 
                $contenidoComentario  = $botonEliminar;
                $contenidoComentario .= HTML::enlace(HTML::imagen($coment->fotoAutor, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("USUARIOS", $coment->usuarioAutor));
                $contenidoComentario .= HTML::parrafo(HTML::enlace($coment->autor, HTML::urlInterna("USUARIOS", $coment->usuarioAutor)).$textos->id("USUARIO_DIJO"), "negrilla margenInferior");
                $contenidoComentario .= HTML::parrafo(nl2br($coment->contenido));
                $contenidoComentario .= HTML::parrafo(date("D, d M Y h:i:s A", $coment->fecha), "pequenia cursiva negrilla margenSuperior margenInferior");
                $respuestaComentarios = "<li class='nuevosComentarios'>".HTML::contenedor($contenidoComentario, "contenedorListaComentariosModal", "contenedorComentarioModal".$coment->id)."</li>";
           /**************************************************************************************/     

                $respuesta["error"]                = false;
                $respuesta["accion"]               = "insertar";
                $respuesta["contenido"]            = $respuestaComentarios;
                $respuesta["idContenedor"]         = "#contenedorComentario".$idComentario;
                $respuesta["insertarAjax"]         = true;
		$respuesta["cerrarModal"]          = true;
                $respuesta["destino"]              = "#listaComentariosModal";
               

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }

      Servidor::enviarJSON($respuesta);

  }

/*
* Funcion para eliminar los comentarios desde la ventana modal
*/
  function eliminarComentario($id){
    global $textos;
    $comentario   = new Comentario($id);

        if ($comentario->eliminar()) {
            $respuesta["error"]             = false;
            $respuesta["accion"]            = "insertar";
            $respuesta["idContenedor"]      = "#contenedorComentarioModal".$id;
	    $respuesta["cerrarModal"]       = true;
            $respuesta["eliminarAjaxLista"] = true;

        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    

    Servidor::enviarJSON($respuesta);

  }




/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function enviarMensajeBncWebmaster($datos) {
    global $textos, $sql, $sesion_usuarioSesion;

    if(!isset($sesion_usuarioSesion)){
      return NULL;
    }

    $destino      = "/ajax/users/contactBncWebmaster";
    $respuesta    = array();

    if (empty($datos)) {
	//$sql->depurar = true;
	$idBncWebmaster = $sql->obtenerValor('admin_centro', 'id_usuario', 'id_centro = "'.$sesion_usuarioSesion->idCentro.'"');
	if(!$idBncWebmaster){
	   $idBncWebmaster = '66';
	}
	$bncWebmaster	= new Usuario($idBncWebmaster);
	
        $sobre = HTML::contenedor("", "fondoSobre", "fondoSobre");
        
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("CONTACTO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("usuario_destinatario", 50, 255, $bncWebmaster->persona->nombreCompleto, "", "", array("readOnly" => "true"));
        $codigo .= HTML::campoOculto("datos[id_usuario_destinatario]", $bncWebmaster->id);
        $codigo .= HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[titulo]", 50, 255);
        $codigo .= HTML::parrafo($textos->id("CONTENIDO"), "negrilla margenSuperior");
        $codigo .= HTML::areaTexto("datos[contenido]", 10, 60, "", "", "txtAreaLimitado511");
        $codigo .= HTML::parrafo($textos->id("MAXIMO_TEXTO_511"), "maximoTexto", "maximoTexto");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "botonOk", "botonOk"), "margenSuperior");
        $codigo .= HTML::parrafo($sobre."<br>");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("CONTACTAR_WEBMASTER"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 500;
        $respuesta["alto"]    = 490;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["id_usuario_destinatario"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTACTO");

        } elseif (empty($datos["titulo"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_TITULO");

        } elseif (empty($datos["contenido"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CONTENIDO");

        } else {
            $datos["id_usuario_remitente"] = $sesion_usuarioSesion->id;
            $datos["titulo"]               = strip_tags($datos["titulo"]);
            $datos["contenido"]            = strip_tags($datos["contenido"]);
            $datos["fecha"]                = date("Y-m-d G:i:s");
            $datos["leido"]                = 0;

            $mensaje = $sql->insertar("mensajes", $datos);

            if ($mensaje) {
                 $respuesta["error"]         = false;
                 $respuesta["accion"]        = "insertar";
                 $respuesta["insertarAjax"]  = true;

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO").mysql_error();
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}


?>
