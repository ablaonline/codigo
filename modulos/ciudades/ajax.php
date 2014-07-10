<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Ciudades
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/

global $url_accion, $forma_procesar, $forma_id, $forma_datos, $forma_pagina, $forma_orden, $forma_nombreOrden, $url_cadena, $forma_dialogo, $forma_consultaGlobal;


if (isset($url_accion)) {
    switch ($url_accion) {
        case "add"              :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    adicionarItem($datos);
                                    break;
        case "see"              :   cosultarItem($forma_id);
                                    break;
        case "edit"             :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    modificarItem($forma_id, $datos);
                                    break;
        case "delete"           :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                                    eliminarItem($forma_id, $confirmado, $forma_dialogo);
                                    break;

        case "search"           :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                    buscarItem($forma_datos);
                                    break;
        case "move"             :   paginador($forma_pagina, $forma_orden, $forma_nombreOrden, $forma_consultaGlobal);
                                    break;   
        case "listar"           :   listarItems($url_cadena);
                                    break;   
        case "listarEstados"    :   listarEstados($url_cadena);
                                    break;                                  
                      

    }
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @return null 
 */
function cosultarItem($id) {
    global $textos, $sql;
    
    if(!isset($id) || (isset($id) && !$sql->existeItem("ciudades", "id", $id))){
        $respuesta             = array();
        $respuesta["error"]    = true;
        $respuesta["mensaje"]  = $textos->id("NO_HA_SELECCIONADO_ITEM");
        
        Servidor::enviarJSON($respuesta);
        return  NULL;
        
    }     
        
    $objeto   = new Ciudad($id);
    $respuesta = array();

    $codigo  = HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
    $codigo .= HTML::parrafo($objeto->nombre, "", "");
    $codigo .= HTML::parrafo($textos->id("ESTADO_DPTO"), "negrilla margenSuperior");
    $codigo .= HTML::parrafo($objeto->Estado, "", "");
    $codigo .= HTML::parrafo($textos->id("PAIS"), "negrilla margenSuperior");
    $codigo .= HTML::parrafo($objeto->pais, "", "");
    

    $respuesta["generar"] = true;
    $respuesta["codigo"]  = $codigo;
    $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("CONSULTAR_ITEM"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
    $respuesta["destino"] = "#cuadroDialogo";
    $respuesta["ancho"]   = 450;
    $respuesta["alto"]    = 300;



    Servidor::enviarJSON($respuesta);
} //Fin del metodo consultar


/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $datos 
 */
function adicionarItem($datos = array()) {
    global $textos, $sql;

    $objeto    = new Ciudad();
    $destino = "/ajax".$objeto->urlBase."/add";
    $respuesta = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("datos[dialogo]", "", "idDialogo");
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 30, 255);
        $codigo .= HTML::parrafo($textos->id("ESTADO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[id_estado]", 50, 255, "", "autocompletable", "", array("title" => HTML::urlInterna("CIUDADES",0,true,"listarEstados")));
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_ITEM"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 450;
        $respuesta["alto"]    = 300;

    } else {
        $respuesta["error"]   = true;
        $idEstado = $sql->obtenerValor("lista_estados", "id", "cadena = '".utf8_decode($datos["id_estado"])."'");

        if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif (empty($datos["id_estado"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_ESTADO");

        } elseif ($sql->existeItem("ciudades", "nombre", utf8_decode($datos["nombre"]) , "id_estado = '".$idEstado."'")) {
            $respuesta["mensaje"] = $textos->id("ERROR_EXISTE_NOMBRE");

        } elseif (!$sql->existeItem("lista_estados", "cadena", utf8_decode ($datos["id_estado"]) )) {
            $respuesta["mensaje"] = $textos->id("ERROR_NO_EXISTE_ESTADO");

        } else {

            $idItem = $objeto->adicionar($datos);
            if ($idItem) {                
            /**************** Creo el nuevo item que se insertara via ajax ****************/
                $objeto  = new Ciudad($idItem);               
                 
                $celdas    = array($objeto->nombre, $objeto->Estado, $objeto->pais); 
                $claseFila = "";
                $idFila    = $idItem;
                $celdas    = HTML::crearNuevaFila($celdas, $claseFila, $idFila);
                
                if($datos["dialogo"] == ""){
                    $respuesta["error"]                = false;
                    $respuesta["accion"]               = "insertar";
                    $respuesta["contenido"]            = $celdas;
                    $respuesta["idContenedor"]         = "#tr_".$idItem;
                    $respuesta["insertarNuevaFila"]    = true;
                    $respuesta["idDestino"]            = "#tablaRegistros";
                    
                }else{
                    $respuesta["error"]                = false;
                    $respuesta["accion"]               = "insertar";
                    $respuesta["contenido"]            = $celdas;
                    $respuesta["idContenedor"]         = "#tr_".$idItem;
                    $respuesta["insertarNuevaFilaDialogo"]    = true;
                    $respuesta["idDestino"]            = "#tablaRegistros";
                    $respuesta["ventanaDialogo"]       = $datos["dialogo"];
                }

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
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
function modificarItem($id, $datos = array()) {
    global $textos, $sql;

    $objeto    = new Ciudad($id);
    $destino   = "/ajax".$objeto->urlBase."/edit";
    $respuesta = array();

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::campoOculto("datos[dialogo]", "", "idDialogo");
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 30, 255, $objeto->nombre);
        $codigo .= HTML::parrafo($textos->id("ESTADO"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[id_estado]", 30, 255, $objeto->Estado." - ".$objeto->paisSolo, "autocompletable", "", array("title" => HTML::urlInterna("CIUDADES",0,true,"listarEstados")));
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_AGREGADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_ITEM"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 450;
        $respuesta["alto"]    = 300;

    } else {
        $respuesta["error"]   = true;
        $idEstado = $sql->obtenerValor("lista_estados", "id", "cadena = '".utf8_decode($datos["id_estado"])."'");
        if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif (empty($datos["id_estado"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_ESTADO");

        } elseif ($sql->existeItem("ciudades", "nombre", utf8_decode($datos["nombre"]), "id != '".$objeto->id."' AND id_estado = '".$idEstado."'")) {
            $respuesta["mensaje"] = $textos->id("ERROR_EXISTE_NOMBRE");

        } elseif (!$sql->existeItem("lista_estados", "cadena", utf8_decode($datos["id_estado"]) )) {
            $respuesta["mensaje"] = $textos->id("ERROR_NO_EXISTE_ESTADO");

        } else {
            $idItem = $objeto->modificar($datos);
            if ($idItem) {                
            /**************** Creo el nuevo item que se insertara via ajax ****************/
                $objeto  = new Ciudad($id);                  
                $celdas    = array($objeto->nombre, $objeto->Estado, $objeto->pais);
                $celdas    = HTML::crearFilaAModificar($celdas);

                if($datos["dialogo"] == ""){
                    $respuesta["error"]                = false;
                    $respuesta["accion"]               = "insertar";
                    $respuesta["contenido"]            = $celdas;
                    $respuesta["idContenedor"]         = "#tr_".$id;
                    $respuesta["modificarFilaTabla"]   = true;
                    $respuesta["idDestino"]            = "#tr_".$id;
                }else{
                    $respuesta["error"]                = false;
                    $respuesta["accion"]               = "insertar";
                    $respuesta["contenido"]            = $celdas;
                    $respuesta["idContenedor"]         = "#tr_".$id;
                    $respuesta["modificarFilaDialogo"]   = true;
                    $respuesta["idDestino"]            = "#tr_".$id;
                    $respuesta["ventanaDialogo"]       = $datos["dialogo"];
                }



            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
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
 * @param type $confirmado 
 */
function eliminarItem($id, $confirmado, $dialogo) {
    global $textos;

    $objeto    = new Ciudad($id);
    $destino = "/ajax".$objeto->urlBase."/delete";
    $respuesta = array();

    if (!$confirmado) {
        $nombre  = HTML::frase($objeto->nombre, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::campoOculto("dialogo", "", "idDialogo");
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo .= HTML::parrafo($textos->id("REGISTRO_ELIMINADO"), "textoExitoso", "textoExitoso");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
         $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_ITEM"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 350;
        $respuesta["alto"]    = 150;
    } else {

        if ($objeto->eliminar()) {
            if($dialogo == ""){
                $respuesta["error"]              = false;
                $respuesta["accion"]             = "insertar";
                $respuesta["idDestino"]          = "#tr_".$id;
                $respuesta["eliminarFilaTabla"]  = true;
            }else{
                $respuesta["error"]              = false;
                $respuesta["accion"]             = "insertar";
                $respuesta["idDestino"]          = "#tr_".$id;
                $respuesta["eliminarFilaDialogo"]  = true;
                $respuesta["ventanaDialogo"]       = $dialogo;
            }


        } else {
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}


   
/**
 * 
 * @global type $textos
 * @global type $sql
 * @param type $datos 
 */
function buscarItem($data) {
    global $textos, $configuracion;
     
    $data = explode("[", $data);    
    $datos = $data[0];
    
     if(empty($datos)) { 
         $respuesta["error"]   = true;
         $respuesta["mensaje"] = $textos->id("ERROR_FALTA_CADENA_BUSQUEDA");
            
     }else if(!empty($datos) && strlen($datos) < 2){
         $respuesta["error"]   = true;
         $respuesta["mensaje"] = str_replace("%1", "2", $textos->id("ERROR_TAMAÑO_CADENA_BUSQUEDA"));
         
         
     } else {
            $item       = "";
            $respuesta  = array();
            $objeto = new Ciudad();
            $registros = $configuracion["GENERAL"]["registrosPorPagina"];
            $pagina = 1;
            $registroInicial = 0;
           
            
            $palabras = explode(" ", $datos);
            
            $condicionales = $data[1];
            
            if($condicionales == ""){
                $condicion = "(c.nombre REGEXP '(".implode("|", $palabras).")')";
                
            }else{
                //$condicion = str_replace("]", "'", $data[1]);
                $condicionales = explode("|", $condicionales);
                
                $condicion = "(";
                $tam = sizeof($condicionales) - 1;
                for($i = 0; $i < $tam; $i++){
                    $condicion .=  $condicionales[$i]." REGEXP '(".implode("|", $palabras).")' ";
                    if($i != $tam -1){
                        $condicion .= " OR ";
                    }
                }
                $condicion .= ")";            
                
            }

            $arregloItems = $objeto->listar($registroInicial, $registros, array("0"), $condicion, "c.nombre");

            if ($objeto->registrosConsulta) {//si la consulta trajo registros
                $datosPaginacion = array($objeto->registrosConsulta, $registroInicial, $registros, $pagina, $objeto->registrosConsulta);         
                $item  .= $objeto->generarTabla($arregloItems, $datosPaginacion);    
                $info   = HTML::parrafo("You got ".$objeto->registrosConsulta." results", "textoExitosoNotificaciones");

            }else{
                $datosPaginacion = 0;        
                $item  .= $objeto->generarTabla($textos->id("NO_HAY_REGISTROS"), $datosPaginacion);
                $info   = HTML::parrafo("Your search didn't bring results", "textoErrorNotificaciones");
            }

            $respuesta["error"]                = false;
            $respuesta["accion"]               = "insertar";
            $respuesta["contenido"]            = $item;
            $respuesta["idContenedor"]         = "#tablaRegistros";
            $respuesta["idDestino"]            = "#contenedorTablaRegistros";
            $respuesta["paginarTabla"]         = true;
            $respuesta["info"]                 = $info; 
          

        } 

    Servidor::enviarJSON($respuesta);
}



/**
 *
 * @global type $configuracion
 * @param type $pagina
 * @param type $orden
 * @param type $nombreOrden
 * @param type $consultaGlobal 
 */
function paginador($pagina, $orden = NULL, $nombreOrden = NULL, $consultaGlobal = NULL){
    global $configuracion;
    
    $item = "";
    $respuesta = array();
    $objeto = new Ciudad();
    
    $registros = $configuracion["GENERAL"]["registrosPorPaginaTabla"];
    
    if (isset($pagina)) {
        $pagina = $pagina;
    } else {
        $pagina = 1;
    }

    if(isset($consultaGlobal) && $consultaGlobal != ""){

         $data = explode("[", $consultaGlobal);
         $datos = $data[0];
         $palabras = explode(" ", $datos);

         if($data[1] != ""){    
            $condicionales = explode("|",  $data[1]);
                
                $condicion = "(";
                $tam = sizeof($condicionales) - 1;
                for($i = 0; $i < $tam; $i++){
                    $condicion .=  $condicionales[$i]." REGEXP '(".implode("|", $palabras).")' ";
                    if($i != $tam -1){
                        $condicion .= " OR ";
                    }
                }
                $condicion .= ")";     

             $consultaGlobal = $condicion; 
           }else{
             $consultaGlobal = "(c.nombre REGEXP '(".implode("|", $palabras).")')";
           } 
  
    }else{
      $consultaGlobal = "";
    }
    
    if(!isset($nombreOrden)){
        $nombreOrden = $objeto->ordenInicial;
    }    
    
    
    if(isset($orden) && $orden == "ascendente"){//ordenamiento
        $objeto->listaAscendente = true;
    }else{
        $objeto->listaAscendente = false;
    }
    
    if(isset($nombreOrden) && $nombreOrden == "estado"){//ordenamiento
        $nombreOrden = "activo";
    }
    
    $registroInicial = ($pagina - 1) * $registros;
        
    
    $arregloItems = $objeto->listar($registroInicial, $registros, array("0"), $consultaGlobal, $nombreOrden);
    
    if ($objeto->registrosConsulta) {//si la consulta trajo registros
        $datosPaginacion = array($objeto->registrosConsulta, $registroInicial, $registros, $pagina);        
        $item  .= $objeto->generarTabla($arregloItems, $datosPaginacion);    

    }
    
    $respuesta["error"]                = false;
    $respuesta["accion"]               = "insertar";
    $respuesta["contenido"]            = $item;
    $respuesta["idContenedor"]         = "#tablaRegistros";
    $respuesta["idDestino"]            = "#contenedorTablaRegistros";
    $respuesta["paginarTabla"]         = true;   
    
    Servidor::enviarJSON($respuesta);    
    
}



/**
 *
 * @global type $sql
 * @param type $cadena 
 */
function listarItems($cadena) {
    global $sql;

    $respuesta = array();
    $consulta  = $sql->seleccionar(array("lista_ciudades"), array("cadena"), "cadena LIKE '%$cadena%'", "", "cadena ASC", 0, 20);

    while ($fila = $sql->filaEnObjeto($consulta)) {
        $respuesta[] = $fila->cadena;
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $sql
 * @param type $cadena 
 */
function listarEstados($cadena) {
    global $sql;

    $respuesta = array();
    $consulta  = $sql->seleccionar(array("lista_estados"), array("cadena"), "cadena LIKE '%$cadena%'", "", "cadena ASC", 0, 20);

    while ($fila = $sql->filaEnObjeto($consulta)) {
        $respuesta[] = $fila->cadena;
    }

    Servidor::enviarJSON($respuesta);
}


?>