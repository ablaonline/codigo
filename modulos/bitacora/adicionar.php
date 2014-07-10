<?php

/**
*
* Copyright (C) 2008 Felinux Ltda
* Francisco J. Lozano B. <fjlozano@felinux.com.co>
*
* Este archivo es parte de:
* PANCE :: Plataforma para la Administración del Nexo Cliente-Empresa
*
* Este programa es software libre: usted puede redistribuirlo y/o
* modificarlo  bajo los términos de la Licencia Pública General GNU
* publicada por la Fundación para el Software Libre, ya sea la versión 3
* de la Licencia, o (a su elección) cualquier versión posterior.
*
* Este programa se distribuye con la esperanza de que sea útil, pero
* SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita MERCANTIL o
* de APTITUD PARA UN PROPÓSITO DETERMINADO. Consulte los detalles de
* la Licencia Pública General GNU para obtener una información más
* detallada.
*
* Debería haber recibido una copia de la Licencia Pública General GNU
* junto a este programa. En caso contrario, consulte:
* <http://www.gnu.org/licenses/>.
*
**/

/*** Devolver datos para autocompletar la búsqueda ***/
if (isset($url_completar)){
    if (($url_item) == "selector1") {
        echo SQL::datosAutoCompletar("seleccion_estudiantes", $url_q);
    }
    exit;
}

/*** Devolver datos para cargar la lista de facturas ***/
if(isset($url_verificarFacturas) && isset($url_id_estudiante)){
    $lista = array();
    if($url_id_estudiante){
        $consulta = SQL::seleccionar(array("facturas f", "matriculas m", "resoluciones_facturacion r", "cursos c", "programacion p"),array("m.id AS id", "IF(m.id_factura = 0,CONCAT('COM-',substring(m.comprobante,3)),CONCAT(r.prefijo, '-' , substring(cast(f.numero as char(8)),3))) AS nombre"), "m.id_estudiante = '$url_id_estudiante' AND m.id_factura = f.id AND m.id_curso = p.id AND p.id_curso = c.id AND m.estado = '1' AND c.nombre like '%met%' AND f.id_resolucion = r.id AND m.id NOT IN (SELECT id_matricula FROM pance_met)");
    }
    if(SQL::filasDevueltas($consulta)){
        while ($datos = SQL::filaEnObjeto($consulta)) {
            $lista[$datos->id] = $datos->nombre;
        }
    }
    HTTP::enviarJSON($lista);
exit;
}

/*** Generar el formulario para la captura de datos ***/
if (!empty($url_generar)) {
    $error  = "";
    $titulo = $componente->nombre;

    /*** Definición de pestañas para datos del tercero***/
    $formularios["PESTANA_PRINCIPAL"] = array(
        array(
            HTML::campoTextoCorto("*selector1", $textos["NOMBRE_ESTUDIANTE"], 30, 255, "", array("title" => $textos["AYUDA_NOMBRE_ESTUDIANTE"], "class" => "autocompletable", "onChange" => "cargarFacturas();", "onBlur" => "cargarFacturas();"))
            .HTML::campoOculto("id_estudiante", "")
        ),
        array(
			HTML::listaSeleccionSimple("*factura", $textos["FACTURA"], "","", array("title" => $textos["AYUDA_FACTURA"]))
		)
    );

    /*** Definición de botones ***/
    $botones = array(
        HTML::boton("botonAceptar", $textos["ACEPTAR"], "adicionarItem();", "aceptar"),
    );

    $contenido = HTML::generarPestanas($formularios, $botones);

    /*** Enviar datos para la generación del formulario al script que originó la petición ***/
    $respuesta[0] = $error;
    $respuesta[1] = $titulo;
    $respuesta[2] = $contenido;
    HTTP::enviarJSON($respuesta);


/*** Adicionar los datos provenientes del formulario ***/
} elseif (!empty($forma_procesar)) {
    /*** Asumir por defecto que no hubo error ***/
    $error   = false;
    $mensaje = $textos["ITEM_ADICIONADO"];
    $existe_met = SQL::existeItem("met", "id_matricula", $forma_factura);
        
    if(empty($forma_id_estudiante)){
        $error   = true;
        $mensaje = $textos["ESTUDIANTE_VACIO"];
        $ruta_archivo = "";
    
	}else if($existe_met){
		$error   = true;
        $mensaje = $textos["EXISTE_REGISTRO_MET"];
        $ruta_archivo = "";
        
    }else{
		
		$consecutivo = SQL::obtenerValor("met","max(consecutivo)+1","");
		
		/** Genera el pdf del registro del met **/
		$nombre         = "";
		$nombreArchivo  = "";
		$ruta_archivo   = "";
		
		do {
            $nombre         = Cadena::generarCadenaAleatoria(8).".pdf";
            $subcarpeta     = substr($nombre, 0, 2);
            $nombreArchivo  = $rutasGlobales["archivos"]."/met/$subcarpeta/$nombre";
        } while (file_exists($nombreArchivo));
        
        if(!file_exists($rutasGlobales["archivos"]."/met/$subcarpeta")){
			$creaCarpeta = mkdir($rutasGlobales["archivos"]."/met/$subcarpeta", 0777, true);
		}
		
		$tabla		 = array("programacion p","matriculas m","estudiantes e","terceros t","municipios mun","departamentos dep","paises pai");
		$columna 	 = array("p.fecha_inicio AS examen","t.documento_identidad AS identificacion","t.primer_nombre AS p_nombre",
		"t.segundo_nombre AS s_nombre","t.primer_apellido AS p_apellido","t.segundo_apellido AS s_apellido","t.direccion_principal AS direccion",
		"t.telefono_principal AS telefono","t.celular AS celular","mun.nombre AS ciudad","pai.nombre AS pais","t.genero AS genero",
		"t.fecha_nacimiento AS nacimiento","m.fecha AS matricula","t.correo AS email","p.hora_inicio AS hora","e.id_idioma AS idioma","e.id_profesion AS profesion");
		$condicion   = "m.id = '$forma_factura' AND m.id_curso = p.id AND m.id_estudiante = e.id AND e.id_tercero = t.id 
		AND e.id_municipio_origen = mun.id AND mun.id_departamento = dep.id AND dep.id_pais = pai.id";
		$consulta_met = SQL::seleccionar($tabla, $columna, $condicion);
		$datos = SQL::filaEnObjeto($consulta_met);
		
		if($datos->idioma == 148){
			$idioma = "Español";
		}else if($datos->idioma == 41){
			$idioma = "Inglés";
		}else{
			$idioma = SQL::obtenerValor("idiomas","nombre","id = $datos->idioma");
		}
		
		$profesion = SQL::obtenerValor("profesiones_oficios","descripcion","id = $datos->profesion");
		
		$fecha_matricula	= explode("-", $datos->matricula);
		$fecha_hora			= explode(" ", $fecha_matricula[2]);
		$dia_matricula 		= $fecha_hora[0];
		$mes_matricula		= $fecha_matricula[1];
		$año_matricula		= $fecha_matricula[0];
		
		$fecha_examen 		= explode("-", $datos->examen);
		$dia_examen	 		= $fecha_examen[2];
		$mes_examen			= $fecha_examen[1];
		$año_examen			= $fecha_examen[0];
		
		$fecha_nacimiento 	= explode("-", $datos->nacimiento);
		$dia_nacimiento	 	= $fecha_nacimiento[2];
		$mes_nacimiento		= $fecha_nacimiento[1];
		$año_nacimiento		= $fecha_nacimiento[0];
		
		$archivo = new PDF("P","mm","Letter");
		$archivo->AddPage();	
		$archivo->Image($rutasGlobales["imagenes"].'/R-MET.jpg', 0, 0, 215.9, 279.4);
		
		$archivo->setXY(158,40);
		$archivo->SetFont('Arial','',12);
		$archivo->Cell(30,4, $mes_matricula."/".$dia_matricula."/".$año_matricula);
		$archivo->setXY(140,68);
		$archivo->Cell(30,4, $datos->identificacion);
		
		$registro = str_pad($consecutivo, 9, "0", STR_PAD_LEFT);

		$archivo->SetFont('Arial','',16);
		$archivo->setXY(148.5,55);
		$archivo->Cell(30,4, $registro[0]);
		$archivo->setXY(154.5,55);
		$archivo->Cell(30,4, $registro[1]);
		$archivo->setXY(160.5,55);
		$archivo->Cell(30,4, $registro[2]);
		$x = 20.5;
		$archivo->setXY(148.5 + $x,55);
		$archivo->Cell(30,4, $registro[3]);
		$archivo->setXY(154.5 + $x,55);
		$archivo->Cell(30,4, $registro[4]);
		$archivo->setXY(160.5 + $x,55);
		$archivo->Cell(30,4, $registro[5]);
		$x = 38;
		$archivo->setXY(148.5 + $x,55);
		$archivo->Cell(30,4, $registro[6]);
		$archivo->setXY(154.5 + $x,55);
		$archivo->Cell(30,4, $registro[7]);
		$archivo->setXY(160.5 + $x,55);
		$archivo->Cell(30,4, $registro[8]);
		
		$archivo->setXY(12,89);
		$archivo->SetFont('Arial','',12);
		$archivo->Cell(30,4, $datos->p_nombre);
		$archivo->setXY(76,89);
		$archivo->Cell(30,4, $datos->s_nombre);
		$archivo->SetFont('Arial','',12);
		$archivo->setXY(127,89);
		$archivo->Cell(30,4,$datos->p_apellido);
		$archivo->SetFont('Arial','',12);
		$archivo->setXY(162,89);
		$archivo->Cell(30,4,$datos->s_apellido);
		
		$archivo->setXY(31,101);
		$archivo->SetFont('Arial','',10);
		$archivo->Cell(30,4, $datos->direccion);
		$archivo->setXY(148,101);
		$archivo->Cell(30,4, $datos->ciudad);
		$archivo->setXY(31,112);
		$archivo->Cell(30,4, $datos->pais);
		$archivo->setXY(100,112);
		$archivo->Cell(30,4, $datos->telefono);
		$archivo->setXY(152,112);
		$archivo->Cell(30,4, $datos->celular);
		
		if ($datos->genero == "M") {
			$archivo->setXY(26,131);
		} else {
			$archivo->setXY(40,131);
		}

		$archivo->SetFont('Arial','B',12);
		$archivo->Cell(30,4, "X");

		$archivo->setXY(90,131);
		$archivo->SetFont('Arial','',12);
		$archivo->Cell(30,4, $mes_nacimiento."/".$dia_nacimiento."/".$año_nacimiento);

		$archivo->setXY(135,131);
		$archivo->SetFont('Arial','',10);
		$archivo->Cell(30,4, $datos->email);
		$archivo->setXY(43,147);
		$archivo->Cell(30,4, $idioma);
		$archivo->setXY(125,147);
		$archivo->Cell(30,4, substr($profesion,0,48));
		$archivo->setXY(150,160);
		$archivo->Cell(30,4, $mes_examen."/".$dia_examen."/".$año_examen);
		$archivo->setXY(144,175);
		$archivo->Cell(30,4, substr($datos->hora, 0, 5));
		
		$archivo->Output($nombreArchivo, "F");
		$longitud = filesize($nombreArchivo);
		SQL::insertar("archivos", array("titulo" => "Registro_Met_No.".$consecutivo, "ruta" => "/met/$subcarpeta/$nombre", "formato"=> "pdf", "longitud"=> $longitud, "id_usuario"=> $sesion_id_usuario));
		$id_archivo = SQL::$ultimoId;
		$ruta_archivo = HTTP::generarURL("DESCARCH")."&id=".$id_archivo."&temporal=0";
		
		/** Inserta en la tabla del met **/
		$datos = array(
			"id_matricula"       => $forma_factura,
			"id_archivo"         => $id_archivo,
			"consecutivo"        => $consecutivo
		);

		$insertar = SQL::insertar("met", $datos);

		if(!$insertar){
			$error   = true;
			$mensaje = $textos["ERROR_ADICIONAR_MET"];
		}
		
	}
    
    /*** Enviar datos con la respuesta del proceso al script que originó la petición ***/
    $respuesta    = array();
    $respuesta[0] = $error;
    $respuesta[1] = $mensaje;
    $respuesta[2] = $ruta_archivo;
    HTTP::enviarJSON($respuesta);
}
?>
