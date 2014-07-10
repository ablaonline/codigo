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
                       
        case 'see'          : verRegistro($forma_id);
                              break;

        case 'search'       : ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              buscarItem($forma_datos);
                              break;
        case 'move'         : paginador(htmlspecialchars($forma_pagina), htmlspecialchars($forma_orden), htmlspecialchars($forma_nombreOrden), htmlspecialchars($forma_consultaGlobal));
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

    if(!is_numeric($id) || !$sql->existeItem('bitacora', 'id', $id)){
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
         $respuesta['mensaje'] = str_replace('%1', '2', $textos->id('ERROR_TAMAÑO_CADENA_BUSQUEDA'));
         
         
     } else {
            $item       = '';
            $respuesta  = array();
            $objeto = new Bitacora();
            $registros = $configuracion['GENERAL']['registrosPorPaginaTabla'];
            $pagina = 1;
            $registroInicial = 0;
           
            
            $palabras = explode(' ', htmlspecialchars($datos));
            
            $condicionales = $data[1];
            
            if($condicionales == ""){
                $condicion = '(b.usuario REGEXP "('.implode('|', $palabras).')")';
                
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

            $arregloItems = $objeto->listar($registroInicial, $registros, array('0'), $condicion, 'b.usuario');

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
    $objeto = new Bitacora();
    
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
             $consultaGlobal = '(b.usuario REGEXP "('.implode('|', $palabras).')")';
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

?>