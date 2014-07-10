<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Destacados
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón , William Vargas
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/

if (isset($url_accion)) {
    switch ($url_accion) {
        case "addHighLight"     :   insertarMeGusta($forma_id_modulo, $forma_id_item, $forma_id_usuario);
                                    break;
        case "delHighLight"     :   eliminarMeGusta($forma_id_modulo, $forma_id_item, $forma_id_usuario);
                                    break; 
        case "addComunicado"     :   eliminarMeGusta($forma_id_modulo, $forma_id_item, $forma_id_usuario);
                                    break;
      
    }
}



/**
 *
 * Insertar un Registro en la tabla de destacados, el equivalente a me Gusta de FaceBook
 *
 * @param  arreglo $datos       Datos del me gusta
 *
 */
function insertarMeGusta($idModulo, $idItem, $idUsuario) {
    global $textos, $sql, $configuracion;

    $des = new Destacado();


        $datos = array(
                "id_modulo"  =>  $idModulo,
                "id_item"    =>  $idItem,
                "id_usuario" =>  $idUsuario
                );
 
   $nuevoDestacado = $des->insertarDestacados($idModulo, $idItem, $idUsuario);
   

        if ($nuevoDestacado) {            
            $respuesta["error"]     = NULL;
            $respuesta["accion"]    = "insertar";
            $respuesta["limpiar"]   = false;
            $respuesta["contenido"] = Recursos::cargarMegusta($idModulo, $idItem, $idUsuario); 
            $respuesta["destino"]   = "#meGusta";


        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    
    Servidor::enviarJSON($respuesta);
}







/**
 *
 * Eliminar un destacado
 *
 * @param  arreglo $datos       Datos del destacado
 *
 */
function eliminarMeGusta($idModulo, $idItem, $idUsuario) {
    global $textos, $sql, $configuracion;

    $des = new Destacado();

    $datos = array(
                "id_modulo"  =>  $idModulo,
                "id_item"    =>  $idItem,
                "id_usuario" =>  $idUsuario
                );

 
   $nuevoDestacado = $des->eliminarDestacados($idModulo, $idItem, $idUsuario);
   

        if ($nuevoDestacado) {           
            $respuesta["error"]     = NULL;
            $respuesta["accion"]    = "insertar";
            $respuesta["limpiar"]   = false;
            $respuesta["contenido"] = Recursos::cargarMegusta($idModulo, $idItem, $idUsuario); 
            $respuesta["destino"]   = "#meGusta";


        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    
    Servidor::enviarJSON($respuesta);
}





?>
