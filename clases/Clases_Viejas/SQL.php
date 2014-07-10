<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.1
 *
 * */
class SQL {

    /**
     * Nombre o dirección IP del servidor de bases de datos MySQL
     * @var cadena
     */
    public $servidor;

    /**
     * Nombre de usuario para la conexión al servidor de bases de datos MySQL
     * @var cadena
     */
    public $usuario;

    /**
     * Contraseña del usuario para la conexión al servidor de bases de datos MySQL
     * @var cadena
     */
    public $contrasena;

    /**
     * Nombre de la base datos para la conexión al servidor de bases de datos MySQL
     * @var cadena
     */
    public $baseDatos;

    /**
     * Prefijo para las tablas y vistas del proyecto en la base de datos MySQL
     * @var cadena
     */
    public $prefijo;

    /**
     * Gestor de la conexión a la base de datos MySQL
     * @var recurso
     */
    public $conexion;

    /**
     * Número asignado para el último registro adicionado mediante incremento automático
     * @var entero
     */
    public $ultimoId;

    /**
     * Número de filas devueltas por una consulta
     * @var entero
     */
    public $filasDevueltas;

    /**
     * Número de filas afectadas por una consulta
     * @var recurso
     */
    public $filasAfectadas;

    /**
     * Número de consultas realizadas en cada página generada
     * @var entero
     */
    public $consultas;

    /**
     * Tiempo total empleado para las consultas realizadas (en segundos)
     * @var flotante
     */
    public $tiempo;

    /**
     * Tiempo total empleado para las consultas realizadas (en segundos)
     * @var flotante
     */
    public $sentenciaSql;

    /**
     * Depurar las consultas realizadas en la base de datos MySQL mediante los archivos de registro (logs)
     * @var lógico
     */
    public $depurar = false;

    /**
     * Depurar las consultas realizadas en la base de datos MySQL mediante los archivos de registro (logs)
     * @var lógico
     */
    public $guardarBitacora = true;

    /**
     *
     * Inicializar la clase estableciendo una conexión con el servidor de bases de datos MySQL
     *
     * @param cadena $servidor      Nombre o dirección IP del servidor de bases de datos MySQL
     * @param cadena $usuario       Nombre de usuario para la conexión al servidor de bases de datos MySQL
     * @param cadena $contrasena    Contraseña del usuario para la conexión al servidor de bases de datos MySQL
     * @param cadena $nombre        Nombre de la base datos para la conexión al servidor de bases de datos MySQL
     * @return                      recurso
     *
     */
    function __construct($servidor = "", $usuario = "", $contrasena = "", $nombre = "") {
        global $configuracion;

        if (empty($servidor) && empty($usuario) && empty($usuario) && empty($usuario)) {
            $this->servidor = $configuracion["BASEDATOS"]["servidor"];
            $this->usuario = $configuracion["BASEDATOS"]["usuario"];
            $this->contrasena = $configuracion["BASEDATOS"]["contraseña"];
            $this->baseDatos = $configuracion["BASEDATOS"]["nombre"];
            $this->prefijo = $configuracion["BASEDATOS"]["prefijo"];
        } else {
            $this->servidor = $servidor;
            $this->usuario = $usuario;
            $this->contrasena = $contrasena;
            $this->baseDatos = $nombre;
            $this->prefijo = "";
        }

        $this->conectar();
    }

    /**
     *
     * Establecer una conexión con el servidor de bases de datos MySQL
     *
     * @param cadena $servidor      Nombre o dirección IP del servidor de bases de datos MySQL
     * @param cadena $usuario       Nombre de usuario para la conexión al servidor de bases de datos MySQL
     * @param cadena $contrasena    Contraseña del usuario para la conexión al servidor de bases de datos MySQL
     * @param cadena $nombre        Nombre de la base datos para la conexión al servidor de bases de datos MySQL
     * @return                      recurso
     *
     */
    public function conectar($servidor = "", $usuario = "", $contrasena = "", $baseDatos = "") {
        $conexion = mysql_connect($this->servidor, $this->usuario, $this->contrasena);
        $resultado = mysql_select_db($this->baseDatos);
        $this->conexion = $conexion;
    }

    /**
     *
     * Finalizar una conexión con el servidor de bases de datos MySQL
     *
     * @param recurso $conexion     Gestor de la conexión a la base de datos MySQL
     * @return                      lógico
     *
     */
    public function desconectar($conexion = "") {

        if (empty($conexion)) {
            $cierre = mysql_close($this->conexion);
        } else {
            $cierre = mysql_close($conexion);
        }
    }

    /**
     *
     * Ejecutar una consulta en el servidor de bases de datos MySQL
     *
     * @param cadena $consulta      Instrucción SQL a ejecutar
     * @return                      recurso
     *
     */
    public function ejecutar($consulta) {
        global $modulo, $sesion_usuarioSesion;

        $this->consultas++;
        $this->filasDevueltas = NULL;
        $this->filasAfectadas = NULL;
        $horaInicio = microtime(true);
        $resultado = mysql_query($consulta);
        $horaFinalizacion = microtime(true);
        $this->tiempo += round($horaFinalizacion - $horaInicio, 4);




        if (preg_match("/^(SELECT|SHOW)/", $consulta) && !mysql_errno()) {
            $this->filasDevueltas = mysql_num_rows($resultado);
        } else {
            $this->filasAfectadas = mysql_affected_rows($this->conexion);

	  //Funciones para guardar registro de actividades en la bitacora
	  if($this->guardarBitacora){
	    $tipo = '';


	      if (isset($sesion_usuarioSesion) && !empty($sesion_usuarioSesion->usuario)) {
		  $username = $sesion_usuarioSesion->usuario;
	      } else {
		  $username = 'sin sesion';
	      }
	      if (preg_match("/INSERT/", $consulta)) {
		$tipo = 'INSERT';
		$this->ultimoId = @mysql_insert_id($this->conexion);
	      } else if (preg_match("/DELETE/", $consulta)) {
		$tipo = 'DELETE';
	      } else if (preg_match("/UPDATE/", $consulta)) {
		$tipo = 'UPDATE';
	      }

	    $sentencia = "INSERT INTO folcs_bitacora (usuario, ip, tipo, consulta, fecha, modulo) VALUES ('$username', '" . Recursos::getRealIP() . "', '$tipo', '" . addslashes($consulta) . "', '" . date('Y-m-d H:i:s') . "', '$modulo->nombre')";
	    mysql_query($sentencia);
	  }

	  $this->guardarBitacora = true;

        }



        if (mysql_errno() || $this->depurar) {
            openlog("FOLCS", LOG_PID, LOG_LOCAL0);
            $log = syslog(LOG_DEBUG, $consulta);

            if (mysql_errno()) {
                $log = syslog(LOG_DEBUG, mysql_error());
            }

            $this->depurar = false;
        }

        return $resultado;
    }

    /**
     *
     * Convertir el recurso resultante de una consulta en un objeto
     *
     * @param recurso $resultado    Recurso resultante de una consulta
     * @return                      objeto
     *
     */
    public function filaEnObjeto($resultado) {
        $fila = mysql_fetch_object($resultado);
        return $fila;
    }

    /**
     *
     * Convertir el recurso resultante de una consulta en un arreglo
     *
     * @param recurso $resultado    Recurso resultante de una consulta
     * @return                      arreglo
     *
     */
    public function filaEnArreglo($resultado) {
        $fila = mysql_fetch_array($resultado, MYSQL_NUM);
        return $fila;
    }

    /**
     *
     * Convertir el recurso resultante de una consulta en un arreglo ASOCIATIVO
     *
     * @param recurso $resultado    Recurso resultante de una consulta
     * @return                      arreglo
     *
     */
    public function filaEnArregloAsoc($resultado) {
        $fila = mysql_fetch_assoc($resultado, MYSQL_NUM);
        return $fila;
    }

    /**
     *
     * Obtener una lista con los nombres de las columnas o campos de una tabla
     *
     * @param cadena $tabla         Nombre de la tabla
     * @return                      arreglo
     *
     */
    public function obtenerColumnas($tabla) {
        $tabla = $this->prefijo . $tabla;
        $columnas = array();
        $resultado = $this->ejecutar("SHOW COLUMNS FROM $tabla");

        while ($datos = $this->filaEnArreglo($resultado)) {
            $columnas[] = $datos[0];
        }

        return $columnas;
    }

    /**
     *
     * Seleccionar datos de una o varias tablas del servidor de bases de datos MySQL
     *
     * @return recurso
     *
     */
    public function seleccionar($tablas, $columnas, $condicion = "", $agrupamiento = "", $ordenamiento = "", $filaInicial = NULL, $numeroFilas = NULL) {
        $listaColumnas = array();
        $listaTablas = array();
        $limite = "";

        foreach ($columnas as $alias => $columna) {

            if (preg_match("/(^[a-zA-z]+[a-zA-Z0-9]*)/", $alias)) {
                $alias = " AS $alias";
            } else {
                $alias = "";
            }

            $listaColumnas[] = $columna . $alias;
        }

        $columnas = implode(", ", $listaColumnas);

        foreach ($tablas as $alias => $tabla) {

            if (preg_match("/(^[a-zA-z]+[a-zA-Z0-9]*)/", $alias)) {
                $alias = " AS $alias";
            } else {
                $alias = "";
            }

            $tabla = $this->prefijo . $tabla;
            $listaTablas[] = $tabla . $alias;
        }

        $tablas = implode(", ", $listaTablas);

        if (!empty($condicion)) {
            $condicion = " WHERE $condicion";
        }

        if (!empty($agrupamiento)) {
            $agrupamiento = " GROUP BY $agrupamiento";
        }

        if (!empty($ordenamiento)) {
            $ordenamiento = " ORDER BY $ordenamiento";
        }

        if (is_int($numeroFilas) && $numeroFilas > 0) {
            $limite = " LIMIT ";

            if (is_int($filaInicial) && $filaInicial >= 0) {
                $limite .= "$filaInicial, ";
            }

            $limite .= $numeroFilas;
        }

        $tablas = implode(", ", $listaTablas);
        $sentencia = "SELECT $columnas FROM $tablas" . $condicion . $agrupamiento . $ordenamiento . $limite;

        $this->sentenciaSql = $sentencia;

        return $this->ejecutar($sentencia);
    }

    /*     * * Insertar datos en la tabla ** */

    public function insertar($tabla, $datos) {
        $tabla = $this->prefijo . $tabla;

        if (is_array($datos) && count($datos) > 0) {

            $campos = array();
            $valores = array();

            foreach ($datos as $campo => $valor) {

                if ($valor != "") {
                    $campos[] = $campo;

                    if (Variable::contieneUTF8($valor)) {
                        $valor = Variable::codificarCadena($valor);
                    }

                    $valores[] = "'$valor'";
                }
            }

            $campos = implode(",", $campos);
            $valores = implode(",", $valores);
            $sentencia = "INSERT INTO $tabla ($campos) VALUES ($valores)";
        }

        $resultado = $this->ejecutar($sentencia);
        

        return $resultado;
    }

    /*     * * Reemplazar datos existentes en la tabla o insertarlos si no existen ** */

    public function reemplazar($tabla, $datos) {

        $tabla = $this->prefijo . $tabla;

        if (is_array($datos) && count($datos) > 0) {
            $campos = array();
            $valores = array();

            foreach ($datos as $campo => $valor) {
                $campos[] = $campo;

                if (Variable::contieneUTF8($valor)) {
                    $valor = Variable::codificarCadena($valor);
                }

                $valores[] = "'$valor'";
            }

            $campos = implode(", ", $campos);
            $valores = implode(", ", $valores);
            $sentencia = "REPLACE INTO $tabla ($campos) VALUES ($valores)";
        }

        $resultado = $this->ejecutar($sentencia);

        return $resultado;
    }

    /*     * * Modificar datos existentes en la tabla de acuerdo con una condición ** */

    public function modificar($tabla, $datos, $condicion) {
        $tabla = $this->prefijo . $tabla;

        if (is_array($datos) && count($datos) > 0) {
            $campos = array();
            $valores = array();

            foreach ($datos as $campo => $valor) {

                if ($valor != "") {

                    if (Variable::contieneUTF8($valor)) {
                        $valor = Variable::codificarCadena($valor);
                    }

                    $valores[] = "$campo='$valor'";
                    $campos["$campo"] = "'$valor'";
                } else {
                    $valores[] = "$campo=NULL";
                    $campos["$campo"] = "NULL";
                }
            }

            $valores = implode(", ", $valores);
            $sentencia = "UPDATE $tabla SET $valores WHERE $condicion";
        }

        $resultado = $this->ejecutar($sentencia);

        return $resultado;
    }

    /*     * * Eliminar datos de una tabla que coincidan con una condición  ** */

    public function eliminar($tabla, $condicion) {
        $tabla = $this->prefijo . $tabla;
        $sentencia = "DELETE FROM $tabla WHERE $condicion";

        $resultado = $this->ejecutar($sentencia);

        return $resultado;
    }

    /*     * * Insertar datos en la tabla de imágenes o de archivos adjuntos ** */

    public function insertarArchivo($tabla, $datos) {

        $tabla = $this->prefijo . $tabla;

        if (is_array($datos) && count($datos) > 0) {

            foreach ($datos as $campo => $valor) {

                if ($valor != "") {
                    $campos[] = $campo;
                    $valores[] = "'" . mysql_real_escape_string($valor) . "'";
                }
            }

            $campos = implode(",", $campos);
            $valores = implode(",", $valores);
            $sentencia = "INSERT INTO $tabla ($campos) VALUES ($valores)";
        }

        $resultado = $this->ejecutar($sentencia);

        return $resultado;
    }

    /*     * * Verificar si un registro con un valor específico existe en una tabla ** */

    /**
     *
     * @param type $tabla
     * @param type $columna
     * @param type $valor
     * @param type $condicionExtra
     * @return type boolean
     */
    public function existeItem($tabla, $columna, $valor, $condicionExtra = "") {
        $tablas = array($tabla);
        $columnas = array($columna);
        $condicion = "$columna = '$valor'";

        if (!empty($condicionExtra)) {
            $condicion .= " AND $condicionExtra";
        }

        $resultado = $this->seleccionar($tablas, $columnas, $condicion);

        if ($this->filasDevueltas) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*     * * Obtener el valor de un campo en una tabla cuyo registro (único) coincida con una condición dada ** */

    public function obtenerValor($tabla, $columna, $condicion) {
        $tablas = array($tabla);
        $columnas = array($columna);
        //$this->depurar = true;
        $resultado = $this->seleccionar($tablas, $columnas, $condicion);

        if ($this->filasDevueltas == 1) {
            $datos = $this->filaEnObjeto($resultado);
            $valor = $datos->$columna;
            return $valor;
        } else {
            return FALSE;
        }
    }

    /*     * * Realizar búsqueda y devolver filas coincidentes ???** */

    public function evaluarBusqueda($vistaBuscador, $vistaMenu) {
        global $componente, $url_buscar, $url_expresion, $sesion_expresion, $sesion_origenExpresion;

        $tabla = $this->prefijo . $vistaBuscador;
        $camposBuscador = $this->obtenerColumnas($vistaBuscador);
        $camposMenu = $this->obtenerColumnas($vistaMenu);
        $campoClave = $camposMenu[0];
        $condicionFinal = "$campoClave IS NOT NULL";

        /*         * * Verificar si la solicitud proviene del formulario de búsqueda ** */
        if (isset($url_buscar)) {
            if (!empty($url_expresion)) {
                Sesion::registrar("expresion", $url_expresion);
                Sesion::registrar("origenExpresion", $componente->id);
            } else {
                Sesion::borrar("expresion");
                unset($sesion_expresion);
                Sesion::borrar("origenExpresion");
                unset($sesion_origenExpresion);
            }
        } else {
            $condicion = "";
        }

        /*         * * Verificar si se está en medio de de una búusqueda ** */
        if (!empty($sesion_expresion) && ($sesion_origenExpresion == $componente->id)) {
            $expresion = Texto::expresionRegular($sesion_expresion);
            $campoInicial = true;
            $listaCampos = array();

            foreach ($camposBuscador as $campo) {
                if (!$campoInicial) {
                    $listaCampos[] = "$tabla.$campo REGEXP '$expresion'";
                }

                $campoInicial = false;
            }

            $condicion = "(" . implode(" OR ", $listaCampos) . ")";
            $tablas = array($vistaBuscador);
            $columnas = array($camposBuscador[0]);
            $consulta = $this->seleccionar($tablas, $columnas, $condicion);

            if ($this->filasDevueltas) {
                $lista = array();

                while ($datos = $this->filaEnObjeto($consulta)) {
                    $lista[] = $datos->id;
                }

                $condicionFinal = "$campoClave IN (" . implode(",", $lista) . ")";
            } else {
                $condicionFinal = "$campoClave IN (NULL)";
            }
        } else {
            Sesion::borrar("expresion");
            unset($sesion_expresion);
            Sesion::borrar("origenExpresion");
            unset($sesion_origenExpresion);
        }

        return $condicionFinal;
    }

    /*     * * Devolver lista de elementos que coincidan con la búsqueda parcial del usuario para autocompletar ** */

    public function datosAutoCompletar($tabla, $patron) {
        $columnas = $this->obtenerColumnas($tabla);
        $primera = true;
        $lista = array();
        $patron = Texto::expresionRegular($patron, false);

        foreach ($columnas as $columna) {

            if ($primera) {
                $primera = false;
                continue;
            }

            $consulta = $this->seleccionar(array($tabla), array($columna), "CAST($columna AS CHAR) REGEXP '$patron'");

            while ($datos = $this->filaEnArreglo($consulta)) {
                $lista[] = $datos[0];
            }
        }
        natsort($lista);
        $lista = implode("\n", array_unique($lista));
        return $lista;
    }

    /*     * * Devuelve una condicion para el orden de presentacion de los datos ** */

    public function ordenColumnas($columna = "") {
        global $url_orden, $sesion_columnaOrdenamiento, $sesion_origenOrdenamiento, $sesion_sentidoOrdenamiento, $componente;

        if (empty($columna)) {
            $columna = "id";
        }

        $ordenamiento = "";

        if (!empty($url_orden)) {

            if (empty($sesion_origenOrdenamiento) || ($sesion_origenOrdenamiento != $componente->id)) {
                Sesion::registrar("origenOrdenamiento", $componente->id);
            }

            if (empty($sesion_sentidoOrdenamiento)) {
                Sesion::registrar("sentidoOrdenamiento", "DESC");
            }

            if ($sesion_sentidoOrdenamiento == "DESC") {
                Sesion::registrar("sentidoOrdenamiento", "ASC");
            } else {
                Sesion::registrar("sentidoOrdenamiento", "DESC");
            }

            Sesion::registrar("columnaOrdenamiento", $url_orden);
            $ordenamiento = "$sesion_columnaOrdenamiento $sesion_sentidoOrdenamiento";
        } else {
            if (empty($sesion_origenOrdenamiento) || ($sesion_origenOrdenamiento != $componente->id)) {
                $ordenamiento = "$columna";
            } else {
                if (empty($sesion_columnaOrdenamiento)) {
                    $ordenamiento = "$columna";
                } else {
                    $ordenamiento = "$sesion_columnaOrdenamiento $sesion_sentidoOrdenamiento";
                }
            }
        }

        return $ordenamiento;
    }


}

?>