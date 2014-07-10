<?php

/**
 * @package     FOLCS
 * @subpackage  Bulletin Board item_bb = Clase encargada de las interacciones CRUD con la BD
 *              así como de generar la estructura de la tabla del listado general
 * @author      Pablo A. Vélez <pavelez@genesyscorporation.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
class BulletinBoardItem {

    /**
     * Código interno o identificador del item_bb en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de item_bb
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un item_bb específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador del item_bb en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;
    
    /**
     * Código interno o identificador de la subcategoria ala cual pertenece esta subcategoria
     * @var entero
     */
    public $idSubcategoria;
    
    /**
     * Objeto que representa al objeto categoria
     * @var entero
     */
    public $subcategoria;

    /**
     * Objeto usuario que representa al usuario creador del objeto
     * @var cadena
     */
    public $usuario;

    /**
     * Título del item_bb
     * @var cadena
     */
    public $titulo;

    /**
     * Resumen de la subcategoria_bb
     * @var cadena
     */
    public $resumen;     

    /**
     * Contenido completo del item_bb
     * @var cadena
     */
    public $descripcion;

    /**
     * Fecha de creación del item_bb
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de item_bb
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registrosActivos = NULL;

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
     * Lista de objetos que representan los archivos que puede contener el bulletin board item
     * @var lógico
     */
    public $listaArchivos;          

    /**
     * Inicializar el item_bb
     *
     * @param entero $id Código interno o identificador del item_bb en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('ITEMS_BB');

        $this->urlBase      = '/' . $modulo->url;
        $this->url          = $modulo->url;
        $this->idModulo     = $modulo->id;
        $this->ordenInicial = 'titulo';

        $this->registros        = $sql->obtenerValor('items_bb', 'COUNT(id)', '');
     
        $this->registrosActivos = $sql->obtenerValor('items_bb', 'COUNT(id)', 'activo = "1"');

        if (isset($id)) {
            $this->cargar($id);

        }

    }

    /**
     * Cargar los datos de un item_bb en un objeto de esta clase
     *
     * @param entero $id Código interno o identificador del item_bb en la base de datos
     */
    private function cargar($id) {
        global $sql, $configuracion;

        if (is_numeric($id) && $sql->existeItem('items_bb', 'id', intval($id))) {

            $tablas = array(
                'n'     => 'items_bb',
                'sc'    => 'subcategorias_bb',
                'i'     => 'imagenes'
            );

            $columnas = array(
                'id'                => 'n.id',
                'idAutor'           => 'n.id_usuario',
                'idSubcategoria'    => 'n.id_subcategoria',
                'titulo'            => 'n.titulo',
                'resumen'           => 'n.resumen',                
                'descripcion'       => 'n.descripcion',
                'fechaCreacion'     => 'UNIX_TIMESTAMP(n.fecha_creacion)',
                'activo'            => 'n.activo',

            );

            $condicion = 'n.id_subcategoria = sc.id AND n.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->usuario      = new Usuario($this->idAutor);
                
                $this->subcategoria = new SubCategoriaBB($this->idSubcategoria);

                $this->url = $this->urlBase . '/' . $this->id;

            }

        }

    }

    /**
     * Adicionar un item_bb
     *
     * @param  arreglo $datos       Datos del item_bb a adicionar
     * @return entero               Código interno o identificador del item_bb en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo;
    
        $datosItemBB = array(
                                'titulo'                => htmlspecialchars($datos['titulo']),
                                'resumen'               => htmlspecialchars($datos['resumen']),                                
                                'id_subcategoria'       => htmlspecialchars($datos['id_subcategoria']),
                                'descripcion'           => Variable::filtrarTagsInseguros($datos['descripcion']),
                                'id_usuario'            => $sesion_usuarioSesion->id,
                            );

        if (isset($datos['activo'])) {
            $datosItemBB['activo'] = '1';

        } else {
            $datosItemBB['activo'] = '0';

        }

        $consulta   = $sql->insertar('items_bb', $datosItemBB);

        $idItem     = $sql->ultimoId;

        if ($consulta) {
            return $idItem;

        } else {
            return false;

        }

    }

    /**
     * Modificar un item_bb
     *
     * @param  arreglo $datos       Datos del item_bb a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo;

        if (!isset($this->id)) {
            return NULL;
        }

        $datosItemBB = array(
                                'titulo'                => htmlspecialchars($datos['titulo']),
                                'resumen'               => htmlspecialchars($datos['resumen']),                                
                                'id_subcategoria'       => htmlspecialchars($datos['id_subcategoria']),
                                'descripcion'           => Variable::filtrarTagsInseguros($datos['descripcion']),
                                'id_usuario'            => $sesion_usuarioSesion->id,
                            );

        if (isset($datos['activo'])) {
            $datosItemBB['activo'] = '1';

        } else {
            $datosItemBB['activo'] = '0';

        }


        $consulta = $sql->modificar('items_bb', $datosItemBB, 'id = "' . $this->id . '"');

        if ($consulta) {
            return $consulta;

        } else {
            return false;

        }

    }

    /**
     * Eliminar un item_bb
     *
     * @param entero $id    Código interno o identificador del item_bb en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        
        $consulta = $sql->eliminar('items_bb', 'id = "' . $this->id . '"');

        if ($consulta) {
            return $consulta;

        } else {
            return false;

        }

    }

    /**
     * Listar las item_bb
     * @param entero  $cantidad    Número de item_bb a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de item_bb
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicionGlobal = NULL, $orden = NULL) {
        global $sql, $configuracion, $textos;

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
            $condicion = "n.id NOT IN ($excepcion) ";
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
            'n' => 'items_bb',
            'sc' => 'subcategorias_bb',
            'u'  => 'usuarios'
        );

        $columnas = array(
            'id'                => 'n.id',
            'titulo'            => 'n.titulo',
            'resumen'           => 'n.resumen',                
            'idSubcategoria'    => 'n.id_subcategoria',
            'fechaCreacion'     => 'UNIX_TIMESTAMP(n.fecha_creacion)',
            'activo'            => 'n.activo',
            'idAutor'           => 'u.id',
            'autor'             => 'u.usuario',
        );

        if (!empty($condicionGlobal)) {
            if($condicion != ''){
                $condicion .= ' AND ';
            }
            $condicion .= $condicionGlobal." AND ";
        }  

		$condicion .= ' n.id_subcategoria = sc.id AND n.id_usuario = u.id';

       
        if (is_null($this->registrosConsulta)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registrosConsulta = $sql->filasDevueltas;

        }
        
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($objeto = $sql->filaEnObjeto($consulta)) {
                $objeto->url = $this->urlBase."/".$objeto->id;

                if ($objeto->activo) {
                    $objeto->estado = HTML::frase($textos->id('ACTIVO'), 'activo');

                } else {
                    $objeto->estado = HTML::frase($textos->id('INACTIVO'), 'inactivo');

                }  

                $objeto->subcategoria = new SubCategoriaBB($objeto->idSubcategoria);

                $lista[]   = $objeto;
            }

        }

        return $lista;

    }

}
