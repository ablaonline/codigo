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

                        $notificacion = str_replace('%1', HTML::enlace($objetoCurso->autor, HTML::urlInterna('CURSOS', $idCurso)), $textos->id('MENSAJE_ADICION_ITEM_CURSO'));
                        $notificacion = str_replace('%2', HTML::enlace($tipoItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion);
                        $notificacion = str_replace('%3', HTML::enlace($objetoCurso->nombre, HTML::urlInterna('CURSOS', $idCurso)), $notificacion);
                        $notificacion = str_replace('%4', HTML::enlace($nombreItem, HTML::urlInterna('CURSOS', $idCurso)), $notificacion);

                        Servidor::notificar($seguidor->id_usuario, $notificacion);
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
     * @global type $textos
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
     * @global type $sql
     * @global type $configuracion
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
     * @global type $sql
     * @global type $configuracion
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