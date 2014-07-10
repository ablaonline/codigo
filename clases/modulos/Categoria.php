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
 * @global type $sql
 * @global type $textos
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
 * @global type $sql
 * @global type $sesion_usuarioSesion
 * @param type $idModulo
 * @param type $valorPredeterminado
 * @param type $opciones
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
    * @global type $sql
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
     * @global type $textos
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

