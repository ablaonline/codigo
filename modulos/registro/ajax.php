<?php
/**
 * @package     FOLCS
 * @subpackage  Registro de personas para el evento AblaOnline
 * @author      
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 COLOMBOAMERICANO CALI
 * @version     0.1
 **/
global $url_accion, $forma_procesar, $forma_id, $forma_datos, $forma_pagina, $forma_orden, $forma_nombreOrden, $forma_rol, $forma_titulo_carnet;

if (isset($url_accion)) {
    switch ($url_accion) {
        case 'add'          : ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              adicionarRegistro($datos);
                              break;                         
        case 'see'          : verRegistro($forma_id);
                              break;
        case 'edit'         : ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              modificarRegistro($forma_id, $datos);
                              break;
        case 'delete'       : ($forma_procesar) ? $confirmado = true : $confirmado = false;
                              eliminarRegistro($forma_id, $confirmado);
                              break;
        case 'search'       : ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              buscarItem($forma_datos);
                              break;
        case 'move'         : paginador(htmlspecialchars($forma_pagina), htmlspecialchars($forma_orden), htmlspecialchars($forma_nombreOrden), htmlspecialchars($forma_consultaGlobal));
                              break;
        case 'checkPaid'    : registrarPago($forma_id);
                              break;
        case 'defineRol'    : definirRol($forma_id, $forma_rol);
                              break; 
        case 'defineTitle'  : definirTitulo($forma_id, $forma_titulo_carnet);
                              break;                           
        case 'printXls'     : imprimirExcel();
                              break;                            
        case 'printCertificate'   : imprimirCertificado($forma_id);
                                    break; 
        case 'printCertificates'  : imprimirCertificados();
                                    break;                                
        case 'printCarnet'        : imprimirEscarapela($forma_id);
                                    break;
        case 'printCarnets'       : imprimirEscarapelas();
                                    break;
        case 'printRegister'      : imprimirRegistro($forma_id);
                                    break;                                
    }
}

/**
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function verRegistro($id) {
    global $textos, $sql;

    if(!is_numeric($id) || !$sql->existeItem('registro', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $objeto         = new Registro($id);
    $destinoMod     = '/ajax'.$objeto->urlBase.'/edit';
    $destinoDel     = '/ajax'.$objeto->urlBase.'/delete';
    $destinoCer     = '/ajax'.$objeto->urlBase.'/printCertificate';
    $destinoCar     = '/ajax'.$objeto->urlBase.'/printCarnet';
    $destinoReg     = '/ajax'.$objeto->urlBase.'/printRegister';
    $respuesta      = array();
        
        $contenedor1 = '';
        $contenedor2 = '';
      
        $campos  = HTML::campoOculto('id', $id);
        $codigo  = HTML::parrafo($textos->id('TITULO_FORMULARIO'), 'tituloFormularioRegistro mitadMargenSuperior');
        $codigo .= HTML::parrafo($textos->id('SUBTITULO_FORMULARIO'), 'subtituloFormularioRegistro');
        $codigo .= HTML::parrafo($textos->id('SUBTITULO2'), 'negrilla margenIzquierdaDoble titulo margenSuperior');
        $codigo  = HTML::contenedor($codigo, 'contenedorTitulosRegistro margenInferior');
        $contenedor1 .= HTML::parrafo($textos->id('NOMBRES'), 'negrita margenSuperior');
        $contenedor1 .= HTML::parrafo($objeto->nombres, 'grande');
        $contenedor2 .= HTML::parrafo($textos->id('APELLIDOS'), 'negrita margenSuperior');
        $contenedor2 .= HTML::parrafo($objeto->apellidos, 'grande');
        $contenedor1 .= HTML::parrafo($textos->id('INSTITUCION'), 'negrita margenSuperior');
        $contenedor1 .= HTML::parrafo($objeto->institucion, 'grande');  
        $contenedor2 .= HTML::parrafo($textos->id('CARGO'), 'negrita margenSuperior');
        $contenedor2 .= HTML::parrafo($objeto->cargo, 'grande'); 
        $contenedor1 .= HTML::parrafo($textos->id('CIUDAD'), 'negrita margenSuperior');
        $contenedor1 .= HTML::parrafo($objeto->pais.', '.$objeto->ciudad, 'grande');
        $contenedor2 .= HTML::parrafo($textos->id('EMAIL'), 'negrita margenSuperior');
        $contenedor2 .= HTML::parrafo($objeto->email, 'grande');
        $contenedor1 .= HTML::parrafo($textos->id('CODIGO_POSTAL'), 'negrita margenSuperior');
        $contenedor1 .= HTML::parrafo($objeto->codigoPostal, 'grande'); 
        $contenedor2 .= HTML::parrafo($textos->id('DIRECCION_CORREO'), 'negrita margenSuperior');
        $contenedor2 .= HTML::parrafo($objeto->direccionCorreo, 'grande');
        $contenedor1 .= HTML::parrafo($textos->id('TELEFONO'), 'negrita margenSuperior');
        $contenedor1 .= HTML::parrafo($objeto->telefono, 'grande'); 
        $contenedor2 .= HTML::parrafo($textos->id('FAX'), 'negrita margenSuperior');
        $contenedor2 .= HTML::parrafo($objeto->fax, 'grande'); 
        $contenedor1 .= HTML::parrafo($textos->id('INFO_PAGO'), 'negrita margenSuperior');
        $contenedor1 .= HTML::parrafo($textos->id('INFO_EVENTO_'.$objeto->evento.''), 'grande');         
        $contenedor1 .= HTML::parrafo($textos->id('NOMBRE_CERTIFICADO'), 'negrita margenSuperior');
        $contenedor1 .= HTML::parrafo($objeto->nombreCertificado, 'grande');
        $contenedor2 .= HTML::parrafo($textos->id('TOTAL'), 'negrita margenSuperior');
        $contenedor2 .= HTML::parrafo($textos->id('VALOR_EVENTO_'.$objeto->evento.''), 'grande');
        $contenedor2 .= HTML::parrafo($textos->id('PAGADO'), 'negrita margenSuperior checkRegistraPago');
        $contenedor2 .= HTML::campoChequeo('pagado', $objeto->pagado, 'checkRegistraPago', 'checkRegistraPago', array('identificador' => $id));
        
        $chequeado1 = '';
        $chequeado2 = '';
        $checked = 'chequeado'.$objeto->rol;
        $$checked = true;        
        
        $contenedor1 .= HTML::parrafo($textos->id('ROL'), 'negrita margenSuperior');
        $contenedor1 .= HTML::frase($textos->id('ROL_1'), 'mitadMargenDerecha').HTML::radioBoton('rol', $chequeado1, 'botonRegistraRol', '', array('identificador' => $id, 'rol' => '1'), 'botonRegistraRol').HTML::frase($textos->id('ROL_2'), 'margenIzquierdaDoble mitadMargenDerecha').HTML::radioBoton('rol', $chequeado2, 'botonRegistraRol', '', array('identificador' => $id, 'rol' => '2'), 'botonRegistraRol');                  
        
        $titulos = array('Librarian' => 'Librarian', 'Publisher Full Access' => 'Publisher Full Access', 'Leader' => 'Leader', 'Elt Presenter' => 'Elt Presenter', 'Academic Director Elt' => 'Academic Director Elt', 'Elt Attendees' => 'Elt Attendees', 'Publisher Exhibit Hall' => 'Publisher Exhibit Hall', 'Plenary Speaker' => 'Plenary Speaker', 'Cao' => 'Cao');
        
        $contenedor2 .= HTML::parrafo($textos->id('TITULO_CARNET'), 'negrita margenSuperior');
        $contenedor2 .= HTML::listaDesplegable('titulo_carnet', $titulos, $objeto->tituloCarnet, 'selectorTituloCarnet', 'selectorTituloCarnet', '', array('identificador' => $id));
        
        $contenedor1 = HTML::contenedor($contenedor1, 'contenedorRegistroIzquierda');
        $contenedor2 = HTML::contenedor($contenedor2, 'contenedorRegistroDerecha');
          
        $botonModificar    = HTML::parrafo(HTML::boton('chequeo', $textos->id('MODIFICAR_REGISTRO'), 'botonOk', 'botonOk', 'botonOk'), 'margenSuperior');
        $botonEliminar     = HTML::parrafo(HTML::boton('chequeo', $textos->id('ELIMINAR_ITEM'), 'botonOk', 'botonOk', 'botonOk'), 'margenSuperior');
        $botonCertificar   = HTML::parrafo(HTML::boton('chequeo', $textos->id('IMPRIMIR_CERTIFICADO'), 'botonOk directo', 'botonOk', 'botonOk'), 'margenSuperior');
        $botonCarnetizar   = HTML::parrafo(HTML::boton('chequeo', $textos->id('IMPRIMIR_CARNET'), 'botonOk directo', 'botonOk', 'botonOk'), 'margenSuperior');
        $botonImpRegistro  = HTML::parrafo(HTML::boton('chequeo', $textos->id('IMPRIMIR_FORMA_REGISTRO'), 'botonOk directo', 'botonOk', 'botonOk'), 'margenSuperior');
        
        $contenedor3  = HTML::contenedor(HTML::forma($destinoMod, $campos.$botonModificar, 'P'), 'flotanteIzquierda margenIzquierda');
        $contenedor3 .= HTML::contenedor(HTML::forma($destinoDel, $campos.$botonEliminar, 'P'), 'flotanteIzquierda margenIzquierda');
        $contenedor3 .= HTML::contenedor(HTML::forma($destinoCer, $campos.$botonCertificar, 'P'), 'flotanteIzquierda margenIzquierda');
        $contenedor3 .= HTML::contenedor(HTML::forma($destinoCar, $campos.$botonCarnetizar, 'P'), 'flotanteIzquierda margenIzquierda');
        $contenedor3 .= HTML::contenedor(HTML::forma($destinoReg, $campos.$botonImpRegistro, 'P'), 'flotanteIzquierda margenIzquierda');
        
        $contenedor3 = HTML::contenedor($contenedor3, 'contenedorRegistroInferior margenSuperiorDoble');
        
        $codigo .= $contenedor1.$contenedor2.$contenedor3;
        
        $codigo  = HTML::contenedor($codigo, 'contenedorFormularioRegistro');

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('CONSULTAR_REGISTRO_ABLA'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['ancho']   = 810;
        $respuesta['alto']    = 590;

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @global type $configuracion
 * @global type $archivo_imagen
 * @global type $sesion_usuarioSesion
 * @param type $datos 
 */
function adicionarRegistro($datos = array()) {
    global $textos, $sql;

    $objeto    = new Registro();
    $destino   = '/ajax'.$objeto->urlBase.'/add';
    $respuesta = array();

    if (empty($datos)) {
        
        $pais = array();
        $paises   = $sql->seleccionar(array('paises'), array('id', 'nombre'), 'id !=0', 'id', 'nombre ASC');
        while ($tipo = $sql->filaEnObjeto($paises)) {
            $pais[$tipo->nombre] = $tipo->nombre;
        }        
        $listaPaises = HTML::listaDesplegable('datos[pais]', $pais, '', '', '', 'Select Country...');
        
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::parrafo($textos->id('TITULO_FORMULARIO'), 'tituloFormularioRegistro mitadMargenSuperior');
        $codigo .= HTML::parrafo($textos->id('SUBTITULO_FORMULARIO'), 'subtituloFormularioRegistro');
        $codigo .= HTML::parrafo($textos->id('SUBTITULO2'), 'negrilla margenIzquierdaDoble titulo margenSuperior');
        $codigo  = HTML::contenedor($codigo, 'contenedorTitulosRegistro');
        $contenedor1  = HTML::parrafo($textos->id('NOMBRES'), 'negrilla margenSuperior');
        $contenedor1 .= HTML::campoTexto('datos[nombres]', 40, 255);
        $contenedor2  = HTML::parrafo($textos->id('APELLIDOS'), 'negrilla margenSuperior');
        $contenedor2 .= HTML::campoTexto('datos[apellidos]', 40, 255);
        $contenedor1 .= HTML::parrafo($textos->id('INSTITUCION'), 'negrilla margenSuperior');
        $contenedor1 .= HTML::campoTexto('datos[institucion]', 40, 255, '', 'autocompletable', '', array('title' => HTML::urlInterna('INICIO',0,true,'listCenters')), $textos->id('ERROR_FALTA_INSTITUCION'));  
        $contenedor2 .= HTML::parrafo($textos->id('CARGO'), 'negrilla margenSuperior');
        $contenedor2 .= HTML::campoTexto('datos[cargo]', 40, 255, '', ''); 
        $contenedor1 .= HTML::parrafo($textos->id('PAIS'), 'negrilla margenSuperior');
        $contenedor1 .= $listaPaises;
        $contenedor2 .= HTML::parrafo($textos->id('CIUDAD'), 'negrilla margenSuperior');
        $contenedor2 .= HTML::campoTexto('datos[ciudad]', 40, 255, '', 'autocompletable', '', array('title' => HTML::urlInterna('INICIO',0,true,'listJustCities')), $textos->id('SELECCIONE_CIUDAD_RESIDENCIA'));
        $contenedor1 .= HTML::parrafo($textos->id('CODIGO_POSTAL'), 'negrilla margenSuperior');
        $contenedor1 .= HTML::campoTexto('datos[codigo_postal]', 40, 255, '', ''); 
        $contenedor2 .= HTML::parrafo($textos->id('DIRECCION_CORREO'), 'negrilla margenSuperior');
        $contenedor2 .= HTML::campoTexto('datos[direccion_correo]', 40, 255, '', '');
        $contenedor2 .= HTML::parrafo($textos->id('EMAIL'), 'negrilla margenSuperior');
        $contenedor2 .= HTML::campoTexto('datos[email]', 40, 255, '', '');
        $contenedor1 .= HTML::parrafo($textos->id('TELEFONO'), 'negrilla margenSuperior');
        $contenedor1 .= HTML::campoTexto('datos[telefono]', 40, 255, '', ''); 
        $contenedor2 .= HTML::parrafo($textos->id('FAX'), 'negrilla margenSuperior');
        $contenedor2 .= HTML::campoTexto('datos[fax]', 40, 255, '', ''); 
        $contenedor1 .= HTML::parrafo($textos->id('NOMBRE_CERTIFICADO'), 'negrilla margenSuperior');
        $contenedor1 .= HTML::campoTexto('datos[nombre_certificado]', 40, 255, '', ''); 
        
        $clase  = 'radioBotonEvento';
        $nombre = 'datos[evento]';
        
        $contenedor3  = HTML::parrafo(HTML::radioBoton($nombre, $chequeado, $clase, '1').HTML::frase($textos->id('INFO_EVENTO_1'), 'grande').HTML::frase($textos->id('VALOR_EVENTO_1'), 'grande alineadoDerecha margenDerechaDoble negrilla'), 'margenSuperiorDoble bordeInferior checkBoxRegistro'); 
        $contenedor3 .= HTML::parrafo(HTML::radioBoton($nombre, $chequeado, $clase, '2').HTML::frase($textos->id('INFO_EVENTO_2'), 'grande').HTML::frase($textos->id('VALOR_EVENTO_2'), 'grande alineadoDerecha margenDerechaDoble negrilla'), 'margenSuperior bordeInferior checkBoxRegistro'); 
        $contenedor3 .= HTML::parrafo(HTML::radioBoton($nombre, $chequeado, $clase, '3').HTML::frase($textos->id('INFO_EVENTO_3'), 'grande').HTML::frase($textos->id('VALOR_EVENTO_3'), 'grande alineadoDerecha margenDerechaDoble negrilla'), 'margenSuperior bordeInferior checkBoxRegistro'); 
        $contenedor3 .= HTML::parrafo(HTML::radioBoton($nombre, $chequeado, $clase, '4').HTML::frase($textos->id('INFO_EVENTO_4'), 'grande').HTML::frase($textos->id('VALOR_EVENTO_4'), 'grande alineadoDerecha margenDerechaDoble negrilla'), 'margenSuperior bordeInferior checkBoxRegistro'); 
        $contenedor3 .= HTML::parrafo(HTML::radioBoton($nombre, $chequeado, $clase, '5').HTML::frase($textos->id('INFO_EVENTO_5'), 'grande').HTML::frase($textos->id('VALOR_EVENTO_5'), 'grande alineadoDerecha margenDerechaDoble negrilla'), 'margenSuperior bordeInferior checkBoxRegistro'); 
        
        
        $contenedor3 .= HTML::parrafo(HTML::boton('chequeo', $textos->id('REGISTRARSE'), '', '', ''), 'margenSuperior');
        $contenedor3 .= HTML::frase($textos->id('REGISTRO_AGREGADO'), 'textoExitoso', 'textoExitoso');
 
        $contenedor1 = HTML::contenedor($contenedor1, 'contenedorRegistroIzquierda');
        $contenedor2 = HTML::contenedor($contenedor2, 'contenedorRegistroDerecha');
        $contenedor3 = HTML::contenedor($contenedor3, 'contenedorRegistroInferior margenSuperiorDoble');
        $codigo .= $contenedor1.$contenedor2.$contenedor3;
        $codigo  = HTML::forma($destino, $codigo, 'P', true);
        
        $codigo  = HTML::contenedor($codigo, 'contenedorFormularioRegistro');

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('FORMULARIO_DE_REGISTRO_ABLA'), 'letraNegra negrilla'), 'bloqueTitulo'), 'encabezadoBloque');
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['ancho']   = 810;
        $respuesta['alto']    = 630;

    } else {
        $respuesta['error']   = true;
      
        if (empty($datos['nombres'])) {
        $respuesta['mensaje'] = $textos->id('ERROR_FALTA_NOMBRES');

        } elseif (empty($datos['apellidos'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_APELLIDOS');

        } elseif (empty($datos['institucion'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_INSTITUCION');

        } elseif (empty($datos['cargo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CARGO');

        } elseif (empty($datos['pais']) || $datos['pais'] == 'Select Country...') {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_PAIS');

        } elseif (empty($datos['ciudad'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CIUDAD');

        } elseif (empty($datos['email'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_EMAIL');

        } elseif (isset($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $respuesta['mensaje'] = $textos->id('ERROR_SINTAXIS_CORREO');

        } elseif (empty($datos['direccion_correo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_DIRECCION_CORREO');

        } elseif (empty($datos['nombre_certificado'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_NOMBRE_CERTIFICADO');

        } elseif (empty($datos['evento'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_EVENTO');

        }else {
             
                $idRegistro = $objeto->adicionar($datos);
                if ($idRegistro) {                    
                  /******* Armo la nueva objeto ya modificada y la devuelvo via Ajax *******/
                        $idRegistro = explode('|', $idRegistro);//como recibo el id del registro y la ruta del pdf, la separo
                        $objeto  = new Registro($idRegistro[0]);               
                         
                        $celdas    = array($objeto->nombres, $objeto->apellidos, $objeto->institucion, $objeto->fechaRegistro); 
                        $claseFila = '';
                        $idFila    = $idRegistro[0];
                        $celdas    = HTML::crearNuevaFila($celdas, $claseFila, $idFila);

                        $respuesta['error']                 = false;
                        $respuesta['accion']                = 'insertar';
                        $respuesta['contenido']             = $celdas;
                        $respuesta['idContenedor']          = '#tr_'.$idRegistro[0];
                        $respuesta['insertarNuevaFila']     = true;
                        $respuesta['idDestino']             = '#tablaRegistros';
                        $respuesta['mensaje']               = str_replace('%1', $idRegistro[1], $textos->id('REPORTE_PDF_GENERADO_EXITOSAMENTE') );
                        $respuesta['mensajeExito']          = true;

                } else {
                    $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
                }
 
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $datos 
 */
function modificarRegistro($id, $datos = array()) {
    global $textos, $sql;

    if(!is_numeric($id) || !$sql->existeItem('registro', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $objeto    = new Registro($id);
    $destino   = '/ajax'.$objeto->urlBase.'/edit';
    $respuesta = array();

    if (empty($datos)) {
        
        $pais = array();
        $paises   = $sql->seleccionar(array('paises'), array('id', 'nombre'), 'id !=0', 'id', 'nombre ASC');
        while ($tipo = $sql->filaEnObjeto($paises)) {
            $pais[$tipo->nombre] = $tipo->nombre;
        }
        
        $listaPaises = HTML::listaDesplegable('datos[pais]', $pais, $objeto->pais, '', '', 'Select Country...');
        
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($textos->id('TITULO_FORMULARIO'), 'tituloFormularioRegistro mitadMargenSuperior');
        $codigo .= HTML::parrafo($textos->id('SUBTITULO_FORMULARIO'), 'subtituloFormularioRegistro');
        $codigo .= HTML::parrafo($textos->id('SUBTITULO2'), 'negrilla margenIzquierdaDoble titulo margenSuperior');
        $codigo  = HTML::contenedor($codigo, 'contenedorTitulosRegistro');
        $contenedor1  = HTML::parrafo($textos->id('NOMBRES'), 'negrilla margenSuperior');
        $contenedor1 .= HTML::campoTexto('datos[nombres]', 40, 255, $objeto->nombres);
        $contenedor2  = HTML::parrafo($textos->id('APELLIDOS'), 'negrilla margenSuperior');
        $contenedor2 .= HTML::campoTexto('datos[apellidos]', 40, 255, $objeto->apellidos);
        $contenedor1 .= HTML::parrafo($textos->id('INSTITUCION'), 'negrilla margenSuperior');
        $contenedor1 .= HTML::campoTexto('datos[institucion]', 40, 255,  $objeto->institucion, 'autocompletable', '', array('title' => HTML::urlInterna('INICIO',0,true,'listCenters')), $textos->id('ERROR_FALTA_INSTITUCION'));  
        $contenedor2 .= HTML::parrafo($textos->id('CARGO'), 'negrilla margenSuperior');
        $contenedor2 .= HTML::campoTexto('datos[cargo]', 40, 255 , $objeto->cargo, ''); 
        $contenedor1 .= HTML::parrafo($textos->id('PAIS'), 'negrilla margenSuperior');
        $contenedor1 .= $listaPaises;
        $contenedor2 .= HTML::parrafo($textos->id('CIUDAD'), 'negrilla margenSuperior');
        $contenedor2 .= HTML::campoTexto('datos[ciudad]', 40, 255, $objeto->ciudad, 'autocompletable', '', array('title' => HTML::urlInterna('INICIO',0,true,'listJustCities')), $textos->id('SELECCIONE_CIUDAD_RESIDENCIA'));
        $contenedor1 .= HTML::parrafo($textos->id('CODIGO_POSTAL'), 'negrilla margenSuperior');
        $contenedor1 .= HTML::campoTexto('datos[codigo_postal]', 40, 255, $objeto->codigoPostal, ''); 
        $contenedor2 .= HTML::parrafo($textos->id('DIRECCION_CORREO'), 'negrilla margenSuperior');
        $contenedor2 .= HTML::campoTexto('datos[direccion_correo]', 40, 255, $objeto->direccionCorreo, '');
        $contenedor2 .= HTML::parrafo($textos->id('EMAIL'), 'negrilla margenSuperior');
        $contenedor2 .= HTML::campoTexto('datos[email]', 40, 255, $objeto->email, '');
        $contenedor1 .= HTML::parrafo($textos->id('TELEFONO'), 'negrilla margenSuperior');
        $contenedor1 .= HTML::campoTexto('datos[telefono]', 40, 255, $objeto->telefono, ''); 
        $contenedor2 .= HTML::parrafo($textos->id('FAX'), 'negrilla margenSuperior');
        $contenedor2 .= HTML::campoTexto('datos[fax]', 40, 255, $objeto->fax, ''); 
        $contenedor1 .= HTML::parrafo($textos->id('NOMBRE_CERTIFICADO'), 'negrilla margenSuperior');
        $contenedor1 .= HTML::campoTexto('datos[nombre_certificado]', 40, 255, $objeto->nombreCertificado, ''); 
        
        $clase  = 'radioBotonEvento';
        $nombre = 'datos[evento]';
        
        $checked = 'chequeado'.$objeto->evento;
        $$checked = true;

        $contenedor3  = HTML::parrafo(HTML::radioBoton($nombre, $chequeado1, $clase, '1').HTML::frase($textos->id('INFO_EVENTO_1'), 'grande').HTML::frase($textos->id('VALOR_EVENTO_1'), 'grande alineadoDerecha margenDerechaDoble negrilla'), 'margenSuperiorDoble bordeInferior checkBoxRegistro'); 
        $contenedor3 .= HTML::parrafo(HTML::radioBoton($nombre, $chequeado2, $clase, '2').HTML::frase($textos->id('INFO_EVENTO_2'), 'grande').HTML::frase($textos->id('VALOR_EVENTO_2'), 'grande alineadoDerecha margenDerechaDoble negrilla'), 'margenSuperior bordeInferior checkBoxRegistro'); 
        $contenedor3 .= HTML::parrafo(HTML::radioBoton($nombre, $chequeado3, $clase, '3').HTML::frase($textos->id('INFO_EVENTO_3'), 'grande').HTML::frase($textos->id('VALOR_EVENTO_3'), 'grande alineadoDerecha margenDerechaDoble negrilla'), 'margenSuperior bordeInferior checkBoxRegistro'); 
        $contenedor3 .= HTML::parrafo(HTML::radioBoton($nombre, $chequeado4, $clase, '4').HTML::frase($textos->id('INFO_EVENTO_4'), 'grande').HTML::frase($textos->id('VALOR_EVENTO_4'), 'grande alineadoDerecha margenDerechaDoble negrilla'), 'margenSuperior bordeInferior checkBoxRegistro'); 
	$contenedor3 .= HTML::parrafo(HTML::radioBoton($nombre, $chequeado4, $clase, '5').HTML::frase($textos->id('INFO_EVENTO_5'), 'grande').HTML::frase($textos->id('VALOR_EVENTO_5'), 'grande alineadoDerecha margenDerechaDoble negrilla'), 'margenSuperior bordeInferior checkBoxRegistro'); 
        
        $contenedor3 .= HTML::parrafo(HTML::boton('chequeo', $textos->id('MODIFICAR_REGISTRO'), 'botonOk', 'botonOk', 'botonOk'), 'margenSuperior');
        $contenedor3 .= HTML::frase($textos->id('REGISTRO_AGREGADO'), 'textoExitoso', 'textoExitoso');

        $contenedor1 = HTML::contenedor($contenedor1, 'contenedorRegistroIzquierda');
        $contenedor2 = HTML::contenedor($contenedor2, 'contenedorRegistroDerecha');
        $contenedor3 = HTML::contenedor($contenedor3, 'contenedorRegistroInferior margenSuperiorDoble');
        $codigo .= $contenedor1.$contenedor2.$contenedor3;
        $codigo  = HTML::forma($destino, $codigo, 'P', true);
        
        $codigo  = HTML::contenedor($codigo, 'contenedorFormularioRegistro');

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('MODIFICAR_REGISTRO_ABLA'), 'letraNegra negrilla'), 'bloqueTitulo'), 'encabezadoBloque');
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['ancho']   = 810;
        $respuesta['alto']    = 630;

    } else {
        $respuesta['error']   = true;
      
        if (empty($datos['nombres'])) {
        $respuesta['mensaje'] = $textos->id('ERROR_FALTA_NOMBRES');

        } elseif (empty($datos['apellidos'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_APELLIDOS');

        } elseif (empty($datos['institucion'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_INSTITUCION');

        } elseif (empty($datos['cargo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CARGO');

        } elseif (empty($datos['pais']) || $datos['pais'] == 'Select Country...') {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_PAIS');

        } elseif (empty($datos['ciudad'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CIUDAD');

        } elseif (empty($datos['email'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_EMAIL');

        } elseif (isset($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $respuesta['mensaje'] = $textos->id('ERROR_SINTAXIS_CORREO');

        } elseif (empty($datos['direccion_correo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_DIRECCION_CORREO');

        } elseif (empty($datos['nombre_certificado'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_NOMBRE_CERTIFICADO');

        } elseif (empty($datos['evento'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_EVENTO');

        }else {
             
       $idRegistro = $objeto->modificar($datos);
                if ($idRegistro) {                    
                  /******* Armo la nueva objeto ya modificada y la devuelvo via Ajax *******/
                        $objeto  = new Registro($idRegistro);           
                        $celdas    = array($objeto->nombres, $objeto->apellidos, $objeto->institucion, $objeto->fechaRegistro);                        
                        $celdas    = HTML::crearFilaAModificar($celdas);
 
                        $respuesta['error']                = false;
                        $respuesta['accion']               = 'insertar';
                        $respuesta['contenido']            = $celdas;
                        $respuesta['idContenedor']         = '#tr_'.$id;
                        $respuesta['modificarFilaTabla']   = true;
                        $respuesta['idDestino']            = '#tr_'.$id;

                } else {
                    $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
                }            

        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @param type $id
 * @param type $confirmado 
 */
function eliminarRegistro($id, $confirmado) {
    global $textos, $sql;

    if(!is_numeric($id) || !$sql->existeItem('registro', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $objeto    = new Registro($id);
    $destino   = '/ajax'.$objeto->urlBase.'/delete';
    $respuesta = array();

    if (!$confirmado) {
        $titulo  = HTML::frase($objeto->nombres.' '.$objeto->apellidos, 'negrilla');
        $titulo  = str_replace('%1', $titulo, $textos->id('CONFIRMAR_ELIMINACION'));
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), '', 'botonOk', 'botonOk'), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id('REGISTRO_ELIMINADO'), 'textoExitoso', 'textoExitoso');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ELIMINAR_ITEM'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 400;
        $respuesta['alto']    = 170;
    } else {

        if ($objeto->eliminar()) {
               $respuesta['error']              = false;
               $respuesta['accion']             = 'insertar';
               $respuesta['idDestino']          = '#tr_'.$id;
               $respuesta['eliminarFilaTabla']  = true;
            

        } else {
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * @global type $textos
 * @global type $sql
 * @param type $datos 
 */
function buscarItem($data) {
    global $textos, $configuracion;
     
    $data = explode('[', $data);    
    $datos = $data[0];
    
     if(empty($datos)) { 
         $respuesta['error']   = true;
         $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CADENA_BUSQUEDA');
            
     }else if(!empty($datos) && strlen($datos) < 2){
         $respuesta['error']   = true;
         $respuesta['mensaje'] = str_replace('%1', '2', $textos->id('ERROR_TAMA�O_CADENA_BUSQUEDA'));
         
         
     } else {
            $item       = '';
            $respuesta  = array();
            $objeto = new Registro();
            $registros = $configuracion['GENERAL']['registrosPorPaginaTabla'];
            $pagina = 1;
            $registroInicial = 0;
           
            
            $palabras = explode(' ', htmlspecialchars($datos));
            
            $condicionales = $data[1];
            
            if($condicionales == ""){
                $condicion = '(r.nombres REGEXP "('.implode('|', $palabras).')")';
                
            }else{
                //$condicion = str_replace("]", "'", $data[1]);
                $condicionales = explode("|", $condicionales);
                
                $condicion = '(';
                $tam = sizeof($condicionales) - 1;
                for($i = 0; $i < $tam; $i++){
                    $condicion .=  $condicionales[$i].' REGEXP "('.implode('|', $palabras).')" ';
                    if($i != $tam -1){
                        $condicion .= ' OR ';
                    }
                }
                $condicion .= ')';            
                
            }

            $arregloItems = $objeto->listar($registroInicial, $registros, array('0'), $condicion, 'r.nombres');

            if ($objeto->registrosConsulta) {//si la consulta trajo registros
                $datosPaginacion = array($objeto->registrosConsulta, $registroInicial, $registros, $pagina, $objeto->registrosConsulta);         
                $item  .= $objeto->generarTabla($arregloItems, $datosPaginacion);    
                $info   = HTML::parrafo('You got '.$objeto->registrosConsulta.' results', 'textoExitosoNotificaciones');

            }else{
                $datosPaginacion = 0;        
                $item  .= $objeto->generarTabla($textos->id('NO_HAY_REGISTROS'), $datosPaginacion);
                $info   = HTML::parrafo('Your search didn\'t bring results', 'textoErrorNotificaciones');
            }

            $respuesta['error']                = false;
            $respuesta['accion']               = 'insertar';
            $respuesta['contenido']            = $item;
            $respuesta['idContenedor']         = '#tablaRegistros';
            $respuesta['idDestino']            = '#contenedorTablaRegistros';
            $respuesta['paginarTabla']         = true;
            $respuesta['info']                 = $info; 

        } 

    Servidor::enviarJSON($respuesta);
}

/**
 * @global type $configuracion
 * @param type $pagina
 * @param type $orden
 * @param type $nombreOrden
 * @param type $consultaGlobal 
 */
function paginador($pagina, $orden = NULL, $nombreOrden = NULL, $consultaGlobal = NULL){
    global $configuracion;
    
    $item = '';
    $respuesta = array();
    $objeto = new Registro();
    
    $registros = $configuracion['GENERAL']['registrosPorPaginaTabla'];
    
    if (isset($pagina)) {
        $pagina = $pagina;
    } else {
        $pagina = 1;
    }

    if(isset($consultaGlobal) && $consultaGlobal != ''){

         $data = explode('[', $consultaGlobal);
         $datos = $data[0];
         $palabras = explode(' ', htmlspecialchars($datos));

         if($data[1] != ''){    
            $condicionales = explode('|',  $data[1]);
                
                $condicion = '(';
                $tam = sizeof($condicionales) - 1;
                for($i = 0; $i < $tam; $i++){
                    $condicion .=  $condicionales[$i].' REGEXP "('.implode('|', $palabras).')" ';
                    if($i != $tam -1){
                        $condicion .= ' OR ';
                    }
                }
                $condicion .= ')';     

             $consultaGlobal = $condicion; 
           }else{
             $consultaGlobal = '(r.nombres REGEXP "('.implode('|', $palabras).')")';
           } 
  
    }else{
      $consultaGlobal = '';
    }
    
    if(empty($nombreOrden)){
        $nombreOrden = $objeto->ordenInicial;
    }    


    
    if(isset($orden) && $orden == 'ascendente'){//ordenamiento
        $objeto->listaAscendente = true;
    }else{
        $objeto->listaAscendente = false;
    }
    
    if(isset($nombreOrden) && $nombreOrden == 'estado'){//ordenamiento
        $nombreOrden = 'activo';
    }
    
    $registroInicial = ($pagina - 1) * $registros;
         
    $arregloItems = $objeto->listar($registroInicial, $registros, array('0'), $consultaGlobal, $nombreOrden);
    
    if ($objeto->registrosConsulta) {//si la consulta trajo registros
        $datosPaginacion = array($objeto->registrosConsulta, $registroInicial, $registros, $pagina);        
        $item  .= $objeto->generarTabla($arregloItems, $datosPaginacion);    

    }
    
    $respuesta['error']                = false;
    $respuesta['accion']               = 'insertar';
    $respuesta['contenido']            = $item;
    $respuesta['idContenedor']         = '#tablaRegistros';
    $respuesta['idDestino']            = '#contenedorTablaRegistros';
    $respuesta['paginarTabla']         = true;   
    
    Servidor::enviarJSON($respuesta);    
    
}

/**
 * @global type $textos
 * @global type $configuracion 
 */
function imprimirExcel(){  
    global $textos, $configuracion;
    require_once 'Xls.php';
    
    $objeto     = new Registro();
    $archivo    = new XLS();
    $respuesta  = array();
    
    $nombreArchivo = '/reporte-'.date('Y-m-d H:i:s').'.xls';
    
    $rutaArchivo   = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['archivos'].$nombreArchivo;
    //Recursos::escribirTxt($nombreArchivo);    
    $fila    = 0;
    $columna = 0;
    
    $objeto->listaAscendente = true;
    $datos = $objeto->listar(0, 0, '', '', 'r.pais');  
    
    $archivo->escribirTexto($fila, $columna,   $textos->id(''));
    $fila = 1;
    $archivo->escribirTexto($fila, $columna,   $textos->id('REGISTRATION REPORT OF ').date('Y-m-d'));
    $archivo->escribirTexto($fila, $columna,   $textos->id(''));
    $archivo->escribirTexto($fila, $columna,   $textos->id(''));
    $fila = 4;
    $archivo->escribirTexto($fila, $columna++, $textos->id('NUMBER'));
    $archivo->escribirTexto($fila, $columna++, $textos->id('NAMES'));
    $archivo->escribirTexto($fila, $columna++, $textos->id('LAST NAMES'));  
    $archivo->escribirTexto($fila, $columna++, $textos->id('INSTITUTION'));  
    $archivo->escribirTexto($fila, $columna++, $textos->id('POSITION')); 
    $archivo->escribirTexto($fila, $columna++, $textos->id('COUNTRY')); 
    $archivo->escribirTexto($fila, $columna++, $textos->id('CITY ')); 
    $archivo->escribirTexto($fila, $columna++, $textos->id('POSTAL CODE')); 
    $archivo->escribirTexto($fila, $columna++, $textos->id('MAILING ADDRESS'));
    $archivo->escribirTexto($fila, $columna++, $textos->id('E-MAIL '));
    $archivo->escribirTexto($fila, $columna++, $textos->id('TELEPHONE')); 
    $archivo->escribirTexto($fila, $columna++, $textos->id('FAX   ')); 
    $archivo->escribirTexto($fila, $columna++, $textos->id('REGISTRATION DATE')); 
    $archivo->escribirTexto($fila, $columna++, $textos->id('CERTIFICATE NAME')); 
    $archivo->escribirTexto($fila, $columna++, $textos->id('EVENT')); 
    
    $id = 1;
    foreach ($datos as $elemento){
        $fila++;        
        $columna = 0;
        $archivo->escribirTexto($fila, $columna++, $id);
        $archivo->escribirTexto($fila, $columna++, $elemento->nombres);
        $archivo->escribirTexto($fila, $columna++, $elemento->apellidos);
        $archivo->escribirTexto($fila, $columna++, $elemento->institucion);  
        $archivo->escribirTexto($fila, $columna++, $elemento->cargo);  
        $archivo->escribirTexto($fila, $columna++, $elemento->pais); 
        $archivo->escribirTexto($fila, $columna++, $elemento->ciudad); 
        $archivo->escribirTexto($fila, $columna++, $elemento->codigoPostal); 
        $archivo->escribirTexto($fila, $columna++, $elemento->direccionCorreo);
        $archivo->escribirTexto($fila, $columna++, $elemento->email); 
        $archivo->escribirTexto($fila, $columna++, $elemento->telefono); 
        $archivo->escribirTexto($fila, $columna++, $elemento->fax); 
        $archivo->escribirTexto($fila, $columna++, date('D, d M Y h:i:s A', $elemento->fechaRegistro) ); 
        $archivo->escribirTexto($fila, $columna++, $elemento->nombreCertificado); 
        $archivo->escribirTexto($fila, $columna++, $textos->id('INFO_EVENTO_'.$elemento->evento) );
        $id++;
    }
          
   $archivo->enviar($rutaArchivo); 
   
   if(file_exists($rutaArchivo)){
       chmod($rutaArchivo, 0777); 
   }
   
    $respuesta['error']         = true;
    $respuesta['mensaje']       = $textos->id('REPORTE_GENERADO_EXITOSAMENTE');
    $respuesta['errorExito']    = true;
    $respuesta['imprimirExcel'] = true;
    $respuesta['archivoExcel']  = $configuracion['SERVIDOR']['media'].'/'.$configuracion['RUTAS']['archivos'].$nombreArchivo;
 
    
    Servidor::enviarJSON($respuesta); 
}

/**
 *
 * @global type $configuracion
 * @global type $textos
 * @param type $id 
 */
function imprimirCertificado($id){   
global $configuracion, $textos, $sql;    
   
    if(!is_numeric($id) || !$sql->existeItem('registro', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $objeto = new Registro($id);

    $rutaLogoAbla = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['imagenesEstaticas'].'/abla2012.jpg';
    $rutaLogoDomi = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['imagenesEstaticas'].'/logoDominico.jpg';
    $rutaLogoAbl4 = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['imagenesEstaticas'].'/logo.jpg';

    $pdf = new FPDF('L');
    $pdf->AddPage();

    /*$pdf->Image($rutaLogoAbla, 10, 5, 37, 29, 'jpg');
    $pdf->Image($rutaLogoDomi, 255, 5, 37, 29, 'jpg');
    $pdf->Image($rutaLogoAbl4, 20, 35, 250, 170, 'jpg');*/
    $pdf->Ln(17);

    $pdf->SetFont('times','B',23);
    $pdf->Cell(290,10,'12th ABLA CONVENTION 2012', 0, 0, 'C');
    $pdf->Ln(8);
    $pdf->SetFont('times','',11);
    $pdf->Cell(290,10, 'Santo Domingo, DN; Dominican Republic', 0, 0, 'C');

    $pdf->Ln(8);
    $pdf->SetFont('times','',9);
    $pdf->Cell(97,9, 'Leaders Convention', 0, 0, 'C');
    $pdf->SetFont('times','',9);
    $pdf->Cell(97,9, 'Librarians Convention', 0, 0, 'C');
    $pdf->SetFont('times','',9);
    $pdf->Cell(97,9, 'ELT Convention', 0, 0, 'C'); 

    $pdf->Ln(8);
    $pdf->SetFont('times','B',12);
    $pdf->Cell(290,10, 'International Certificate', 0, 0, 'C');   

    $pdf->Ln(12);
    $pdf->Cell(275, 7, '', 'T', 0, 'L');

    $pdf->Ln(7);
    $pdf->SetFont('times','',12);
    $pdf->Cell(290,9, 'The Instituto Cultural Dominico Americano and The Association of Binational Centers of Latin America', 0, 0, 'C'); 
    $pdf->Ln(8);
    $pdf->SetFont('times','',12);
    $pdf->Cell(290,9, 'Certify that:', 0, 0, 'C'); 
    
    $pdf->Ln(15);
    $pdf->SetFont('times','IU',30);
    $pdf->Cell(290,9, mb_convert_case($objeto->nombreCertificado, MB_CASE_TITLE, "iso-8859-1"), 0, 0, 'C');
    
    $pdf->Ln(15);
    $pdf->SetFont('times','',12);
    $pdf->Cell(25,  9, "", 0, 0, 'R');//espacio
    $pdf->Cell(70,  9, 'Was a '.$textos->id('ROL_'.$objeto->rol).' at', 0, 0, 'R');
    $pdf->Cell(2,  9, '', 0, 0, 'R');//espacio
    $pdf->SetFont('times','U',12);
    $pdf->Cell(190, 9, $textos->id('INFO_EVENTO_'.$objeto->evento), 0, 0, 'L'); 

    $pdf->Ln(35);

    $pdf->Cell(30, 7, '', '', 0, 'L'); 
    $pdf->Cell(50, 7, '', 'T', 0, 'L');
    
    $pdf->Cell(35, 7, '', '', 0, 'L'); 
    $pdf->Cell(50, 7, '', 'T', 0, 'L');
    
    $pdf->Cell(35, 7, '', '', 0, 'L'); 
    $pdf->Cell(50, 7, '', 'T', 0, 'L');    
       
    $pdf->Ln(4);
    
    $pdf->Cell(30, 7, '', '', 0, 'L'); 
    $pdf->SetFont('times','',12);
    $pdf->Cell(50, 7, 'General Director', 0, 0, 'C');
    
    $pdf->Cell(35, 7, '', '', 0, 'L'); 
    $pdf->SetFont('times','',12);
    $pdf->Cell(50, 7, 'President, Board of Directors', 0, 0, 'C');
    
    $pdf->Cell(35, 7, '', '', 0, 'L'); 
    $pdf->SetFont('times','',12);
    $pdf->Cell(50, 7, 'Academic Director', 0, 0, 'C');     
    
    $pdf->Ln(4);
    
    $pdf->Cell(30, 7, '', '', 0, 'L'); 
    $pdf->SetFont('times','',12);
    $pdf->Cell(50, 7, 'Instituto Cultural Dominico Americano', 0, 0, 'C');
    
    $pdf->Cell(35, 7, '', '', 0, 'L'); 
    $pdf->SetFont('times','',12);
    $pdf->Cell(50, 7, 'Instituto Cultural Dominico Americano', 0, 0, 'C');
    
    $pdf->Cell(35, 7, '', '', 0, 'L'); 
    $pdf->SetFont('times','',12);
    $pdf->Cell(50, 7, 'Instituto Cultural Dominico Americano', 0, 0, 'C');     

    $pdf->Ln(8);    
    $pdf->Output();

}

/**
 * @global type $configuracion
 * @param type $id 
 */
function imprimirEscarapela($id){
global $configuracion, $sql;    

    if(!is_numeric($id) || !$sql->existeItem('registro', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $objeto = new Registro($id);

    $rutaLogoAbla  = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['imagenesEstaticas'].'/abla2012.jpg';
    $rutaGloboAbla = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['imagenesEstaticas'].'/globo.jpg';
    
    $pdf = new FPDF('P', 'cm', array(10, 12));
    $pdf->AddPage();
    
    /*$pdf->Image($rutaLogoAbla, 6.5, 0.2, 2.8, 1.9, 'jpg');
    $pdf->Image($rutaGloboAbla, 3, 3, 5, 6.5, 'jpg');*/
    
    $pdf->Ln(2.2);
    $pdf->SetFont('times','B',20);
    $pdf->Cell(8,3, strtoupper(mb_convert_case($objeto->tituloCarnet, MB_CASE_TITLE, "iso-8859-1")), 0, 0, 'C');
    
    $pdf->Ln(2.2);
    $pdf->SetFont('times','B',14);
    $pdf->Cell(8,3, mb_convert_case($objeto->nombres." ".$objeto->apellidos, MB_CASE_TITLE, "iso-8859-1"), 0, 0, 'C');
    
    $pdf->Ln(1.5);
    $pdf->SetFont('times','B',14);
    $pdf->Cell(8,3, mb_convert_case($objeto->pais, MB_CASE_TITLE, "iso-8859-1"), 0, 0, 'C');    

    $pdf->Output();

}

/**
 *
 * @global type $sql
 * @param type $id 
 */
function registrarPago($id) {
    global $sql;
    
    $valor = $sql->obtenerValor('registro', 'pagado', 'id = "'.$id.'"');
    
    if($valor){
        $valor = '0';
    }else{
        $valor = '1';
    }
    
    $datos     = array('pagado' => $valor);
    $sql->modificar('registro', $datos, 'id = "'.$id.'"');

}

/**
 *
 * @global type $sql
 * @param type $id
 * @param type $rol 
 */
function definirRol($id, $rol) {
    global $sql;
        
    $datos     = array('rol' => $rol);
    $sql->modificar('registro', $datos, 'id = "'.$id.'"');
}

/**
 *
 * @global type $sql
 * @param type $id
 * @param type $titulo 
 */
function definirTitulo($id, $titulo) {
    global $sql;
        
    $datos     = array('titulo_carnet' => $titulo);
    $sql->modificar('registro', $datos, 'id = "'.$id.'"');
}

/**
 *
 * @global type $configuracion
 * @global type $textos
 * @param type $id 
 */
function imprimirCertificados(){   
global $configuracion, $textos;   
   
    $obj = new Registro();
    $obj->listaAscendente = true;
    $arreglo = $obj->listar(0, 0, '', 'pagado = "1"', 'r.nombres');

    $rutaLogoAbla = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['imagenesEstaticas'].'/abla2012.jpg';
    $rutaLogoDomi = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['imagenesEstaticas'].'/logoDominico.jpg';
    $rutaLogoAbl4 = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['imagenesEstaticas'].'/logo.jpg';
    
    if($obj->pagados){
        $pdf = new FPDF('L');
    }
    
    foreach($arreglo as $objeto){  
        
        $pdf->AddPage();
        
       /* $pdf->Image($rutaLogoAbla, 10, 5, 37, 29, 'jpg');
        $pdf->Image($rutaLogoDomi, 255, 5, 37, 29, 'jpg');
        $pdf->Image($rutaLogoAbl4, 20, 35, 250, 170, 'jpg'); */
        $pdf->Ln(17);

        $pdf->SetFont('times','B',23);
        $pdf->Cell(290,10,'12th ABLA CONVENTION 2012', 0, 0, 'C');
        $pdf->Ln(8);
        $pdf->SetFont('times','',11);
        $pdf->Cell(290,10, 'Santo Domingo, DN; Dominican Republic', 0, 0, 'C');

        $pdf->Ln(8);
        $pdf->SetFont('times','',9);
        $pdf->Cell(97,9, 'Leaders Convention', 0, 0, 'C');
        $pdf->SetFont('times','',9);
        $pdf->Cell(97,9, 'Librarians Convention', 0, 0, 'C');
        $pdf->SetFont('times','',9);
        $pdf->Cell(97,9, 'ELT Convention', 0, 0, 'C'); 

        $pdf->Ln(8);
        $pdf->SetFont('times','B',12);
        $pdf->Cell(290,10, 'International Certificate', 0, 0, 'C');   

        $pdf->Ln(12);
        $pdf->Cell(275, 7, '', 'T', 0, 'L');

        $pdf->Ln(7);
        $pdf->SetFont('times','',12);
        $pdf->Cell(290,9, 'The Instituto Cultural Dominico Americano and The Association of Binational Centers of Latin America', 0, 0, 'C'); 
        $pdf->Ln(8);
        $pdf->SetFont('times','',12);
        $pdf->Cell(290,9, 'Certify that:', 0, 0, 'C'); 

        $pdf->Ln(15);
        $pdf->SetFont('times','IU',30);
        $pdf->Cell(290,9, mb_convert_case($objeto->nombreCertificado, MB_CASE_TITLE, "iso-8859-1"), 0, 0, 'C');

        $pdf->Ln(15);
        $pdf->SetFont('times','',12);
        $pdf->Cell(25,  9, "", 0, 0, 'R');//espacio
        $pdf->Cell(70,  9, "Was a ".$textos->id("ROL_".$objeto->rol)." at", 0, 0, 'R');
        $pdf->Cell(2,  9, "", 0, 0, 'R');//espacio
        $pdf->SetFont('times','U',12);
        $pdf->Cell(190, 9, $textos->id("INFO_EVENTO_".$objeto->evento), 0, 0, 'L'); 

        $pdf->Ln(35);

        $pdf->Cell(30, 7, '', '', 0, 'L'); 
        $pdf->Cell(50, 7, '', 'T', 0, 'L');

        $pdf->Cell(35, 7, '', '', 0, 'L'); 
        $pdf->Cell(50, 7, '', 'T', 0, 'L');

        $pdf->Cell(35, 7, '', '', 0, 'L'); 
        $pdf->Cell(50, 7, '', 'T', 0, 'L');    


        $pdf->Ln(4);

        $pdf->Cell(30, 7, '', '', 0, 'L'); 
        $pdf->SetFont('times','',12);
        $pdf->Cell(50, 7, 'General Director', 0, 0, 'C');

        $pdf->Cell(35, 7, '', '', 0, 'L'); 
        $pdf->SetFont('times','',12);
        $pdf->Cell(50, 7, 'President, Board of Directors', 0, 0, 'C');

        $pdf->Cell(35, 7, '', '', 0, 'L'); 
        $pdf->SetFont('times','',12);
        $pdf->Cell(50, 7, 'Academic Director', 0, 0, 'C');     

        $pdf->Ln(4);

        $pdf->Cell(30, 7, '', '', 0, 'L'); 
        $pdf->SetFont('times','',12);
        $pdf->Cell(50, 7, 'Instituto Cultural Dominico Americano', 0, 0, 'C');

        $pdf->Cell(35, 7, '', '', 0, 'L'); 
        $pdf->SetFont('times','',12);
        $pdf->Cell(50, 7, 'Instituto Cultural Dominico Americano', 0, 0, 'C');

        $pdf->Cell(35, 7, '', '', 0, 'L'); 
        $pdf->SetFont('times','',12);
        $pdf->Cell(50, 7, 'Instituto Cultural Dominico Americano', 0, 0, 'C');     

        $pdf->Ln(8); 
    
    }
  
    $pdf->Output();

}

/**
 *
 * @global type $configuracion
 * @param type $id 
 */
function imprimirEscarapelas(){
global $configuracion;    

    $obj = new Registro();
    $obj->listaAscendente = true;
    $arreglo = $obj->listar(0, 0, '', 'pagado = "1"', 'r.nombres');

    $rutaLogoAbla  = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['imagenesEstaticas'].'/abla2012.jpg';
    $rutaGloboAbla = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['imagenesEstaticas'].'/globo.jpg';
    
    $pdf = new FPDF('P', 'cm', array(10, 12));
    
    if($obj->pagados){
         $pdf = new FPDF('P', 'cm', array(10, 12));
    }
    
    foreach($arreglo as $objeto){
    
        $pdf->AddPage();

       /* $pdf->Image($rutaLogoAbla, 6.5, 0.2, 2.8, 1.9, 'jpg');
        $pdf->Image($rutaGloboAbla, 3, 3, 5, 6.5, 'jpg');*/

        $pdf->Ln(2.2);
        $pdf->SetFont('times','B',20);
        $pdf->Cell(8,3, strtoupper(mb_convert_case($objeto->tituloCarnet, MB_CASE_TITLE, 'iso-8859-1')), 0, 0, 'C');

        $pdf->Ln(2.2);
        $pdf->SetFont('times','B',14);
        $pdf->Cell(8,3, mb_convert_case($objeto->nombres.' '.$objeto->apellidos, MB_CASE_TITLE, 'iso-8859-1'), 0, 0, 'C');

        $pdf->Ln(1.5);
        $pdf->SetFont('times','B',14);
        $pdf->Cell(8,3, mb_convert_case($objeto->pais, MB_CASE_TITLE, 'iso-8859-1'), 0, 0, 'C');    
  
    }
    
    $pdf->Output();
}

/**
 *
 * @global type $textos
 * @global type $configuracion
 * @param type $id 
 */
function imprimirRegistro($id){
    global  $textos, $configuracion, $sql;
    
    if(!is_numeric($id) || !$sql->existeItem('registro', 'id', $id)){
	$respuesta['error']   = true;
	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
	Servidor::enviarJSON($respuesta);
	return NULL;
    }

    $objeto = new Registro($id);   

        $rutaLogoAbla = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['imagenesEstaticas'].'/abla2012.jpg';
        $rutaLogoDomi = $configuracion['RUTAS']['media'].'/'.$configuracion['RUTAS']['imagenesEstaticas'].'/logoDominico.jpg';

        $pdf = new FPDF();
        $pdf->AddPage();

        $pdf->Image($rutaLogoAbla, 10, 10, 37, 29, 'jpg');
        $pdf->Image($rutaLogoDomi, 165, 10, 37, 29, 'jpg');
        $pdf->Ln(17);

        $pdf->SetFont('times','B',17);
        $pdf->Cell(190,10,'12th ABLA CONVENTION 2012', 0, 0, 'C');
        $pdf->Ln(8);
        $pdf->SetFont('times','',11);
        $pdf->Cell(190,10, 'Santo Domingo, DN; Dominican Republic', 0, 0, 'C');

        $pdf->Ln(8);
        $pdf->SetFont('times','',9);
        $pdf->Cell(63,9, 'Leaders Convention', 0, 0, 'C');
        $pdf->SetFont('times','',9);
        $pdf->Cell(63,9, 'Librarians Convention', 0, 0, 'C');
        $pdf->SetFont('times','',9);
        $pdf->Cell(63,9, 'ELT Convention', 0, 0, 'C'); 

        $pdf->Ln(8);
        $pdf->SetFont('times','B',12);
        $pdf->Cell(190,10, 'International Registration Form', 0, 0, 'C');   

        $pdf->Ln(12);
        $pdf->Cell(190, 7, '', 'T', 0, 'L');

        $pdf->Ln(4);
        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,9, 'First Name:', 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(60,9, $objeto->nombres, 0, 0, 'L');

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,9, 'Last Name:', 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(60,9, $objeto->apellidos, 0, 0, 'L');


        $pdf->Ln(8);

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,10, 'Institution:', 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(90,9, $objeto->institucion, 0, 0, 'L');

        $pdf->Ln(8);
        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,9, 'Position:', 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(60,9, $objeto->cargo, 0, 0, 'L');

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,9, 'Country:', 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(60,9, $objeto->pais, 0, 0, 'L');


        $pdf->Ln(8);

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,9, 'City:', 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(70,9, $objeto->ciudad, 0, 0, 'L');

        $pdf->Ln(8);

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,9, 'Postal Code:', 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(60,9, $objeto->codigoPostal, 0, 0, 'L');

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,9, 'Mailing address:', 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(60,9, $objeto->direccionCorreo, 0, 0, 'L');            

        $pdf->Ln(8);

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,9, 'Telephone:', 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(60,9, $objeto->telefono, 0, 0, 'L');

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,9, 'Fax:', 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(60,9, $objeto->fax, 0, 0, 'L');  

        $pdf->Ln(8);

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,9, 'E-mail:', 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(60,9, $objeto->email, 0, 0, 'L');

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,9, "Date:", 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(60,9, date("D, d M Y"), 0, 0, 'L');               


        $pdf->Ln(8);

        $pdf->SetFont('times','B',9);
        $pdf->Cell(90,9, 'How your name will appear on the certificate?', 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(60,9, $objeto->nombreCertificado, 0, 0, 'L');


        $pdf->Ln(8);

        $pdf->SetFont('times','B',12);
        $pdf->Cell(90,9, 'Payment information:', 0, 0, 'L'); 

        $pdf->Ln(8);

        $pdf->SetFont('times','',10);
        $pdf->Cell(90,9, $textos->id("INFO_EVENTO_".$objeto->evento.""), 0, 0, 'L'); 


        $pdf->Ln(8);

        $pdf->SetFont('times','B',12);
        $pdf->Cell(90,9, 'Total:', 0, 0, 'L'); 

        $pdf->Ln(8);

        $pdf->SetFont('times','',10);
        $pdf->Cell(90,9, $textos->id("VALOR_EVENTO_".$objeto->evento.""), 0, 0, 'L');  

        $pdf->Ln(8);

        $pdf->SetFont('times','B',12);
        $pdf->Cell(90,9, 'Payment method:', 0, 0, 'L'); 

        $pdf->Ln(10);

        $pdf->SetFont('times','',12);
        $pdf->Cell(5,5, '', 1, 0, 'L');      

        $pdf->SetFont('times','B',10);
        $pdf->Cell(50, 5, 'VISA', 0, 0, 'L'); 

        $pdf->SetFont('times','',12);
        $pdf->Cell(5,5, '', 1, 0, 'L');      

        $pdf->SetFont('times','B',10);
        $pdf->Cell(50, 5, 'MASTER CARD', 0, 0, 'L');    


        $pdf->Ln(8);

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,5, "Credit Card Number:", 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(50,5, '', 'B', 0, 'L');  

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,5, "Cardholder's name:", 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(50,5, '', 'B', 0, 'L'); 


        $pdf->Ln(8);

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,5, "Expiration date:", 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(50,5, '', 'B', 0, 'L');  

        $pdf->SetFont('times','B',9);
        $pdf->Cell(30,5, "Security code:", 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(50,5, '', 'B', 0, 'L'); 

        $pdf->Ln(3);

        $pdf->SetFont('times','',7);
        $pdf->Cell(150, 7, '(Last three digits on back of card)', 0, 0, 'R'); 

        $pdf->Ln(7);
        $pdf->SetFont('times','',10);
        $pdf->Cell(190,9, '* Early Registration:  If you register before June 30th, 2012, you will receive a US$20.00 discount.', 0, 0, 'L');

        $pdf->Ln(7);
        $pdf->SetFont('times','',10);
        $pdf->Cell(190,9, '* Regular Registration: Registrations submitted between July 1st and September 30th, 2012.', 0, 0, 'L');

        $pdf->Ln(7);
        $pdf->SetFont('times','',10);
        $pdf->Cell(190,9, '* Late Registration:  If your register after October 1st, you will be charged an additional fee of US $25.00.', 0, 0, 'L');  

        $pdf->Ln(7);
        $pdf->SetFont('times','',10);
        $pdf->Cell(190,9, '+ Additional 20% discount if three or more people from the same BNC register before June 30th, 2012.', 0, 0, 'L'); 


        $pdf->Ln(15);

        $pdf->SetFont('times','B',9);
        $pdf->Cell(15,5, "City:", 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(40,5, '', 'B', 0, 'L');  

        $pdf->SetFont('times','B',9);
        $pdf->Cell(15,5, "Signature:", 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(40,5, '', 'B', 0, 'L');

        $pdf->SetFont('times','B',9);
        $pdf->Cell(15,5, "Date:", 0, 0, 'L'); 

        $pdf->SetFont('times','',9);
        $pdf->Cell(40,5, '', 'B', 0, 'L');              

        $pdf->Ln(10);
        $pdf->SetFont('times','',10);
        $pdf->Cell(190,9, 'Fill up this form and send it to this Fax number 8092552600', 0, 0, 'C');


        $pdf->Ln(16);
        $pdf->SetFont('times','',10);
        $pdf->Cell(190,9, 'Av. Abraham Lincoln #21, Santo Domingo, Rep�blica Dominicana,', 0, 0, 'C');

        $pdf->Ln(4);
        $pdf->SetFont('times','',10);
        $pdf->Cell(190,9, 'Tel. 809-535-0665, ext. 2202, 2102', 0, 0, 'C');            


        $pdf->Output();   
    
}

?>