<?php

/**
 * @package     FOLCS
 * @subpackage  Centros
 * @author      Francisco J. Lozano b. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 * */
class Centro {

    /**
     * C�digo interno o identificador del centro en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del m�dulo de centros
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un centro espec�fica
     * @var cadena
     */
    public $url;

    /**
     * C�digo interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Nombre del centro
     * @var cadena
     */
    public $nombre;

    /**
     * Descripci�n del centro
     * @var cadena
     */
    public $descripcion;

    /**
     * P�gina web del centro
     * @var cadena
     */
    public $paginaWeb;

    /**
     * C�digo interno o identificador en la base de datos de la ciudad del centro binacional
     * @var entero
     */
    public $idCiudad;

    /**
     * Nombre de la ciudad del centro binacional al que pertenece persona
     * @var cadena
     */
    public $ciudad;

    /**
     * C�digo interno o identificador en la base de datos del estado del centro binacional
     * @var entero
     */
    public $idEstado;

    /**
     * Nombre del estado del centro binacional al que pertenece persona
     * @var cadena
     */
    public $estado;

    /**
     * C�digo interno o identificador en la base de datos del pa�s del centro binacional
     * @var entero
     */
    public $idPais;

    /**
     * Nombre del pa�s del centro binacional
     * @var cadena
     */
    public $pais;

    /**
     * C�digo interno o identificador en la base de datos de la imagen relacionada con el centro
     * @var entero
     */
    public $idLogo;

    /**
     * Ruta de la imagen del centro en tama�o normal
     * @var cadena
     */
    public $logo;

    /**
     * Indicador de disponibilidad del registro
     * @var l�gico
     */
    public $activo;

    /**
     * Determina en que orden se muestra la lista
     * @var l�gico
     */
    public $listaAscendente;

    /**
     * Inicializar el centro
     * @param entero $id C�digo interno o identificador del centro en la base de datos
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
     * @param entero $id C�digo interno o identificador del centro en la base de datos
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
     * @return entero               C�digo interno o identificador del centro en la base de datos (NULL si hubo error)
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
     * @return l�gico               Indica si el procedimiento se pudo realizar correctamente o no
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
     * @param entero $id    C�digo interno o identificador del centro en la base de datos
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
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
     * @param entero  $cantidad    N�mero de centros a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los c�digos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condici�n adicional (SQL)
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

        /*         * * Validar que la condici�n sea una cadena de texto ** */
        if (!is_string($condicion)) {
            $condicion = '';
        }

        /*         * * Validar que la excepci�n sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'b.id NOT IN ('.$excepcion.')';
        }

        /*         * * Definir el orden de presentaci�n de los datos ** */
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