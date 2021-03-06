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
class PermisosItem {

    /**
     *
     * Metodo InsertarPerfilesCompartidos--> ingresa en la tabla permisos_item los perfiles que tienen un determinado item compartido
     * utiliza el metodo insertar de esta misma clase
     *
     * */
    public function insertarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles) {


        if ($datosVisibilidad == "privado") {//verifico si el item es privado, en caso de serlo ingreso los perfiles con los cuales se debe compartir
            foreach ($datosPerfiles as $idPerfil => $valor) {
                //$sql->depurar   = true;
                $this->insertar($idPerfil, $idModulo, $idItem);
            }//fin del foreach
        } else {//si viene publico se comparte con el perfil 99
            $idPerfil = 99;
            //$sql->depurar   = true;
            $this->insertar($idPerfil, $idModulo, $idItem);
        }//fin del if

        return true;
    }

//fin del metodo insertar perfiles

    /**
     *
     * Metodo Insertar--> ingresa a la base de datos a la tabla permisos item registros que informa de un item (por ejemplo BLOG, COURSES)
     * con que perfiles puede ser compartido
     *
     * */
    public function insertar($idPerfil, $idModulo, $idItem) {
        global $sql, $configuracion;


        $datos = array(
            "id_modulo" => $idModulo,
            "id_item" => $idItem,
            "id_perfil" => $idPerfil
        );

        $sql->guardarBitacora = false;
        $consulta = $sql->insertar("permisos_item", $datos);


        if ($consulta) {

            return $sql->ultimoId;
        } else {

            return NULL;
        }//fin del if
    }

//fin del metodo insertar

    /**
     *
     * Metodo modificarPerfilesCompartidos--> ingresa en la tabla permisos_item los perfiles que tienen un determinado item compartido
     * utiliza el metodo insertar de esta misma clase
     *
     * */
    public function modificarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles) {


        if ($datosVisibilidad == "privado") {//verifico si el item es privado, en caso de serlo ingreso los perfiles con los cuales se debe compartir
            //$sql->depurar = true;
            if (!($this->eliminar($idItem, $idModulo))) {
                return false;
            } else {
                foreach ($datosPerfiles as $idPerfil => $valor) {
                    $sql->depurar = true;
                    $this->insertar($idPerfil, $idModulo, $idItem);
                }//fin del foreach
            }
        } else {//si viene publico se comparte con el perfil 99 y solo se ingresa un registro a la BD
            $idPerfil = 99;

            //primero elimino todos los permisos que hayan para determinado item en la tabla
            //permisos item
            if (!($this->eliminar($idItem, $idModulo))) {
                return false;
            } else {
                //luego inserto los nuevos permisos
                $this->insertar($idPerfil, $idModulo, $idItem);
            }
        }//fin del if datosVisibilidad = privado

        return true;
    }

//fin del metodo modificar perfiles

    /**
     *
     * Metodo Modificar--> modifica de la base de datos la tabla permisos_item los registros que informa de un item (por ejemplo BLOG, COURSES)
     * con que perfiles puede ser compartido
     *
     * */
    public function modificar($idPerfil, $idModulo, $idItem) {
        global $sql, $configuracion;

        /* Primero debe de borrar todos los registros que encuentre de dicho modulo
          y despues debe volver a insertarlos */

        $datos = array(
            "id_modulo" => $idModulo,
            "id_item" => $idItem,
            "id_perfil" => $idPerfil
        );
        $sql->guardarBitacora = false;
        $consulta = $sql->insertar("permisos_item", $datos);

        if ($consulta) {

            return $sql->ultimoId;
        } else {

            return NULL;
        }//fin del if       
    }

//fin del metodo modificar

    /**
     *
     * Metodo Eliminar--> Es llamado cuando se requiere modificar los permisos-perfiles de un determinado blog para que primero elimine todos
     * los permisos existentes antes de volver a insertar los nuevos.    Tambien es llamado cuando se elimina determinado item de determinado modulo
     * para que borre todos los permisos relacionados a el item que se ha eliminado
     * */
    public function eliminar($idItem, $idModulo) {
        global $sql, $configuracion;

        $condicion = "id_item = '" . $idItem . "' AND id_modulo = '" . $idModulo . "'";

        $borrar = $sql->eliminar("permisos_item", $condicion);

        if ($borrar) {

            return true;
        } else {

            return false;
        }//fin del if    
    }

//Fin del metodo Eliminar

    /**
     *
     * Cargar en la variable de tipo array Perfiles, los perfiles con los cuales es compartido determinado item
     * esta información es cargada desde la BD de la tabla permisos_item
     *
     * @param entero $id Código interno o identificador del blog en la base de datos
     *
     */
    public static function cargarPerfiles($idItem, $idModulo) {
        global $configuracion, $sql;

        $perfiles = array();
        $tabla = array("permisos_item");
        $condicion = "id_modulo = '" . $idModulo . "' AND id_item = '" . $idItem . "'";

        $consulta = $sql->seleccionar($tabla, array("id_modulo", "id_item", "id_perfil"), $condicion);

        while ($perfil = $sql->filaEnObjeto($consulta)) {

            $perfiles[] = $perfil->id_perfil;
        }

        return $perfiles;
    }

//Fin del metodo cargarPerfiles
}
