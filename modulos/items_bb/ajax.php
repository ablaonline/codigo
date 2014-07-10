<?php
/**
 * @package     FOLCS
 * @subpackage  ItemsBB
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2013 Colombo Americano Cali
 * @version     0.1
 **/    
if (isset($url_accion)) {
    switch ($url_accion) {

        case 'add'        :    ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                               adicionarItem($datos, $forma_id_subcategoria);
                               break;   

        case 'edit'       :    ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                               modificarItem($forma_id, $datos);
                               break;
        case 'editRegister' :    ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                 modificarItem($forma_id, $datos);
                                 break;

        case 'deleteRegister' :    ($forma_procesar) ? $confirmado = true : $confirmado = false;
                               eliminarItem($forma_id, $confirmado);
                               break;

        case 'delete'     :    ($forma_procesar) ? $confirmado = true : $confirmado = false;
                               eliminarItem($forma_id, $confirmado);
                               break;                               

        case 'search'     :    ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                                buscar($forma_datos, $forma_id_subcategoria);
                                break;

    }

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
function adicionarItem($datos = array(), $idSubcategoria) {
    global $textos, $sql, $configuracion, $sesion_usuarioSesion;

    $objeto         = new BulletinBoardItem();
    $destino        = '/ajax'.$objeto->urlBase.'/add';
    $respuesta      = array();

    if (empty($datos)) {
        
        $objetoSubcategorias    = new SubCategoriaBB();

        $objetoCategorias       = new CategoriaBB();    

        //cargar el selector de categorias con la informacion correspondiente
        $arregloCategorias  = $objetoCategorias->listar(0, 0, array(0), '', 'n.titulo', 'CATEGORIAS_BB');
        $subcategoriasBB    = array();

        if ($objetoCategorias->registros) { 
            foreach ($arregloCategorias as $key => $value) {
                //llenar el arreglo de id de categorias disponibles para filtrar las subcategorias
                $arregloIdCategorias[] = $value->id;
            }

            $condicionSubCat        = " n.id_categoria IN ('".implode('\',\'',$arregloIdCategorias)."') ";    

            $arregloSubcategorias  = $objetoSubcategorias->listar(0, 0, array(0), $condicionSubCat); 

            if ($objetoSubcategorias->registros) {
                foreach ($arregloSubcategorias as $key => $value) {
                    $subcategoriasBB[$value->id] = $value->titulo;
                }
            }

        }

        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo]', 50, 255);
        $codigo .= HTML::parrafo($textos->id('RESUMEN'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[resumen]', 2, 90, '', '');   
        $codigo .= HTML::campoOculto("datos[id_subcategoria]", $idSubcategoria);  
        $codigo .= HTML::parrafo($textos->id('DESCRIPCION'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[descripcion]', 10, 60, '', 'editor');
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', true).$textos->id('ACTIVO'), 'margenSuperior');       
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

        if (empty($datos['titulo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TITULO');

        } elseif (empty($datos['descripcion'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_DESCRIPCION');

        } else {

            $idItem = $objeto->adicionar($datos);

            if ($idItem) {                
                $respuesta["error"]                = false;//desactivamos el error por defecto
                $respuesta["accion"]               = "redireccionar";
                $respuesta["destino"]              = $objeto->urlBase.'/'.$idItem;

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

    if (!is_numeric($id) || !$sql->existeItem('items_bb', 'id', $id)) {
    	$respuesta['error']   = true;
    	$respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');

    	Servidor::enviarJSON($respuesta);

    	return NULL;

    }

    $objeto         = new BulletinBoardItem($id);
    $destino        = '/ajax'.$objeto->urlBase.'/edit';
    $respuesta      = array();

    if (empty($datos)) {
        
        $objetoSubcategorias    = new SubCategoriaBB();

        $objetoCategorias       = new CategoriaBB();    

        //cargar el selector de categorias con la informacion correspondiente
        $arregloCategorias  = $objetoCategorias->listar(0, 0, array(0), '', 'n.titulo', 'CATEGORIAS_BB');
        $subcategoriasBB    = array();

        if ($objetoCategorias->registros) { 
            foreach ($arregloCategorias as $key => $value) {
                //llenar el arreglo de id de categorias disponibles para filtrar las subcategorias
                $arregloIdCategorias[] = $value->id;
            }

            $condicionSubCat        = " n.id_categoria IN ('".implode('\',\'',$arregloIdCategorias)."') ";    

            $arregloSubcategorias  = $objetoSubcategorias->listar(0, 0, array(0), $condicionSubCat); 

            if ($objetoSubcategorias->registros) {
                foreach ($arregloSubcategorias as $key => $value) {
                    $subcategoriasBB[$value->id] = $value->titulo;
                }
            }

        }
            
        $codigo  = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[titulo]', 50, 255, $objeto->titulo);
        $codigo .= HTML::parrafo($textos->id('RESUMEN'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[resumen]', 2, 90, $objeto->resumen, '');   
        $codigo .= HTML::campoOculto("datos[id_subcategoria]", $objeto->idSubcategoria);
        $codigo .= HTML::parrafo($textos->id('DESCRIPCION'), 'negrilla margenSuperior');
        $codigo .= HTML::areaTexto('datos[descripcion]', 10, 60, $objeto->descripcion, 'editor');       
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', $objeto->activo).$textos->id('ACTIVO'), 'margenSuperior');   
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo  = HTML::forma($destino, $codigo, 'P', true);

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('MODIFICAR_ITEM'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 700;
        $respuesta['alto']    = 630;

    } else {
        $respuesta['error']   = true;//asumimos que por defecto hay error

        if (empty($datos['titulo'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TITULO');

        } elseif (empty($datos['descripcion'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_DESCRIPCION');

        } else {

            $idItem = $objeto->modificar($datos);

            if ($idItem) {                
                $respuesta["error"]                = false;//desactivamos el error por defecto
                $respuesta["accion"]               = "redireccionar";
                $respuesta["destino"]              = $objeto->urlBase.'/'.$id;

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

    if(!is_numeric($id) || !$sql->existeItem('items_bb', 'id', $id)){
    $respuesta['error']   = true;
    $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
    Servidor::enviarJSON($respuesta);
    return NULL;
    }

    $objeto    = new BulletinBoardItem($id);
    $destino = '/ajax'.$objeto->urlBase.'/delete';

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
               $respuesta['error']   = false;
               $respuesta['accion']  = 'recargar';
          }else{                                
                 
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
         }

    }

    Servidor::enviarJSON($respuesta);

}

/**
*Metodo que carga el formulario para buscar y filtrar Items
**/
function buscar($datos, $idSubcategoria) {
    global $textos, $sql, $configuracion, $sesion_usuarioSesion;

    $objeto = new BulletinBoardItem();
    $destino = '/ajax'.$objeto->urlBase.'/search';

    if (empty($datos)) {

        $forma2  = HTML::campoOculto('datos[criterio]', 'titulo');
        $forma2 .= HTML::campoOculto('procesar', true);
        $forma2 .= HTML::campoOculto('datos[id_subcategoria]', $idSubcategoria);
        $forma2 .= HTML::parrafo($textos->id('TITULO'), 'negrilla margenSuperior');
        $forma2 .= HTML::parrafo(HTML::campoTexto('datos[patron]', 30, 255).HTML::boton('buscar', $textos->id('BUSCAR')), 'margenSuperior');

        $codigo1  = HTML::forma($destino, $forma2);
        $codigo   = HTML::contenedor($codigo1, 'bloqueBorde');
        $codigo  .= HTML::contenedor('','margenSuperior', 'resultadosBusqueda');

        $respuesta['generar'] = true;
        $respuesta['codigo']  = $codigo;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo']  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id('BUSCAR_ITEM'), 'letraNegra negrilla'), 'bloqueTitulo-IS'), 'encabezadoBloque-IS');
        $respuesta['ancho']   = 530;
        $respuesta['alto']    = 400;

    } else {

        if (!empty($datos['criterio']) && !empty($datos['patron'])) {

            if ($datos['criterio'] == 'titulo') {

                $palabras = explode(' ', htmlspecialchars($datos['patron']));

                foreach ($palabras as $palabra) {
                   $palabrasResaltadas[] =  HTML::frase($palabra, 'resaltado');
                   $palabrasMarcadas[]   =  '%'.$palabra.'%';
                }               
                
            }

            $condicion = "(n.titulo REGEXP '(".implode("|", $palabras).")' OR n.resumen REGEXP '(".implode("|", $palabras).")' OR n.descripcion REGEXP '(".implode("|", $palabras).")' ";

            if(isset($sesion_usuarioSesion)){
                 $condicion .= " OR n.id_usuario = '$sesion_usuarioSesion->id')";
            } else {
                $condicion .= " )";
            }

            $condicion .= " AND sc.id_subcategoria = '".$datos['id_subcategoria']."' ";

            //$sql->depurar = true;
            $consulta = $objeto->listar(0, 0, array(0), $condicion);

            //print_r($consulta);
                     
            if (sizeof($consulta) > 0) {
                $listaNoticias = array();

                foreach ($consulta as $key => $fila) {
                    $titulo = str_ireplace($palabras, $palabrasMarcadas, $fila->titulo);

                    $autor   = $sql->obtenerValor('usuarios', 'sobrenombre', 'id = "'.$fila->idAutor.'"');
                    $item3   = HTML::parrafo(str_replace('%1', $autor, $textos->id('CREADO_POR')), 'negrilla');
                    $item3  .= HTML::parrafo(HTML::enlace(str_ireplace($palabrasMarcadas, $palabrasResaltadas, $titulo).' '.' '.HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['imagenesEstilos'].'goButton.png'), HTML::urlInterna('ITEMS_BB', $fila->id)), 'negrilla');
                    $item3  .= HTML::parrafo(str_replace('%1', date('D, d M Y h:i:s A',$fila->fechaCreacion), $textos->id('PUBLICADO_EN')), 'negrilla cursiva pequenia');                  

                    $item    = HTML::contenedor($item3, 'fondoBuscadorNoticias');//barra del contenedor gris

                    $listaNoticias[] = $item;  

                 }
            } else {
                $listaNoticias[] = HTML::frase($textos->id('SIN_REGISTROS'));
            } 

            $listaNoticias = HTML::lista($listaNoticias, 'listaVertical listaConIconos bordeSuperiorLista');


            $respuesta['accion']        = 'insertar';
            $respuesta['contenido']     = $listaNoticias;
            $respuesta['destino']       = '#resultadosBusqueda';
            $respuesta['limpiaDestino'] = true;

            } else {
                $respuesta['error']     = true;
                $respuesta['mensaje']   = $textos->id('ERROR_FALTA_CADENA_BUSQUEDA');

            }

        }

    Servidor::enviarJSON($respuesta);
}
