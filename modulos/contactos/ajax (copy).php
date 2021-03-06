<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Contactos
 * @author      Pablo Andr�s V�lez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * 
 * Modificado el 03-02-12
 *
 **/


if (isset($url_accion)) {
    switch ($url_accion) {
      
        case "searchContacts"       :  ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                       buscarContactos($forma_datos);
                                       break;
        case "addContact"           :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                       adicionarContacto($forma_id, $confirmado);
                                       break;
        case "acceptFromNotification" :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                       aceptarAmistadDesdeNotificacionDinamica($forma_id, $confirmado);
                                       break;                               
        case "acceptContact"        :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                       aceptarContacto($forma_id, $confirmado);
                                       break;            
         case "deleteContact"       : ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                       eliminarContacto($forma_id, $confirmado);
                                       break;
        case "sendMessage"          :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                       enviarMensaje($forma_datos, $forma_id_usuario_destinatario);
                                       break;
	case "replyMessage"         :   ($forma_procesar) ? $datos = $forma_datos : $datos = $forma_id;
                                       responderMensaje($datos);
                                       break;
        case "deleteMessage"        :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                       eliminarMensaje($forma_id, $confirmado);
                                       break;
        case "listContacts"         :  listarContactos($url_cadena);
                                       break;
        case "acceptFriendRequest"  :  aceptarSolicitudAmistad($forma_id_contacto);
                                       break;        
        case "rejectFriendRequest"  :  rechazarSolicitudAmistad($forma_id_contacto);
                                       break;
        case "deleteFriend"         :  ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                       eliminarAmistad($forma_id_contacto, $confirmado);
                                       break;        
        case "requestFriendship"    :  solicitarAmistad($forma_id_contacto);
                                       break;
        case "conexionStatus"       :  estadoConexion();
                                       break; 
//        case "verifyStatus"         :  verificarEstado($forma_userChat);
//                                       break;  
       
    }
}




/**
 *
 * Funcion que carga el formulario para SOLICITAR LA AMISTAD DE un nuevo contacto desde dentro del contacto
 * @global type $textos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $id
 * @param type $confirmado 
 */
function adicionarContacto($id, $confirmado) {
    global $textos, $sql, $sesion_usuarioSesion;

    $modulo = new Modulo("CONTACTOS");
    $destino      = "/ajax/".$modulo->url."/addContact";
    $respuesta = array();

    if (!$confirmado) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("CONFIRMAR_ADICION_CONTACTO"));
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_CONTACTO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 120;

    } else {
        $datos = array(
            "id_usuario_solicitante" => $sesion_usuarioSesion->id,
            "id_usuario_solicitado"  => $id,
            "estado"                 => "0"
        );

        $consulta = $sql->insertar("contactos", $datos);

        if ($consulta) {
            Recursos::escribirTxt("si hace la consulta");
            //desea recibir notificacion al correo???
            if(Recursos::recibirNotificacionesAlCorreo($id) ){
                $contacto             = new Usuario($id);
                $mensaje              = preg_replace("/\%1/", $sesion_usuarioSesion->persona->nombreCompleto, $textos->id("QUIERE_SER_TU_AMIGO")); 
                Servidor::enviarCorreo($contacto->persona->correo, $mensaje, $mensaje); 
            }
            
            $notificacion         = preg_replace("/\%1/", HTML::enlace($sesion_usuarioSesion->persona->nombreCompleto, HTML::urlInterna("USUARIOS", $sesion_usuarioSesion->usuario)), $textos->id("QUIERE_SER_TU_AMIGO"));
            Servidor::notificar($id, $notificacion);
            
            //PONER LA RESPUESTA DE ESTE METODO EN AJAX
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
 * //aqui en este metodo tengo que hacer que oculte el bloque de videos y eso que puede ver mientras son amigos
 * //y que muestre de nuevo el boton de solicitar amistad
 * 
 * Metodo que carga el formulario para eliminar un contacto desde dentro del contacto
 * @global type $textos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $id
 * @param type $confirmado 
 */
function eliminarContacto($id, $confirmado) {
    global $textos, $sql, $sesion_usuarioSesion;

    $moduloInicio = new Modulo("CONTACTOS");
    $destino      = "/ajax/".$moduloInicio->url."/deleteContact";

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
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 150;

    } else {
        $contacto = new Contacto();
        $consulta = $contacto->eliminarAmistad($id);

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




//Documentado el 03 - 01 - 2012
//
//function listarContactos($cadena) {
//    global $sql, $sesion_usuarioSesion;
//
//    $consulta  = $sql->seleccionar(array("contactos"), array("nombre"), "nombre LIKE '%$cadena%' AND id_usuario = '".$sesion_usuarioSesion->id."'");
//
//    while ($fila = $sql->filaEnObjeto($consulta)) {
//        $respuesta[] = $fila->nombre;
//    }
//
//    Servidor::enviarJSON($respuesta);
//}



/**
*
*Metodo que carga el formulario para buscar y filtrar usuarios ya sea por email o por nombre, tambien busca en mis contactos actuales
*es decir, cuando hago una busqueda busca tanto en mis contactos como en la lista de usuarios general teniendo en cuenta, que si ya 
*son amigos no muestra nada, si no son amigos muestra el boton para solicitar la amistad y si uno de los usuarios listados ha realizado
*una solicitud de amistad muestra el boton para aceptar la solicitud de amistad
*
**/

function buscarContactos($datos) {
    global $textos, $sql, $configuracion, $sesion_usuarioSesion;

    $contacto = new Contacto();
    $destino = "/ajax".$contacto->urlBase."/searchContacts";

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
        $respuesta["alto"]    = 600;

    
    } else {
        
//1) Verifico que se haya escogido un parametro para la busqueda y que se haya introducido un dato en el campo de texto para la busqueda
         if (!empty($datos["criterio"]) && ( !empty($datos["patron"]) || !empty($datos["patron1"]) || !empty($datos["patron2"]) ) ) {

           if(isset($datos['patron'])){  
                $palabras = explode(" ", $datos["patron"]);

                foreach ($palabras as $palabra) {
                    $palabrasResaltadas[] =  HTML::frase($palabra, "resaltado");
                    $palabrasMarcadas[]   =  "%".$palabra."%";
                }
           }
            
//2) Segun el criterio que se haya escogido elaboro una condicion para la consulta en la BD
     
            
            if($datos["criterio"] == "nombre"){
                $condicion2 = "(lu.nombre REGEXP '(".implode("|", $palabras).")' OR lu.usuario REGEXP '(".implode("|", $palabras).")')";

            }else if($datos["criterio"] == "email"){
                $condicion2 = "lu.correo REGEXP '(".implode("|", $palabras).")' ";

            }else if($datos["criterio"] == "centro"){
                
                $condicion2 = "lu.centro = '".$datos['patron2']."' ";
                $palabrasMarcadas[]   = $datos['patron2'];

            }else if($datos["criterio"] == "perfil"){
                
                $condicion2 = "lu.tipo_usuario = '".$datos['patron1']."' ";
                $palabrasMarcadas[]   = $datos['patron1'];
            }
            
            

//3)Creo la condicion completa de la primera consulta la cual realizo en mis contactos actuales
           $condicion = "(c.id_usuario_solicitante = '".$sesion_usuarioSesion->id."' AND c.estado = '1' AND c.id_usuario_solicitado = lu.id AND ".$condicion2;
           $condicion .= ") OR (c.id_usuario_solicitado = '".$sesion_usuarioSesion->id."' AND c.estado = '1'  AND c.id_usuario_solicitante = lu.id AND ".$condicion2." )";

           $tablas = array (
                            "lu" =>  "lista_usuarios",
                            "c"  =>  "contactos"
                           );

           $columnas = array(
                             "id"            => "lu.id",
                             "usuario"       => "lu.usuario",
                             "nombre"        => "lu.nombre", 
                             "imagen"        => "lu.imagen",
                             "ciudad"        => "lu.ciudad",
                             "pais"          => "lu.pais",
                             "correo"        => "lu.correo",
                             "centro"        => "lu.centro", 
                             "tipo_usuario"  => "lu.tipo_usuario"
                             );            
           
            //$sql->depurar = true;
//4)se consulta en la tabla de contactos y se relaciona con la lista de usuarios 
            $consulta = $sql->seleccionar($tablas , $columnas, $condicion);
           

//5) Se prepara la consulta para la lista de usuarios en general (que no son contactos del usuario actual)
           $tablas1 = array (
                            "lu" =>  "lista_usuarios"                            
                           );
           $columnas1 = array(
                             "id"            => "lu.id",
                             "usuario"       => "lu.usuario",
                             "nombre"        => "lu.nombre", 
                             "imagen"        => "lu.imagen",
                             "ciudad"        => "lu.ciudad",
                             "pais"          => "lu.pais",
                             "correo"        => "lu.correo",
                             "centro"        => "lu.centro", 
                             "tipo_usuario"  => "lu.tipo_usuario"
                             );            
           
           // $sql->depurar = true;    
//6)Se consulta en la lista de usuarios para ver cuales coinciden con los parametros de busqueda
            $consulta1 = $sql->seleccionar($tablas1 , $columnas1, $condicion2." AND lu.id NOT IN(0)");


            $ids = array();//almaceno en este arreglo todos los id's de los contactos del usuario que aparecen en la busqueda

            if ($sql->filasDevueltas) {
//7)Recorro el arreglo con los registros que estan en los contactos del usuario actual y los guardo en un arreglo -> $listaContactos
                while ($fila = $sql->filaEnObjeto($consulta)) {
                    
                    $nombre = str_ireplace($palabras, $palabrasMarcadas, $fila->nombre);
                    $formaEnviarMensaje = Contacto::formaEnviarMensajeDesdeBuscador($fila->id);
                    $imagen  = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesMiniaturas"]."/".$fila->imagen;
                    $item    = HTML::enlace(HTML::imagen($imagen, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("USUARIOS", $fila->usuario));
                    $item   .= HTML::parrafo(HTML::enlace(str_ireplace($palabrasMarcadas, $palabrasResaltadas, $nombre), HTML::urlInterna("USUARIOS", $fila->usuario)), "negrilla");
                    $item3   = "";
                    
                    $ids[]   = $fila->id;//almaceno los id's
                    if ($fila->ciudad){
                        $item3 .= HTML::parrafo($fila->ciudad.", ".$fila->pais);
                    }
                    if ($fila->centro){
                        $item3 .= HTML::parrafo(HTML::frase("Center: ", "negrilla").$fila->centro.$formaEnviarMensaje);
                    }else{
                        $item3 .= HTML::parrafo(HTML::frase("Center: ", "negrilla").$textos->id("SIN_CENTRO_BINACIONAL").$formaEnviarMensaje);
                    }

                    $item      .=HTML::contenedor($item3, "fondoUltimos5Gris");//barra del contenedor gris
                    $listaContactos[] = $item;
                }//fin del while

/**********************************************************************************************************************************/

//8)Creo dos arreglos Para almacenar en ellos los datos sobre los usuarios a los cuales ya se les ha enviado la solicitud de 
//amistad, o cuales le han enviado ya la solictud al usuario actual para colocar un formulario diferente en cada caso

                //En $arregloSolicitantes almacenamos cuales de los usuarios que aparecen en la busqueda han realizado solicitud ya al usuario actual
                //para en vez de mostrar el formulario de agregar muestre el de aceptar
                 $tablas = array(
                    "c"  =>  "contactos"            
                    );
                $columnas = array(
                    "id_usuario"        => "DISTINCT c.id_usuario_solicitante"                    
                     );
                $condicion = "c.id_usuario_solicitado = '".$sesion_usuarioSesion->id."' AND estado = '0'";
                   //$sql->depurar = true;
                $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
                //$arregloSolicitantes = $sql->filaEnArreglo($consulta7);//almaceno los ids de los usuarios que estoy buscando y han solicitado mi amistad
                
                $idSolicitantes = array();
                while ($fila = $sql->filaEnObjeto($consulta)) { 
                    
                    $idSolicitantes[] = $fila->id_usuario;
                 }

                //En $arregloSolicitados mostramos cuales de los usuarios que aparecen en la busqueda, el usuario actual les ha realizado solicitud 
                //para en vez de mostrar el formulario de agregar muestre uno de que ya se le ha solictado la amistad
                 $tablas = array(
                    "c"  =>  "contactos"            
                    );
                $columnas = array(
                    "id_usuario"        => "DISTINCT c.id_usuario_solicitado"                    
                     );
                $condicion = "c.id_usuario_solicitante = '".$sesion_usuarioSesion->id."' AND estado = '0'";
                   //$sql->depurar = true;
                $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
                $idSolicitados = array();
               // $arregloSolicitados = $sql->filaEnArreglo($consulta);//almaceno los ids de los usuarios que estoy buscando y a los cuales les he solicitado la amistad
                 while ($fila = $sql->filaEnObjeto($consulta)) { 
                    
                    $idSolicitados[] = $fila->id_usuario;
                 }
/***************************************************************************************************************************************/

//9)Recorro el arreglo con el listado de usuarios completos, es decir la consulta que se hace solo a la tabla lista usuarios 
                while ($fila = $sql->filaEnObjeto($consulta1)) {                                                                
                  

//10)Muestro solo los usuarios que no aparecen listados en los contactos del usuario actual 
//  (que $fila->id no se encuentre en el arreglo de los id's y que no sea el id del usuario actual)
                        if(!in_array($fila->id, $ids) && $fila->id != $sesion_usuarioSesion->id){

                            if(in_array($fila->id,  $idSolicitantes)){//Si el usuario ha a solicitado mi amistad
                               $formaSolicitar =  Contacto::formaAceptarAmistad2($fila->id); 
                            }elseif(in_array($fila->id, $idSolicitados)){
                             $formaSolicitar = Contacto::formaSolicitudEnviada($fila->id);
                            }
                            elseif(!in_array($fila->id, $idSolicitados) && !in_array($fila->id, $idSolicitantes)){
                               $formaSolicitar =  Contacto::formaSolicitarAmistad($fila->id);
                            }
                            $nombre = str_ireplace($palabras, $palabrasMarcadas, $fila->nombre);

                            $imagen  = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesMiniaturas"]."/".$fila->imagen;
                            $item    = HTML::enlace(HTML::imagen($imagen, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("USUARIOS", $fila->usuario));
                            $item   .= HTML::parrafo(HTML::enlace(str_ireplace($palabrasMarcadas, $palabrasResaltadas, $nombre), HTML::urlInterna("USUARIOS", $fila->usuario)), "negrilla");
                            $item3   = "";

                            if ($fila->ciudad){
                                $item3 .= HTML::parrafo($fila->ciudad.", ".$fila->pais);
                            }else{
                                $item3 .= HTML::parrafo(HTML::frase("City: ", "negrilla").$textos->id("SIN_CIUDAD"));
                            }

                            if ($fila->centro){
                                $item3 .= HTML::parrafo(HTML::frase("Center: ", "negrilla").$fila->centro.$formaSolicitar);
                            }else{
                                $item3 .= HTML::parrafo(HTML::frase("Center: ", "negrilla").$textos->id("SIN_CENTRO_BINACIONAL").$formaSolicitar);
                            }

                            $item      .=HTML::contenedor($item3, "fondoUltimos5Gris");//barra del contenedor gris
                            $listaContactos[] = $item;
                        }

                }//fin del while

            }
                
               if(sizeof($listaContactos) == 0){
                   
                 $listaContactos[]            = HTML::contenedor(HTML::frase($textos->id("SIN_REGISTROS")), "bloqueError", "textoError");
                 $respuesta["textoError"]  = true;
                 
               } 
               $listaContactos = HTML::lista($listaContactos, "listaVertical listaConIconos bordeSuperiorLista");
                
            

            $respuesta["accion"]            = "insertar";
            $respuesta["contenido"]         = $listaContactos;
            $respuesta["destino"]           = "#resultadosBuscarContactos";
            $respuesta["limpiaDestino"]     = true;

        } else {
            
            $listaContactos[] = HTML::contenedor(HTML::frase($textos->id("ERROR_FALTA_CADENA_DE_BUSQUEDA")), "bloqueError", "textoError");
                
            $listaContactos = HTML::lista($listaContactos, "listaVertical listaConIconos bordeSuperiorLista");
            
            $respuesta["accion"]            = "insertar";
            $respuesta["contenido"]         = $listaContactos;
            $respuesta["textoError"]        = true;
            $respuesta["destino"]           = "#resultadosBuscarContactos";
            $respuesta["limpiaDestino"]     = true;
        }

    }

    Servidor::enviarJSON($respuesta);
}




/**
 *
 * Metodo para aceptar una solicitud de amistad, 
 * este modifica el valor estado en la tabla contactos de 0 a 1
 *
 * @param  arreglo $datos       Datos del me gusta
 *
 */
function aceptarSolicitudAmistad($idContacto) {
    global $textos, $sql, $configuracion;

   $con = new Contacto(); 
   $contactoAceptado = $con->aceptarAmistad($idContacto);
   

        if ($contactoAceptado) {            
            $respuesta["error"]     = NULL;
            $respuesta["accion"]    = "insertar";
            $respuesta["limpiar"]   = false;
            $respuesta["contenido"] = HTML::contenedor($textos->id("AHORA_SON_AMIGOS"), "respuestaAmistad"); 
            $respuesta["destino"]   = "#contactosPendientes".$idContacto;


        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    
    Servidor::enviarJSON($respuesta);
}




/**
 *
 * Funcion rechazarSolicitudAmistad-> crea un nuevo objeto de la clase contacto para llamar al metodo rechazar amistad
 * el cual borra el registro de la BD en la tabla contactos donde el usuario solicitado es el usuario con la sesion activa
 * @param  arreglo $datos       Datos del me gusta
 *
 */
function rechazarSolicitudAmistad($idContacto) {
    global $textos, $sql, $configuracion;

   $con = new Contacto(); 
   $contactoRechazado = $con->rechazarAmistad($idContacto);
   

        if ($contactoRechazado) {            
            $respuesta["error"]     = NULL;
            $respuesta["accion"]    = "insertar";
            $respuesta["limpiar"]   = false;
            $respuesta["contenido"] = HTML::contenedor($textos->id("SOLICITUD_RECHAZADA"), "respuestaAmistad"); 
            $respuesta["destino"]   = "#contactosPendientes".$idContacto;


        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    
    Servidor::enviarJSON($respuesta);
}




/**
 *
 * Funcion rechazarSolicitudAmistad-> crea un nuevo objeto de la clase contacto para llamar al metodo rechazar amistad
 * el cual borra el registro de la BD en la tabla contactos donde el usuario solicitado es el usuario con la sesion activa
 * @param  arreglo $datos       Datos del me gusta
 *
 */
function solicitarAmistad($idContacto) {
    global $textos;
    
    if(!isset($idContacto)){
        return NULL;
    }
    $respuesta = array();

   $con = new Contacto();   
   

    if ($con->solicitarAmistad($idContacto)) {            
        $respuesta["error"]     = NULL;
        $respuesta["accion"]    = "insertar";
        $respuesta["limpiar"]   = false;
        $respuesta["contenido"] = HTML::contenedor($textos->id("SOLICITUD_ENVIADA"), "respuestaAmistad"); 
        $respuesta["destino"]   = "#solicitarAmistadInterno".$idContacto;


    } else {
        $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
    }
    
    Servidor::enviarJSON($respuesta);
}





/**
 *
 * Funcion rechazarSolicitudAmistad-> crea un nuevo objeto de la clase contacto para llamar al metodo rechazar amistad
 * el cual borra el registro de la BD en la tabla contactos donde el usuario solicitado es el usuario con la sesion activa
 * @param  arreglo $datos       Datos del me gusta
 *
 */
function eliminarAmistad($idContacto, $confirmado) {
    global $textos, $sql, $configuracion;

    $con   = new Contacto();
    $destino = "/ajax".$con->urlBase."/deleteFriend";

     if (!$confirmado) {
        $user = new Usuario($idContacto);
        $titulo  = HTML::frase($user->persona->nombreCompleto, "negrilla");
        $titulo  = preg_replace("/\%1/", $titulo, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id_contacto", $idContacto);
        $codigo .= HTML::parrafo($titulo);
        $opciones = array("onClick" => "$('#contactosActuales".$idContacto."').fadeOut(500);
                                           $('#cuadroDialogo').dialog('close');");
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR"), "", "", "", "", $opciones), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_CONTACTO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 300;
        $respuesta["alto"]    = 150;

    } else {

            $con = new Contacto(); 
            $contactoEliminado= $con->eliminarAmistad($idContacto);   

                if ($contactoEliminado) {            
                    $respuesta["error"]     = NULL;
                    $respuesta["accion"]    = "insertar";
                    $respuesta["limpiar"]   = false;
                    $respuesta["contenido"] = HTML::contenedor($textos->id("YA_NO_SON_AMIGOS"), "respuestaAmistad");
                    $respuesta["idContenedor"] = "#contactosNuevosPendientes".$idContacto;
                    $respuesta["eliminarAjaxLista"] = true;
                    $respuesta["eliminarAmistad"] = true;
                    $respuesta["destino"]   = "#contactosActuales".$idContacto;


                } else {
                    $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                }

    }
    
    Servidor::enviarJSON($respuesta);
}





/**
 * 
 */


function aceptarContacto($id, $confirmado) {
    global $textos, $sql, $sesion_usuarioSesion;

    $moduloInicio = new Modulo("CONTACTOS");
    $contacto     = new Usuario($id);
    $destino      = "/ajax/".$moduloInicio->url."/acceptContact";

    if (!$confirmado) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo(str_replace("%1", HTML::frase($contacto->usuario, "negrilla"), $textos->id("CONFIRMAR_ACEPTAR_CONTACTO")));
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ACEPTAR_CONTACTO"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 370;
        $respuesta["alto"]    = 150;

    } else {
        
        //Como esta funciona bien, pero esta realizando
        //operaciones de logica de negocio directamente desde aqu�.
        //esta pendiente de someter a pruebas retirando todo este codigo de aqui
        //y usando el metodo aceptarAmistad de la clase contacto
        
        $datosContacto["estado"] = '1';
        $consulta = $sql->modificar("contactos", $datosContacto, "id_usuario_solicitado = '".$sesion_usuarioSesion->id."' AND id_usuario_solicitante = '$id'");

        if ($consulta) {
                        
            if( Recursos::recibirNotificacionesAlCorreo($id) ){
                $contacto             = new Persona($id);
                $mensaje              = preg_replace("/\%1/", $sesion_usuarioSesion->persona->nombreCompleto, $textos->id("TE_HA_ACEPTADO_COMO_AMIGO")); 
                Servidor::enviarCorreo($contacto->correo, $mensaje, $mensaje);
            }
                           
            $contacto_usuario     = $sql->obtenerValor("usuarios", "usuario", "id = '$id'");
            $notificacion     = preg_replace("/\%1/", HTML::enlace($sesion_usuarioSesion->usuario, HTML::urlInterna("CONTACTOS", $sesion_usuarioSesion->usuario)), $textos->id("MENSAJE_ADICION_CONTACTO"));
            $notificacion     = preg_replace("/\%2/", HTML::enlace($contacto_usuario, HTML::urlInterna("CONTACTOS", $contacto_usuario)), $notificacion);
            
            //Consulto los contactos del usuario con la sesion actual donde �l ha sido el usuario solicitante
            $contactos1 = $sql->seleccionar(array("contactos"), array("id_usuario_solicitado"), "id_usuario_solicitante = '".$sesion_usuarioSesion->id."' AND id_usuario_solicitado != '".$id."' AND estado = '1'", "", "");
            if ($sql->filasDevueltas) {                
                while ($contacto_lista = $sql->filaEnObjeto($contactos1)) {
                    Servidor::notificar($contacto_lista->id_usuario_solicitado, $notificacion);
                }
            }
            
             //Consulto los contactos del usuario con la sesion actual donde �l ha sido el usuario solicitado
            $contactos2 = $sql->seleccionar(array("contactos"), array("id_usuario_solicitante"), "id_usuario_solicitante != '".$id."' AND id_usuario_solicitado = '".$sesion_usuarioSesion->id."' AND estado = '1'", "", "");
            if ($sql->filasDevueltas) {                
                while ($contacto_lista = $sql->filaEnObjeto($contactos2)) {
                    Servidor::notificar($contacto_lista->id_usuario_solicitante, $notificacion);
                }
            }
            
            //Consulto los contactos del usuario que solicit� la amistad donde �l ha sido el usuario solicitante
            $contactos3 = $sql->seleccionar(array("contactos"), array("id_usuario_solicitado"), "id_usuario_solicitante = '".$id."' AND id_usuario_solicitado != '".$sesion_usuarioSesion->id."' AND estado = '1'", "", "");
            if ($sql->filasDevueltas) {                
                while ($contacto_lista = $sql->filaEnObjeto($contactos3)) {
                    Servidor::notificar($contacto_lista->id_usuario_solicitado, $notificacion);
                }
            }
            
             //Consulto los contactos del usuario que solicit� la amistad donde �l ha sido el usuario solicitado
            $contactos4 = $sql->seleccionar(array("contactos"), array("id_usuario_solicitante"), "id_usuario_solicitante != '".$sesion_usuarioSesion->id."' AND id_usuario_solicitado = '".$id."' AND estado = '1'", "", "");
            if ($sql->filasDevueltas) {                
                while ($contacto_lista = $sql->filaEnObjeto($contactos4)) {
                    Servidor::notificar($contacto_lista->id_usuario_solicitante, $notificacion);
                }
            }          
            
            
            
            //PONER ESTA RESPUESTA EN AJAX TAMBIEN, Y QUE MUESTRE LOS DOCS, AUDIOS, IMAGENES, ETC DEL USUARIO
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
 * Funcion que se encarga de mostrar el formulario para enviar un mensaje a un contacto haciendo click en el sobresito
 * de esta forma si se puede capturar correctamente el id del usuario. Seguidamente con el id del usuario
 * se consulta en lista_usuarios el nombre del usuario y se carga el formulario con este nombre en un textfield
 * que sea de solo lectura para evitar cambios.
 * @global type $textos
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function enviarMensaje($datos = array(), $id_usuario_destinatario) {
    global $textos, $sql, $sesion_usuarioSesion;

    $usuario = new Contacto();
    $destino = "/ajax".$usuario->urlBase."/sendMessage";

    if (empty($datos)) {
        
        $nombre  = $sql->obtenerValor("lista_usuarios", "nombre", "id = '".$id_usuario_destinatario."'");      
        
        $sobre = HTML::contenedor("", "fondoSobre", "fondoSobre");
        
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("CONTACTO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("", 50, 255, $nombre, "", "", array("readOnly" => "true"));
        $codigo .= HTML::campoOculto("datos[id_usuario_destinatario]", $id_usuario_destinatario);
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
        $respuesta["ancho"]   = 430;
        $respuesta["alto"]    = 450;

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
                    $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
                }
            
        }
    }

    Servidor::enviarJSON($respuesta);
}




/**
 *
 * Metodo que se encarga de refrescar la pesta�a donde 
 * aparecen los contactos conectados, es invocado por 
 * una peticion ajax cada 30 segundos
 *
 * @param  arreglo $datos       Datos del me gusta
 *
 */
function estadoConexion() {
    global $textos, $sql, $configuracion, $sesion_usuarioSesion;

   if(isset($sesion_usuarioSesion)){
           
       $existe = $sql->existeItem("usuarios_conectados", "id_usuario", $sesion_usuarioSesion->id);
       //Recursos::escribirTxt("hola si entre");
        if($existe){
            
            $visible = $sql->obtenerValor("usuarios_conectados", "visible", "id_usuario = ".$sesion_usuarioSesion->id."");
            
            if($visible == "1"){
              $datos = array( "visible" => '0' );
              $consulta = $sql->modificar("usuarios_conectados", $datos, "id_usuario = ".$sesion_usuarioSesion->id."");
              
            }else{
              $datos = array( "visible" => '1' );
              $consulta = $sql->modificar("usuarios_conectados", $datos, "id_usuario = ".$sesion_usuarioSesion->id.""); 
            } 
              
        }       
   
   }
    Servidor::enviarJSON($respuesta);
    
}//fin del metodo estado



/**
 *
 * Funcion que se encarga de mostrar la ventana modal para aceptar una solicitud de amistad
 * desde la notificacion dinamica
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @global type $textos
 * @global type $configuracion
 * @param type $id 
 */
function aceptarAmistadDesdeNotificacionDinamica($id, $confirmado){
    global $sql, $sesion_usuarioSesion, $textos, $configuracion;
    
    $moduloInicio = new Modulo("CONTACTOS");
    
    $destino      = "/ajax/".$moduloInicio->url."/acceptFromNotification";
    $nombre = $sql->obtenerValor("lista_usuarios", "nombre", "id = ".$id);
    $nombre = HTML::frase($nombre, "negrilla");
    $imagen = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesMiniaturas"]."/".$sql->obtenerValor("lista_usuarios", "imagen", "id = ".$id);
    $imagen = HTML::imagen($imagen, "miniaturaListaChat");
    
    $texto = str_replace("%1", $nombre, $textos->id("CONFIRMAR_ADICIONAR_CONTACTO"));
        
    if (!$confirmado) {
        $codigo   = HTML::campoOculto("procesar", "true");
        $codigo  .= HTML::campoOculto("id", $id, "idContacto");
        $codigo  .= HTML::parrafo( $texto );
        $botones  = HTML::boton("chequeo", $textos->id("ACEPTAR"));
        $botones .= HTML::boton("basura", $textos->id("RECHAZAR"), "directo", "", "cancelarSolicitudAmistad");
        $codigo  .= HTML::contenedor(HTML::parrafo($botones, "margenSuperior"), "flotanteIzquierda");
        $codigo  .= HTML::parrafo( $textos->id("AHORA_SON_AMIGOS"), "letraVerde margenSuperiorCuadruple oculto", "textoExitoso");
        $codigo   = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = $textos->id("ADICIONAR_CONTACTO")." ".$imagen;
        $respuesta["ancho"]   = 400;
        $respuesta["alto"]    = 140;

    } else {

        $con = new Contacto();
        $contactoAceptado = $con->aceptarAmistad($id);


        if ($contactoAceptado) {
             $respuesta["error"]   = false;
             $respuesta["accion"]  = "insertar";
             $respuesta["insertarAjax"]   = true;
             
        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            
        }
        
    }
    Servidor::enviarJSON($respuesta); 
    
}

?>
