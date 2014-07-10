<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Permisos Item
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 COLOMBO-AMERICANO
 * @version     0.1
 *
 * */
class Destacado {

    /**
     *
     * Metodo Insertar--> ingresa a la base de datos a la tabla destacados los 'me gusta'
     *
     * */
    public function insertarDestacados($idModulo, $idItem, $idUsuario) {
        global $sql;

        $datos = array(
            'id_modulo' => htmlspecialchars($idModulo),
            'id_item' => htmlspecialchars($idItem),
            'id_usuario' => htmlspecialchars($idUsuario)
        );


        $consulta = $sql->insertar('destacados', $datos);


        if ($consulta) {

            return true;
        } else {

            return false;
        }//fin del if        
    }

    /**
     *
     * Metodo Eliminar--> Es llamado cuando se requiere eliminar un punto o un 'me gusta' de destacados
     * */
    public function eliminarDestacados($idModulo, $idItem, $idUsuario) {
        global $sql;

        $condicion = 'id_item = "' . htmlspecialchars($idItem) . '" AND id_modulo = "' . htmlspecialchars($idModulo) . '" AND id_usuario = "' . htmlspecialchars($idUsuario) . '"';

        $borrar = $sql->eliminar('destacados', $condicion);

        if ($borrar) {

            return true;
        } else {

            return false;
        }//fin del if    
    }

    /**
     * Metodo que se encarga de eliminar todos los registros de me gusta de un determinado item de determinado
     * modulo en caso de que este sea eliminado
     * 
     * @global type $sql
     * @global type $configuracion
     * @param type $idModulo
     * @param type $idItem
     * @return type boolean
     */
    public function eliminarTodosDestacados($idModulo, $idItem) {
        global $sql;

        $condicion = 'id_item = "' . $idItem . '" AND id_modulo = "' . $idModulo . '"';

        $borrar = $sql->eliminar('destacados', $condicion);
        if ($borrar) {
            return true;
        } else {
            return false;
        }//fin del if    
    }

    /**
     *
     * Cantidad de destacados que tiene este item
     *
     * @param entero $id 
     *
     */
    public function cantidadDestacados($idModulo, $idItem) {
        global $sql;


        $registros = $sql->obtenerValor('destacados', 'count(*)', 'id_modulo = "' . $idModulo . '" AND id_item = "' . $idItem . '"');

        return $registros;
    }

    /**
     *
     * Saber si a un usuario determinado le gusta un Item
     *
     * @param entero $id 
     *
     */
    public function meGusta($idModulo, $idItem, $idUsuario) {
        global $sql;


        $registros = $sql->obtenerValor('destacados', 'count(*)', 'id_modulo = "' . $idModulo . '" AND id_item = "' . $idItem . '" AND id_usuario = "' . $idUsuario . '"');

        return $registros;
    }

}

