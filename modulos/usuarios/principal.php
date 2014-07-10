<?php
/**
 * @package     FOLCS
 * @subpackage  Usuarios
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón , William Vargas
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * Modificado el: 20-01-12
 **/
global $url_ruta, $url_funcionalidad, $sesion_usuarioSesion;

if (isset($url_ruta) || isset($url_funcionalidad)) {
    global $sql, $sesion_usuarioSesion;
    $contenido = '';

    if (isset($url_funcionalidad)) {
        $usuario = new Usuario($url_funcionalidad);
    } else {
        $usuario = new Usuario($url_ruta);
    }


    /*     * * Activar cuenta de usuario después de su creación ** */
//    if (isset($url_funcionalidad) && isset($url_categoria)) {
//        $url_ruta = $url_funcionalidad;
//        $codigo = $url_categoria;
//
//        /*         * * El código de confirmación es correcto ** */
//        if ($sql->existeItem("usuarios", "confirmacion", "$codigo", "usuario = '$url_ruta'")) {
//            $activar = $sql->modificar("usuarios", array("confirmacion" => "", "activo" => "1"), "usuario = '$url_ruta'");
//            $contenido .= HTML::bloque("", $textos->id("TITULO_CONFIRMACION_CORRECTA"), $textos->id("CONTENIDO_CONFIRMACION_CORRECTA"));
//
//            /*             * * El código de confirmación es incorrecto ** */
//        } else {
//            $contenido .= HTML::bloque("", $textos->id("TITULO_CONFIRMACION_INCORRECTA"), $textos->id("CONTENIDO_CONFIRMACION_INCORRECTA"));
//        }
//    } else {

        if (isset($usuario->id)) {
            Plantilla::$etiquetas['TITULO_PAGINA'] .= ' :: ' . $textos->id('MODULO_ACTUAL');
            Plantilla::$etiquetas['DESCRIPCION'] = $usuario->sobrenombre;
            
            $botones = '';
            if (isset($sesion_usuarioSesion) ) {

                if (Contacto::verificarAmistad($usuario->id)) {
                    $formaContacto = HTML::contenedor(HTML::botonAjax('basura', $textos->id('ELIMINAR_CONTACTO'), HTML::urlInterna('CONTACTOS', 0, true, 'deleteContact'), array('id' => $usuario->id)), 'botonesInternos flotanteDerecha botonOk');
                } else if (Contacto::verificarEstadoSolicitudEnviada($usuario->id)) {
                    $formaContacto = HTML::contenedor($textos->id('SOLICITUD_DE_AMISTAD_YA_ENVIADA'), ' negrilla flotanteDerecha');
                } else if (Contacto::verificarEstadoSolicitudRecibida($usuario->id)) {
                    $formaContacto = HTML::contenedor(HTML::botonAjax('chequeo', $textos->id('ACEPTAR_CONTACTO'), HTML::urlInterna('CONTACTOS', 0, true, 'acceptContact'), array('id' => $usuario->id)), 'botonesInternos flotanteDerecha botonOk');
                } else {
                    $formaContacto = HTML::contenedor(HTML::botonAjax('chequeo', $textos->id('ADICIONAR_CONTACTO'), HTML::urlInterna('CONTACTOS', 0, true, 'addContact'), array('id' => $usuario->id)), 'botonesInternos flotanteDerecha botonOk');
                }
                
                if($sesion_usuarioSesion->id != $usuario->id){
                    $botones = $formaContacto;
                }
                
                //verificar si el usuario es un administrador de centro
                $idCentro = '';
                if(isset($sesion_usuarioSesion)){
                    $adminCentro = $sql->existeItem('admin_centro', 'id_usuario', $sesion_usuarioSesion->id);                    
                    if($adminCentro){
                        $idCentro = $sql->obtenerValor('admin_centro', 'id_centro', 'id_usuario = "'.$sesion_usuarioSesion->id.'"');
                    }                   
                }                
                if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) || (isset($sesion_usuarioSesion) && $usuario->idCentro == $idCentro) ) {
                    $destino = '/ajax/users/edit';
                    $datoUsuario = array('id' => $usuario->id);
                    $botones .= HTML::contenedor(HTML::botonAjax('lapiz', $textos->id('MODIFICAR_PERFIL'), $destino, $datoUsuario), 'alineadoDerecha');

                }
                
            } else {
                $botones = '';
            }

            $contenidoUsuario = $botones;
            $img = HTML::imagen($usuario->persona->imagenPrincipal, 'imagenUsuario');
            $contenidoUsuario .= HTML::enlace($img, $usuario->persona->imagenPrincipal, '', '', array('rel' => 'prettyPhoto[""]'));
            $contenidoUsuario .= HTML::parrafo($textos->id('NOMBRE_COMPLETO'), 'negrilla');
            $contenidoUsuario .= HTML::parrafo($usuario->persona->nombreCompleto, 'justificado margenInferior ancho200px');
            $imagen = HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'warning_blue.png');

            if ($usuario->persona->ciudadResidencia) {
                $contenidoUsuario .= HTML::parrafo($textos->id('CIUDAD'), 'negrilla');
                $contenidoUsuario .= HTML::parrafo($usuario->persona->ciudadResidencia . ', ' . $usuario->persona->paisResidencia, 'justificado margenInferior ancho250px');
            } else {
                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) {
                    $contenidoUsuario .= HTML::parrafo($textos->id('SIN_CIUDAD') . $imagen, 'negrilla');
                    $contenidoUsuario .= HTML::botonImagenAjax($textos->id('POR_FAVOR_ESCOJA_CIUDAD'), 'estiloEnlace justificado margenInferior ancho250px', 'iLikeIt', '', '/ajax/users/edit', array('id' => $usuario->id), '');
                }
            }

            if ($usuario->centro) {
                $contenidoUsuario .= HTML::parrafo($textos->id('CENTRO_BINACIONAL'), 'negrilla');
                $contenidoUsuario .= HTML::parrafo($usuario->centro, 'justificado margenInferior ancho250px');
            } else {
                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) {
                    $contenidoUsuario .= HTML::parrafo($textos->id('SIN_CENTRO_BINACIONAL') . $imagen, 'negrilla');
                    $contenidoUsuario .= HTML::botonImagenAjax($textos->id('POR_FAVOR_ESCOJA_CENTRO'), 'estiloEnlace justificado margenInferior ancho250px', 'iLikeIt', '', '/ajax/users/edit', array('id' => $usuario->id), '');
                }
            }

            if ($usuario->persona->descripcion) {
                $contenidoUsuario .= HTML::parrafo($textos->id('ACERCA_DE_USUARIO'), 'negrilla');
                $contenidoUsuario .= HTML::parrafo($usuario->persona->descripcion, 'justificado margenInferior ancho250px');
            }

	    
	    if(isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id){
	      if ($usuario->tipo) {
		  $centroAdmin = '';
		  if ($usuario->idTipo == 2) {
		      $adminCentro = $sql->obtenerValor("admin_centro", "id_centro", "id_usuario = '".$usuario->id."'");
		      $clase = "";
		      if ($adminCentro) {
			  $centroAdmin = $sql->obtenerValor("lista_centros", "nombre", "id = '" . $adminCentro . "'");
			  $centroAdmin = ' of '.$centroAdmin;
			  $img = HTML::imagen($configuracion['SERVIDOR']['media'].'/'.$configuracion['RUTAS']['imagenesEstilos'].'ayuda.png', 'margenSuperior');
			  $videoCambiarPerfil = HTML::enlace($textos->id('VIDEO_CAMBIAR_PERFIL').' '.$img, 'http://www.youtube.com/watch?v=zwovZ2jYF24', 'estiloBoton', '', array('rel' => 'prettyPhoto[]', 'ayuda' => $textos->id('VIDEO_CAMBIAR_PERFIL')) );
				  
			  $centroAdmin .= HTML::parrafo($videoCambiarPerfil);
		      }
		  }


		  $contenidoUsuario .= HTML::parrafo($textos->id('PERFIL'), 'negrilla');
		  $contenidoUsuario .= HTML::parrafo($usuario->tipo.$centroAdmin, 'justificado margenInferior ancho250px');
	      }

	    }

            if ($usuario->idTipo == 105) {
                $contenidoUsuario .= HTML::parrafo(HTML::enlace('Educational advisors profile page', 'http://www.ablaonline.org/bnc/34'), 'medioMargenIzquierda bordeInferior mitadEspacioInferior');
            }

            if ($usuario->idTipo == 101 ) {
                $contenidoUsuario .= HTML::parrafo(HTML::enlace('Information Resource Center profile page', 'http://www.ablaonline.org/bnc/33'), 'medioMargenIzquierda bordeInferior mitadEspacioInferior');
            }

	    if(isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id){

	      if($usuario->idCentro == 0){
		$contenidoUsuario .= HTML::parrafo($textos->id("ESCOGER_CENTRO_PARA_CONTACTAR_WEBMASTER"), 'negrita margenSuperior bordeInferior mitadEspacioInferior');
	      }else{
		$enlaceContacto = HTML::frase($textos->id('AQUI'), 'estiloEnlace enlaceAjax', '', array('alt' => '/ajax/users/contactBncWebmaster'));
		$contenidoUsuario .= HTML::parrafo(str_replace('%1', $enlaceContacto, $textos->id("TEXTO_EXPLICACION_CONTACTAR_WEBMASTER")), ' margenSuperior bordeInferior mitadEspacioInferior');
	      }

	    }

            $contenidoUsuario = HTML::contenedor($contenidoUsuario, '', 'contenidoUsuario');
            $contenido = HTML::bloque('usuario_' . $usuario->id, $usuario->sobrenombre, $contenidoUsuario);

            /* condicional que muestra las opciones del ususario como notificaciones, contactos, 
              etc en la pestaña "my profile" o cuando recien se inicia sesion" */
            if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) || ( isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 )) {

                /**
                 * 
                 *  Inicio contenido pestaña de notificaciones 
                 */
                $listaNotificaciones = array();

                $notificaciones = $sql->seleccionar(array('notificaciones'), array('fecha' => 'UNIX_TIMESTAMP(fecha)', 'contenido', 'id', 'tipo_notificacion', 'leido'), 'id_usuario = "' . $usuario->id . '" AND activo = "1"', '', 'fecha DESC', 0, 25);

                if ($sql->filasDevueltas) {
                    
                    $rutaImagen = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"];

                    while ($notificacion = $sql->filaEnObjeto($notificaciones)) {

                        if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0)) {
                            $botonEliminar = '';
                            $botonEliminar = HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('INICIO', '', true, 'deleteNotification'), array('id' => $notificacion->id));
                            
                            $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista', 'botonesLista');
                          
                        }
                        $texto_notificacion = $botonEliminar;
                        $texto_notificacion .= HTML::campoChequeo($notificacion->id, "", "checksItems");
                        $texto_notificacion .= HTML::imagen($rutaImagen.'icono_notificacion_'.$notificacion->tipo_notificacion.'.png', 'iconoNotificacion');
                        $texto_notificacion .= HTML::parrafo($notificacion->contenido, '');
                        $claseContenedorNotificacion = 'contenedorNotificacion';
                        if($notificacion->leido == '0'){
                            $claseContenedorNotificacion = 'contenedorNotificacionNew';
                        }
                        $texto_notificacion .= HTML::parrafo(date('D, d M Y h:i:s A', $notificacion->fecha), 'pequenia cursiva negrilla');
                        $texto_notificaciones = HTML::contenedor($texto_notificacion, $claseContenedorNotificacion, 'contenedorNotificacion' . $notificacion->id);
                        
                        
                                             
                        
                        
                        $listaNotificaciones[] = $texto_notificaciones;
                    }
                } else {
                    $listaNotificaciones[] = HTML::parrafo($textos->id('SIN_NOTIFICACIONES'));
                }
                           
                $listaNoti = HTML::contenedor(HTML::lista($listaNotificaciones, 'listaVertical bordeInferiorLista'), 'margenSuperior');
                $botonBorrar        = HTML::contenedor(HTML::boton("basura", $textos->id("ELIMINAR"), "directo", "", "botonBorrarItemsMultiples", "", array("ruta" => "/ajax/users/deleteNotifications")), "contenedorBotonBorrarItemsMultiples", "contenedorBotonBorrarItemsMultiples");
                $checkBorrar        = HTML::campoChequeo("", "", "marcarTodosLosItems", "marcarTodosLosItems") . HTML::frase($textos->id("SELECCIONAR_TODOS"), "negrilla letraMasGrande1 margenDerechaDoble margenSuperior margenIzquierda");
                
                $contenedorBorrar   = HTML::contenedor($checkBorrar . $botonBorrar, "margenInferiorDoble");
                
                $listaNotificaciones = HTML::contenedor($contenedorBorrar.$listaNoti, '');
                

                /**
                 * Al iniciar sesion lo primero que ve el usuario son sus notificaciones,
                 * Asi que aqui mismo deberan marcarse ya como vistas
                 * */

                /*                 * * Fin contenido pestaña de notificaciones ** */

                /** Pestaña contactos  */
                
                global $configuracion, $sesion_usuarioSesion, $textos, $sql;

                $contacto = new Contacto();
                $excluidas = "";
                $contenidoContactos = "";

                $listaItems = array();

                $tituloBloque = $textos->id('CONTACTOS') . ': ' . HTML::frase($contacto->contarContactos($usuario->id), 'cantidadContactos', '');

                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) {
		    $buscadorFiltro  = HTML::campoTexto("datos[id_amigo]", 50, 255, $textos->id("BUSCAR_TUS_CONTACTOS"), "autocompletable margenInferior", "campoBuscarAmigos", array("title" => HTML::urlInterna("USUARIOS",0,true,"listContactsFromInput")));
                    $buscadorFiltro .= HTML::contenedor(HTML::botonAjax('masGrueso', HTML::frase($textos->id('BUSCAR_USUARIOS'), 'botonPequeno'), HTML::urlInterna('CONTACTOS', 0, true, 'searchContacts')), 'botonBuscarAmigos flotanteDerecha');
                } else {
                    $buscadorFiltro = '';
                }
                
                $listaUsuarios    = $textos->id('SIN_CONTENIDO');
                $contactos        = new Contacto();
                $lista            = array();
                $listaPendientes  = array();
                $listaContactos   = '';

                $tamanoArregloContactos = $contactos->contarContactos($usuario->id);

                $arregloContactos = $contactos->listarContactos(0, 4, array('0'), '', $usuario->id);

                $arregloPendientes = $contactos->listarSolicitudesAmistad(0, 0, NULL, '', $usuario->id);
                $tamanoArregloPendientes = sizeof($arregloPendientes);
             

                if ($tamanoArregloPendientes > 0) {//si el usuario tiene solicitudes de amistad
                    foreach ($arregloPendientes as $contacto) {//recorro las solicitudes de amistad pendientes de aceptar

                        //$solicitante = new Usuario($contacto->id);
                        //Creo los dos botones para aceptar o rechazar una solicitud de amistad
                        $formaAceptar = Contacto::formaAceptarAmistad($contacto->id);
                        $formaRechazar = Contacto::formaRechazarAmistad($contacto->id);
                        $url = '/users/' . $contacto->usuario;

                        $imagen = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $contacto->imagen;

                        $item = HTML::enlace(HTML::imagen($imagen, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $url);

                        $item .= HTML::enlace(HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $contacto->genero . '.png') . $contacto->nombre, 'negrilla'), $url);
                        $item3 = '';

                        if (!empty($contacto->centro)) {
                            $item3 .= HTML::parrafo($contacto->centro . $formaRechazar . $formaAceptar, 'pequenia cursiva negrilla margenInferior');
                        } else {
                            $item3 .= HTML::parrafo($textos->id('SIN_CENTRO_BINACIONAL') . $formaRechazar . $formaAceptar, 'pequenia cursiva negrilla margenInferior');
                        }

                        if (!empty($contacto->ciudad)) {
                            $item3 .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['iconosBanderas'] . '/' . strtolower($contacto->codigoIsoPais) . '.png', 'miniaturaBanderas') . ' ' . $contacto->ciudad . ', ' . $contacto->pais);
                        } else {
                            $item3 .= HTML::parrafo($textos->id('SIN_CIUDAD'), 'pequenia negrilla');
                        }
                        $item .= HTML::contenedor($item3, 'fondoUltimos5Gris'); //barra del contenedor gris  
                        
                        $item = HTML::contenedor($item, 'contactosPendientes', 'contactosPendientes' . $contacto->id);
                        $listaPendientes[] = $item;
                    }//fin del foreach

                    $listaPendientes = HTML::lista($listaPendientes, 'listaVertical listaConIconos bordeInferiorLista', '');
                    //$listaPendientes .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('CONTACTOS', $usuario->usuario), 'flotanteCentro margenSuperior');
                    //$contenidoContactos .= HTML::bloque('bloqueSolicitudesDeAmistad', $textos->id('SOLICITUDES_DE_AMISTAD') . ': ' . HTML::frase($tamanoArregloPendientes.'', 'cantidadAmigosPendientes'), $listaPendientes);
                    $contenidoContactos .= HTML::contenedor($listaPendientes, 'contenedorListaContactos');
                }


                if ($tamanoArregloContactos > 0) {//si el usuario actual tiene contactos


                    if ($tamanoArregloPendientes > 0) {
                        foreach ($arregloPendientes as $contacto) {//si el usuario tiene solicitudes de amistad, las recorro y las pongo en el DOM pero ocultas
                                                                   //para en caso de aceptar alguna, se muestre directamente en sus contactos
                            //$solicitante = new Usuario($contacto->id);
                            //Creo los dos botones para aceptar o rechazar una solicitud de amistad
                            $formaEliminar = Contacto::formaEliminarAmistad($contacto->id); //boton para eliminar una amistad
                            $formaEnviarMensaje = Contacto::formaEnviarMensaje($contacto->id);
                            $url = '/users/' . $contacto->usuario;

                            $imagen = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $contacto->imagen;

                            $item = HTML::enlace(HTML::imagen($imagen, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $url);
                            $item .= HTML::enlace(HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $contacto->genero . '.png') . $contacto->nombre, 'negrilla'), $url);
                            $item3 = '';

                            if (!empty($contacto->centro)) {
                                $item3 .= HTML::parrafo($contacto->centro . $formaEliminar . $formaEnviarMensaje, 'pequenia cursiva negrilla margenInferior');
                            } else {
                                $item3 .= HTML::parrafo($textos->id('SIN_CENTRO_BINACIONAL') . $formaEliminar . $formaEnviarMensaje, 'pequenia cursiva negrilla margenInferior');
                            }

                            if (!empty($contacto->ciudad)) {
                                $item3 .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['iconosBanderas'] . '/' . strtolower($contacto->codigoIsoPais) . '.png', 'miniaturaBanderas') . ' ' . $contacto->ciudad . ', ' . $contacto->pais);
                            } else {
                                $item3 .= HTML::parrafo($textos->id('SIN_CIUDAD'), 'pequenia negrilla');
                            }
                            $item .= HTML::contenedor($item3, 'fondoUltimos5Gris'); //barra del contenedor gris                        
                            $item = HTML::contenedor($item, 'contactosPendientesOcultos', 'contactosNuevosPendientes' . $contacto->id, '');
                            $listaContactos[] = $item;
                        }//fin del foreach          
                    }



                    foreach ($arregloContactos as $contacto) {//recorro y muestro los contactos del usuario

                        $formaEliminar = Contacto::formaEliminarAmistad($contacto->id); //boton para eliminar una amistad
                        $formaEnviarMensaje = Contacto::formaEnviarMensaje($contacto->id);

                        $url = '/users/' . $contacto->usuario;
                        //$user = new Usuario($contacto->id);
                        //$amigo = new Persona($user->idPersona);
                        $imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $contacto->imagen;

                        $item = HTML::enlace(HTML::imagen($imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $url);
                        $item .= HTML::enlace(HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $contacto->genero . '.png') . $contacto->nombre, 'negrilla'), $url);
                        $item3 = '';
                        if (!empty($contacto->centro)) {
                            $item3 .= HTML::parrafo($contacto->centro . $formaEliminar . $formaEnviarMensaje, 'pequenia cursiva negrilla margenInferior');
                        } else {
                            $item3 .= HTML::parrafo($textos->id('SIN_CENTRO_BINACIONAL') . $formaEliminar . $formaEnviarMensaje, 'pequenia cursiva negrilla margenInferior');
                        }

                        if (!empty($contacto->ciudad)) {
                            $item3 .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['iconosBanderas'] . '/' . strtolower($contacto->codigoIsoPais) . '.png', 'miniaturaBanderas') . ' ' . $contacto->ciudad . ', ' . $contacto->pais);
                        } else {
                            $item3 .= HTML::parrafo($textos->id('SIN_CIUDAD'), 'pequenia negrilla');
                        }
                        $item .= HTML::contenedor($item3, 'fondoUltimos5Gris'); //barra del contenedor gris
                        $item = HTML::contenedor($item, 'contactosActuales', 'contactosActuales' . $contacto->id);
                        // Recursos::escribirTxt('Este es: '.$amigo->persona->nombreCompleto, 5);  
                        $listaContactos[] = $item;
                    }//fin del foreach
                    

                    $listaContactos = HTML::lista($listaContactos, 'listaVertical listaConIconos bordeInferiorLista margenSuperiorTriple', 'botonesOcultos');
                    $listaContactos .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('CONTACTOS', $usuario->usuario), 'flotanteCentro margenSuperior');
                    //$contenidoContactos .= HTML::bloque('listadoUsuarios', $tituloBloque  , $buscadorFiltro.$listaContactos);
		    $contenidoContactos .= HTML::contenedor($buscadorFiltro.$listaContactos, 'contenedorListaContactos');
                } else {//si el usuario no tiene contactos

                    if ($tamanoArregloPendientes > 0) {//verifico si el usuario tiene solicitudes de amistad y las pongo en el dom ocultas
                                                       //por si aun asi, no tiene contactos, y acepta una solicitud, se muestre como su contacto
                        foreach ($arregloPendientes as $contacto) {
                            // Recursos::escribirTxt("Este es: ".$contactos[2], 5); 
                            //$solicitante = new Usuario($contacto->id);
                            //Creo los dos botones para aceptar o rechazar una solicitud de amistad
                            $formaEliminar = Contacto::formaEliminarAmistad($contacto->id); //boton para eliminar una amistad
                            $url = '/users/' . $contacto->usuario;

                            $imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $contacto->imagen;

                            $item = HTML::enlace(HTML::imagen($imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $url);
                            $item .= HTML::enlace(HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $contacto->genero . '.png') . $contacto->nombre, 'negrilla'), $url);
                            $item3 = '';

                            if (!empty($contacto->centro)) {
                                $item3 .= HTML::parrafo($contacto->centro . $formaEliminar, 'pequenia cursiva negrilla margenInferior');
                            } else {
                                $item3 .= HTML::parrafo($textos->id('SIN_CENTRO_BINACIONAL') . $formaEliminar, 'pequenia cursiva negrilla margenInferior');
                            }

                            if (!empty($contacto->ciudad)) {
                                $item3 .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['iconosBanderas'] . '/' . strtolower($contacto->codigoIsoPais) . '.png', 'miniaturaBanderas') . ' ' . $contacto->ciudad . ', ' . $contacto->pais);
                            } else {
                                $item3 .= HTML::parrafo($textos->id('SIN_CIUDAD'), 'pequenia negrilla');
                            }
                            $item .= HTML::contenedor($item3, 'fondoUltimos5Gris'); //barra del contenedor gris                        
                            $item = HTML::contenedor($item, 'contactosPendientesOcultos', 'contactosNuevosPendientes' . $contacto->id, '');
                            $listaContactos[] = $item;
                        }//fin del foreach          
                    }
                    
                    $listaContactos  = HTML::lista($listaContactos, 'listaVertical listaConIconos bordeInferiorLista', 'botonesOcultos');
                    $verMas  = HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('CONTACTOS'), 'flotanteCentro margenSuperior');
                    $texto   = HTML::contenedor($textos->id('NO_TIENES_CONTACTOS'), 'contactosPendientesVisibles', 'sinContactos');
                    //$contenidoContactos .= HTML::bloque('listadoUsuarios', $tituloBloque , $buscadorFiltro.$listaContactos.'<br>'.$texto.'</br>'.$verMas , NULL, NULL, '-IS');
		    $contenidoContactos .= HTML::contenedor($buscadorFiltro.$listaContactos.'<br>'.$texto.'</br>'.$verMas, 'contenedorListaContactos');
                }
                /****************************************************  fin del bloque de contactos  ****************************************************************************** */

                /*                 * * Inicio contenido pestaña de mensajes ** */
                $modulo_principal = new Modulo('USUARIOS');

                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) {
                    $botones = HTML::contenedor(HTML::botonAjax('sobreCerrado', $textos->id('ENVIAR_MENSAJE'), HTML::urlInterna('USUARIOS', 0, true, 'sendMessage')), 'alineadoDerecha');
                    $listaMensajes[] = HTML::contenedor($botones, 'contenedorBotonesMensajes');
                } else {
                    $listaMensajes[] = '';
                }

                $mensajes = new Mensaje();

                $numMensajes = $sql->seleccionar(array('mensajes'), array('id', 'id_usuario_destinatario', 'titulo'), 'id_usuario_destinatario = "' . $usuario->id . '"', '', '');
                $numMensajes = $sql->filasDevueltas;
                if ($sql->filasDevueltas) {

                    foreach ($mensajes->listar(0, 3, $usuario->id) as $mensaje) {

                        $botones = HTML::contenedor(HTML::botonAjax('basura', $textos->id('ELIMINAR'), '/ajax/'.$modulo_principal->url.'/deleteMessage', array('id' => $mensaje->id)), 'flotanteCentro contenedorBotonesLista', 'contenedorBotonesLista');
                        $botones .= HTML::contenedor(HTML::botonAjax('sobreCerrado', $textos->id('RESPONDER_MENSAJE'), '/ajax/'.$modulo_principal->url.'/replyMessage', array('id' => $mensaje->idAutor)), 'flotanteDerecha contenedorBotonesLista', 'contenedorBotonesLista');
                        $item = HTML::contenedor($botones, 'botonesLista', 'botonesLista');
                        $item .= HTML::enlace(HTML::imagen($mensaje->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('USUARIOS', $mensaje->usuario));
                        $item .= HTML::parrafo(HTML::enlace($mensaje->autor, HTML::urlInterna('USUARIOS', $mensaje->usuario)), 'negrilla');
                        $item .= HTML::parrafo(date('D, d M Y h:i:s A', $mensaje->fecha), 'pequenia cursiva negrilla');

                        $sobre   = '';
                        $idSobre = '';
                        if ($mensaje->leido == 0) {
                            $sobre = HTML::contenedor('', 'mensajesNuevos', 'mensajeNuevo'.$mensaje->id);
                            $idSobre = '#mensajeNuevo'.$mensaje->id;
                        }

                        $opciones   = array('id_mensaje' => $mensaje->id, 'icono_sobre' => $idSobre);
                        $boton      = HTML::parrafo($mensaje->titulo . $textos->id('VER_MENSAJE') . HTML::icono('circuloFlechaDerecha') . $sobre, 'estiloEnlace mensajeNuevoClick', 'leerMensaje', $opciones);

                        $item      .= $boton;
                        $item       = HTML::contenedor($item, 'contenedorListaMensajes', 'contenedorMensajes' . $mensaje->id);

                        $listaMensajes[] = $item;
                    }//fin del foreach     
                } else {

                    $listaMensajes[].= $textos->id('SIN_MENSAJES');
                }//fin del if


                $listaMensajes = HTML::lista($listaMensajes, 'listaVertical listaConIconos bordeInferiorLista');
                if ($numMensajes >= 4) {
                    $listaMensajes .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('MENSAJES', $usuario->usuario), 'flotanteCentro margenSuperior') . '</BR></BR>';
                }

	        $curso   = new Curso();
                $barraPersonal = array(
                    HTML::frase($textos->id('NOTIFICACIONES'), 'letraBlanca', 'listaNotificaciones') => $listaNotificaciones,
                    HTML::frase($textos->id('CONTACTOS'), 'letraBlanca', 'listaContactos') => $contenidoContactos,
                    HTML::frase($textos->id('MENSAJES'), 'letraBlanca', 'listaMensajes') => $listaMensajes,
		    HTML::frase($textos->id('CURSOS_QUE_SIGO'), 'letraBlanca')          => $curso->cursosQueSigo()
                );
	      if( (isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicion('CURSOS') || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0 ) && $sesion_usuarioSesion->id == $usuario->id){
		$barraPersonal[HTML::frase($textos->id('CURSOS_QUE_DICTO'), 'letraBlanca')] = $curso->cursosQueDicto();
	      }
                /**
                 * Bloque de codigo que muestra en la pantalla principal del usuario
                 * si tiene nuevas notificaciones, o mensajes, mostrando la cantidad y una imagen
                 * para ello llama a metodos especificos de la clase usuario los cuales se encargan
                 * de devolver un bloque de codigo HTML con dicha informacion          * 
                 */
                $nuevasNotificaciones = $usuario->mostrarNuevasNotificaciones();
                $nuevosContactos = $usuario->mostrarNuevasSolicitudesAmistad();
                $nuevosMensajes = $usuario->mostrarNuevosMensajes();

                $contenido .= HTML::contenedor($nuevasNotificaciones . $nuevosContactos . $nuevosMensajes, 'imagenesNotificaciones', 'imagenesNotificaciones' . $usuario->id);
                $contenido .= HTML::contenedor(HTML::pestanas2('barraPersonal', $barraPersonal), 'pestanasRecursosUsuarios');
            }

            if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) || ( isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) || (isset($sesion_usuarioSesion) && Contacto::verificarAmistad($usuario->id))) {
                $recursos = array(
                    HTML::frase($textos->id('COMENTARIOS'), 'letraBlanca')  => Recursos::bloqueComentarios('USUARIOS', $usuario->id, $usuario->id),
                    HTML::frase($textos->id('VIDEOS'), 'letraBlanca')       => Recursos::bloqueVideos('USUARIOS', $usuario->id, $usuario->id),
                    HTML::frase($textos->id('AUDIOS'), 'letraBlanca')       => Recursos::bloqueAudios('USUARIOS', $usuario->id, $usuario->id),
                    HTML::frase($textos->id('IMAGENES'), 'letraBlanca')     => Recursos::bloqueImagenes('USUARIOS', $usuario->id, $usuario->id),
                    HTML::frase($textos->id('GALERIAS'), 'letraBlanca')     => Recursos::bloqueGalerias('USUARIOS', $usuario->id, $usuario->id),
                    HTML::frase($textos->id('DOCUMENTOS'), 'letraBlanca')   => Recursos::bloqueArchivos('USUARIOS', $usuario->id, $usuario->id),
                                      
                    );
            if(isset($sesion_usuarioSesion) && Perfil::verificarPermisosConsulta('ENLACES') || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0){
                $recursos[HTML::frase($textos->id('ENLACES'), 'letraBlanca')] = Recursos::bloqueEnlaces('USUARIOS', $usuario->id, $usuario->id);
            }

	    if(isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id != $usuario->id && (Perfil::verificarPermisosAdicion('CURSOS', $usuario->idTipo) && $usuario->idTipo != '99') ){
	      $curso   = new Curso();
	      $recursos[HTML::frase($textos->id('MIS_CURSOS'), 'letraBlanca')] = $curso->cursosQueDicto($usuario);
	    }
	      $contenido .= HTML::contenedor('', 'sombraInferior');

              $contenido .= HTML::contenedor(HTML::pestanas2('recursosUsuario', $recursos), 'pestanasRecursosUsuarios', 'pestanasRecursosUsuarios' . $usuario->id);

	      $contenido .= HTML::contenedor('', 'sombraInferior');
            }

            /************************* BLoque donde apareceran mis blogs, mis noticias, blogs que me gustan, etc *********************************** */
//            if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id /* ) */) {
//                $blog    = new Blog();
//                $noticia = new Noticia();
//               // $curso   = new Curso();
//                $misGustos = array(
//                    HTML::frase($textos->id('MIS_BLOGS'), 'letraBlanca')                => $blog->misBlogs(),
//                    HTML::frase($textos->id('BLOGS_QUE_ME_GUSTAN'), 'letraBlanca')      => $blog->blogsQueMeGustan(),
//                    HTML::frase($textos->id('NOTICIAS_QUE_ME_GUSTAN'), 'letraBlanca')   => $noticia->NoticiasDestacadas(),                   
//                );
//
//                $contenido .= HTML::contenedor(HTML::pestanas2('itemsUsuario', $misGustos), 'pestanasRecursosUsuarios');
//               
//            }
            /**************************************************************************************************************************************** */
        }
//    }//este es el que hay que eliminar
} else {

    global $configuracion, $forma_pagina;

    $usuario = new Usuario();
    $excluidas = '';


    /////////// DATOS DE PAGINACION ///////////////////////////////////////////   
    $listaItems = array();
    $registros = $configuracion['GENERAL']['registrosPorPagina'];

    if (isset($forma_pagina)) {
        $pagina = $forma_pagina;
    } else {
        $pagina = 1;
    }

    $registroInicial = ($pagina - 1) * $registros;

/////////////////////////////////////////////////////////////////////////////////////////////



    $tituloBloque = $textos->id('USUARIOS');
    $buscadorFiltro = HTML::contenedor(HTML::botonAjax('masGrueso', $textos->id('BUSCAR'), HTML::urlInterna('USUARIOS', 0, true, 'searchUsers')), 'flotanteDerecha');
    $listaUsuarios = $textos->id('SIN_CONTENIDO');
    $usuarios = new Usuario();
    $lista = array();


    $reg = sizeof($usuarios->listar(0, 0, NULL, 'u.id != "0"'));
    $arregloUsuarios = $usuarios->listar($registroInicial, $registros, NULL, 'u.id != "0"');

    if ($usuarios->registros) {

        foreach ($arregloUsuarios as $usuario) {
            $item = '';
            //verificar si el usuario es un administrador de centro
            if(isset($sesion_usuarioSesion)){
                $adminCentro = $sql->existeItem('admin_centro', 'id_usuario', $sesion_usuarioSesion->id);
                $idCentro = '';
                if($adminCentro){
                    $idCentro = $sql->obtenerValor('admin_centro', 'id_centro', 'id_usuario = "'.$sesion_usuarioSesion->id.'"');
                }                   
            }
         
            if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) || (isset($sesion_usuarioSesion) && $usuario->idCentro == $idCentro)) {
                $botones = '';
                if(isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0){
                    $botones .= HTML::botonEliminarItem($usuario->id, $usuario->urlBase);
                }
                $botones .= HTML::botonModificarItem($usuario->id, $usuario->urlBase);
                $item = HTML::contenedor($botones, 'botonesLista', 'botonesLista');
            }
            $item .= HTML::enlace(HTML::imagen($usuario->persona->imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $usuario->url);
            $item .= HTML::enlace(HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $usuario->persona->idGenero . '.png') . $usuario->persona->nombreCompleto, 'negrilla'), $usuario->url);
            $item3 = HTML::parrafo(date('D, d M Y h:i:s A', $usuario->fechaRegistro), 'pequenia cursiva negrilla margenInferior');

            if (!empty($usuario->persona->ciudadResidencia)) {
                $item3 .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['iconosBanderas'] . '/' . strtolower($usuario->persona->codigoIsoPais) . '.png', 'miniaturaBanderas') . ' ' . $usuario->persona->ciudadResidencia . ', ' . $usuario->persona->paisResidencia);
                // $item3 .= HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['iconosBanderas'].'/'.strtolower($usuario->persona->codigoIsoPais).'.png', 'miniaturaBanderas');
            }

            $item .= HTML::contenedor($item3, 'fondoUltimos5Gris'); //barra del contenedor gris
            $item = HTML::contenedor($item, '', 'usuario_' . $usuario->id);
            $lista[] = $item;
        }//fin del foreach
        //////////////////paginacion /////////////////////////////////////////////////////
        $paginacion = Recursos::mostrarPaginador($reg, $registroInicial, $registros, $pagina, $totalPaginas);

        $lista[] = $paginacion;


        $listaUsuarios = HTML::lista($lista, 'listaVertical listaConIconos bordeSuperiorLista', 'botonesOcultos');
    }//fin del if($usuarios->registros)

    $contenido .= HTML::bloque('listadoUsuarios', $tituloBloque , $buscadorFiltro.$listaUsuarios);
}


Plantilla::$etiquetas['BLOQUE_IZQUIERDO'] = $contenido;
?>
