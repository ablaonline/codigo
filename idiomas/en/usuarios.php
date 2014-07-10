<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Usuarios
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 * 
 * Modificado el: 16-01-12
 *
 **/

$textos = array(
    'MODULO_ACTUAL'                     => 'Users',
    'ADICIONAR_USUARIO'                 => 'Add user',
    'MODIFICAR_USUARIO'                 => 'Edit user',
    'ELIMINAR_USUARIO'                  => 'Delete user',
    'BUSCAR_CONTACTOS'                  => 'Search contacts',
    'BUSCAR_USUARIOS'                   => 'Search for new users',
    'ADICIONAR_CONTACTO'                => 'Add contact',
    'ACEPTAR_CONTACTO'                  => 'Accept contact',    
    'ELIMINAR_CONTACTO'                 => 'Delete contact',
    'SOLICITUD_DE_AMISTAD_YA_ENVIADA'   => 'Frienship request already sent',
    'CONFIRMAR_ADICION_CONTACTO'        => 'Are you sure you want to add this contact to your list?',
    'CONFIRMAR_ELIMINACION_CONTACTO'    => 'Are you sure you want to delete this contact from your list?',
    'ENVIAR_MENSAJE'                    => 'Send a message',
    'ELIMINAR_MENSAJE'                  => 'Delete message',
    'RESPONDER_MENSAJE'                 => 'Reply',    
    'INFORMACION_PERSONAL'              => 'Personal info',
    'CONTACTO'                          => 'Contact',
    'UBICACION'                         => 'Location',
    'SOBRENOMBRE'                       => 'Nickname',
    'FOTOGRAFIA_USUARIO'                => 'Personal picture',
    'ACERCA_DE_USUARIO'                 => 'About me',
    'USUARIO_ADICIONADO'                => 'User has been added successfully',
    'USUARIO_MODIFICADO'                => 'User has been modified successfully',
    'USUARIO_ELIMINADO'                 => 'User has been deleted successfully',
    'ERROR_FALTA_NOMBRE'                => 'Please enter your first name',
    'TIPO_USUARIO'                      => 'User type',//falta verificar
    
    'NOMBRE_COMPLETO'                   => 'Full name',
    'CIUDAD_NACIMIENTO'                 => 'City of Birth',
    'CIUDAD_RESIDENCIA'                 => 'City of Residence',
    'INGRESE_SU_SOBRENOMBRE'            => 'Enter your nickname',
    'SELECCIONE_FECHA_NACIMIENTO'       => 'Select your birthday',
    'SELECCIONE_CIUDAD_NACIMIENTO'      => 'Enter your city of birth',
    'SELECCIONE_CIUDAD_RESIDENCIA'      => 'Enter your city of residence',
    'SELECCIONE_CENTRO_BINACIONAL'      => 'Enter your binational center or organization',
    'INGRESE_CENTRO_BINACIONAL'         => 'Select your binational center or your organization',
    'INGRESE_EMAIL'                     => 'Enter your e-mail address',
    'INGRESE_SU_CONTRASENA'             => 'Enter your new password',
    'REPITA_CONTRASENA'                 => 'Confirm new password',
    'SIN_NOTIFICACIONES'                => 'You don\'t have new notifications',
    'SIN_CONTACTOS'                     => 'You don\'t have contacts.',
    'SIN_MENSAJES'                      => 'You don\'t have new messages',
    'ERROR_USUARIO_REQUERIDO'           => 'Please enter your username',
    'ERROR_CONTRASENA_REQUERIDA'        => 'Please enter your password',
    'ERROR_CONTRASENA2_REQUERIDA'       => 'Plese enter your password verification',
    'ERROR_USUARIO_INVALIDO'            => 'Invalid username or password',
    'ERROR_USUARIO_INACTIVO'            => 'Your account is disabled',
    'ERROR_NOMBRE_REQUERIDO'            => 'Please enter your first name',
    'ERROR_APELLIDOS_REQUERIDOS'        => 'Please enter your last name',
    'ERROR_CORREO_REQUERIDO'            => 'Please enter your e-mail',
    'ERROR_USUARIO_EXISTENTE'           => 'Username already exists',
    'ERROR_CORREO_EXISTENTE'            => 'There\'s already a user registered with that e-mail',
    'ERROR_USUARIO_INEXISTENTE'         => 'There is no user with that e-mail',
    'ERROR_CONTRASENAS_DIFERENTES'      => 'Password and verification don\'t match',
    'ERROR_NOMBRE_CORTO'                => 'First name too short',
    'ERROR_APELLIDO_CORTO'              => 'Last name too short',
    'ERROR_USUARIO_CORTO'               => 'Username too short',
    'ERROR_CONTRASENA_CORTA'            => 'Password too short',
    'ERROR_SINTAXIS_NOMBRE'             => 'Invalid first name',
    'ERROR_SINTAXIS_APELLIDO'           => 'Invalid last name',
    'ERROR_SINTAXIS_USUARIO'            => 'Invalid username. Please use only lowercase.',
    'ERROR_SINTAXIS_CONTRASENA'         => 'Invalid password. Please include lowercase, uppercase and numbers.',
    'ERROR_SINTAXIS_CORREO'             => 'Invalid e-mail address',
    'ERROR_FALTA_SOBRENOMBRE'           => 'Please enter the nickname',
    'ERROR_FALTA_CIUDAD'                => 'Please enter the city',
    'ERROR_FALTA_CENTRO'                => 'Please enter the center',
    'ERROR_CIUDAD_INEXISTENTE'          => 'Please choose a city from the list',
    'ERROR_FALTA_CADENA_BUSQUEDA'       => 'Please enter the text you want to search',
    'ERROR_FALTA_CONTACTO'              => 'Please enter contact',
    'ERROR_FALTA_TITULO'                => 'Please enter title',
    'ERROR_FALTA_CONTENIDO'             => 'Please enter content',
    'ERROR_CONTACTO_INEXISTENTE'        => 'The contact is not in your list',
    'REGISTRO_USUARIO_EXITOSO'          => "User has been registered succesfully.\nWe will send a message to your e-mail address in order to activate your account.",
    'ASUNTO_MENSAJE_REGISTRO'           => '[ABLAOnline] Welcome to AblaOnline',
    'CONTENIDO_MENSAJE_REGISTRO'        => "Hi, %1\n\nWelcome to AblaOnline, your registration has been done successfully.\n\n From now on you have full access to ABLAOnline and all its wonderful features. \n\n Remember, your username is: %2.\n And your password is: %3.\n\n All future notifications will be sent to this e-mail address, if you want, you can change this option by modifying your profile.\n\nABLAOnline Staff\n\n",
    'ASUNTO_MENSAJE_CONTRASENA'         => '[ABLAOnline] Your new password',
    'CONTENIDO_MENSAJE_CONTRASENA'      => "Hi, %1\n\nYour password has been changed as requested.\n\nUsername:%2\nPassword:%3\n\nABLAOnline Staff\n\n",
    'TITULO_CONFIRMACION_CORRECTA'      => 'Congratulations',
    'CONTENIDO_CONFIRMACION_CORRECTA'   => 'Your account has been enabled. You can login now with your username and password.',
    'TITULO_CONFIRMACION_INCORRECTA'    => 'We\'re sorry',
    'CONTENIDO_CONFIRMACION_INCORRECTA' => 'Either your confirmation code is not valid or your account is already enabled..',
    'ERROR_FALTA_NOMBRE'                => 'Please enter first name',
    'ERROR_FALTA_APELLIDOS'             => 'Please enter last name',
    'SOLICITUDES_DE_AMISTAD'            => 'Friend Request',
    'NO_TIENES_CONTACTOS'               => 'You don\'t have any contacts',
    'SOLICITUD_ENVIADA'                 => 'Request Sent',
    'CLICK_PARA_BORRAR_AMISTAD'         => 'Click to Delete Friendship...',
    'CLICK_PARA_SOLICITAR_AMISTAD'      => 'Click to Request Friendship...',
    'CLICK_PARA_ENVIAR_UN_MENSAJE'      => 'Click to Send a Message...',
    'CLICK_PARA_RECHAZAR_SOLICITUD'     => 'Click to reject...',
    'YA_NO_SON_AMIGOS'                  => 'You are not friends anymore',
    'ADICIONAR_COMENTARIO'		=> 'Add comment',
    'BUSCAR_TUS_CONTACTOS'              => 'Search your contacts',
    'MIS_BLOGS'                         => 'My blogs',
    'BLOGS_QUE_ME_GUSTAN'               => 'Blogs I Like',
    'NO_TIENES_BLOGS'                   => 'You don\'t have any blogs',
    'NOTICIAS_QUE_ME_GUSTAN'            => 'News I like',
    'CURSOS_QUE_SIGO'                   => 'Training I\'m taking',
    'CURSOS_QUE_DICTO'                  => 'Training I\'m giving',
    'NO_TIENES_BLOGS_QUE_TE_GUSTEN'     => 'You don\'t have any favorite Blogs',
    'NO_TIENES_NOTICIAS_QUE_TE_GUSTEN'  => 'You don\'t have any favorite News',
    'NO_SIGUES_NINGUN_CURSO'            => 'You are not taking any training',
    'NO_DICTAS_NINGUN_CURSO'            => 'You are not giving any training',
    'VIDEO_CAMBIAR_PERFIL'		=> 'Click here to learn how to change a user profile ',
    /*** Notificaciones ***/
    'MENSAJE_COMENTARIO_PERFIL'         => '%1 has made a comment to %2.',
    'MENSAJE_CONTACTO_ADICIONADO'       => '%1 has added you as a contact.',
    'MENSAJE_ADICION_CONTACTO'          => '%1 has added %2 as a contact.',
    'MENSAJE'				=> 'Message',
    'MENSAJE_ELIMINADO'			=> 'Message has been deleted successfully',
    'RECIBIR_NOTIFICACIONES_CORREO'     => 'Do you want to receive notifications on your e-mail?',
    'INGRESE_CENTRO_BINACIONAL_A_ADMINISTRAR' => 'Select the center which is going to be managed by this user',
    /*** Mensajes de error de usuario bloqueado ****/
    
    'ASUNTO_MENSAJE_USUARIO_BLOQUEADO'    => '[ABLAOnline] Your password has been changed...',
    'CONTENIDO_MENSAJE_USUARIO_BLOQUEADO' => "Hi, %1\n\n You have tried to log in on Ablaonline three times unsuccessfully. Therefore, your password has been changed. Please go to :\n\n%2\n\n and log in with your user and this new password: \n\n%3\n\n. After this, you will be able to change your password again, in your user profile ...\n\nABLAOnline Staff\n\n"
    
    
    
);

?>
