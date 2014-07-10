<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Estados
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/

/**
 * Clase Estado: clase encargada de gestionar la informacion de los registros sobre los estados almacenados en el sistema.
 * es una clase que contiene en su mayoria metodos crud para la gestion con la bd, ademas de esto tiene algunos metodos
 * para el renderizado de informacion, como por ejemplo el metodo generar tabla. Esta clase mantiene una relacion directa
 * con las clases paises y ciudades, ya que una ciudad pertenece a un estado y un estado pertenece a un pais.
 */
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
     * URL relativa de una ciudad específica
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
     * Indicador del orden cronológio de la lista de ciudades
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
     * Eliminar una ciudad
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
     * Listar las ciudades
     *
     * @param entero  $cantidad    Número de ciudadesa incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de ciudades
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
	//$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($ciudad = $sql->filaEnObjeto($consulta)) {
                $ciudad->url = $this->urlBase."/".$ciudad->id;
                $ciudad->pais = HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["iconosBanderas"] . "/" . strtolower($ciudad->codigo) . ".png", "miniaturaBanderas margenDerechaTriple").$ciudad->pais;
                $lista[]   = $ciudad;
            }
        }

        return $lista;

    }
    
    
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
/**
 *
 * @package     FOLCS
 * @subpackage  Anuncio
 * @author      Pablo A. Vélez <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 *
 * */
/**
 * Clase Anuncio: clase encargada de gestionar la informacion de los registros sobre los anuncios almacenados en el sistema.
 * es una clase que contiene en su mayoria metodos crud para la gestion con la bd.
 * Nota: esta clase y el modulo de anuncios fueron removidos temporalmente de ablaOnline.
 */
class Anuncio {

    /**
     * Código interno o identificador del anuncio en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de anuncio
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un anuncio específico
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador de la categoria a la cual pertenece el anuncio
     * @var entero
     */
    public $idCategoria;

    /**
     * Título del anuncio
     * @var cadena
     */
    public $titulo;

    /**
     * Título del anuncio
     * @var cadena
     */
    public $descripcion;

    /**
     * Código interno o identificador en la base de datos de la imagen relacionada con el anuncio
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen del anuncio en tamaño normal
     * @var cadena
     */
    public $imagenPrincipal;

    /**
     * Fecha de creación del anuncio
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación inicial del anuncio
     * @var fecha
     */
    public $fechaInicial;

    /**
     * Fecha de publicación final del anuncio
     * @var fecha
     */
    public $fechaFinal;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de Anuncios
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Vinculo al que dirige el banner
     * @var entero
     */
    public $vinculo = NULL;

    /**
     * Inicializar el Anuncio
     * @param entero $id Código interno o identificador del anuncio en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('ANUNCIOS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;


        $consulta = $sql->filaEnObjeto($sql->seleccionar(array('anuncios'), array('registros' => 'COUNT(id)')));
        $this->registros = $consulta->registros;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos del anuncio
     *
     * @param entero $id Código interno o identificador del anuncio en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem('anuncios', 'id', intval($id))) {

            $tablas = array(
                'a' => 'anuncios',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id' => 'a.id',
                'idImagen' => 'a.id_imagen',
                'imagen' => 'i.ruta',
                'titulo' => 'a.titulo',
                'descripcion' => 'a.descripcion',
                'vinculo' => 'a.vinculo',
                'fechaCreacion' => 'UNIX_TIMESTAMP(a.fecha_creacion)',
                'fechaInicial' => 'UNIX_TIMESTAMP(a.fecha_inicial)',
                'fechaFinal' => 'UNIX_TIMESTAMP(a.fecha_final)',
                'activo' => 'a.activo'
            );

            $condicion = 'a.id_imagen = i.id AND a.id = "'.$id.'"';
            //$sql->depurar = true;
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->usuario;
                $this->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $this->imagen;
            }
        }
    }

    /**
     *
     * Adicionar un anuncio
     *
     * @param  arreglo $datos       Datos del anuncio a adicionar
     * @return entero               Código interno o identificador del anuncio en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $archivo_imagen;

        if (isset($datos['activo'])) {
            $datos['activo'] = '1';
            $datos['fecha_inicial'] = date('Y-m-d H:i:s');

            $activo = array('activo' => '0');
            // $sql->depurar = true;
            $consulta = $sql->modificar('anuncios', $activo, 'id IS NOT NULL');
        } else {
            $datos['activo'] = '0';
            $datos['fecha_publicacion'] = NULL;
        }

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            $objetoImagen = new Imagen();

            $datosImagen = array(
                'idRegistro' => '',
                'modulo' => 'ANUNCIOS',
                'descripcion' => 'Image of' . htmlspecialchars($datos['titulo']),
                'titulo' => 'Image of' . htmlspecialchars($datos['titulo'])
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }

        $datosAnuncio = array(
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => htmlspecialchars($datos['descripcion']),
            'vinculo' => htmlspecialchars($datos['vinculo']),
            'id_imagen' => $idImagen,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'fecha_inicial' => htmlspecialchars($datos['fecha_inicial']),
            'activo' => $datos['activo']
        );

        $consulta = $sql->insertar('anuncios', $datosAnuncio);

        if ($consulta) {
            return $sql->ultimoId;
        } else {
            return FALSE;
        }
    }


    /**
     * Modificar un anuncio
     * @param  arreglo $datos       Datos del anuncio  a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
        }

        if (isset($datos['activo'])) {
            $datos['activo'] = '1';
            $datos['fecha_inicial'] = date('Y-m-d H:i:s');

            $fechaFinal = array('fecha_final' => date('Y-m-d H:i:s'));
            $sql->modificar('anuncios', $fechaFinal, 'activo = "1"');

            $activo = array('activo' => '0');
            $sql->modificar('anuncios', $activo, 'id IS NOT NULL');
        } else {
            $datos['activo'] = '0';
            $datos['fecha_inicial'] = NULL;
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
                'idRegistro' => $this->id,
                'modulo' => 'ANUNCIOS',
                'titulo' => 'Image of ' . htmlspecialchars($datos['titulo']),
                'descripcion' => 'Image of ' . htmlspecialchars($datos['titulo'])
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }

        $datos = array(
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => htmlspecialchars($datos['descripcion']),
            'vinculo' => htmlspecialchars($datos['vinculo']),
            'fecha_inicial' => htmlspecialchars($datos['fecha_inicial']),
            'id_imagen' => $idImagen,
            'activo' => $datos['activo']
        );


        $consulta = $sql->modificar('anuncios', $datos, 'id = "' . $this->id . '"');

        if ($consulta) {
            return TRUE;
        } else {
            return FALSE;
        }
    }


    /**
     * Eliminar un anuncio
     * @param entero $id    Código interno o identificador del anuncio  en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('anuncios', 'id = "' . $this->id . '"');

        if (!($consulta)) {
            return false;
        } else {
            $objetoImagen = new Imagen($this->idImagen);
            $objetoImagen->eliminar();
            return true;
        }
    }

    /**
     * Listar los anuncios
     * @param entero  $cantidad    Número de anuncios a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de anuncios
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'a.id NOT IN ('.$excepcion.')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'a.fecha_creacion ASC';
        } else {
            $orden = 'a.fecha_creacion ASC';
        }


        $tablas = array(
            'a' => 'anuncios',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'a.id',
            'idImagen' => 'a.id_imagen',
            'imagen' => 'i.ruta',
            'titulo' => 'a.titulo',
            'descripcion' => 'a.descripcion',
            'vinculo' => 'a.vinculo',
            'fechaCreacion' => 'UNIX_TIMESTAMP(a.fecha_creacion)',
            'fechaInicial' => 'UNIX_TIMESTAMP(a.fecha_inicial)',
            'fechaFinal' => 'UNIX_TIMESTAMP(a.fecha_final)',
            'activo' => 'a.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }
        $condicion .= 'a.id_imagen = i.id';


        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($anuncio = $sql->filaEnObjeto($consulta)) {
                $anuncio->url = $this->urlBase . '/' . $anuncio->id;
                $anuncio->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $anuncio->imagen;

                $lista[] = $anuncio;
            }
        }

        return $lista;
    }


    /**
     * Metodo que retorna un div con el anuncio que esta establecido como activo
     * */
    public static function mostrarAnuncio() {
        global $sql, $configuracion;

        $codigo = '';
        $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/';
        $rutaAlterna = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/00000001.gif';

        $tablas = array(
            'a' => 'anuncios',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'a.id',
            'idImg' => 'a.id_imagen',
            'vinculo' => 'a.vinculo',
            'idImagen' => 'i.id',
            'ruta' => 'i.ruta',
            'activo' => 'a.activo'
        );

        $condicion = 'a.id_imagen = i.id AND a.activo = "1"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
        $anuncio = $sql->filaEnObjeto($consulta);

        if ($sql->filasDevueltas) {
            $imagen = HTML::imagen($ruta . $anuncio->ruta, 'claseBanner');
            $vinculo = $anuncio->vinculo;
        } else {
            $imagen = HTML::imagen($rutaAlterna, 'claseBanner');
            $vinculo = 'http://www.colomboamericano.edu.co';
        }

        $enlace = HTML::enlace($imagen, $vinculo);
        $codigo .= HTML::contenedor($enlace, 'claseAnuncio');
        
        return $codigo;
    }

}

?>
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Asociado
 * @author      Pablo A. Vélez <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 *
 * */
/**
 * Clase Asociado: clase encargada de gestionar la informacion de los registros sobre los asociados almacenados en el sistema.
 * los asociados son los logos de las empresas que aparecen en la parte superior del footer en la pagina principal de ablaonline.
 * es una clase que contiene en su mayoria metodos crud para la gestion con la bd.
 */
class Asociado {

    /**
     * Código interno o identificador del Asociado en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de Asociado
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un Asociado específico
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador de la categoria a la cual pertenece el Asociado
     * @var entero
     */
    public $idCategoria;

    /**
     * Título del Asociado
     * @var cadena
     */
    public $titulo;

    /**
     * Título del Asociado
     * @var cadena
     */
    public $descripcion;

    /**
     * Código interno o identificador en la base de datos de la imagen relacionada con el Asociado
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen del Asociado en tamaño normal
     * @var cadena
     */
    public $imagenPrincipal;

    /**
     * Fecha de creación del Asociado
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación inicial del Asociado
     * @var fecha
     */
    public $fechaInicial;

    /**
     * Fecha de publicación final del Asociado
     * @var fecha
     */
    public $fechaFinal;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de Asociado
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Vinculo al que dirige el banner
     * @var entero
     */
    public $vinculo = NULL;

    /**
     *
     * Inicializar el Asociado
     *
     * @param entero $id Código interno o identificador del Asociado en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo("ASOCIADOS");
        $this->urlBase   = "/" . $modulo->url;
        $this->url       = $modulo->url;
        $this->idModulo  = $modulo->id;

        $this->registros = $sql->obtenerValor("asociados", "COUNT(id)", "activo = '1'");

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos del Asociado
     *
     * @param entero $id Código interno o identificador del Asociado en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("asociados", "id", intval($id))) {

            $tablas = array(
                "a" => "asociados",
                "i" => "imagenes"
            );

            $columnas = array(
                "id"            => "a.id",
                "idImagen"      => "a.id_imagen",
                "imagen"        => "i.ruta",
                "titulo"        => "a.titulo",
                "descripcion"   => "a.descripcion",
                "vinculo"       => "a.vinculo",
                "fechaCreacion" => "UNIX_TIMESTAMP(a.fecha_creacion)",
                "fechaInicial"  => "UNIX_TIMESTAMP(a.fecha_inicial)",
                "fechaFinal"    => "UNIX_TIMESTAMP(a.fecha_final)",
                "activo"        => "a.activo"
            );

            $condicion = "a.id_imagen = i.id AND a.id = '$id'";
            //$sql->depurar = true;
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . "/" . $this->usuario;
                $this->registros = $sql->obtenerValor("asociados", "COUNT(id)", "activo = '1'");
                $this->imagenPrincipal = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesNormales"] . "/" . $this->imagen;
            }
        }
    }

    /**
     *
     * Adicionar un asociado
     *
     * @param  arreglo $datos       Datos del asociado a adicionar
     * @return entero               Código interno o identificador del asociado en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql,  $archivo_imagen;

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";
            $datos["fecha_inicial"] = date("Y-m-d H:i:s");
        } else {
            $datos["activo"] = "0";
            $datos["fecha_publicacion"] = NULL;
        }

        $idImagen = '8';
        
        if(isset($archivo_imagen) && !empty($archivo_imagen["tmp_name"])){
            
            $imagen = new Imagen();
            $datosImagen = array(
                "modulo"      => "ASOCIADOS",
                "idRegistro"  => "",
                "titulo"      => "imagen_asociado",
                "descripcion" => "imagen_asociado"
            );
            
            $idImagen = $imagen->adicionar($datosImagen);            
        }

        $datosAsociado = array(
            "titulo"        => $datos["titulo"],
            "descripcion"   => $datos["descripcion"],
            "vinculo"       => $datos["vinculo"],
            "id_imagen"     => $idImagen,
            "fecha_creacion"=> date("Y-m-d H:i:s"),
            "fecha_inicial" => $datos['fecha_inicial'],
            "activo"        => $datos['activo']
        );

        $consulta = $sql->insertar("asociados", $datosAsociado);

        if ($consulta) {
            return $sql->ultimoId;
            
        } else {
            return FALSE;
        }
    }


    /**
     *
     * Modificar un asociado
     *
     * @param  arreglo $datos       Datos del asociado  a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
        }

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";
            $datos["fecha_inicial"] = date("Y-m-d H:i:s");
        } else {
            $datos["activo"] = "0";
            $datos["fecha_inicial"] = NULL;
        }

        if (isset($archivo_imagen) && !empty($archivo_imagen["tmp_name"])) {
            $imagen = new Imagen($this->idImagen);
            $imagen->eliminar();
            
            $datosImagen = array(
                "idRegistro" => "",
                "modulo"     => "35"
            );
            $idImagen = $imagen->adicionar($datosImagen);
            
        } else {
            $idImagen = $this->idImagen;
        }

        $datos = array(
            "titulo"        => $datos["titulo"],
            "descripcion"   => $datos["descripcion"],
            "vinculo"       => $datos["vinculo"],
            "fecha_inicial" => $datos['fecha_inicial'],
            "id_imagen"     => $idImagen,
            "activo"        => $datos['activo']
        );

        //$sql->depurar = true;
        $consulta = $sql->modificar("asociados", $datos, "id = '" . $this->id . "'");

        if ($consulta) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * Eliminar un asociado
     *
     * @param entero $id    Código interno o identificador del asociado  en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        
        $consulta = $sql->eliminar("asociados", "id = '" . $this->id . "'");

        if (!($consulta)) {            
            return false;
        } else {
            $imagen = new Imagen($this->idImagen);
            $imagen->eliminar();
            return true;
        }
    }

    /**
     *
     * Listar los asociados
     *
     * @param entero  $cantidad    Número de asociados a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de asociados
     *
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "a.id NOT IN ($excepcion)";
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = "a.fecha_creacion ASC";
        } else {
            $orden = "a.fecha_creacion ASC";
        }


        $tablas = array(
            "a" => "asociados",
            "i" => "imagenes"
        );

        $columnas = array(
            "id"            => "a.id",
            "idImagen"      => "a.id_imagen",
            "imagen"        => "i.ruta",
            "titulo"        => "a.titulo",
            "descripcion"   => "a.descripcion",
            "vinculo"       => "a.vinculo",
            "fechaCreacion" => "UNIX_TIMESTAMP(a.fecha_creacion)",
            "fechaInicial"  => "UNIX_TIMESTAMP(a.fecha_inicial)",
            "fechaFinal"    => "UNIX_TIMESTAMP(a.fecha_final)",
            "activo"        => "a.activo"
        );

        if (!empty($condicion)) {
            $condicion .= " AND ";
        }
        $condicion .= "a.id_imagen = i.id";

        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ( $asociado = $sql->filaEnObjeto($consulta) ) {
                $asociado->url = $this->urlBase . "/" . $asociado->id;
                $asociado->imagenPrincipal = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesDinamicas"] . "/" . $asociado->imagen;

                $lista[] = $asociado;
            }
        }

        return $lista;
    }

//fin del metodo listar
}

?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Bitacora
 * @author      Pablo A. Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
/**
 * Clase Bitacora: clase encargada de gestionar la informacion de los registros de actividades o logs sobre el sistema.
 * la informacion que gestiona esta clase es almacenada en la bd desde la clase sql, cada vez que se generan actividades
 * de una minima importancia sobre el sistema, como por ejemplo eliminar o modificar un registro. (ver clases/SQL.php).
 */
class Bitacora {

    /**
     * Código interno o identificador del registro en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * URL relativa del módulo de registros
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un registro específica
     * @var cadena
     */
    public $url;

    /**
     * Número de registros activos de la lista de foros
     * @var entero
     */
    public $registrosConsulta = NULL;

    /**
     * Orden predeterminado para organizar los listados
     * @var entero
     */
    public $ordenInicial = 'fecha';

    /**
     * Inicializar el objeto
     * @param entero $id Código interno o identificador del objeto en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('BITACORA');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        $consulta = $sql->obtenerValor('registro', 'COUNT(id)', '');
        $this->registros = $consulta;


        if (isset($id)) {
            $this->cargar($id);
        }
    }



    /**
     * Listar las registros
     * @param entero  $cantidad    Número de ciudadesa incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de ciudades
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicionGlobal = NULL, $orden = NULL) {
        global $sql;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicionGlobal)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion = 'b.id NOT IN (' . $excepcion . ') ';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if (!isset($orden)) {
            $orden = $this->ordenInicial;
        }
        if ($this->listaAscendente) {
            $orden = $orden . ' ASC';
        } else {
            $orden = $orden . ' DESC';
        }


        $tablas = array(
            'b' => 'bitacora'
        );

        $columnas = array(
            'id' => 'b.id',
            'usuario' => 'b.usuario',
            'ip' => 'b.ip',
            'consulta' => 'b.consulta',
            'fecha' => 'b.fecha'
        );

        if (!empty($condicionGlobal)) {
            if ($condicion != '') {
                $condicion .= ' AND ';
            }
            $condicion .= $condicionGlobal;
        }

        if (is_null($this->registrosConsulta)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registrosConsulta = $sql->filasDevueltas;
        }
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'b.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($bitacora = $sql->filaEnObjeto($consulta)) {
                $lista[] = $bitacora;
            }
        }

        return $lista;
    }

//fin del metodo listar

    /**
     * MEtodo que se encargar de generar la tabla que lista los usuarios
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @param type $arregloRegistros
     * @param type $datosPaginacion
     * @return type 
     */
    public function generarTabla($arregloRegistros, $datosPaginacion = NULL) {
        global $textos;
        //Declaracion de las columnas que se van a mostrar en la tabla
        $datosTabla = array(
            HTML::contenedor($textos->id('USUARIO'), 'columnaCabeceraTabla') => 'usuario|b.usuario',
            HTML::contenedor($textos->id('IP'), 'columnaCabeceraTabla') => 'ip|b.ip',
            HTML::contenedor($textos->id('CONSULTA'), 'columnaCabeceraTabla') => 'consulta|b.consulta',
            HTML::contenedor($textos->id('FECHA'), 'columnaCabeceraTabla') => 'fecha|b.fecha'
        );

        //ruta a donde se mandara la accion del doble click
        $rutaPaginador = '/ajax' . $this->urlBase . '/move';

        return Recursos::generarTablaRegistros($arregloRegistros, $datosTabla, $rutaPaginador, $datosPaginacion) . HTML::crearMenuBotonDerecho('BITACORA');
    }

}

?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Blogs
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * */
/**
 * Clase Blog: clase encargada de gestionar la informacion de los registros sobre los blogs almacenados en el sistema.
 * es una clase que contiene en su mayoria metodos crud para la gestion con la bd, pero tambien tiene metodos mas especializados
 * para agilizar ciertas busquedas, como por ejemplo los metodos para listar los blogs que le gustan a un usuario determinado.
 */
class Blog {

    /**
     * Código interno o identificador del blog en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de blogs
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un blog específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador del blog en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador de la categoria a la cual pertenece la noticia 
     * @var entero
     */
    public $idCategoria;

    /**
     * Nombre de usuario (login) del usuario creador del blog en la base de datos
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del blog
     * @var cadena
     */
    public $autor;

    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;

    /**
     * Título del blog
     * @var cadena
     */
    public $titulo;

    /**
     * Contenido completo del blog
     * @var cadena
     */
    public $contenido;

    /**
     * Palabras claves del blog para las búsquedas
     * @var cadena
     */
    public $palabrasClaves;

    /**
     * Calificación obtenida por el blog
     * @var entero
     */
    public $calificacion;

    /**
     * Fecha de creación del blog
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación del blog
     * @var fecha
     */
    public $fechaPublicacion;

    /**
     * Fecha de la última modificación del blog
     * @var fecha
     */
    public $fechaActualizacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de blogs
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Número de comentarios que tiene este item 
     * @var entero
     */
    public $cantidadComentarios = NULL;

    /**
     * Número de me gusta que tiene este item 
     * @var entero
     */
    public $cantidadMeGusta = NULL;

    /**
     * Número de me galerias fotograficas que tiene este item
     * @var entero
     */
    public $cantidadGalerias = NULL;

    /**
     * Número de registros activos de la lista de foros
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * Codigos de los perfiles de usuario con los cuales es compartido un Blog
     * @var array
     */
    public $permisosPerfiles = array();

    /**
     * Inicializar el blog
     * @param entero $id Código interno o identificador del blog en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('BLOGS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;
        //Saber el numero de registros
        $this->registros =  $sql->obtenerValor('blogs', 'count(id)', 'id != "0"');
        
        $this->registrosActivos = $sql->obtenerValor('blogs', 'count(id)', 'activo = "1"');

        if (isset($id)) {
            $this->cargar($id);
            $this->permisosPerfiles = PermisosItem::cargarPerfiles($id, $modulo->id);
            //Saber la cantidad de comentarios que tiene este blog
            $this->cantidadComentarios = $sql->obtenerValor('comentarios', 'COUNT(id)', 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
           
            //Saber la cantidad de me Gusta que tiene este blog
            $this->cantidadMeGusta = $sql->obtenerValor('destacados', 'COUNT(*)', 'id_modulo = "' . $this->idModulo . '" AND id_item = "' . $this->id . '"');
          
            //Saber la cantidad de galerias que tiene este blog
            $this->cantidadGalerias = $sql->obtenerValor('galerias', 'COUNT(id)', 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
            
        }
    }


    /**
     * Cargar los datos de un blog
     * @param entero $id Código interno o identificador del blog en la base de datos
     */
    private function cargar($id) {
        global $sql, $configuracion;

        if (is_numeric($id) && $sql->existeItem('blogs', 'id', intval($id))) {

            $tablas = array(
                'b' => 'blogs',
                'u' => 'usuarios',
                'p' => 'personas',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id' => 'b.id',
                'idAutor' => 'b.id_usuario',
                'usuarioAutor' => 'u.usuario',
                'autor' => 'u.sobrenombre',
                'fotoAutor' => 'i.ruta',
                'titulo' => 'b.titulo',
                'contenido' => 'b.contenido',
                'idCategoria' => 'b.id_categoria',
                'calificacion' => 'b.calificacion',
                'palabrasClaves' => 'b.palabras_claves',
                'fechaCreacion' => 'UNIX_TIMESTAMP(b.fecha_creacion)',
                'fechaPublicacion' => 'UNIX_TIMESTAMP(b.fecha_publicacion)',
                'fechaActualizacion' => 'UNIX_TIMESTAMP(b.fecha_actualizacion)',
                'activo' => 'b.activo'
            );

            $condicion = 'b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND b.id = "' . $id . '" ';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->id;
                $this->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->fotoAutor;
                //sumar una visita al blog
                $this->sumarVisita();
            }
        }
    }


    /**
     * Adicionar un blog
     * @param  arreglo $datos       Datos del blog a adicionar
     * @return entero               Código interno o identificador del blog en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo;

        $datosBlog = array();

        $datosBlog['titulo'] = htmlspecialchars($datos['titulo']);
        $datosBlog['contenido'] = $datos['contenido'];
        $datosBlog['palabras_claves'] = htmlspecialchars($datos['palabrasClaves']);
        $datosBlog['id_categoria'] = htmlspecialchars($datos['categorias']);
        $datosBlog['id_usuario'] = $sesion_usuarioSesion->id;
        $datosBlog['fecha_creacion'] = date('Y-m-d H:i:s');
        $datosBlog['fecha_actualizacion'] = date('Y-m-d H:i:s');

        //nuevos datos que recibo sobre los perfiles con los que comparto y la visibilidad
        $datosPerfiles = $datos['perfiles'];
        $datosVisibilidad = $datos['visibilidad'];

        if (isset($datos['activo'])) {
            $datosBlog['activo'] = '1';
            $datosBlog['fecha_publicacion'] = date('Y-m-d H:i:s');
        } else {
            $datosBlog['activo'] = '0';
            $datosBlog['fecha_publicacion'] = NULL;
        }

        $consulta = $sql->insertar('blogs', $datosBlog);
        $idItem = $sql->ultimoId;
        if ($consulta) {

            if ($datos['cantCampoImagenGaleria']) {//si viene alguna imagen se crea la galeria             
                $galeria = new Galeria();
                $datos['id_modulo_actual'] = $this->idModulo;
                $datos['id_registro_actual'] = $idItem;
                $galeria->adicionar($datos);
            }

            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;
            $permisosItem->insertarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);
            return $idItem;
        } else {
            return NULL;
        }//fin del if($consulta)
    }


    /**
     * Modificar un blog
     * @param  arreglo $datos       Datos de la blog a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $modulo;

        if (!isset($this->id)) {
            return NULL;
        }

        $datosBlog = array();

        $datosBlog['titulo'] = htmlspecialchars($datos['titulo']);
        $datosBlog['contenido'] = $datos['contenido'];
        $datosBlog['palabras_claves'] = htmlspecialchars($datos['palabrasClaves']);
        $datosBlog['id_categoria'] = htmlspecialchars($datos['categorias']);
        $datosBlog['fecha_actualizacion'] = date('Y-m-d H:i:s');

        //nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfiles = $datos['perfiles'];
        $datosVisibilidad = $datos['visibilidad'];

        if (isset($datos['activo'])) {
            $datosBlog['activo'] = '1';
            $datosBlog['fecha_publicacion'] = date('Y-m-d H:i:s');
        } else {
            $datosBlog['activo'] = '0';
            $datosBlog['fecha_publicacion'] = NULL;
        }

        $consulta = $sql->modificar('blogs', $datosBlog, 'id = "' . $this->id . '" ');

        if ($consulta) {
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;
            $idItem = $this->id;

            $permisosItem->modificarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);

            return 1;
        } else {

            return NULL;
        }//fin del if(consulta)
    }


    /**
     * Eliminar un blog
     * @param entero $id    Código interno o identificador del blog en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        if (!($consulta = $sql->eliminar('blogs', 'id = "' . $this->id . '" '))) {
            return false;
        } else {
            /* Eliminar todos los comentarios que pueda tener el Blog */
            if ($this->cantidadComentarios > 0) {
                $comentario = new Comentario();
                $comentario->eliminarComentarios($this->id, $this->idModulo);
            }
            /* Eliminar todos los "me gusta" que pueda tener el Blog */
            if ($this->cantidadMeGusta > 0) {
                $destacado = new Destacado();
                $destacado->eliminarTodosDestacados($this->idModulo, $this->id);
            }
            /* Eliminar todas las galerias que pueda tener el Blog */
            if ($this->cantidadGalerias > 0) {
                $galeria = new Galeria();
                $galeria->eliminarGalerias($this->idModulo, $this->id);
            }

            $permisosItem = new PermisosItem();

            if (!($permisosItem->eliminar($this->id, $this->idModulo))) {
                return false;
            } else {
                return true;
            }

            return true;
        }//fin del si funciono eliminar
    }


    /**
     * Listar los blogs filtrando el perfil con el cual es compartido, es decir
     * que blogs puede ver un usuario segun su perfil
     * @param entero  $cantidad    Número de blogs a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de blogs
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfil = NULL, $idModulo = NULL, $filtroCategoria = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'b.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'b.fecha_publicacion ASC';
        } else {
            $orden = 'b.fecha_publicacion DESC';
        }

        $tablas = array(
            'b' => 'blogs',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'b.id',
            'idAutor' => 'b.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'titulo' => 'b.titulo',
            'contenido' => 'b.contenido',
            'calificacion' => 'b.calificacion',
            'palabrasClaves' => 'b.palabras_claves',
            'fechaCreacion' => 'UNIX_TIMESTAMP(b.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(b.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(b.fecha_actualizacion)',
            'activo' => 'b.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= ' b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id';
        //filtro de categoria
        if (!empty($filtroCategoria)) {

            if ($filtroCategoria == 'my_item') {
                $filtroCategoria = htmlspecialchars($filtroCategoria);
                $condicion .= ' AND b.id_usuario = "' . $sesion_usuarioSesion->id . '" '; //filtro de categoria
            } else {
                $condicion .= ' AND id_categoria = "' . $filtroCategoria . '" '; //filtro de categoria
            }
        }

        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {
                //codigo para perfiles que pueden ver items de varios perfiles
                $condicion2 = '';
                $tienePrivilegios = $sql->obtenerValor('tipos_usuario', 'otros_perfiles', 'id = "' . $sesion_usuarioSesion->idTipo . '" ');
                if (isset($sesion_usuarioSesion) && $tienePrivilegios) {
                    $otrosPerfiles = implode(',', Perfil::verOtrosPerfiles());
                    $condicion2 = ', ' . $otrosPerfiles . ' '; //condicion extra para ver items de los otros perfiles sobre los cuales tiene privilegios                  
                }

                $tablas['pi'] = 'permisos_item';
                $columnas['idItem'] = 'pi.id_item';
                $columnas['idPerfil'] = 'pi.id_perfil';
                $columnas['idModulo'] = 'pi.id_modulo';

                $condicion .= ' AND pi.id_item = b.id AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" , "' . $idPerfil . ' ' . $condicion2 . '") ';
                $condicion .= ' OR (b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND b.id_usuario = "' . $sesion_usuarioSesion->id . '" AND pi.id_item = b.id AND pi.id_modulo = "' . $idModulo . '" ';
                if (!empty($filtroCategoria)) {
                    if ($filtroCategoria == 'my_item') {
                        $condicion .= ' AND b.id_usuario = "' . $sesion_usuarioSesion->id . '" '; //filtro de categoria
                    } else {
                        $condicion .= ' AND id_categoria = "' . $filtroCategoria . '" '; //filtro de categoria
                    }
                }
                $condicion .= ')';
            }
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= ' AND pi.id_item = b.id AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" )';
        }

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'b.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($blog = $sql->filaEnObjeto($consulta)) {
                $blog->url = $this->urlBase . '/' . $blog->id;
                $blog->idModulo = $this->idModulo;
                $blog->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $blog->fotoAutor;
                $lista[] = $blog;
            }
        }

        return $lista;
    }


    /**
     * Listar los blogs filtrando el perfil con el cual es compartido, es decir
     * que blogs puede ver un usuario segun su perfil
     * @param entero  $cantidad    Número de blogs a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de blogs
     */
    public function listarMasBlogs($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfil = NULL, $idModulo = NULL, $filtroCategoria = NULL, $idUsuarioPropietario = NULL, $idBlog = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        $lista = array();

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'b.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'b.fecha_publicacion ASC';
        } else {
            $orden = 'b.fecha_publicacion DESC';
        }

        $tablas = array(
            'b' => 'blogs',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'b.id',
            'idAutor' => 'b.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'titulo' => 'b.titulo',
            'contenido' => 'b.contenido',
            'calificacion' => 'b.calificacion',
            'palabrasClaves' => 'b.palabras_claves',
            'fechaCreacion' => 'UNIX_TIMESTAMP(b.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(b.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(b.fecha_actualizacion)',
            'activo' => 'b.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'b.id_usuario = "' . $idUsuarioPropietario . '" AND b.id != "' . $idBlog . '" AND b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id';
        //filtro de categoria
        if (!empty($filtroCategoria)) {
            $filtroCategoria = htmlspecialchars($filtroCategoria);
            if ($filtroCategoria == 'my_item') {
                $condicion .= ' AND b.id_usuario = "' . $sesion_usuarioSesion->id . '"'; //filtro de categoria
            } else {
                $condicion .= ' AND id_categoria = "' . $filtroCategoria . '" '; //filtro de categoria
            }
        }
        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {

                $tablas['pi'] = 'permisos_item';
                $columnas['idItem'] = 'pi.id_item';
                $columnas['idPerfil'] = 'pi.id_perfil';
                $columnas['idModulo'] = 'pi.id_modulo';

                $condicion .= ' AND pi.id_item = b.id AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" , "' . $idPerfil . '")';
                $condicion .= 'OR (b.id_usuario = "' . $idUsuarioPropietario . '" AND b.id != "' . $idBlog . '" AND b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND b.id_usuario = "' . $sesion_usuarioSesion->id . '" AND pi.id_item = b.id AND pi.id_modulo = "' . $idModulo . '"';
                if (!empty($filtroCategoria)) {
                    if ($filtroCategoria == 'my_item') {
                        $condicion .= ' AND b.id_usuario = "' . $sesion_usuarioSesion->id . '" '; //filtro de categoria
                    } else {
                        $condicion .= ' AND id_categoria = "' . $filtroCategoria . '" '; //filtro de categoria
                    }
                }
                $condicion .= ')';
            }
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= ' AND pi.id_item = b.id AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" )';
        }
       
        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
       
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'b.id', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            while ($blog = $sql->filaEnObjeto($consulta)) {
                $blog->url = $this->urlBase . '/' . $blog->id;
                $blog->idModulo = $this->idModulo;
                $blog->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $blog->fotoAutor;
                $lista[] = $blog;
            }
        }

        return $lista;
    }


    /**
     * Listar los blogs que le gustan al usuario que ha iniciado sesion
     * @param entero  $cantidad    Número de blogs a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de blogs
     */
    public function listarMeGusta($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'b.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'b.fecha_publicacion ASC';
        } else {
            $orden = 'b.fecha_publicacion DESC';
        }

        $tablas = array(
            'b' => 'blogs',
            'd' => 'destacados',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'b.id',
            'idAutor' => 'b.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'titulo' => 'b.titulo',
            'contenido' => 'b.contenido',
            'calificacion' => 'b.calificacion',
            'palabrasClaves' => 'b.palabras_claves',
            'fechaCreacion' => 'UNIX_TIMESTAMP(b.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(b.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(b.fecha_actualizacion)',
            'activo' => 'b.activo',
            'blog' => 'd.id_item',
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= ' b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND b.id= d.id_item AND d.id_modulo = "' . $this->idModulo . '" AND d.id_usuario = "' . $sesion_usuarioSesion->id . '" ';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'b.id', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($blog = $sql->filaEnObjeto($consulta)) {
                $blog->url = $this->urlBase . '/' . $blog->id;
                $blog->idModulo = $this->idModulo;
                $blog->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $blog->fotoAutor;
                $lista[] = $blog;
            }
        }

        return $lista;
    }


    /**
     * Metodo que muestra y lista los Blogs de el ususario que ha iniciado sesion
     * */
    public function misBlogs() {
        global $configuracion, $textos, $sesion_usuarioSesion;

        $bloqueBlogs = '';
        $arregloBlogs = self::listar(0, 5, '', 'b.id_usuario = "' . $sesion_usuarioSesion->id . '"', $sesion_usuarioSesion->idTipo, 20, '');

        if (sizeof($arregloBlogs) > 0) {
            foreach ($arregloBlogs as $elemento) {
                $item = '';

                if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->id == $elemento->idAutor)) {
                    $botones = '';
                    $botones .= HTML::botonEliminarItemAjax($elemento->id, $this->urlBase);
                    $botones .= HTML::botonModificarItemAjax($elemento->id, $this->urlBase);
                    $item .= HTML::contenedor($botones, 'botonesLista', 'botonesLista');
                }

                if ($elemento->activo) {
                    $comentario = new Comentario();

                    $contenedorComentarios = $comentario->mostrarComentarios($elemento->idModulo, $elemento->id);
                    $contenedorMeGusta = Recursos::mostrarContadorMeGusta($elemento->idModulo, $elemento->id);
                    $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, 'mostrarPosted');
                    //seleccionar el genero de una persona 
                    $persona = new Persona($elemento->idAutor);

                    $item .= HTML::enlace(HTML::imagen($elemento->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $elemento->url);
                    $item .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $persona->idGenero . '.png') . str_replace('%1', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)) . $comentarios, $textos->id('PUBLICADO_POR')));

                    $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                    $item2 .= HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion), 'pequenia cursiva negrilla');
                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris
                    $item = HTML::contenedor($item, 'contenedorListaBlogs', 'contenedorListaBlogs' . $elemento->id);
                    $listaBlogs[] = $item;
                }//fin del  SI Blog es activo
            }//fin del foreach

            $listaBlogs[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('BLOGS', '', '', '', 'my_item'), 'flotanteCentro margenSuperior');
        } else {
            $listaBlogs[] = $textos->id('NO_TIENES_BLOGS');
        }
        $bloqueBlogs .= HTML::lista($listaBlogs, 'listaVertical bordeSuperiorLista', 'botonesOcultos', '');

        return $bloqueBlogs;
    }


    /**
     * Metodo que muestra y lista los Blogs de el ususario que ha iniciado sesion a los cuales ha dado click en meGusta
     * */
    public function blogsQueMeGustan() {
        global $configuracion, $textos, $sesion_usuarioSesion;

        $bloqueBlogs = '';
        $arregloBlogs = self::listarMeGusta(0, 0, '', '');

        if (sizeof($arregloBlogs) > 0) {
            foreach ($arregloBlogs as $elemento) {
                $item = '';

                if ($elemento->activo) {

                    if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->id == $elemento->idAutor)) {
                        $botones = '';
                        $botones .= HTML::botonEliminarItemAjax($elemento->id, $this->urlBase);
                        $botones .= HTML::botonModificarItemAjax($elemento->id, $this->urlBase);
                        $item .= HTML::contenedor($botones, 'botonesLista', 'botonesLista');
                    }

                    $comentario = new Comentario();

                    $contenedorComentarios = $comentario->mostrarComentarios($elemento->idModulo, $elemento->id);
                    $contenedorMeGusta = Recursos::mostrarContadorMeGusta($elemento->idModulo, $elemento->id);
                    $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, 'mostrarPosted');
                    //seleccionar el genero de una persona 
                    $persona = new Persona($elemento->idAutor);
                    $item .= HTML::enlace(HTML::imagen($elemento->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $elemento->url);
                    $item .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $persona->idGenero . '.png') . str_replace('%1', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)) . $comentarios, $textos->id('PUBLICADO_POR')));

                    $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                    $item2 .= HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion), 'pequenia cursiva negrilla');
                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris
                    $item = HTML::contenedor($item, 'contenedorListaBlogs', 'contenedorListaBlogs' . $elemento->id);
                    $listaBlogs[] = $item;
                }//fin del  SI Blog es activo
            }//fin del foreach

            $listaBlogs[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('BLOGS', '', '', '', 'i_like'), 'flotanteCentro margenSuperior');
        } else {
            $listaBlogs[] = $textos->id('NO_TIENES_BLOGS_QUE_TE_GUSTEN');
        }

        $bloqueBlogs .= HTML::lista($listaBlogs, 'listaVertical bordeSuperiorLista', 'botonesOcultos', '');

        return $bloqueBlogs;
    }


    private function sumarVisita() {
        global $sql;
        //$sql = new SQL();
        if (!isset($this->id)) {
            return NULL;
        }

        $numVisitas = $sql->obtenerValor('blogs', 'visitas', 'id = "' . $this->id . '"');

        $datosBlog['visitas'] = $numVisitas + 1;
	$sql->guardarBitacora = false;	
        $sumVisita = $sql->modificar('blogs', $datosBlog, 'id = "' . $this->id . '"');

        if ($sumVisita) {
            return true;
        } else {
            return false;
        }
    }

}


?>
<?php
/**
 * @package     FOLCS
 * @subpackage  Categorias
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/
/**
 * Clase Categoria: clase encargada de gestionar la informacion de los registros sobre las categorias almacenadas en el sistema.
 * por categoria hablamos de las categorias en las que se pueden incluir los items de algunos modulos, como por ejemplo noticias
 * o blogs, es decir, al agregar una noticia el usuario la puede incluir en determinada categoria.
 */
class Categoria{

    /**
     * Código interno o identificador de la categoria en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de categorias
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de una categoria específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador de la categoria en la base de datos
     * @var entero
     */
    public $idAutor;

     /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Nombre de usuario (login) del usuario creador de la categoria en la base de datos
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador de la categoria
     * @var cadena
     */
    public $autor;

    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;

    /**
     * nombre de la categoria
     * @var cadena
     */
    public $nombre;

    /**
     * Descripcion de la categoria
     * @var cadena
     */
    public $descripcion;

    /**
     * Fecha de creación de la categoria
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de categorias
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Codigos de los perfiles de usuario en los cuales serán visibles las categorias
     * @var array
     */
    public $permisosPerfiles = array();

    /**
     *
     * Inicializar la categoria
     *
     * @param entero $id Código interno o identificador de la categoria en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo             = new Modulo("CATEGORIAS");
        $this->urlBase      = "/".$modulo->url;
        $this->url          = $modulo->url;
        $this->idModulo     = $modulo->id;

        $consulta           = $sql->filaEnObjeto($sql->seleccionar(array("categoria"), array("registros" => "COUNT(id)")));
        $this->registros    = $consulta->registros;

        if (isset($id)) {
            $this->cargar($id);
            $this->permisosPerfiles = PermisosItem::cargarPerfiles($id, $modulo->id);
        }
     }//Fin del metodo constructor

    /**
     *
     * Cargar los datos de una categoria
     *
     * @param entero $id Código interno o identificador de la categoria en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("categoria", "id", intval($id))) {

            $tablas = array(
                "c" => "categoria",
                "u" => "usuarios",
                "p" => "personas",
                "i" => "imagenes"
            );

            $columnas = array(
                "id"                 => "c.id",
                "idAutor"            => "c.id_autor",
                "usuarioAutor"       => "u.usuario",
                "autor"              => "u.sobrenombre",
                "fotoAutor"          => "i.ruta",
                "nombre"             => "c.nombre",
                "descripcion"        => "c.descripcion",
                "fechaCreacion"      => "UNIX_TIMESTAMP(c.fecha_creacion)",
                "activo"             => "c.activo"
            );

            $condicion = "c.id_autor = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND c.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url       = $this->urlBase."/".$this->usuario;
                $this->fotoAutor = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesMiniaturas"]."/".$this->fotoAutor;
            }
        }
    }//Fin del metodo Cargar

    /**
     *
     * Adicionar una Categoria
     *
     * @param  arreglo $datos       Datos de la categoria a adicionar
     * @return entero               Código interno o identificador de la categoria en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion;

        $datosCategoria = array();

        $datosCategoria["nombre"]                = $datos["nombre"];
        $datosCategoria["descripcion"]           = $datos["descripcion"];
        $datosCategoria["id_autor"]              = $sesion_usuarioSesion->id;
        $datosCategoria["fecha_creacion"]        = date("Y-m-d H:i:s");     

      //nuevos datos que recibo sobre los perfiles con los que comparto y la visibilidad
        $datosModulos                       =  $datos["modulos"];
        $datosVisibilidad                   =  $datos["visibilidad"];         

        if (isset($datos["activo"])) {
            $datosCategoria["activo"]   = "1";

        } else {
            $datosCategoria["activo"]   = "0";

        }
        
        $consulta = $sql->insertar("categoria", $datosCategoria);
        $idCategoria = $sql->ultimoId;
        if ($consulta) {             
               if($datosVisibilidad == "privado"){
                                            
                    foreach($datosModulos as $idModulo => $valor){            
                        //$sql->depurar   = true;
                        $sql->insertar("categoria_modulo", array('id_categoria' => $idCategoria,'id_modulo' => $idModulo));
                        }//fin del foreach
                      return TRUE;

                }else{//si viene publico se comparte con el perfil 99
                   $idModulo       =  999;
                   //$sql->depurar   = true;
                   $sql->insertar("categoria_modulo", array('id_categoria' => $idCategoria,'id_modulo' => $idModulo));  
                    return TRUE;
                }//fin del if

           return $sql->ultimoId;

        }else{
            return NULL;

        }//fin del if($consulta)

    }//fin del metodo adicionar categoria

    /**
     *
     * Modificar una categoria
     *
     * @param  arreglo $datos       Datos de la categoria a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
 public function modificar($datos) {
        global $sql, $sesion_usuarioSesion;

        if (!isset($this->id)) {
            return NULL;
        }

        $datosCategoria = array();

        $datosCategoria["nombre"]           = $datos["nombre"];
        $datosCategoria["descripcion"]      = $datos["descripcion"];
        $datosCategoria["id_autor"]         = $sesion_usuarioSesion->id;
        $datosCategoria["fecha_creacion"]   = date("Y-m-d H:i:s");
        //nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosModulos                       = $datos["modulos"];
        $datosVisibilidad                   = $datos["visibilidad"];

        if (isset($datos["activo"])) {
            $datosCategoria["activo"] = "1";
        } else {
            $datosCategoria["activo"] = "0";
        }
        //$sql->depurar = true;
        $consulta = $sql->modificar("categoria", $datosCategoria, "id = '" . $this->id . "'");

        if ($consulta) {

            if ($datosVisibilidad == "privado") {
                $sql->eliminar("categoria_modulo", "id_categoria = '" . $this->id . "'");
                foreach ($datosModulos as $idModulo => $valor) {
                    //$sql->depurar   = true;                   
                    $sql->insertar("categoria_modulo", array('id_categoria' => $this->id, 'id_modulo' => $idModulo));
                }//fin del foreach
                return TRUE;
            } else {//si viene publico se ingresa con el id 999
                $idModulo = 999;
                //$sql->depurar   = true;
                $sql->eliminar("categoria_modulo", "id_categoria = '" . $this->id . "'");
                $sql->insertar("categoria_modulo", array('id_categoria' => $this->id, 'id_modulo' => $idModulo));
                return TRUE;
            }//fin del if
            return TRUE;
        } else {

            return false;
        }//fin del if(consulta)
    }//fin del metodo Modificar

    /**
     *
     * Eliminar una categoria
     *
     * @param entero $id    Código interno o identificador de la categoria en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        if (!($consulta = $sql->eliminar("categoria", "id = '" . $this->id . "'"))) {
            return false;
        } else {
            $sql->eliminar("categoria_modulo", "id_categoria = '" . $this->id . "'");
            return true;
        }//fin del si funciono eliminar
    }//Fin del metodo eliminar Categoria

    /**
     *
     * Listar las categorias filtrando el perfil con el cual es compartido, es decir
     * que categoria puede ver un usuario segun su perfil
     *
     * @param entero  $cantidad    Número de categorias a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de categorias
     *
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfilUsuario = NULL, $idModulo = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }
        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }
        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = "";
        }
        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "c.id NOT IN ($excepcion)";
        }
        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = "c.fecha_creacion ASC";
        } else {
            $orden = "c.fecha_creacion DESC";
        }
        //compruebo que se le haya pasado un valor al idPerfil
        $idPerfil = $idPerfilUsuario;

        $tablas = array(
            "c" => "categoria",
            "u" => "usuarios",
            "p" => "personas",
            "i" => "imagenes"
        );

        $columnas = array(
            "id"            => "c.id",
            "idAutor"       => "c.id_autor",
            "usuarioAutor"  => "u.usuario",
            "autor"         => "u.sobrenombre",
            "fotoAutor"     => "i.ruta",
            "nombre"        => "c.nombre",
            "descripcion"   => "c.descripcion",
            "fechaCreacion" => "UNIX_TIMESTAMP(c.fecha_creacion)",
            "activo"        => "c.activo"
        );

        if (!empty($condicion)) {
            $condicion .= " AND ";
        }


        $condicion .= " c.id_autor = u.id AND u.id_persona = p.id AND p.id_imagen = i.id";


        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 0) {
            $tablas["pi"] = "permisos_item";
            $columnas["idItem"] = "pi.id_item";
            $columnas["idPerfil"] = "pi.id_perfil";
            $columnas["idModulo"] = "pi.id_modulo";

            $condicion.= " AND pi.id_item = b.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil')";
        }

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($categoria = $sql->filaEnObjeto($consulta)) {
                $categoria->url = $this->urlBase . "/" . $categoria->id;
                $categoria->fotoAutor = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $categoria->fotoAutor;
                $lista[] = $categoria;
            }
        }

        return $lista;
    }//Fin del metodo de listar las categorias

/**
 *Metodo que se encarga de mostrar los checkbox con los modulos que necesitan categorizacion
 * 
 * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
 * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
 * @param type $id
 * @return type 
 */
   public static function mostrarChecksModulos($id = NULL) {
        global $sql, $textos;

        $cod = "";
        $arreglo = $sql->seleccionar(array("modulos"), array("id", "nombre"), "categoria = '1'", "id", "orden DESC");

        if (!empty($id)) {
            $modulos = $sql->seleccionar(array("categoria_modulo"), array("id_categoria", "id_modulo"), "id_categoria = '$id'");
            $listaModulos = array();

            while ($mod = $sql->filaEnObjeto($modulos)) {
                $listaModulos[] = $mod->id_modulo;
            }
        }

        while ($elemento = $sql->filaEnObjeto($arreglo)) {
            if (!empty($listaModulos)) {
                $seleccionado = (in_array($elemento->id, $listaModulos)) ? true : false;
                $cod.= HTML::campoChequeo("datos[modulos][$elemento->id]", $seleccionado) . $elemento->nombre . "<br>";
            } else {
                $cod.= HTML::campoChequeo("datos[modulos][$elemento->id]", false) . $elemento->nombre . "<br>";
            }//fin del if 
        }

        if (!empty($id)) {
            if (!in_array(999, $listaModulos)) {
                $opciones = array("style" => "display:block");
                $cod2 = HTML::contenedor(HTML::parrafo($textos->id("QUE_MODULOS_TIENEN_CATEGORIA"), "centrado negrita") . $cod, "listaCheckUsuarios", "listaCheckUsuarios", $opciones);
            } else {
                $opciones = array("style" => "display:none");
                $cod2 = HTML::contenedor(HTML::parrafo($textos->id("QUE_MODULOS_TIENEN_CATEGORIA"), "centrado negrita") . $cod, "listaCheckUsuarios", "listaCheckUsuarios", $opciones);
            }
        } else {
            $opciones = array("style" => "display:none");
            $cod2 = HTML::contenedor(HTML::parrafo($textos->id("QUE_MODULOS_TIENEN_CATEGORIA"), "centrado negrita") . $cod, "listaCheckUsuarios", "listaCheckUsuarios", $opciones);
        }

        //pongo los dos radiobutton que verifica si es publico a privado
        $opcionesPublico = array("onClick" => "$('#listaCheckUsuarios').css({ display: 'none'})"); //cargo las opciones, en este caso
        $opcionesPrivado = array("onClick" => "$('#listaCheckUsuarios').css({ display: 'block'})"); //eventos javascript

        if (!empty($id)) {
            if (in_array(999, $listaModulos)) {
                $cod3 .= HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesPublico) . $textos->id("TODOS") . HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesPrivado) . $textos->id("ALGUNOS"), "margenSuperior");
            } else {
                $cod3 .= HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "", "", "publico", $opcionesPublico) . $textos->id("TODOS") . HTML::radioBoton("datos[visibilidad]", "si", "", "privado", $opcionesPrivado) . $textos->id("ALGUNOS"), "margenSuperior");
            }
        } else {
            $cod3 .= HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesPublico) . $textos->id("TODOS") . HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesPrivado) . $textos->id("ALGUNOS"), "margenSuperior");
        }

        return $cod3 . $cod2;
    }//fin del metodo mostrarChecksModulos()

/**
 *Metodo que se encarga de Mostrar el select con las categorias de cada Modulo
 * 
 * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
 * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
 * @param type $idModulo 
 * @param type $valorPredeterminado
 * @param type $opciones arreglo de opciones, tal vez estilos o id para el selector de categorias
 * @param type $tipo
 * @return type 
 */
 public static function mostrarSelectCategorias($idModulo, $valorPredeterminado = NULL, $opciones = NULL, $tipo = NULL) {
        global $sql, $sesion_usuarioSesion;
        $cod = "";
        $categoria = array();

        $tablas = array(
            "c"     => "categoria",
            "c_m"   => "categoria_modulo"
        );

        $columnas = array(
            "id"            => "c.id",
            "nombre"        => "c.nombre",
            "idCategoria"   => "c_m.id_categoria",
            "idModulo"      => "c_m.id_modulo"
        );

        $condicion = "(c.id = c_m.id_categoria AND c_m.id_modulo = '" . $idModulo . "') OR (c.id = '0')";

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "id", "id DESC");

        while ($categorias = $sql->filaEnObjeto($consulta)) {
            $categoria[$categorias->id] = $categorias->nombre;
        }

        /******************************** MOSTRAR EL SELECTOR CON LAS OPCIONES MIS BLOGS, BLOGS I LIKE ***************************************************************/
        //la validacion del tipo es porque este mismo selector se usa para categorizar en los formularios de insercion y edicion y para filtrar en los listados
        if (isset($sesion_usuarioSesion) && $tipo == "si") {//verificar esto para que no se muestre en los formularios de adicion o modificacion
            $nombre = "";
            
            switch ($idModulo) {
                case 9:
                    $nombre = "News";
                    if (Perfil::verificarPermisosAdicion("NOTICIAS") || $sesion_usuarioSesion->idTipo == 0) {
                        $categoria["my_item"] = "My " . $nombre;
                    }
                    $categoria["i_like"] = $nombre . " I Like";
                    break;
                case 20:
                    $nombre = "Blogs";
                    if (Perfil::verificarPermisosAdicion("BLOGS") || $sesion_usuarioSesion->idTipo == 0) {
                        $categoria["my_item"] = "My " . $nombre;
                    }
                    $categoria["i_like"] = $nombre . " I Like";
                    break;
                case 21:
                    $nombre = "Events";
                    if (Perfil::verificarPermisosAdicion("EVENTOS") || $sesion_usuarioSesion->idTipo == 0) {
                        $categoria["my_item"] = "My " . $nombre;
                    }
                    $categoria["past_events"] = "Past Events";
                    break;
                case 23:
                    if (Perfil::verificarPermisosAdicion("FOROS") || $sesion_usuarioSesion->idTipo == 0) {
                        $nombre = "Forums";
                        $categoria["my_item"] = "My " . $nombre;
                    }
                    break;
                case 26:
                    $nombre = "Classes";
                    if (Perfil::verificarPermisosAdicion("CURSOS") || $sesion_usuarioSesion->idTipo == 0) {
                        $categoria["my_item"] = "My " . $nombre;
                    }
                    $categoria["i_follow"] = $nombre . " I take";
                    break;
            }//fin del switch
        }//fin del if(isset($sesion...


        $cod .= HTML::listaDesplegable("datos[categorias]", $categoria, $valorPredeterminado, "", "selectCategorias", "", $opciones);

        return $cod;
    }//fin del metodo mostrar select categorias

/**
    *Metodo que se encarga de eliminar las categorias de cada uno de los items  
    * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
    * @return null|boolean 
    */
    public static function eliminarCategorias() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        if (!($consulta = $sql->eliminar("categoria", "id = '" . $this->id . "'"))) {
            return false;
        } else {
            $sql->eliminar("categoria_modulo", "id_categoria = '" . $this->id . "'");
            return true;
        }//fin del si funciono eliminar
    }//Fin del metodo eliminar Categoria

/**
     *
     * Este es el metodo que retorna el selector de las categorias para cada uno de los modulos
     * 
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @param type $urlModulo
     * @param type $idModulo
     * @param type $valPredeterminado
     * @param type $nombreModulo
     * @param type $botonAdicionar
     * @param type $tipo
     * @return type 
     */
    public static function selectorCategorias($urlModulo, $idModulo, $valPredeterminado, $nombreModulo, $botonAdicionar, $tipo) {
        global $textos;

        $opciones = array(
            "onChange" => "var categoria = document.getElementById('selectCategorias').value;
                           document.formuCategoria.action= '/" . $urlModulo . "/category/'+categoria;
                           document.formuCategoria.submit()"
        );

        $frase      = HTML::frase($textos->id("SELECCIONE_CATEGORIA"), "negrilla");
        $select     = Categoria::mostrarSelectCategorias($idModulo, $valPredeterminado, $opciones, $tipo);
        $ruta       = HTML::urlInterna($nombreModulo, "", "", "", $valPredeterminado);
        $formulario = HTML::forma($ruta, $frase . $select, "", "", "", "", "formuCategoria");

        $filtroCategoria = HTML::contenedor($formulario . $botonAdicionar, "filtroCategoria");

        return $filtroCategoria;
    }//fin del metodo selector categorias
}
?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Centros
 * @author      Francisco J. Lozano b. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 * */
/**
 * Clase Centro: clase encargada de gestionar la informacion de los registros sobre los centros almacenados en el sistema.
 * es una clase que contiene en su mayoria metodos crud para la gestion con la bd. Los centros  representan la informacion
 * de los BNCs o centros binacionales, como por ejemplo el colombo americano, o el ICANA - Instituto Cultural Argentino Norteamericano .
 */
class Centro {

    /**
     * Código interno o identificador del centro en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de centros
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un centro específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Nombre del centro
     * @var cadena
     */
    public $nombre;

    /**
     * Descripción del centro
     * @var cadena
     */
    public $descripcion;

    /**
     * Página web del centro
     * @var cadena
     */
    public $paginaWeb;

    /**
     * Código interno o identificador en la base de datos de la ciudad del centro binacional
     * @var entero
     */
    public $idCiudad;

    /**
     * Nombre de la ciudad del centro binacional al que pertenece persona
     * @var cadena
     */
    public $ciudad;

    /**
     * Código interno o identificador en la base de datos del estado del centro binacional
     * @var entero
     */
    public $idEstado;

    /**
     * Nombre del estado del centro binacional al que pertenece persona
     * @var cadena
     */
    public $estado;

    /**
     * Código interno o identificador en la base de datos del país del centro binacional
     * @var entero
     */
    public $idPais;

    /**
     * Nombre del país del centro binacional
     * @var cadena
     */
    public $pais;

    /**
     * Código interno o identificador en la base de datos de la imagen relacionada con el centro
     * @var entero
     */
    public $idLogo;

    /**
     * Ruta de la imagen del centro en tamaño normal
     * @var cadena
     */
    public $logo;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Determina en que orden se muestra la lista
     * @var lógico
     */
    public $listaAscendente;

    /**
     * Inicializar el centro
     * @param entero $id Código interno o identificador del centro en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('CENTROS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        $consulta = $sql->filaEnObjeto($sql->seleccionar(array('centros'), array('registros' => 'COUNT(id)')));
        $this->registros = $consulta->registros;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos de un centro
     * @param entero $id Código interno o identificador del centro en la base de datos
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem('centros', 'id', intval($id))) {

            $tablas = array(
                'b' => 'centros',
                'i' => 'imagenes',
                'c' => 'ciudades',
                'e' => 'estados',
                'p' => 'paises'
            );

            $columnas = array(
                'id' => 'b.id',
                'nombre' => 'b.nombre',
                'descripcion' => 'b.descripcion',
                'paginaWeb' => 'b.pagina_web',
                'idCiudad' => 'b.id_ciudad',
                'ciudad' => 'c.nombre',
                'idEstado' => 'c.id_estado',
                'estado' => 'e.nombre',
                'idPais' => 'e.id_pais',
                'pais' => 'p.nombre',
                'idLogo' => 'b.id_imagen',
                'logo' => 'i.ruta',
                'activo' => 'b.activo'
            );

            $condicion = 'b.id_imagen = i.id AND b.id_ciudad = c.id AND c.id_estado = e.id AND e.id_pais = p.id AND b.id = "'.$id.'"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->id;
                $this->logo = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $this->logo;
            }
        }
    }

    /**
     * Adicionar un centro
     * @param  arreglo $datos       Datos del centro a adicionar
     * @return entero               Código interno o identificador del centro en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $archivo_imagen;

        if (empty($archivo_imagen['tmp_name'])) {
            return NULL;
        }

        if (isset($datos['activo'])) {
            $datos['activo'] = '1';
        } else {
            $datos['activo'] = '0';
        }

        $datosCentro = array(
            'id_ciudad' => $datos['ciudad'],
            'nombre' => htmlspecialchars($datos['nombre']),
            'descripcion' => Variable::filtrarTagsInseguros($datos['descripcion']),
            'pagina_web' => htmlspecialchars($datos['paginaWeb']),
            'activo' => $datos['activo']
        );


        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            $objetoImagen = new Imagen();

            $datosImagen = array(
                'idRegistro' => '',
                'modulo' => 'CENTROS',
                'descripcion' => 'Center Image',
                'titulo' => 'Center Image'
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }


        $datosCentro['id_imagen'] = $idImagen;

        $consulta = $sql->insertar('centros', $datosCentro);

        if ($consulta) {
            $idCentro = $sql->ultimoId;

            return $idCentro;
        } else {
            return NULL;
        }
    }

    /**
     * Modificar un centro
     * @param  arreglo $datos       Datos del centro a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
        }

        if (isset($datos['activo'])) {
            $datos['activo'] = '1';
        } else {
            $datos['activo'] = '0';
        }

        $idImagen = $this->idLogo;

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            if ($this->idLogo == '8') {
                $objetoImagen = new Imagen();
            } else {
                $objetoImagen = new Imagen($this->idLogo);
                $objetoImagen->eliminar();
            }

            $datosImagen = array(
                'idRegistro' => $this->id,
                'modulo' => 'CENTROS',
                'titulo' => 'Center Image',
                'descripcion' => 'Center Image'
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }


        $datosCentro = array(
            'id_ciudad' => $datos['ciudad'],
            'nombre' => htmlspecialchars($datos['nombre']),
            'descripcion' => Variable::filtrarTagsInseguros($datos['descripcion']),
            'pagina_web' => htmlspecialchars($datos['paginaWeb']),
            'activo' => $datos['activo'],
            'id_imagen' => $idImagen
        );

        $consulta = $sql->modificar('centros', $datosCentro, 'id = "' . $this->id . '"');

        return $consulta;
    }

    /**
     * Eliminar un centro
     * @param entero $id    Código interno o identificador del centro en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('centros', 'id = "' . $this->id . '"');
        if ($consulta) {
            $imagen = new Imagen($this->idLogo);
            $imagen->eliminar();
        }
        return $consulta;
    }

    /**
     * Listar las centros
     * @param entero  $cantidad    Número de centros a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de centros
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'b.id NOT IN ('.$excepcion.')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'p.nombre ASC, e.nombre ASC, c.nombre ASC, b.nombre ASC';
        } else {
            $orden = 'p.nombre ASC, e.nombre ASC, c.nombre ASC, b.nombre DESC';
        }

        $tablas = array(
            'b' => 'centros',
            'i' => 'imagenes',
            'c' => 'ciudades',
            'e' => 'estados',
            'p' => 'paises'
        );

        $columnas = array(
            'id' => 'b.id',
            'nombre' => 'b.nombre',
            'idCiudad' => 'b.id_ciudad',
            'ciudad' => 'c.nombre',
            'idEstado' => 'c.id_estado',
            'estado' => 'e.nombre',
            'idPais' => 'e.id_pais',
            'pais' => 'p.nombre',
            'idLogo' => 'b.id_imagen',
            'logo' => 'i.ruta',
            'activo' => 'b.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'b.id_imagen = i.id AND b.id_ciudad = c.id AND c.id_estado = e.id AND e.id_pais = p.id AND b.id > 0'; // AND p.id != 234';

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($centro = $sql->filaEnObjeto($consulta)) {
                $centro->url = $this->urlBase . '/' . $centro->id;
                $centro->logo = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $centro->logo;
                $lista[] = $centro;
            }
        }

        return $lista;
    }

}

?>
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Ciudades
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/

/**
 * Clase Ciudad: clase encargada de gestionar la informacion de los registros sobre las ciudades almacenadas en el sistema.
 * es una clase que contiene en su mayoria metodos crud para la gestion con la bd, ademas de esto tiene algunos metodos
 * para el renderizado de informacion, como por ejemplo el metodo generar tabla.Esta clase mantiene una relacion directa
 * con las clases paises y estados, ya que una ciudad pertenece a un estado y un estado pertenece a un pais.
 */
class Ciudad {

    /**
     * Código interno o identificador del país en la base de datos
     * @var entero
     */
    public $id;


    /**
     * URL relativa del módulo de la ciudad
     * @var cadena
     */
    public $urlBase;


    /**
     * URL relativa de una ciudad específica
     * @var cadena
     */
    public $url;


    /**
     * Nombre de la ciudad
     * @var cadena
     */
    public $nombre;


    /**
     * id del Estado
     * @var cadena
     */
    public $idEstado;


     /**
     * nombre del Estado
     * @var cadena
     */
    public $Estado;
    
    /**
     * id del Estado
     * @var cadena
     */
    public $idPais;


     /**
     * nombre del pais y su bandera
     * @var cadena
     */
    public $pais;   
    
     /**
     * solo el nombre del pais
     * @var cadena
     */
    public $paisSolo;       
    

     /**
     * nombre del Estado
     * @var cadena
     */
    public $codigo;      


    /**
     * Indicador del orden cronológio de la lista de ciudades
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
     * Inicializar la Ciudad
     *
     * @param entero $id Código interno o identificador de la ciudad en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo        = new Modulo("CIUDADES");
        $this->urlBase = "/".$modulo->url;
        $this->url     = $modulo->url;
       
        $this->registros = $sql->obtenerValor("ciudades", "COUNT(id)", "id != '0'");
        //establecer el valor del campo predeterminado para organizar los listados
        $this->ordenInicial = "nombre";

        if (isset($id)) {
            $this->cargar($id);
        }
    }





    /**
     *
     * Cargar los datos de una ciudad
     *
     * @param entero $id Código interno o identificador de la ciudad en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("ciudades", "id", intval($id))) {

            $tablas = array(
                "c" => "ciudades",
                "e" => "estados",
                "p" => "paises"
            );

            $columnas = array(
                "id"       => "c.id",
                "idEstado" => "c.id_estado",
                "nombre"   => "c.nombre",
                "Estado"   => "e.nombre",
                "idPais"   => "e.id_pais",
                "pais"     => "p.nombre",
                "codigo"   => "p.codigo_iso"
            );

            $condicion = "c.id_estado = e.id AND e.id_pais = p.id AND c.id = '$id'";

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
     * Adicionar una ciudad
     *
     * @param  arreglo $datos       Datos de la ciudad a adicionar
     * @return entero               Código interno o identificador de la ciudad en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql;
        
        $idEstado = $sql->obtenerValor("lista_estados", "id", "cadena = '".utf8_decode($datos["id_estado"]) ."'");
        
        $datosCiudad = array(
            "nombre"    => $datos["nombre"],
            "id_estado" => $idEstado
        );

        $consulta = $sql->insertar("ciudades", $datosCiudad);

        if ($consulta) {
            return $sql->ultimoId;

        } else {
            return NULL;
        }
    }



    /**
     *
     * Modificar una ciudad
     *
     * @param  arreglo $datos       Datos de la ciudad a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        
        $idEstado = $sql->obtenerValor("lista_estados", "id", "cadena = '".utf8_decode($datos["id_estado"])."'");
        
        $datosCiudad = array(
            "nombre"    => $datos["nombre"],
            "id_estado" => $idEstado
        );
        

        $consulta = $sql->modificar("ciudades", $datosCiudad, "id = '".$this->id."'");
        return $consulta;
    }




    /**
     *
     * Eliminar una ciudad
     *
     * @param entero $id    Código interno o identificador de la ciudad en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("ciudades", "id = '".$this->id."'");
        return $consulta;
    }





    /**
     *
     * Listar las ciudades
     *
     * @param entero  $cantidad    Número de ciudadesa incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de ciudades
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
            $condicion = "c.id NOT IN ($excepcion) AND ";
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
            "c" => "ciudades",
            "e" => "estados",
            "p" => "paises"
        );

        $columnas = array(
            "id"       => "c.id",
            "idEstado" => "c.id_estado",
            "nombre"   => "c.nombre",
            "Estado"   => "e.nombre",
            "idPais"   => "e.id_pais",
            "pais"     => "p.nombre",
            "codigo"   => "p.codigo_iso"
        );

            
        
         
        if (!empty($condicionGlobal)) {
            
            $condicion .= $condicionGlobal." AND ";
        } 
        
        $condicion .= "c.id_estado = e.id AND e.id_pais = p.id";
       

        if (is_null($this->registrosConsulta)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registrosConsulta = $sql->filasDevueltas;
        }
	$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($ciudad = $sql->filaEnObjeto($consulta)) {
                $ciudad->url = $this->urlBase."/".$ciudad->id;
                $ciudad->pais = HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["iconosBanderas"] . "/" . strtolower($ciudad->codigo) . ".png", "miniaturaBanderas margenDerechaTriple").$ciudad->pais;
                $lista[]   = $ciudad;
            }
        }

        return $lista;

    }
    
    
    public function generarTabla($arregloRegistros, $datosPaginacion = NULL){
        global $textos;
        //Declaracion de las columnas que se van a mostrar en la tabla
        $datosTabla = array(                      
            HTML::parrafo( $textos->id("NOMBRE")                ,  "centrado" ) => "nombre|c.nombre",
            HTML::parrafo( $textos->id("ESTADO")                ,  "centrado" ) => "Estado|e.nombre",
            HTML::parrafo( $textos->id("PAIS")                  ,  "centrado" ) => "pais|p.nombre"
        );        
        //ruta a donde se mandara la accion del doble click
        $rutaPaginador = "/ajax".$this->urlBase."/move";
        
        return Recursos::generarTablaRegistros($arregloRegistros, $datosTabla, $rutaPaginador, $datosPaginacion).HTML::crearMenuBotonDerecho("CIUDADES");
        
    }    
    
    
    
}
?>
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
/**
 * Clase Comunicado: gestiona la informacion del comunicado que se publica en el home de ablaonline.
 *Nota: clase obsoleta, ya no se usa mas este comunicado en la pagina principal.
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
        global $sql;

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
        global $sql;

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
        global $sql;

       
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
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Contactos
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón , William Vargas
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * */
class Contacto {

    /**
     * Código interno o identificador del usuario en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de usuarios
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un usuario específico
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del tipo de usuario en la base de datos
     * @var entero
     */
    public $id_usuario;

    /**
     * Nombre del tipo de usuario
     * @var cadena
     */
    public $id_contacto;

    /**
     * Estado del usuario
     * @var cadena
     */
    public $estado;

    /**
     * id de la persona que es un usuario
     * @var entero
     * */
    public $idPersona;

    /**
     * Usuario del contacto
     * @var cadena
     */
    public $usuario;

    /**
     * Nombre del contacto
     * @var cadena
     */
    public $sobrenombre;

    /**
     * Persona contacto
     * @var cadena
     */
    public $persona;

    /**
     * Código interno o identificador en la base de datos de la persona con la cual está relacionada el usuario
     * @var entero
     */
    public $idModulo;

    /**
     * Indicador del orden cronológio de la lista de usuarios
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     *
     * Inicializar del contacto
     *
     * @param entero $id Código interno o identificador del usuario en la base de datos
     *
     */
    public function __construct($id_contacto = NULL) {

        $modulo = new Modulo("CONTACTOS");
        $this->urlBase = "/" . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        if (isset($id_contacto) && !empty($id_contacto)) {
            $this->cargar($id_contacto);
        }
    }

    /**
     * Cargar los datos del usuario
     * @param entero $id Código interno o identificador del usuario en la base de datos
     */
    public function cargar($id = NULL) {
        global $sql, $sesion_usuarioSesion;

        if (!isset($id)) {
            return NULL;
        }

        $tablas = array(
            "c" => "lista_contactos"
        );

        $columnas = array(
            "id_usuario" => "c.id_usuario",
            "id_contacto" => "c.id_contacto",
            "id_persona" => "c.id_persona",
            "estado" => "c.estado",
            "usuario" => "c.usuario",
            "sobrenombre" => "c.sobrenombre"
        );


        $condicion = "c.id_usuario = '" . $sesion_usuarioSesion->id . "' AND c.id_contacto = '" . $id . "'";

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $fila = $sql->filaEnObjeto($consulta);

            foreach ($fila as $propiedad => $valor) {
                $this->$propiedad = $valor;
            }

            $this->url = $this->urlBase . "/" . $this->usuario;
            $this->persona = new Persona($this->id_persona);
        }
    }

    /**
     * Metodo que ingresa a la BD la informacion sobre una solicitud de amistad y a su vez
     * notifica a la persona en el sistema ablaonline y en el correo en caso de querer recibir notificaciones al correo
     * sobre dichas solicitudes
     * */
    public function solicitarAmistad($idContacto) {
        global $sql, $sesion_usuarioSesion, $textos;

        if (!isset($idContacto)) {
            return NULL;
        }

        $datos = array(
            "id_usuario_solicitante" => $sesion_usuarioSesion->id,
            "id_usuario_solicitado" => $idContacto,
            "estado" => "0"
        );

        $consulta = $sql->insertar("contactos", $datos);
        if ($consulta) {
            //desea recibir notificacion al correo???
            if (Recursos::recibirNotificacionesAlCorreo($idContacto)) {
                $contacto = new Usuario($idContacto);
                $mensaje = str_replace("%1", $sesion_usuarioSesion->persona->nombreCompleto, $textos->id("QUIERE_SER_TU_AMIGO"));
                Servidor::enviarCorreo($contacto->persona->correo, $mensaje, $mensaje);
            }

            $notificacion = str_replace("%1", HTML::enlace($sesion_usuarioSesion->persona->nombreCompleto, HTML::urlInterna("USUARIOS", $sesion_usuarioSesion->usuario)), $textos->id("QUIERE_SER_TU_AMIGO"));
            Servidor::notificar($idContacto, $notificacion, array(), '1');

            return true;
        } else {
            return false;
        }//fin del if     
    }

    /**
     * Metodo aceptar amistad, modifica  una solicitud de amistad que le hayan hecho al usuario
     * */
    public function aceptarAmistad($idContacto) {
        global $sql, $sesion_usuarioSesion, $textos;

        if (empty($idContacto) || !$sql->existeItem('usuarios', 'id', $idContacto)) {
            return NULL;
        }

        $datos = array(
            "estado" => "1"
        );

        $consulta = $sql->modificar("contactos", $datos, "id_usuario_solicitado = '" . $sesion_usuarioSesion->id . "' AND id_usuario_solicitante = '" . $idContacto . "'");
        if ($consulta) {


            if (Recursos::recibirNotificacionesAlCorreo($idContacto)) {
                $contacto = new Usuario($idContacto);
                $mensaje = str_replace("%1", $sesion_usuarioSesion->persona->nombreCompleto, $textos->id("TE_HA_ACEPTADO_COMO_AMIGO"));
                Servidor::enviarCorreo($contacto->persona->correo, $mensaje, $mensaje);
            }


            $contacto_usuario = $sql->obtenerValor("usuarios", "usuario", "id = '$idContacto'");
            $notificacion1 = str_replace("%1", HTML::enlace($sesion_usuarioSesion->usuario, HTML::urlInterna("USUARIOS", $sesion_usuarioSesion->usuario)), $textos->id("MENSAJE_ADICION_CONTACTO"));
            $notificacion = str_replace("%2", HTML::enlace($contacto_usuario, HTML::urlInterna("USUARIOS", $contacto_usuario)), $notificacion1);

            //Consulto los contactos del usuario con la sesion actual donde él ha sido el usuario solicitante
            $contactos1 = $sql->seleccionar(array("contactos"), array("id_usuario_solicitado"), "id_usuario_solicitante = '" . $sesion_usuarioSesion->id . "' AND id_usuario_solicitado != '" . $idContacto . "' AND estado = '1'", "", "");
            if ($sql->filasDevueltas) {
                while ($contacto_lista = $sql->filaEnObjeto($contactos1)) {
                    Servidor::notificar($contacto_lista->id_usuario_solicitado, $notificacion, array(), '5');
                }
            }

            //Consulto los contactos del usuario con la sesion actual donde él ha sido el usuario solicitado
            $contactos2 = $sql->seleccionar(array("contactos"), array("id_usuario_solicitante"), "id_usuario_solicitante != '" . $idContacto . "' AND id_usuario_solicitado = '" . $sesion_usuarioSesion->id . "' AND estado = '1'", "", "");
            if ($sql->filasDevueltas) {
                while ($contacto_lista = $sql->filaEnObjeto($contactos2)) {
                    Servidor::notificar($contacto_lista->id_usuario_solicitante, $notificacion, array(), '5');
                }
            }

            //Consulto los contactos del usuario que solicitó la amistad donde él ha sido el usuario solicitante
            $contactos3 = $sql->seleccionar(array("contactos"), array("id_usuario_solicitado"), "id_usuario_solicitante = '" . $idContacto . "' AND id_usuario_solicitado != '" . $sesion_usuarioSesion->id . "' AND estado = '1'", "", "");
            if ($sql->filasDevueltas) {
                while ($contacto_lista = $sql->filaEnObjeto($contactos3)) {
                    Servidor::notificar($contacto_lista->id_usuario_solicitado, $notificacion, array(), '5');
                }
            }

            //Consulto los contactos del usuario que solicitó la amistad donde él ha sido el usuario solicitado
            $contactos4 = $sql->seleccionar(array("contactos"), array("id_usuario_solicitante"), "id_usuario_solicitante != '" . $sesion_usuarioSesion->id . "' AND id_usuario_solicitado = '" . $idContacto . "' AND estado = '1'", "", "");
            if ($sql->filasDevueltas) {
                while ($contacto_lista = $sql->filaEnObjeto($contactos4)) {
                    Servidor::notificar($contacto_lista->id_usuario_solicitante, $notificacion, array(), '5');
                }
            }

            return true;
        } else {
            return false;
        }//fin del if        
    }

    /**
     * Metodo rechazar amistad, elimina un registro en la BD contactos donde el usuario solicitado es el usuario actual
     * */
    public function rechazarAmistad($idContacto) {
        global $sql, $sesion_usuarioSesion;

        if (empty($idContacto) || !$sql->existeItem('usuarios', 'id', $idContacto)) {
            return NULL;
        }

        $condicion = "id_usuario_solicitante = '$idContacto' AND id_usuario_solicitado = '$sesion_usuarioSesion->id'";

        $borrar = $sql->eliminar("contactos", $condicion);

        if ($borrar) {
            return true;
        } else {
            return false;
        }//fin del if        
    }

//fin del metodo insertar

    /**
     * Metodo Eliminar--> Elimina una relacion de amistad entre dos usuarios, borra un registro de la BD de la tabla contactos donde el 
     * id_usuario_solicitante puede ser tanto el usuario de la sesion activa, como otro usuario que haya solicitado su amistad.
     * */
    public function eliminarAmistad($idContacto) {
        global $sql, $sesion_usuarioSesion;

        if (empty($idContacto) || !$sql->existeItem('usuarios', 'id', $idContacto)) {
            return NULL;
        }

        $condicion = "(id_usuario_solicitante = '$idContacto' AND id_usuario_solicitado = '$sesion_usuarioSesion->id') OR (id_usuario_solicitante = '$sesion_usuarioSesion->id' AND id_usuario_solicitado = '$idContacto')";
        $borrar = $sql->eliminar("contactos", $condicion);

        if ($borrar) {
            return true;
        } else {
            return false;
        }//fin del if        
    }

    /**
     * Lista las amistades de el usuario
     * @param entero  $cantidad    Número de usuarios a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de usuarios
     *
     */
    public function listarContactos($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idUsuario = NULL, $condicion2 = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }


        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "lu.id NOT IN ($excepcion)";
        }

        $tablas = array(
            "c" => "contactos",
            "lu" => "lista_usuarios"
        );


        $columnas = array(
            "id_usuario" => "c.id_usuario_solicitante",
            "id_contacto" => "c.id_usuario_solicitado",
            "estado" => "c.estado",
            "id" => "lu.id",
            "usuario" => "lu.usuario",
            "genero" => "lu.genero",
            "codigoIsoPais" => "lu.codigo_iso_pais",
            "nombre" => "lu.nombre",
            "imagen" => "lu.imagen",
            "ciudad" => "lu.ciudad",
            "pais" => "lu.pais",
            "correo" => "lu.correo",
            "centro" => "lu.centro",
            "tipo_usuario" => "lu.tipo_usuario"
        );

        if (!empty($condicion)) {
            $condicion .= " AND ";
        }
        $condicion3 = "";
        if (!empty($condicion2)) {
            $condicion3 = " AND " . $condicion2;
        }

        $condicion .= "(c.id_usuario_solicitante = '" . $idUsuario . "' AND c.estado = '1' AND c.id_usuario_solicitado = lu.id $condicion3) OR (c.id_usuario_solicitado = '" . $idUsuario . "' AND c.estado = '1' AND c.id_usuario_solicitante = lu.id $condicion3)";

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        //$sql->depurar = true;        
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", "nombre ASC", $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($contacto = $sql->filaEnObjeto($consulta)) {
                $contacto->url = $this->urlBase . "/" . $contacto->usuario;
                $contacto->icono = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $contacto->imagen;
                $lista[] = $contacto;
            }
        }

        return $lista;
    }

    /**
     * Lista las amistades de el usuario
     * @param entero  $cantidad    Número de usuarios a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de usuarios
     */
    public function listarSolicitudesAmistad($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idUsuario = NULL) {
        global $sql;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }


        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "lu.id NOT IN ($excepcion)";
        }

        $tablas = array(
            "c" => "contactos",
            "lu" => "lista_usuarios"
        );


        $columnas = array(
            "id_usuario" => "c.id_usuario_solicitante",
            "id_contacto" => "c.id_usuario_solicitado",
            "estado" => "c.estado",
            "id" => "lu.id",
            "usuario" => "lu.usuario",
            "genero" => "lu.genero",
            "codigoIsoPais" => "lu.codigo_iso_pais",
            "nombre" => "lu.nombre",
            "imagen" => "lu.imagen",
            "ciudad" => "lu.ciudad",
            "pais" => "lu.pais",
            "correo" => "lu.correo",
            "centro" => "lu.centro",
            "tipo_usuario" => "lu.tipo_usuario"
        );

        if (!empty($condicion)) {
            $condicion .= " AND ";
        }

        $condicion .= "c.id_usuario_solicitado = '" . $idUsuario . "' AND c.estado = '0' AND c.id_usuario_solicitante = lu.id";

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", "", $inicio, $cantidad);

        $lista = array();

        if ($sql->filasDevueltas) {

            while ($contacto = $sql->filaEnObjeto($consulta)) {
                $contacto->url = $this->urlBase . "/" . $contacto->usuario;
                $lista[] = $contacto;
            }
        }

        return $lista;
    }

    /**
     * Metodo que cuenta todos los contactos de un usuario dterminado
     */
    public static function contarContactos($idUsuario) {
        global $sql;

        if (empty($idUsuario)) {
            return NULL;
        }
        //$sql->depurar = true;
        $consulta = $sql->obtenerValor("contactos", "COUNT(id)", "(id_usuario_solicitante = " . $idUsuario . " AND estado = '1') OR (id_usuario_solicitado = " . $idUsuario . " AND estado = '1')");
        //$consulta = HTML::frase($consulta, "", "cantidadContactos");

        return $consulta;
    }

    /**
     * Metodo para cargar el formulario aceptar amistad
     * */
    public static function formaAceptarAmistad($idContacto) {

        $cod = "";
        $datos = array("id_contacto" => $idContacto);


        $opciones = array(
            "onClick" => "$('#contactosPendientes" . $idContacto . "').fadeOut(500); 
                                        $('#contactosPendientes" . $idContacto . "').remove();
                                        $('#contactosNuevosPendientes" . $idContacto . "').show('drop', {}, 300);
                                        $('#sinContactos').fadeOut(500);
                                        aceptarAmistadJS();",
            "onMouseOver" => "$('#ayudaAmistad" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#ayudaAmistad" . $idContacto . "').hide('drop', {}, 300)"
        );



        $url = HTML::urlInterna("CONTACTOS", "", true, "acceptFriendRequest");

        $ayuda = HTML::contenedor("Click to Accept...", "ayudaAmistad", "ayudaAmistad" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "aceptarAmistad", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");

        $cod .= HTML::contenedor($boton, "aceptarAmistadInterno", "aceptarAmistadInterno");

        return $cod;
    }

    /**
     * Metodo para cargar el formulario aceptar amistad que aparece en el formulario de la ventana modal del buscador
     * cuando se busca a un usuario y este previamente nos ha enviado una solicitud de amistad, esta aparece directamente en el buscador
     * y nos aparece este formulario para aceptar la mistad, o el de rechazar la amistad
     * */
    public static function formaAceptarAmistad2($idContacto) {
        global $textos;

        $cod = "";
        $datos = array("id_contacto" => $idContacto);


        $opciones = array(
            "onClick" => "$('#contactosPendientes" . $idContacto . "').fadeOut(500);
                                      setTimeout(function(){ $('#contactosPendientes" . $idContacto . "').remove(); }, 550);
                                      $('#contactosNuevosPendientes" . $idContacto . "').show('drop', {}, 300);
                                      $('#sinContactos').fadeOut(500);
                                      $(this).fadeOut(500);
                                      $('#textoContactoAceptado').fadeIn(500);
                                      aceptarAmistadJS();",
            "onMouseOver" => "$('#ayudaAmistad2" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#ayudaAmistad2" . $idContacto . "').hide('drop', {}, 300)"
        );


        $url = HTML::urlInterna("CONTACTOS", "", true, "acceptFriendRequest");

        $ayuda = HTML::contenedor($textos->id("CLICK_PARA_ACEPTAR"), "ayudaAmistad", "ayudaAmistad2" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "aceptarAmistad", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");
        $boton .= HTML::frase($textos->id("SOLICITUD_ACEPTADA"), "oculto negrilla", "textoContactoAceptado");
        //$contenido =  HTML::frase("Accept...??", "cantidadDestacados").$boton;

        $cod .= HTML::contenedor($boton, "aceptarAmistadInterno2", "aceptarAmistadInterno");

        return $cod;
    }

    /**
     * Metodo para cargar el formulario aceptar amistad que aparece en el formulario de la ventana modal del buscador
     * */
    public static function formaSolicitudEnviada($idContacto) {
        global $textos;

        $opciones = array(
            "onMouseOver" => "$('#ayudaAmistad" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#ayudaAmistad" . $idContacto . "').hide('drop', {}, 300)"
        );

        $ayuda = HTML::contenedor($textos->id("SOLICITUD_YA_ENVIADA"), "ayudaAmistadM", "ayudaAmistad" . $idContacto, array("style" => "display: none"));

        $cod = HTML::contenedor($ayuda, "solicitudEnviada", "solicitudEnviada", $opciones);

        return $cod;
    }

    /**
     * Metodo para cargar el formulario rechazar amistad
     *
     * */
    public static function formaRechazarAmistad($idContacto) {
        global $textos;

        $cod = "";
        $datos = array("id_contacto" => $idContacto);

        $opciones = array(
            "onClick" => "$('#contactosPendientes" . $idContacto . "').fadeOut(500);
                                      $('#contactosPendientes" . $idContacto . "').remove(500);
                                      rechazarAmistadJS();", //voy a llamar funcion javascript que verifica contactos pendientes, si no queda ninguno elimino el bloque completo
            "onMouseOver" => "$('#rechazaAmistad" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#rechazaAmistad" . $idContacto . "').hide('drop', {}, 300)"
        );


        $url = HTML::urlInterna("CONTACTOS", "", true, "rejectFriendRequest");

        $ayuda = HTML::frase($textos->id("CLICK_PARA_RECHAZAR_SOLICITUD"), "ayudaAmistad", "rechazaAmistad" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "rechazarAmistad", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");

        $cod .= HTML::contenedor($boton, "rechazarAmistadInterno", "rechazarAmistadInterno");

        return $cod;
    }

    /**
     * Metodo para cargar el formulario para eliminar una amistad
     * */
    public static function formaEliminarAmistad($idContacto) {
        global $textos;

        $cod = "";
        $datos = array("id_contacto" => $idContacto);

        $opciones = array(
            "onMouseOver" => "$('#eliminaAmistad" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#eliminaAmistad" . $idContacto . "').hide('drop', {}, 300)"
        );


        $url = HTML::urlInterna("CONTACTOS", "", true, "deleteFriend");

        $ayuda = HTML::contenedor($textos->id("CLICK_PARA_BORRAR_AMISTAD"), "ayudaAmistadL", "eliminaAmistad" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "eliminarAmistad", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");

        $cod .= HTML::contenedor($boton, "eliminarAmistadInterno", "eliminarAmistadInterno");

        return $cod;
    }

    /**
     * Metodo para cargar el formulario para solicitar una amistad
     * */
    public static function formaSolicitarAmistad($idContacto) {
        global $textos;

        $cod = "";
        $datos = array("id_contacto" => $idContacto);

        $opciones = array(
            "onMouseOver" => "$('#solicitaAmistad" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#solicitaAmistad" . $idContacto . "').hide('drop', {}, 300)"
        );


        $url = HTML::urlInterna("CONTACTOS", "", true, "requestFriendship");

        $ayuda = HTML::contenedor($textos->id("CLICK_PARA_SOLICITAR_AMISTAD"), "ayudaAmistadL", "solicitaAmistad" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "solicitarAmistad", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");

        $cod .= HTML::contenedor($boton, "solicitarAmistadInterno", "solicitarAmistadInterno" . $idContacto);


        return $cod;
    }

    /**
     * Metodo para cargar el formulario para enviar un Mensaje
     * Directamente desde la lista de contactos 
     * */
    public static function formaEnviarMensaje($idContacto) {
        global $textos;

        $cod = "";
        $datos = array("id_usuario_destinatario" => $idContacto);

        $opciones = array(
            "onMouseOver" => "$('#enviaMensaje" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#enviaMensaje" . $idContacto . "').hide('drop', {}, 300)"
        );


        $url = HTML::urlInterna("CONTACTOS", "", true, "sendMessage");

        $ayuda = HTML::contenedor($textos->id("CLICK_PARA_ENVIAR_UN_MENSAJE"), "ayudaAmistadL", "enviaMensaje" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "formaEnviarMensaje", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");

        $cod .= HTML::contenedor($boton, "formaEnviarMensajeInterno", "solicitarAmistadInterno" . $idContacto);

        return $cod;
    }

    /**
     * Metodo para cargar el formulario para enviar un Mensaje
     * */
    public static function formaEnviarMensajeDesdeBuscador($idContacto) {
        global $textos;

        $cod = "";
        $datos = array("id_usuario_destinatario" => $idContacto);

        $opciones = array(
            "onMouseOver" => "$('#enviaMensaje2" . $idContacto . "').show('drop', {}, 300)",
            "onMouseOut" => "$('#enviaMensaje2" . $idContacto . "').hide('drop', {}, 300)"
        );

        $url = HTML::urlInterna("CONTACTOS", "", true, "sendMessage");

        $ayuda = HTML::contenedor($textos->id("CLICK_PARA_ENVIAR_UN_MENSAJE"), "ayudaAmistadL", "enviaMensaje2" . $idContacto, array("style" => "display: none"));

        $boton = $ayuda . HTML::botonImagenAjax("", "formaEnviarMensaje", "enviarFormaAjax", $opciones, $url, $datos, "formaMeGusta");

        $cod .= HTML::contenedor($boton, "formaEnviarMensajeInterno", "solicitarAmistadInterno" . $idContacto);

        return $cod;
    }

    /**
     * Metodo que se encarga de verificar si dos personas tienen establecida una relacion de amistad
     */
    public static function verificarAmistad($idUsuario) {
        global $sql, $sesion_usuarioSesion;

        if (empty($idUsuario) || !isset($sesion_usuarioSesion)) {
            return NULL;
        }

        $usuarioSolicitante = $sql->existeItem("contactos", "id_usuario_solicitante", $idUsuario, "id_usuario_solicitado = '" . $sesion_usuarioSesion->id . "' AND estado = '1'");

        $usuarioSolicitado = $sql->existeItem("contactos", "id_usuario_solicitante", $sesion_usuarioSesion->id, "id_usuario_solicitado = '" . $idUsuario . "' AND estado = '1'");

        if ($usuarioSolicitante || $usuarioSolicitado) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Metodo que se encarga de verificar si el usuario que tiene la sesion ha ENVIADO una solicitud de amistad
     * al usuario que esta viendo
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @param type $idUsuario entero ->identificador del usuario que se esta observando
     * @return type boolean
     */
    public static function verificarEstadoSolicitudEnviada($idUsuario) {
        global $sql, $sesion_usuarioSesion;

        if (empty($idUsuario) || !isset($sesion_usuarioSesion)) {
            return NULL;
        }

        $usuarioSolicitado = $sql->existeItem("contactos", "id_usuario_solicitante", $sesion_usuarioSesion->id, "id_usuario_solicitado = '" . $idUsuario . "' AND estado = '0'");

        if ($usuarioSolicitado) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Metodo que se encarga de verificar si el usuario que tiene la sesion ha RECIBIDO una solicitud de amistad
     * al usuario que esta viendo
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @param type $idUsuario entero ->identificador del usuario que se esta observando
     * @return type boolean
     */
    public static function verificarEstadoSolicitudRecibida($idUsuario) {
        global $sql, $sesion_usuarioSesion;

        if (empty($idUsuario) || !isset($sesion_usuarioSesion)) {
            return NULL;
        }

        $usuarioSolicitante = $sql->existeItem("contactos", "id_usuario_solicitante", $idUsuario, "id_usuario_solicitado = '" . $sesion_usuarioSesion->id . "' AND estado = '0'");

        if ($usuarioSolicitante) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Metodo que sen encarga de mostrar la cantidad de amigos
     * conectados de la persona que ha iniciado la sesion
     */
    public static function cantidadAmigosConectados() {
        global $sql, $sesion_usuarioSesion;

        $tablas = array(
            "c" => "contactos",
            "uc" => "usuarios_conectados"
        );

        $columnas = array(
            "id_contacto1" => "c.id_usuario_solicitante",
            "id_contacto2" => "c.id_usuario_solicitado",
            "estado" => "c.estado",
            "id_usuario" => "uc.id_usuario",
            "visible" => "uc.visible"
        );

        $condicion = "(c.id_usuario_solicitante = " . $sesion_usuarioSesion->id . " AND c.id_usuario_solicitado = uc.id_usuario AND c.estado = '1' AND uc.visible = '1') OR (c.id_usuario_solicitado = " . $sesion_usuarioSesion->id . " AND c.id_usuario_solicitante = uc.id_usuario AND c.estado = '1' AND uc.visible = '1')";

        //$sql->depurar = true;
        $sql->seleccionar($tablas, $columnas, $condicion);
        if ($sql->filasDevueltas) {
            return $sql->filasDevueltas;
        } else {
            return "0 ";
        }
    }

    /**
     * Metodo que sen encarga de mostrar los amigos
     * conectados de la persona que ha iniciado la sesion,
     * mostrando su foto en miniatura
     */
    public static function amigosConectados() {
        global $sql, $sesion_usuarioSesion, $textos, $configuracion;

        $tablas = array(
            "c" => "contactos",
            "uc" => "usuarios_conectados",
            "u" => "usuarios",
            "p" => "personas",
            "i" => "imagenes"
        );

        $columnas = array(
            "id_contacto1" => "c.id_usuario_solicitante",
            "id_contacto2" => "c.id_usuario_solicitado",
            "estado" => "c.estado",
            "id_usuario" => "uc.id_usuario",
            "visible" => "uc.visible",
            "sobrenombre" => "u.sobrenombre",
            "usuario" => "u.usuario",
            "imagen" => "i.ruta"
        );

        $condicion = "(c.id_usuario_solicitante = " . $sesion_usuarioSesion->id . " AND c.id_usuario_solicitado = uc.id_usuario AND c.estado = '1' AND uc.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND uc.visible = '1') OR (c.id_usuario_solicitado = " . $sesion_usuarioSesion->id . " AND c.id_usuario_solicitante = uc.id_usuario AND c.estado = '1' AND uc.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND uc.visible = '1')";

        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
        if ($sql->filasDevueltas) {
            while ($contacto = $sql->filaEnObjeto($consulta)) {
                $contacto->foto = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $contacto->imagen;
                $lista[] = $contacto;
            }

            foreach ($lista as $elemento) {
                $item = HTML::enlace(HTML::imagen($elemento->foto, "flotanteIzquierda  margenDerecha miniaturaListaChat"), '/users/' . $elemento->usuario);
                $opciones = array("onClick" => "javascript:chatWith('" . $elemento->usuario . "')");
                $item .= HTML::enlace(HTML::frase($elemento->sobrenombre, "claseUsuariosConectados margenSuperior", "usuarioChat_" . $elemento->usuario), "javascript:void(0)", 'margenSuperior', "", $opciones);
                $listaContactos[] = $item;
            }

            $listaContactos = HTML::lista($listaContactos, "listaVertical listaConIconos bordeSuperiorLista", "", "");
            $codigo = HTML::contenedor($listaContactos, "contenedorChat");

            return $codigo;
        } else {
            return $textos->id("NO_HAY_CONTACTOS_CONECTADOS");
        }
    }



    /**
     * Metodo que sen encarga de mostrar la cantidad de usuarios conectados en ABLAOnline
     */
    public static function cantidadUsuariosConectados() {
        global $sql, $sesion_usuarioSesion;

        $tablas = array(
            "uc" => "usuarios_conectados"
        );

        $columnas = array(
            "id_usuario" => "uc.id_usuario",
            "visible" => "uc.visible"
        );

        $condicion = "uc.visible = '1' AND uc.id_usuario != '" . $sesion_usuarioSesion->id . "'";

        //$sql->depurar = true;
        $sql->seleccionar($tablas, $columnas, $condicion);
        if ($sql->filasDevueltas) {
            return $sql->filasDevueltas;
        } else {
            return "0 ";
        }
    }

    /**
     * Metodo que sen encarga de mostrar los usuarios
     * conectados en toda la red social
     */
    public static function usuariosConectados() {
        global $sql, $sesion_usuarioSesion, $textos, $configuracion;

        $tablas = array(
            "uc" => "usuarios_conectados",
            "u" => "usuarios",
            "p" => "personas",
            "i" => "imagenes"
        );

        $columnas = array(
            "id_usuario" => "uc.id_usuario",
            "visible" => "uc.visible",
            "sobrenombre" => "u.sobrenombre",
            "usuario" => "u.usuario",
            "imagen" => "i.ruta"
        );

        $condicion = "uc.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND uc.visible = '1' AND uc.id_usuario != '" . $sesion_usuarioSesion->id . "'";

        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
        if ($sql->filasDevueltas) {
            while ($contacto = $sql->filaEnObjeto($consulta)) {
                $contacto->foto = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $contacto->imagen;
                $lista[] = $contacto;
            }

            foreach ($lista as $elemento) {
                $item = HTML::enlace(HTML::imagen($elemento->foto, "flotanteIzquierda  margenDerecha miniaturaListaChat"), '/users/' . $elemento->usuario);
                $opciones = array("onClick" => "javascript:chatWith('" . $elemento->usuario . "')");
                $item .= HTML::enlace(HTML::frase($elemento->sobrenombre, "claseUsuariosConectados margenSuperior", "usuarioChat_" . $elemento->usuario), "javascript:void(0)", 'margenSuperior', "", $opciones);
                $listaContactos[] = $item;
            }

            $listaContactos = HTML::lista($listaContactos, "listaVertical listaConIconos bordeSuperiorLista", "", "");
            $codigo = HTML::contenedor($listaContactos, "contenedorChat");

            return $codigo;
        } else {
            return $textos->id("NO_HAY_USUARIOS_CONECTADOS");
        }
    }


    /**
     * Metodo que se encarga de mostrar un div con scroll con un listado de tus contactos y checkboxes para enviar un mensaje a multiples
     * contactos a la vez
     * */
    public static function mostrarChecksConMisContactos() {
        global $textos, $sql, $sesion_usuarioSesion;
        $cod = "";
        $codigo = "";

        $tablas = array(
            "c" => "contactos",
            "u" => "usuarios",
            "p" => "personas"
        );

        $columnas = array(
            "id_contacto1" => "c.id_usuario_solicitante",
            "id_contacto2" => "c.id_usuario_solicitado",
            "estado" => "c.estado",
            "usuario" => "u.usuario",
            "nombre" => "CONCAT(p.nombre, ' ', p.apellidos)",
            "id" => "u.id"
        );

        $condicion = "(u.id_persona = p.id AND c.id_usuario_solicitante = " . $sesion_usuarioSesion->id . " AND c.id_usuario_solicitado = u.id AND c.estado = '1') OR (u.id_persona = p.id AND c.id_usuario_solicitado = " . $sesion_usuarioSesion->id . " AND c.id_usuario_solicitante = u.id AND c.estado = '1')";

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $arreglo = array();

            while ($contacto = $sql->filaEnObjeto($consulta)) {
                $arreglo[] = $contacto;
            }
        }

        $cod .= HTML::campoChequeo("", "", "", "marcarTodosLosChecks") . $textos->id("SELECCIONAR_TODOS") . "<br/><br/>";

        foreach ($arreglo as $elemento) {
            $cod .= HTML::campoChequeo("datos[varios_contactos][$elemento->id]", "", "checksContactos") . $elemento->nombre . "<br>";
        }//fin del foreach 


        $codigo .= HTML::contenedor(HTML::parrafo($textos->id("SELECCIONAR_CONTACTOS"), "centrado negrilla") . "<br>" . $cod, "mostrarChecksConMisContactos", "mostrarChecksConMisContactos");

        return $codigo;
    }

}

?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Cursos
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 * */
class Curso {

    /**
     * Código interno o identificador del curso en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de cursos
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un curso específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador del curso en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador de la categoria a la cual pertenece la noticia
     * @var entero
     */
    public $idCategoria;

    /**
     * Nombre de usuario (login) del usuario creador del curso en la base de datos
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del curso
     * @var cadena
     */
    public $autor;

    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;

    /**
     * Título del curso
     * @var cadena
     */
    public $nombre;

    /**
     * Descripción del curso
     * @var cadena
     */
    public $descripcion;

    /**
     * Contenido completo del curso
     * @var cadena
     */
    public $contenido;

    /**
     * Fecha de creación del curso
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación del curso
     * @var fecha
     */
    public $fechaPublicacion;

    /**
     * Fecha de la última modificación del curso
     * @var fecha
     */
    public $fechaActualizacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de cursos
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Número de registros Activos de la lista
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * Codigos de los perfiles de usuario con los cuales es compartido un Blog
     * @var array
     */
    public $permisosPerfiles = array();

    /**
     * Número de comentarios que tiene este item 
     * @var entero
     */
    public $cantidadComentarios = NULL;

    /**
     * Número de me gusta que tiene este item 
     * @var entero
     */
    public $cantidadMeGusta = NULL;

    /**
     * Inicializar el curso
     * @param entero $id Código interno o identificador del curso en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('CURSOS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        $this->registros = $sql->obtenerValor('cursos', 'count(id)', 'id != "0"');
        
        $this->registrosActivos = $sql->obtenerValor('cursos', 'count(id)', 'activo = "1"');

        if (isset($id)) {
            $this->cargar($id);
            $this->permisosPerfiles = PermisosItem::cargarPerfiles($id, $modulo->id);
        }
    }

    /**
     * Cargar los datos de un curso
     * @param entero $id Código interno o identificador del curso en la base de datos
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (is_numeric($id) && $sql->existeItem('cursos', 'id', intval($id))) {

            $tablas = array(
                'c' => 'cursos',
                'u' => 'usuarios',
                'p' => 'personas',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id' => 'c.id',
                'idAutor' => 'c.id_usuario',
                'usuarioAutor' => 'u.usuario',
                'autor' => 'u.sobrenombre',
                'fotoAutor' => 'i.ruta',
                'nombre' => 'c.nombre',
                'descripcion' => 'c.descripcion',
                'contenido' => 'c.contenido',
                'fechaCreacion' => 'UNIX_TIMESTAMP(c.fecha_creacion)',
                'fechaPublicacion' => 'UNIX_TIMESTAMP(c.fecha_publicacion)',
                'fechaActualizacion' => 'UNIX_TIMESTAMP(c.fecha_actualizacion)',
                'idCategoria' => 'c.id_categoria',
                'activo' => 'c.activo'
            );

            $condicion = 'c.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND c.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->usuario;
                $this->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->fotoAutor;
                //sumar una visita al curso
                $this->sumarVisita();
            }
        }
    }

    /**
     * Adicionar un curso
     * @param  arreglo $datos       Datos del curso a adicionar
     * @return entero               Código interno o identificador del curso en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo;

        $datosCurso = array();

        $datosCurso['nombre'] = htmlspecialchars($datos['nombre']);
        $datosCurso['descripcion'] = htmlspecialchars($datos['descripcion']);
        $datosCurso['contenido'] = Variable::filtrarTagsInseguros($datos['contenido']);
        $datosCurso['id_categoria'] = htmlspecialchars($datos['categorias']);
        $datosCurso['id_usuario'] = $sesion_usuarioSesion->id;
        $datosCurso['fecha_creacion'] = date('Y-m-d H:i:s');
        $datosCurso['fecha_actualizacion'] = date('Y-m-d H:i:s');

        //nuevos datos que recibo sobre los perfiles con los que comparto y la visibilidad
        $datosPerfiles = $datos['perfiles'];
        $datosVisibilidad = $datos['visibilidad'];

        if (isset($datos['activo'])) {
            $datosCurso['activo'] = '1';
            $datosCurso['fecha_publicacion'] = date('Y-m-d H:i:s');
        } else {
            $datosCurso['activo'] = '0';
            $datosCurso['fecha_publicacion'] = NULL;
        }

        $consulta = $sql->insertar('cursos', $datosCurso);

        if ($consulta) {
            $idItem = $sql->ultimoId;
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;
            $permisosItem->insertarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);

            return $idItem;
        } else {
            return NULL;
        }
    }

    /**
     * Modificar un curso
     * @param  arreglo $datos       Datos de la curso a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $modulo;

        if (!isset($this->id)) {
            return NULL;
        }

        $datosCurso = array();

        $datosCurso['nombre'] = htmlspecialchars($datos['nombre']);
        $datosCurso['descripcion'] = htmlspecialchars($datos['descripcion']);
        $datosCurso['contenido'] = Variable::filtrarTagsInseguros($datos['contenido']);
        $datosCurso['id_categoria'] = htmlspecialchars($datos['categorias']);
        $datosCurso['fecha_actualizacion'] = date('Y-m-d H:i:s');
//nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfiles = $datos['perfiles'];
        $datosVisibilidad = $datos['visibilidad'];


        if (isset($datos['activo'])) {
            $datosCurso['activo'] = '1';
            $datosCurso['fecha_publicacion'] = date('Y-m-d H:i:s');
        } else {
            $datosCurso['activo'] = '0';
            $datosCurso['fecha_publicacion'] = NULL;
        }
        //$sql->depurar = true;
        $consulta = $sql->modificar('cursos', $datosCurso, 'id = "' . $this->id . '"');

        if ($consulta) {
//codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;
            $idItem = $this->id;
            $permisosItem->modificarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);

            return 1;
        } else {

            return NULL;
        }//fin del if(consulta)
    }

    /**
     * Eliminar un curso
     * @param entero $id    Código interno o identificador del curso en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        /* Elimino las imagenes que tenga el curso */
        $consultaImg = $sql->seleccionar(array('imagenes'), array('id' => 'id'), 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
        if ($sql->filasDevueltas) {
            while ($imagenes = $sql->filaEnObjeto($consultaImg)) {
                $img = new Imagen($imagenes->id);
                $img->eliminar();
            }
        }

        /* Elimino los documentos que tenga el curso */
        $consultaDoc = $sql->seleccionar(array('documentos'), array('id'), 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
        if ($sql->filasDevueltas) {
            while ($documentos = $sql->filaEnObjeto($consultaDoc)) {
                $doc = new Documento($documentos->id);
                $doc->eliminar();
            }
        }

        /* Elimino los Audios que tenga el curso */
        $audios = $sql->filaEnObjeto($sql->seleccionar(array('audios'), array('id'), 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"'));
        if ($sql->filasDevueltas) {
            foreach ($audios as $audio) {
                $aud = new Audio($audio->id);
                $aud->eliminar();
            }
        }

        /* Elimino los Videos que tenga el curso */
        $videos = $sql->filaEnObjeto($sql->seleccionar(array('videos'), array('id'), 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"'));
        if ($sql->filasDevueltas) {
            foreach ($videos as $video) {
                $vid = new Video($video->id);
                $vid->eliminar();
            }
        }

        /* Elimino los foros y los mensajes que tenga relacionado el curso */
        $consultaForo = $sql->seleccionar(array('foros'), array('id'), 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
        if ($sql->filasDevueltas) {
            while ($foros = $sql->filaEnObjeto($consultaForo)) {
                $consulta = $sql->eliminar('mensajes_foro', 'id_foro = "' . $foros->id . '"');
            }
        }
        $sql->eliminar('foros', 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');

        /* Elimino los seguidores que tenga relacionado el curso */
        $sql->eliminar('cursos_seguidos', ' id_curso= "' . $this->id . '"');


        if (!($consulta = $sql->eliminar('cursos', 'id = "' . $this->id . '"'))) {

            return false;
        } else {
            $permisosItem = new PermisosItem();
            //eliminar los permisos del item
            if (!($permisosItem->eliminar($this->id, $this->idModulo))) {
                return false;
            } else {
                return true;
            }

            return true;
        }//fin del si funciono eliminar       
    }

    /**
     * Seguir un curso
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     * */
    public function seguir() {
        global $sql, $sesion_usuarioSesion;

        if (!isset($this->id)) {
            return NULL;
        }

        $datosCurso = array();
        $datosCurso['id_curso'] = $this->id;
        $datosCurso['id_usuario'] = $sesion_usuarioSesion->id;
        $consulta = $sql->insertar('cursos_seguidos', $datosCurso);
        return $consulta;
    }

    /**
     * Abandonar un curso
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     * */
    public function abandonar() {
        global $sql, $sesion_usuarioSesion;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('cursos_seguidos', 'id_curso = "' . $this->id . '" AND id_usuario = "' . $sesion_usuarioSesion->id . '"');
        return $consulta;
    }

    /**
     * Listar los cursos
     * @param entero  $cantidad    Número de cursos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de cursos
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfil = NULL, $idModulo = NULL, $filtroCategoria = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        $lista = array();

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'c.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'c.fecha_publicacion ASC';
        } else {
            $orden = 'c.fecha_publicacion DESC';
        }

        $tablas = array(
            'c' => 'cursos',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'c.id',
            'idAutor' => 'c.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
	    'genero' => 'p.genero',
            'nombre' => 'c.nombre',
            'descripcion' => 'c.descripcion',
            'fechaCreacion' => 'UNIX_TIMESTAMP(c.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(c.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(c.fecha_actualizacion)',
            'activo' => 'c.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'c.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id';
        //filtro de categoria
        if (!empty($filtroCategoria)) {
            $filtroCategoria = htmlspecialchars($filtroCategoria);
            if ($filtroCategoria == 'my_item') {
                $condicion .= ' AND c.id_usuario = "' . $sesion_usuarioSesion->id . '"'; //filtro de categoria
            } else {
                $condicion .= ' AND c.id_categoria = "' . $filtroCategoria . '"'; //filtro de categoria
            }
        }

        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {

                //codigo para perfiles que pueden ver items de varios perfiles
                $condicion2 = "";
                $tienePrivilegios = $sql->obtenerValor('tipos_usuario', 'otros_perfiles', 'id = "' . $sesion_usuarioSesion->idTipo . '"');
                if (isset($sesion_usuarioSesion) && $tienePrivilegios) {
                    $otrosPerfiles = implode(',', Perfil::verOtrosPerfiles());
                    $condicion2 = ", $otrosPerfiles "; //condicion extra para ver items de los otros perfiles sobre los cuales tiene privilegios                  
                }

                $tablas["pi"] = "permisos_item";
                $columnas["idItem"] = "pi.id_item";
                $columnas["idPerfil"] = "pi.id_perfil";
                $columnas["idModulo"] = "pi.id_modulo";

                $condicion .= " AND pi.id_item = c.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil'$condicion2)";
                $condicion .= "OR ( c.id_usuario = '$sesion_usuarioSesion->id' AND c.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND pi.id_item = c.id AND pi.id_modulo = '" . $idModulo . "'";
                if (!empty($filtroCategoria)) {
                    if ($filtroCategoria == 'my_item') {
                        $condicion .= ' AND c.id_usuario = "' . $sesion_usuarioSesion->id . '"'; //filtro de categoria
                    } else {
                        $condicion .= ' AND c.id_categoria = "' . $filtroCategoria . '"'; //filtro de categoria
                    }
                }
                $condicion .= ')';
            }
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= ' AND pi.id_item = c.id AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" )';
        }

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'c.id', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {

            while ($curso = $sql->filaEnObjeto($consulta)) {
                $curso->url = $this->urlBase . '/' . $curso->id;
                $curso->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $curso->fotoAutor;
                $lista[] = $curso;
            }
        }

        return $lista;
    }

    /**
     * Listar los cursos que sigue el usuario que ha iniciado la sesion
     * @param entero  $cantidad    Número de cursos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de cursos
     */
    public function listarCursosQueSigo($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        $lista = array();

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'b.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'b.fecha_publicacion ASC';
        } else {
            $orden = 'b.fecha_publicacion DESC';
        }

        $tablas = array(
            'b' => 'cursos',
            'c' => 'cursos_seguidos',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'b.id',
            'idAutor' => 'b.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'nombre' => 'b.nombre',
            'descripcion' => 'b.descripcion',
            'fechaCreacion' => 'UNIX_TIMESTAMP(b.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(b.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(b.fecha_actualizacion)',
            'activo' => 'b.activo',
            'cursos_seguidos' => 'c.id_curso'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND b.id = c.id_curso AND c.id_usuario = "' . $sesion_usuarioSesion->id . '"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {

            while ($curso = $sql->filaEnObjeto($consulta)) {
                $curso->url = $this->urlBase . '/' . $curso->id;
                $curso->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $curso->fotoAutor;
                $lista[] = $curso;
            }
        }

        return $lista;
    }

    /**
     * Listar los cursos que dicta el usuario que ha iniciado la sesion
     * @param entero  $cantidad    Número de cursos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de cursos
     */
    public function listarCursosQueDicto($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $usuario = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        $lista = array();

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'b.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'b.fecha_publicacion ASC';
        } else {
            $orden = 'b.fecha_publicacion DESC';
        }

        if (isset($usuario)) {
            $usuario = $usuario;
        } else {
            $usuario = $sesion_usuarioSesion;
        }

        $tablas = array(
            'b' => 'cursos',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'b.id',
            'idAutor' => 'b.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'nombre' => 'b.nombre',
            'descripcion' => 'b.descripcion',
            'fechaCreacion' => 'UNIX_TIMESTAMP(b.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(b.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(b.fecha_actualizacion)',
            'activo' => 'b.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'b.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND b.id_usuario = "' . $usuario->id . '"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {

            while ($curso = $sql->filaEnObjeto($consulta)) {
                $curso->url = $this->urlBase . '/' . $curso->id;
                $curso->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $curso->fotoAutor;
                $lista[] = $curso;
            }
        }

        return $lista;
    }   

    /**
     * Metodo que muestra y lista los cursos que sigue el ususario que ha iniciado sesion 
     * */
    public function cursosQueSigo() {
        global $configuracion, $textos;

        $bloqueCursos = '';
        $arregloCursos = self::listarCursosQueSigo(0, 5, '', '');

        if (sizeof($arregloCursos) > 0) {

            foreach ($arregloCursos as $elemento) {
                $item = '';

                if ($elemento->activo) {

                    $persona = new Persona($elemento->idAutor);
                    $item = HTML::enlace(HTML::imagen($elemento->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $elemento->url);
                    $item .= HTML::parrafo(HTML::enlace($elemento->nombre, $elemento->url) . ' ' . HTML::frase(preg_replace('/\%1/', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)), HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $persona->idGenero . '.png') . $textos->id('CREADO_POR2')), 'flotanteCentro'));

                    $item2 = HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion), 'pequenia cursiva negrilla margenInferior');
                    $item2 .= HTML::parrafo($elemento->descripcion, 'margenInferior');

                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisL'); //barra del contenedor gris

                    $listaCursos[] = $item;
                }
            }//fin del foreach

            $listaCursos[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('CURSOS', '', '', '', 'i_follow'), 'flotanteCentro margenSuperior');
        } else {
            $listaCursos[] = $textos->id('NO_SIGUES_NINGUN_CURSO');
        }

        $bloqueCursos .= HTML::lista($listaCursos, 'listaVertical bordeSuperiorLista', 'botonesOcultos', '');

        return $bloqueCursos;
    }

    /**
     * Metodo que muestra y lista los cursos que dicta el ususario que ha iniciado sesion
     * */
    public function cursosQueDicto($usuario = NULL) {
        global $configuracion, $textos, $sesion_usuarioSesion;

        if (isset($usuario)) {
            $usuario = $usuario;
        } else {
            $usuario = $sesion_usuarioSesion;
        }

        $bloqueCursos = '';
        $arregloCursos = self::listarCursosQueDicto(0, 5, '', '', $usuario);

        if (sizeof($arregloCursos) > 0) {

            foreach ($arregloCursos as $elemento) {
                $item = '';

                if (isset($usuario) && ($usuario->idTipo == 0 || $usuario->id == $elemento->idAutor)) {
                    $botones = '';
                    $botones .= HTML::botonModificarItem($elemento->id, $this->urlBase);
                    $botones .= HTML::botonEliminarItem($elemento->id, $this->urlBase);
                    $item .= HTML::contenedor($botones, 'oculto flotanteDerecha');
                }

                if ($elemento->activo) {

                    $persona = new Persona($elemento->idAutor);
                    $item = HTML::enlace(HTML::imagen($elemento->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $elemento->url);
                    $item .= HTML::parrafo(HTML::enlace($elemento->nombre, $elemento->url) . ' ' . HTML::frase(preg_replace('/\%1/', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)), HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $persona->idGenero . '.png') . $textos->id('CREADO_POR2')), 'flotanteCentro'));

                    $item2 = HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion), 'pequenia cursiva negrilla margenInferior');
                    $item2 .= HTML::parrafo($elemento->descripcion, 'margenInferior');

                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisL'); //barra del contenedor gris

                    $listaCursos[] = $item;
                }
            }//fin del foreach

            $listaCursos[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('CURSOS', '', '', '', 'i_follow'), 'flotanteCentro margenSuperior');
        } else {
            $listaCursos[] = $textos->id('NO_DICTAS_NINGUN_CURSO');
        }

        $bloqueCursos .= HTML::lista($listaCursos, 'listaVertical bordeSuperiorLista', 'botonesOcultos', '');

        return $bloqueCursos;
    }


    /**
     * Eliminar seguidores
     * @param array datos   Códigos internos o identificadores delos seguidores en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminarSeguidores($datos) {
        global $sql;

        if (empty($datos)) {//datos me esta llegando como un string concatenado con comas
            return NULL;
        }

        $ids = explode(',', $datos);

        for ($i = 0; $i < sizeof($ids); $i++) {
            $ids[$i] = htmlspecialchars($ids[$i]);
            $consulta = $sql->eliminar('cursos_seguidos', 'id = "' . $ids[$i] . '"');
        }

        return $consulta;
    }

    public function sumarVisita() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $numVisitas = $sql->obtenerValor('cursos', 'visitas', 'id = "' . $this->id . '"');

        $datosCurso['visitas'] = $numVisitas + 1;

	$sql->guardarBitacora = false;
        $sumVisita = $sql->modificar('cursos', $datosCurso, 'id = "' . $this->id . '"');

        if ($sumVisita) {
            return true;
        } else {
            return false;
        }
    }

}


?>
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Actividades poesteadas en los cursos
 * @author      Pablo Andrés Vélez Vidal
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 COLOMBO AMERICANO
 * @version     0.1
 *
 * */
class ActividadCurso {

    /**
     * Código interno o identificador  de la actibvidad en la base de datos
     * @var entero
     */
    public $id;
    
    /**
     * URL relativa del módulo de actividades
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de una actividad específica
     * @var cadena
     */
    public $url;    

    /**
     * Identificador del curso al cual pertenece la actividad
     * @var entero
     */
    public $idCurso;

    /**
     * objeto curso al cual pertenece la actividad
     * @var cadena
     */
    public $curso;

    /**
     * Código interno o identificador del usuario propietario de la actividad
     * @var entero
     */
    public $idUsuario;

    /**
     * Titulo que lleva la actividad
     * @var entero
     */
    public $titulo;
    
     /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;    

    /**
     * Descripcion de la actividad
     * @var cadena
     */
    public $descripcion;

    /**
     * Fecha en la que se publica la actividad
     * @var cadena
     */
    public $fechaPublicacion;
    
    /**
     * Icono para mostrar en el listado de actividades
     * @var cadena
     */
    public $icono;    

    /**
     * Fecha limite que tiene la actividad para ser resuelta
     * @var cadena
     */
    public $fechaLimite;
    
    /**
     * Dias restantes para que la actividad sea resuelta
     * @var cadena
     */
    public $diasRestantes;    

    /**
     * Archivo numero 1 relacionado a la actividad
     * @var cadena
     */
    public $archivoActividad1;
    
    /**
     * enlace al archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $enlaceArchivoActividad1;    

    /**
     * ruta al Archivo numero 1 relacionado a la respuesta
     * @var cadena
     */
    public $rutaArchivoActividad1;

    /**
     * Icono que representa el tipo de archivo del Archivo numero 1 relacionado a la respuesta
     * @var cadena
     */
    public $icono1;

    /**
     * Archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $archivoActividad2;
    
    /**
     * enlace al archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $enlaceArchivoActividad2;    

    /**
     * ruta al Archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $rutaArchivoActividad2;

    /**
     * Icono que representa el tipo de archivo del Archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $icono2;
    
    
    /**
     * Representa que tipo de calificacion se usara para esta actividad
     * 1 = calificacion con palabras, ej: excellent, very good, acceptable, etc
     * 2 = calificacion tipo universidad, de 1 a 5
     * 3 = calificacion entre 0 y 100, siendo 100 el score mas alto
     * @var cadena
     */
    public $tipoCalificacion;    

    /**
     * Inicializar el usuario administrador del centro
     * @param entero $id Código interno o identificador de la actividaden la base de datos
     */
    public function __construct($id = NULL) {//recibo el id de la actividad
        
        $modulo         = new Modulo("ACTIVIDADES");
        $this->urlBase  = "/".$modulo->url;
        $this->url      = $modulo->url;
        $this->idModulo = $modulo->id;        

        if (isset($id) && $id != NULL) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos de la actividad
     * @param entero $id Código interno o identificador de la  en la base de datos
     */
    public function cargar($id = NULL) {
        global $sql, $configuracion, $textos;

        if (!empty($id) && $sql->existeItem('actividades_curso', 'id', intval($id))) {
            $this->id = $id;

            $tablas = array(
                'ac' => 'actividades_curso'
            );

            $columnas = array(
                'idCurso' => 'ac.id_curso',
                'idUsuario' => 'ac.id_usuario',
                'titulo' => 'ac.titulo',
                'descripcion' => 'ac.descripcion',
                'fechaPublicacion' => 'ac.fecha_publicacion',
                'fechaLimite' => 'ac.fecha_limite',
                'archivoActividad1' => 'ac.archivo',
                'archivoActividad2' => 'ac.archivo_2',
                'tipoCalificacion' => 'ac.tipo_calificacion',
                'diasRestantes' => 'DATEDIFF(ac.fecha_limite, NOW())'
            );

            $condicion = 'ac.id = "' . $id . '"';
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                if($this->archivoActividad1 != ''){
                    $tipo = Recursos::getTipoArchivo($this->archivoActividad1);
                    $this->icono1 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'icono_' . $tipo . '.png';
                    if ($tipo == 'video') {
                        $this->rutaArchivoActividad1 = $this->archivoActividad1;
                        $this->enlaceArchivoActividad1 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono1, 'margenIzquierda'), $this->rutaArchivoActividad1, '', '', array('rel' => 'prettyPhoto'), true);
                    } elseif ($tipo == 'imagen') {
                        $this->rutaArchivoActividad1 =  $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad1;
                        $this->enlaceArchivoActividad1 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono1, 'margenIzquierda'), $this->rutaArchivoActividad1, '', '', array("rel" => "prettyPhoto['']"), true);
                    } elseif ($tipo == 'audio'){
                        $reproductor1 = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["reproductor"]."?file=";
                        $ruta1 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad1;
                        $this->enlaceArchivoActividad1 = HTML::enlace('', $reproductor1.$ruta1, 'recursoAudio recursoAudioActividad');
                    } else {
                        $this->rutaArchivoActividad1 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad1;
                        $this->enlaceArchivoActividad1 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono1, 'margenIzquierda'), $this->rutaArchivoActividad1);
                    }                    
                    
                }

                

                if($this->archivoActividad2 != ''){
                    $tipo1 = Recursos::getTipoArchivo($this->archivoActividad2);
                    $this->icono2 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'icono_' . $tipo1 . '.png';
                    if ($tipo1 == 'video') {
                        $this->rutaArchivoActividad2 = $this->archivoActividad2;
                        $this->enlaceArchivoActividad2 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono2, 'margenIzquierda'), $this->rutaArchivoActividad2, '', '', array('rel' => 'prettyPhoto'), true);
                    } elseif ($tipo1 == 'imagen') {
                        $this->rutaArchivoActividad2 =  $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad2;
                        $this->enlaceArchivoActividad2 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono2, 'margenIzquierda'), $this->rutaArchivoActividad2, '', '', array("rel" => "prettyPhoto['']"), true);
                    } elseif ($tipo1 == 'audio'){
                        $reproductor = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["reproductor"]."?file=";
                        $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad2;
                        $this->enlaceArchivoActividad2 = HTML::enlace('', $reproductor.$ruta, 'recursoAudio recursoAudioActividad');
                    } else {
                        $this->rutaArchivoActividad2 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad2;
                        $this->enlaceArchivoActividad2 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD2').HTML::imagen($this->icono2, 'margenIzquierda'), $this->rutaArchivoActividad2);
                    }
                }
                
                
                
                $this->icono = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'activity.png';
                
                $this->curso = new Curso($this->idCurso);
                
            }
        }
    }

    /**
     *
     * Adicionar una actividad
     *
     * @param  arreglo $datos       Datos de la actividad a adicionar
     * @return entero               Código interno o identificador de la actividad en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $configuracion, $sesion_usuarioSesion, $archivo_recurso_1, $archivo_recurso_2, $textos;

        if (!isset($sesion_usuarioSesion)) {
            return NULL;
        }

        $datosActividad = array(
            'id_curso' => $datos['id_curso'],
            'id_usuario' => $sesion_usuarioSesion->id,
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => Variable::filtrarTagsInseguros($datos['descripcion']),
            'fecha_publicacion' => date('Y-m-d H:i:s'),
            'fecha_limite' => htmlspecialchars($datos['fecha_limite']),
            'archivo' => htmlspecialchars($datos['recurso_1']),
            'archivo_2' => htmlspecialchars($datos['recurso_2']),
            'tipo_calificacion' => htmlspecialchars($datos['tipo_calificacion']),
        );

        
        if (isset($archivo_recurso_1) && !empty($archivo_recurso_1['tmp_name'])) {
            
            $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["archivosActividades"];
            $formato = strtolower(substr($archivo_recurso_1["name"], strrpos($archivo_recurso_1["name"], ".") + 1));

            if (in_array($formato, array("jpg", "png", "gif", "jpeg"))) {
                $area = getimagesize($archivo_recurso_1["tmp_name"]);
                $ancho = $area[0];
                $alto = $area[1];

                while ($ancho > 800 || $alto > 600) {
                    $ancho = ($ancho * 90) / 100;
                    $alto = ($alto * 90) / 100;
                }

                $dimensiones = array($ancho, $alto, 80, 90);

                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta, $dimensiones);
                
            } elseif(in_array($formato, array("mp3", "wma", "wav", "ogg", "3gp", "3gpp"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta);
            } elseif(in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta);
            }
            
           $datosActividad['archivo'] = $recurso; 
        }
        
        if (isset($archivo_recurso_2) && !empty($archivo_recurso_2['tmp_name'])) {
            
            $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["archivosActividades"];
            $formato = strtolower(substr($archivo_recurso_2["name"], strrpos($archivo_recurso_2["name"], ".") + 1));

            if (in_array($formato, array("jpg", "png", "gif", "jpeg"))) {
                $area = getimagesize($archivo_recurso_2["tmp_name"]);
                $ancho = $area[0];
                $alto = $area[1];

                while ($ancho > 800 || $alto > 600) {
                    $ancho = ($ancho * 90) / 100;
                    $alto = ($alto * 90) / 100;
                }

                $dimensiones = array($ancho, $alto, 80, 90);

                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta, $dimensiones);
                
            } elseif(in_array($formato, array("mp3", "wma", "wav", "ogg", "3gp", "3gpp"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta);
            } elseif(in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta);
            }
            
           $datosActividad['archivo_2'] = $recurso; 
        }        

        $consulta = $sql->insertar('actividades_curso', $datosActividad);

        if ($consulta) {
            
            if (isset($datos['notificar_estudiantes'])) {//determina si se escogio notificar a los estudiantes de haber subido la imagen
                $idCurso = $datos['id_curso'];
                $objetoCurso = new Curso($idCurso);
                $consultaSeguidores = $sql->seleccionar(array('cursos_seguidos'), array('id', 'id_usuario'), 'id_curso = "' . $idCurso . '"', '');
                if ($sql->filasDevueltas) {
                    $nombreItem = $datos['titulo'];
                    while ($seguidor = $sql->filaEnObjeto($consultaSeguidores)) {

                        $notificacion1 = str_replace('%1', HTML::enlace($objetoCurso->autor, HTML::urlInterna('CURSOS', $idCurso)), $textos->id('MENSAJE_ADICION_ACTIVIDAD'));
                        $notificacion2 = str_replace('%2', HTML::enlace($nombreItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion1);
                        $notificacion3 = str_replace('%3', HTML::enlace($objetoCurso->nombre, HTML::urlInterna('CURSOS', $idCurso)), $notificacion2);
                        $notificacion = str_replace('%4', HTML::enlace($datos['fecha_limite'], HTML::urlInterna('CURSOS', $idCurso)), $notificacion3);
                        

                        Servidor::notificar($seguidor->id_usuario, $notificacion, array(), '6');
                    }
                }
            }            
            
            
            
            return $sql->ultimoId;
        } else {
            return false;
        }
    }

    /**
     * Modificar la información de una actividad
     * @param  arreglo $datos       Datos de la actividad a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $configuracion, $sesion_usuarioSesion, $archivo_recurso_1, $archivo_recurso_2;

        if (empty($this->id)) {
            return NULL;
        }
        
        $archivo1 = $this->archivoActividad1;

        if(!empty($datos['recurso_1'])){
            $archivo1 = $datos['recurso_1'];
        }
        $archivo2 = $this->archivoActividad2;
        if(!empty($datos['recurso_2'])){
            $archivo2 = $datos['recurso_2'];
        }        

        $datosActividad = array(
            'id_curso' => $datos['id_curso'],
            'id_usuario' => $sesion_usuarioSesion->id,
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => Variable::filtrarTagsInseguros($datos['descripcion']),
            'fecha_publicacion' => date('Y-m-d H:i:s'),
            'fecha_limite' => htmlspecialchars($datos['fecha_limite']),
            'archivo' => $archivo1,
            'archivo_2' => $archivo2,
            'tipo_calificacion' => htmlspecialchars($datos['tipo_calificacion']),
        );

        
        if (isset($archivo_recurso_1) && !empty($archivo_recurso_1['tmp_name'])) {
            
            $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["archivosActividades"];
            $formato = strtolower(substr($archivo_recurso_1["name"], strrpos($archivo_recurso_1["name"], ".") + 1));

            if (in_array($formato, array("jpg", "png", "gif", "jpeg"))) {
                $area = getimagesize($archivo_recurso_1["tmp_name"]);
                $ancho = $area[0];
                $alto = $area[1];

                while ($ancho > 800 || $alto > 600) {
                    $ancho = ($ancho * 90) / 100;
                    $alto = ($alto * 90) / 100;
                }

                $dimensiones = array($ancho, $alto, 80, 90);

                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta, $dimensiones);
                
            } elseif(in_array($formato, array("mp3", "wma", "wav", "ogg", "3gp", "3gpp"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta);
            } elseif(in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta);
            }
            
           $datosActividad['archivo'] = $recurso; 
        }
        
        if (isset($archivo_recurso_2) && !empty($archivo_recurso_2['tmp_name'])) {
            
            $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["archivosActividades"];
            $formato = strtolower(substr($archivo_recurso_2["name"], strrpos($archivo_recurso_2["name"], ".") + 1));

            if (in_array($formato, array("jpg", "png", "gif", "jpeg"))) {
                $area = getimagesize($archivo_recurso_2["tmp_name"]);
                $ancho = $area[0];
                $alto = $area[1];

                while ($ancho > 800 || $alto > 600) {
                    $ancho = ($ancho * 90) / 100;
                    $alto = ($alto * 90) / 100;
                }

                $dimensiones = array($ancho, $alto, 80, 90);

                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta, $dimensiones);
                
            } elseif(in_array($formato, array("mp3", "wma", "wav", "ogg", "3gp", "3gpp"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta);
            } elseif(in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta);
            }
            
           $datosActividad['archivo_2'] = $recurso; 
        }   
        
        $consulta = $sql->modificar('actividades_curso', $datosActividad, 'id = "' . $this->id . '"');

        if ($consulta) {
            return $this->id;
        } else {
            return false;
        }
    }

    /**
     * Eliminar una actividad
     * @param entero $id    Código interno o identificador de la actividad en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql, $configuracion;

        if (!isset($this->id)) {
            return NULL;
        }

        //eliminar cada uno de los archivos que tenga la  actividad
        if (Recursos::getTipoArchivo($this->archivoActividad1) != 'video') {
            $archivo1 = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad1;
            Archivo::eliminarArchivoDelServidor(array($archivo1));
        }

        if (Recursos::getTipoArchivo($this->archivoActividad2) != 'video') {
            $archivo2 = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoActividad2;
            Archivo::eliminarArchivoDelServidor(array($archivo2));
        }

        //eliminar cada una de las respuestas que haya tenido cada una de las actividades
        $tablas = array('respuestas_actividades');
        $columnas = array('id' => 'id');
        $condicion = 'id_actividad = ' . $this->id . '';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($respuesta = $sql->filaEnObjeto($consulta)) {

                $respuestaActividad = new RespuestaActividad($respuesta->id);
                $respuestaActividad->eliminar();
            }
        }


        $sql->eliminar('actividades_curso', 'id = "' . $this->id . '"');

        return $consulta;
    }

    /**
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @param int $inicio
     * @param int $cantidad
     * @param type $excepcion
     * @param type $condicion
     * @return type 
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idCurso = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'ac.id NOT IN (' . $excepcion . ') AND ';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'ac.fecha_publicacion ASC';
        } else {
            $orden = 'ac.fecha_publicacion DESC';
        }

        $tablas = array(
            'ac' => 'actividades_curso'
        );

        $columnas = array(
            'id' => 'ac.id',
            'idCurso' => 'ac.id_curso',
            'idUsuario' => 'ac.id_usuario',
            'titulo' => 'ac.titulo',
            'descripcion' => 'ac.descripcion',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(ac.fecha_publicacion)',
            'fechaLimite' => 'UNIX_TIMESTAMP(ac.fecha_limite)',
            'diasRestantes' => 'DATEDIFF(ac.fecha_limite, NOW())'
        );



        $condicion .= 'ac.id_curso = "'.$idCurso.'"';

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($actividad = $sql->filaEnObjeto($consulta)) {
                $actividad->icono = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'activity.png';
                $lista[] = $actividad;
            }
        }

        return $lista;
    }
    
    
    /**
     *
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @param type $idCurso
     * @return type 
     */
    public function contar($idCurso){
        global $sql;       
        
        $cantidad = $sql->obtenerValor('actividades_curso', 'COUNT(id)', 'id_curso = "'.$idCurso.'"');
        
        return $cantidad;
    }
    
      
    
    

}

?>
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  respuestas a las actividades poesteadas en los cursos
 * @author      Pablo Andrés Vélez Vidal
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 COLOMBO AMERICANO
 * @version     0.1
 *
 * */
class RespuestaActividad {

    /**
     * Código interno o identificador de la respuesta en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Código interno o identificador del centro binacional en la base de datos al cual pertenece usuario
     * @var entero
     */
    public $idActividad;

    /**
     * Objeto Actividad a la cual pertenece la respuesta
     * @var cadena
     */
    public $actividad;

    /**
     * Código interno o identificador del usuario que publica la respuesta
     * @var entero
     */
    public $idUsuario;
    
    /**
     * usuario que publica la respuesta
     * @var entero
     */
    public $usuario;    
    
    /**
     * imagen miniatura del usuario que publico la repuesta
     * @var entero
     */
    public $imagenAutor;      
        

    /**
     * titulo de la respuesta a la actividad
     * @var entero
     */
    public $titulo;

    /**
     * Descripcion de la respuesta a la actividad
     * @var cadena
     */
    public $descripcion;

    /**
     * Fecha en la cual se publica la respuesta de la actividad
     * @var cadena
     */
    public $fechaPublicacion;
    
    /**
     * Nota de la la respuesta de la actividad
     * @var cadena
     */
    public $nota;   
    
    /**
     * retroalimentacion de la la respuesta de la actividad
     * @var cadena
     */
    public $retroalimentacion;     

    /**
     * Archivo numero 1 relacionado a la respuesta
     * @var cadena
     */
    public $archivoRespuesta1;
    
    /**
     * enlace al archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $enlaceArchivoRespuesta1;      
    
    /**
     * ruta al Archivo numero 1 relacionado a la respuesta
     * @var cadena
     */
    public $rutaArchivoRespuesta1;    

    /**
     * Icono que representa el tipo de archivo del Archivo numero 1 relacionado a la respuesta
     * @var cadena
     */
    public $icono1;

    /**
     * Archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $archivoRespuesta2;
    
    /**
     * enlace al archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $enlaceArchivoRespuesta2;      
    
    /**
     * ruta al Archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $rutaArchivoRespuesta2;      

    /**
     * Icono que representa el tipo de archivo del Archivo numero 2 relacionado a la respuesta
     * @var cadena
     */
    public $icono2;
    
    /**
     * Icono que representa las respuestas a las actividades
     * @var cadena
     */
    public $icono;    

    /**
     * Inicializar el usuario administrador del centro
     * @param entero $id Código interno o identificador de la actividaden la base de datos
     */
    public function __construct($id = NULL) {//recibo el id de la actividad
        global $sql, $configuracion;

        if (!empty($id) && !$sql->existeItem('respuestas_actividades', 'id', $id)) {
            return NULL;
        }

        $this->icono = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'response_activity2.png';
        if (isset($id) && $id != NULL) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos de la actividad
     * @param entero $id Código interno o identificador de la  en la base de datos
     */
    public function cargar($id = NULL) {
        global $sql, $configuracion, $textos;

        if (!empty($id) && $sql->existeItem('respuestas_actividades', 'id', intval($id))) {
            $this->id = $id;

            $tablas = array(
                'ra' => 'respuestas_actividades',
                'u' => 'usuarios',
                'p' => 'personas',
                'i' => 'imagenes'
            );

            $columnas = array(
                'idActividad' => 'ra.id_actividad',
                'idUsuario' => 'ra.id_usuario',
                'usuario'   => 'u.usuario',
                'imagenAutor' => 'i.ruta',
                'titulo' => 'ra.titulo',
                'descripcion' => 'ra.descripcion',
                'fechaPublicacion' => 'UNIX_TIMESTAMP(ra.fecha_publicacion)',
                'archivoRespuesta1' => 'ra.archivo',
                'archivoRespuesta2' => 'ra.archivo_2',
                'nota'              => 'ra.nota',
                'retroalimentacion' => 'ra.retroalimentacion'
            );

            $condicion = 'ra.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND ra.id = "' . $id . '"';
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $tipo = Recursos::getTipoArchivo($this->archivoRespuesta1);
                $this->icono1 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'icono_' . $tipo . '.png';
                if ($tipo == 'video') {
                    $this->rutaArchivoRespuesta1 = $this->archivoRespuesta1;
                    $this->enlaceArchivoRespuesta1 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono1, 'margenIzquierda'), $this->rutaArchivoRespuesta1, '', '', array('rel' => 'prettyPhoto'), true);
                } elseif ($tipo == 'imagen') {
                    $this->rutaArchivoRespuesta1 =  $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta1;
                    $this->enlaceArchivoRespuesta1 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono1, 'margenIzquierda'), $this->rutaArchivoRespuesta1, '', '', array("rel" => "prettyPhoto['']"), true);
                } elseif ($tipo == 'audio'){
                    $reproductor1 = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["reproductor"]."?file=";
                    $ruta1 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta1;
                    $this->enlaceArchivoRespuesta1 = HTML::enlace('', $reproductor1.$ruta1, 'recursoAudio recursoAudioActividad');
                } else {
                    $this->rutaArchivoRespuesta1 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta1;
                    $this->enlaceArchivoRespuesta1 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono1, 'margenIzquierda'), $this->rutaArchivoRespuesta1);
                }
                


                $tipo1 = Recursos::getTipoArchivo($this->archivoRespuesta2);
                $this->icono2 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'icono_' . $tipo1 . '.png';
                if ($tipo1 == 'video') {
                    $this->rutaArchivoRespuesta2 = $this->archivoRespuesta2;
                    $this->enlaceArchivoRespuesta2 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono2, 'margenIzquierda'), $this->rutaArchivoRespuesta2, '', '', array('rel' => 'prettyPhoto'), true);
                } elseif ($tipo1 == 'imagen') {
                    $this->rutaArchivoRespuesta2 =  $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta2;
                    $this->enlaceArchivoRespuesta2 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD1').HTML::imagen($this->icono2, 'margenIzquierda'), $this->rutaArchivoRespuesta2, '', '', array("rel" => "prettyPhoto['']"), true);
                } elseif ($tipo1 == 'audio'){
                    $reproductor = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["reproductor"]."?file=";
                    $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta2;
                    $this->enlaceArchivoRespuesta2 = HTML::enlace('', $reproductor.$ruta, 'recursoAudio recursoAudioActividad');
                } else {
                    $this->rutaArchivoRespuesta2 = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta2;
                    $this->enlaceArchivoRespuesta2 = HTML::enlace($textos->id('ARCHIVO_ACTIVIDAD2').HTML::imagen($this->icono2, 'margenIzquierda'), $this->rutaArchivoRespuesta2);
                }
                
                $this->actividad = new ActividadCurso($this->idActividad);
                
                $this->imagenAutor = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesMiniaturas"]."/".$this->imagenAutor;
                
                
            }
        }
    }

    /**
     *
     * Adicionar una respuesta a una actividad
     *
     * @param  arreglo $datos       Datos de la respuesta a adicionar
     * @return entero               Código interno o identificador de la respuesta en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $configuracion, $textos, $sesion_usuarioSesion, $archivo_recurso_1, $archivo_recurso_2;
        
        if( !isset($sesion_usuarioSesion) ){
            return NULL;
        }

        $datosRespuestaActividad = array(
            'id_actividad' => $datos['id_actividad'],
            'id_usuario' => $sesion_usuarioSesion->id,
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => Variable::filtrarTagsInseguros($datos['descripcion']),
            'fecha_publicacion' => date('Y-m-d H:i:s'),
            'archivo' => htmlspecialchars($datos['recurso_1']),
            'archivo_2' => htmlspecialchars($datos['recurso_2']),
        );

        
       
        if (isset($archivo_recurso_1) && !empty($archivo_recurso_1['tmp_name'])) {
            
            $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["archivosActividades"];
            $formato = strtolower(substr($archivo_recurso_1["name"], strrpos($archivo_recurso_1["name"], ".") + 1));

            if (in_array($formato, array("jpg", "png", "gif", "jpeg"))) {
                $area = getimagesize($archivo_recurso_1["tmp_name"]);
                $ancho = $area[0];
                $alto = $area[1];

                while ($ancho > 800 || $alto > 600) {
                    $ancho = ($ancho * 90) / 100;
                    $alto = ($alto * 90) / 100;
                }

                $dimensiones = array($ancho, $alto, 80, 90);

                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta, $dimensiones);
                
            } elseif(in_array($formato, array("mp3", "wma", "wav", "ogg", "3gp", "3gpp"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta);
            } elseif(in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta);
            }
            
           $datosRespuestaActividad['archivo'] = $recurso; 
        }
        
        if (isset($archivo_recurso_2) && !empty($archivo_recurso_2['tmp_name'])) {
            
            $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["archivosActividades"];
            $formato = strtolower(substr($archivo_recurso_2["name"], strrpos($archivo_recurso_2["name"], ".") + 1));

            if (in_array($formato, array("jpg", "png", "gif", "jpeg"))) {
                $area = getimagesize($archivo_recurso_2["tmp_name"]);
                $ancho = $area[0];
                $alto = $area[1];

                while ($ancho > 800 || $alto > 600) {
                    $ancho = ($ancho * 90) / 100;
                    $alto = ($alto * 90) / 100;
                }

                $dimensiones = array($ancho, $alto, 80, 90);

                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta, $dimensiones);
                
            } elseif(in_array($formato, array("mp3", "wma", "wav", "ogg", "3gp", "3gpp"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta);
            } elseif(in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta);
            }
            
           $datosRespuestaActividad['archivo_2'] = $recurso; 
        }        

        
        $consulta = $sql->insertar('respuestas_actividades', $datosRespuestaActividad);

        if ($consulta) {
            
                $idActividad = $datos['id_actividad'];
                $objeto = new ActividadCurso($idActividad);

                $notificacion1 = str_replace('%1', HTML::enlace($sesion_usuarioSesion->persona->nombreCompleto, HTML::urlInterna('CURSOS', $objeto->curso->id)), $textos->id('MENSAJE_RESPUESTA_ACTIVIDAD'));
                $notificacion = str_replace('%2', HTML::enlace($objeto->titulo, HTML::urlInterna('CURSOS', $objeto->curso->id)), $notificacion1);

                Servidor::notificar($objeto->curso->idAutor, $notificacion, array(), '7');
            
            
            return $sql->ultimoId;
            
        } else {
            return false;
        }
    }

    /**
     * Modificar la información de una actividad
     * @param  arreglo $datos       Datos de la actividad a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $configuracion, $sesion_usuarioSesion, $archivo_recurso_1, $archivo_recurso_2;

        if (empty($this->id)) {
            return NULL;
        }
        
        $archivo1 = $this->archivoRespuesta1;
        if(isset($datos['recurso_1'])){
            $archivo1 = $datos['recurso_1'];
        }
        $archivo2 = $this->archivoRespuesta2;
        if(isset($datos['recurso_2'])){
            $archivo2 = $datos['recurso_2'];
        }

        $datosRespuestaActividad = array(
            'id_actividad' => $datos['id_actividad'],
            'id_usuario' => $sesion_usuarioSesion->id,
            'titulo' => htmlspecialchars($datos['titulo']),
            'descripcion' => Variable::filtrarTagsInseguros($datos['descripcion']),
            'fecha_publicacion' => date('Y-m-d H:i:s'),
            'archivo' => $archivo1,
            'archivo_2' => $archivo2,
        );


        if (isset($archivo_recurso_1) && !empty($archivo_recurso_1['tmp_name'])) {
            
            $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["archivosActividades"];
            $formato = strtolower(substr($archivo_recurso_1["name"], strrpos($archivo_recurso_1["name"], ".") + 1));

            if (in_array($formato, array("jpg", "png", "gif", "jpeg"))) {
                $area = getimagesize($archivo_recurso_1["tmp_name"]);
                $ancho = $area[0];
                $alto = $area[1];

                while ($ancho > 800 || $alto > 600) {
                    $ancho = ($ancho * 90) / 100;
                    $alto = ($alto * 90) / 100;
                }

                $dimensiones = array($ancho, $alto, 80, 90);

                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta, $dimensiones);
                
            } elseif(in_array($formato, array("mp3", "wma", "wav", "ogg", "3gp", "3gpp"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta);
            } elseif(in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_1, $ruta);
            }
            
           $datosRespuestaActividad['archivo'] = $recurso; 
        }
        
        if (isset($archivo_recurso_2) && !empty($archivo_recurso_2['tmp_name'])) {
            
            $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["archivosActividades"];
            $formato = strtolower(substr($archivo_recurso_2["name"], strrpos($archivo_recurso_2["name"], ".") + 1));

            if (in_array($formato, array("jpg", "png", "gif", "jpeg"))) {
                $area = getimagesize($archivo_recurso_2["tmp_name"]);
                $ancho = $area[0];
                $alto = $area[1];

                while ($ancho > 800 || $alto > 600) {
                    $ancho = ($ancho * 90) / 100;
                    $alto = ($alto * 90) / 100;
                }

                $dimensiones = array($ancho, $alto, 80, 90);

                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta, $dimensiones);
                
            } elseif(in_array($formato, array("mp3", "wma", "wav", "ogg", "3gp", "3gpp"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta);
            } elseif(in_array($formato, array("pdf", "doc", "odt", "xls", "ods", "ppt", "pps", "odp", "docx", "xlsx", "pptx", "txt"))){
                $recurso = Archivo::subirArchivoAlServidor($archivo_recurso_2, $ruta);
            }
            
           $datosRespuestaActividad['archivo_2'] = $recurso; 
        }        

        
        $consulta = $sql->modificar('respuestas_actividades', $datosRespuestaActividad, 'id = "' . $this->id . '"');

        if ($consulta) {
            return $this->id;
        } else {
            return false;
        }
    }

    /**
     * Eliminar una actividad
     * @param entero $id    Código interno o identificador de la actividad en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql, $configuracion;

        if (!isset($this->id)) {
            return NULL;
        }

        //eliminar cada uno de los archivos que tenga la  actividad
        if (Recursos::getTipoArchivo($this->archivoRespuesta1) != 'video') {
            $archivo1 = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta1;
            Archivo::eliminarArchivoDelServidor(array($archivo1));
        }

        if (Recursos::getTipoArchivo($this->archivoRespuesta2) != 'video') {
            $archivo2 = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['archivosActividades'] . '/' . $this->archivoRespuesta2;
            Archivo::eliminarArchivoDelServidor(array($archivo2));
        }     

        $consulta1 = $sql->eliminar('respuestas_actividades', 'id = "' . $this->id . '"');

        return $consulta1;
    }

    /**
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @param int $inicio
     * @param int $cantidad
     * @param type $excepcion
     * @param type $condicion
     * @return type 
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'ra.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'ra.fecha_publicacion ASC';
        } else {
            $orden = 'ra.fecha_publicacion DESC';
        }

        $tablas = array(
            'ra' => 'respuestas_actividades',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id'    => 'ra.id',
            'idActividad' => 'ra.id_actividad',
            'idUsuario' => 'ra.id_usuario',
            'usuario'   => 'u.usuario',
            'titulo' => 'ra.titulo',
            'nota' => 'ra.nota',            
            'descripcion' => 'ra.descripcion',
            'fechaPublicacion' => 'ra.fecha_publicacion',
            'imagenAutor' => 'i.ruta',
            'nombreAutor' => 'CONCAT(p.nombre, " ", p.apellidos)'
        );



        $condicion .= 'ra.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND ra.id != 0';

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($respuesta = $sql->filaEnObjeto($consulta)) {
                $respuesta->imagenAutor = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesMiniaturas"]."/".$respuesta->imagenAutor;
                $respuesta->actividad = new ActividadCurso($respuesta->idActividad);
                $lista[] = $respuesta;
            }
        }

        return $lista;
    }
    
    
    
     /**
     * Modificar la información de una respuesta para agregar la calificacion a dicha respuesta a la actividad
     * @param  arreglo $datos       Datos de la calificacion y retroaliemtacion a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificarCalificacion($datos) {
        global $sql, $textos;

        if (empty($this->id)) {
            return NULL;
        }
        
//        print_r($datos);

        $datosCalificacion = array(
            'nota' => $datos['nota'],
            'retroalimentacion' => Variable::filtrarTagsInseguros($datos['retroalimentacion']),
            'fecha_publicacion_nota' => date('Y-m-d H:i:s')
        );
        $sql->depurar = true;
        $consulta = $sql->modificar('respuestas_actividades', $datosCalificacion, 'id = "' . $this->id . '"');

        if ($consulta) {
            
                $notificacion1 = str_replace('%1', HTML::enlace($this->actividad->curso->usuarioAutor, HTML::urlInterna('CURSOS', $this->actividad->curso->id)), $textos->id('MENSAJE_CALIFICACION_RESPUESTA'));
                $notificacion2 = str_replace('%2', HTML::enlace($this->actividad->titulo, HTML::urlInterna('CURSOS', $this->actividad->curso->id)), $notificacion1);
                $notificacion3 = str_replace('%3', HTML::enlace($this->actividad->curso->nombre, HTML::urlInterna('CURSOS', $this->actividad->curso->id)), $notificacion2);
                $notificacion = str_replace('%4', HTML::frase($textos->id('VER_CALIFICACION'), 'estiloEnlace subtitulo consultarCalificacion', '', array('idRespuesta' => $this->id, 'ruta' => '/ajax/activities/seeGrade')), $notificacion3);

                Servidor::notificar($this->idUsuario, $notificacion, array(), '8');            
            return $this->id;
        } else {
            return false;
        }
    }
    
    
    /**
     *
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @param type $idCurso
     * @return type 
     */
    public function contar($idActividad){
        global $sql;       
        
        $cantidad = $sql->obtenerValor('respuestas_actividades', 'COUNT(id)', 'id_actividad = "'.$idActividad.'"');
        
        return $cantidad;
    }    
    
    

}

?>
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
     * Modificar un enlace
     *
     * @param  arreglo $datos       Datos del enlace a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
// public function modificar($datos) {
//        global $sql, $sesion_usuarioSesion,  $modulo;
//
//        if (!isset($this->id)) {
//            return NULL;
//        }
//
//         //nuevos datos que recibo sobre los perfiles con los que comparto y la visibilidad
//       $datosPerfiles                      =  $datos["perfiles"];
//       $datosVisibilidad                   =  $datos["visibilidad"]; 
//       
//       
//        if (isset($datos["activo"])) {
//            $datosRecurso["activo"] = "1";
//            $datosRecurso["fecha"] = date("Y-m-d H:i:s");
//        } else {
//            $datosRecurso["activo"] = "0";
//            $datosRecurso["fecha"] = NULL;
//        }
//
//        $datosRecurso = array(
//            "id_modulo"   => $datos["idModulo"],
//            "id_registro" => $datos["idRegistro"],
//            "id_usuario"  => $sesion_usuarioSesion->id,
//            "titulo"      => $datos["titulo"],
//            "descripcion" => $datos["descripcion"],
//            "enlace"      => $datos["enlace"]
//        );
//        //$sql->depurar = true;
//        $consulta = $sql->modificar("enlaces", $datosRecurso, "id = '".$this->id."'");
//
//
//     if($consulta){
// //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
//             $permisosItem    =  new PermisosItem();
//             $idModulo        =  $modulo->id;
//             $idItem          =  $this->id;
//
//             $permisosItem->modificarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);
//
//           return 1;  
//
//      }else{
//
//     return NULL;
//
//    }//fin del if(consulta)
//
//
// }//fin del metodo Modificar
    
    
    
    
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
?>
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Evento
 * @author      Pablo A. Vélez <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 *
 * */
class Evento {

    /**
     * Código interno o identificador del evento en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de eventos
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un evento específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador del evento en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Nombre unico del usuario(nombre login) creador del evento
     * @var String
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del evento
     * @var cadena
     */
    public $autor;

    /**
     * 
     * Identificador de la imagen Miniatura del autor
     */
    public $idImagenAutor;

    /**
     * Ruta imagen autor
     */
    public $imagenAutor;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Título del evento
     * @var cadena
     */
    public $titulo;

    /**
     * Resumen corto del evento
     * @var cadena
     */
    public $resumen;

    /**
     * Descripcion completa del evento
     * @var cadena
     */
    public $descripcion;

    /**
     * Código interno o identificador de la ciudad donde se realizara el evento
     * @var entero
     */
    public $idCiudad;

    /**
     * Nombre de la ciudad que esta directamente relacionado con el evento
     * @var string
     */
    public $ciudad;

    /**
     * Código interno o identificador del estado donde se realizara el evento
     * @var entero
     */
    public $idEstado;

    /**
     * Nombre del estado que esta directamente relacionado con el evento
     * @var string
     */
    public $estado;

    /**
     * Código interno o identificador del estado donde se realizara el evento
     * @var entero
     */
    public $idPais;

    /**
     * Nombre del estado que esta directamente relacionado con el evento
     * @var string
     */
    public $pais;

    /**
     * Código interno o identificador del centro donde se realizara el evento
     * @var entero
     */
    public $idCentro;

    /**
     * Nombre del centro que esta directamente relacionado con el evento
     * @var string
     */
    public $centro;

    /**
     * Nombre del centro que esta directamente relacionado con el evento
     * @var string
     */
    public $ciudadCentro;

    /**
     * Código interno o identificador de la categoria a la cual pertenece el evento
     * @var entero
     */
    public $idCategoria;

    /**
     * Lugar donde se realizara el evento
     * @var cadena
     */
    public $lugar;

    /**
     * Código interno o identificador en la base de datos de la imagen relacionada con el evento
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen del evento en tamaño normal
     * @var cadena
     */
    public $imagenPrincipal;

    /**
     * Ruta de la imagen del evento en miniatura
     * @var cadena
     */
    public $imagenMiniatura;

    /**
     * Ruta del icono de la bandera del pais donde se realizara el evento
     * @var cadena
     */
    public $iconoBandera;

    /**
     * Codigo iso del pais donde se realizara el evento
     * @var cadena
     */
    public $codigoIsoPais;

    /**
     * Fecha de Inicio del evento
     * @var fecha
     */
    public $fechaInicio;

    /**
     * Hora de inicio del evento
     * @var fecha
     */
    public $horaInicio;

    /**
     * Fecha de finalizacion del evento
     * @var fecha
     */
    public $fechaFin;

    /**
     * Hora de fin del evento
     * @var fecha
     */
    public $horaFin;

    /**
     * Fecha de creación del Registro
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación del evento
     * @var fecha
     */
    public $fechaActivacion;

    /**
     * Fecha de la última modificación del evento
     * @var fecha
     */
    public $fechaInactivacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Informacion de contacto donde se dará mas informacion sobre el evento
     * @var String
     */
    public $infoContacto;

    /**
     * Indicador del orden cronológio de la lista de eventos
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Número de registros activos de la lista
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * Codigos de los perfiles de usuario con los cuales es compara la informacion de un respectivo evento
     * @var array
     */
    public $permisosPerfiles = array();

    /**
     * Número de comentarios que tiene este item 
     * @var entero
     */
    public $cantidadComentarios = NULL;

    /**
     * Número de me gusta que tiene este item 
     * @var entero
     */
    public $cantidadMeGusta = NULL;

    /**
     * Número de me galerias fotograficas que tiene este item
     * @var entero
     */
    public $cantidadGalerias = NULL;

    /**
     *
     * Inicializar un evento
     *
     * @param entero $id Código interno o identificador del evento en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('EVENTOS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        $this->registros = $sql->obtenerValor('eventos', 'COUNT(id)', '');
        $this->registrosActivos = $sql->obtenerValor('eventos', 'COUNT(id)', 'activo = "1"');


        if (isset($id)) {
            $this->cargar($id);
            $this->permisosPerfiles = PermisosItem::cargarPerfiles($id, $modulo->id);
            //Saber la cantidad de comentarios que tiene este blog
            $this->cantidadComentarios = $sql->obtenerValor('comentarios', 'COUNT(id)', 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
            
            //Saber la cantidad de me Gusta que tiene este blog
            $this->cantidadMeGusta = $sql->obtenerValor('destacados', 'COUNT(*)', 'id_modulo = "' . $this->idModulo . '" AND id_item = "' . $this->id . '"');
            

            //Saber la cantidad de galerias que tiene este blog
            $this->cantidadGalerias = $sql->obtenerValor('galerias', 'COUNT(id)', 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
            
        }
    }

    /**
     *
     * Cargar los datos de un evento
     *
     * @param entero $id Código interno o identificador del evento en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem('eventos', 'id', intval($id))) {

            $tablas = array(
                'e' => 'eventos',
                'u' => 'usuarios',
                'p' => 'personas',
                'ce' => 'centros',
                'c' => 'ciudades',
                'c2' => 'ciudades',
                'es' => 'estados',
                'pa' => 'paises',
                'i' => 'imagenes',
                'i1' => 'imagenes'
            );

            $columnas = array(
                'id' => 'e.id',
                'idAutor' => 'e.id_usuario',
                'usuarioAutor' => 'u.usuario',
                'autor' => 'u.sobrenombre',
                'titulo' => 'e.titulo',
                'resumen' => 'e.resumen',
                'descripcion' => 'e.descripcion',
                'idCiudad' => 'e.id_ciudad',
                'ciudad' => 'c.nombre',
                'idEstado' => 'es.id',
                'estado' => 'es.nombre',
                'idPais' => 'pa.id',
                'codigoIsoPais' => 'pa.codigo_iso',
                'pais' => 'pa.nombre',
                'idCentro' => 'e.id_centro',
                'centro' => 'ce.nombre',
                'idCiudadCentro' => 'ce.id_ciudad',
                'ciudadCentro' => 'c2.nombre',
                'idCategoria' => 'e.id_categoria',
                'lugar' => 'e.lugar',
                'idImagen' => 'e.id_imagen',
                'imagen' => 'i.ruta',
                'fechaInicio' => 'e.fecha_inicio',
                'horaInicio' => 'e.hora_inicio',
                'idImagenAutor' => 'i1.id',
                'imagenAutor' => 'i1.ruta',
                'fechaFin' => 'e.fecha_fin',
                'horaFin' => 'e.hora_fin',
                'fechaCreacion' => 'UNIX_TIMESTAMP(e.fecha_creacion)',
                'fechaActivacion' => 'UNIX_TIMESTAMP(e.fecha_activacion)',
                'fechaInactivacion' => 'UNIX_TIMESTAMP(e.fecha_inactivacion)',
                'activo' => 'e.activo',
                'infoContacto' => 'e.info_contacto'
            );

            $condicion = 'e.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i1.id AND e.id_imagen = i.id AND e.id_ciudad = c.id AND es.id = c.id_estado AND pa.id = es.id_pais AND e.id_centro = ce.id AND ce.id_ciudad = c2.id AND e.id = "'.$id.'"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->id;
                $this->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $this->imagen;
                $this->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->imagen;
                $this->imagenAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->imagenAutor;
                $this->iconoBandera = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['iconosBanderas'] . '/' . strtolower($this->codigoIsoPais) . '.png';
                //sumar una visita al evento
                //$this->sumarVisita();
            }
        }
    }

    /**
     *
     * Adicionar un evento
     *
     * @param  arreglo $datos       Datos del evento a adicionar
     * @return entero               Código interno o identificador del evento en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo, $archivo_imagen;

        //nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfiles = $datos['perfiles'];
        $datosVisibilidad = $datos['visibilidad'];


        $idImagen = '8';

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            $imagen = new Imagen();
            $datosImagen = array(
                'modulo' => 'EVENTOS',
                'idRegistro' => '',
                'titulo' => 'imagen_evento',
                'descripcion' => 'imagen_evento'
            );

            $idImagen = $imagen->adicionar($datosImagen);
        }

        $idCiudad = $sql->obtenerValor('lista_ciudades', 'id', 'cadena = "' . utf8_decode($datos['ciudad']) . '"');
        $idCentro = $sql->obtenerValor('lista_centros', 'id', 'nombre = "' . utf8_decode($datos['centro']) . '"');


        $datosEvento = array(
            'id_usuario' => $sesion_usuarioSesion->id,
            'titulo' => htmlspecialchars($datos['titulo']),
            'resumen' => htmlspecialchars($datos['resumen']),
            'descripcion' => Variable::filtrarTagsInseguros($datos['descripcion']),
            'id_ciudad' => $idCiudad,
            'id_centro' => $idCentro,
            'id_categoria' => htmlspecialchars($datos['categorias']),
            'lugar' => htmlspecialchars($datos['lugar']),
            'id_imagen' => $idImagen,
            'fecha_inicio' => htmlspecialchars($datos['fecha_inicio']),
            'hora_inicio' => htmlspecialchars($datos['hora_inicio']),
            'fecha_fin' => htmlspecialchars($datos['fecha_fin']),
            'hora_fin' => htmlspecialchars($datos['hora_fin']),
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'info_contacto' => htmlspecialchars($datos['info_contacto'])
        );



        if (isset($datos['activo'])) {
            $datosEvento['activo'] = '1';
            $datosEvento['fecha_activacion'] = date('Y-m-d H:i:s');
            //Recursos::escribirTxt('si llegue aqui: '.date('Y-m-d H:i:s'));
        } else {
            $datosEvento['activo'] = '0';
            $datosEvento['fecha_activacion'] = NULL;
        }


        $consulta = $sql->insertar('eventos', $datosEvento);
        $idItem = $sql->ultimoId;

        if ($consulta) {
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;

            if ($datos['cantCampoImagenGaleria']) {//si viene alguna imagen se crea la galeria             
                $galeria = new Galeria();
                $datos['id_modulo_actual'] = $this->idModulo;
                $datos['id_registro_actual'] = $idItem;
                $galeria->adicionar($datos);
            }

            $permisosItem->insertarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);

            return $idItem;
        } else {
            return FALSE;
        }
    }

    /**
     *
     * Modificar un evento
     *
     * @param  arreglo $datos       Datos del evento a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
        }


        //nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfiles = $datos['perfiles'];
        $datosVisibilidad = $datos['visibilidad'];


        $idImagen = $this->idImagen;

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            $imagen = new Imagen($this->idImagen);
            $imagen->eliminar();
            $datosImagen = array(
                'modulo' => 'EVENTOS',
                'idRegistro' => '',
                'titulo' => 'imagen_evento',
                'descripcion' => 'imagen_evento'
            );

            $idImagen = $imagen->adicionar($datosImagen);
        }

        $idCiudad = $sql->obtenerValor('lista_ciudades', 'id', 'cadena = "' . utf8_decode($datos['ciudad']) . '"');
        $idCentro = $sql->obtenerValor('lista_centros', 'id', 'nombre = "' . utf8_decode($datos['centro']) . '"');
        //Recursos::escribirTxt('si llegue'.$datos['centro']);

        $datosEvento = array(
            'id_usuario' => $sesion_usuarioSesion->id,
            'titulo' => htmlspecialchars($datos['titulo']),
            'resumen' => htmlspecialchars($datos['resumen']),
            'descripcion' => $datos['descripcion'],
            'id_ciudad' => $idCiudad,
            'id_centro' => $idCentro,
            'id_categoria' => htmlspecialchars($datos['categorias']),
            'lugar' => htmlspecialchars($datos['lugar']),
            'id_imagen' => $idImagen,
            'fecha_inicio' => htmlspecialchars($datos['fecha_inicio']),
            'hora_inicio' => htmlspecialchars($datos['hora_inicio']),
            'fecha_fin' => htmlspecialchars($datos['fecha_fin']),
            'hora_fin' => htmlspecialchars($datos['hora_fin']),
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'info_contacto' => htmlspecialchars($datos['info_contacto'])
        );

        if (isset($datos['activo'])) {
            $datosEvento['activo'] = '1';
            $datosEvento['fecha_activacion'] = date('Y-m-d H:i:s');
            // Recursos::escribirTxt('si llegue aqui: '.date('Y-m-d H:i:s'));
        } else {
            $datosEvento['activo'] = '0';
            $datosEvento['fecha_inactivacion'] = NULL;
        }

        $consulta = $sql->modificar('eventos', $datosEvento, 'id = "' . $this->id . '"');

        if ($consulta) {
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el Eventos
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;
            $idItem = $this->id;

            $permisosItem->modificarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);

            return $consulta;
        } else {
            return FALSE;
        }
    }

    /**
     * Eliminar un evento
     * @param entero $id    Código interno o identificador del evento en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        /* Eliminar todos los comentarios que pueda tener el evento */
        if ($this->cantidadComentarios > 0) {
            $comentario = new Comentario();
            $comentario->eliminarComentarios($this->id, $this->idModulo);
        }
        /* Eliminar todos los 'me gusta' que pueda tener el evento */
        if ($this->cantidadMeGusta > 0) {
            $destacado = new Destacado();
            $destacado->eliminarTodosDestacados($this->idModulo, $this->id);
        }

//        $permisosItem = new PermisosItem();
//        if (!($permisosItem->eliminar($this->id, $this->idModulo))) {
//            return false;
//        } else {
//            return true;
//        }

        $imagen = new Imagen($this->idImagen);
        $imagen->eliminar();

        $consulta = $sql->eliminar('eventos', 'id = "' . $this->id . '"');

        if ($consulta) {
            return $consulta;
        } else {
            return false;
        }
    }


    /**
     * Listar los eventos
     * @param entero  $cantidad    Número de eventos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de eventos
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfil = NULL, $idModulo=NULL, $filtroCategoria = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'e.id NOT IN ('.$excepcion.')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'e.fecha_inicio DESC';
        } else {
            $orden = 'e.fecha_inicio DESC';
        }

      
        $tablas = array(
            'e' => 'eventos',
            'u' => 'usuarios',
            'ce' => 'centros',
            'c' => 'ciudades',
            'es' => 'estados',
            'pa' => 'paises',
            'c2' => 'ciudades'
        );

        $columnas = array(
            'id' => 'e.id',
            'idAutor' => 'e.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'titulo' => 'e.titulo',
            'resumen' => 'e.resumen',
            'descripcion' => 'e.descripcion',
            'idCiudad' => 'e.id_ciudad',
            'ciudad' => 'c.nombre',
            'idEstado' => 'es.id',
            'estado' => 'es.nombre',
            'idPais' => 'pa.id',
            'pais' => 'pa.nombre',
            'codigoIsoPais' => 'pa.codigo_iso',
            'idCentro' => 'e.id_centro',
            'centro' => 'ce.nombre',
            'idCiudadCentro' => 'ce.id_ciudad',
            'ciudadCentro' => 'c2.nombre',
            'idCategoria' => 'e.id_categoria',
            'lugar' => 'e.lugar',
            'fechaInicio' => 'UNIX_TIMESTAMP(e.fecha_inicio)',
            'horaInicio' => 'e.hora_inicio',
            'fechaFin' => 'UNIX_TIMESTAMP(e.fecha_fin)',
            'horaFin' => 'e.hora_fin',
            'fechaCreacion' => 'UNIX_TIMESTAMP(e.fecha_creacion)',
            'fechaActivacion' => 'UNIX_TIMESTAMP(e.fecha_activacion)',
            'fechaInactivacion' => 'UNIX_TIMESTAMP(e.fecha_inactivacion)',
            'activo' => 'e.activo',
            'infoContacto' => 'e.info_contacto'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'e.id_usuario = u.id  AND e.id_ciudad = c.id AND  c.id_estado = es.id AND es.id_pais = pa.id AND e.id_centro = ce.id AND ce.id_ciudad = c2.id';

        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {

                //codigo para perfiles que pueden ver items de varios perfiles
                $condicion2 = "";
                $tienePrivilegios = $sql->obtenerValor("tipos_usuario", "otros_perfiles", "id = '" . $sesion_usuarioSesion->idTipo . "'");
                if (isset($sesion_usuarioSesion) && $tienePrivilegios) {
 
                    $otrosPerfiles = implode(",", Perfil::verOtrosPerfiles());
                    $condicion2 = ", $otrosPerfiles "; //condicion extra para ver items de los otros perfiles sobre los cuales tiene privilegios                  
                }

                if (!empty($filtroCategoria)) {
                    if ($filtroCategoria == "my_item") {
                        $condicion .= " AND e.id_usuario = '" . $sesion_usuarioSesion->id . "'"; //filtro de categoria
                    } elseif ($filtroCategoria == "past_events") {
                        $condicion .= " AND e.fecha_fin <= NOW()"; //filtro de categoria
                    } else {
                        $condicion .= " AND e.id_categoria = '" . $filtroCategoria . "'"; //filtro de categoria
                    }
                }

                $condicionA = $condicion; //hasta aqui llega la condicion sin haber realizado lo de los perfiles

                $tablas["pi"] = "permisos_item";
                $columnas["idItem"] = "pi.id_item";
                $columnas["idPerfil"] = "pi.id_perfil";
                $columnas["idModulo"] = "pi.id_modulo";

                $condicion .= " AND ( ( pi.id_item = e.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil $condicion2') )";


//                $condicion = "(" . $condicion . ")";
                $condicion .= " OR ( $condicionA AND e.id_usuario = '$sesion_usuarioSesion->id'";


//                if (!empty($filtroCategoria)) {
//                    $condicion .= " AND e.id_categoria = '" . $filtroCategoria . "'"; //filtro de categoria
//                }

                $condicion .= ") )";
            } else {
                //filtro de categoria
                if (!empty($filtroCategoria)) {
                    if ($filtroCategoria == "my_item") {
                        $condicion .= " AND e.id_usuario = '" . $sesion_usuarioSesion->id . "'"; //filtro de categoria
                    } elseif ($filtroCategoria == "past_events") {
                        $condicion .= " AND e.fecha_fin <= NOW()"; //filtro de categoria
                    } else {
                        $condicion .= " AND id_categoria = '" . $filtroCategoria . "'"; //filtro de categoria
                    }
                }
            }//fin del if($idPerfil != 0)                
        } else {
            $tablas["pi"] = "permisos_item";
            $columnas["idItem"] = "pi.id_item";
            $columnas["idPerfil"] = "pi.id_perfil";
            $columnas["idModulo"] = "pi.id_modulo";

            $condicion.= " AND pi.id_item = e.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' )";

            if (!empty($filtroCategoria)) {
                $condicion .= " AND id_categoria = '" . $filtroCategoria . "'"; //filtro de categoria
            }
        }


        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
            $sql->seleccionar($tablas, $columnas, $condicion . " AND e.activo = '1'");
            $this->registrosActivos = $sql->filasDevueltas;
        }
        
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'e.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {
            while ($evento = $sql->filaEnObjeto($consulta)) {
                $evento->url = $this->urlBase . '/' . $evento->id;
                $evento->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $evento->imagen;
                $evento->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $evento->imagen;
                $evento->iconoBandera = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['iconosBanderas'] . '/' . strtolower($evento->codigoIsoPais) . '.png';
                $evento->registros = $this->registros;
                $evento->registrosActivos = $this->registrosActivos;
                $lista[] = $evento;
            }
        }

        return $lista;
    }


    /**
     *
     * Listar los proximos eventos
     *
     * @param entero  $cantidad    Número de eventos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de eventos
     *
     */
    public function listarProximosEventos($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idModulo=NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;
        ;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'e.id NOT IN ('.$excepcion.')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'e.fecha_inicio DESC';
        } else {
            $orden = 'e.fecha_inicio DESC';
        }

        //compruebo que se le haya pasado un valor al idPerfil

        $idPerfil = $sesion_usuarioSesion->idTipo;

        $tablas = array(
            'e' => 'eventos',
            'u' => 'usuarios',
            'p' => 'personas',
            'ce' => 'centros',
            'c' => 'ciudades',
            'es' => 'estados',
            'pa' => 'paises',
            'c2' => 'ciudades',
            'i' => 'imagenes',
            'i1' => 'imagenes'
        );

        $columnas = array(
            'id' => 'e.id',
            'idAutor' => 'e.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'titulo' => 'e.titulo',
            'resumen' => 'e.resumen',
            'descripcion' => 'e.descripcion',
            'idCiudad' => 'e.id_ciudad',
            'ciudad' => 'c.nombre',
            'idEstado' => 'es.id',
            'estado' => 'es.nombre',
            'idPais' => 'pa.id',
            'pais' => 'pa.nombre',
            'idCentro' => 'e.id_centro',
            'centro' => 'ce.nombre',
            'idCiudadCentro' => 'ce.id_ciudad',
            'ciudadCentro' => 'c2.nombre',
            'idCategoria' => 'e.id_categoria',
            'lugar' => 'e.lugar',
            'idImagen' => 'e.id_imagen',
            'imagen' => 'i.ruta',
            'fechaInicio' => 'e.fecha_inicio',
            'horaInicio' => 'e.hora_inicio',
            'idImagenAutor' => 'i1.id',
            'imagenAutor' => 'i1.ruta',
            'fechaFin' => 'e.fecha_fin',
            'horaFin' => 'e.hora_fin',
            'fechaCreacion' => 'UNIX_TIMESTAMP(e.fecha_creacion)',
            'fechaActivacion' => 'UNIX_TIMESTAMP(e.fecha_activacion)',
            'fechaInactivacion' => 'UNIX_TIMESTAMP(e.fecha_inactivacion)',
            'activo' => 'e.activo',
            'infoContacto' => 'e.info_contacto'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'e.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i1.id AND e.id_imagen = i.id AND e.id_ciudad = c.id AND es.id = c.id_estado AND pa.id = es.id_pais  AND e.id_centro = ce.id AND ce.id_ciudad = c2.id';

        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {

                $condicionA = $condicion;

                $tablas['pi'] = 'permisos_item';
                $columnas['idItem'] = 'pi.id_item';
                $columnas['idPerfil'] = 'pi.id_perfil';
                $columnas['idModulo'] = 'pi.id_modulo';

                $condicion .= " AND pi.id_item = e.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil')";

                $condicion = "(" . $condicion . ")";
                $condicion .= "OR ($condicionA AND e.id_usuario = '$sesion_usuarioSesion->id'";

                $condicion .= ")";
            }//fin del if($idPerfil != 0)                
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= " AND pi.id_item = e.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' )";
        }


        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'e.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {
            while ($evento = $sql->filaEnObjeto($consulta)) {
                $evento->url = $this->urlBase . '/' . $evento->id;
                $evento->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $evento->imagen;
                $evento->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $evento->imagen;
                $lista[] = $evento;
            }
        }

        return $lista;
    }


    /**
     *
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @param type $idUsuario
     * @param type $idNoticiaActual
     * @return boolean 
     */
    public function proximosEventos() {
        global $textos, $configuracion, $sesion_usuarioSesion;


        /* Capturar el tipo de usuario que tiene el usuario actual */
        if (isset($sesion_usuarioSesion)) {
            $idTipo = $sesion_usuarioSesion->idTipo;
        } else {
            $idTipo = 99;
        }

        $condicion = ' e.fecha_inicio BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 8 DAY) AND ';

        $arregloEventos = $this->listarProximosEventos(0, 5, array($this->id), $condicion, $idTipo, $this->idModulo);
        $listaProximosEventos = array($textos->id('PROXIMOS_EVENTOS'));
        $listaEventos = array();


        if (sizeof($arregloEventos) > 0) {

            foreach ($arregloEventos as $elemento) {
                $item = '';

                if ($elemento->activo) {
                    $comentario = new Comentario();

                    $contenedorComentarios = $comentario->mostrarComentarios($elemento->idModulo, $elemento->id);
                    $contenedorMeGusta = Recursos::mostrarContadorMeGusta($elemento->idModulo, $elemento->id);
                    $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, 'mostrarPosted');
                    //seleccionar el genero de una persona 
                    $usuario = new Usuario();
                    $item .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('EVENTOS', $elemento->id));
                    $item .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $usuario->getGenero($elemento->idAutor) . '.png') . str_replace('%1', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)) . 'On ' . HTML::frase(date('D, d M Y', $elemento->fechaPublicacion), 'pequenia cursiva negrilla') . $comentarios, $textos->id('PUBLICADO_POR')));
                    $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                    $item2 .= HTML::parrafo($elemento->resumen, 'pequenia cursiva');
                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris

                    $item = HTML::contenedor($item, 'contenedorListaMasNoticias', 'contenedorListaNoticias' . $elemento->id);

                    $listaEventos[] = $item;
                }//fin del  SI Blog es activo
            }//fin del foreach

            $acordeon = HTML::acordeonLargo2($listaProximosEventos, $listaEventos, 'masEventos', '');
        }//fin del if  
        return $acordeon;
    }

}

?>
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 * */
class Foro {

    /**
     * Código interno o identificador del foro en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de foros
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un foro específico
     * @var cadena
     */
    public $url;

    /**
     * URL relativa de un foro específico
     * @var cadena
     */
    public $enlace;

    /**
     * Código interno o identificador del módulo al cual pertenece el foro en la base de datos
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador del registro del módulo al cual pertenece el foro en la base de datos
     * @var entero
     */
    public $idRegistro;

    /**
     * Código interno o identificador del registro del módulo al cual pertenece el foro en la base de datos
     * @var entero
     */
    public $idModuloActual;

    /**
     * Código interno o identificador del usuario creador del foro en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Código interno o identificador de la categoria del foro en la base de datos
     * @var entero
     */
    public $idCategoria;

    /**
     * Categoria del foro en la base de datos
     * @var entero
     */
    public $categoria;

    /**
     * Nombre de usuario (login) del usuario creador del foro
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador del foro
     * @var cadena
     */
    public $autor;

    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;

    /**
     * Título del foro
     * @var cadena
     */
    public $titulo;

    /**
     * Título del foro
     * @var cadena
     */
    public $descripcion;

    /**
     * Fecha de publicación del foro
     * @var fecha
     */
    public $fecha;

    /**
     * Indicador del estado del foro
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
     * Número de registros activos de la lista de foros
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * Número de mensajes de la lista
     * @var entero
     */
    public $mensajes = NULL;

    /**
     * Inicializar el foro
     * @param entero $id Código interno o identificador del foro en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('FOROS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;
        $this->registros = $sql->obtenerValor('foros', 'count(id)', 'id != "0"');

        $this->registrosActivos = $this->registros;


        if (isset($id)) {
            $this->cargar($id);
	    $this->mensajes = $sql->obtenerValor('mensajes_foro', 'COUNT(id)', 'id_foro = "' . $id . '"');
        }
    }

    /**
     * Cargar los datos del foro
     * @param entero $id Código interno o identificador del foro en la base de datos
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (!empty($id) && $sql->existeItem('foros', 'id', intval($id))) {

            $tablas = array(
                'f' => 'foros',
                'c' => 'categoria',
                'u' => 'usuarios',
                'p' => 'personas',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id' => 'f.id',
                'idAutor' => 'f.id_usuario',
                'descripcion' => 'f.descripcion',
                'idModuloActual' => 'f.id_modulo',
                'idRegistro' => 'f.id_registro',
                'usuarioAutor' => 'u.usuario',
                'autor' => 'u.sobrenombre',
                'fotoAutor' => 'i.ruta',
                'titulo' => 'f.titulo',
                'fecha' => 'UNIX_TIMESTAMP(f.fecha)',
                'idCategoria' => 'c.id',
                'categoria' => 'c.nombre'
            );

            $condicion = 'f.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND f.id_categoria = c.id AND f.id = "' . $id . '"';
            //$sql->depurar = true;
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->enlace = '/' . $this->url . '/' . $id;
                $this->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->fotoAutor;
            }
        }
    }

    /**
     * Adicionar un foro
     * @param  arreglo $datos       Datos del foro a adicionar
     * @return entero               Código interno o identificador del foro en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $textos;

        if (!isset($sesion_usuarioSesion)) {
            return NULL;
        }
        $datosForo = array(
            'id_modulo' => $datos['idModulo'],
            'id_registro' => htmlspecialchars($datos['idRegistro']),
            'id_usuario' => htmlspecialchars($sesion_usuarioSesion->id),
            'descripcion' => htmlspecialchars($datos['contenido']),
            'titulo' => htmlspecialchars($datos['titulo']),
            'fecha' => date('Y-m-d H:i:s'),
            'activo' => '1'
        );

        $datosForo['tipo'] = '1';

        if (empty($datos['idRegistro'])) {
            $datosForo['tipo'] = '2';
            $datosForo['id_categoria'] = $datos['categorias'];
        }

        $consulta = $sql->insertar('foros', $datosForo);

        if ($consulta) {
            $idForo = $sql->ultimoId;
            $mod = $sql->obtenerValor('modulos', 'nombre', 'id = "' . $datos['idModulo'] . '"');
            if ($mod == 'FOROS' && isset($datos['notificar_estudiantes'])) {
                $idCurso = $datos['idRegistro'];
                $objetoCurso = new Curso($idCurso);
                $consultaSeguidores = $sql->seleccionar(array('cursos_seguidos'), array('id', 'id_usuario'), 'id_curso = "' . $idCurso . '"', '');
                if ($sql->filasDevueltas) {
                    $tipoItem = $textos->id('FORO');
                    $nombreItem = $datos['titulo'];
                    while ($seguidor = $sql->filaEnObjeto($consultaSeguidores)) {

                        $notificacion1 = str_replace('%1', HTML::enlace($objetoCurso->autor, HTML::urlInterna('CURSOS', $idCurso)), $textos->id('MENSAJE_ADICION_ITEM_CURSO'));
                        $notificacion2 = str_replace('%2', HTML::enlace($tipoItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion1);
                        $notificacion3 = str_replace('%3', HTML::enlace($objetoCurso->nombre, HTML::urlInterna('CURSOS', $idCurso)), $notificacion2);
                        $notificacion = str_replace('%4', HTML::enlace($nombreItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion3);

                        Servidor::notificar($seguidor->id_usuario, $notificacion, array(), '14');
                    }
                }
            }            
            return $idForo;
        } else {
            return NULL;
        }
    }

    /**
     * Modificar un foro
     * @param  arreglo $datos       Datos del foro a modificar
     * @return entero               Código interno o identificador del foro en la base de datos (NULL si hubo error)
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion;

        if (!isset($sesion_usuarioSesion)) {
            return NULL;
        }
        $datosForo = array(
            'id_modulo' => htmlspecialchars($datos['idModulo']),
            'id_registro' => htmlspecialchars($datos['idRegistro']),
            'id_usuario' => $sesion_usuarioSesion->id,
            'descripcion' => htmlspecialchars($datos['contenido']),
            'titulo' => htmlspecialchars($datos['titulo']),
            'fecha' => date('Y-m-d H:i:s'),
            'activo' => '1'
        );

        $datosForo['tipo'] = '1';

        if (empty($datos['idRegistro'])) {
            $datosForo['tipo'] = '2';
            $datosForo['id_categoria'] = $datos['categorias'];
        }
        //$sql->depurar = true;
        $consulta = $sql->modificar('foros', $datosForo, 'id = "' . $datos['id'] . '"');

        if ($consulta) {
            return $consulta;
        } else {
            return NULL;
        }
    }

    /**
     * Adicionar un mensaje o respuesta en un foro
     * @param  entero   $id         Código interno o identificador del foro en la base de datos
     * @param  cadena   $contenido  Contenido del mensaje a adicionar
     * @return entero               Código interno o identificador del foro en la base de datos (NULL si hubo error)
     */
    public function adicionarMensaje($id, $contenido) {
        global $sql, $sesion_usuarioSesion;
        if(!isset($sesion_usuarioSesion)){
            return NULL;
        }
        $datos = array(
            'id_foro' => $id,
            'id_usuario' => $sesion_usuarioSesion->id,
            'contenido' => htmlspecialchars($contenido),
            'fecha' => date('Y-m-d H:i:s')
        );

        $consulta = $sql->insertar('mensajes_foro', $datos);

        if ($consulta) {
            return $sql->ultimoId;
        } else {
            return NULL;
        }
    }

    /**
     * Eliminar un foro
     * @param entero $id    Código interno o identificador del foro en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $sql->eliminar('mensajes_foro', 'id_foro = "' . $this->id . '"');

        $consulta = $sql->eliminar('foros', 'id = "' . $this->id . '"');

        return $consulta;
    }

    /**
     * Eliminar un mensaje de un foro
     * @param entero $id    Código interno o identificador del foro en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminarMessage($id_mensaje) {
        global $sql;

        if (!isset($id_mensaje)) {
            return NULL;
        }
        //$sql->depurar = true;
        $consulta = $sql->eliminar('mensajes_foro', 'id = "' . $id_mensaje . '"');

        return $consulta;
    }

    /**
     * Contar la cantidad de mensajes que tiene un determinado foro
     * */
    public function contarMensajes($idForo) {
        global $sql;
        if (!isset($idForo)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array('mensajes_foro'), array('registros' => 'COUNT(id)'), 'id_foro = "' . $idForo . '"');
        $arreglo = $sql->filaEnObjeto($consulta);

        if ($arreglo->registros != 0) {
            $numMensajes = $arreglo->registros;
        } else {
            $numMensajes = ' 0 ';
        }

        return $numMensajes;
    }

    /**
     * Contar la cantidad de foros de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return entero              Número de foros hechos al registro del módulo
     */
    public function contar($modulo, $registro) {
        global $sql;

        $tablas = array(
            'f' => 'foros',
            'm' => 'modulos'
        );

        $columnas = array(
            'registros' => 'COUNT(f.id)'
        );

        $condicion = 'f.id_modulo = m.id AND f.id_registro = "' . $registro . '" AND m.nombre = "' . $modulo . '" AND f.activo = "1"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $foro = $sql->filaEnObjeto($consulta);
            return $foro->registros;
        } else {
            return NULL;
        }
    }

    /**
     * Listar los foros de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de foros hechos al registro del módulo
     */
    public function listar($modulo, $registro) {
        global $sql, $configuracion;


        $tablas = array(
            'f' => 'foros',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes',
            'm' => 'modulos'
        );

        $columnas = array(
            'id' => 'f.id',
            'idAutor' => 'f.id_usuario',
            'activo' => 'f.activo',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'titulo' => 'f.titulo',
            'fecha' => 'UNIX_TIMESTAMP(f.fecha)'
        );

        $condicion = 'f.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND f.id_modulo = m.id AND f.id_registro = "' . $registro . '" AND m.nombre = "' . $modulo . '" AND f.activo = "1"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', 'fecha DESC');

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($foro = $sql->filaEnObjeto($consulta)) {
                $foro->url = $this->urlBase . '/' . $foro->id;
                $foro->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $foro->fotoAutor;
                $lista[] = $foro;
            }
        }

        return $lista;
    }

    /**
     * Listar los mensajes de un foro
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de foros hechos al registro del módulo
     */
    public function listarMensajes() {
        global $sql, $configuracion;


        $tablas = array(
            'f' => 'mensajes_foro',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'f.id',
            'idAutor' => 'f.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'contenido' => 'f.contenido',
            'fecha' => 'UNIX_TIMESTAMP(f.fecha)'
        );

        $condicion = 'f.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND f.id_foro = "' . $this->id . '"';

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', 'fecha ASC');

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($foro = $sql->filaEnObjeto($consulta)) {
                $foro->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $foro->fotoAutor;
                $lista[] = $foro;
            }
        }

        return $lista;
    }

    /**
     *
     * Listar los foros existentes
     *
     * @param  cadena $modulo      Nombre
     * @return arreglo             Lista de foros hechos al registro del módulo
     *
     */
    public function listarForos($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $filtroCategoria = NULL) {
        global $sql, $sesion_usuarioSesion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'f.id NOT IN (' . $excepcion . ')';
        }


        $tablas = array(
            'f' => 'foros',
            'c' => 'categoria',
            'u' => 'usuarios',
            'p' => 'personas'
        );

        $columnas = array(
            'id' => 'f.id',
            'idAutor' => 'f.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'activo' => 'f.activo',
            'autor' => 'u.sobrenombre',
            'titulo' => 'f.titulo',
            'fecha' => 'UNIX_TIMESTAMP(f.fecha)',
            'idCategoria' => 'c.id',
            'categoria' => 'c.nombre'
        );

        $condicion = 'f.id_usuario = u.id AND u.id_persona = p.id AND f.id_categoria = c.id AND f.tipo = 2 ';

        if (!empty($filtroCategoria)) {

            if ($filtroCategoria == 'my_item') {
                $condicion .= ' AND f.id_usuario = "' . $sesion_usuarioSesion->id . '"'; //filtro de categoria
            } else {
                $condicion .= ' AND f.id_categoria = "' . $filtroCategoria . '"'; //filtro de categoria
            }
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', 'fecha DESC', $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($foro = $sql->filaEnObjeto($consulta)) {
                $foro->url = $this->urlBase . '/' . $foro->id;

                $lista[] = $foro;
            }
        }

        return $lista;
    }

}

?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Galeria
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 Colombo-Americano Soft.
 * @version     0.2
 * */
class Galeria {

    /**
     * Código interno o identificador del blog en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de blogs
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un blog específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador del blog en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador de la categoria a la cual pertenece la noticia 
     * @var entero
     */
    public $idRegistro;

    /**
     * Título del blog
     * @var cadena
     */
    public $titulo;

    /**
     * Contenido completo del blog
     * @var cadena
     */
    public $descripcion;

    /**
     * Fecha de creación del blog
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $miniatura;

    /**
     * Indicador del orden cronológio de la lista de blogs
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Número de fotos que tiene de la galeria
     * @var entero
     */
    public $cantidadFotos;

    /**
     * Número de registros activos de la lista de foros
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * Inicializar la galeria
     * @param entero $id Código interno o identificador de la galeria en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('GALERIAS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;
        //Saber el numero de registros
        $this->registros = $sql->obtenerValor('galerias', 'COUNT(id)', '');
        //Saber el numero de registros activos
        $this->registrosActivos = $sql->obtenerValor('galerias', 'COUNT(id)', 'activo = "1"');

        if (isset($id)) {
            $this->cargar($id);
            //Saber la cantidad de fotos que tiene esta galeria
            $consulta = $sql->obtenerValor('imagenes', 'COUNT(id)', 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');
            $this->cantidadFotos = $consulta;
        }
    }

    /**
     * Cargar los datos de una galeria
     * @param entero $id Código interno o identificador de la galeria en la base de datos
     */
    public function cargar($id) {
        global $sql;

        if (isset($id) && $sql->existeItem('galerias', 'id', intval($id))) {

            $tablas = array(
                'g' => 'galerias'
            );

            $columnas = array(
                'id' => 'g.id',
                'idAutor' => 'g.id_usuario',
                'titulo' => 'g.titulo',
                'descripcion' => 'g.descripcion',
                'idModulo' => 'g.id_modulo',
                'idRegistro' => 'g.id_registro',
                'fechaCreacion' => 'UNIX_TIMESTAMP(g.fecha)',
                'activo' => 'g.activo'
            );

            $condicion = 'g.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->id;
            }
        }
    }

    /**
     * Adicionar una galeria
     * @param  arreglo $datos       Datos de la galeria a adicionar
     * @return entero               Código interno o identificador de la galeria en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $textos;

        $cantImagenes = $datos['cantCampoImagenGaleria']; //este campo es el valor del campo oculto que va guardando cuantas imagenes esta recibiendo 
        if ($cantImagenes) {
            for ($i = 1; $i <= $cantImagenes; $i++) {//creo como globales cada uno de los archivos imagenes
                $nombre = 'archivo_imagen' . $i;
                global $$nombre;
            }
        }
        $datosGaleria = array();

        $datosGaleria['titulo'] = htmlspecialchars($datos['titulo_galeria']);
        $datosGaleria['descripcion'] = htmlspecialchars($datos['descripcion_galeria']);
        $datosGaleria['id_modulo'] = htmlspecialchars($datos['id_modulo_actual']);
        $datosGaleria['id_registro'] = htmlspecialchars($datos['id_registro_actual']);
        $datosGaleria['id_usuario'] = $sesion_usuarioSesion->id;
        $datosGaleria['fecha'] = date('Y-m-d H:i:s');

        if (isset($datos['activo_galeria'])) {
            $datosGaleria['activo'] = '1';
        } else {
            $datosGaleria['activo'] = '0';
        }

        $consulta = $sql->insertar('galerias', $datosGaleria);
        $idGaleria = $sql->ultimoId;

        if ($consulta) {

            if ($cantImagenes) {
                $imagen = new Imagen();
                $datosImagen = array();
                for ($i = 1; $i <= $cantImagenes; $i++) {//primero validar que si exista el archivo
                    $datosImagen['titulo'] = htmlspecialchars($datos['titulo_imagen' . $i . '']);
                    $datosImagen['descripcion'] = htmlspecialchars($datos['descripcion_imagen' . $i . '']);
                    $datosImagen['idRegistro'] = $idGaleria;
                    $datosImagen['modulo'] = 'GALERIAS';
                    $archivo = 'archivo_imagen' . $i;
                    $imagen->adicionar($datosImagen, $$archivo);
                }
            }

            $mod = $sql->obtenerValor('modulos', 'nombre', 'id = "' . $datos['id_modulo_actual'] . '"');
            if ($mod == 'CURSOS' && isset($datos['notificar_estudiantes'])) {
                $idCurso = $datos['id_registro_actual'];
                $objetoCurso = new Curso($idCurso);
                $consultaSeguidores = $sql->seleccionar(array('cursos_seguidos'), array('id', 'id_usuario'), 'id_curso = "' . $idCurso . '"', '');
                if ($sql->filasDevueltas) {
                    $tipoItem = $textos->id('GALERIA');
                    $nombreItem = $datos['titulo_galeria'];
                    while ($seguidor = $sql->filaEnObjeto($consultaSeguidores)) {

                        $notificacion1 = str_replace('%1', HTML::enlace($objetoCurso->autor, HTML::urlInterna('CURSOS', $idCurso)), $textos->id('MENSAJE_ADICION_ITEM_CURSO'));
                        $notificacion2 = str_replace('%2', HTML::enlace($tipoItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion1);
                        $notificacion3 = str_replace('%3', HTML::enlace($objetoCurso->nombre, HTML::urlInterna('CURSOS', $idCurso)), $notificacion2);
                        $notificacion = str_replace('%4', HTML::enlace($nombreItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion3);

                        Servidor::notificar($seguidor->id_usuario, $notificacion, array(), '13');
                    }
                }
            }

            return 1;
        } else {
            return NULL;
        }//fin del if($consulta)
    }

    /**
     * Modificar una galeria
     * @param  arreglo $datos       Datos de la galeria a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion;

        if (!isset($this->id)) {
            return NULL;
        }

        $cantImagenes = $datos['cantCampoImagenGaleria']; //este campo es el valor del campo oculto que va guardando cuantas imagenes esta recibiendo 
        $imagenesAEliminar = $datos['imagenesAEliminar'];
        $imagenesAEditar = $datos['imagenesAEditar'];

        if ($cantImagenes) {
            for ($i = 1; $i <= $cantImagenes; $i++) {//creo como globales cada uno de los archivos imagenes
                $nombre = 'archivo_imagen' . $i;
                global $$nombre;
            }
        }

        $datosGaleria = array();

        $datosGaleria['titulo'] = htmlspecialchars($datos['titulo_galeria']);
        $datosGaleria['descripcion'] = htmlspecialchars($datos['descripcion_galeria']);
        $datosGaleria['id_modulo'] = htmlspecialchars($datos['id_modulo_actual']);
        $datosGaleria['id_registro'] = htmlspecialchars($datos['id_registro_actual']);
        $datosGaleria['id_usuario'] = $sesion_usuarioSesion->id;
        $datosGaleria['fecha'] = date('Y-m-d H:i:s');

        if (isset($datos['activo_galeria'])) {
            $datosGaleria['activo'] = '1';
        } else {
            $datosGaleria['activo'] = '0';
        }

        $consulta = $sql->modificar('galerias', $datosGaleria, 'id = "' . $this->id . '"');

        if ($consulta) {

            if ($cantImagenes) {
                $imagen = new Imagen();
                $datosImagen = array();
                for ($i = 1; $i <= $cantImagenes; $i++) {//primero validar que si exista el archivo
                    $datosImagen['titulo'] = htmlspecialchars($datos['titulo_imagen' . $i . '']);
                    $datosImagen['descripcion'] = htmlspecialchars($datos['descripcion_imagen' . $i . '']);
                    $datosImagen['idRegistro'] = $this->id;
                    $datosImagen['modulo'] = 'GALERIAS';
                    $archivo = 'archivo_imagen' . $i;
                    $imagen->adicionar($datosImagen, $$archivo);
                }
            }

            if ($imagenesAEditar) {
                $imagen = new Imagen();
                $imagenesAEditar = explode('|', $imagenesAEditar);
                foreach ($imagenesAEditar as $valor) {//primero validar que si exista el archivo
                    if ($valor != '0') {
                        $datosImagen['id'] = $valor;
                        $datosImagen['titulo'] = htmlspecialchars($datos['titulo_imagen_' . $valor . '']);
                        $datosImagen['descripcion'] = htmlspecialchars($datos['descripcion_imagen_' . $valor . '']);
                        $imagen->modificarInfoImagen($datosImagen);
                    }
                }
            }

            if ($imagenesAEliminar) {
                $imagenesAEliminar = explode('|', $imagenesAEliminar);
                foreach ($imagenesAEliminar as $valor) {//primero validar que si exista el archivo
                    if ($valor != '0') {
                        $imagen = new Imagen($valor);
                        $imagen->eliminar();
                    }
                }
            }

            return 1;
        } else {
            return NULL;
        }//fin del if(consulta)
    }

    /**
     * Eliminar una galeria
     * @param entero $id    Código interno o identificador de la galeria en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;
        if (!isset($this->id)) {
            return NULL;
        }
        //verificar si la galeria tiene imagenes y eliminarlas todas
        $idModulo = $sql->obtenerValor('modulos', 'id', 'BINARY nombre = "GALERIAS"');
        $cantidadImagenes = $sql->seleccionar(array('imagenes'), array('id'), 'id_modulo = "' . $idModulo . '" AND id_registro = "' . $this->id . '"');

        if ($sql->filasDevueltas) {
            while ($fila = $sql->filaEnObjeto($cantidadImagenes)) {
                $imagen = new Imagen($fila->id);
                $imagen->eliminar();
            }
        }

        if (!($consulta = $sql->eliminar('galerias', 'id = "' . $this->id . '"'))) {
            return false;
        } else {
            return true;
        }//fin del si funciono eliminar
    }

//Fin del metodo eliminar Galerias

    /**
     * Eliminar todas las galerias de un item 
     */
    public function eliminarGalerias($idModulo, $idRegistro) {
        global $sql;

        $cantidadGalerias = $sql->seleccionar(array('galerias'), array('id'), 'id_modulo = "' . $idModulo . '" AND id_registro = "' . $idRegistro . '"');
        if ($sql->filasDevueltas) {

            while ($fila = $sql->filaEnObjeto($cantidadGalerias)) {
                $galeria = new Galeria($fila->id);
                $galeria->eliminar();
            }
        }
    }

    /**
     * Listar las galerias que tiene un determinado modulo de un registro en un módulo
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de archivos hechos al registro del módulo
     */
    public function listar($inicio = 0, $cantidad = 0, $modulo = NULL, $registro = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        $tablas = array(
            'g' => 'galerias',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes',
            'm' => 'modulos'
        );

        $columnas = array(
            'id' => 'g.id',
            'idAutor' => 'g.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'fotoAutor' => 'i.ruta',
            'titulo' => 'g.titulo',
            'descripcion' => 'g.descripcion'
        );

        $condicion = 'g.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND g.id_modulo = m.id AND g.id_registro = "' . htmlspecialchars($registro) . '" AND m.nombre = "' . htmlspecialchars($modulo) . '"';
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', 'descripcion ASC', $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($documento = $sql->filaEnObjeto($consulta)) {
                $documento->url = $this->urlBase . '/' . $documento->id;
                $documento->fotoAutor = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $documento->fotoAutor;
                $documento->enlace = $documento->id;
                $documento->icono = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'galeria.png';
                $lista[] = $documento;
            }
        }

        return $lista;
    }

    /**
     * Metodo que carga el formulario
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @return type 
     */
    public static function formularioAdicionarGaleria($idModulo = NULL, $idRegistro = NULL) {
        global $textos;

        $codigo = '';
        $codigo .= HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo_galeria]', 40, 255);
        $codigo .= HTML::parrafo($textos->id('DESCRIPCION'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[descripcion_galeria]', 4, 50, '', '');
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo_galeria]', true) . $textos->id('ACTIVO'), 'margenSuperior');
        $codigo .= HTML::parrafo(HTML::boton('imagen', $textos->id('ADICIONAR_IMAGEN'), 'adicionarImagen directo', '', 'adicionarImagenGaleria') . HTML::frase($textos->id('CLICK_PARA_MAS_IMAGENES')), 'margenSuperiorDoble');
        $codigo .= HTML::contenedor('', 'contenedorImagenesGaleria margenSuperior', 'contenedorImagenesGaleria');
        $codigo .= HTML::campoOculto('datos[cantCampoImagenGaleria]', '0', 'cantCampoImagenGaleria');
        if (isset($idModulo) && isset($idRegistro)) {
            $codigo .= HTML::campoOculto('datos[id_modulo_actual]', $idModulo, 'idModulo');
            $codigo .= HTML::campoOculto('datos[id_registro_actual]', $idRegistro, 'idModulo');
        }
        return $codigo;
    }

    public static function formularioModificarGaleria($id = NULL, $idModuloActual = NULL, $idRegistroActual = NULL) {
        global $textos, $sql, $configuracion;

        if (!isset($id)) {
            return NULL;
        }

        $idModuloActual = htmlspecialchars($idModuloActual);
        $idRegistroActual = htmlspecialchars($idRegistroActual);
        $id = htmlspecialchars($id);

        $galeria = new Galeria($id);
        $idsImagenesAEditar = '0';

        $codigo = '';
        $codigo .= HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::campoOculto('datos[id_modulo_actual]', $idModuloActual);
        $codigo .= HTML::campoOculto('datos[id_registro_actual]', $idRegistroActual);
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo_galeria]', 40, 50, $galeria->titulo);
        $codigo .= HTML::parrafo($textos->id('DESCRIPCION'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[descripcion_galeria]', 4, 50, $galeria->descripcion, '');
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo_galeria]', $galeria->activo) . $textos->id('ACTIVO'), 'margenSuperior');
        $codigo .= HTML::parrafo(HTML::boton('imagen', $textos->id('ADICIONAR_IMAGEN'), 'adicionarImagen directo', '', 'adicionarImagenGaleria'), 'margenSuperiorDoble');
        $codigo .= HTML::contenedor('', 'contenedorImagenesGaleria margenSuperior', 'contenedorImagenesGaleria');
        $codigo .= HTML::campoOculto('datos[cantCampoImagenGaleria]', '0', 'cantCampoImagenGaleria');
        $codigo .= HTML::campoOculto('datos[imagenesAEliminar]', '0', 'imagenesAEliminar');

        $modulo = $sql->obtenerValor('modulos', 'id', 'BINARY nombre = "GALERIAS"');
        $consulta = $sql->seleccionar(array('imagenes'), array('id', 'titulo', 'descripcion', 'ruta'), 'id_modulo = "' . $modulo . '" AND id_registro = "' . $galeria->id . '"');


        while ($img = $sql->filaEnObjeto($consulta)) {

            $ruta = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $img->ruta;
            $ruta = HTML::contenedor(HTML::imagen($ruta, '', ''), 'alineadoIzquierda');
            $titulo = HTML::parrafo($textos->id('TITULO'), 'negrilla');
            $titulo .= HTML::campoTexto('datos[titulo_imagen_' . $img->id . ']', 30, 100, $img->titulo);
            $descripcion = HTML::parrafo($textos->id('DESCRIPCION'), 'negrilla');
            $descripcion .= HTML::campoTexto('datos[descripcion_imagen_' . $img->id . ']', 40, 100, $img->descripcion);
            $infoImagen = HTML::contenedor($titulo . $descripcion, 'alineadoIzquierda');
            $btnEliminar = HTML::boton('', 'X', 'directo alineadoDerecha negrilla', '', 'editarEliminarImagenGaleria');
            $codigo .= HTML::contenedor($ruta . $infoImagen . $btnEliminar, 'contenedorImagenGaleria margenIzquierda margenDerecha margenSuperior margenInferior bordeInferior espacioInferior espacioSuperior', $img->id);
            $idsImagenesAEditar .= '|' . $img->id;
        }

        $codigo .= HTML::campoOculto('datos[imagenesAEditar]', $idsImagenesAEditar, 'imagenesAEditar');

        return $codigo;
    }

    /**
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @param type $idModulo //modulo al cual pertenece el registro
     * @param type $idRegistro //registro que desea mostrar si tiene una galeria asociada a el
     * @return type array arrelo con las galerias asociadas a el
     */
    public static function cargarGaleria($idModulo, $idRegistro) {
        global $sql, $configuracion;

        $idModulo = htmlspecialchars($idModulo);
        $idRegistro = htmlspecialchars($idRegistro);

        $cantidadGalerias = $sql->obtenerValor('galerias', 'COUNT(id)', 'id_modulo = "' . $idModulo . '" AND id_registro = "' . $idRegistro . '"'); //consulto si el item tiene galerias

        if ($cantidadGalerias) {
            $codigo = '';
            $galerias = array(); //declaro el arreglo de galerias

            $consulta = $sql->seleccionar(array('galerias'), array('id', 'titulo', 'id_modulo', 'id_registro', 'id_usuario', 'descripcion', 'fecha'), 'id_modulo = "' . $idModulo . '" AND id_registro = "' . $idRegistro . '"'); //consulto las galerias     
            $modulo = new Modulo('GALERIAS');

            if ($sql->filasDevueltas) {//compruebo de nuevo de que si hayan galerias        
                while ($galeria = $sql->filaEnObjeto($consulta)) { //voy recorriendo las galerias y creando objetos de galerias         
                    $imagenes = array(); //declaro el arreglo de imagenes y lo incializo de nuevo
                    $cantidadImagenes = $sql->obtenerValor('imagenes', 'COUNT(id)', 'id_modulo = "' . $modulo->id . '" AND id_registro = "' . $galeria->id . '"'); //cuento la cantidad de imagenes de la galeria

                    if ($cantidadImagenes) {//verifico de nuevo que dicha galeria tenga imagenes
                        $consulta2 = $sql->seleccionar(array('imagenes'), array('id', 'titulo', 'descripcion', 'ruta'), 'id_modulo = "' . $modulo->id . '" AND id_registro = "' . $galeria->id . '"'); //consulto las imagenes de la galeria actual que esta siendo recorrida por el ciclo
                        if ($sql->filasDevueltas) {  //en caso de que haya registros     
                            while ($imagen = $sql->filaEnObjeto($consulta2)) {//voy recorriendo todas las imagenes y voy creando objetos con dichos registros
                                $imagen->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $imagen->ruta; //creo dos atributos de imagen ya con sus rutas
                                $imagen->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $imagen->ruta;

                                $imagenes[] = $imagen; //al arreglo de imagenes agrego el objeto imagen que se acaba de crear en el ciclo                          
                            }

                            $galeria->imagenes = $imagenes; //agrego al objeto galeria actual el arreglo de imagenes suyo
                        }//fin de  if ($sql->filasDevueltas) 
                    }//fin de if($cantidadImagenes)

                    $galerias[] = $galeria; //por ultimo agrego el objeto galeria al arreglo de galerias que voy a crear   
                }//fin de  while ($galeria = $sql->filaEnObjeto($consulta))
            }//fin de if ($sql->filasDevueltas) 

            $codigo .= HTML::crearGaleriaFotos($galerias);
        } else {
            $codigo .= '';
        }

        return $codigo;
    }

    public static function validarImagenesGaleria($cantImagenes) {

        if ($cantImagenes) {//si hay imagenes para ingresar
            for ($i = 1; $i <= $cantImagenes; $i++) {//creo como globales cada uno de los archivos imagenes
                $nombre = 'archivo_imagen' . $i;
                global $$nombre;
            }

            $erroresImagenes = ''; //array();//aqui almacenaré las imagenes que tengan errores

            for ($i = 1; $i <= $cantImagenes; $i++) {//primero validar que si exista el archivo
                $archivo = 'archivo_imagen' . $i;
                $validarFormato = Recursos::validarArchivo($$archivo, array('jpg', 'png', 'gif', 'jpeg')); //valido cada una de las imagenes de la galeria

                if ($validarFormato) {
                    $erroresImagenes .= $i . ', ';
                }
            }
        }

        return $erroresImagenes;
    }

    /**
     * Metodo que devuelve una sola galeria 
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @param type $idModulo //modulo al cual pertenece el registro
     * @param type $idRegistro //registro que desea mostrar si tiene una galeria asociada a el
     * @return type array arrelo con las galerias asociadas a el
     */
    public static function mostrarGaleria($idGaleria) {
        global $sql, $configuracion;

        $idGaleria = htmlspecialchars($idGaleria);

        if (isset($idGaleria)) {
            $codigo = '';
            $modulo = new Modulo('GALERIAS');

            $imagenes = array(); //declaro el arreglo de imagenes y lo incializo de nuevo
            $cantidadImagenes = $sql->obtenerValor('imagenes', 'COUNT(id)', 'id_modulo = "' . $modulo->id . '" AND id_registro = "' . $idGaleria . '"'); //cuento la cantidad de imagenes de la galeria

            if ($cantidadImagenes) {//verifico de nuevo que dicha galeria tenga imagenes
                $consulta2 = $sql->seleccionar(array('imagenes'), array('id', 'titulo', 'descripcion', 'ruta'), 'id_modulo = "' . $modulo->id . '" AND id_registro = "' . $idGaleria . '"'); //consulto las imagenes de la galeria actual que esta siendo recorrida por el ciclo
                if ($sql->filasDevueltas) {  //en caso de que haya registros     
                    while ($imagen = $sql->filaEnObjeto($consulta2)) {//voy recorriendo todas las imagenes y voy creando objetos con dichos registros
                        $imagen->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $imagen->ruta; //creo dos atributos de imagen ya con sus rutas
                        $imagen->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $imagen->ruta;

                        $imagenes[] = $imagen; //al arreglo de imagenes agrego el objeto imagen que se acaba de crear en el ciclo                          
                    }

                    $galeria->imagenes = $imagenes; //agrego al objeto galeria actual el arreglo de imagenes suyo
                }
            }

            $galerias[] = $galeria; //por ultimo agrego el objeto galeria al arreglo de galerias que voy a crear   

            $codigo .= HTML::crearGaleriaFotos($galerias);
        } else {
            $codigo .= '';
        }

        return $codigo;
    }

}

?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Juegos
 * @author      Pablo Andrés Vélez Vidal
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 Colombo-Americano
 * @version     0.1
 * */
class Juego {

    /**
     * Código interno o identificador del juego en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de juegos
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un juego específica
     * @var cadena
     */
    public $url;

    /**
     * Nombre del juego
     * @var cadena
     */
    public $nombre;

    /**
     * Script para insertar (embeber) el juego en la página
     * @var cadena
     */
    public $script;

    /**
     * Descripción del juego
     * @var cadena
     */
    public $descripcion;

    /**
     * Código interno o identificador en la base de datos de la imagen relacionada con el juego
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen del juego en tamaño normal
     * @var cadena
     */
    public $imagen;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Número de registros activos de la lista de foros
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * Indicador del orden cronológio de la lista de noticias
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Número de comentarios que tiene este item 
     * @var entero
     */
    public $cantidadComentarios = NULL;

    /**
     * Número de me gusta que tiene este item 
     * @var entero
     */
    public $cantidadMeGusta = NULL;

    /**
     * Inicializar el juego
     * @param entero $id Código interno o identificador del juego en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('JUEGOS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        $this->registros = $sql->obtenerValor('juegos', 'COUNT(id)', 'id != "0"');

        $this->registrosActivos = $sql->obtenerValor('juegos', 'COUNT(id)', 'activo = "1"');

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos de un juego
     * @param entero $id Código interno o identificador del juego en la base de datos
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (!empty($id) && $sql->existeItem('juegos', 'id', intval($id))) {

            $tablas = array(
                'j' => 'juegos',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id' => 'j.id',
                'idImagen' => 'j.id_imagen',
                'imagen' => 'i.ruta',
                'script' => 'j.script',
                'nombre' => 'j.nombre',
                'descripcion' => 'j.descripcion',
                'activo' => 'j.activo',
                'fechaPublicacion' => 'j.fecha_publicacion'
            );

            $condicion = 'j.id_imagen = i.id AND j.id = "'.$id.'"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->id;
                $this->imagen = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $this->imagen;
                //sumar una visita a la noticia
                $this->sumarVisita();
            }
        }
    }

    /**
     * Adicionar un juego
     * @param  arreglo $datos       Datos del juego a adicionar
     * @return entero               Código interno o identificador del juego en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $archivo_imagen;

        if (empty($datos)) {
            return NULL;
        }

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            $objetoImagen = new Imagen();

            $datosImagen = array(
                'idRegistro' => '',
                'modulo' => 'JUEGOS',
                'descripcion' => 'Image of' . htmlspecialchars($datos['nombre']),
                'titulo' => 'Image of' . htmlspecialchars($datos['nombre'])
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }

        $datos = array(
            'nombre' => $datos['nombre'],
            'script' => $datos['script'],
            'descripcion' => $datos['descripcion'],
            'id_imagen' => $idImagen,
            'fecha_publicacion' => date('Y-m-d H:i:s')
        );


        $consulta = $sql->insertar('juegos', $datos);

        if ($consulta) {
            return $sql->ultimoId;
        } else {
            return false;
        }
    }

    /**
     * Modificar un juego
     * @param  arreglo $datos       Datos del juego a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
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
                'idRegistro' => $this->id,
                'modulo' => 'JUEGOS',
                'titulo' => 'Image of ' . htmlspecialchars($datos['nombre']),
                'descripcion' => 'Image of ' . htmlspecialchars($datos['nombre'])
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }

        $datos = array(
            'nombre' => $datos['nombre'],
            'script' => $datos['script'],
            'descripcion' => $datos['descripcion'],
            'id_imagen' => $idImagen
        );

        $consulta = $sql->modificar('juegos', $datos, 'id = "' . $this->id . '"');

        return $consulta;
    }

    /**
     * Eliminar un juego
     * @param entero $id    Código interno o identificador del juego en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('juegos', 'id = "' . $this->id . '"');

        if ($consulta) {
            $objetoImagen = new Imagen($this->idImagen);
            $objetoImagen->eliminar();

            $comentario = new Comentario();
            $comentario->eliminarComentarios($this->id, $this->idModulo);
        }

        return $consulta;
    }

    /**
     * Listar las juegos
     * @param entero  $cantidad    Número de juegos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de juegos
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'j.id NOT IN ('.$excepcion.')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'j.fecha_publicacion ASC';
        } else {
            $orden = 'j.fecha_publicacion DESC';
        }

        $tablas = array(
            'j' => 'juegos',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'j.id',
            'idImagen' => 'j.id_imagen',
            'imagen' => 'i.ruta',
            'nombre' => 'j.nombre',
            'descripcion' => 'j.descripcion',
            'fechaPublicacion' => 'j.fecha_publicacion'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'j.id_imagen = i.id';

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($juego = $sql->filaEnObjeto($consulta)) {
                $juego->url = $this->urlBase . '/' . $juego->id;
                $juego->imagen = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $juego->imagen;
                $lista[] = $juego;
            }
        }

        return $lista;
    }

    /**
     *
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @return null|boolean 
     */
    public function sumarVisita() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $numVisitas = $sql->obtenerValor('juegos', 'visitas', 'id = "' . $this->id . '"');

        $datosJuego['visitas'] = $numVisitas + 1;

        $sql->guardarBitacora = false;
        $sumVisita = $sql->modificar('juegos', $datosJuego, 'id = "' . $this->id . '"');

        if ($sumVisita) {
            return true;
        } else {
            return false;
        }
    }


}

?>
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Menus
 * @author      Francisco J. Lozano c. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

class Menu {

    /**
     * Código interno o identificador del menú en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Valor numérico que determina el orden o la posición del menú en la base de datos
     * @var entero
     */
    public $orden;

    /**
     * URL relativa del módulo de menús
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un menu específico
     * @var cadena
     */
    public $url;

    /**
     * Nombre del menú
     * @var cadena
     */
    public $nombre;

    /**
     * Dirección (URL) a la cual conduce el menú al hacer clic
     * @var cadena
     */
    public $destino;

    /**
     * Número de páginas que contiene el menú
     * @var entero
     */
    public $paginas;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de menús
     * @var lógico
     */
    public $listaAscendente = true;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     *
     * Inicializar el menu
     *
     * @param entero $id Código interno o identificador del menú en la base de datos
     *
     */
    public function __construct($id = NULL) {

        $modulo        = new Modulo("MENUS");
        $this->urlBase = "/".$modulo->url;
        $this->url     = $modulo->url;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos de un menu
     *
     * @param entero $id Código interno o identificador del menú en la base de datos
     *
     */
    public function cargar($id) {
        global $sql;

        if (isset($id) && $sql->existeItem("menus", "id", intval($id))) {

            $tablas = array(
                "m" => "menus"
            );

            $columnas = array(
                "id"      => "m.id",
                "nombre"  => "m.nombre",
                "orden"   => "m.orden",
                "destino" => "m.destino",
                "activo"  => "m.activo"
            );

            $condicion = "m.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url     = $this->urlBase."/".$this->usuario;
                $paginas       = $sql->filaEnObjeto($sql->seleccionar(array("paginas"), array("paginas" => "COUNT(*)"), "id_menu = '".$this->id."'"));
                $this->paginas = $paginas->paginas;
            }
        }
    }

    /**
     *
     * Adicionar un menu
     *
     * @param  arreglo $datos       Datos del menú a adicionar
     * @return entero               Código interno o identificador del menú en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql;

        $orden = $datos["orden"];
        $menus = $sql->seleccionar(array("menus"), array("orden"), "orden >= '$orden'", "id", "orden ASC", 0, 2);
        $orden = $items = 0;

        if ($sql->filasDevueltas == 2) {
            while ($menu = $sql->filaEnObjeto($menus)) {
                $items++;
                $orden += $menu->orden;
            }

            $orden /= $items;

        } elseif ($sql->filasDevueltas == 1) {
            $menu  = $sql->filaEnObjeto($menus);
            $orden = ($menu->orden + 10000) / 2;
        }

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";

        } else {
            $datos["activo"] = "0";
        }

        $datos = array(
            "orden"   => $orden,
            "nombre"  => $datos["nombre"],
            "destino" => $datos["destino"],
            "activo"  => $datos["activo"]
        );

        $consulta = $sql->insertar("menus", $datos);

        if ($consulta) {
            return $sql->ultimoId;

        } else {
            return NULL;
        }
    }

    /**
     *
     * Modificar un menu
     *
     * @param  arreglo $datos       Datos del menú a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $orden = $datos["orden"];
        $menus = $sql->seleccionar(array("menus"), array("orden"), "orden >= '$orden'", "id", "orden ASC", 0, 2);
        $orden = $items = 0;

        if ($sql->filasDevueltas == 2) {
            while ($menu = $sql->filaEnObjeto($menus)) {
                $items++;
                $orden += $menu->orden;
            }

            $orden /= $items;

        } elseif ($sql->filasDevueltas == 1) {
            $menu  = $sql->filaEnObjeto($menus);
            $orden = ($menu->orden + 10000) / 2;
        }

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";

        } else {
            $datos["activo"] = "0";
        }

        $datos = array(
            "orden"   => $orden,
            "nombre"  => $datos["nombre"],
            "destino" => $datos["destino"],
            "activo"  => $datos["activo"]
        );

        $consulta = $sql->modificar("menus", $datos, "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Eliminar un menu
     *
     * @param entero $id    Código interno o identificador del menú en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("menus", "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Subir de nivel un menú
     *
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function subir() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array("menus"), array("id", "orden"), "orden < '".$this->orden."'", "id", "orden DESC", 0, 1);

        if ($sql->filasDevueltas) {
            $menu      = $sql->filaEnObjeto($consulta);
            $temporal  = ($this->orden + $menu->orden)/2;
            $sql->modificar("menus",array("orden" => $temporal), "id = '".$this->id."'");
            $sql->modificar("menus",array("orden" => $this->orden), "id = '".$menu->id."'");
            $sql->modificar("menus",array("orden" => $menu->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     *
     * Bajar de nivel un menú
     *
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function bajar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array("menus"), array("id", "orden"), "orden > '".$this->orden."'", "id", "orden ASC", 0, 1);

        if ($sql->filasDevueltas) {
            $menu      = $sql->filaEnObjeto($consulta);
            $temporal  = ($this->orden + $menu->orden)/2;
            $sql->modificar("menus",array("orden" => $temporal), "id = '".$this->id."'");
            $sql->modificar("menus",array("orden" => $this->orden), "id = '".$menu->id."'");
            $sql->modificar("menus",array("orden" => $menu->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     *
     * Listar los menús
     *
     * @param entero  $cantidad    Número de menús a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de menús
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

        /*** Validar que la condición sea una cadena de texto ***/
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*** Validar que la excepción sea un arreglo y contenga elementos ***/
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "m.id NOT IN ($excepcion)";
        }

        /*** Definir el orden de presentación de los datos ***/
        if ($this->listaAscendente) {
            $orden = "m.orden ASC";
        } else {
            $orden = "m.orden DESC";
        }

        $tablas = array(
            "m" => "menus",
        );

        $columnas = array(
            "id"      => "m.id",
            "nombre"  => "m.nombre",
            "orden"   => "m.orden",
            "destino" => "m.destino",
            "activo"  => "m.activo"
        );

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($menu = $sql->filaEnObjeto($consulta)) {
                $menu->url     = $this->urlBase."/".$menu->id;
                $paginas       = $sql->filaEnObjeto($sql->seleccionar(array("paginas"), array("paginas" => "COUNT(*)"), "id_menu = '".$menu->id."'"));
                $menu->paginas = $paginas->paginas;
                $lista[]       = $menu;
            }
        }

        return $lista;

    }
}
?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Noticias
 * @author      Pablo A. Vélez <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
class Noticia {

    /**
     * Código interno o identificador de la noticia en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de noticias
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de una noticia específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador de la noticia en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Código interno o identificador de la categoria a la cual pertenece la noticia
     * @var entero
     */
    public $idCategoria;

    /**
     * Nombre de usuario (login) del usuario creador de la noticia
     * @var cadena
     */
    public $usuarioAutor;

    /**
     * Sobrenombre o apodo del usuario creador de la noticia
     * @var cadena
     */
    public $autor;

    /**
     * Título de la noticia
     * @var cadena
     */
    public $titulo;

    /**
     * Resumen corto de la noticia
     * @var cadena
     */
    public $resumen;

    /**
     * Contenido completo de la noticia
     * @var cadena
     */
    public $contenido;

    /**
     * Código interno o identificador en la base de datos de la imagen relacionada con la noticia
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen de la noticia en tamaño normal
     * @var cadena
     */
    public $imagenPrincipal;

    /**
     * Ruta de la imagen de la noticia en miniatura
     * @var cadena
     */
    public $imagenMiniatura;

    /**
     * Fecha de creación de la noticia
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación de la noticia
     * @var fecha
     */
    public $fechaPublicacion;

    /**
     * Fecha de la última modificación de la noticia
     * @var fecha
     */
    public $fechaActualizacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de noticias
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
     * Codigos de los perfiles de usuario con los cuales es compartido un Blog
     * @var array
     */
    public $permisosPerfiles = array();


    /**
     * Número de visitas que tiene este item 
     * @var entero
     */
    public $cantidadVisitas = 0;

    /**
     * Número de comentarios que tiene este item 
     * @var entero
     */
    public $cantidadComentarios = 0;

    /**
     * Número de me gusta que tiene este item 
     * @var entero
     */
    public $cantidadMeGusta = 0;

    /**
     * Número de me galerias fotograficas que tiene este item
     * @var entero
     */
    public $cantidadGalerias = 0;

    /**
     * Inicializar la noticia
     * @param entero $id Código interno o identificador de la noticia en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('NOTICIAS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        $this->registros = $sql->obtenerValor('noticias', 'COUNT(id)', '');
     
        $this->registrosActivos = $sql->obtenerValor('noticias', 'COUNT(id)', 'activo = "1"');
        
        //Saber la cantidad de galerias que tiene este blog
        $this->cantidadGalerias = $sql->obtenerValor('galerias', 'COUNT(id)', 'id_modulo = "' . $this->idModulo . '" AND id_registro = "' . $this->id . '"');

        if (isset($id)) {
            $this->cargar($id);
            $this->permisosPerfiles = PermisosItem::cargarPerfiles($id, $modulo->id);
        }
    }

    /**
     * Cargar los datos de una noticia
     * @param entero $id Código interno o identificador de la noticia en la base de datos
     */
    private function cargar($id) {
        global $sql, $configuracion;

        if (is_numeric($id) && $sql->existeItem('noticias', 'id', intval($id))) {

            $tablas = array(
                'n' => 'noticias',
                'u' => 'usuarios',
                'p' => 'personas',
                'i' => 'imagenes'
            );

            $columnas = array(
                'id' => 'n.id',
                'idAutor' => 'n.id_usuario',
                'usuarioAutor' => 'u.usuario',
                'autor' => 'u.sobrenombre',
                'idImagen' => 'n.id_imagen',
                'imagen' => 'i.ruta',
                'resumen' => 'n.resumen',
                'titulo' => 'n.titulo',
                'contenido' => 'n.contenido',
                'fechaCreacion' => 'UNIX_TIMESTAMP(n.fecha_creacion)',
                'fechaPublicacion' => 'UNIX_TIMESTAMP(n.fecha_publicacion)',
                'fechaActualizacion' => 'UNIX_TIMESTAMP(n.fecha_actualizacion)',
                'idCategoria' => 'id_categoria',
                'activo' => 'n.activo',
		'cantidadVisitas' => 'n.visitas'
            );

            $condicion = 'n.id_usuario = u.id AND u.id_persona = p.id AND n.id_imagen = i.id AND n.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->id;
                $this->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $this->imagen;
                $this->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $this->imagen;
                //sumar una visita al blog
                $this->sumarVisita();
            }
        }
    }

    /**
     * Adicionar una noticia
     * @param  arreglo $datos       Datos de la noticia a adicionar
     * @return entero               Código interno o identificador de la noticia en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo, $archivo_imagen;
        //nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfiles = $datos['perfiles'];
        $datosVisibilidad = $datos['visibilidad'];


        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            $objetoImagen = new Imagen();

            $datosImagen = array(
                'idRegistro' => '',
                'modulo' => 'NOTICIAS',
                'descripcion' => 'Image of'.htmlspecialchars($datos['titulo']),
                'titulo' => 'Image of'.htmlspecialchars($datos['titulo'])
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }
        
        if(!empty($datos['id_imagen_evento'])){
            $idImagen = $datos['id_imagen_evento'];
        }


        $datosNoticia = array(
            'titulo' => htmlspecialchars($datos['titulo']),
            'resumen' => htmlspecialchars($datos['resumen']),
            'contenido' => Variable::filtrarTagsInseguros($datos['contenido']),
            'id_categoria' => htmlspecialchars($datos['categorias']),
            'id_usuario' => $sesion_usuarioSesion->id,
            'id_imagen' => $idImagen,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        );

        if (isset($datos['activo'])) {
            $datosNoticia['activo'] = '1';
            $datosNoticia['fecha_publicacion'] = date('Y-m-d H:i:s');
        } else {
            $datosNoticia['activo'] = '0';
            $datosNoticia['fecha_publicacion'] = NULL;
        }

        $consulta = $sql->insertar('noticias', $datosNoticia);
        $idItem = $sql->ultimoId;

        if ($consulta) {
            if ($datos['cantCampoImagenGaleria']) {//si viene alguna imagen se crea la galeria             
                $galeria = new Galeria();
                $datos['id_modulo_actual'] = $this->idModulo;
                $datos['id_registro_actual'] = $idItem;
                $galeria->adicionar($datos);
            }
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;

            $permisosItem->insertarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);

            return $idItem;
        } else {
            return false;
        }
    }

    /**
     * Modificar una noticia
     * @param  arreglo $datos       Datos de la noticia a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $modulo, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
        }
        // datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfiles = $datos['perfiles'];
        $datosVisibilidad = $datos['visibilidad'];

        if (isset($datos['activo'])) {
            $datos['activo'] = '1';
            $datos['fecha_publicacion'] = date('Y-m-d H:i:s');
        } else {
            $datos['activo'] = '0';
            $datos['fecha_publicacion'] = NULL;
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
                'idRegistro' => $this->id,
                'modulo' => 'NOTICIAS',
                'titulo' => 'Image of '.htmlspecialchars($datos['titulo']),
                'descripcion' => 'Image of '.htmlspecialchars($datos['titulo'])
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }

        $datos = array(
            'titulo' => htmlspecialchars($datos['titulo']),
            'resumen' => htmlspecialchars($datos['resumen']),
            'contenido' => Variable::filtrarTagsInseguros($datos['contenido']),
            'id_categoria' => htmlspecialchars($datos['categorias']),
	    'id_imagen' => $idImagen,
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        );


        $consulta = $sql->modificar('noticias', $datos, 'id = "' . $this->id . '"');

        if ($consulta) {
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;
            $idItem = $this->id;

            $permisosItem->modificarPerfilesCompartidos($datosVisibilidad, $idModulo, $idItem, $datosPerfiles);

            return $consulta;
        } else {
            return false;
        }
    }

    /**
     * Eliminar una noticia
     * @param entero $id    Código interno o identificador de la noticia en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('noticias', 'id = "' . $this->id . '"');

        if ($consulta) {
            /* Eliminar todos los comentarios que pueda tener la Noticia */
            if ($this->cantidadComentarios > 0) {
                $comentario = new Comentario();
                $comentario->eliminarComentarios($this->id, $this->idModulo);
            }
            /* Eliminar todos los "me gusta" que pueda tener la Noticia */
            if ($this->cantidadMeGusta > 0) {
                $destacado = new Destacado();
                $destacado->eliminarTodosDestacados($this->idModulo, $this->id);
            }

            /* Eliminar todas las galerias que pueda tener el Blog */
            if ($this->cantidadGalerias > 0) {
                $galeria = new Galeria();
                $galeria->eliminarGalerias($this->idModulo, $this->id);
            }

	    $objetoImagen = new Imagen($this->idImagen);
	    $objetoImagen->eliminar();

            $permisosItem = new PermisosItem();
            if (!($permisosItem->eliminar($this->id, $this->idModulo))) {
                return false;
            } else {
                return true;
            }

            return $consulta;
        } else {
            return false;
        }
    }

    /**
     * Listar las noticias
     * @param entero  $cantidad    Número de noticias a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de noticias
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfilUsuario = NULL, $idModulo = NULL, $filtroCategoria = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;
        ;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'n.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'n.fecha_publicacion DESC';
        } else {
            $orden = 'n.fecha_publicacion DESC';
        }

        //compruebo que se le haya pasado un valor al idPerfil

        $idPerfil = $idPerfilUsuario;

        $tablas = array(
            'n' => 'noticias',
            'u' => 'usuarios',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'n.id',
            'idAutor' => 'n.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'idImagen' => 'n.id_imagen',
            'imagen' => 'i.ruta',
            'titulo' => 'n.titulo',
            'resumen' => 'n.resumen',
            'contenido' => 'n.contenido',
            'fechaCreacion' => 'UNIX_TIMESTAMP(n.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(n.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(n.fecha_actualizacion)',
            'activo' => 'n.activo',
	    'numeroVisitas' => 'n.visitas'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'n.id_usuario = u.id AND n.id_imagen = i.id';

        //filtro de categoria
        if (!empty($filtroCategoria) && $filtroCategoria != 'my_item') {
            $condicion .= ' AND n.id_categoria = "' . $filtroCategoria . '"'; //filtro de categoria
        }

        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {

                if (!empty($filtroCategoria) && $filtroCategoria == "my_item") {
                    $condicion .= ' AND n.id_usuario = "' . $sesion_usuarioSesion->id . '"'; //filtro de categoria
                } else {

                    //codigo para perfiles que pueden ver items de varios perfiles
                    $condicion2 = '';
                    $tienePrivilegios = $sql->obtenerValor('tipos_usuario', 'otros_perfiles', 'id = "' . $sesion_usuarioSesion->idTipo . '"');
                    if (isset($sesion_usuarioSesion) && $tienePrivilegios) {
                        //print_r($otrosPerfiles);
                        $otrosPerfiles = implode(',', Perfil::verOtrosPerfiles());
                        $condicion2 = ', ' . $otrosPerfiles . ' '; //condicion extra para ver items de los otros perfiles sobre los cuales tiene privilegios                  
                    }

                    $tablas['pi'] = 'permisos_item';
                    $columnas['idItem'] = 'pi.id_item';
                    $columnas['idPerfil'] = 'pi.id_perfil';
                    $columnas['idModulo'] = 'pi.id_modulo';

                    $condicion .= " AND ( (n.id  = pi.id_item AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil'$condicion2) )";
                    $condicion .= " OR (n.id_usuario = '$sesion_usuarioSesion->id'";

//                    if (!empty($filtroCategoria)) {
//                        $condicion .= " AND n.id_categoria = '" . $filtroCategoria . "'"; //filtro de categoria
//                    }

                    $condicion .= ') )';
                }//fin del if(!empty($filtroCategoria)..)
            }//fin del if($idPerfil != 0)                
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= ' AND n.id  = pi.id_item AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" )';

            if (!empty($filtroCategoria)) {
                $condicion .= ' AND id_categoria = "' . $filtroCategoria . '"'; //filtro de categoria
            }
        }

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'n.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($noticia = $sql->filaEnObjeto($consulta)) {
                $noticia->url = $this->urlBase . '/' . $noticia->id;
                $noticia->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $noticia->imagen;
                $noticia->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $noticia->imagen;
                $lista[] = $noticia;
            }
        }

        return $lista;
    }
//fin del metodo listar

    /**
     * Listar las noticias
     * @param entero  $cantidad    Número de noticias a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de noticias
     */
    public function listarMasVisitadas($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfil = NULL, $idModulo = NULL, $filtroCategoria = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;
        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }
        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }
        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }
        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'n.id NOT IN (' . $excepcion . ')';
        }
        /*         * * Definir el orden de presentación de los datos ** */
        $orden = 'n.visitas DESC';

        $tablas = array(
            'n' => 'noticias',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'n.id',
            'idAutor' => 'n.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'idImagen' => 'n.id_imagen',
            'imagen' => 'i.ruta',
            'titulo' => 'n.titulo',
            'resumen' => 'n.resumen',
            'contenido' => 'n.contenido',
            'fechaCreacion' => 'UNIX_TIMESTAMP(n.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(n.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(n.fecha_actualizacion)',
            'activo' => 'n.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'n.id_usuario = u.id AND u.id_persona = p.id AND n.id_imagen = i.id';

        //filtro de categoria
        if (!empty($filtroCategoria) && $filtroCategoria != 'my_item') {
            $condicion .= ' AND id_categoria = "' . $filtroCategoria . '"'; //filtro de categoria
        }

        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {

                if (!empty($filtroCategoria) && $filtroCategoria == 'my_item') {
                    $condicion .= " AND n.id_usuario = '" . $sesion_usuarioSesion->id . "'"; //filtro de categoria
                } else {

                    //codigo para perfiles que pueden ver items de varios perfiles
                    $condicion2 = "";
                    $tienePrivilegios = $sql->obtenerValor('tipos_usuario', 'otros_perfiles', "id = '" . $sesion_usuarioSesion->idTipo . "'");
                    if (isset($sesion_usuarioSesion) && $tienePrivilegios) {
                        //print_r($otrosPerfiles);
                        $otrosPerfiles = implode(",", Perfil::verOtrosPerfiles());
                        $condicion2 = ", $otrosPerfiles "; //condicion extra para ver items de los otros perfiles sobre los cuales tiene privilegios                  
                    }

                    $tablas['pi'] = 'permisos_item';
                    $columnas['idItem'] = 'pi.id_item';
                    $columnas['idPerfil'] = 'pi.id_perfil';
                    $columnas['idModulo'] = 'pi.id_modulo';

                    $condicion .= " AND pi.id_item = n.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil'$condicion2)";
                    $condicion .= "OR (n.id_usuario = u.id AND u.id_persona = p.id AND n.id_imagen = i.id AND n.id_usuario = '$sesion_usuarioSesion->id'";

                    if (!empty($filtroCategoria)) {
                        $condicion .= " AND n.id_categoria = '" . $filtroCategoria . "'"; //filtro de categoria
                    }

                    $condicion .= ")";
                }//fin del if(!empty($filtroCategoria)..)
            }//fin del if($idPerfil != 0)                
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= " AND pi.id_item = n.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' )";

            if (!empty($filtroCategoria)) {
                $condicion .= " AND id_categoria = '" . $filtroCategoria . "'"; //filtro de categoria
            }
        }

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'n.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($noticia = $sql->filaEnObjeto($consulta)) {
                $noticia->url = $this->urlBase . '/' . $noticia->id;
                $noticia->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $noticia->imagen;
                $noticia->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $noticia->imagen;
                $lista[] = $noticia;
            }
        }

        return $lista;
    }

//fin del metodo listar

    /**
     * Listar las noticias que le gustan al usuario que ha iniciado la sesion
     * @param entero  $cantidad    Número de noticias a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de noticias que tienen un "me gusta" por parte del usuario que ha iniciado la sesion
     */
    public function listarMeGusta($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;
        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }
        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }
        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }
        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'n.id NOT IN (' . $excepcion . ')';
        }
        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'n.fecha_publicacion ASC';
        } else {
            $orden = 'n.fecha_publicacion ASC';
        }

        $tablas = array(
            'n' => 'noticias',
            'd' => 'destacados',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'n.id',
            'idAutor' => 'n.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'idImagen' => 'n.id_imagen',
            'imagen' => 'i.ruta',
            'titulo' => 'n.titulo',
            'resumen' => 'n.resumen',
            'contenido' => 'n.contenido',
            'fechaCreacion' => 'UNIX_TIMESTAMP(n.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(n.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(n.fecha_actualizacion)',
            'activo' => 'n.activo',
            'noticia' => 'd.id_item'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= "n.id_usuario = u.id AND u.id_persona = p.id AND n.id_imagen = i.id AND n.id = d.id_item AND d.id_modulo = '" . $this->idModulo . "' AND d.id_usuario = '" . $sesion_usuarioSesion->id . "'";


        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);
        $lista = array();
        if ($sql->filasDevueltas) {

            while ($noticia = $sql->filaEnObjeto($consulta)) {
                $noticia->url = $this->urlBase . '/' . $noticia->id;
                $noticia->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $noticia->imagen;
                $noticia->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $noticia->imagen;
                $lista[] = $noticia;
            }
        }

        return $lista;
    }

//fin del metodo listarMeGusta

    /**
     * Metodo que devuelve un listado con las noticias(aquí en el metodo directamente se arman con el html y css) 
     * las cuales el usuario que ha iniciado la sesion ha hecho click en "me gusta"
     * 
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @return type array ->arreglo con el listado de noticias (ya listo para desplegar en el navegador) 
     *                      a las cuales el usuario que tiene la sesion actual ha hecho click en "me gusta"
     */
    public function NoticiasDestacadas() {
        global $configuracion, $textos, $sesion_usuarioSesion;

        $bloqueNoticias = '';
        $arregloNoticias = self::listarMeGusta(0, 5, '', '');

        if (sizeof($arregloNoticias) > 0) {
            foreach ($arregloNoticias as $elemento) {

                $item = '';

                if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $elemento->idAutor)) {
                    $botones = '';
                    $botones .= HTML::botonModificarItem($elemento->id, $this->urlBase);
                    $botones .= HTML::botonEliminarItem($elemento->id, $this->urlBase);
                    $item .= HTML::contenedor($botones, 'oculto flotanteDerecha');
                }

                if ($elemento->activo) {

                    $comentario = new Comentario();

                    $contenedorComentarios = $comentario->mostrarComentarios($this->idModulo, $elemento->id);
                    $contenedorMeGusta = Recursos::mostrarContadorMeGusta($this->idModulo, $elemento->id);
                    $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, 'mostrarPosted');
                    //seleccionar el genero de una persona 
                    $persona = new Persona($elemento->idAutor);
                    $item = HTML::enlace(HTML::imagen($elemento->imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('NOTICIAS', $elemento->id));
                    $item .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $persona->idGenero . '.png') . preg_replace('/\%1/', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)) . 'On ' . HTML::frase(date('D, d M Y', $elemento->fechaPublicacion), 'pequenia cursiva negrilla') . $comentarios, $textos->id('PUBLICADO_POR')));
                    $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                    $item2 .= HTML::parrafo($elemento->resumen, 'pequenia cursiva');
                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris

                    $listaNoticias[] = $item;
                }
            }//fin del foreach

            $listaNoticias[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('NOTICIAS', '', '', '', 'i_like'), 'flotanteCentro margenSuperior');
        } else {
            $listaNoticias[] = $textos->id('NO_TIENES_NOTICIAS_QUE_TE_GUSTEN');
        }

        $bloqueNoticias .= HTML::lista($listaNoticias, 'listaVertical bordeSuperiorLista', 'botonesOcultos', '');

        return $bloqueNoticias;
    }

//fin del metodo Noticias que me gustan

    /**
     * Listar mas noticias de un usuario determinado
     * @param entero  $cantidad    Número de noticias a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de noticias
     */
    public function listarMasNoticiasUsuario($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfilUsuario = NULL, $idModulo = NULL, $filtroCategoria = NULL, $idUsuarioPropietario = NULL, $idNoticiaActual = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;
        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }
        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }
        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }
        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'n.id NOT IN (' . $excepcion . ')';
        }
        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'n.fecha_publicacion DESC';
        } else {
            $orden = 'n.fecha_publicacion DESC';
        }
        //compruebo que se le haya pasado un valor al idPerfil
        $idPerfil = $idPerfilUsuario;

        $tablas = array(
            'n' => 'noticias',
            'u' => 'usuarios',
            'p' => 'personas',
            'i' => 'imagenes'
        );

        $columnas = array(
            'id' => 'n.id',
            'idAutor' => 'n.id_usuario',
            'usuarioAutor' => 'u.usuario',
            'autor' => 'u.sobrenombre',
            'idImagen' => 'n.id_imagen',
            'imagen' => 'i.ruta',
            'titulo' => 'n.titulo',
            'resumen' => 'n.resumen',
            'contenido' => 'n.contenido',
            'fechaCreacion' => 'UNIX_TIMESTAMP(n.fecha_creacion)',
            'fechaPublicacion' => 'UNIX_TIMESTAMP(n.fecha_publicacion)',
            'fechaActualizacion' => 'UNIX_TIMESTAMP(n.fecha_actualizacion)',
            'activo' => 'n.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'n.id_usuario = "' . $idUsuarioPropietario . '" AND n.id != "' . $idNoticiaActual . '" AND n.id_usuario = u.id AND u.id_persona = p.id AND n.id_imagen = i.id';

        //filtro de categoria
        if (!empty($filtroCategoria) && $filtroCategoria != 'my_item') {
            $condicion .= ' AND id_categoria = "' . htmlspecialchars($filtroCategoria) . '"'; //filtro de categoria
        }
        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {
                if (!empty($filtroCategoria) && $filtroCategoria == 'my_item') {
                    $condicion .= ' AND n.id_usuario = "' . $sesion_usuarioSesion->id . '"'; //filtro de categoria
                } else {
                    //codigo para perfiles que pueden ver items de varios perfiles
                    $condicion2 = '';
                    $tienePrivilegios = $sql->obtenerValor('tipos_usuario', 'otros_perfiles', 'id = "' . $sesion_usuarioSesion->idTipo . '"');
                    if (isset($sesion_usuarioSesion) && $tienePrivilegios) {
                        $otrosPerfiles = implode(',', Perfil::verOtrosPerfiles());
                        $condicion2 = ", $otrosPerfiles "; //condicion extra para ver items de los otros perfiles sobre los cuales tiene privilegios                  
                    }

                    $tablas['pi'] = 'permisos_item';
                    $columnas['idItem'] = 'pi.id_item';
                    $columnas['idPerfil'] = 'pi.id_perfil';
                    $columnas['idModulo'] = 'pi.id_modulo';

                    $condicion .= " AND pi.id_item = n.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil'$condicion2)";
                    $condicion .= "OR (n.id_usuario = '" . $idUsuarioPropietario . "' AND n.id != '" . $idNoticiaActual . "' AND n.id_usuario = u.id AND u.id_persona = p.id AND n.id_imagen = i.id AND n.id_usuario = '$sesion_usuarioSesion->id'";

                    if (!empty($filtroCategoria)) {
                        $condicion .= ' AND n.id_categoria = "' . htmlspecialchars($filtroCategoria) . '"'; //filtro de categoria
                    }

                    $condicion .= ')';
                }//fin del if(!empty($filtroCategoria)..)
            }//fin del if($idPerfil != 0)                
        } else {
            $tablas['pi'] = 'permisos_item';
            $columnas['idItem'] = 'pi.id_item';
            $columnas['idPerfil'] = 'pi.id_perfil';
            $columnas['idModulo'] = 'pi.id_modulo';

            $condicion.= ' AND pi.id_item = n.id AND pi.id_modulo = "' . $idModulo . '" AND pi.id_perfil IN ( "99" )';

            if (!empty($filtroCategoria)) {
                $condicion .= ' AND id_categoria = "' . htmlspecialchars($filtroCategoria) . '"'; //filtro de categoria
            }
        }

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'n.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($noticia = $sql->filaEnObjeto($consulta)) {
                $noticia->url = $this->urlBase . '/' . $noticia->id;
                $noticia->imagenPrincipal = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $noticia->imagen;
                $noticia->imagenMiniatura = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesMiniaturas'] . '/' . $noticia->imagen;
                $lista[] = $noticia;
            }
        }

        return $lista;
    }

//fin del metodo listar

    /**
     * Metodo que se encarga de armar el acordeon que aparece al ver una noticia con otras noticias del usuario propietario
     * de la noticia que se esta viendo actualmente
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @param type $idUsuario
     * @param type $idNoticiaActual
     * @return type 
     */
    public function masNoticiasUsuario($idUsuario, $idNoticiaActual) {
        global $textos, $configuracion, $sesion_usuarioSesion;

        if (!isset($idUsuario)) {
            return false;
        }
        /* Capturar el tipo de usuario que tiene el usuario actual */
        if (isset($sesion_usuarioSesion)) {
            $idTipo = $sesion_usuarioSesion->idTipo;
        } else {
            $idTipo = 99;
        }

        $arregloNoticias = $this->listarMasNoticiasUsuario(0, 5, '', '', $idTipo, $this->idModulo, '', $idUsuario, $idNoticiaActual);
        $listaMasNoticias = array($textos->id('MAS_NOTICIAS_DE_ESTE_USUARIO'));
        $listaNoticias = array();

        if (sizeof($arregloNoticias) > 0) {
            foreach ($arregloNoticias as $elemento) {
                $item = '';

                if ($elemento->activo) {
                    $comentario = new Comentario();

                    $contenedorComentarios = $comentario->mostrarComentarios($elemento->idModulo, $elemento->id);
                    $contenedorMeGusta = Recursos::mostrarContadorMeGusta($elemento->idModulo, $elemento->id);
                    $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, 'mostrarPosted');
                    //seleccionar el genero de una persona 
                    $usuario = new Usuario();
                    $item .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('NOTICIAS', $elemento->id));
                    $item .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $usuario->getGenero($elemento->idAutor) . '.png') . preg_replace('/\%1/', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)) . 'On ' . HTML::frase(date('D, d M Y', $elemento->fechaPublicacion), 'pequenia cursiva negrilla') . $comentarios, $textos->id('PUBLICADO_POR')));
                    $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, 'negrilla'), $elemento->url);
                    $item2 .= HTML::parrafo($elemento->resumen, 'pequenia cursiva');
                    $item .= HTML::contenedor($item2, 'fondoUltimos5GrisB'); //barra del contenedor gris

                    $item = HTML::contenedor($item, 'contenedorListaMasNoticias', 'contenedorListaNoticias' . $elemento->id);

                    $listaNoticias[] = $item;
                }//fin del  SI Blog es activo
            }//fin del foreach

            $acordeon = HTML::acordeonLargo2($listaMasNoticias, $listaNoticias, 'masNoticias' . $idNoticiaActual, '');
        }//fin del if  
        return $acordeon;
    }

//fin del metodo mas noticias usuario

    /**
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL  object -> objeto sql para interacciones con la BD
     * @return type boolean ->     verdadero si se realizo la actividad sin problema
     */
    public function sumarVisita() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $numVisitas = $sql->obtenerValor('noticias', 'visitas', 'id = "' . $this->id . '"');

        $datosNoticia['visitas'] = $numVisitas + 1;

	$sql->guardarBitacora = false;
        $sumVisita = $sql->modificar('noticias', $datosNoticia, 'id = "' . $this->id . '"');

        if ($sumVisita) {
            return true;
        } else {
            return false;
        }
    }

//fin del metodo sumar visita
}

//fin de la clase Noticias
?>
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Paginas
 * @author      Francisco J. Lozano c. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

class Pagina {

    /**
     * Código interno o identificador de la página en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Código interno o identificador del menú al cual pertenece la página en la base de datos
     * @var entero
     */
    public $idMenu;

    /**
     * Nombre del menú al cual pertenece la página
     * @var cadena
     */
    public $menu;

    /**
     * Valor numérico que determina el orden o la posición de la página en la base de datos
     * @var entero
     */
    public $orden;

    /**
     * URL relativa del módulo de páginas
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de una página específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador de la página en la base de datos
     * @var entero
     */
    public $idAutor;
    
     /**
     * Código interno o identificador del usuario creador de la página en la base de datos
     * @var entero
     */
    public $idModulo;

    /**
     * Sobrenombre o apodo del usuario creador de la página
     * @var cadena
     */
    public $autor;

    /**
     * Ruta de la imagen (miniatura) que representa al usuario creador de la página
     * @var cadena
     */
    public $fotoAutor;

    /**
     * Título de la página
     * @var cadena
     */
    public $titulo;

    /**
     * Contenido completo de la página
     * @var cadena
     */
    public $contenido;

    /**
     * Fecha de creación de la página
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicación de la página
     * @var fecha
     */
    public $fechaPublicacion;

    /**
     * Fecha de la última modificación de la página
     * @var fecha
     */
    public $fechaActualizacion;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador de disponibilidad de bloque multimedia
     * @var lógico
     */
    public $multimedia;

    /**
     * Indicador del orden cronológio de la lista de páginas
     * @var lógico
     */
    public $listaAscendente = true;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     *
     * Inicializar el página
     *
     * @param entero $id Código interno o identificador de la página en la base de datos
     *
     */
    public function __construct($id = NULL) {

        $modulo         = new Modulo("PAGINAS");
        $this->urlBase  = "/".$modulo->url;
        $this->url      = $modulo->url;
        $this->idModulo = $modulo->id;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos de un página
     *
     * @param entero $id Código interno o identificador de la página en la base de datos
     *
     */
    public function cargar($id) {
        global $sql;

        if (isset($id) && $sql->existeItem("paginas", "id", intval($id))) {

            $tablas = array(
                "g" => "paginas",
                "m" => "menus",
                "u" => "usuarios",
                "p" => "personas",
                "i" => "imagenes"
            );

            $columnas = array(
                "id"                 => "g.id",
                "idMenu"             => "g.id_menu",
                "menu"               => "m.nombre",
                "orden"              => "g.orden",
                "idAutor"            => "g.id_usuario",
                "autor"              => "u.sobrenombre",
                "fotoAutor"          => "i.ruta",
                "titulo"             => "g.titulo",
                "destino"            => "m.destino",
                "contenido"          => "g.contenido",
                "fechaCreacion"      => "UNIX_TIMESTAMP(g.fecha_creacion)",
                "fechaPublicacion"   => "UNIX_TIMESTAMP(g.fecha_publicacion)",
                "fechaActualizacion" => "UNIX_TIMESTAMP(g.fecha_actualizacion)",
                "activo"             => "g.activo",
		"multimedia"         => "g.multimedia"
            );

            $condicion = "g.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND g.id_menu = m.id AND g.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase."/".$this->destino;
            }
        }
    }

    /**
     *
     * Adicionar un página
     *
     * @param  arreglo $datos       Datos de la página a adicionar
     * @return entero               Código interno o identificador de la página en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion;

        $paginas = $sql->seleccionar(array("paginas"), array("orden" => "MAX(orden)"), "id_menu = '".$datos["menu"]."'");
        $orden   = 0;

        if ($sql->filasDevueltas == 1) {

            $pagina = $sql->filaEnObjeto($paginas);
            $orden  = $pagina->orden + 50000;
            $orden /= 2;

        } elseif ($sql->filasDevueltas == 0) {
            $orden = 500000;
        }

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";

        } else {
            $datos["activo"] = "0";
        }

        if (isset($datos["multimedia"])) {
            $datos["multimedia"] = "1";

        } else {
            $datos["multimedia"] = "0";
        }

        $datos = array(
            "orden"                 => $orden,
            "titulo"                => $datos["titulo"],
            "contenido"             => $datos["contenido"],
            "id_menu"               => $datos["menu"],
            "id_usuario"            => $sesion_usuarioSesion->id,
            "fecha_creacion"        => date("Y-m-d H:i:s"),
            "fecha_publicacion"     => date("Y-m-d H:i:s"),
            "fecha_actualizacion"   => date("Y-m-d H:i:s"),
            "activo"                => $datos["activo"]
        );

        $consulta = $sql->insertar("paginas", $datos);

        if ($consulta) {
            return $sql->ultimoId;

        } else {
            return NULL;
        }
    }

    /**
     *
     * Modificar un página
     *
     * @param  arreglo $datos       Datos de la página a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";

        } else {
            $datos["activo"] = "0";
        }


        if (isset($datos["multimedia"])) {
            $datos["multimedia"] = "1";

        } else {
            $datos["multimedia"] = "0";
        }

        $datos = array(
            "titulo"              => $datos["titulo"],
            "contenido"           => $datos["contenido"],
            "id_menu"             => $datos["menu"],
            "fecha_actualizacion" => date("Y-m-d H:i:s"),
            "activo"              => $datos["activo"]
        );


        $consulta = $sql->modificar("paginas", $datos, "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Eliminar un página
     *
     * @param entero $id    Código interno o identificador de la página en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("paginas", "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Subir de nivel una página
     *
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function subir() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array("paginas"), array("id", "orden"), "orden < '".$this->orden."'", "id", "orden DESC", 0, 1);

        if ($sql->filasDevueltas) {
            $pagina      = $sql->filaEnObjeto($consulta);
            $temporal  = ($this->orden + $pagina->orden)/2;
            $sql->modificar("paginas",array("orden" => $temporal), "id = '".$this->id."'");
            $sql->modificar("paginas",array("orden" => $this->orden), "id = '".$pagina->id."'");
            $sql->modificar("paginas",array("orden" => $pagina->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     *
     * Bajar de nivel una página
     *
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function bajar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array("paginas"), array("id", "orden"), "orden > '".$this->orden."'", "id", "orden ASC", 0, 1);

        if ($sql->filasDevueltas) {
            $pagina      = $sql->filaEnObjeto($consulta);
            $temporal  = ($this->orden + $pagina->orden)/2;
            $sql->modificar("paginas",array("orden" => $temporal), "id = '".$this->id."'");
            $sql->modificar("paginas",array("orden" => $this->orden), "id = '".$pagina->id."'");
            $sql->modificar("paginas",array("orden" => $pagina->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     *
     * Listar las páginas
     *
     * @param entero  $cantidad    Número de páginas a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de páginas
     *
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion, $modulo;

        /*** Validar la fila inicial de la consulta ***/
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*** Validar la cantidad de registros requeridos en la consulta ***/
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*** Validar que la condición sea una cadena de texto ***/
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*** Validar que la excepción sea un arreglo y contenga elementos ***/
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "g.id NOT IN ($excepcion)";
        }

        /*** Definir el orden de presentación de los datos ***/
        if ($this->listaAscendente) {
            $orden = "m.nombre ASC, g.orden ASC";
        } else {
            $orden = "m.nombre ASC, g.orden DESC";
        }

        $tablas = array(
            "g" => "paginas",
            "m" => "menus",
            "u" => "usuarios",
            "p" => "personas",
            "i" => "imagenes"
        );

        $columnas = array(
            "id"                 => "g.id",
            "idMenu"             => "g.id_menu",
            "menu"               => "m.nombre",
            "orden"              => "g.orden",
            "idAutor"            => "g.id_usuario",
            "autor"              => "u.sobrenombre",
            "fotoAutor"          => "i.ruta",
            "titulo"             => "g.titulo",
            "contenido"          => "g.contenido",
            "fechaCreacion"      => "UNIX_TIMESTAMP(g.fecha_creacion)",
            "fechaPublicacion"   => "UNIX_TIMESTAMP(g.fecha_publicacion)",
            "fechaActualizacion" => "UNIX_TIMESTAMP(g.fecha_actualizacion)",
            "activo"             => "g.activo",
	    "multimedia"         => "g.multimedia"
        );

        if (!empty($condicion)) {
            $condicion .= " AND ";
        }

        $condicion .= "g.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND g.id_menu = m.id";

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($pagina = $sql->filaEnObjeto($consulta)) {
                $pagina->url = $this->urlBase."/".$pagina->id;
                $lista[]   = $pagina;
            }
        }

        return $lista;

    }
}
?>
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Paises
 * @author      Francisco J. Lozano c. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

/**
 * Clase Pais: clase encargada de gestionar la informacion de los registros sobre los paises almacenados en el sistema.
 * es una clase que contiene en su mayoria metodos crud para la gestion con la bd, ademas de esto tiene algunos metodos
 * para el renderizado de informacion, como por ejemplo el metodo generar tabla.Esta clase mantiene una relacion directa
 * con las clases paises y ciudades, ya que una ciudad pertenece a un estado y un estado pertenece a un pais.
 */
class Pais {

    /**
     * Código interno o identificador del país en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de paises
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un pais específico
     * @var cadena
     */
    public $url;

    /**
     * Nombre del país
     * @var cadena
     */
    public $nombre;

    /**
     * Código ISO del país
     * @var cadena
     */
    public $codigo;

    /**
     * Indicador del orden cronológio de la lista de paises
     * @var lógico
     */
    public $listaAscendente = true;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     *
     * Inicializar el pais
     *
     * @param entero $id Código interno o identificador del país en la base de datos
     *
     */
    public function __construct($id = NULL) {
        $modulo        = new Modulo("PAISES");
        $this->urlBase = "/".$modulo->url;
        $this->url     = $modulo->url;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     *
     * Cargar los datos de un pais
     *
     * @param entero $id Código interno o identificador del país en la base de datos
     *
     */
    public function cargar($id) {
        global $sql;

        if (isset($id) && $sql->existeItem("paises", "id", intval($id))) {

            $tablas = array(
                "p" => "paises"
            );

            $columnas = array(
                "id"     => "p.id",
                "nombre" => "p.nombre",
                "codigo" => "p.codigo_iso"
            );

            $condicion = "p.id = '$id'";

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
     * Adicionar un pais
     *
     * @param  arreglo $datos       Datos del país a adicionar
     * @return entero               Código interno o identificador del país en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql;

        $consulta = $sql->insertar("paises", $datos);

        if ($consulta) {
            return $sql->ultimoId;

        } else {
            return NULL;
        }
    }

    /**
     *
     * Modificar un pais
     *
     * @param  arreglo $datos       Datos del país a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->modificar("paises", $datos, "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Eliminar un pais
     *
     * @param entero $id    Código interno o identificador del país en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("paises", "id = '".$this->id."'");
        return $consulta;
    }

    /**
     *
     * Listar los paises
     *
     * @param entero  $cantidad    Número de paises a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de paises
     *
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion, $modulo;

        /*** Validar la fila inicial de la consulta ***/
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*** Validar la cantidad de registros requeridos en la consulta ***/
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*** Validar que la condición sea una cadena de texto ***/
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*** Validar que la excepción sea un arreglo y contenga elementos ***/
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "p.id NOT IN ($excepcion)";
        }

        /*** Definir el orden de presentación de los datos ***/
        if ($this->listaAscendente) {
            $orden = "p.nombre ASC";
        } else {
            $orden = "p.nombre DESC";
        }

        $tablas = array(
            "p" => "paises",
        );

        $columnas = array(
            "id"     => "p.id",
            "nombre" => "p.nombre",
            "codigo" => "p.codigo_iso",
        );

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($pais = $sql->filaEnObjeto($consulta)) {
                $pais->url = $this->urlBase."/".$pais->id;
                
                $lista[]   = $pais;
            }
        }

        return $lista;

    }
}
?>
<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Perfiles
 * @author      Francisco J. Lozano c. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/
/**
 * Clase Perfil: clase encargada de gestionar la informacion de los registros sobre los perfiles almacenados en el sistema.
 * es una clase que contiene  metodos crud para la gestion con la bd, ademas de esto tiene algunos metodos
 * para el renderizado del bloque con los checkboxes (bloque que se encuentra en los formularios de adicionar o modificar en todos los 
 * modulos) que se utilizan al gestionar los permisos de visualizacion y adicion sobre los items
 * en cada uno de los modulos.
 */
class Perfil {

    /**
     * Código interno o identificador del perfil en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Valor numérico que determina el orden o la posición del perfil en la base de datos
     * @var entero
     */
    public $orden;

    /**
     * URL relativa del módulo de perfiles
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un perfil específico
     * @var cadena
     */
    public $url;

    /**
     * Nombre del perfil
     * @var cadena
     */
    public $nombre;

    /**
     * Indicador del orden cronológio de la lista de perfiles
     * @var lógico
     */
    public $listaAscendente = true;

    /**
     * Número de registros de la lista
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
     * @param entero $id Código interno o identificador del perfil en la base de datos
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
     * @param entero $id Código interno o identificador del perfil en la base de datos
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
     * @return entero               Código interno o identificador del perfil en la base de datos (NULL si hubo error)
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
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
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
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
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

            $sql->eliminar("permisos", "id_tipo_usuario = '".$this->id."'");
            $sql->eliminar("permisos_item", "id_perfil = '".$this->id."'");
            $sql->eliminar("relacion_perfiles", "perfil_padre = '".$this->id."'");
            $consulta = $sql->eliminar("tipos_usuario", "id = '".$this->id."'");
            return $consulta;
        }

    }

    /**
     *
     * Subir de nivel un perfil
     *
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
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

            $sql->modificar("tipos_usuario",array("orden" => $temporal), "id = '".$this->id."'");
            $sql->modificar("tipos_usuario",array("orden" => $this->orden), "id = '".$perfil->id."'");
            $sql->modificar("tipos_usuario",array("orden" => $perfil->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     *
     * Bajar de nivel un perfil
     *
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
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

            $sql->modificar("tipos_usuario",array("orden" => $temporal), "id = '".$this->id."'");
            $sql->modificar("tipos_usuario",array("orden" => $this->orden), "id = '".$perfil->id."'");
            $sql->modificar("tipos_usuario",array("orden" => $perfil->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     *
     * Listar los perfiles
     *
     * @param entero  $cantidad    Número de perfiles a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
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

        /*** Validar que la condición sea una cadena de texto ***/
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*** Validar que la excepción sea un arreglo y contenga elementos ***/
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion).",0";

        } else {
            $excepcion .= "0";
        }

        $condicion .= "t.id NOT IN ($excepcion)";

        /*** Definir el orden de presentación de los datos ***/
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


        $frase = HTML::frase($textos->id("PERMISOS_VISUALIZACION"), "negrilla")." : ";

        if(!empty($id)){
            if(in_array(99, $perfiles)){ 
                        $cod3 .= HTML::parrafo($frase.HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");
                }else{                          
                        $cod3 .= HTML::parrafo($frase.HTML::radioBoton("datos[visibilidad]", "", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad]", "si", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");
            }
        }else{
             $cod3 .= HTML::parrafo($frase.HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");

        }

        //$cod3 = HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");


      return $cod3.$cod2;

    }//fin del metodo mostrarChecks Modificar



/**
*
*Metodo para verificar los permisos para añadir contenido del "tipo de usuario" del usuario que ha iniciado la sesion
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
    *Metodo para verificar los permisos para añadir contenido del "tipo de usuario" del usuario que ha iniciado la sesion
    *sobre determinado modulo.
    *
    **/
    public static function verificarPermisosAdicionItem($modulo, $item){
        global $sql, $sesion_usuarioSesion;
        
        if(is_string($modulo) && !is_numeric($modulo)){            
            $modulo = $sql->obtenerValor("modulos", "id", "BINARY nombre = '".$modulo."'");
        }

        if(!isset($sesion_usuarioSesion)){
            return NULL;
        }

        $perfil    = $sesion_usuarioSesion->idTipo;
        $condicion = "id_perfil = '".$perfil."' AND id_modulo = '".$modulo."'  AND id_item = '".$item."'";
        $permiso   = $sql->obtenerValor("permisos_adicion_item", "id_item", $condicion);
        return ($permiso) ? true : false;

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
        
        if(!$consulta){
            return NULL;
        }else{
            $sSql = $sql->seleccionar(array("relacion_perfiles"), array("perfil_hijo"), "perfil_padre = '".$sesion_usuarioSesion->idTipo."'");
            
            
            while ($perfil = $sql->filaEnObjeto($sSql)) {
                $perfiles[] = $perfil->perfil_hijo;
            }

        }

        return $perfiles;

    }
    
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
     * @global arreglo $configuracion arreglo global que contiene toda la informacion de configuracion del sistema (@see /codigo/configuracion/*)
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
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
   * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
   * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
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
   * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
   * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
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


/**
*
*Metodo que se encarga de mostrar los checkBoxes con los perfiles para seleccionar
*cual de los perfiles tendrá permiso de adición sobre un determinado item (uso principal Bulletin Board)
* --PA = permisos adicion
**/
   public static function mostrarChecksPermisosAdicion($id = NULL,  $idModulo = NULL){
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
               
               $perfiles = PermisosItem::cargarPerfilesPA($id, $idModulo);
               $seleccionado  = (in_array($elemento->id, $perfiles)) ? true : false;
               $cod.= HTML::campoChequeo("datos[perfiles_pa][$elemento->id]", $seleccionado).$elemento->nombre."<br>";

               }else{
                $cod.= HTML::campoChequeo("datos[perfiles_pa][$elemento->id]", false).$elemento->nombre."<br>"; 

              }//fin del if                

       }//fin del foreach 

      if(!empty($id)) {
            if(count($perfiles) > 0 && !in_array(99, $perfiles)){
                $opciones = array("style" => "display:block");
                $cod2 =  HTML::contenedor(HTML::parrafo($textos->id("SELECCIONAR_COMPARTIR_ITEM"), "centrado negrita").$cod, "listaCheckUsuarios", "listaCheckUsuariosPA", $opciones);
            }else{
                $opciones = array("style" => "display:none");
                $cod2 =  HTML::contenedor(HTML::parrafo($textos->id("SELECCIONAR_COMPARTIR_ITEM"), "centrado negrita").$cod, "listaCheckUsuarios", "listaCheckUsuariosPA", $opciones);
                
            }
       }else{
             $opciones = array("style" => "display:none");
             $cod2 =  HTML::contenedor(HTML::parrafo($textos->id("SELECCIONAR_COMPARTIR_ITEM"), "centrado negrita").$cod, "listaCheckUsuarios", "listaCheckUsuariosPA", $opciones);
                
       }

         //pongo los dos radiobutton que verifica si es publico a privado
        $opcionesPublico = array("onClick" => "$('#listaCheckUsuariosPA').css({ display: 'none'})"); //cargo las opciones, en este caso
        $opcionesPrivado = array("onClick" => "$('#listaCheckUsuariosPA').css({ display: 'block'})");//eventos javascript

        $frase = HTML::frase($textos->id("PERMISOS_ADICION"), "negrilla")." : ";

        if(!empty($id)){
            if(count($perfiles) == 0 || in_array(99, $perfiles)){ 
                        $cod3 .= HTML::parrafo($frase.HTML::radioBoton("datos[visibilidad_pa]", "si", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad_pa]", "", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");
                }else{                          
                        $cod3 .= HTML::parrafo($frase.HTML::radioBoton("datos[visibilidad_pa]", "", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad_pa]", "si", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");
            }
        }else{
             $cod3 .= HTML::parrafo($frase.HTML::radioBoton("datos[visibilidad_pa]", "si", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad_pa]", "", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");

        }

        //$cod3 = HTML::parrafo(HTML::radioBoton("datos[visibilidad]", "si", "", "publico", $opcionesPublico).$textos->id("PUBLICO").HTML::radioBoton("datos[visibilidad]", "", "", "privado", $opcionesPrivado).$textos->id("PRIVADO"), "margenSuperior");


      return $cod3.$cod2;

    }


}
?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Registro
 * @author      Pablo A. Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */

/**
 * Clase Registro: clase encargada de gestionar la informacion de los registros a un determinado evento, fue creada para
 * gestionar los registros de inscripciones de asistentes al evento de ABLA 2012 realizado en santo domingo.
 * es una clase que contiene en su mayoria metodos crud para la gestion con la bd, ademas de esto tiene algunos metodos
 * para el renderizado de informacion, como por ejemplo el metodo generar tabla y tambien tiene metodos para generar los archivos.
 * pdf con la informacion de las inscripciones. 
 */
class Registro {

    /**
     * Código interno o identificador del registro en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * URL relativa del módulo de registros
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un registro específica
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del usuario creador de la registro en la base de datos
     * @var entero
     */
    public $nombres;

    /**
     * Código interno o identificador del modulo
     * @var entero
     */
    public $apellidos;

    /**
     * Nombre de usuario (login) del usuario creador de la registro
     * @var cadena
     */
    public $institucion;

    /**
     * Sobrenombre o apodo del usuario creador de la registro
     * @var cadena
     */
    public $cargo;

    /**
     * Título de la registro
     * @var cadena
     */
    public $ciudad;

    /**
     * Título de la registro
     * @var cadena
     */
    public $pais;

    /**
     * Indicador de si ya ha realizado el pago
     * @var lógico
     */
    public $pagado;

    /**
     * Codigo postal del inscrito
     * @var cadena
     */
    public $codigoPostal;

    /**
     * Correo postal del inscrito
     * @var cadena
     */
    public $direccionCorreo;

    /**
     * Correo electronico del inscrito
     * @var cadena
     */
    public $email;

    /**
     * Telefono de la persona
     * @var cadena
     */
    public $telefono;

    /**
     * Numero de fax para contactar la persona
     * @var cadena
     */
    public $fax;

    /**
     * Evento al cual se inscribio la persona
     * @var enum
     */
    public $evento;

    /**
     * Fecha de creación de la registro
     * @var fecha
     */
    public $tituloCarnet;

    /**
     * Fecha de creación de la registro
     * @var fecha
     */
    public $rol;

    /**
     * Fecha de creación de la registro
     * @var fecha
     */
    public $fechaRegistro;

    /**
     * Fecha de publicación de la registro
     * @var fecha
     */
    public $nombreCertificado;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Indicador del orden cronológio de la lista de registros
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
    public $pagados = NULL;

    /**
     * Número de registros activos de la lista de foros
     * @var entero
     */
    public $registrosConsulta = NULL;

    /**
     * Orden predeterminado para organizar los listados
     * @var entero
     */
    public $ordenInicial = 'apellidos';

    /**
     * Inicializar el objeto
     * @param entero $id Código interno o identificador del objeto en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('REGISTRO');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        $this->registros = $sql->obtenerValor('registro', 'COUNT(id)', '');

        $this->pagados = $sql->obtenerValor('registro', 'COUNT(id)', ' pagado = "1"');

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos de unobjeto
     * @param entero $id Código interno o identificador del objeto en la base de datos
     */
    public function cargar($id) {
        global $sql;

        if (is_numeric($id) && $sql->existeItem('registro', 'id', intval($id))) {
            $tablas = array('r' => 'registro');

            $columnas = array(
                'id' => 'r.id',
                'nombres' => 'r.nombres',
                'apellidos' => 'r.apellidos',
                'institucion' => 'r.institucion',
                'cargo' => 'r.cargo',
                'ciudad' => 'r.ciudad',
                'pais' => 'r.pais',
                'codigoPostal' => 'r.codigo_postal',
                'direccionCorreo' => 'r.direccion_correo',
                'telefono' => 'r.telefono',
                'fax' => 'r.fax',
                'fechaRegistro' => 'r.fecha_registro',
                'nombreCertificado' => 'r.nombre_certificado',
                'evento' => 'r.evento',
                'email' => 'r.email',
                'rol' => 'r.rol',
                'tituloCarnet' => 'r.titulo_carnet',
                'pagado' => 'r.pagado'
            );

            $condicion = 'r.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->id;
            }
        }
    }

    /**
     * Adicionar un objeto
     * @param  arreglo $datos       Datos del objeto a adicionar
     * @return entero               Código interno o identificador del objeto en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $textos, $configuracion;

        $datosRegistro = array(
            'nombres' => htmlspecialchars($datos['nombres']),
            'apellidos' => htmlspecialchars($datos['apellidos']),
            'institucion' => htmlspecialchars($datos['institucion']),
            'cargo' => htmlspecialchars($datos['cargo']),
            'ciudad' => htmlspecialchars($datos['ciudad']),
            'pais' => htmlspecialchars($datos['pais']),
            'codigo_postal' => htmlspecialchars($datos['codigo_postal']),
            'direccion_correo' => htmlspecialchars($datos['direccion_correo']),
            'email' => strip_tags($datos['email'], '@'),
            'telefono' => htmlspecialchars($datos['telefono']),
            'fax' => htmlspecialchars($datos['fax']),
            'evento' => htmlspecialchars($datos['evento']),
            'fecha_registro' => htmlspecialchars(date('Y-m-d H:i:s')),
            'nombre_certificado' => htmlspecialchars($datos['nombre_certificado'])
        );

        $consulta = $sql->insertar('registro', $datosRegistro);

        $idItem = $sql->ultimoId;

        if ($consulta) {

            $nombrePdf = 'registration_' . $idItem . '.pdf';
            $rutaPdf = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['pdfs'] . '/' . $configuracion['RUTAS']['registros'] . '/' . $nombrePdf;

            $registroPdf = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['pdfs'] . '/' . $configuracion['RUTAS']['registros'] . '/' . $nombrePdf;

            $linkPdf = HTML::enlace('.:Download registration of ' . utf8_decode($datos['nombres']) . ' ' . utf8_decode($datos['apellidos']) . ':.', $registroPdf);

            $mensaje1 = str_replace('%1', utf8_decode($datosRegistro['nombres']) . ' ' . utf8_decode($datosRegistro['apellidos']), $textos->id('CONTENIDO_MENSAJE_REGISTRO_ABLA2012'));
            $mensaje = str_replace('%2', $registroPdf, $mensaje1);
            Servidor::enviarCorreo($datos['email'], $textos->id('ASUNTO_MENSAJE_REGISTRO_2012'), $mensaje, utf8_decode($datos['nombres']) . ' ' . utf8_decode($datos['apellidos']));


            $rutaLogoAbla = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['imagenesEstaticas'] . '/abla2012.jpg';
            $rutaLogoDomi = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['imagenesEstaticas'] . '/logoDominico.jpg';

            $pdf = new FPDF();
            $pdf->AddPage();

            $pdf->Image($rutaLogoAbla, 10, 10, 37, 29, 'jpg');
            $pdf->Image($rutaLogoDomi, 165, 10, 37, 29, 'jpg');
            $pdf->Ln(17);

            $pdf->SetFont('times', 'B', 17);
            $pdf->Cell(190, 10, '12th ABLA CONVENTION 2012', 0, 0, 'C');
            $pdf->Ln(8);
            $pdf->SetFont('times', '', 11);
            $pdf->Cell(190, 10, 'Santo Domingo, DN; Dominican Republic', 0, 0, 'C');

            $pdf->Ln(8);
            $pdf->SetFont('times', '', 9);
            $pdf->Cell(63, 9, 'Leaders Convention', 0, 0, 'C');
            $pdf->SetFont('times', '', 9);
            $pdf->Cell(63, 9, 'Librarians Convention', 0, 0, 'C');
            $pdf->SetFont('times', '', 9);
            $pdf->Cell(63, 9, 'ELT Convention', 0, 0, 'C');

            $pdf->Ln(8);
            $pdf->SetFont('times', 'B', 12);
            $pdf->Cell(190, 10, 'International Registration Form', 0, 0, 'C');

            $pdf->Ln(12);
            $pdf->Cell(190, 7, '', 'T', 0, 'L');

            $pdf->Ln(4);
            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 9, 'First Name:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(60, 9, utf8_decode($datos['nombres']), 0, 0, 'L');

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 9, 'Last Name:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(60, 9, utf8_decode($datos['apellidos']), 0, 0, 'L');


            $pdf->Ln(8);

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 10, 'Institution:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(90, 9, utf8_decode($datos['institucion']), 0, 0, 'L');

            $pdf->Ln(8);
            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 9, 'Position:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(60, 9, utf8_decode($datos['cargo']), 0, 0, 'L');

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 9, 'Country:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(60, 9, utf8_decode($datos['pais']), 0, 0, 'L');


            $pdf->Ln(8);

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 9, 'City:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(70, 9, utf8_decode($datos['ciudad']), 0, 0, 'L');

            $pdf->Ln(8);

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 9, 'Postal Code:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(60, 9, utf8_decode($datos['codigo_postal']), 0, 0, 'L');

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 9, 'Mailing address:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(60, 9, utf8_decode($datos['direccion_correo']), 0, 0, 'L');

            $pdf->Ln(8);

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 9, 'Telephone:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(60, 9, utf8_decode($datos['telefono']), 0, 0, 'L');

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 9, 'Fax:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(60, 9, utf8_decode($datos['fax']), 0, 0, 'L');

            $pdf->Ln(8);

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 9, 'E-mail:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(60, 9, utf8_decode($datos['email']), 0, 0, 'L');

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 9, 'Date:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(60, 9, date('D, d M Y'), 0, 0, 'L');

            $pdf->Ln(8);

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(90, 9, 'How your name will appear on the certificate?', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(60, 9, utf8_decode($datos['nombre_certificado']), 0, 0, 'L');

            $pdf->Ln(8);

            $pdf->SetFont('times', 'B', 12);
            $pdf->Cell(90, 9, 'Payment information:', 0, 0, 'L');

            $pdf->Ln(8);

            $pdf->SetFont('times', '', 10);
            $pdf->Cell(90, 9, $textos->id('INFO_EVENTO_' . $datos['evento'] . ''), 0, 0, 'L');

            $pdf->Ln(8);

            $pdf->SetFont('times', 'B', 12);
            $pdf->Cell(90, 9, 'Total:', 0, 0, 'L');

            $pdf->Ln(8);

            $pdf->SetFont('times', '', 10);
            $pdf->Cell(90, 9, $textos->id('VALOR_EVENTO_' . $datos['evento'] . ''), 0, 0, 'L');

            $pdf->Ln(8);

            $pdf->SetFont('times', 'B', 12);
            $pdf->Cell(90, 9, 'Payment method:', 0, 0, 'L');

            $pdf->Ln(10);

            $pdf->SetFont('times', '', 12);
            $pdf->Cell(5, 5, '', 1, 0, 'L');

            $pdf->SetFont('times', 'B', 10);
            $pdf->Cell(50, 5, 'VISA', 0, 0, 'L');

            $pdf->SetFont('times', '', 12);
            $pdf->Cell(5, 5, '', 1, 0, 'L');

            $pdf->SetFont('times', 'B', 10);
            $pdf->Cell(50, 5, 'MASTER CARD', 0, 0, 'L');

            $pdf->Ln(8);

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 5, 'Credit Card Number:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(50, 5, '', 'B', 0, 'L');

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 5, 'Cardholder\'s name:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(50, 5, '', 'B', 0, 'L');

            $pdf->Ln(8);

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 5, 'Expiration date:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(50, 5, '', 'B', 0, 'L');

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(30, 5, "Security code:", 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(50, 5, '', 'B', 0, 'L');

            $pdf->Ln(3);

            $pdf->SetFont('times', '', 7);
            $pdf->Cell(150, 7, '(Last three digits on back of card)', 0, 0, 'R');

            $pdf->Ln(7);
            $pdf->SetFont('times', '', 10);
            $pdf->Cell(190, 9, '* Early Registration:  If you register before June 30th, 2012, you will receive a US$20.00 discount.', 0, 0, 'L');

            $pdf->Ln(7);
            $pdf->SetFont('times', '', 10);
            $pdf->Cell(190, 9, '* Regular Registration: Registrations submitted between July 1st and September 30th, 2012.', 0, 0, 'L');

            $pdf->Ln(7);
            $pdf->SetFont('times', '', 10);
            $pdf->Cell(190, 9, '* Late Registration:  If your register after October 1st, you will be charged an additional fee of US $25.00.', 0, 0, 'L');

            $pdf->Ln(7);
            $pdf->SetFont('times', '', 10);
            $pdf->Cell(190, 9, '+ Additional 20% discount if three or more people from the same BNC register before June 30th, 2012.', 0, 0, 'L');

            $pdf->Ln(15);

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(15, 5, "City:", 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(40, 5, '', 'B', 0, 'L');

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(15, 5, 'Signature:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(40, 5, '', 'B', 0, 'L');

            $pdf->SetFont('times', 'B', 9);
            $pdf->Cell(15, 5, 'Date:', 0, 0, 'L');

            $pdf->SetFont('times', '', 9);
            $pdf->Cell(40, 5, '', 'B', 0, 'L');

            $pdf->Ln(10);
            $pdf->SetFont('times', '', 10);
            $pdf->Cell(190, 9, 'Fill up this form and send it to this Fax number 8092552600', 0, 0, 'C');

            $pdf->Ln(16);
            $pdf->SetFont('times', '', 10);
            $pdf->Cell(190, 9, 'Av. Abraham Lincoln #21, Santo Domingo, República Dominicana,', 0, 0, 'C');

            $pdf->Ln(4);
            $pdf->SetFont('times', '', 10);
            $pdf->Cell(190, 9, 'Tel. 809-535-0665, ext. 2202, 2102', 0, 0, 'C');

            $pdf->Output($rutaPdf, "F");

            chmod($rutaPdf, 0777);

            return $idItem . "|" . $linkPdf;
        } else {
            return FALSE;
        }
    }

//fin del metodo adicionar

    /**
     * Modificar un registro
     * @param  arreglo $datos       Datos de la registro a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $datosRegistro = array(
            'nombres' => htmlspecialchars($datos['nombres']),
            'apellidos' => htmlspecialchars($datos['apellidos']),
            'institucion' => htmlspecialchars($datos['institucion']),
            'cargo' => htmlspecialchars($datos['cargo']),
            'ciudad' => htmlspecialchars($datos['ciudad']),
            'pais' => htmlspecialchars($datos['pais']),
            'codigo_postal' => htmlspecialchars($datos['codigo_postal']),
            'direccion_correo' => htmlspecialchars($datos['direccion_correo']),
            'email' => strip_tags($datos['email'], '@'),
            'telefono' => htmlspecialchars($datos['telefono']),
            'fax' => htmlspecialchars($datos['fax']),
            'fecha_registro' => htmlspecialchars(date('Y-m-d H:i:s')),
            'evento' => htmlspecialchars($datos['evento']),
            'nombre_certificado' => htmlspecialchars($datos['nombre_certificado'])
        );

        $consulta = $sql->modificar('registro', $datosRegistro, 'id = "' . $this->id . '"');

        if ($consulta) {
            return $this->id;
        } else {
            return FALSE;
        }
    }

//fin del metodo modificar

    /**
     * Eliminar un registro
     * @param entero $id    Código interno o identificador de la registro en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('registro', 'id = "' . $this->id . '"');

        if ($consulta) {
            return $consulta;
        } else {
            return false;
        }
    }

//fin del metodo eliminar

    /**
     * Listar las registros
     * @param entero  $cantidad    Número de ciudadesa incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de ciudades
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicionGlobal = NULL, $orden = NULL) {
        global $sql;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicionGlobal)) {
            $condicion = '';
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion = 'r.id NOT IN (' . $excepcion . ') ';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if (!isset($orden)) {
            $orden = $this->ordenInicial;
        }
        if ($this->listaAscendente) {
            $orden = $orden . ' ASC';
        } else {
            $orden = $orden . ' DESC';
        }


        $tablas = array(
            'r' => 'registro'
        );

        $columnas = array(
            'id' => 'r.id',
            'nombres' => 'r.nombres',
            'apellidos' => 'r.apellidos',
            'institucion' => 'r.institucion',
            'cargo' => 'r.cargo',
            'ciudad' => 'r.ciudad',
            'pais' => 'r.pais',
            'codigoPostal' => 'r.codigo_postal',
            'direccionCorreo' => 'r.direccion_correo',
            'telefono' => 'r.telefono',
            'fax' => 'r.fax',
            'fechaRegistro' => 'UNIX_TIMESTAMP(r.fecha_registro)',
            'nombreCertificado' => 'r.nombre_certificado',
            'evento' => 'r.evento',
            'email' => 'r.email',
            'rol' => 'r.rol',
            'tituloCarnet' => 'r.titulo_carnet',
            'pagado' => 'r.pagado'
        );

        if (!empty($condicionGlobal)) {
            if ($condicion != '') {
                $condicion .= ' AND ';
            }
            $condicion .= $condicionGlobal;
        }

        if (is_null($this->registrosConsulta)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registrosConsulta = $sql->filasDevueltas;
        }
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'r.id', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($registro = $sql->filaEnObjeto($consulta)) {
                $registro->url = $this->urlBase . '/' . $registro->id;
                $lista[] = $registro;
            }
        }

        return $lista;
    }

//fin del metodo listar

    /**
     * MEtodo que se encargar de generar la tabla que lista los usuarios
     * @global objeto $textos objeto utilizado para las traducciones, su principal metodo el metodo id()
     * @param type $arregloRegistros
     * @param type $datosPaginacion
     * @return type 
     */
    public function generarTabla($arregloRegistros, $datosPaginacion = NULL) {
        global $textos;
        //Declaracion de las columnas que se van a mostrar en la tabla
        $datosTabla = array(
            HTML::contenedor($textos->id('NOMBRES'), 'columnaCabeceraTabla') => 'nombres|r.nombres',
            HTML::contenedor($textos->id('APELLIDOS'), 'columnaCabeceraTabla') => 'apellidos|r.apellidos',
            HTML::contenedor($textos->id('INSTITUCION'), 'columnaCabeceraTabla') => 'institucion|r.institucion',
            HTML::contenedor($textos->id('PAIS'), 'columnaCabeceraTabla') => 'pais|r.pais'
        );

        //ruta a donde se mandara la accion del doble click
        $rutaPaginador = '/ajax' . $this->urlBase . '/move';

        return Recursos::generarTablaRegistros($arregloRegistros, $datosTabla, $rutaPaginador, $datosPaginacion) . HTML::crearMenuBotonDerecho('REGISTRO');
    }

}

//fin de la clase Registro
?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Sedes
 * @author      Pablo A. Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
/**
 * Clase Sede: clase encargada de gestionar la informacion de los registros sobre las sedes almacenadas en el sistema.
 * es una clase que contiene en su mayoria metodos crud para la gestion con la bd.
 */
class Sede {

    /**
     * Código interno o identificador de la sede en la base de datos
     * @var entero
     */
    public $id;

    /**
     * Nombre de la sede
     * @var cadena
     */
    public $nombre;

    /**
     * Código interno o identificador en la base de datos de la ciudad de la sede binacional
     * @var entero
     */
    public $idCiudad;

    /**
     * Nombre de la ciudad de la sede binacional al que pertenece persona
     * @var cadena
     */
    public $ciudad;

    /**
     * Código interno o identificador en la base de datos del estado de la sede binacional
     * @var entero
     */
    public $idEstado;

    /**
     * Nombre del estado de la sede binacional al que pertenece persona
     * @var cadena
     */
    public $estado;

    /**
     * Código interno o identificador en la base de datos del país de la sede binacional
     * @var entero
     */
    public $idPais;

    /**
     * Nombre del país de la sede binacional
     * @var cadena
     */
    public $pais;

    /**
     * Indicador de disponibilidad del registro
     * @var lógico
     */
    public $activo;

    /**
     * Inicializar la sede
     * @param entero $id Código interno o identificador de la sede en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('SEDES');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;

        $consulta = $sql->filaEnObjeto($sql->seleccionar(array('sedes'), array('registros' => 'COUNT(id)')));
        $this->registros = $consulta->registros;

        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos de una sede
     * @param entero $id Código interno o identificador de la sede en la base de datos
     */
    public function cargar($id) {
        global $sql;

        if (isset($id) && $sql->existeItem('sedes', 'id', intval($id))) {

            $tablas = array(
                's' => 'sedes',
                'c' => 'ciudades',
                'e' => 'estados',
                'p' => 'paises'
            );

            $columnas = array(
                'id' => 's.id',
                'nombre' => 's.nombre',
                'direccion' => 's.direccion',
                'telefono1' => 's.telefono_1',
                'telefono2' => 's.telefono_2',
                'celular' => 's.celular',
                'correo' => 's.correo',
                'idCiudad' => 's.id_ciudad',
                'ciudad' => 'c.nombre',
                'idEstado' => 'c.id_estado',
                'estado' => 'e.nombre',
                'idPais' => 'e.id_pais',
                'pais' => 'p.nombre',
                'activo' => 's.activo'
            );

            $condicion = 's.id_ciudad = c.id AND c.id_estado = e.id AND e.id_pais = p.id AND s.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }
            }
        }
    }

    /**
     * Adicionar una sede
     * @param  arreglo $datos       Datos de la sede a adicionar
     * @return entero               Código interno o identificador de la sede en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql;

        $datos = array(
            'id_centro' => htmlspecialchars($datos['id_centro']),
            'id_ciudad' => $datos['id_ciudad'],
            'nombre' => htmlspecialchars($datos['nombre']),
            'direccion' => htmlspecialchars($datos['direccion']),
            'telefono_1' => htmlspecialchars($datos['telefono_1']),
            'telefono_2' => htmlspecialchars($datos['telefono_2']),
            'celular' => htmlspecialchars($datos['celular']),
            'correo' => strip_tags($datos['correo'], '@')
        );

        if (isset($datos['activo'])) {
            $datos['activo'] = '1';
        } else {
            $datos['activo'] = '0';
        }

        $consulta = $sql->insertar('sedes', $datos);

        if ($consulta) {
            return $sql->ultimoId;
        } else {
            return NULL;
        }
    }

    /**
     * Modificar una sede
     * @param  arreglo $datos       Datos de la sede a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $datos = array(
            'id_ciudad' => $datos['id_ciudad'],
            'nombre' => htmlspecialchars($datos['nombre']),
            'direccion' => htmlspecialchars($datos['direccion']),
            'telefono_1' => htmlspecialchars($datos['telefono_1']),
            'telefono_2' => htmlspecialchars($datos['telefono_2']),
            'celular' => htmlspecialchars($datos['celular']),
            'correo' => strip_tags($datos['correo'], '@')
        );

        if (isset($datos['activo'])) {
            $datos['activo'] = '1';
        } else {
            $datos['activo'] = '0';
        }

        $consulta = $sql->modificar('sedes', $datos, 'id = "' . $this->id . '"');

        return $consulta;
    }

    /**
     * Eliminar una sede
     * @param entero $id    Código interno o identificador de la sede en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar('sedes', 'id = "' . $this->id . '"');
        return $consulta;
    }

    /**
     * Listar las sedes
     * @param entero  $cantidad    Número de sedes a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de sedes
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql, $configuracion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= ' AND s.id NOT IN (' . $excepcion . ') ';
        }

        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'p.nombre ASC, e.nombre ASC, c.nombre ASC, s.nombre ASC';
        } else {
            $orden = 'p.nombre ASC, e.nombre ASC, c.nombre ASC, s.nombre DESC';
        }

        $tablas = array(
            's' => 'sedes',
            'c' => 'ciudades',
            'e' => 'estados',
            'p' => 'paises'
        );

        $columnas = array(
            'id' => 's.id',
            'nombre' => 's.nombre',
            'direccion' => 's.direccion',
            'telefono1' => 's.telefono_1',
            'telefono2' => 's.telefono_2',
            'celular' => 's.celular',
            'correo' => 's.correo',
            'idCiudad' => 's.id_ciudad',
            'ciudad' => 'c.nombre',
            'idEstado' => 'c.id_estado',
            'estado' => 'e.nombre',
            'idPais' => 'e.id_pais',
            'pais' => 'p.nombre',
            'activo' => 's.activo'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 's.id_ciudad = c.id AND c.id_estado = e.id AND e.id_pais = p.id AND s.id > 0';

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($sede = $sql->filaEnObjeto($consulta)) {
                $sede->url = $this->urlBase . '/' . $sede->id;
                $sede->logo = $configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesDinamicas'] . '/' . $sede->logo;
                $lista[] = $sede;
            }
        }

        return $lista;
    }

}

?>
<?php

/**
 * @package     FOLCS
 * @subpackage  Usuarios
 * @author      Pablo A. Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
/**
 * Clase Usuario: clase encargada de gestionar la informacion de los usuarios  almacenados en el sistema. Es la clase
 * desde la cual se instancia el objeto usuario que es almacenado en la variable de sesion (servidor) global de gestion de la "sesion" 
 * del usuario logueado en el sistema.
 * es una clase que contiene en su mayoria metodos crud para la gestion con la bd, ademas de esto tiene algunos metodos
 * para el renderizado de informacion, como por ejemplo el metodo mostrarNuevasNotificaciones, ademas de tener metodos de validacion
 * del ingreso de informacion relacionada con los usuarios.
 */
class Usuario {

    /**
     * Código interno o identificador del usuario en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del módulo de usuarios
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un usuario específico
     * @var cadena
     */
    public $url;

    /**
     * Código interno o identificador del tipo de usuario en la base de datos
     * @var entero
     */
    public $idTipo;

    /**
     * Nombre del tipo de usuario
     * @var cadena
     */
    public $tipo;

    /**
     * Nombre de usuario para el inicio de sesión
     * @var cadena
     */
    public $usuario;

    /**
     * Código interno o identificador en la base de datos de la persona con la cual está relacionada el usuario
     * @var entero
     */
    public $idPersona;

    /**
     * Código interno o identificador en la base de datos de la persona con la cual está relacionada el usuario
     * @var entero
     */
    public $idModulo;

    /**
     * Representación (objeto) de la persona con la cual está relacionada el usuario
     * @var objeto
     */
    public $persona;

    /**
     * Sobrenombre del usuario
     * @var cadena
     */
    public $sobrenombre;

    /**
     * Código interno o identificador del centro binacional en la base de datos al cual pertenece usuario
     * @var entero
     */
    public $idCentro;

    /**
     * Nombre del centro binacional al cual pertenece el usuario
     * @var cadena
     */
    public $centro;

    /**
     * Código interno o identificador en la base de datos de la ciudad del centro binacional al que pertenece persona
     * @var entero
     */
    public $idCiudadCentro;

    /**
     * Nombre de la ciudad del centro binacional al que pertenece persona
     * @var cadena
     */
    public $ciudadCentro;

    /**
     * Código interno o identificador en la base de datos del estado del centro binacional al que pertenece persona
     * @var entero
     */
    public $idEstadoCentro;

    /**
     * Nombre del estado del centro binacional al que pertenece persona
     * @var cadena
     */
    public $estadoCentro;

    /**
     * Código interno o identificador en la base de datos del usuario del centro binacional al que pertenece persona
     * @var entero
     */
    public $idPaisCentro;

    /**
     * Nombre del usuario del centro binacional al que pertenece persona
     * @var cadena
     */
    public $paisCentro;

    /**
     * Variable que determina si un usuario desea recibir notificaciones de ablaonline en su correo
     * @var boolean
     */
    public $notificaciones;

    /**
     * Indicador del orden cronológio de la lista de usuarios
     * @var lógico
     */
    public $listaAscendente = false;

    /**
     * Número de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Inicializar el usuario
     * @param entero $id Código interno o identificador del usuario en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('USUARIOS');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;

        if (is_string($id) && isset($id) && $sql->existeItem('usuarios', 'usuario', $id)) {
            $usuario = $sql->obtenerValor('usuarios', 'id', 'usuario = "' . $id . '"');
        } elseif (isset($id) && is_numeric($id)) {
            $usuario = $id;
        }

        $consulta = $sql->filaEnObjeto($sql->seleccionar(array('usuarios'), array('registros' => 'COUNT(id)')));
        $this->registros = $consulta->registros;
        $this->idModulo = $modulo->id;
        if (isset($id) && $id != NULL) {
            $this->cargar($usuario);
        }
    }

    /**
     * Metodo que se encarga de consultar y devolver el genero de la persona en la base de datos
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @param type $id = identificador del usuario, ya sea su id, o su nombre de usuario
     * @return type $idPersona = identificador de la persona en la base de datos
     */
    public function getGenero($id = NULL) {
        global $sql;

        if (is_string($id) && isset($id) && $sql->existeItem('usuarios', 'usuario', $id)) {
            $usuario = $sql->obtenerValor('usuarios', 'id', 'usuario = "' . $id . '"');
        } elseif (isset($id) && is_numeric($id)) {
            $usuario = $id;
        }

        $idPersona = $sql->obtenerValor('usuarios', 'id_persona', 'id = "' . $usuario . '"');
        $genero = $sql->obtenerValor('personas', 'genero', 'id = "' . $idPersona . '"');
        return $genero;
    }

    /**
     * Cargar los datos del usuario
     * @param entero $id Código interno o identificador del usuario en la base de datos
     */
    public function cargar($id = NULL) {
        global $sql;

        if (isset($id) && $sql->existeItem('usuarios', 'id', intval($id))) {
            $this->id = $id;

            $tablas = array(
                'u' => 'usuarios',
                't' => 'tipos_usuario',
                'c' => 'centros',
                'c0' => 'ciudades',
                'e0' => 'estados',
                'p0' => 'paises'
            );

            $columnas = array(
                'idTipo' => 'u.id_tipo',
                'tipo' => 't.nombre',
                'usuario' => 'u.usuario',
                'idPersona' => 'u.id_persona',
                'sobrenombre' => 'u.sobrenombre',
                'idCentro' => 'u.id_centro',
                'centro' => 'c.nombre',
                'idCiudadCentro' => 'c.id_ciudad',
                'ciudadCentro' => 'c0.nombre',
                'idEstadoCentro' => 'c0.id_estado',
                'estadoCentro' => 'e0.nombre',
                'idPaisCentro' => 'e0.id_pais',
                'paisCentro' => 'p0.nombre',
                'fechaRegistro' => 'u.fecha_registro',
                'cambiarContrasena' => 'u.cambiar_contrasena',
                'fechaCambioContrasena' => 'u.fecha_cambio_contrasena',
                'cambioContrasenaMinimo' => 'u.cambio_contrasena_minimo',
                'cambioContrasenaMaximo' => 'u.cambio_contrasena_maximo',
                'fechaExpiracion' => 'u.fecha_expiracion',
                'activo' => 'u.activo',
                'notificaciones' => 'u.notificaciones'
            );

            $condicion = 'u.id_centro = c.id AND c.id_ciudad = c0.id AND c0.id_estado = e0.id AND e0.id_pais = p0.id AND u.id_tipo = t.id AND u.id = "' . $id . '"';
            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->usuario;
                $this->persona = new Persona($this->idPersona);
            }
        }
    }

    /**
     * Validar un usuario
     * @param  cadena $usuario      Nombre de acceso del usuario a validar
     * @param  cadena $contrasena   Contraseña del usuario a validar
     * @return entero               Código interno o identificador del usuario en la base de datos (-1 si el usuario está inactivo, NULL si hubo error)
     */
    public function validar($usuario, $contrasena) {
        global $sql;

        $usuario = htmlspecialchars($usuario);
        $contrasena = htmlspecialchars($contrasena);

        if (is_string($usuario) && !preg_match('/[^a-z0-9]/', $usuario) && is_string($contrasena) && !preg_match('/[^a-zA-Z0-9]/', $contrasena)) {
	    //$sql->depurar = true;
            $consulta = $sql->seleccionar(array('usuarios'), array('id', 'activo', 'bloqueado', 'fecha_expiracion'), 'usuario="' . $usuario . '" AND contrasena=MD5("' . $contrasena . '")');

            if ($sql->filasDevueltas) {
                $datos = $sql->filaEnObjeto($consulta);
                /*                 * ********* Verifico si el usuario esta bloqueado y lo desbloqueo porque coinciden el usuario y la contraseña**************** */
                if ($datos->bloqueado) {
                    $datosUser['bloqueado'] = '0';
                    $consulta = $sql->modificar('usuarios', $datosUser, 'usuario = "' . $usuario . '"');
                }
                if ($datos->activo) {
                    return $datos->id;
                } else {
                    return -1;
                }
            }
        }

        return NULL;
    }

    /**
     * Validar si un usuario que trata de ingresar al sistema esta bloqueado
     * @param  cadena $usuario      Nombre de acceso del usuario a validar
     * @param  cadena $contrasena   Contraseña del usuario a validar
     * @return entero               Código interno o identificador del usuario en la base de datos (-1 si el usuario está inactivo, NULL si hubo error)
     */
    public function validarUsuarioBloqueado($usuario) {
        global $sql;

        $usuario = htmlspecialchars($usuario);

        if (is_string($usuario) && !preg_match('/[^a-z0-9]/', $usuario)) {
            $consulta = $sql->seleccionar(array('usuarios'), array('bloqueado'), 'usuario="' . $usuario . '"');

            if ($sql->filasDevueltas) {
                $datos = $sql->filaEnObjeto($consulta);

                if ($datos->bloqueado) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return NULL;
    }

    /**
     * Registrar un usuario con los datos básicos
     * @param  arreglo $datos       Datos del usuario a registrar
     * @return entero               Código interno o identificador del usuario en la base de datos (NULL si hubo error)
     */
    public function registrar($datos) {
        global $sql, $textos;

	$idCentro = $sql->obtenerValor('lista_centros', 'id', 'nombre = "' . htmlspecialchars(utf8_decode($datos['id_centro'])) . '"');

	$idCiudad = $sql->obtenerValor('centros', 'id_ciudad', 'id = "'. $idCentro .'"');

        $datosPersona = array(
            'nombre' => htmlspecialchars($datos['nombre']),
            'apellidos' => htmlspecialchars($datos['apellidos']),
            'correo' => strip_tags($datos['correo'], '@'),
            'id_ciudad_residencia' => $idCiudad,
            'id_imagen' => '0'
        );

        $persona = new Persona();

        if ($persona->adicionar($datosPersona)) {
            $persona = new Persona($sql->ultimoId);
            $codigo = md5(uniqid(rand(), true));
            $datosUsuario = array(
                'usuario' => htmlspecialchars($datos['usuario']),
                'sobrenombre' => htmlspecialchars($datos['nombre']),
                'id_tipo' => '99',
                'id_centro' => $idCentro,
                'id_persona' => $persona->id,
                'contrasena' => md5(htmlspecialchars($datos['contrasena1'])),
                'fecha_registro' => date('Y-m-d H:i:s'),
                'confirmacion' => $codigo,
                'activo' => '1'//quitar lo del registro
            );

            $consulta = $sql->insertar('usuarios', $datosUsuario);
            $idUsuario = $sql->ultimoId;
            if ($consulta) {

                $sobrenombre = $datos['nombre'] . ' ' . substr($datos['apellidos'], 0, 1) . '.';
                $sql->modificar('usuarios', array('sobrenombre' => $sobrenombre), 'id = "' . $sql->ultimoId . '"');
                $mensaje1 = str_replace('%1', $datosPersona['nombre'], $textos->id('CONTENIDO_MENSAJE_REGISTRO'));
                $mensaje2 = str_replace('%2', $datos['usuario'], $mensaje1);
                $mensaje = str_replace('%3', $datos['contrasena1'], $mensaje2);
                Servidor::enviarCorreo($datosPersona['correo'], $textos->id('ASUNTO_MENSAJE_REGISTRO'), $mensaje, $datosPersona['nombre'] . ' ' . $datosPersona['apellidos']);
                return $idUsuario;
            } else {
                $persona->eliminar();
            }
        }

        return NULL;
    }

    /**
     * Adicionar un usuario
     * @param  arreglo $datos       Datos del usuario a adicionar
     * @return entero               Código interno o identificador del usuario en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql;

        $datosPersona = array(
            'nombre' => htmlspecialchars($datos['nombre']),
            'apellidos' => htmlspecialchars($datos['apellidos']),
            'correo' => strip_tags($datos['correo'], '@'),
            'id_ciudad_residencia' => htmlspecialchars($datos['id_ciudad'])
        );

        $persona = new Persona();

        if ($persona->adicionar($datosPersona)) {
            $persona = new Persona($sql->ultimoId);
            $datosUsuario = array(
                'usuario' => htmlspecialchars($datos['usuario']),
                'sobrenombre' => htmlspecialchars($datos['nombre']),
                'id_tipo' => '99',
                'id_centro' => '0',
                'id_persona' => $persona->id,
                'contrasena' => md5(htmlspecialchars($datos['contrasena1'])),
                'fecha_registro' => date('Y-m-d H:i:s')
            );

            $consulta = $sql->insertar('usuarios', $datosUsuario);

            if ($consulta) {
                return $sql->ultimoId;
            } else {
                $persona->eliminar();
            }
        }

        return NULL;
    }

    /**
     * Modificar la información básica de un usuario
     * @param  arreglo $datos       Datos del usuario a modificar
     * @return lógico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
        }

        $datosPersona = array(
            'nombre' => htmlspecialchars($datos['nombre']),
            'apellidos' => htmlspecialchars($datos['apellidos']),
            'correo' => strip_tags($datos['correo'], '@'),
            'pagina_web' => htmlspecialchars($datos['pagina_web']),
            'id_ciudad_nacimiento' => htmlspecialchars($datos['id_ciudad_nacimiento']),
            'id_ciudad_residencia' => htmlspecialchars(utf8_decode($datos['id_ciudad_residencia'])),
            'genero' => htmlspecialchars($datos['genero']),
            'fecha_nacimiento' => htmlspecialchars($datos['fecha_nacimiento']),
            'descripcion' => htmlspecialchars($datos['descripcion']),
        );

        $idImagen = $this->persona->idImagen;

        if (isset($archivo_imagen) && !empty($archivo_imagen['tmp_name'])) {

            if (empty($this->persona->idImagen)) {
                $objetoImagen = new Imagen();
            } else {
                $objetoImagen = new Imagen($this->persona->idImagen);
                $objetoImagen->eliminar();
            }

            $datosImagen = array(
                'idRegistro' => $this->id,
                'modulo' => 'USUARIOS',
                'descripcion' => 'Profile Image',
                'titulo' => 'Profile Image'
            );

            $idImagen = $objetoImagen->adicionar($datosImagen);
        }

        $datosPersona['id_imagen'] = $idImagen;

        $sql->modificar('personas', $datosPersona, 'id = "' . $this->persona->id . '"');

        $datosUsuario = array(
            'id_centro' => $datos['id_centro'],
            'sobrenombre' => htmlspecialchars($datos['sobrenombre'])
        );

        if (!isset($datos['notificaciones'])) {
            $datosUsuario['notificaciones'] = '0';
        } else {
            $datosUsuario['notificaciones'] = '1';
        }

        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 && !isset($datos['activo'])) {
            $datosUsuario['activo'] = '0';
        } else {
            $datosUsuario['activo'] = '1';
        }

        if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0 ) || isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 2 )) {//Aqui deberá hacerse la validacion de si el BNC webmaster puede editar el perfil del usuario
            $datosUsuario['id_tipo'] = htmlspecialchars($datos['id_tipo']);

            if ($datosUsuario['id_tipo'] == 2 && !empty($datos['id_centro_admin'])) {
                //primero borro datos en caso de que ya este administrando un centro
                $val = $sql->obtenerValor('admin_centro', 'id', 'id_usuario = "' . $this->id . '"');
                if ($val) {
                    $sql->eliminar('admin_centro', 'id = "' . $val . '"');
                }

                $datosAdminCentro = array(
                    'id_usuario' => $this->id,
                    'id_centro' =>  $datos['id_centro_admin']
                );

                $consulta = $sql->insertar('admin_centro', $datosAdminCentro);
            }
        }

	//Verificar si antes era un administrador de centro, y deja de serlo, que se borre el registro de la tabla admin centro

	if($this->idTipo == 2 && $datosUsuario['id_tipo'] != 2){
	    $sql->eliminar('admin_centro', 'id_usuario = "' . $this->id . '"');

	}



        if (!empty($datos['contrasena1'])) {
            $datosUsuario['contrasena'] = md5(htmlspecialchars($datos['contrasena1']));
        }

        $consulta = $sql->modificar('usuarios', $datosUsuario, 'id = "' . $this->id . '"');

        return $consulta;
    }

    /**
     * Eliminar un usuario
     * @param entero $id    Código interno o identificador del usuario en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        //Eliminar de la tabla contactos
        $sql->eliminar('contactos', 'id_usuario_solicitante = "' . $this->id . '" OR id_usuario_solicitado = "' . $this->id . '"');

        //eliminar los comentarios del usuario
        $sql->eliminar('comentarios', 'id_usuario = "' . $this->id . '"');

        //eliminar los videos del usuario
        $sql->eliminar('videos', 'id_usuario = "' . $this->id . '"');

        //eliminar las imagenes del usuarios
        $sql->eliminar('imagenes_usuarios', 'id_usuario = "' . $this->id . '"');

        //eliminar cada uno de los Blog posteados por el usuario
        $tablas = array('imagenes');
        $columnas = array('id' => 'id');
        $condicion = 'id_usuario = "' . $this->id . '"';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($imagenes = $sql->filaEnObjeto($consulta)) {
                $img = new Imagen($imagenes->id);
                $img->eliminar();
            }
        }

        //eliminar los mensajes de los foros que haya podido hacer el usuario 
        $consulta = $sql->eliminar('mensajes_foro', 'id_usuario = "' . $this->id . '"');

        //eliminar cada uno de los Blog posteados por el usuario
        $tablas1 = array('documentos');
        $columnas1 = array('id' => 'id');
        $condicion1 = 'id_usuario = "' . $this->id . '"';
        $consulta1 = $sql->seleccionar($tablas1, $columnas1, $condicion1);

        if ($sql->filasDevueltas) {
            while ($docs = $sql->filaEnObjeto($consulta1)) {
                $doc = new Documento($docs->id);
                $doc->eliminar();
            }
        }

        //eliminar cada uno de los Blog posteados por el usuario
        $tablas2 = array('audios');
        $columnas2 = array('id' => 'id');
        $condicion2 = 'id_usuario = "' . $this->id . '"';
        $consulta2 = $sql->seleccionar($tablas2, $columnas2, $condicion2);

        if ($sql->filasDevueltas) {
            while ($audios = $sql->filaEnObjeto($consulta2)) {
                $aud = new Audio($audios->id);
                $aud->eliminar();
            }
        }


        //eliminar cada uno de los Blog posteados por el usuario
        $tablas = array('blogs');
        $columnas = array('id' => 'id');
        $condicion = 'id_usuario = "' . $this->id . '"';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($blogs = $sql->filaEnObjeto($consulta)) {
                $blog = new Blog($blogs->id);
                $blog->eliminar();
            }
        }

        //eliminar cada uno de las posibles noticias posteadas por el usuario
        $tablas = array('noticias');
        $columnas = array('id' => 'id');
        $condicion = 'id_usuario = "' . $this->id . '"';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($news = $sql->filaEnObjeto($consulta)) {
                $not = new Noticia($news->id);
                $not->eliminar();
            }
        }

        //eliminar cada uno de los foros posteado por el usuario y los mensajes de dicho foro
        $tablas = array('foros');
        $columnas = array('id' => 'id');
        $condicion = 'id_usuario = "' . $this->id . '"';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($foros = $sql->filaEnObjeto($consulta)) {
                $foro = new Foro($foros->id);
                $foro->eliminar();
            }
        }
        //eliminar cada uno de los cursos posteados por el usuario y los items de dicho foro
        $tablas = array('cursos');
        $columnas = array('id' => 'id');
        $condicion = 'id_usuario = "' . $this->id . '"';
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            while ($cursos = $sql->filaEnObjeto($consulta)) {
                $curso = new Curso($cursos->id);
                $curso->eliminar();
            }
        }
        //eliminar los mensajes que tenga el usuario
        $consulta = $sql->eliminar('mensajes', 'id_usuario_remitente = "' . $this->id . '" OR id_usuario_destinatario = "' . $this->id . '"');

        $consulta = $sql->eliminar('personas', 'id = "' . $this->idPersona . '"');
        //$sql->depurar = true;
        $consulta = $sql->eliminar('usuarios', 'id = "' . $this->id . '"');
        return $consulta;
    }

    /**
     * Listar los usuarios
     * @param entero  $cantidad    Número de usuarios a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los códigos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condición adicional (SQL)
     * @return arreglo             Lista de usuarios
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql;
        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }
        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }
        /*         * * Validar que la condición sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }
        /*         * * Validar que la excepción sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'u.id NOT IN (' . $excepcion . ')';
        }
        /*         * * Definir el orden de presentación de los datos ** */
        if ($this->listaAscendente) {
            $orden = 'u.fecha_registro ASC';
        } else {
            $orden = 'u.fecha_registro DESC';
        }

        $tablas = array(
            'u' => 'usuarios',
            't' => 'tipos_usuario',
            'c' => 'centros',
            'c0' => 'ciudades',
            'e0' => 'estados',
            'p0' => 'paises'
        );

        $columnas = array(
            'id' => 'u.id',
            'idTipo' => 'u.id_tipo',
            'tipo' => 't.nombre',
            'usuario' => 'u.usuario',
            'idPersona' => 'u.id_persona',
            'sobrenombre' => 'u.sobrenombre',
            'idCentro' => 'u.id_centro',
            'centro' => 'c.nombre',
            'idCiudadCentro' => 'c.id_ciudad',
            'ciudadCentro' => 'c0.nombre',
            'idEstadoCentro' => 'c0.id_estado',
            'estadoCentro' => 'e0.nombre',
            'idPaisCentro' => 'e0.id_pais',
            'paisCentro' => 'p0.nombre',
            'fechaRegistro' => 'UNIX_TIMESTAMP(u.fecha_registro)',
            'cambiarContrasena' => 'u.cambiar_contrasena',
            'fechaCambioContrasena' => 'u.fecha_cambio_contrasena',
            'cambioContrasenaMinimo' => 'u.cambio_contrasena_minimo',
            'cambioContrasenaMaximo' => 'u.cambio_contrasena_maximo',
            'fechaExpiracion' => 'u.fecha_expiracion',
            'activo' => 'u.activo',
            'notificaciones' => 'u.notificaciones'
        );

        if (!empty($condicion)) {
            $condicion .= ' AND ';
        }

        $condicion .= 'u.id_centro = c.id AND c.id_ciudad = c0.id AND c0.id_estado = e0.id AND e0.id_pais = p0.id AND u.id_tipo = t.id';

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, '', $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {

            while ($usuario = $sql->filaEnObjeto($consulta)) {
                $usuario->url = $this->urlBase . '/' . $usuario->usuario;
                $usuario->urlBase = $this->urlBase;
                $usuario->persona = new Persona($usuario->idPersona);
                $lista[] = $usuario;
            }
        }

        return $lista;
    }

//fin del metodo listar

    /**
     * metodo que se ecarga de contar Nuevas Solicitudes de Amistad
     *
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @return null 
     */
    public function contarNuevasSolicitudesAmistad() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $cantidad = $sql->obtenerValor('contactos', 'COUNT(id)', 'id_usuario_solicitado = "' . $this->id . '" AND estado = "0"');

        return $cantidad;
    }

    /**
     * metodo que se ecarga de mostrar Nuevas Solicitudes de Amistad
     * @return null 
     */
    public function mostrarNuevasSolicitudesAmistad() {

        if (!isset($this->id)) {
            return NULL;
        }
        $cantidad = self::contarNuevasSolicitudesAmistad();
        if ($cantidad > 0) {
            $codigo = HTML::contenedor(HTML::frase($cantidad, 'cantidadNuevasSolicitudesAmistad'), 'contenedorNuevasSolicitudesAmistad', 'contenedorSolicitudesAmistad');
        } else {
            $codigo = HTML::contenedor(HTML::frase('  ', 'cantidadNuevasSolicitudesAmistad'), 'contenedorSinSolicitudesAmistad', 'contenedorSolicitudesAmistad');
        }
        return $codigo;
    }

    /**
     * metodo que se ecarga de contar Nuevos Mensajes
     *
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @return null 
     */
    public function contarNuevosMensajes() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $cantidad = $sql->obtenerValor('mensajes', 'COUNT(id)', 'id_usuario_destinatario = "' . $this->id . '" AND leido = "0"');
        //Recursos::escribirTxt($sql->sentenciaSql);

        return $cantidad;
    }

    /**
     * mettodo que se encarga de mostrar los Nuevos Mensajes
     * @return null 
     */
    public function mostrarNuevosMensajes() {

        if (!isset($this->id)) {
            return NULL;
        }
        $cantidad = self::contarNuevosMensajes();
        if ($cantidad > 0) {
            $codigo = HTML::contenedor(HTML::frase($cantidad, 'cantidadNuevosMensajes'), 'contenedorNuevosMensajes', 'contenedorMensajes');
        } else {
            $codigo = HTML::contenedor(HTML::frase('  ', 'cantidadNuevosMensajes'), 'contenedorSinMensajes', 'contenedorMensajes');
        }
        return $codigo;
    }

    /**
     * metodo que cuenta las nuevas notificaciones recibidas por un usuario
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @return null 
     */
    public function contarNuevasNotificaciones() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        $consulta = $sql->obtenerValor('notificaciones', 'COUNT(id)', 'id_usuario = "' . $this->id . '" AND leido = "0"');

        return $consulta;
    }

    /**
     * metodo que genera el contenedor con la informacion sobre las nuevas notificaciones recibidas por un usuario
     * @return null 
     */
    public function mostrarNuevasNotificaciones() {

        if (!isset($this->id)) {
            return NULL;
        }
        $cantidad = self::contarNuevasNotificaciones();

        if ($cantidad > 0) {
            $codigo = HTML::contenedor(HTML::frase($cantidad, 'cantidadNuevasNotificaciones'), 'contenedorNuevasNotificaciones', 'contenedorNotificaciones');
        } else {
            $codigo = HTML::contenedor(HTML::frase('', 'cantidadNuevasNotificaciones'), 'contenedorSinNotificaciones', 'contenedorNotificaciones');
        }

        return $codigo;
    }


    /**
     * Metodo que se encarga de de conectar un usuario al chat,
     * ingresando sus datos en la tabla usuarios_conectados
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @return boolean 
     */
    public static function conectarUsuario() {
        global $sql, $sesion_usuarioSesion;

        $existe = $sql->existeItem('usuarios_conectados', 'id_usuario', $sesion_usuarioSesion->id);

        if (!$existe) {
            $datos = array(
                'id_usuario' => $sesion_usuarioSesion->id,
                'usuario' => $sesion_usuarioSesion->usuario,
                'nombre' => $sesion_usuarioSesion->persona->nombreCompleto,
                'tiempo' => date('Y-m-d H:i:s')
            );

            $consulta = $sql->insertar('usuarios_conectados', $datos);

            if ($consulta) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Metodo que se encarga de desconectar un usuario del chat,
     * eliminando el registro de la tabla usuarios_conectados
     * @global objeto $sql recurso global de interaccion con la BD, es una instancia de la clase SQL 
     * @global objeto $sesion_usuarioSesion variable de sesion que contiene un objeto de tipo usuario el cual representa al usuario logeado
     * @return type 
     */
    public static function desconectarUsuario() {
        global $sql, $sesion_usuarioSesion;
        if (!isset($sesion_usuarioSesion)) {
            return NULL;
        }

        $consulta = $sql->eliminar('usuarios_conectados', 'id_usuario = "' . $sesion_usuarioSesion->id . '"');

        if ($consulta) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Funcion que se encarga de verificar si un usuario carga notificaciones dinamicamente 
     * y las muestra
     */
    public static function mostrarNotificacionesDinamicas() {
        global $sql, $sesion_usuarioSesion;
        $existe = $sql->existeItem('notificaciones_dinamicas', 'id_usuario_destinatario', $sesion_usuarioSesion->id, 'leido = "0" AND UNIX_TIMESTAMP(fecha) >=  (UNIX_TIMESTAMP() - 600)');

        if ($existe) {
            $tablas = array(
                'nd' => 'notificaciones_dinamicas'
            );

            $columnas = array(
                'id' => 'nd.id',
                'usuarioDest' => 'nd.id_usuario_destinatario',
                'usuarioRemi' => 'nd.id_usuario_remitente',
                'registro' => 'nd.id_registro',
                'modulo' => 'nd.id_modulo',
                'fecha' => 'nd.fecha',
                'contenido' => 'nd.contenido',
                'leido' => 'nd.leido'
            );

            $condicion = 'nd.id_usuario_destinatario = "' . $sesion_usuarioSesion->id . '" AND nd.leido = "0" AND UNIX_TIMESTAMP(nd.fecha) >=  (UNIX_TIMESTAMP() - 600)';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
            $lista = '';
            if ($sql->filasDevueltas) {

                while ($notificacion = $sql->filaEnObjeto($consulta)) {

                    $idNotificacion = $notificacion->id;
                    $idModulo = $notificacion->modulo;
                    $idRegistro = $notificacion->registro;
                    $idUsuarioRemitente = $notificacion->usuarioRemi;
                    $contenido = $notificacion->contenido;

                    if ($idModulo == '33') {
                        $url = '/ajax/users/readMessage';
                        $datos = array('id' => $idRegistro);
                    } else if ($idModulo == '15') {
                        $url = '/ajax/contacts/acceptFromNotification';
                        $datos = array('id' => $idUsuarioRemitente);
                    }

                    $boton = HTML::botonImagenAjax(HTML::frase($contenido, 'letraBlanca manito peticionAjax', '', ''), '', '', '', $url, $datos, '');
                    $boton = HTML::contenedor($boton, 'cuadroNotificacion', 'cuadroNotificacion_' . $idNotificacion);

                    $datos2 = array(
                        'leido' => '1'
                    );
                    $sql->modificar('notificaciones_dinamicas', $datos2, 'id = ' . $idNotificacion);

                    $lista .= $boton . '|' . '#cuadroNotificacion_' . $idNotificacion . '%';
                }
            }

            return $lista;
        } else {
            return 'sin_notificaciones';
        }
    }

    /**
     * Funcion que termina la sesion de un usuario
     */
    public static function cerrarSesion() {
        self::desconectarUsuario();
        Sesion::terminar();
        $respuesta = array();
        $respuesta['error'] = NULL;
        $respuesta['accion'] = 'redireccionar';
        $respuesta['destino'] = '/';
        Servidor::enviarJSON($respuesta);
    }

}

?>