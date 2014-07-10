<?php

/**
 * @package     FOLCS
 * @subpackage  SubCategorias Bulletin Board = Clase encargada de las interacciones CRUD con la BD
 *              así como de generar la estructura de la tabla del listado general
 * @author      Pablo A. Vélez <pavelez@genesyscorporation.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
class SubCategoriaBB {

    /**
     * Código interno o identificador de la subcategoria_bb en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de subCategorias_bb
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de una subcategoria_bb específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador de la subcategoria_bb en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;
    
    /**
     * Código interno o identificador de la categoria ala cual pertenece esta subcategoria
     * @var entero
     */
    public $idCategoria;   
    
    /**
     * Objeto que representa al objeto categoria
     * @var entero
     */
    public $categoria;      

    /**
     * Objeto usuario que representa al usuario creador del objeto
     * @var cadena
     */
    public $usuario;

    /**
     * Título de la subcategoria_bb
     * @var cadena
     */
    public $titulo;

    /**
     * Resumen de la subcategoria_bb
     * @var cadena
     */
    public $resumen;    

    /**
     * Contenido completo de la subcategoria_bb
     * @var cadena
     */
    public $descripcion;

    /**
     * Código interno o identificador en la base de datos de la imagen relacionada con la subcategoria_bb
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen de la subcategoria_bb en tamaño normal
     * @var cadena
     */
    public $imagenPrincipal;

    /**
     * Ruta de la imagen de la subcategoria_bb en miniatura
     * @var cadena
     */
    public $imagenMiniatura;

    /**
     * Fecha de creación de la subcategoria_bb
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de subCategorias_bb
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
     * Inicializar la subcategoria_bb
     *
     * @param entero $id Código interno o identificador de la categoria_bb en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('SUBCATEGORIAS_BB');

        $this->urlBase      = '/' . $modulo->url;
        $this->url          = $modulo->url;
        $this->idModulo     = $modulo->id;
        $this->ordenInicial = 'titulo';

        $this->registros        = $sql->obtenerValor('subcategorias_bb', 'COUNT(id)', '');
     
        $this->registrosActivos = $sql->obtenerValor('subcategorias_bb', 'COUNT(id)', 'activo = "1"');

        if (isset($id)) {
            $this->cargar($id);

        }

    }

    /**
     * Cargar los datos de una subcategoria_bb en un objeto de esta clase
     *
     * @param entero $id Código interno o identificador de la subcategoria_bb en la base de datos
     */
    private function cargar($id) {
        global $sql, $configuracion;

        if (is_numeric($id) && $sql->existeItem('subcategorias_bb', 'id', intval($id))) {

            $tablas = array(
                'n' => 'subcategorias_bb',
                'c' => 'categorias_bb',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id'                => 'n.id',
                'idAutor'           => 'n.id_usuario',
                'idCategoria'       => 'n.id_categoria',
                'idImagen'          => 'n.id_imagen',
                'imagen'            => 'i.ruta',
                'titulo'            => 'n.titulo',
                'resumen'           => 'n.resumen',
                'descripcion'       => 'n.descripcion',
                'fechaCreacion'     => 'UNIX_TIMESTAMP(n.fecha_creacion)',
                'activo'            => 'n.activo',
            );

            $condicion = 'n.id_imagen = i.id AND n.id_categoria = c.id AND n.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->usuario = new Usuario($this->idAutor);
                
                $this->categoria = new CategoriaBB($this->idCategoria);

                $this->fechaCreacion = date('D, d M Y', $this->fechaCreacion);

                $this->url = $this->urlBase . '/' . $this->id;
                $this->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $this->imagen;
                $this->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->imagen;

            }

        }

    }

    /**
     * Adicionar una subcategoria_bb
     *
     * @param  arreglo $datos       Datos de la subcategoria_bb a adicionar
     * @return entero               Código interno o identificador de la subcategoria_bb en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo, $archivo_imagen;

        //nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfilesPA      = $datos['perfiles_pa'];
        $datosVisibilidadPA   = $datos['visibilidad_pa'];             

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            $objetoImagen = new Imagen();

            $datosImagen = array(
                'idRegistro'    => '',
                'modulo'        => 'SUBCATEGORIAS_BB',
                'descripcion'   => 'Image of'.htmlspecialchars($datos['titulo']),
                'titulo'        => 'Image of'.htmlspecialchars($datos['titulo'])
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }
    
        $datosSubCategoriaBb = array(
                                'titulo'                => htmlspecialchars($datos['titulo']),
                                'resumen'               => htmlspecialchars($datos['resumen']),
                                'id_categoria'          => htmlspecialchars($datos['id_categoria']),
                                'descripcion'           => Variable::filtrarTagsInseguros($datos['descripcion']),
                                'id_usuario'            => $sesion_usuarioSesion->id,
                                'id_imagen'             => $idImagen,
                            );

        if (isset($datos['activo'])) {
            $datosSubCategoriaBb['activo'] = '1';

        } else {
            $datosSubCategoriaBb['activo'] = '0';

        }

        $consulta   = $sql->insertar('subcategorias_bb', $datosSubCategoriaBb);

        $idItem     = $sql->ultimoId;

        if ($consulta) {
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el item
            $permisosItem   = new PermisosItem();
            $idModulo       = $modulo->id;
            //insertar los permisos de adicion para los perfiles
            $permisosItem->insertarPerfilesCompartidosPA($datosVisibilidadPA, $idModulo, $idItem, $datosPerfilesPA);

            return $idItem;

        } else {
            return false;

        }

    }

    /**
     * Modificar una subcategoria_bb
     *
     * @param  arreglo $datos       Datos de la subcategoria_bb a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $modulo, $archivo_imagen, $sesion_usuarioSesion;

        //nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfilesPA      = $datos['perfiles_pa'];
        $datosVisibilidadPA   = $datos['visibilidad_pa'];          

        if (!isset($this->id)) {
            return NULL;
        }

        if (isset($datos['activo'])) {
            $datos['activo'] = '1';

        } else {
            $datos['activo'] = '0';

        }

        $idImagen = $this->idImagen;

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            if ($this->idImagen == '8') {
                $objetoImagen = new Imagen();

            } else {
                $objetoImagen = new Imagen($this->idImagen);
                $objetoImagen->eliminar();

            }

            $datosImagen = array(
                'idRegistro'    => '',
                'modulo'        => 'SUBCATEGORIAS_BB',
                'descripcion'   => 'Image of'.htmlspecialchars($datos['titulo']),
                'titulo'        => 'Image of'.htmlspecialchars($datos['titulo'])
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }

        $datosSubCategoriaBb = array(
                                'titulo'                => htmlspecialchars($datos['titulo']),
                                'id_categoria'          => htmlspecialchars($datos['id_categoria']),
                                'resumen'               => htmlspecialchars($datos['resumen']),
                                'descripcion'           => Variable::filtrarTagsInseguros($datos['descripcion']),
                                'id_usuario'            => $sesion_usuarioSesion->id,
                                'id_imagen'             => $idImagen,
                                'activo'                => $datos['activo']
                            );


        $consulta = $sql->modificar('subcategorias_bb', $datosSubCategoriaBb, 'id = "' . $this->id . '"');

        if ($consulta) {
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el item
            $permisosItem   = new PermisosItem();
            $idModulo       = $modulo->id;
            $idItem         = $this->id;
            //insertar los permisos de adicion para los perfiles
            $permisosItem->modificarPerfilesCompartidosPA($datosVisibilidadPA, $idModulo, $idItem, $datosPerfilesPA);            
            return $consulta;

        } else {
            return false;

        }

    }

    /**
     * Eliminar una subcategoria_bb
     *
     * @param entero $id    Código interno o identificador de la subcategoria_bb en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        
        $consulta = $sql->eliminar('subcategorias_bb', 'id = "' . $this->id . '"');

        if ($consulta) {
            return $consulta;

        } else {
            return false;

        }

    }

    /**
     * Listar las subCategorias_bb
     * @param entero  $cantidad    Número de subCategorias_bb a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de subCategorias_bb
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicionGlobal = NULL, $orden = NULL, $modulo = 'SUBCATEGORIAS_BB') {
        global $sql, $configuracion, $textos, $sesion_usuarioSesion;

        $modulo = new Modulo($modulo);

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
            $condicion = "n.id NOT IN ($excepcion) AND ";
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
            'n' => 'subcategorias_bb',
            'c' => 'categorias_bb',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id'                => 'n.id',
            'titulo'            => 'n.titulo',
            'resumen'           => 'n.resumen',
            'idCategoria'       => 'n.id_categoria',
            'categoria'         => 'c.titulo',
            'color'             => 'c.color',
            'descripcion'       => 'n.descripcion',
            'imagen'            => 'i.ruta',                
            'fechaCreacion'     => 'UNIX_TIMESTAMP(n.fecha_creacion)',
            'activo'            => 'n.activo',
        );

        if (!empty($condicionGlobal)) {
            if($condicion == ''){
                $condicion .= ' AND ';
            }
            $condicion .= $condicionGlobal." AND ";
        }  

		$condicion .= ' n.id_categoria = c.id AND n.id_imagen = i.id ';

       
        if (is_null($this->registrosConsulta)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registrosConsulta = $sql->filasDevueltas;

        }
        
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($objeto = $sql->filaEnObjeto($consulta)) {
                $objeto->url = $this->urlBase."/".$objeto->id;

                $objeto->fechaCreacion = date('D, d M Y', $objeto->fechaCreacion);

                if ($objeto->activo) {
                    $objeto->estado = HTML::frase($textos->id('ACTIVO'), 'activo');

                } else {
                    $objeto->estado = HTML::frase($textos->id('INACTIVO'), 'inactivo');

                }                 
                $lista[]   = $objeto;
            }

        }

        return $lista;

    }

    /**
     *
     * Generar Tabla método encargado de generar la grilla donde se desplegar{a la información
     * del listado de subCategoriasBB
     *
     * @param  array        $arregloRegistros       Datos del listado de categorias para que la tabla los muestre ordenadamente
     * @return string       cadena de texto que representa una tabla HTML con toda la información del listado de subCategoriasBB
     *
     */
    public function generarTabla($arregloRegistros, $datosPaginacion = NULL){
        global $textos;
        //Declaracion de las columnas que se van a mostrar en la tabla
        $datosTabla = array(                      
            HTML::parrafo( $textos->id("TITULO")                ,  "centrado" ) => "titulo|n.titulo",
            HTML::parrafo( $textos->id("CATEGORIA")             ,  "centrado" ) => "categoria|c.titulo",
            HTML::parrafo( $textos->id("FECHA_CREACION")        ,  "centrado" ) => "fechaCreacion|n.fecha_creacion",
            HTML::parrafo( $textos->id('ESTADO')                ,  'centrado' ) => 'estado'
        );        
        //ruta a donde se mandara la accion del doble click
        $rutaPaginador = "/ajax".$this->urlBase."/move";
        
        return Recursos::generarTablaRegistros($arregloRegistros, $datosTabla, $rutaPaginador, $datosPaginacion).HTML::crearMenuBotonDerecho("SUBCATEGORIAS_BB");
        
    }  

}
