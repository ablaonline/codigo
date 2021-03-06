<?php

/**
 * @package     FOLCS
 * @subpackage  Registro
 * @author      Pablo A. V�lez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 CENTRO CULTURAL COLOMBO AMERICANO
 * @version     0.2
 * */
class Registro {

    /**
     * C�digo interno o identificador del registro en la base de datos
     * @var entero
     */
    public $id;

    /**
     * C�digo interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * URL relativa del m�dulo de registros
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de un registro espec�fica
     * @var cadena
     */
    public $url;

    /**
     * C�digo interno o identificador del usuario creador de la registro en la base de datos
     * @var entero
     */
    public $nombres;

    /**
     * C�digo interno o identificador del modulo
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
     * T�tulo de la registro
     * @var cadena
     */
    public $ciudad;

    /**
     * T�tulo de la registro
     * @var cadena
     */
    public $pais;

    /**
     * Indicador de si ya ha realizado el pago
     * @var l�gico
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
     * Fecha de creaci�n de la registro
     * @var fecha
     */
    public $tituloCarnet;

    /**
     * Fecha de creaci�n de la registro
     * @var fecha
     */
    public $rol;

    /**
     * Fecha de creaci�n de la registro
     * @var fecha
     */
    public $fechaRegistro;

    /**
     * Fecha de publicaci�n de la registro
     * @var fecha
     */
    public $nombreCertificado;

    /**
     * Indicador de disponibilidad del registro
     * @var l�gico
     */
    public $activo;

    /**
     * Indicador del orden cronol�gio de la lista de registros
     * @var l�gico
     */
    public $listaAscendente = false;

    /**
     * N�mero de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * N�mero de registros de la lista
     * @var entero
     */
    public $pagados = NULL;

    /**
     * N�mero de registros activos de la lista de foros
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
     * @param entero $id C�digo interno o identificador del objeto en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql;

        $modulo = new Modulo('REGISTRO');
        $this->urlBase = '/' . $modulo->url;
        $this->url = $modulo->url;
        $this->idModulo = $modulo->id;

        $consulta = $sql->obtenerValor('registro', 'COUNT(id)', '');
        $this->registros = $consulta;

        $consulta = $sql->obtenerValor('registro', 'COUNT(id)', ' pagado = "1"');
        $this->pagados = $consulta;


        if (isset($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos de unobjeto
     * @param entero $id C�digo interno o identificador del objeto en la base de datos
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
     * @return entero               C�digo interno o identificador del objeto en la base de datos (NULL si hubo error)
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

            $mensaje = str_replace('%1', utf8_decode($datosPersona['nombres']) . ' ' . utf8_decode($datosPersona['apellidos']), $textos->id('CONTENIDO_MENSAJE_REGISTRO_ABLA2012'));
            $mensaje = str_replace('%2', $registroPdf, $mensaje);
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
            $pdf->Cell(190, 9, 'Av. Abraham Lincoln #21, Santo Domingo, Rep�blica Dominicana,', 0, 0, 'C');

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
     * @return l�gico               Indica si el procedimiento se pudo realizar correctamente o no
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
     * @param entero $id    C�digo interno o identificador de la registro en la base de datos
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
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
     * @param entero  $cantidad    N�mero de ciudadesa incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los c�digos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condici�n adicional (SQL)
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

        /*         * * Validar que la condici�n sea una cadena de texto ** */
        if (!is_string($condicionGlobal)) {
            $condicion = '';
        }

        /*         * * Validar que la excepci�n sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion = 'r.id NOT IN (' . $excepcion . ') ';
        }

        /*         * * Definir el orden de presentaci�n de los datos ** */
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
     * @global type $textos
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