<?php
/**
 * @package     FOLCS
 * @subpackage  SubcategoriasBB
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 **/    
if (isset($url_accion)) {
    switch ($url_accion) {
        case "see"        :   cosultarItem($forma_id);
                            break;

        case 'add'        :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              adicionarItem($datos);
                               break;   

        case 'edit'       :    ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                              modificarItem($forma_id, $datos);
                               break;

        case 'delete'     :    ($forma_procesar) ? $confirmado = true : $confirmado = false;
                               eliminarItem($forma_id, $confirmado);
                               break;

        case "search"     :    ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                buscarItem($forma_datos);
                                break;

        case "move"       :    paginador($forma_pagina, $forma_orden, $forma_nombreOrden, $forma_consultaGlobal);
                               break;   

        case "listar"     :    listarItems($url_cadena);
                               break;  

    }

}

 /**
 * Funcion que muestra la ventana modal de consulta para un item
 * 
 * @global objeto $textos   = objeto global encargado de la traduccion de los textos     
 * @param int $id           = id del item a consultar 
 */
function cosultarItem($id) {
    global $textos, $sql;

    if(!isset($id) || (isset($id) && !$sql->existeItem("subcategorias_bb", "id", $id))){
        $respuesta             = array();
        $respuesta["error"]    = true;
        $respuesta["mensaje"]  = $textos->id("NO_HA_SELECCIONADO_ITEM");
        
        Servidor::enviarJSON($respuesta);
        return  NULL;
        
    }     
        
    $objeto   = new SubCategoriaBB($id);
    $respuesta = array();

    $codigo  = HTML::parrafo($textos->id("TITULO"), "negrilla margenSuperior");
    $codigo .= HTML::parrafo($objeto->titulo, "", "");
    $codigo  = HTML::parrafo($textos->id("RESUMEN"), "negrilla margenSuperior");
    $codigo .= HTML::parrafo($objeto->resumen, "", "");    
    $codigo .= HTML::parrafo($textos->id("DESCRIPCION"), "negrilla margenSuperior");
    $codigo .= HTML::parrafo($objeto->descripcion, "", "");
    $codigo .= HTML::parrafo($textos->id("CATEGORIA"), "negrilla margenSuperior");
    $codigo .= HTML::parrafo($objeto->categoria->titulo, "", "");    
    $codigo .= HTML::parrafo($textos->id("AUTOR"), "negrilla margenSuperior");
    $codigo .= HTML::parrafo($objeto->usuario->usuario, "", "");    
    $codigo .= HTML::parrafo($textos->id("FECHA_CREACION"), "negrilla margenSuperior");
    $codigo .= HTML::parrafo($objeto->fechaCreacion, "", "");     
    $codigo .= HTML::parrafo($textos->id('IMAGEN'), 'negrilla margenSuperior');
    $codigo .= HTML::enlace(HTML::imagen($objeto->imagenMiniatura, 'imagenItem', ''), $objeto->imagenPrincipal, '', '', array('rel' => 'prettyPhoto[]'));   
    $codigo .= HTML::parrafo($textos->id('ESTADO'), 'negrilla margenSuperior');
    $activo  = ($objeto->activo) ?  HTML::frase($textos->id('ACTIVO'), 'activo') : HTML::frase($textos->id('INACTIVO'), 'inactivo');
    $codigo .= HTML::parrafo($activo, '', '');     
    

    $respuesta["generar"] = true;
    $respuesta["codigo"]  = $codigo;
    $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("CONSULTAR_ITEM"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
    $respuesta["destino"] = "#cuadroDialogo";
    $respuesta["ancho"]   = 450;
    $respuesta["alto"]    = 450;


    Servidor::enviarJSON($respuesta);
} 

/**
 * Función con doble comportamiento. La primera llamada (con el arreglo de datos vacio)
 * muestra el formulario para el ingreso del registro. El destino de este formulario es esta 
 * misma función. Una vez viene desde el formulario con el arreglo datos cargado de valores
 * se encarga de validar la información y llamar al metodo adicionar del objeto.
 * 
 * @global recurso $textos  = objeto global de gestion de los textos de idioma
 * @global recurso $sql     = objeto global de interaccion con la BD
 * @global archivo $archivo_imagen = variable global que contiene la posicion del objeto global $_FILES en la posicion "el nombre del archivo en el form"
 * @global arreglo $configuracion = variable global que contiene la informacion general de configuración
 * @global objeto $sesion_usuarioSesion = variable global que contiene el objeto usuario que representa el usuario que inició la sesión  
 * @param array $datos      = arreglo con la informacion a adicionar
 */
function adicionarItem($datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen, $sesion_usuarioSesion;

    $objeto         = new SubCategoriaBB();
    $destino        = '/ajax'.$objeto->urlBase.'/add';
    $respuesta      = array();

    if (empty($datos)) {
    

        //cargar el selector de categorias con la informacion correspondiente
        $categorias = $sql->seleccionar(array("categorias_bb"), array("id", "titulo"), "id IS NOT NULL AND id!= '0' AND activo ='1'", "id", "titulo ASC");

		$categoriasBB = array();
        //lista desplegable para la seleccion de la categoria
        while ($categoria = $sql->filaEnObjeto($categorias)) {
                $categoriasBB[$categoria->id] = $categoria->titulo;
        }      
        
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo]', 50, 255);
        $codigo .= HTML::parrafo($textos->id('RESUMEN'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[resumen]', 2, 90, '', '');        
        $codigo .= HTML::parrafo($textos->id("CATEGORIA"), "negrilla margenSuperior");    
        $codigo .= HTML::listaDesplegable("datos[id_categoria]", $categoriasBB, '', "listaSeleccionaCategoria", "listaSeleccionaCategoria");  
        $codigo .= HTML::parrafo($textos->id('DESCRIPCION'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[descripcion]', 10, 60, '', 'editor');
        $codigo .= HTML::parrafo($textos->id('IMAGEN'), 'negrilla margenSuperior');
        $codigo .= HTML::campoArchivo('imagen', 50, 255);  
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', true).$textos->id('ACTIVO'), 'margenSuperior');       
        $codigo .= Perfil::mostrarChecksPermisosAdicion('');
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), 'botonOk', 'botonOk', 'botonOk').HTML::frase('     '.$textos->id('REGISTRO_AGREGADO'), 'textoExitoso', 'textoExitoso'), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo, 'P', true);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ADICIONAR_ITEM'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['ancho']   = 750;
        $respuesta['alto']    = 630;

    } else {
        $respuesta['error']   = true;//asumimos que por defecto hay error

        if(!empty($archivo_imagen['tmp_name'])){
            $validarFormato = Recursos::validarArchivo($archivo_imagen, array('jpg','png','gif', 'jpeg'));
            $area           = getimagesize($archivo_imagen['tmp_name']);
         }

        if (empty($datos['titulo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TITULO');

        } elseif (empty($datos['descripcion'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_DESCRIPCION');

        } elseif (empty($archivo_imagen['tmp_name'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_IMAGEN');

        } elseif ($validarFormato) {
            $respuesta['mensaje'] = $textos->id('ERROR_FORMATO_IMAGEN_CATEGORIA_BB');

        } elseif ($area[0] != $configuracion['DIMENSIONES']['anchoSubcategoriaBBNormal'] || $area[1] != $configuracion['DIMENSIONES']['altoSubcategoriaBBNormal']) {
                    $respuesta['mensaje'] = $textos->id('ERROR_AREA_IMAGEN_SUBCATEGORIA_BB');

        }else {

            $idItem = $objeto->adicionar($datos);

            if ($idItem) {                
            /**************** Creo el nuevo item que se insertara via ajax ****************/
                $objeto     = new SubcategoriaBB($idItem);               

                $estado     = ($objeto->activo) ? HTML::frase($textos->id('ACTIVO'), 'activo') : $estado = HTML::frase($textos->id('INACTIVO'), 'inactivo');
                 
                $celdas     = array($objeto->titulo, $objeto->categoria->titulo, $objeto->fechaCreacion, $estado); 
                $claseFila  = "";
                $idFila     = $idItem;
                $celdas     = HTML::crearNuevaFila($celdas, $claseFila, $idFila);
                
                if($datos["dialogo"] == ""){
                    $respuesta["error"]                = false;//desactivamos el error por defecto
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
 * Función con doble comportamiento. La primera llamada (con el arreglo de datos vacio)
 * muestra el formulario con los datos del registro a ser modificado. El destino de este formulario es esta 
 * misma función, pero una vez viene desde el formulario con el arreglo datos cargado de valores
 * se encarga de validar la información y llamar al metodo modificar del objeto.
 * 
 * @global recurso $textos  = objeto global de gestion de los textos de idioma
 * @global recurso $sql     = objeto global de interaccion con la BD
 * @global archivo $archivo_imagen = variable global que contiene la posicion del objeto global $_FILES en la posicion "el nombre del archivo en el form"
 * @global arreglo $configuracion = variable global que contiene la informacion general de configuración
 * @param int $id           = id del registro a modificar
 * @param array $datos      = arreglo con la informacion a adicionar
 */
function modificarItem($id, $datos = array()) {
    global $textos, $sql, $configuracion, $archivo_imagen;

    if (!is_numeric($id) || !$sql->existeItem('subcategorias_bb', 'id', $id)) {
    	$respuesta['error']   = true;
    	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');

    	Servidor::enviarJSON($respuesta);

    	return NULL;

    }

    $objeto         = new SubCategoriaBB($id);
    $destino        = '/ajax'.$objeto->urlBase.'/edit';
    $idArchivo      = '';

    if (empty($datos)) {
    
        //cargar el selector de categorias con la informacion correspondiente
        $categorias = $sql->seleccionar(array("categorias_bb"), array("id", "titulo"), "id IS NOT NULL AND id!= '0' AND activo ='1'", "id", "titulo ASC");

		$categoriasBB = array();
        //lista desplegable para la seleccion de la categoria
        while ($categoria = $sql->filaEnObjeto($categorias)) {
                $categoriasBB[$categoria->id] = $categoria->titulo;
        }  
            
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo]', 50, 255, $objeto->titulo);
        $codigo .= HTML::parrafo($textos->id('RESUMEN'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[resumen]', 2, 90, $objeto->resumen, '');         
        $codigo .= HTML::parrafo($textos->id("CATEGORIA"), "negrilla margenSuperior");    
        $codigo .= HTML::listaDesplegable("datos[id_categoria]", $categoriasBB, $objeto->idCategoria, "listaSeleccionaCategoria", "listaSeleccionaCategoria");
        $codigo .= HTML::parrafo($textos->id('DESCRIPCION'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[descripcion]', 10, 60, $objeto->descripcion, 'editor');
        $codigo .= HTML::parrafo($textos->id('IMAGEN'), 'negrilla margenSuperior');
        $codigo .= HTML::campoArchivo('imagen', 50, 255);
        $codigo .= HTML::contenedor(HTML::imagen($objeto->imagenMiniatura, 'imagenItem', ''), 'imagenExistenteEnItem');         
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', $objeto->activo).$textos->id('ACTIVO'), 'margenSuperior');
        $codigo .= Perfil::mostrarChecksPermisosAdicion($id, $objeto->idModulo);        
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo, 'P', true);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('MODIFICAR_ITEM'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 700;
        $respuesta['alto']    = 630;

    } else {
        $respuesta['error']   = true;

        $validarFormato = false;

        if(!empty($archivo_imagen['tmp_name'])){
            $validarFormato = Recursos::validarArchivo($archivo_imagen, array('jpg','png','gif', 'jpeg'));
            $area           = getimagesize($archivo_imagen['tmp_name']);
         }        

        if (empty($datos['titulo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TITULO');

        } else if (empty($datos['descripcion'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_DESCRIPCION');

        } else if ($validarFormato) {
            $respuesta['mensaje'] = $textos->id('ERROR_FORMATO_IMAGEN_CATEGORIA_BB');

        } else if (!empty($archivo_imagen['tmp_name']) && ($area[0] != $configuracion['DIMENSIONES']['anchoSubcategoriaBBNormal'] || $area[1] != $configuracion['DIMENSIONES']['altoSubcategoriaBBNormal']) ) {
                    $respuesta['mensaje'] = $textos->id('ERROR_AREA_IMAGEN_SUBCATEGORIA_BB');

        } else {

            $idItem = $objeto->modificar($datos);

            if ($idItem) {                
            /**************** Creo el nuevo item que se insertara via ajax ****************/
                $objeto    = new SubCategoriaBB($id);   

                $estado     = ($objeto->activo) ? HTML::frase($textos->id('ACTIVO'), 'activo') : $estado = HTML::frase($textos->id('INACTIVO'), 'inactivo');
                 
                $celdas     = array($objeto->titulo, $objeto->categoria->titulo, $objeto->fechaCreacion, $estado); 

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
*Metodo Para eliminar una categoria_bb desde dentro de la categoria_bb
**/
function eliminarItem($id, $confirmado) {
    global $textos, $sql;

    if(!is_numeric($id) || !$sql->existeItem('subcategorias_bb', 'id', $id)){
    	$respuesta['error']   = true;
    	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');

    	Servidor::enviarJSON($respuesta);
    	return NULL;
    }

    $objeto     = new SubCategoriaBB($id);
    $destino    = '/ajax'.$objeto->urlBase.'/delete';

    if (!$confirmado) {
        $titulo  = HTML::frase($objeto->titulo, 'negrilla');
        $titulo  = str_replace('%1', $titulo, $textos->id('CONFIRMAR_ELIMINACION'));
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($titulo);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('ELIMINAR_ITEM'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 350;
        $respuesta['alto']    = 150;

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

            $objeto     = new SubcategoriaBB();

            $registros  = $configuracion["GENERAL"]["registrosPorPagina"];
            $pagina     = 1;

            $registroInicial = 0;
           
            
            $palabras = explode(" ", $datos);
            
            $condicionales = $data[1];
            
            if($condicionales == ""){
                $condicion = "(n.titulo REGEXP '(".implode("|", $palabras).")')";
                
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

            $arregloItems = $objeto->listar($registroInicial, $registros, array("0"), $condicion, "n.titulo");

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
    
    $item       = "";
    $respuesta  = array();
    $objeto     = new SubcategoriaBB();
    
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
             $consultaGlobal = "(n.titulo REGEXP '(".implode("|", $palabras).")')";
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
 * Funcion encargada de devvolver una lista con la informacion para el autocompletar
 * @global type $sql
 * @param type $cadena 
 */
function listarItems($cadena) {
    global $sql;

    $respuesta = array();
    $consulta  = $sql->seleccionar(array("subcategorias_bb"), array("titulo"), "nombre LIKE '%$cadena%'", "", "titulo ASC", 0, 20);

    while ($fila = $sql->filaEnObjeto($consulta)) {
        $respuesta[] = $fila->titulo;

    }

    Servidor::enviarJSON($respuesta);

}
