<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Perfiles
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 **/

if (isset($url_accion)) {
    switch ($url_accion) {
        case "add"      :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                            adicionarPerfil($datos);
                            break;
        case "edit"     :   ($forma_procesar) ? $datos = $forma_datos : $datos = array();
                            modificarPerfil($forma_id, $datos);
                            break;
        case "delete"   :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                            eliminarPerfil($forma_id, $confirmado);
                            break;
        case "up"       :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                            subirPerfil($forma_id, $confirmado);
                            break;
        case "down"     :   ($forma_procesar) ? $confirmado = true : $confirmado = false;
                            bajarPerfil($forma_id, $confirmado);
                            break;
    }
}





function adicionarPerfil($datos = array()) {
    global $textos, $sql;

    $perfil    = new Perfil();
    $destino = "/ajax".$perfil->urlBase."/add";

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 30, 255);
        $codigo .= HTML::parrafo($textos->id("DESPUES_DE"), "negrilla margenSuperior");

        $tipos   = $sql->seleccionar(array("tipos_usuario"), array("orden", "nombre"), "id IS NOT NULL", "id", "orden DESC");

        while ($tipo = $sql->filaEnObjeto($tipos)) {
            $ubicacion[$tipo->orden] = $tipo->nombre;
        }

        $codigo .= HTML::listaDesplegable("datos[orden]", $ubicacion);

        $consulta = $sql->seleccionar(array("modulos"), array("id", "nombre"), "global = '0' AND requiere_permisos = '1'", "id", "clase ASC, orden ASC");

        if ($sql->filasDevueltas) {

            $columnas = array(
                $textos->id("MODULO"),
                $textos->id("NIVEL_CONSULTA"),
                $textos->id("NIVEL_ADICION")
                
            );

            
                $opciones[1] = "SI";
                $opciones[0] = "NO";
            

            while ($modulo = $sql->filaEnObjeto($consulta)) {
                $filas[] = array(
                    $textos->id($modulo->nombre),
                    HTML::listaDesplegable("datos[permisos][".$modulo->id."][nivel_consulta]", $opciones, "", "ancho100px"),
                    HTML::listaDesplegable("datos[permisos][".$modulo->id."][nivel_adicion]", $opciones, "", "ancho100px")
                  
                );
            }

            $claseCeldas = array(
                "izquierda",
                "centrado",
                "centrado",

            );

            $codigo .= HTML::tabla($columnas, $filas, "margenSuperior", "", "", $claseCeldas);
        }

        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ADICIONAR_PERFIL"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 500;
        $respuesta["alto"]    = 600;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif (preg_match("/[^A-Za-z\,\ ]/", $datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_NOMBRE");

        } elseif ($sql->existeItem("tipos_usuario", "nombre", $datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_EXISTE_NOMBRE");

        } else {

            // Recursos::escribirTxt("hola", 5);

           if ($perfil->adicionar($datos)) {
                $respuesta["error"]   = false;
                $respuesta["accion"]  = "recargar";

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            } 
        }
    }

    Servidor::enviarJSON($respuesta);
}






function modificarPerfil($id, $datos = array()) {
    global $textos, $sql;

    $perfil    = new Perfil($id);
    $destino = "/ajax".$perfil->urlBase."/edit";

    if (empty($datos)) {
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($textos->id("NOMBRE"), "negrilla margenSuperior");
        $codigo .= HTML::campoTexto("datos[nombre]", 30, 255, $perfil->nombre);
        $codigo .= HTML::parrafo($textos->id("DESPUES_DE"), "negrilla margenSuperior");

        $tipos   = $sql->seleccionar(array("tipos_usuario"), array("orden", "nombre"), "id != '".$perfil->id."'", "id", "orden DESC");
        $orden   = "";

        while ($tipo = $sql->filaEnObjeto($tipos)) {

            if (!$orden) {
                $orden = $tipo->orden;
            }

            $ubicacion[$tipo->orden] = $tipo->nombre;

            if ($tipo->orden > $perfil->orden && $tipo->orden < $orden) {
                $orden = $tipo->orden;
            }
        }

        $codigo .= HTML::listaDesplegable("datos[orden]", $ubicacion, $orden, "", "", "-");

        $consulta = $sql->seleccionar(array("modulos"), array("id", "nombre"), "global = '0' AND requiere_permisos = '1'", "id", "clase ASC, orden ASC");


        if ($sql->filasDevueltas) {

            $columnas = array(
                $textos->id("MODULO"),
                $textos->id("NIVEL_CONSULTA"),
                $textos->id("NIVEL_ADICION")
            );


             $opciones[1] = "SI";
             $opciones[0] = "NO";


            $columnasTabla = $sql->obtenerColumnas("permisos");

            while ($modulo = $sql->filaEnObjeto($consulta)) {
                $permisosActuales = $sql->seleccionar(array("permisos"), $columnasTabla, "id_tipo_usuario = '".$perfil->id."' AND id_modulo = '".$modulo->id."'");
                $permisoActual    = $sql->filaEnObjeto($permisosActuales);

                $filas[] = array(
                    $textos->id($modulo->nombre),
                    HTML::listaDesplegable("datos[permisos][".$modulo->id."][nivel_consulta]", $opciones, $permisoActual->nivel_consulta, "ancho100px"),
                    HTML::listaDesplegable("datos[permisos][".$modulo->id."][nivel_adicion]", $opciones, $permisoActual->nivel_adicion, "ancho100px")
                );
            }

            $claseCeldas = array(
                "izquierda",
                "centrado",
                "centrado"
            );


            $codigo .= HTML::tabla($columnas, $filas, "margenSuperior", "", "", $claseCeldas);
        }


        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_PERFIL"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 700;
        $respuesta["alto"]    = 600;

    } else {
        $respuesta["error"]   = true;

        if (empty($datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FALTA_NOMBRE");

        } elseif (preg_match("/[^A-Za-z\,\ ]/", $datos["nombre"])) {
            $respuesta["mensaje"] = $textos->id("ERROR_FORMATO_NOMBRE");

        } elseif ($sql->existeItem("tipos_usuario", "nombre", $datos["nombre"], "id != '".$perfil->id."'")) {
            $respuesta["mensaje"] = $textos->id("ERROR_EXISTE_NOMBRE");

        } else {

            if ($perfil->modificar($datos)) {
                $respuesta["error"]   = false;
                //
                $respuesta["accion"]  = "recargar";

            } else {
                $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}





function eliminarPerfil($id, $confirmado) {
    global $textos, $sql;

    $perfil    = new Perfil($id);
    $destino = "/ajax".$perfil->urlBase."/delete";

    if (!$confirmado) {
        $nombre  = HTML::frase($perfil->nombre, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_ELIMINACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("ELIMINAR_PERFIL"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 250;
        $respuesta["alto"]    = 140;

    } else {

        if ($perfil->eliminar()) {
            $respuesta["error"]   = false;
            //$respuesta["mensaje"] = $textos->id("PERFIL_ELIMINADO");
            $respuesta["accion"]  = "recargar";

        } else {
            $respuesta["error"]   = true;
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}




function subirPerfil($id, $confirmado) {
    global $textos, $sql;

    $perfil    = new Perfil($id);
    $destino = "/ajax".$perfil->urlBase."/up";

    if (!$confirmado) {
        $nombre  = HTML::frase($perfil->nombre, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_MODIFICACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
        $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_PERFIL"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 250;
        $respuesta["alto"]    = 140;

    } else {

        if ($perfil->subir()) {
            $respuesta["error"]   = false;
            //$respuesta["mensaje"] = $textos->id("PERFIL_MODIFICADO");
            $respuesta["accion"]  = "recargar";

        } else {
            $respuesta["error"]   = true;
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}






function bajarPerfil($id, $confirmado) {
    global $textos, $sql;

    $perfil    = new Perfil($id);
    $destino = "/ajax".$perfil->urlBase."/down";

    if (!$confirmado) {
        $nombre  = HTML::frase($perfil->nombre, "negrilla");
        $nombre  = preg_replace("/\%1/", $nombre, $textos->id("CONFIRMAR_MODIFICACION"));
        $codigo  = HTML::campoOculto("procesar", "true");
        $codigo .= HTML::campoOculto("id", $id);
        $codigo .= HTML::parrafo($nombre);
        $codigo .= HTML::parrafo(HTML::boton("chequeo", $textos->id("ACEPTAR")), "margenSuperior");
        $codigo  = HTML::forma($destino, $codigo);

        $respuesta["generar"] = true;
        $respuesta["codigo"]  = $codigo;
        $respuesta["destino"] = "#cuadroDialogo";
         $respuesta["titulo"]  = HTML::contenedor(HTML::frase(HTML::parrafo($textos->id("MODIFICAR_PERFIL"), "letraNegra negrilla"), "bloqueTitulo-IS"), "encabezadoBloque-IS");
        $respuesta["ancho"]   = 250;
        $respuesta["alto"]    = 140;

    } else {

        if ($perfil->bajar()) {
            $respuesta["error"]   = false;
            //$respuesta["mensaje"] = $textos->id("PERFIL_MODIFICADO");
            $respuesta["accion"]  = "recargar";

        } else {
            $respuesta["error"]   = true;
            $respuesta["mensaje"] = $textos->id("ERROR_DESCONOCIDO");
        }
    }

    Servidor::enviarJSON($respuesta);
}

?>