<?php

/**
 *
 * @package     FOLCS
 * @subpackage  ENLACE
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/

class Enlace {

    /**
     * Código interno o identificador del archivo en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa de un archivo específico
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del módulo al cual pertenece el enlace en la base de datos
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador del registro del módulo al cual pertenece el enlace en la base de datos
     * @var entero
     */
    public $idRegistro;

    /**
     * Código interno o identificador del usuario creador del enlace en la base de datos
     * @var entero
     */
    public $idAutor;

   /**
     * icono que representa al modulo
     * @var entero
     */
    public $icono;

    /**
     * Nombre de usuario (login) del usuario creador del enlace
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del enlace
     * @var cadena
     */
    public $autor;

    /**
     * Título del enlace
     * @var cadena
     */
    public $titulo;

    /**
     * Descripción corta del enlace
     * @var cadena
     */
    public $descripcion;

    /**
     * Fecha del enlace
     * @var cadena
     */
    public $fecha;

    /**
     * Indicador del estado del enlace
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de temas
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;
    
    /**
    * ruta del enlace
    * @var entero
    */
    public $enlace;

    /**
     *
     * Inicializar el enlace
     *
     * @param entero $id Código interno o identificador del enlace en la base de datos
     *
     */
    public function __construct($id = NULL) {
              
        $modulo         = new Modulo("ENLACES");
        $this->urlBase  = "/".$modulo->url;
        $this->url      = $modulo->url;
        $this->idModulo = $modulo->id;

        if (isset($id)) {
            $this->cargar($id);
           
        }

    }

    /**
     *
     * Cargar los datos del enlace
     *
     * @param entero $id Código interno o identificador del enlace en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("enlaces", "id", intval($id))) {

            $tablas = array(
                "e" => "enlaces",
                "u" => "usuarios"
           );

           $columnas = array(
                "id"            => "e.id",
                "idAutor"       => "e.id_usuario",
                "usuarioAutor"  => "u.usuario",
                "autor"         => "u.sobrenombre",
                "titulo"        => "e.titulo",
                "fecha"         => "e.fecha",
                "descripcion"   => "e.descripcion",
                "enlace"        => "e.enlace"
            );

            $condicion = "e.id_usuario = u.id AND e.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
               }

               $this->fotoAutor   = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesMiniaturas"]."/".$this->fotoAutor;
               $this->icono       = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."/link.jpg";
            }
       }
    }

    /**
     *
     * Adicionar un Enlace
     *
     * @param  arreglo $datos       Datos del enlace a adicionar
     * @return entero               Código interno o identificador del enlace en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
       global $sql, $sesion_usuarioSesion;
       
       //nuevos datos que recibo sobre los perfiles con los que comparto y la visibilidad
       $datosPerfiles      =  $datos["perfiles"];
       $datosVisibilidad   =  $datos["visibilidad"]; 
       
       
        if (isset($datos["activo"])) {
            $datosRecurso["activo"] = "1";
            $datosRecurso["fecha"]  = date("Y-m-d");
        } else {
            $datosRecurso["activo"] = "0";
            $datosRecurso["fecha"]  = NULL;
        }

        $datosRecurso = array(
            "id_modulo"     => $datos["idModulo"],
            "id_registro"   => $datos["idRegistro"],
            "id_usuario"    => $sesion_usuarioSesion->id,
            "titulo"        => $datos["titulo"],
            "descripcion"   => $datos["descripcion"],
            "enlace"        => $datos["enlace"]
        );

        $consulta = $sql->insertar("enlaces", $datosRecurso);

        if ($consulta) {
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
             
             $permisosItem    =  new PermisosItem();
             $idModulo        =  $this->idModulo;
             $idItem          =  $sql->ultimoId;
             $permisosItem->insertarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);
             return $idItem;

        } else {
            return NULL;
        }//fin del if($consulta)
        
    }
    
    /**
     *
     * Eliminar un enlace
     *
     * @param entero $id    Código interno o identificador del enlace en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;
       
        if (!isset($this->id)) {
            return NULL;
        }

        if(!($consulta = $sql->eliminar("enlaces", "id = '".$this->id."'"))){                  
            return false;
            
         }else{
           /* Eliminar todos los "me gusta" que pueda tener el Blog */ 
//            if ($this->cantidadMeGusta > 0) {
//                $destacado = new Destacado();
//                $destacado->eliminarTodosDestacados($this->idModulo, $this->id);
//            }            
            $permisosItem    = new PermisosItem();

            if(!($permisosItem->eliminar($this->id, $this->idModulo))){
              return false;
              }else{
              return true;
            }

            return true;

         }//fin del si funciono eliminar

    }

    /**
     *
     * Listar los enlaces de un registro en un módulo
     *
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de archivos hechos al registro del módulo
     *
     */
    public function listar($inicio = 0, $cantidad = 0, $modulo = NULL, $registro = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;
        
        if(!isset($modulo) || !isset($registro)){
            return false;
        }

        /*** Validar la fila inicial de la consulta ***/
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*** Validar la cantidad de registros requeridos en la consulta ***/
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }
        
        $modulo = new Modulo($modulo);

        $tablas = array(
            "e" => "enlaces",
            "u" => "usuarios"
        );

           $columnas = array(
                "id"            => "DISTINCT e.id",
                "idAutor"       => "e.id_usuario",
                "usuarioAutor"  => "u.usuario",
                "autor"         => "u.sobrenombre",
                "titulo"        => "e.titulo",
                "descripcion"   => "e.descripcion",
                "fecha"         => "e.fecha",
                "enlace"        => "e.enlace",
               
            );

       if($modulo->nombre == "USUARIOS"){//si el modulo es usuarios no necesito saber a que registro en especial pertenece
           $condicion1 = "(e.id_usuario = u.id AND e.id_modulo = '$modulo->id'";     
       }else{
           $condicion1 = "(e.id_usuario = u.id AND e.id_registro = '$registro' AND e.id_modulo = '$modulo->id' ";     
       }
       
           
       $idPerfil = $sesion_usuarioSesion->idTipo;
           
           
       if($idPerfil != 99){
            if($idPerfil != 0){
                
                
                //codigo para perfiles que pueden ver items de varios perfiles
                $condicion2 = "";                
                $tienePrivilegios = $sql->obtenerValor("tipos_usuario", "otros_perfiles", "id = '".$sesion_usuarioSesion->idTipo."'");
                if( isset($sesion_usuarioSesion) &&  $tienePrivilegios){
                    $otrosPerfiles = Perfil::verOtrosPerfiles();//arreglo con los otros perfiles sobre los cuales este tiene privilegios
                    //print_r($otrosPerfiles);
                    $otrosPerfiles = implode(",", $otrosPerfiles);
                    $condicion2 = ", $otrosPerfiles ";//condicion extra para ver items de los otros perfiles sobre los cuales tiene privilegios                  
                }
                
                
                $tablas["pi"]           = "permisos_item";
                $columnas["idItem"]     = "pi.id_item";
                $columnas["idModulo"]   = "pi.id_modulo";

                $condicion  = $condicion1." AND pi.id_item = e.id AND pi.id_modulo = '".$this->idModulo."' AND pi.id_perfil IN (99, '$idPerfil'$condicion2) )";//que me liste los links en los cuales el usuario actual tiene permiso para visualizar
                $condicion .= " OR ";
                $condicion .= $condicion1." AND e.id_usuario = '$sesion_usuarioSesion->id' AND pi.id_modulo = '".$this->idModulo."' AND pi.id_item = e.id)";//o donde el usuario es el autor del link
 
            }else{
                $condicion = $condicion1.")";
            }
                

        } else {
                $tablas["pi"]           = "permisos_item";
                $columnas["idItem"]     = "pi.id_item";
                $columnas["idPerfil"]   = "pi.id_perfil";
                $columnas["idModulo"]   = "pi.id_modulo";

                $condicion.= $condicion1." AND pi.id_item = e.id AND pi.id_modulo = '".$this->idModulo."' AND pi.id_perfil = '99' )";

        }
        
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", "fecha DESC", $inicio, $cantidad);
        $lista = array();
        if ($sql->filasDevueltas) {            

            while ($evento = $sql->filaEnObjeto($consulta)) {
                $evento->url       = $this->urlBase."/".$evento->id;
                $evento->icono     = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."/link.jpg";
                $lista[]           = $evento;
            }
        }

        return $lista;

    }
    
    /**
     *
     * Contar la cantidad de imagenes de un registro en un módulo
     *
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return entero              Número de imagenes hechos al registro del módulo
     *
     */
    public function contar($modulo, $registro) {
        global $sql;
        
        
        $tablas = array(
            "a" => "enlaces",
            "m" => "modulos"
        );

        $columnas = array(
            "registros" => "COUNT(a.id)"
        );

        $condicion = "a.id_modulo = m.id AND a.id_registro = '$registro' AND m.nombre = '$modulo'";
        
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $archivo  = $sql->filaEnObjeto($consulta);
            return $archivo->registros;
        } else {
            return NULL;
        }
    }
    
}

