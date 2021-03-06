<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Evento
 * @author      Pablo A. V�lez <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondrag�n <jmondragon@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 *
 * */
class Evento {

    /**
     * C�digo interno o identificador del evento en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del m�dulo de eventos
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un evento espec�fica
     * @var cadena
     */
    public $url;

    /**
     * C�digo interno o identificador del usuario creador del evento en la base de datos
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
     * C�digo interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * T�tulo del evento
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
     * C�digo interno o identificador de la ciudad donde se realizara el evento
     * @var entero
     */
    public $idCiudad;

    /**
     * Nombre de la ciudad que esta directamente relacionado con el evento
     * @var string
     */
    public $ciudad;

    /**
     * C�digo interno o identificador del estado donde se realizara el evento
     * @var entero
     */
    public $idEstado;

    /**
     * Nombre del estado que esta directamente relacionado con el evento
     * @var string
     */
    public $estado;

    /**
     * C�digo interno o identificador del estado donde se realizara el evento
     * @var entero
     */
    public $idPais;

    /**
     * Nombre del estado que esta directamente relacionado con el evento
     * @var string
     */
    public $pais;

    /**
     * C�digo interno o identificador del centro donde se realizara el evento
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
     * C�digo interno o identificador de la categoria a la cual pertenece el evento
     * @var entero
     */
    public $idCategoria;

    /**
     * Lugar donde se realizara el evento
     * @var cadena
     */
    public $lugar;

    /**
     * C�digo interno o identificador en la base de datos de la imagen relacionada con el evento
     * @var entero
     */
    public $idImagen;

    /**
     * Ruta de la imagen del evento en tama�o normal
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
     * Fecha de creaci�n del Registro
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicaci�n del evento
     * @var fecha
     */
    public $fechaActivacion;

    /**
     * Fecha de la �ltima modificaci�n del evento
     * @var fecha
     */
    public $fechaInactivacion;

    /**
     * Indicador de disponibilidad del registro
     * @var l�gico
     */
    public $activo;

    /**
     * Informacion de contacto donde se dar� mas informacion sobre el evento
     * @var String
     */
    public $infoContacto;

    /**
     * Indicador del orden cronol�gio de la lista de eventos
     * @var l�gico
     */
    public $listaAscendente = false;

    /**
     * N�mero de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * N�mero de registros activos de la lista
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * Codigos de los perfiles de usuario con los cuales es compara la informacion de un respectivo evento
     * @var array
     */
    public $permisosPerfiles = array();

    /**
     * N�mero de comentarios que tiene este item 
     * @var entero
     */
    public $cantidadComentarios = NULL;

    /**
     * N�mero de me gusta que tiene este item 
     * @var entero
     */
    public $cantidadMeGusta = NULL;

    /**
     * N�mero de me galerias fotograficas que tiene este item
     * @var entero
     */
    public $cantidadGalerias = NULL;

    /**
     *
     * Inicializar un evento
     *
     * @param entero $id C�digo interno o identificador del evento en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo("EVENTOS");
        $this->urlBase = "/" . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        $this->registros = $sql->obtenerValor("eventos", "COUNT(id)", "");
        $this->registrosActivos = $sql->obtenerValor("eventos", "COUNT(id)", "activo = '1'");


        if (isset($id)) {
            $this->cargar($id);
            $this->permisosPerfiles = PermisosItem::cargarPerfiles($id, $modulo->id);
            //Saber la cantidad de comentarios que tiene este blog
            $consulta = $sql->obtenerValor("comentarios", "COUNT(id)", "id_modulo = '" . $this->idModulo . "' AND id_registro = '" . $this->id . "'");
            $this->cantidadComentarios = $consulta;
            //Saber la cantidad de me Gusta que tiene este blog
            $consulta = $sql->obtenerValor("destacados", "COUNT(*)", "id_modulo = '" . $this->idModulo . "' AND id_item = '" . $this->id . "'");
            $this->cantidadMeGusta = $consulta;

            //Saber la cantidad de galerias que tiene este blog
            $consulta = $sql->obtenerValor("galerias", "COUNT(id)", "id_modulo = '" . $this->idModulo . "' AND id_registro = '" . $this->id . "'");
            $this->cantidadGalerias = $consulta;
        }
    }

    /**
     *
     * Cargar los datos de un evento
     *
     * @param entero $id C�digo interno o identificador del evento en la base de datos
     *
     */
    public function cargar($id) {
        global $sql, $configuracion;

        if (isset($id) && $sql->existeItem("eventos", "id", intval($id))) {

            $tablas = array(
                "e" => "eventos",
                "u" => "usuarios",
                "p" => "personas",
                "ce" => "centros",
                "c" => "ciudades",
                "c2" => "ciudades",
                "es" => "estados",
                "pa" => "paises",
                "i" => "imagenes",
                "i1" => "imagenes"
            );

            $columnas = array(
                "id" => "e.id",
                "idAutor" => "e.id_usuario",
                "usuarioAutor" => "u.usuario",
                "autor" => "u.sobrenombre",
                "titulo" => "e.titulo",
                "resumen" => "e.resumen",
                "descripcion" => "e.descripcion",
                "idCiudad" => "e.id_ciudad",
                "ciudad" => "c.nombre",
                "idEstado" => "es.id",
                "estado" => "es.nombre",
                "idPais" => "pa.id",
                "codigoIsoPais" => "pa.codigo_iso",
                "pais" => "pa.nombre",
                "idCentro" => "e.id_centro",
                "centro" => "ce.nombre",
                "idCiudadCentro" => "ce.id_ciudad",
                "ciudadCentro" => "c2.nombre",
                "idCategoria" => "e.id_categoria",
                "lugar" => "e.lugar",
                "idImagen" => "e.id_imagen",
                "imagen" => "i.ruta",
                "fechaInicio" => "e.fecha_inicio",
                "horaInicio" => "e.hora_inicio",
                "idImagenAutor" => "i1.id",
                "imagenAutor" => "i1.ruta",
                "fechaFin" => "e.fecha_fin",
                "horaFin" => "e.hora_fin",
                "fechaCreacion" => "UNIX_TIMESTAMP(e.fecha_creacion)",
                "fechaActivacion" => "UNIX_TIMESTAMP(e.fecha_activacion)",
                "fechaInactivacion" => "UNIX_TIMESTAMP(e.fecha_inactivacion)",
                "activo" => "e.activo",
                "infoContacto" => "e.info_contacto"
            );

            $condicion = "e.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i1.id AND e.id_imagen = i.id AND e.id_ciudad = c.id AND es.id = c.id_estado AND pa.id = es.id_pais AND e.id_centro = ce.id AND ce.id_ciudad = c2.id AND e.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . "/" . $this->id;
                $this->imagenPrincipal = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesDinamicas"] . "/" . $this->imagen;
                $this->imagenMiniatura = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $this->imagen;
                $this->imagenAutor = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $this->imagenAutor;
                $this->iconoBandera = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["iconosBanderas"] . "/" . strtolower($this->codigoIsoPais) . ".png";
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
     * @return entero               C�digo interno o identificador del evento en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo, $archivo_imagen;

        //nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfiles = $datos["perfiles"];
        $datosVisibilidad = $datos["visibilidad"];


        $idImagen = '8';

        if (isset($archivo_imagen) && !empty($archivo_imagen["tmp_name"])) {

            $imagen = new Imagen();
            $datosImagen = array(
                "modulo" => "EVENTOS",
                "idRegistro" => "",
                "titulo" => "imagen_evento",
                "descripcion" => "imagen_evento"
            );

            $idImagen = $imagen->adicionar($datosImagen);
        }

        $idCiudad = $sql->obtenerValor("lista_ciudades", "id", "cadena = '" . utf8_decode($datos["ciudad"]) . "'");
        $idCentro = $sql->obtenerValor("lista_centros", "id", "nombre = '" . utf8_decode($datos["centro"]) . "'");


        $datosEvento = array(
            "id_usuario" => $sesion_usuarioSesion->id,
            "titulo" => htmlspecialchars($datos["titulo"]),
            "resumen" => htmlspecialchars($datos["resumen"]),
            "descripcion" => Variable::filtrarTagsInseguros($datos["descripcion"]),
            "id_ciudad" => $idCiudad,
            "id_centro" => $idCentro,
            "id_categoria" => htmlspecialchars($datos["categorias"]),
            "lugar" => htmlspecialchars($datos["lugar"]),
            "id_imagen" => $idImagen,
            "fecha_inicio" => htmlspecialchars($datos["fecha_inicio"]),
            "hora_inicio" => htmlspecialchars($datos["hora_inicio"]),
            "fecha_fin" => htmlspecialchars($datos["fecha_fin"]),
            "hora_fin" => htmlspecialchars($datos["hora_fin"]),
            "fecha_creacion" => date("Y-m-d H:i:s"),
            "info_contacto" => htmlspecialchars($datos["info_contacto"])
        );



        if (isset($datos["activo"])) {
            $datosEvento["activo"] = "1";
            $datosEvento["fecha_activacion"] = date("Y-m-d H:i:s");
            //Recursos::escribirTxt("si llegue aqui: ".date("Y-m-d H:i:s"));
        } else {
            $datosEvento["activo"] = "0";
            $datosEvento["fecha_activacion"] = NULL;
        }


        $consulta = $sql->insertar("eventos", $datosEvento);
        $idItem = $sql->ultimoId;

        if ($consulta) {
            //codigo para ingresar en la tabla permisos_item los perfiles con los cuales se comparte el BLOG
            $permisosItem = new PermisosItem();
            $idModulo = $modulo->id;

            if ($datos["cantCampoImagenGaleria"]) {//si viene alguna imagen se crea la galeria             
                $galeria = new Galeria();
                $datos["id_modulo_actual"] = $this->idModulo;
                $datos["id_registro_actual"] = $idItem;
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
     * @return l�gico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function modificar($datos) {
        global $sql, $sesion_usuarioSesion, $modulo, $archivo_imagen;

        if (!isset($this->id)) {
            return NULL;
        }


        //nuevos datos que recibo sobre los perfiles con los que se comparte  la visibilidad del item
        $datosPerfiles = $datos["perfiles"];
        $datosVisibilidad = $datos["visibilidad"];


        $idImagen = $this->idImagen;

        if (isset($archivo_imagen) && !empty($archivo_imagen["tmp_name"])) {

            $imagen = new Imagen($this->idImagen);
            $imagen->eliminar();
            $datosImagen = array(
                "modulo" => "EVENTOS",
                "idRegistro" => "",
                "titulo" => "imagen_evento",
                "descripcion" => "imagen_evento"
            );

            $idImagen = $imagen->adicionar($datosImagen);
        }

        $idCiudad = $sql->obtenerValor("lista_ciudades", "id", "cadena = '" . utf8_decode($datos["ciudad"]) . "'");
        $idCentro = $sql->obtenerValor("lista_centros", "id", "nombre = '" . utf8_decode($datos["centro"]) . "'");
        //Recursos::escribirTxt("si llegue".$datos["centro"]);

        $datosEvento = array(
            "id_usuario" => $sesion_usuarioSesion->id,
            "titulo" => htmlspecialchars($datos["titulo"]),
            "resumen" => htmlspecialchars($datos["resumen"]),
            "descripcion" => Variable::filtrarTagsInseguros($datos["descripcion"]),
            "id_ciudad" => $idCiudad,
            "id_centro" => $idCentro,
            "id_categoria" => htmlspecialchars($datos["categorias"]),
            "lugar" => htmlspecialchars($datos["lugar"]),
            "id_imagen" => $idImagen,
            "fecha_inicio" => htmlspecialchars($datos["fecha_inicio"]),
            "hora_inicio" => htmlspecialchars($datos["hora_inicio"]),
            "fecha_fin" => htmlspecialchars($datos["fecha_fin"]),
            "hora_fin" => htmlspecialchars($datos["hora_fin"]),
            "fecha_creacion" => date("Y-m-d H:i:s"),
            "info_contacto" => htmlspecialchars($datos["info_contacto"])
        );

        if (isset($datos["activo"])) {
            $datosEvento["activo"] = "1";
            $datosEvento["fecha_activacion"] = date("Y-m-d H:i:s");
            // Recursos::escribirTxt("si llegue aqui: ".date("Y-m-d H:i:s"));
        } else {
            $datosEvento["activo"] = "0";
            $datosEvento["fecha_inactivacion"] = NULL;
        }

        $consulta = $sql->modificar("eventos", $datosEvento, "id = '" . $this->id . "'");

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
     * @param entero $id    C�digo interno o identificador del evento en la base de datos
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
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
        /* Eliminar todos los "me gusta" que pueda tener el evento */
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

        $consulta = $sql->eliminar("eventos", "id = '" . $this->id . "'");

        if ($consulta) {
            return $consulta;
        } else {
            return false;
        }
    }

    /**
     * Listar los eventos
     * @param entero  $cantidad    N�mero de eventos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los c�digos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condici�n adicional (SQL)
     * @return arreglo             Lista de eventos
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idPerfil = NULL, $idModulo, $filtroCategoria = NULL) {
        global $sql, $configuracion, $sesion_usuarioSesion;

        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condici�n sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*         * * Validar que la excepci�n sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "e.id NOT IN ($excepcion)";
        }

        /*         * * Definir el orden de presentaci�n de los datos ** */
        if ($this->listaAscendente) {
            $orden = "e.fecha_inicio DESC";
        } else {
            $orden = "e.fecha_inicio DESC";
        }

        //compruebo que se le haya pasado un valor al idPerfil
        $tablas = array(
            "e" => "eventos",
            "u" => "usuarios",
            "p" => "personas",
            "ce" => "centros",
            "c" => "ciudades",
            "es" => "estados",
            "pa" => "paises",
            "c2" => "ciudades",
            "i" => "imagenes",
            "i1" => "imagenes"
        );

        $columnas = array(
            "id" => "e.id",
            "idAutor" => "e.id_usuario",
            "usuarioAutor" => "u.usuario",
            "autor" => "u.sobrenombre",
            "titulo" => "e.titulo",
            "resumen" => "e.resumen",
            "descripcion" => "e.descripcion",
            "idCiudad" => "e.id_ciudad",
            "ciudad" => "c.nombre",
            "idEstado" => "es.id",
            "estado" => "es.nombre",
            "idPais" => "pa.id",
            "pais" => "pa.nombre",
            "codigoIsoPais" => "pa.codigo_iso",
            "idCentro" => "e.id_centro",
            "centro" => "ce.nombre",
            "idCiudadCentro" => "ce.id_ciudad",
            "ciudadCentro" => "c2.nombre",
            "idCategoria" => "e.id_categoria",
            "lugar" => "e.lugar",
            "idImagen" => "e.id_imagen",
            "imagen" => "i.ruta",
            "fechaInicio" => "UNIX_TIMESTAMP(e.fecha_inicio)",
            "horaInicio" => "e.hora_inicio",
            "idImagenAutor" => "i1.id",
            "imagenAutor" => "i1.ruta",
            "fechaFin" => "UNIX_TIMESTAMP(e.fecha_fin)",
            "horaFin" => "e.hora_fin",
            "fechaCreacion" => "UNIX_TIMESTAMP(e.fecha_creacion)",
            "fechaActivacion" => "UNIX_TIMESTAMP(e.fecha_activacion)",
            "fechaInactivacion" => "UNIX_TIMESTAMP(e.fecha_inactivacion)",
            "activo" => "e.activo",
            "infoContacto" => "e.info_contacto"
        );

        if (!empty($condicion)) {
            $condicion .= " AND ";
        }

        $condicion .= "e.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i1.id AND e.id_imagen = i.id AND e.id_ciudad = c.id AND es.id = c.id_estado AND pa.id = es.id_pais  AND e.id_centro = ce.id AND ce.id_ciudad = c2.id";

        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {

                //codigo para perfiles que pueden ver items de varios perfiles
                $condicion2 = "";
                $tienePrivilegios = $sql->obtenerValor("tipos_usuario", "otros_perfiles", "id = '" . $sesion_usuarioSesion->idTipo . "'");
                if (isset($sesion_usuarioSesion) && $tienePrivilegios) {
                    $otrosPerfiles = Perfil::verOtrosPerfiles(); //arreglo con los otros perfiles sobre los cuales este tiene privilegios
                    //print_r($otrosPerfiles);
                    $otrosPerfiles = implode(",", $otrosPerfiles);
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

                $condicion .= " AND pi.id_item = e.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil $condicion2')";


                $condicion = "(" . $condicion . ")";
                $condicion .= " OR ( $condicionA AND e.id_usuario = '$sesion_usuarioSesion->id'";


                if (!empty($filtroCategoria)) {
                    $condicion .= " AND e.id_categoria = '" . $filtroCategoria . "'"; //filtro de categoria
                }

                $condicion .= ")";
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
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "e.id", $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {
            while ($evento = $sql->filaEnObjeto($consulta)) {
                $evento->url = $this->urlBase . "/" . $evento->id;
                $evento->imagenPrincipal = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesDinamicas"] . "/" . $evento->imagen;
                $evento->imagenMiniatura = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $evento->imagen;
                $evento->iconoBandera = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["iconosBanderas"] . "/" . strtolower($evento->codigoIsoPais) . ".png";
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
     * @param entero  $cantidad    N�mero de eventos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los c�digos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condici�n adicional (SQL)
     * @return arreglo             Lista de eventos
     *
     */
    public function listarProximosEventos($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL, $idModulo) {
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

        /*         * * Validar que la condici�n sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*         * * Validar que la excepci�n sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "e.id NOT IN ($excepcion)";
        }

        /*         * * Definir el orden de presentaci�n de los datos ** */
        if ($this->listaAscendente) {
            $orden = "e.fecha_inicio DESC";
        } else {
            $orden = "e.fecha_inicio DESC";
        }

        //compruebo que se le haya pasado un valor al idPerfil

        $idPerfil = $sesion_usuarioSesion->idTipo;

        $tablas = array(
            "e" => "eventos",
            "u" => "usuarios",
            "p" => "personas",
            "ce" => "centros",
            "c" => "ciudades",
            "es" => "estados",
            "pa" => "paises",
            "c2" => "ciudades",
            "i" => "imagenes",
            "i1" => "imagenes"
        );

        $columnas = array(
            "id" => "e.id",
            "idAutor" => "e.id_usuario",
            "usuarioAutor" => "u.usuario",
            "autor" => "u.sobrenombre",
            "titulo" => "e.titulo",
            "resumen" => "e.resumen",
            "descripcion" => "e.descripcion",
            "idCiudad" => "e.id_ciudad",
            "ciudad" => "c.nombre",
            "idEstado" => "es.id",
            "estado" => "es.nombre",
            "idPais" => "pa.id",
            "pais" => "pa.nombre",
            "idCentro" => "e.id_centro",
            "centro" => "ce.nombre",
            "idCiudadCentro" => "ce.id_ciudad",
            "ciudadCentro" => "c2.nombre",
            "idCategoria" => "e.id_categoria",
            "lugar" => "e.lugar",
            "idImagen" => "e.id_imagen",
            "imagen" => "i.ruta",
            "fechaInicio" => "e.fecha_inicio",
            "horaInicio" => "e.hora_inicio",
            "idImagenAutor" => "i1.id",
            "imagenAutor" => "i1.ruta",
            "fechaFin" => "e.fecha_fin",
            "horaFin" => "e.hora_fin",
            "fechaCreacion" => "UNIX_TIMESTAMP(e.fecha_creacion)",
            "fechaActivacion" => "UNIX_TIMESTAMP(e.fecha_activacion)",
            "fechaInactivacion" => "UNIX_TIMESTAMP(e.fecha_inactivacion)",
            "activo" => "e.activo",
            "infoContacto" => "e.info_contacto"
        );

        if (!empty($condicion)) {
            $condicion .= " AND ";
        }

        $condicion .= "e.id_usuario = u.id AND u.id_persona = p.id AND p.id_imagen = i1.id AND e.id_imagen = i.id AND e.id_ciudad = c.id AND es.id = c.id_estado AND pa.id = es.id_pais  AND e.id_centro = ce.id AND ce.id_ciudad = c2.id";

        //compruebo si el perfil del usuario no es el de administrador y entonces restrinjo mas la consulta
        if ($idPerfil != 99) {
            if ($idPerfil != 0) {

                $condicionA = $condicion;

                $tablas["pi"] = "permisos_item";
                $columnas["idItem"] = "pi.id_item";
                $columnas["idPerfil"] = "pi.id_perfil";
                $columnas["idModulo"] = "pi.id_modulo";

                $condicion .= " AND pi.id_item = e.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' , '$idPerfil')";

                $condicion = "(" . $condicion . ")";
                $condicion .= "OR ($condicionA AND e.id_usuario = '$sesion_usuarioSesion->id'";

                $condicion .= ")";
            }//fin del if($idPerfil != 0)                
        } else {
            $tablas["pi"] = "permisos_item";
            $columnas["idItem"] = "pi.id_item";
            $columnas["idPerfil"] = "pi.id_perfil";
            $columnas["idModulo"] = "pi.id_modulo";

            $condicion.= " AND pi.id_item = e.id AND pi.id_modulo = '" . $idModulo . "' AND pi.id_perfil IN ( '99' )";
        }


        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "e.id", $orden, $inicio, $cantidad);

        $lista = array();
        if ($sql->filasDevueltas) {
            while ($evento = $sql->filaEnObjeto($consulta)) {
                $evento->url = $this->urlBase . "/" . $evento->id;
                $evento->imagenPrincipal = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesDinamicas"] . "/" . $evento->imagen;
                $evento->imagenMiniatura = $configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesMiniaturas"] . "/" . $evento->imagen;
                $lista[] = $evento;
            }
        }

        return $lista;
    }


    /**
     *
     * @global type $textos
     * @global type $configuracion
     * @global type $sesion_usuarioSesion
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

        $condicion = " e.fecha_inicio BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 8 DAY) AND ";

        $arregloEventos = $this->listarProximosEventos(0, 5, array($this->id), $condicion, $idTipo, $this->idModulo);
        $listaProximosEventos = array($textos->id("PROXIMOS_EVENTOS"));
        $listaEventos = array();


        if (sizeof($arregloEventos) > 0) {

            foreach ($arregloEventos as $elemento) {
                $item = "";

                if ($elemento->activo) {
                    $comentario = new Comentario();

                    $contenedorComentarios = $comentario->mostrarComentarios($elemento->idModulo, $elemento->id);
                    $contenedorMeGusta = Recursos::mostrarContadorMeGusta($elemento->idModulo, $elemento->id);
                    $comentarios = HTML::contenedor($contenedorComentarios . $contenedorMeGusta, "mostrarPosted");
                    //seleccionar el genero de una persona 
                    $usuario = new Usuario();
                    $item .= HTML::enlace(HTML::imagen($elemento->imagenMiniatura, "flotanteIzquierda  margenDerecha miniaturaListaUltimos5"), HTML::urlInterna("EVENTOS", $elemento->id));
                    $item .= HTML::parrafo(HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . $usuario->getGenero($elemento->idAutor) . ".png") . preg_replace("/\%1/", HTML::enlace($elemento->autor, HTML::urlInterna("USUARIOS", $elemento->usuarioAutor)) . "On " . HTML::frase(date("D, d M Y", $elemento->fechaPublicacion), "pequenia cursiva negrilla") . $comentarios, $textos->id("PUBLICADO_POR")));
                    $item2 = HTML::enlace(HTML::parrafo($elemento->titulo, "negrilla"), $elemento->url);
                    $item2 .= HTML::parrafo($elemento->resumen, "pequenia cursiva");
                    $item .= HTML::contenedor($item2, "fondoUltimos5GrisB"); //barra del contenedor gris

                    $item = HTML::contenedor($item, "contenedorListaMasNoticias", "contenedorListaNoticias" . $elemento->id);

                    $listaEventos[] = $item;
                }//fin del  SI Blog es activo
            }//fin del foreach

            $acordeon = HTML::acordeonLargo2($listaProximosEventos, $listaEventos, "masEventos", "");
        }//fin del if  
        return $acordeon;
    }

}

?>