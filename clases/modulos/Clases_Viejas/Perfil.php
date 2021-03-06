<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Perfiles
 * @author      Francisco J. Lozano c. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

class Perfil {

    /**
     * C�digo interno o identificador del perfil en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Valor num�rico que determina el orden o la posici�n del perfil en la base de datos
     * @var entero
     */
    public $orden;

    /**
     * URL relativa del m�dulo de perfiles
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un perfil espec�fico
     * @var cadena
     */
    public $url;

    /**
     * Nombre del perfil
     * @var cadena
     */
    public $nombre;

    /**
     * Indicador del orden cronol�gio de la lista de perfiles
     * @var l�gico
     */
    public $listaAscendente = true;

    /**
     * N�mero de registros de la lista
     * @var entero
     */
    public $registros = NULL;
    
    /**
     * Determina si el perfil es visible a la hora de ser seleccionado
     * @var entero
     */
    public $visibilidad;    

    /**
     *
     * Inicializar el perfil
     *
     * @param entero $id C�digo interno o identificador del perfil en la base de datos
     *
     */
    public function __construct($id = NULL) {

        $modulo        = new Modulo("PERFILES");
        $this->urlBase = "/".$modulo->url;
        $this->url     = $modulo->url;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos de un perfil
     *
     * @param entero $id C�digo interno o identificador del perfil en la base de datos
     *
     */
    public function cargar($id) {
        global $sql;

        if (isset($id) && $sql->existeItem("tipos_usuario", "id", intval($id))) {

            $tablas = array(
                "t" => "tipos_usuario"
            );

            $columnas = array(
                "id"            => "t.id",
                "orden"         => "t.orden",
                "nombre"        => "t.nombre",
                "visibilidad"   => "t.visibilidad"
            );

            $condicion = "t.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase."/".$this->usuario;
            }
        }
    }

    /**
     *
     * Adicionar un perfil
     *
     * @param  arreglo $datos       Datos del perfil a adicionar
     * @return entero               C�digo interno o identificador del perfil en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql;

        $orden = $datos["orden"];
        
        //nuevos datos que recibo sobre los perfiles con los que comparto y la visibilidad
        $datosPerfiles     =  $datos["perfiles"];
        $datosVisibilidad  =  $datos["visibilidad"];
        
        

        while ($sql->existeItem("tipos_usuario", "orden", $orden)) {
            $orden++;
        }
        

        $tipo = array(
            "orden"  => $orden,
            "nombre" => $datos["nombre"]
        );
        
        if($datosVisibilidad == "privado"){
           $tipo["otros_perfiles"] = '1'; 
        }else{
           $tipo["otros_perfiles"] = '0'; 
        }
        
        if(isset($datos["visible"])){
           $tipo["visibilidad"] = '1'; 
        }else{
           $tipo["visibilidad"] = '0'; 
        }        

        $consulta = $sql->insertar("tipos_usuario", $tipo);
        $idTipo   = $sql->ultimoId;

        if ($consulta) {

            foreach ($datos["permisos"] as $id => $niveles) {
                $permisos = array(
                    "id_tipo_usuario" => $idTipo,
                    "id_modulo"       => $id,
                );

                $permisos += $niveles;
                $consulta  = $sql->insertar("permisos", $permisos);
            }
            
            if($datosVisibilidad == "privado"){//verifico si es privado es porque tiene permisos sobre otros perfiles  
                self::insertarPerfilesHijos($idTipo, $datosPerfiles);
            }
            
            return $idTipo;

        } else {
            return NULL;
        }
    }

    /**
     *
     * Modificar un perfil
     *
     * @param  arreglo $datos       Datos del perfil a modificar
     * @return l�gico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        
        //nuevos datos que recibo sobre los perfiles con los que comparto y la visibilidad
        $datosPerfiles     =  $datos["perfiles"];
        $datosVisibilidad  =  $datos["visibilidad"];

        $orden = $datos["orden"];

        while ($sql->existeItem("tipos_usuario", "orden", $orden)) {
            $orden--;
        }

        $tipo = array(
            "orden"  => $orden,
            "nombre" => $datos["nombre"]
        );
        
        if($datosVisibilidad == "privado"){
           $tipo["otros_perfiles"] = '1'; 
        }else{
           $tipo["otros_perfiles"] = '0'; 
           $sql->eliminar("relacion_perfiles", "perfil_padre = '".$this->id."'");
        }
        
        if(isset($datos["visible"])){
           $tipo["visibilidad"] = '1'; 
        }else{
           $tipo["visibilidad"] = '0'; 
        }           

        $modificacion = $sql->modificar("tipos_usuario", $tipo, "id = '".$this->id."'");

        if ($modificacion) {
            $consulta = $sql->eliminar("permisos", "id_tipo_usuario = '".$this->id."'");

            foreach ($datos["permisos"] as $id => $niveles) {
                $permisos = array(
                    "id_tipo_usuario" => $this->id,
                    "id_modulo"       => $id,
                );

                $permisos += $niveles;
                $consulta  = $sql->insertar("permisos", $permisos);
            }
            
            if($datosVisibilidad == "privado"){//verifico si es privado es porque tiene permisos sobre otros perfiles  
                self::modificarPerfilesHijos($this->id, $datosPerfiles);
            }

            return $modificacion;

        } else {
            return NULL;
        }
    }

    /**
     *
     * Eliminar un perfil
     *
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        
        if ( $sql->existeItem("usuarios", "id_tipo", $this->id) ) {
            
            $datos = array(
                "id_tipo" => "99"
            );

            $consulta = $sql->modificar("usuarios", $datos, "id_tipo = '" . $this->id . "'");
        }
        
        if (!$sql->existeItem("usuarios", "id_tipo", $this->id)) {

            $consulta = $sql->eliminar("permisos", "id_tipo_usuario = '".$this->id."'");
            $consulta = $sql->eliminar("permisos_item", "id_perfil = '".$this->id."'");
            $consulta = $sql->eliminar("relacion_perfiles", "perfil_padre = '".$this->id."'");
            $consulta = $sql->eliminar("tipos_usuario", "id = '".$this->id."'");
            return $consulta;
        }

    }

    /**
     *
     * Subir de nivel un perfil
     *
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function subir() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array("tipos_usuario"), array("id", "orden"), "orden > '".$this->orden."'", "id", "orden ASC", 0, 1);

        if ($sql->filasDevueltas) {
            $temporal = 0;
            $perfil   = $sql->filaEnObjeto($consulta);

            while ($sql->existeItem("tipos_usuario", "orden", $temporal)) {
                $temporal++;
            }

            $abajo  = $sql->modificar("tipos_usuario",array("orden" => $temporal), "id = '".$this->id."'");
            $arriba = $sql->modificar("tipos_usuario",array("orden" => $this->orden), "id = '".$perfil->id."'");
            $abajo  = $sql->modificar("tipos_usuario",array("orden" => $perfil->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     *
     * Bajar de nivel un perfil
     *
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function bajar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array("tipos_usuario"), array("id", "orden"), "orden < '".$this->orden."'", "id", "orden DESC", 0, 1);

        if ($sql->filasDevueltas) {
            $temporal = 0;
            $perfil   = $sql->filaEnObjeto($consulta);

            while ($sql->existeItem("tipos_usuario", "orden", $temporal)) {
                $temporal++;
            }

            $arriba = $sql->modificar("tipos_usuario",array("orden" => $temporal), "id = '".$this->id."'");
            $abajo  = $sql->modificar("tipos_usuario",array("orden" => $this->orden), "id = '".$perfil->id."'");
            $arriba = $sql->modificar("tipos_usuario",array("orden" => $perfil->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     *
     * Listar los perfiles
     *
     * @param entero  $cantidad    N�mero de perfiles a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los c�digos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condici�n adicional (SQL)
     * @return arreglo             Lista de perfiles
     *
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql;

        /*** Validar la fila inicial de la consulta ***/
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*** Validar la cantidad de registros requeridos en la consulta ***/
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*** Validar que la condici�n sea una cadena de texto ***/
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*** Validar que la excepci�n sea un arreglo y contenga elementos ***/
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion).",0";

        } else {
            $excepcion .= "0";
        }

        $condicion .= "t.id NOT IN ($excepcion)";

        /*** Definir el orden de presentaci�n de los datos ***/
        if ($this->listaAscendente) {
            $orden = "t.orden DESC";
        } else {
            $orden = "t.orden ASC";
        }

        $tablas = array(
            "t" => "tipos_usuario",
        );

        $columnas = array(
            "id"     => "t.id",
            "orden"  => "t.orden",
            "nombre" => "t.nombre"
        );

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {
            
            while ($perfil = $sql->filaEnObjeto($consulta)) {
                $perfil->url = $this->urlBase."/".$perfil->id;
                $lista[]   = $perfil;
            }
        }

        return $lista;

    }




/**
*
*Metodo que se encarga de mostrar los checkBoxes con los perfiles aptos para compartir la info
*En los formularios de modificacion de informacion
*
**/
   public static function mostrarChecks($id = NULL,  $idModulo = NULL){
    global $textos, $sql;
      $cod = "";
      $perfiles = array();
        $excepcion = "0,99";
        $condicion = "t.id NOT IN ($excepcion)";

        $tablas = array(
            "t" => "tipos_usuario",
        );

        $columnas = array(
            "id"     => "t.id",
            "nombre" => "t.nombre"
        );       

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $arreglo= array();

            while ($perfil = $sql->filaEnObjeto($consulta)) {
                $arreglo[]   = $perfil;
            }
        }


   
      foreach ($arreglo as $elemento) { 
  
             if(!empty($id)){
               
               $perfiles = PermisosItem::cargarPerfiles($id, $idModulo);
               $seleccionado  = (in_array($elemento->id, $perfiles)) ? true : false;
               $cod.= HTML::campoChequeo("datos[perfiles][$elemento->id]", $seleccionado).$elemento->nombre."<br>";

               }else{
                $cod.= HTML::campoChequeo("datos[perfiles][$elemento->id]", false).$elemento->nombre."<br>"; 

              }//fin del if                

       }//fin del foreach 



      if(!empty($id)) {
            if(!in_array(99, $perfiles)){
                $opciones = array("style" => "display:block");
                $cod2 =  HTML::contenedor(HTML::parrafo($textos->id("SELECCIONAR_COMPARTIR_ITEM"), "centrado negrita").$cod, "listaCheckUsuarios", "listaCheckUsuarios", $opciones);
            }else{
                $opciones = array("style" => "display:none");
                $cod2 =  HTML::contenedor(HTML::parrafo($textos->id("SELECCIONAR_COMPARTIR_ITEM"), "centrado negrita").$cod, "listaCheckUsuarios", "listaCheckUsuarios", $opciones);
                
            }
       }else{
             $opciones = array("style" => "display:none");
             $cod2 =  HTML::contenedor(HTML::parrafo($textos->id("SELECCIONAR_COMPARTIR_ITEM"), "centrado negrita").$cod, "listaCheckUsuarios", "listaCheckUsuarios", $opciones);
                
       }

         //pongo los dos radiobutton que verifica si es publico a privado
        $opcionesPublico = array("onClick" => "$('#listaCheckUsuarios').css({ display: 'none'})"); //cargo las opciones, en este caso
        $opcionesPrivado = array("onClick" => "$('#listaCheckUsuarios').css({ display: 'block'})");//eventos javascript


        if(!empty($id)){
            if(in_array(99, $perfiles)){ 
                        $cod3 .= HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");
                }else{                          
                        $cod3 .= HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad]", "si", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");
            }
        }else{
             $cod3 .= HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");

        }

        //$cod3 = HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");


      return $cod3.$cod2;

    }//fin del metodo mostrarChecks Modificar





/**
*
*Metodo para verificar los permisos para a�adir contenido del "tipo de usuario" del usuario que ha iniciado la sesion
*sobre determinado modulo.
*
**/



    public static function verificarPermisosAdicion($modulo){
        global $sql, $sesion_usuarioSesion;
        
        if(is_string($modulo) && !is_numeric($modulo)){            
            $modulo = $sql->obtenerValor("modulos", "id", "BINARY nombre = '".$modulo."'");
        }


        if(!isset($sesion_usuarioSesion)){
            return NULL;
        }

        $perfil    = $sesion_usuarioSesion->idTipo;
        $condicion = "id_tipo_usuario = '".$perfil."' AND id_modulo = '".$modulo."'";
        $permiso   = $sql->obtenerValor("permisos", "nivel_adicion", $condicion);

        return $permiso;

    }//fin del metodo verfificar permiso adicion



/**
*
*Metodo para verificar los permisos para visualizar un contenido del "tipo de usuario" del usuario que ha iniciado la sesion
*sobre determinado modulo.
*
**/



    public static function verificarPermisosConsulta($modulo){
        global $sql, $sesion_usuarioSesion;
        
        if(is_string($modulo) && !is_numeric($modulo)){            
            $modulo = $sql->obtenerValor("modulos", "id", "BINARY nombre = '".$modulo."'");
        }

        if(!isset($sesion_usuarioSesion)){
            return NULL;
        }

        $perfil    = $sesion_usuarioSesion->idTipo;
        $condicion = "id_tipo_usuario = '".$perfil."' AND id_modulo = '".$modulo."'";
        $permiso   = $sql->obtenerValor("permisos", "nivel_consulta", $condicion);

        return $permiso;

    }//fin del metodo verfificar permiso adicion

    
    

    /**
*
*Metodo para verificar los permisos para visualizar un contenido del "tipo de usuario" del usuario que ha iniciado la sesion
*sobre determinado modulo.
*
**/

    public static function verOtrosPerfiles(){
        global $sql, $sesion_usuarioSesion;       
        
        if(!isset($sesion_usuarioSesion)){
            return NULL;
        }
        
        
        $consulta = $sql->obtenerValor("tipos_usuario", "otros_perfiles", "id = '".$sesion_usuarioSesion->idTipo."'");//verifico si este tipo de usuario puede ver otros perfiles
        $perfiles = array();
        //$arreglo  = array();
        
        if(!$consulta){
            return NULL;
        }else{
            $sSql = $sql->seleccionar(array("relacion_perfiles"), array("perfil_hijo"), "perfil_padre = '".$sesion_usuarioSesion->idTipo."'");
            
            
            while ($perfil = $sql->filaEnObjeto($sSql)) {
                $perfiles[] = $perfil->perfil_hijo;
            }
            //print_r($perfiles);
            //Recursos::escribirTxt("quinto nivel: ".$perfiles);
        }

        return $perfiles;

    }//fin del metodo verfificar permiso adicion
    
    
    
    
    
   
    
    
/**
*
* Metodo que se encarga de mostrar los checkBoxes con los perfiles para seleccionar 
* sobre cuales perfiles un perfil padre tendra permisos, esto con el fin de que
* un determinado perfil tenga la posibilidad de visualizar items de otros perfiles
*
**/
   public static function mostrarChecksPerfiles($idPerfil){
    global $textos, $sql;
    
      $cod = "";
        $perfiles = array();
        $excepcion = "0,99";
        $condicion = "t.id NOT IN ($excepcion)";

        $tablas = array(
            "t" => "tipos_usuario",
        );

        $columnas = array(
            "id"     => "t.id",
            "nombre" => "t.nombre"
        );       

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $arreglo= array();

            while ($perfil = $sql->filaEnObjeto($consulta)) {
                $arreglo[]   = $perfil;
            }
        }


      foreach ($arreglo as $elemento) { 
  
             if(!empty($idPerfil)){
               
               $perfiles = self::cargarPerfilesHijos($idPerfil);
               $seleccionado  = (in_array($elemento->id, $perfiles)) ? true : false;
               $cod.= HTML::campoChequeo("datos[perfiles][$elemento->id]", $seleccionado).$elemento->nombre."<br>";

               }else{
                $cod.= HTML::campoChequeo("datos[perfiles][$elemento->id]", false).$elemento->nombre."<br>"; 

              }//fin del if                

       }//fin del foreach 

      //Recursos::escribirTxt("si llegue a mostrar checks perfiles: ".$idPerfil);
      $tieneHijos = $sql->obtenerValor("relacion_perfiles", "COUNT(perfil_hijo)", "perfil_padre = '".$idPerfil."'");

      if(!empty($tieneHijos)) {
            if(!in_array(99, $perfiles)){
                $opciones = array("style" => "display:block");
                $cod2 =  HTML::contenedor(HTML::parrafo($textos->id("SELECCIONAR_PERFILES_HIJOS"), "centrado negrita").$cod, "listaCheckUsuarios", "listaCheckUsuarios", $opciones);
            }else{
                $opciones = array("style" => "display:none");
                $cod2 =  HTML::contenedor(HTML::parrafo($textos->id("SELECCIONAR_PERFILES_HIJOS"), "centrado negrita").$cod, "listaCheckUsuarios", "listaCheckUsuarios", $opciones);
                
            }
       }else{
             $opciones = array("style" => "display:none");
             $cod2 =  HTML::contenedor(HTML::parrafo($textos->id("SELECCIONAR_PERFILES_HIJOS"), "centrado negrita").$cod, "listaCheckUsuarios", "listaCheckUsuarios", $opciones);
                
       }

         //pongo los dos radiobutton que verifica si es publico a privado
        $opcionesNoHijos = array("onClick" => "$('#listaCheckUsuarios').css({ display: 'none'})"); //cargo las opciones, en este caso
        $opcionesSiHijos = array("onClick" => "$('#listaCheckUsuarios').css({ display: 'block'})");//eventos javascript


        if(!empty($tieneHijos)){
            if(in_array(99, $perfiles)){ 
                        $cod3 .= HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesNoHijos).HTML::frase($textos->id("NO"), "margenDerechaDoble").HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesSiHijos).$textos->id("SI"), "margenSuperior");
                }else{                          
                        $cod3 .= HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "", "", "publico", $opcionesNoHijos).HTML::frase($textos->id("NO"), "margenDerechaDoble").HTML::radioBoton("datos[visibilidad]", "si", "", "privado", $opcionesSiHijos).$textos->id("SI"), "margenSuperior");
            }
        }else{
             $cod3 .= HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesNoHijos).HTML::frase($textos->id("NO"), "margenDerechaDoble").HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesSiHijos).$textos->id("SI"), "margenSuperior");

        }

        //$cod3 = HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");


      return $cod3.$cod2;

    }//fin del metodo mostrarChecks Modificar



    
    /**
     * Metodo que carga sobre que perfiles tiene permiso un determinado perfil padre
     * @global type $configuracion
     * @global type $sql
     * @param type $idItem
     * @param type $idModulo
     * @return type 
     */
        public static function cargarPerfilesHijos($idPerfil) {
        global $sql;

        $perfiles       =  array();
        $tabla          =  array("relacion_perfiles");
        $condicion      =  "perfil_padre = '".$idPerfil."'";  
        
        //$sql->depurar   = true;
        $consulta       =  $sql->seleccionar($tabla, array("perfil_hijo"), $condicion);

        while($perfil = $sql->filaEnObjeto($consulta)){
            $perfiles[] = $perfil->perfil_hijo;
        }   
        
        
        return $perfiles;

     }  //Fin del metodo cargarPerfiles
    
    
     
  /**
   * Metodo que inserta en la tabla relacion_perfiles los perfiles hijos de
   * determinado perfil padre
   * @global type $sql
   * @global type $sesion_usuarioSesion
   * @param type $datosPerfiles
   * @return boolean 
   */
  public static function insertarPerfilesHijos($idPerfil, $datosPerfiles){
      global $sql;

               foreach($datosPerfiles as $perfilHijo => $valor){ 
                   
                  $datos = array(
                      "perfil_padre" => $idPerfil,
                      "perfil_hijo"  => $perfilHijo
                  ); 
                  
                  $sql->insertar("relacion_perfiles", $datos);
                }//fin del foreach
  

        return true;

   }
   
   
   
   
     /**
   * Metodo que modifica en la tabla relacion_perfiles los perfiles hijos de
   * determinado perfil padre
   * @global type $sql
   * @global type $sesion_usuarioSesion
   * @param type $datosPerfiles
   * @return boolean 
   */
  public static function modificarPerfilesHijos($idPerfil, $datosPerfiles){
      global $sql;

      $cantidad = $sql->obtenerValor("relacion_perfiles", "COUNT(perfil_padre)", "perfil_padre = '".$idPerfil."'");
      
      if($cantidad){
        $sql->eliminar("relacion_perfiles", "perfil_padre = '".$idPerfil."' ");
      }
      
      self::insertarPerfilesHijos($idPerfil, $datosPerfiles);  

      return true;

   }
     
     
    

}
?>
