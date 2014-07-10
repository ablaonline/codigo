<?php
/**
 *
 * @package     FOLCS
 * @subpackage  Estados
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/
class Estado {

    /**
     * Código interno o identificador del país en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de el Estado
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de una estado específica
     * @var cadena
     */
    public $url;

    /**
     * Nombre del estado
     * @var cadena
     */
    public $nombre;

    /**
     * id del Pais
     * @var cadena
     */
    public $idPais;

     /**
     * nombre del Pais
     * @var cadena
     */
    public $pais;     

     /**
     * nombre solo del Pais
     * @var cadena
     */
    public $paisSolo; 

     /**
     * nombre del Estado
     * @var cadena
     */
    public $codigo;      

    /**
     * Indicador del orden cronológio de la lista de estados
     * @var lógico
     */
    public $listaAscendente = true;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;
    
    /**
     * Número de registros activos de la lista de foros
     * @var entero
     */
    public $registrosConsulta = NULL;    
    
    /**
     * Orden predeterminado para organizar los listados
     * @var entero
     */
    public $ordenInicial = NULL;       

    /**
     *
     * Inicializar el Estado
     *
     * @param entero $id Código interno o identificador de el Estado en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo        = new Modulo("ESTADOS");
        $this->urlBase = "/".$modulo->url;
        $this->url     = $modulo->url;
       
        $this->registros = $sql->obtenerValor("estados", "COUNT(id)", "id != '0'");
        //establecer el valor del campo predeterminado para organizar los listados
        $this->ordenInicial = "nombre";

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos de un Estado
     *
     * @param entero $id Código interno o identificador de el Estado en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("estados", "id", intval($id))) {

            $tablas = array(
                "e" => "estados",
                "p" => "paises"
            );

            $columnas = array(
                "id"       => "e.id",
                "idPais"   => "e.id_pais",
                "nombre"   => "e.nombre",
                "pais"     => "p.nombre",
                "codigo"   => "e.codigo"
            );

            $condicion = "e.id_pais = p.id AND e.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }
                
                $this->paisSolo = $this->pais;
                $this->pais = HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["iconosBanderas"] . "/" . strtolower($this->codigo) . ".png", "miniaturaBanderas margenDerecha").$this->pais;
                $this->url = $this->urlBase."/".$this->usuario;
            }
        }
    }

    /**
     *
     * Adicionar un estado
     *
     * @param  arreglo $datos       Datos de el Estado a adicionar
     * @return entero               Código interno o identificador de el Estado en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql;
        
        $idPais = $sql->obtenerValor("paises", "id", "nombre = '".utf8_decode($datos["id_pais"]) ."'");
        
        $datosEstado = array(
            "nombre"    => $datos["nombre"],
            "id_pais" => $idPais
        );

        $consulta = $sql->insertar("estados", $datosEstado);

        if ($consulta) {
            return $sql->ultimoId;

        } else {
            return NULL;
        }
    }

    /**
     *
     * Modificar un estado
     *
     * @param  arreglo $datos       Datos de el Estado a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        
        $idPais = $sql->obtenerValor("paises", "id", "nombre = '".utf8_decode($datos["id_pais"])."'");
        
        $datosEstado = array(
            "nombre"    => $datos["nombre"],
            "id_pais" => $idPais
        );
        

        $consulta = $sql->modificar("estados", $datosEstado, "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Eliminar una estado
     *
     * @param entero $id    Código interno o identificador de el Estado en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("estados", "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Listar las estados
     *
     * @param entero  $cantidad    Número de ciudadesa incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de estados
     *
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicionGlobal = NULL, $orden = NULL) {
        global $sql, $configuracion;

        /*** Validar la fila inicial de la consulta ***/
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*** Validar la cantidad de registros requeridos en la consulta ***/
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*** Validar que la condición sea una cadena de texto ***/
        if (!is_string($condicionGlobal)) {
            $condicion = "";
        }

        /*** Validar que la excepción sea un arreglo y contenga elementos ***/
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion = "e.id NOT IN ($excepcion) AND ";
        }


        /*** Definir el orden de presentación de los datos ***/
        if(!isset($orden)){
            $orden = $this->ordenInicial;
        }
        if ($this->listaAscendente) {
            $orden = "$orden ASC";

        } else {
            $orden = "$orden DESC";
        }

            $tablas = array(
                "e" => "estados",
                "p" => "paises"
            );

            $columnas = array(
                "id"       => "e.id",
                "idPais"   => "e.id_pais",
                "nombre"   => "e.nombre",
                "pais"     => "p.nombre",
                "codigo"   => "e.codigo"
            );

        if (!empty($condicionGlobal)) {
            
            $condicion .= $condicionGlobal." AND ";
        } 
        
        $condicion .= "e.id_pais = p.id";
       
        if (is_null($this->registrosConsulta)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registrosConsulta = $sql->filasDevueltas;
        }
        
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($estado = $sql->filaEnObjeto($consulta)) {
                $estado->url = $this->urlBase."/".$estado->id;
                $estado->pais = HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["iconosBanderas"] . "/" . strtolower($estado->codigo) . ".png", "miniaturaBanderas margenDerechaTriple").$estado->pais;
                $lista[]   = $estado;
            }
        }

        return $lista;

    }
    
    /**
     *
     * Generar Tabla método encargado de generar la grilla donde se desplegar{a la información
     * del listado de estados
     *
     * @param  arreglo $datos       Datos de el Estado a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function generarTabla($arregloRegistros, $datosPaginacion = NULL){
        global $textos;
        //Declaracion de las columnas que se van a mostrar en la tabla
        $datosTabla = array(                      
            HTML::parrafo( $textos->id("NOMBRE")                ,  "centrado" ) => "nombre|e.nombre",
            HTML::parrafo( $textos->id("PAIS")                  ,  "centrado" ) => "pais|p.nombre"
        );        
        //ruta a donde se mandara la accion del doble click
        $rutaPaginador = "/ajax".$this->urlBase."/move";
        
        return Recursos::generarTablaRegistros($arregloRegistros, $datosTabla, $rutaPaginador, $datosPaginacion).HTML::crearMenuBotonDerecho("ESTADOS");
        
    }    
    
    
    
}

