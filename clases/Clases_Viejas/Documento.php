<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Base
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/

class Documento {



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
     * Código interno o identificador del módulo al cual pertenece el archivo en la base de datos
     * @var entero
     */
    public $idModulo;



    /**
     * Código interno o identificador del registro del módulo al cual pertenece el archivo en la base de datos
     * @var entero
     */
    public $idRegistro;



    /**
     * Código interno o identificador del usuario creador del archivo en la base de datos
     * @var entero
     */
    public $idAutor;

    
    
   /**
     * icono que representa al modulo
     * @var entero
     */
    public $icono;


    /**
     * Nombre de usuario (login) del usuario creador del archivo
     * @var cadena
     */
    public $usuarioAutor;



    /**
     * Sobrenombre o apodo del usuario creador del archivo
     * @var cadena
     */
    public $autor;



    /**
     * Ruta de la foto del autor en miniatura
     * @var cadena
     */
    public $fotoAutor;



    /**
     * Título del archivo
     * @var cadena
     */
    public $titulo;



    /**
     * Descripción corta del archivo
     * @var cadena
     */
    public $descripcion;



    /**
     * Indicador del estado del archivo
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
    * ruta del archivo
    * @var entero
    */
    public $ruta;
    
    
    /**
    * ruta del archivo
    * @var entero
    */
    public $enlace;



    /**
     *
     * Inicializar el archivo
     *
     * @param entero $id Código interno o identificador del archivo en la base de datos
     *
     */
    public function __construct($id = NULL) {
   
        $modulo         = new Modulo("DOCUMENTOS");
        $this->urlBase  = "/".$modulo->url;
        $this->url      = $modulo->url;
        $this->idModulo = $modulo->id;

        if (isset($id)) {
            $this->cargar($id);
           
        }

    }

    
    
    
    /**
     *
     * Cargar los datos del archivo
     *
     * @param entero $id Código interno o identificador del archivo en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("documentos", "id", intval($id))) {

            $tablas = array(
                "d" => "documentos",
                "u" => "usuarios",
                "p" => "personas",
                "i" => "imagenes"
           );

           $columnas = array(
                "id"            => "d.id",
                "idAutor"       => "d.id_usuario",
                "usuarioAutor"  => "u.usuario",
                "autor"         => "u.sobrenombre",
                "fotoAutor"     => "i.ruta",
                "titulo"        => "d.titulo",
                "descripcion"   => "d.descripcion",
                "ruta"          => "d.ruta"
            );

            $condicion = "d.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND d.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
               }

               $this->fotoAutor   = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesMiniaturas"]."/".$this->fotoAutor;
               $this->enlace      = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["documentos"]."/".$this->ruta;
               $this->icono       = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."/docs.png";
            }
       }
    }

    
    
    
    /**
     *
     * Adicionar un Documento
     *
     * @param  arreglo $datos       Datos del archivo a adicionar
     * @return entero               Código interno o identificador del archivo en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $textos, $configuracion, $sesion_usuarioSesion, $archivo_recurso;

        if (empty($archivo_recurso["tmp_name"])) {
            return NULL;
        }

        $configuracionRuta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["documentos"];
        $recurso           = Archivo::subirArchivoAlServidor($archivo_recurso, $configuracionRuta);
        
        if(!$recurso){
            echo "Error subiendo el archivo";
            return false;
        }
        
        $datosRecurso = array(
            "id_modulo"   => $datos["idModulo"],
            "id_registro" => $datos["idRegistro"],
            "id_usuario"  => $sesion_usuarioSesion->id,
            "titulo"      => $datos["titulo"],
            "descripcion" => $datos["descripcion"],
            "fecha"       => date("Y-m-d H:i:s"),
            "ruta"        => $recurso
        );

        $consulta = $sql->insertar("documentos", $datosRecurso);
        $idDocumento = $sql->ultimoId;

        if ($consulta) {
            
            $mod = $sql->obtenerValor("modulos", "nombre", "id = '".$datos["idModulo"]."'");
            if($mod == "CURSOS" && isset($datos["notificar_estudiantes"])){
                $idCurso        = $datos["idRegistro"];
                $objetoCurso	=  new Curso($idCurso);
                $consultaSeguidores	= $sql->seleccionar( array("cursos_seguidos"), array("id", "id_usuario"), "id_curso = '$idCurso'", "");
                if ($sql->filasDevueltas) {
                    $tipoItem   = $textos->id("DOCUMENTO"); 
                    $nombreItem = $datos["titulo"];
                    while ($seguidor = $sql->filaEnObjeto($consultaSeguidores)) {
//			$usuarioSeguidor   = new Usuario();
                        
                        $notificacion     = preg_replace("/\%1/", HTML::enlace($objetoCurso->autor, HTML::urlInterna("CURSOS", $idCurso)), $textos->id("MENSAJE_ADICION_ITEM_CURSO"));
                        $notificacion     = preg_replace("/\%2/", HTML::enlace($tipoItem, HTML::urlInterna("CURSOS", $idCurso)), $notificacion); 
                        $notificacion     = preg_replace("/\%3/", HTML::enlace($objetoCurso->nombre, HTML::urlInterna("CURSOS", $idCurso)), $notificacion);
                        $notificacion     = preg_replace("/\%4/", HTML::enlace($nombreItem, HTML::urlInterna("CURSOS", $idCurso)), $notificacion);
                        
                        Servidor::notificar($seguidor->id_usuario, $notificacion);
                    }
                }
            }            
            
            return $idDocumento;

        } else {
            return NULL;
        }
    }

    
    
    
    
    /**
     *
     * Eliminar un archivo
     *
     * @param entero $id    Código interno o identificador del archivo en la base de datos
     * @return lógico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql, $configuracion;

        if (!isset($this->id)) {
            return NULL;
        }
        $ruta = $configuracion["RUTAS"]["media"]."/".$configuracion["RUTAS"]["documentos"]."/".$this->ruta;
        if( Archivo::eliminarArchivoDelServidor( array($ruta) ) ){
            $consulta = $sql->eliminar("documentos", "id = '".$this->id."'");
            return $consulta;
            
        }else{
            return false;
            
        }
    }

    
    
    
    
    
    /**
     *
     * Contar la cantidad de archivos de un registro en un módulo
     *
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return entero              Número de archivos hechos al registro del módulo
     *
     */
    public function contar($modulo, $registro) {
        global $sql;

        $tablas = array(
            "d" => "documentos",
            "m" => "modulos"
        );

        $columnas = array(
            "registros" => "COUNT(d.id)"
        );

        $condicion = "d.id_modulo = m.id AND d.id_registro = '$registro' AND m.nombre = '$modulo'";

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $archivo  = $sql->filaEnObjeto($consulta);
            return $archivo->registros;
        } else {
            return NULL;
        }
    }

    
    
    

    /**
     *
     * Listar los archivos de un registro en un módulo
     *
     * @param  cadena $modulo      Nombre
     * @param  entero $registro    Código interno o identificador del registro del módulo en la base de datos
     * @return arreglo             Lista de archivos hechos al registro del módulo
     *
     */
    public function listar($inicio = 0, $cantidad = 0, $modulo, $registro) {
        global $sql, $configuracion;

        /*** Validar la fila inicial de la consulta ***/
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*** Validar la cantidad de registros requeridos en la consulta ***/
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        $tablas = array(
            "d" => "documentos",
            "u" => "usuarios",
            "p" => "personas",
            "i" => "imagenes",
            "m" => "modulos"
        );

        $columnas = array(
            "id"             => "d.id",
            "idAutor"        => "d.id_usuario",
            "usuarioAutor"   => "u.usuario",
            "autor"          => "u.sobrenombre",
            "fotoAutor"      => "i.ruta",
            "titulo"         => "d.titulo",
            "descripcion"    => "d.descripcion",
            "ruta"           => "d.ruta"
        );

        $condicion = "d.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i.id AND d.id_modulo = m.id AND d.id_registro = '$registro' AND m.nombre = '$modulo'";

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", "descripcion ASC", $inicio, $cantidad);

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($documento = $sql->filaEnObjeto($consulta)) {
                $documento->url       = $this->urlBase."/".$documento->id;
                $documento->fotoAutor = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesMiniaturas"]."/".$documento->fotoAutor;
                $documento->enlace    = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["documentos"]."/".$documento->ruta;
                $documento->icono     = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["imagenesEstilos"]."/docs.png";
                $lista[]              = $documento;
            }
        }

        return $lista;

    }
  
}
?>