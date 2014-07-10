<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Comunicados
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/

class Comunicado {



    /**
     * Código interno o identificador del comunicado en la base de datos
     * @var entero
     */
    public $id;



    /**
     * URL relativa del módulo de comunicados
     * @var cadena
     */
    public $urlBase;



    /**
     * URL relativa de un comunicado específica
     * @var cadena
     */
    public $url;


     /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;




    /**
     * Título del comunicado
     * @var cadena
     */
    public $titulo;



    /**
     * Contenido completo del comunicado
     * @var cadena
     */
    public $contenido;



    /**
     * Fecha de publicación del comunicado
     * @var fecha
     */
    public $fechaPublicacion;





    /**
     *
     * Inicializar el comunicado
     *
     * @param entero $id Código interno o identificador del comunicado en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $configuracion, $sql;

        $modulo         = new Modulo("COMUNICADO");
        $this->urlBase  = "/".$modulo->url;
        $this->url      = $modulo->url;
        $this->idModulo = $modulo->id;
        $this->id       = 1;

        $consulta        = $sql->filaEnObjeto($sql->seleccionar(array("comunicado"), array("registros" => "COUNT(id)")));
        $this->registros = $consulta->registros;
       

        if (isset($id)) {
            $this->cargar($id);
            
        }
     }//Fin del metodo constructor





    /**
     *
     * Cargar los datos de un comunicado
     *
     * @param entero $id Código interno o identificador del comunicado en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("comunicado", "id", intval($id))) {

            $tablas = array(
                "c" => "comunicado"
            );

            $columnas = array(
                "id"                 => "c.id",               
                "titulo"             => "c.titulo",
                "contenido"          => "c.contenido",
                "fechaPublicacion"   => "c.fecha"
            );

            $condicion = "c.id = '$id'";
            //$sql->depurar = true;
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

            }
        }
    }//Fin del metodo Cargar






    /**
     *
     * Modificar un comunicado
     *
     * @param  arreglo $datos       Datos de la comunicado a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
 public function modificar($datos) {
        global $sql, $configuracion, $modulo;

       
        $datosComunicado = array();

        $datosComunicado["titulo"]     = $datos["titulo"];
        $datosComunicado["contenido"]  = $datos["contenido"];
        $datosComunicado["fecha"]      = date("Y-m-d H:i:s");
 

     $sql->depurar = true;
     $consulta = $sql->modificar("comunicado", $datosComunicado, "id = '1'");

        if($consulta){
            return true;

        }else{

        return false;

        }//fin del if(consulta)



 }//fin del metodo Modificar




}
?>