<?php

/**
 * @package     FOLCS
 * @subpackage  Base
 * @author       Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 * */
class Recursos {

    /**
     *
     * Funcion que cargar el bloque con los comentarios realizados a un determinado item de un determinado modulo
     * @global type $sql
     * @global type $textos
     * @global type $sesion_usuarioSesion
     * @param type $modulo
     * @param type $registro
     * @param type $propietario
     * @return type Bloque de codigo HTML cuyo contenido dependera del modulo en el que se encuentre y del registro
     * 
     */
    static function bloqueComentarios($modulo, $registro, $propietario) {
        global $sql, $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }

        if (isset($sesion_usuarioSesion)) {
            $moduloActual = new Modulo($modulo);
            $bloqueComentarios = HTML::campoOculto('idModulo', $moduloActual->id);
            $bloqueComentarios .= HTML::campoOculto('idRegistro', $registro);
            $bloqueComentarios .= HTML::boton('comentario', $textos->id('COMENTAR'), 'flotanteDerecha margenInferior');
            $bloqueComentarios = HTML::forma(HTML::urlInterna('INICIO', '', true, 'addComment'), $bloqueComentarios);
        } else {
            $bloqueComentarios = HTML::parrafo($textos->id('ERROR_COMENTARIO_SESION'), 'margenInferior');
        }

        $comentarios = new Comentario();
        $listaComentarios = array();

        if ($comentarios->contar($modulo, $registro)) {

            foreach ($comentarios->listar($modulo, $registro) as $comentario) {

                $botonEliminar = '';
                if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $comentario->idAutor) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) || $modulo == 'CENTROS' && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 2 && $sesion_usuarioSesion->idCentro == $registro) {
                    $botonEliminar = HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('INICIO', '', true, 'deleteComment'), array('id' => $comentario->id));
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista', 'botonesLista');
                }
                $contenidoComentario = '';
                $contenidoComentario .= $botonEliminar;
                $contenidoComentario .= HTML::enlace(HTML::imagen($comentario->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), HTML::urlInterna('USUARIOS', $comentario->usuarioAutor));
                $contenidoComentario .= HTML::parrafo(HTML::enlace($comentario->autor, HTML::urlInterna('USUARIOS', $comentario->usuarioAutor)) . $textos->id('USUARIO_DIJO'), 'negrilla margenInferior');
                $contenidoComentario .= HTML::parrafo(nl2br($comentario->contenido));
                $contenidoComentario .= HTML::parrafo(date('D, d M Y h:i:s A', $comentario->fecha), 'pequenia cursiva negrilla margenSuperior margenInferior');

                $listaComentarios[] = HTML::contenedor($contenidoComentario, 'contenedorListaComentarios', 'contenedorComentario' . $comentario->id);
            }
        } else {
            $listaComentarios[] = HTML::frase($textos->id('SIN_COMENTARIOS'), 'margenInferior', 'sinRegistros');
        }

        $bloqueComentarios .= HTML::lista($listaComentarios, 'listaVertical listaConIconos bordeSuperiorLista', 'botonesOcultos', 'listaComentarios');

        return $bloqueComentarios;
    }

    /**
     *
     * Metodo que se encarga de armar el bloque donde se muestran los foros 
     * pertenecientes a un determinado item de un determinado modulo
     *
     * @global type $sql
     * @global type $textos
     * @global type $sesion_usuarioSesion
     * @global type $configuracion
     * @param type $modulo
     * @param type $registro
     * @param type $propietario
     * @return type 
     */
    static function bloqueForos($modulo, $registro, $propietario) {
        global $sql, $textos, $sesion_usuarioSesion, $configuracion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }

        if (isset($sesion_usuarioSesion)) {
            $moduloActual = new Modulo($modulo);

            if (Perfil::verificarPermisosAdicion(23) || $sesion_usuarioSesion->idTipo == 0 || $modulo == 'CENTROS' && isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 2 && $sesion_usuarioSesion->idCentro == $registro) {
                $bloqueForos = HTML::campoOculto('idModulo', $moduloActual->id);
                $bloqueForos .= HTML::campoOculto('idRegistro', $registro);
                $bloqueForos .= HTML::boton('comentario', $textos->id('INICIAR_TEMA_FORO'), 'flotanteDerecha margenInferior');
                $bloqueForos = HTML::forma(HTML::urlInterna('FOROS', '', true, 'addTopic'), $bloqueForos);
            }
        } else {
            $bloqueForos = HTML::parrafo($textos->id('ERROR_FORO_SESION'), 'margenInferior');
        }


        $foros = new Foro();
        $listaForos = array();
        $botonEliminar = '';

        if ($foros->contar($modulo, $registro)) {

            foreach ($foros->listar($modulo, $registro) as $foro) {

                if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario) || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $foro->idAutor) {
                    $botonEliminar = HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('FOROS', '', true, 'deleteRegister'), array('id' => $foro->id));
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista flotanteDerecha', 'botonesLista');
                }

                $contenidoForo = $botonEliminar;
                //seleccionar el genero de una persona 
                $persona = new Persona($foro->idAutor);
                $contenidoForo .= HTML::enlace(HTML::imagen($foro->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $foro->url);
                $contenidoForo .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $persona->idGenero . '.png') . str_replace('%1', HTML::enlace($foro->autor, HTML::urlInterna('USUARIOS', $foro->usuarioAutor)), $textos->id('PUBLICADO_POR')));
                $contenidoForo2 = HTML::enlace(HTML::parrafo($foro->titulo, 'negrilla'), $foro->url);
                $contenidoForo2 .= HTML::parrafo(date('D, d M Y h:i:s A', $foro->fecha), 'pequenia cursiva negrilla');
                $contenidoForo2 .= HTML::parrafo('Responses: ' . $foros->contarMensajes($foro->id), 'cursiva negrilla flotanteDerecha');
                $contenidoForo .= HTML::contenedor($contenidoForo2, 'fondoUltimos5GrisB'); //barra del contenedor gris


                $listaForos[] = HTML::contenedor($contenidoForo, 'contenedorListaForos', 'contenedorForo' . $foro->id);
            }
        } else {
            $listaForos[] = HTML::frase($textos->id('SIN_TEMAS'), 'margenInferior');
        }


        $bloqueForos .= HTML::lista($listaForos, 'listaVertical listaConIconos bordeSuperiorLista', 'botonesOcultos', 'listaForos');

        return $bloqueForos;
    }
  
    
   /**
    * Devuelve el bloque de videos dependiendo del modulo de donde se llame
    * @global type $sql
    * @global type $textos
    * @global type $configuracion
    * @global type $sesion_usuarioSesion
    * @param type $modulo
    * @param type $registro
    * @param type $propietario
    * @return null 
    */ 
    static function bloqueVideos($modulo, $registro, $propietario, $tienePermisos = NULL) {
        global $sql, $textos, $configuracion, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem("modulos", "nombre", $modulo)) {
            return NULL;
        }

        $moduloActual = new Modulo($modulo);
        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || $tienePermisos) {
            
            $bloqueVideos  = HTML::campoOculto("idModulo", $moduloActual->id);
            $bloqueVideos .= HTML::campoOculto("idRegistro", $registro);
            $bloqueVideos .= HTML::boton("video", $textos->id("ADICIONAR_VIDEO"), "flotanteDerecha margenInferior");
            $bloqueVideos  = HTML::forma(HTML::urlInterna("INICIO", "", true, "addVideo"), $bloqueVideos);

        } elseif (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id != $propietario) {
            $bloqueVideos = HTML::parrafo($textos->id("ERROR_VIDEO_PROPIETARIO"), "margenInferior");

        } else {
            $bloqueVideos = HTML::parrafo($textos->id("ERROR_VIDEO_SESION"), "margenInferior");
        }

        $videos         = new Video();
        $listaVideos    = array();
        $listaVideos2   = array();
        $botonEliminar  = "";
        
        $cantidadVideos = $videos->contar($modulo, $registro);

        if ($cantidadVideos) { 
              $contador   = 0; // contador que determina si en el listado hay videos de youtube
              $contador1  = 0; // contador que determina si en el listado hay videos subidos al servidor

		$comentario  = new Comentario();
              
                foreach ($videos->listar(0, 0, $modulo, $registro) as $video) {//recorro el listado de videos

		    
		    $comentarios = $comentario->contar("VIDEOS", $video->id);
		    if(!$comentarios){
		      $comentarios = ' 0';
		    }
                    if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $video->idAutor) {// codigo para crear el boton para eliminar un video
                        $botonEliminar = HTML::botonAjax("basura", "x", HTML::urlInterna("INICIO", "", true, "deleteNewVideo"), array("id" => $video->id));
                        $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, "contenedorBotonesLista", "contenedorBotonesLista"), "botonesLista flotanteDerecha margenInferior", "botonesLista");
                    }

		    $contenedorComentarios = HTML::contenedor($comentarios, "contenedorBotonComentariosItems botonComentariosItemsGris", "contenedorBotonComentariosItems", array("ruta" => "/ajax/users/comment", "modulo" => "VIDEOS", "registro" => $video->id, "propietarioItem" => $video->idAutor, "ayuda" => $textos->id("HACER_COMENTARIO")));
		    $contenedorComentarios = HTML::contenedor($contenedorComentarios, "contenedorComentariosListaVideos");


                    $contenidoVideo  = "";//declaro la variable que almacenara el contenido de videos de youtube
                    $contenidoVideo2 = "";//declaro la variable que almacenara el contenido de videos subidos al servidor
                                        
                    if ($video->enlace != "--") {//determina de que si es un video de youtube                    

                        if (preg_match("/\byoutube\b/i", $video->enlace)) {
                            //Aqui entraria toda la validacion de si viene de youtube
                            $codigo = explode("=", $video->enlace);
                            $codigo = explode("&", $codigo[1]);  

                            if (!preg_match("/http/i", $video->enlace)) {
                                $video->enlace = "http://".$video->enlace;
                            }

                            $descripcion = HTML::campoOculto("descripcion", $video->descripcion."<p><span class='negrilla margenSuperior'>".$textos->id("ENLACE").": </span></p>"."<a href='".$video->enlace."' rel='prettyPhoto[]'>$video->enlace</a>", "descripcion");
                            $contenidoVideo .= $descripcion.$botonEliminar.$contenedorComentarios.HTML::enlace($video->titulo."¬".$video->descripcion, $video->enlace, "enlaceVideo", "");

                        }           
                        $contador ++;
                    } else {//determina de que si es un video de los de ablaonline v1, subidos al servidor
                        $reproductor = $configuracion["SERVIDOR"]["media"].$configuracion["RUTAS"]["reproductor"]."?file=";
                        $contenidoVideo2 .= HTML::enlace("", $reproductor.$video->ruta, "recursoVideo");
                        $contador1 ++;
                    }

                    $listaVideos[]   .= $contenidoVideo;
                    $listaVideos2[]  .= $botonEliminar.$contenidoVideo2;
                }
                
                if($contador > 0){
                    $contenedor    = HTML::contenedor("", "", "ytvideo").HTML::contenedor("", "", "descripcionVideo");
                    $listaVideos   = HTML::lista($listaVideos, "listaVideos listaVertical bordeSuperiorLista", "botonesOcultos", "listaVideos");
                    $contenedorSuperior = HTML::contenedor($contenedor.$listaVideos, "yt_holder", "");
                    $bloqueVideos .= $contenedorSuperior;
                }
                
                if($contador1 > 0){
                    $listaVideos2  = HTML::lista($listaVideos2, "listaVideos2 listaVertical bordeSuperiorLista", "botonesOcultos", "listaVideos2");
                    $bloqueVideos .= $listaVideos2;
                }

        } else {
            $listaVideos[] = HTML::frase($textos->id("SIN_VIDEOS"), "margenInferior");
            $listaVideos   = HTML::lista($listaVideos, "listaVideos listaVertical bordeSuperiorLista", "botonesOcultos", "listaVideos");           
            $bloqueVideos .= $listaVideos;
        }
        
        return $bloqueVideos;
    }


    /**
     * Funcion que permite mostrar un listado con los audios pertenecientes
     * a determinado item en determinado modulo
     * @global type $sql
     * @global type $textos
     * @global type $configuracion
     * @global type $sesion_usuarioSesion
     * @param type $modulo
     * @param type $registro
     * @param type $propietario
     * @return type 
     */
    static function bloqueAudios($modulo, $registro, $propietario, $tienePermisos = NULL) {
        global $sql, $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }

        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || $tienePermisos) {
            $moduloActual = new Modulo($modulo);
            $bloqueAudios = HTML::campoOculto('idModulo', $moduloActual->id);
            $bloqueAudios .= HTML::campoOculto('idRegistro', $registro);
            $bloqueAudios .= HTML::boton('conVolumen', $textos->id('ADICIONAR_AUDIO'), 'flotanteDerecha margenInferior');
            $bloqueAudios = HTML::forma(HTML::urlInterna('AUDIOS', '', true, 'addAudio'), $bloqueAudios);
        } elseif (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id != $propietario) {
            $bloqueAudios = HTML::parrafo($textos->id('ERROR_AUDIO_PROPIETARIO'), 'margenInferior');
        } else {
            $bloqueAudios = HTML::parrafo($textos->id('ERROR_AUDIO_SESION'), 'margenInferior');
        }

        $audios = new Audio();
        $listaAudios = array();
        $botonEliminar = '';

        $cantidadAudios = $audios->contar($modulo, $registro);

        if ($cantidadAudios) {

            $comentario = new Comentario();
            foreach ($audios->listar(0, 0, $modulo, $registro) as $audio) {

                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $audio->idAutor) {
                    $botonEliminar = 'verdadero'; //se manda esta palabra al javascript a ver si se muestra el botonsito de eliminar
                }

                $comentarios = $comentario->contar('AUDIOS', $audio->id);
                if (!$comentarios) {
                    $comentarios = ' 0';
                }

                $arreglo = $audio->enlace . '¬' . $audio->titulo . '¬' . $audio->id . '¬' . $botonEliminar . '¬' . $propietario . '¬' . $comentarios;

                $listaAudios[] .= $arreglo;
            }

            $listaAudios = implode('|', $listaAudios);
            $bloqueAudios .= HTML::campoOculto('audios', $listaAudios, 'listadoAudios');
            $bloqueAudios .= HTML::armarReproductorAudio();
        } else {
            $listaAudios[] = HTML::frase($textos->id('SIN_AUDIOS'), 'margenInferior');
            $listaAudios = HTML::lista($listaAudios, 'listaAudios listaVertical bordeSuperiorLista', 'botonesOcultos', 'listaAudios');
            $bloqueAudios .= HTML::campoOculto('audios', '', 'listadoAudios'); //para que el javascript que busca la lista de audios siempre encuentre el campo oculto y no de error
            $bloqueAudios .= $listaAudios;
        }

        return $bloqueAudios;
    }

    /**
     * Funcion que arma y devuelve un bloque de codigo HTML con el listado
     * de documentos que pertenecen a un determinado item de un determinado modulo
     * @global type $sql
     * @global type $textos
     * @global type $configuracion
     * @global type $sesion_usuarioSesion
     * @param type $modulo
     * @param type $registro
     * @param type $propietario
     * @return type bloque de codigo html con el listado de documentos
     */
    static function bloqueArchivos($modulo, $registro, $propietario, $tienePermisos = NULL) {
        global $sql, $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }
        $moduloActual = new Modulo($modulo);
        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || $tienePermisos) {

            $bloqueArchivos = HTML::campoOculto('idModulo', $moduloActual->id);
            $bloqueArchivos .= HTML::campoOculto('idRegistro', $registro);
            $bloqueArchivos .= HTML::boton('documentoNuevo', $textos->id('ADICIONAR_ARCHIVO'), 'flotanteDerecha margenInferior');
            $bloqueArchivos = HTML::forma(HTML::urlInterna('DOCUMENTOS', '', true, 'addDocument'), $bloqueArchivos);
        } elseif (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id != $propietario) {
            $bloqueArchivos = HTML::parrafo($textos->id('ERROR_ARCHIVO_PROPIETARIO'), 'margenInferior');
        } else {
            $bloqueArchivos = HTML::parrafo($textos->id('ERROR_ARCHIVO_SESION'), 'margenInferior');
        }


        $archivos = new Documento();
        $listaArchivos = array();
        $botonEliminar = '';
        //$usuarioActual    = new Usuario($propietario);

        $cantidadArchivos = $archivos->contar($modulo, $registro);

        if ($cantidadArchivos) {

            $comentario = new Comentario();

            foreach ($archivos->listar(0, 4, $modulo, $registro) as $archivo) {

                $comentarios = $comentario->contar('DOCUMENTOS', $archivo->id);
                if (!$comentarios) {
                    $comentarios = ' 0';
                }

                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $archivo->idAutor) {
                    $botonEliminar = HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('DOCUMENTOS', '', true, 'deleteDocument'), array('id' => $archivo->id));
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista flotanteDerecha', 'botonesLista');
                }

                $contenedorComentarios = HTML::contenedor($comentarios, 'contenedorBotonComentariosItems botonComentariosItemsGris', 'contenedorBotonComentariosItems', array('ruta' => '/ajax/users/comment', 'modulo' => 'DOCUMENTOS', 'registro' => $archivo->id, 'propietarioItem' => $archivo->idAutor, 'ayuda' => $textos->id('HACER_COMENTARIO')));
                $contenedorComentarios = HTML::contenedor($contenedorComentarios, 'contenedorComentariosListaDocumentos');

                $contenidoArchivo = $botonEliminar . $contenedorComentarios;
                $contenidoArchivo .= HTML::enlace(HTML::imagen($archivo->icono, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $archivo->enlace);
                $contenidoArchivo .= HTML::parrafo(HTML::enlace($archivo->titulo, $archivo->enlace));
                $contenidoArchivo2 = HTML::parrafo($archivo->descripcion);
                $contenidoArchivo2 .= HTML::parrafo(HTML::frase($textos->id('ENLACE') . ': ', 'negrilla') . $archivo->enlace, 'margenSuperior');
                $contenidoArchivo .= HTML::contenedor($contenidoArchivo2, 'contenedorGrisLargo');

                $listaArchivos[] = HTML::contenedor($contenidoArchivo, 'contenedorListaDocumentos', 'contenedorDocumento' . $archivo->id);
            }//fin del foreach

            if ($cantidadArchivos >= 4) {
                $listaArchivos[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('DOCUMENTOS', $moduloActual->url . '/' . $registro), 'flotanteCentro margenSuperior') . '</BR></BR>';
            }
        } else {
            $listaArchivos[] = HTML::frase(HTML::parrafo($textos->id('SIN_ARCHIVOS'), 'sinRegistros', 'sinRegistros'), 'margenInferior');
        }


        $bloqueArchivos .= HTML::lista($listaArchivos, 'listaVertical bordeSuperiorLista', '', 'listaDocumentos');

        return $bloqueArchivos;
    }

    static function bloqueImagenes($modulo, $registro, $propietario, $tienePermisos = NULL) {
        global $sql, $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }

        $moduloActual = new Modulo($modulo);

        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || $tienePermisos) {

            $bloqueImagens = HTML::campoOculto('idModulo', $moduloActual->id);
            $bloqueImagens = HTML::campoOculto('modulo', $moduloActual->nombre);
            $bloqueImagens .= HTML::campoOculto('idRegistro', $registro);
            $bloqueImagens .= HTML::boton('imagen', $textos->id('ADICIONAR_IMAGEN'), 'flotanteDerecha margenInferior');
            $bloqueImagens = HTML::forma(HTML::urlInterna('INICIO', '', true, 'addImage'), $bloqueImagens);
            //$bloqueImagens  = HTML::forma(HTML::urlInterna('INICIO', '', true, 'callScript'), $bloqueImagens);
            // Recursos::escribirTxt('id_modulo::::: '.$modulo, 5);
        } elseif (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id != $propietario) {
            $bloqueImagens = HTML::parrafo($textos->id('ERROR_IMAGEN_PROPIETARIO'), 'margenInferior');
        } else {
            $bloqueImagens = HTML::parrafo($textos->id('ERROR_IMAGEN_SESION'), 'margenInferior');
        }


        $imagenes = new Imagen();
        $listaImagens = array();
        $botonEliminar = '';
        $usuarioActual = new Usuario($propietario);
        $imagenUsuario = 0;
        if (isset($usuarioActual->persona)) {
            $imagenUsuario = $usuarioActual->persona->idImagen;
        }

        $arregloImagenes = $imagenes->listar(0, 6, $modulo, $registro);

        if (sizeof($arregloImagenes) > 0) {

            $comentario = new Comentario();

            foreach ($arregloImagenes as $imagen) {

                $comentarios = $comentario->contar('IMAGENES', $imagen->id);
                if (!$comentarios) {
                    $comentarios = ' 0';
                }

                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $imagen->idAutor) {
                    $botonEliminar = HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('INICIO', '', true, 'deleteImage'), array('id' => $imagen->id));
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista flotanteDerecha', 'botonesLista');
                }

                $contenedorComentarios = HTML::contenedor($comentarios, 'contenedorBotonComentariosItems botonComentariosItemsGris', 'contenedorBotonComentariosItems', array('ruta' => '/ajax/users/comment', 'modulo' => 'IMAGENES', 'registro' => $imagen->id, 'propietarioItem' => $imagen->idAutor, 'ayuda' => $textos->id('HACER_COMENTARIO')));
                $contenedorComentarios = HTML::contenedor($contenedorComentarios, 'contenedorComentariosListaImagenes');

                if (($imagenUsuario != $imagen->id) && ($imagen->id != 0)) {
                    $contenidoImagen = $botonEliminar . $contenedorComentarios;
                    $img = HTML::imagen($imagen->miniatura, 'listaImagenes recursoImagen', '', array('title' => $imagen->titulo));
                    $contenidoImagen .= HTML::enlace($img, $imagen->ruta, '', '', array('rel' => 'prettyPhoto[' . $sesion_usuarioSesion->id . ' ]'));
                    if ($imagen->titulo) {
                        $contenidoImagen .= HTML::parrafo($imagen->titulo, 'negrilla');
                    } else {
                        $contenidoImagen .= HTML::parrafo('No title', 'negrilla');
                    }
                    if ($imagen->descripcion) {
                        $contenidoImagen .= HTML::parrafo($imagen->descripcion);
                    } else {
                        $contenidoImagen .= HTML::parrafo('No description');
                    }
                    $contenidoImagen .= HTML::parrafo(HTML::frase($textos->id('ENLACE') . ': ', 'negrilla') . $imagen->ruta, 'margenSuperior');
                    $contenidoImagen = HTML::contenedor($contenidoImagen, 'contenedorImagen', 'contenedorImagen' . $imagen->id);
                    $listaImagens[] .= $contenidoImagen;
                }//fin de si el usuario va a ver su foto de perfil en el listado
            }
            if (sizeof($listaImagens) >= 4) {
                $listaImagens[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('IMAGENES', $moduloActual->url . '/' . $registro), 'flotanteCentro margenSuperior') . '</BR></BR>';
            }
        } else {
            $listaImagens[] = HTML::frase($textos->id('SIN_IMAGENES'), 'margenInferior');
        }


        $bloqueImagens .= HTML::lista($listaImagens, 'listaVertical listaConImagenes bordeSuperiorLista', '', 'listaImagenes');

        return $bloqueImagens;
    }

    /**
     *
     * @global type $sql
     * @global type $textos
     * @global type $sesion_usuarioSesion
     * @param type $modulo
     * @param type $registro
     * @param type $propietario
     * @param type $tienePermisos
     * @return null 
     */
    static function bloqueGalerias($modulo, $registro, $propietario, $tienePermisos = NULL) {
        global $sql, $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }

        $moduloActual = new Modulo($modulo);

        if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || $tienePermisos) {

            $bloqueGalerias = HTML::campoOculto('idModulo', $moduloActual->id);
            $bloqueGalerias .= HTML::campoOculto('idRegistro', $registro);
            $bloqueGalerias .= HTML::boton('imagen', $textos->id('AGREGAR_GALERIA'), 'flotanteDerecha margenInferior');
            $bloqueGalerias = HTML::forma(HTML::urlInterna('GALERIAS', '', true, 'add'), $bloqueGalerias);
            //$bloqueGalerias  = HTML::forma(HTML::urlInterna('INICIO', '', true, 'callScript'), $bloqueGalerias);
            // Recursos::escribirTxt('id_modulo::::: '.$modulo, 5);
        } elseif (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id != $propietario) {
            $bloqueGalerias = HTML::parrafo($textos->id('ERROR_IMAGEN_PROPIETARIO'), 'margenInferior');
        } else {
            $bloqueGalerias = HTML::parrafo($textos->id('ERROR_IMAGEN_SESION'), 'margenInferior');
        }


        $galerias = new Galeria();
        $listaGalleries = array();
        $botonEliminar = '';


        $arregloGalerias = $galerias->listar(0, 4, $modulo, $registro);

        if (sizeof($arregloGalerias) > 0) {

            $comentario = new Comentario();

            foreach ($arregloGalerias as $galeria) {

                $comentarios = $comentario->contar('GALERIAS', $galeria->id);
                if (!$comentarios) {
                    $comentarios = ' 0';
                }

                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $propietario || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $galeria->idAutor) {
                    $botonEliminar = HTML::contenedor(HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('GALERIAS', '', true, 'delete'), array('id' => $galeria->id)), 'alineadoDerecha');
                    $botonEliminar .= HTML::contenedor(HTML::botonAjax('lapiz', 'MODIFICAR', HTML::urlInterna('GALERIAS', '', true, 'edit'), array('id' => $galeria->id)), 'alineadoDerecha');
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista flotanteDerecha', 'botonesLista');
                }

                $contenedorComentarios = HTML::contenedor($comentarios, 'contenedorBotonComentariosItems botonComentariosItemsGris', 'contenedorBotonComentariosItems', array('ruta' => '/ajax/users/comment', 'modulo' => 'GALERIAS', 'registro' => $galeria->id, 'propietarioItem' => $galeria->idAutor, 'ayuda' => $textos->id('HACER_COMENTARIO')));
                $contenedorComentarios = HTML::contenedor($contenedorComentarios, 'contenedorComentariosListaImagenes');


                $contenidoGalerias = $botonEliminar . $contenedorComentarios;

                $contenidoGalerias .= HTML::imagen($galeria->icono, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5 estiloEnlace enlaceGaleria', $galeria->id);
                $contenidoGalerias .= HTML::parrafo($galeria->titulo, 'estiloEnlace enlaceGaleria', $galeria->id);
                $contenidoGalerias2 = HTML::parrafo($galeria->descripcion);
                $contenidoGalerias .= HTML::contenedor($contenidoGalerias2, 'fondoUltimos5Gris');

                $contenidoGalerias = HTML::contenedor($contenidoGalerias, 'contenedorListaGalerias', 'contenedorGaleria' . $galeria->id);
                $listaGalleries[] .= $contenidoGalerias;
            }
        } else {
            $listaGalleries[] = HTML::frase($textos->id('SIN_GALERIAS'), 'margenInferior');
        }


        $bloqueGalerias .= HTML::lista($listaGalleries, 'listaVertical listaConGalerias bordeSuperiorLista', '', 'listaGalerias');

        return $bloqueGalerias;
    }

    /**
     * Funcion que arma y devuelve un bloque de codigo HTML con el listado
     * de enlaces que pertenecen a un determinado item de un determinado modulo
     * @global type $sql
     * @global type $textos
     * @global type $configuracion
     * @global type $sesion_usuarioSesion
     * @param type $modulo
     * @param type $registro
     * @param type $propietario
     * @return type bloque de codigo html con el listado de documentos
     */
    static function bloqueEnlaces($modulo, $registro, $propietario, $tienePermisos = NULL) {
        global $sql, $textos, $sesion_usuarioSesion;

        if (!isset($modulo) && !isset($registro) && !$sql->existeItem('modulos', 'nombre', $modulo)) {
            return NULL;
        }

        $moduloActual = new Modulo($modulo);
        $enlaces = new Enlace();
        $bloqueEnlaces = '';
        //Recursos::escribirTxt('permiso: '.Perfil::verificarPermisosAdicion($enlaces->idModulo));

        if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) || (isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicion($enlaces->idModulo) && $sesion_usuarioSesion->id == $propietario ) || $tienePermisos) {

            $bloqueEnlaces = HTML::campoOculto('idModulo', $moduloActual->id);
            $bloqueEnlaces .= HTML::campoOculto('idRegistro', $registro);
            $bloqueEnlaces .= HTML::boton('enlaceNuevo', $textos->id('ADICIONAR_ENLACE'), 'flotanteDerecha margenInferior');
            $bloqueEnlaces = HTML::forma(HTML::urlInterna('ENLACES', '', true, 'addLink'), $bloqueEnlaces);
        }



        $listaEnlaces = array();
        $botonEliminar = '';
        //$usuarioActual   = new Usuario($propietario);

        $cantidadEnlaces = $enlaces->listar(0, 4, $modulo, $registro);
        $cantidadEnlaces = sizeof($cantidadEnlaces);

        if ($cantidadEnlaces) {

            foreach ($enlaces->listar(0, 4, $modulo, $registro) as $enlace) {

                if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $enlace->idAutor)) {
                    $botonEliminar = HTML::botonAjax('basura', 'ELIMINAR', HTML::urlInterna('ENLACES', '', true, 'deleteLink'), array('id' => $enlace->id));
                    $botonEliminar = HTML::contenedor(HTML::contenedor($botonEliminar, 'contenedorBotonesLista', 'contenedorBotonesLista'), 'botonesLista flotanteDerecha', 'botonesLista');
                }
                $contenidoEnlace = $botonEliminar;
                if (!preg_match('/http/i', $enlace->enlace)) {
                    $enlace->enlace = 'http://' . $enlace->enlace;
                }
                $contenidoEnlace .= HTML::enlace(HTML::imagen($enlace->icono, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $enlace->enlace);
                $contenidoEnlace .= HTML::parrafo(HTML::enlace($enlace->titulo, $enlace->enlace));
                $contenidoEnlace2 = HTML::parrafo($enlace->descripcion);
                $contenidoEnlace2 .= HTML::parrafo(HTML::frase($textos->id('ENLACE') . ': ', 'negrilla') . $enlace->enlace, 'margenSuperior');
                $contenidoEnlace .= HTML::contenedor($contenidoEnlace2, 'contenedorGrisLargo');

                $listaEnlaces[] = HTML::contenedor($contenidoEnlace, 'contenedorListaDocumentos', 'contenedorEnlace' . $enlace->id);
            }//fin del foreach

            if ($cantidadEnlaces >= 4) {
                $listaEnlaces[] .= HTML::enlace($textos->id('VER_MAS') . HTML::icono('circuloFlechaDerecha'), HTML::urlInterna('ENLACES', $moduloActual->url . '/' . $registro), 'flotanteCentro margenSuperior') . '</BR></BR>';
            }
        } else {
            $listaEnlaces[] = HTML::frase(HTML::parrafo($textos->id('NO_HAY_ENLACES_REGISTRADOS'), 'sinRegistros', 'sinRegistros'), 'margenInferior');
        }


        $bloqueEnlaces .= HTML::lista($listaEnlaces, 'listaVertical bordeSuperiorLista', 'botonesOcultos', 'listaEnlaces');

        return $bloqueEnlaces;
    }
    

    /**
     * Metodo estatico que se encarga de mostrar cuantos registros totales existen en la consulta
     * y tambien muestra al usuario cuantos registros de cuantos esta viendo. Recibe  parametros
     * de tipo numerico
     * */
    public static function contarPaginacion($totalRegistros, $registroInicial, $registroPorPagina, $pagina, $totalPaginas) {
        global $textos;

        $registroMaximo = $registroInicial + $registroPorPagina;

        if ($pagina == $totalPaginas) {

            //codigo para reemplazar los valores que aparecen con el %1 con la variable que se le pasa, y en el texto que se le pasa.
            $texto = str_replace('%1', ($registroInicial + 1), $textos->id('PAGINACION'));
            $texto = str_replace('%2', $totalRegistros, $texto);
            $texto = str_replace('%3', $totalRegistros, $texto);
        } else {

            $texto = str_replace('%1', ($registroInicial + 1), $textos->id('PAGINACION'));
            $texto = str_replace('%2', $registroMaximo, $texto);
            $texto = str_replace('%3', $totalRegistros, $texto);
        }//fin del if


        $response = HTML::parrafo($texto, 'negrita');
        return $response;
    }

    /**
     * Escribir errores en un archivo txt
     * */
  public static function escribirTxt($texto){

	$fecha = date("d/m/y H:i:s");
	$fp = fopen("errores.txt","a");
	fwrite($fp, "Fecha: $fecha -> \n Variable: $texto  " .PHP_EOL);
	fclose($fp);
  }

    /**
     * Validar archivo
     * */
    public function validarArchivo($archivo, $extensiones) {
        if (!empty($archivo['name'])) {
            $existe = true;
            $extension_archivo = strtolower(substr($archivo['name'], (strrpos($archivo['name'], '.') - strlen($archivo['name'])) + 1));
            if (!empty($extensiones) && is_array($extensiones)) {
                foreach ($extensiones as $extension) {
                    if ($extension_archivo == $extension) {
                        $existe = false;
                    }
                }
            }
            return $existe;
        } else {
            return true;
        }
    }

    /**
     * Metodo para cargar los formularios ('estrellitas')'me Gusta' una vez la persona ha iniciado la sesion
     * */
    public static function cargarMegusta($idModulo, $idItem, $idUsuario) {
        $des = new Destacado();
        $cod = '';
        $datos = array(
            'id_modulo' => $idModulo,
            'id_item' => $idItem,
            'id_usuario' => $idUsuario
        );

        $opciones = array(
            'onMouseOver' => '$("#ayuda").show("drop", {}, 300)',
            'onMouseOut' => '$("#ayuda").hide("drop", {}, 300)',
            );


        $cantidad = $des->cantidadDestacados($idModulo, $idItem); //saber la cantidad de destacados del item
        $meGusta = $des->meGusta($idModulo, $idItem, $idUsuario); //saber si a mi me gusta el item


        if ($cantidad > 0) {
            if ($meGusta > 0) {
                $txt = '';

                if ($cantidad > 1 && $cantidad != 2) {
                    $txt .= ' and ' . ($cantidad - 1) . ' people';
                }
                if ($cantidad > 1 && $cantidad == 2) {
                    $txt .= ' and ' . ($cantidad - 1) . ' person';
                }

                $url = HTML::urlInterna('DESTACADOS', '', true, 'delHighLight');
                $boton = HTML::botonImagenAjax('', 'estrellaAzul', 'iLikeIt', $opciones, $url, $datos, 'formaMeGusta');
                $frase = HTML::frase('You' . $txt . ' like this', 'cantidadDestacados');
                $ayuda = HTML::contenedor('Click on the Star if you Don\'t Like', 'ayudaMeGusta', 'ayuda', array('style' => 'display: none'));

                $cod .= HTML::contenedor($ayuda . HTML::contenedor($frase . $boton, ''), 'meGustaInterno', 'meGustaInterno');
            } else {
                $txt = '';
                if ($cantidad == 1) {
                    $txt .= ' person';
                } else {
                    $txt .= ' people';
                }

                $url = HTML::urlInterna('DESTACADOS', '', true, 'addHighLight');
                $boton = HTML::botonImagenAjax('', 'estrellaGris', 'iLikeIt', $opciones, $url, $datos, 'formaMeGusta');
                $frase = HTML::frase($cantidad . $txt . ' like this... Do you Like?', 'cantidadDestacados');
                $ayuda = HTML::contenedor('Click on the Star if you Like', 'ayudaMeGusta', 'ayuda', array('style' => 'display: none'));

                $cod .= HTML::contenedor($ayuda . HTML::contenedor($frase . $boton, ''), 'meGustaInterno', 'meGustaInterno');
            }
        } else {
            $cod .= HTML::contenedor(HTML::frase('Do You Like This?', 'cantidadDestacados') . HTML::botonImagenAjax('', 'estrellaGris', 'iLikeIt', $opciones, HTML::urlInterna('DESTACADOS', '', true, 'addHighLight'), $datos, 'formaMeGusta') . HTML::frase('Click on the Star if you Like', 'ayuda', 'ayuda', array('style' => 'display: none')), 'meGustaInterno', 'meGustaInterno');
        }//fin del if    


        return $cod;
    }

//fin del metodo cargarMegusta

    /**
     * Metodo para mostrar los 'me Gusta' si no ha iniciado sesion
     *
     * */
    public static function mostrarMegusta($idModulo, $idItem) {

        $des = new Destacado();
        $cod = '';

        $opciones = array(
            'onMouseOver' => '$("#ayuda").show("drop", {}, 300)',
            'onMouseOut' => '$("#ayuda").hide("drop", {}, 300)');

        $cantidad = $des->cantidadDestacados($idModulo, $idItem);


        if ($cantidad > 0) {
            $txt = '';
            if ($cantidad == 1) {
                $txt .= ' person';
            } else {
                $txt .= ' people';
            }

            $boton = HTML::contenedor('', 'estrellaAzul', '', $opciones);
            $frase = HTML::frase($cantidad . $txt . ' like this', 'cantidadDestacados');
            $ayuda = HTML::frase('You must  be logged in to rate', 'ayuda', 'ayuda', array('style' => 'display: none'));

            $cod .= HTML::contenedor($frase . $boton . $ayuda, 'meGustaInterno', 'meGustaInterno');
        } else {
            $ayuda = HTML::frase('You must to be logged in to rate', 'ayuda', 'ayuda', array('style' => 'display: none'));
            $boton = HTML::contenedor('', 'estrellaGris', '', $opciones);
            $frase = HTML::frase('Do You Like This?', 'cantidadDestacados');
            $cod .= HTML::contenedor($frase . $boton . $ayuda, 'meGustaInterno', 'meGustaInterno');
        }//fin del if    


        return $cod;
    }


    /**
     * Metodo para contar los 'me Gusta' que tiene un determinado Item
     * */
    public function contarMeGusta($idModulo, $idItem) {
        global $sql;

        $tablas = array(
            'd' => 'destacados'
        );

        $columnas = array(
            'registros' => 'COUNT(d.id_modulo)'
        );

        $condicion = 'd.id_modulo = "'.$idModulo.'" AND d.id_item = "'.$idItem.'" ';
        //$sql->depurar = true;
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

        if ($sql->filasDevueltas) {
            $meGusta = $sql->filaEnObjeto($consulta);
            return $meGusta->registros;
        } else {
            return NULL;
        }
    }

    /**
     * Metodo que muestra los 'me Gusta' que tiene un determinado Item el el contenedor de la página principal
     * junto a los primeros 5 items
     * */
    public static function mostrarContadorMeGusta($idModulo, $idItem) {
        global $configuracion;

        $cantidad = self::contarMeGusta($idModulo, $idItem);

        if ($cantidad <= 0) {
            $codigo = HTML::contenedor(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'awardOff.png', 'imgCommPosted') . HTML::contenedor(' 0', 'mostrarDivNums'), 'mostrarPostedInf');
        } else {
            $codigo = HTML::contenedor(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'awardOn.png', 'imgCommPosted') . HTML::contenedor($cantidad, 'mostrarDivNums'), 'mostrarPostedInf');
        }

        return $codigo;
    }


    /**
     * @global type $textos
     * @param type $totalRegistrosActivos   = total de registros activos que tiene determinado modulo
     * @param type $registroInicial         = Registro inicial desde el cual debe empezar la consulta         
     * @param type $registros               = Total de registros que se deben mostrar por página, este dato se toma desde el archivo de configuracion
     * @param type $pagina                  = Pagina actual en la que se debe empezar la consulta
     * @return type 
     */
    public static function mostrarPaginador($totalRegistrosActivos, $registroInicial, $registros, $pagina/* , $totalPaginas */) {
        global $textos;
        $botonPrimera = $botonUltima = $botonAnterior = $botonSiguiente = $infoPaginacion = '';

        if ($totalRegistrosActivos > $registros) {
            $totalPaginas = ceil($totalRegistrosActivos / $registros);

            if ($pagina > 1) {
                $botonPrimera = HTML::campoOculto('pagina', 1);
                $botonPrimera .= HTML::boton('primero', $textos->id('PRIMERA_PAGINA'), 'directo');
                $botonPrimera = HTML::forma('', $botonPrimera);
                $botonAnterior = HTML::campoOculto('pagina', $pagina - 1);
                $botonAnterior .= HTML::boton('anterior', $textos->id('PAGINA_ANTERIOR'), 'directo');
                $botonAnterior = HTML::forma('', $botonAnterior);
            }

            if ($pagina < $totalPaginas) {
                $botonSiguiente = HTML::campoOculto('pagina', $pagina + 1);
                $botonSiguiente .= HTML::boton('siguiente', $textos->id('PAGINA_SIGUIENTE'), 'directo');
                $botonSiguiente = HTML::forma('', $botonSiguiente);
                $botonUltima = HTML::campoOculto('pagina', $totalPaginas);
                $botonUltima .= HTML::boton('ultimo', $textos->id('ULTIMA_PAGINA'), 'directo');
                $botonUltima = HTML::forma('', $botonUltima);
            }

            $infoPaginacion = self::contarPaginacion($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas);
        }


        return HTML::contenedor($botonPrimera . $botonAnterior . $botonSiguiente . $botonUltima . $infoPaginacion, 'centrado');
    }

    /**
     * Metodo por si acaso me toca mostrar dos paginadores en una misma pagina
     * @global type $textos
     * @param type $totalRegistrosActivos   = total de registros activos que tiene determinado modulo
     * @param type $registroInicial         = Registro inicial desde el cual debe empezar la consulta         
     * @param type $registros               = Total de registros que se deben mostrar por página, este dato se toma desde el archivo de configuracion
     * @param type $pagina                  = Pagina actual en la que se debe empezar la consulta
     * @return type 
     */
    public static function mostrarPaginador2($totalRegistrosActivos, $registroInicial, $registros, $pagina/* , $totalPaginas */) {
        global $textos;
        $botonPrimera = $botonUltima = $botonAnterior = $botonSiguiente = $infoPaginacion = '';

        if ($totalRegistrosActivos > $registros) {
            $totalPaginas = ceil($totalRegistrosActivos / $registros);

            if ($pagina > 1) {
                $botonPrimera = HTML::campoOculto('pagina1', 1);
                $botonPrimera .= HTML::boton('primero', $textos->id('PRIMERA_PAGINA'), 'directo');
                $botonPrimera = HTML::forma('', $botonPrimera);
                $botonAnterior = HTML::campoOculto('pagina1', $pagina - 1);
                $botonAnterior .= HTML::boton('anterior', $textos->id('PAGINA_ANTERIOR'), 'directo');
                $botonAnterior = HTML::forma('', $botonAnterior);
            }

            if ($pagina < $totalPaginas) {
                $botonSiguiente = HTML::campoOculto('pagina1', $pagina + 1);
                $botonSiguiente .= HTML::boton('siguiente', $textos->id('PAGINA_SIGUIENTE'), 'directo');
                $botonSiguiente = HTML::forma('', $botonSiguiente);
                $botonUltima = HTML::campoOculto('pagina1', $totalPaginas);
                $botonUltima .= HTML::boton('ultimo', $textos->id('ULTIMA_PAGINA'), 'directo');
                $botonUltima = HTML::forma('', $botonUltima);
            }

            $infoPaginacion = self::contarPaginacion($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas);
        }


        return HTML::contenedor($botonPrimera . $botonAnterior . $botonSiguiente . $botonUltima . $infoPaginacion, 'centrado');
    }

    /**
     * Metodo que verifica si un determinado usuario desea recibir notificaciones a su correo
     * */
    public static function recibirNotificacionesAlCorreo($idUsuario) {
        global $sql;

        $notificacion = $sql->obtenerValor('usuarios', 'notificaciones', 'id = "'.$idUsuario.'" ');

        if ($notificacion == 1) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 
     * Metodo para capturar la direccion ip real del cliente
     * 
     */
    public static function getRealIP() {

        if ($_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $client_ip =
                    (!empty($_SERVER['REMOTE_ADDR']) ) ?
                    $_SERVER['REMOTE_ADDR'] :
                    ( (!empty($_ENV['REMOTE_ADDR']) ) ?
                            $_ENV['REMOTE_ADDR'] :
                            'unknown' );

            // los proxys van añadiendo al final de esta cabecera
            // las direcciones ip que van 'ocultando'. Para localizar la ip real
            // del usuario se comienza a mirar por el principio hasta encontrar 
            // una dirección ip que no sea del rango privado. En caso de no 
            // encontrarse ninguna se toma como valor el REMOTE_ADDR

            $entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);

            reset($entries);
            while (list(, $entry) = each($entries)) {
                $entry = trim($entry);
                if (preg_match('/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $entry, $ip_list)) {
                    // http://www.faqs.org/rfcs/rfc1918.html
                    $private_ip = array(
                        '/^0\./',
                        '/^127\.0\.0\.1/',
                        '/^192\.168\..*/',
                        '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                        '/^10\..*/');

                    $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

                    if ($client_ip != $found_ip) {
                        $client_ip = $found_ip;
                        break;
                    }
                }
            }
        } else {
            $client_ip =
                    (!empty($_SERVER['REMOTE_ADDR']) ) ?
                    $_SERVER['REMOTE_ADDR'] :
                    ( (!empty($_ENV['REMOTE_ADDR']) ) ?
                            $_ENV['REMOTE_ADDR'] :
                            'unknown' );
        }

        return $client_ip;
    }


    /**
     * Metodo que se encarga de registrar un error cuando el usuario se intenta logear
     */
    public function registrarError() {

        $varIp = self::getRealIP();

        Sesion::registrar('ipUsuario', $varIp);
    }

    public function registrarErrorDeUsuario($usuario) {
        global $sql, $sesion_errorUsuario, $textos, $configuracion;

        if ($sql->existeItem('usuarios', 'usuario', $usuario)) {
            
            $varIp = self::getRealIP();
            $datos = array();
            $datos['ip'] = $varIp;
            $datos['usuario'] = $usuario;
            if (isset($sesion_errorUsuario) && $sesion_errorUsuario['usuario'] == $usuario) {
                $datos['intentos'] = $sesion_errorUsuario['intentos'] + 1;
            } else {
                $datos['intentos'] = 1;
            }

            Sesion::registrar('errorUsuario', $datos);

            if (isset($sesion_errorUsuario) && $sesion_errorUsuario['intentos'] >= 3 && $sesion_errorUsuario['usuario'] == $usuario) {

                $datosUser['bloqueado'] = '1';
                $consulta = $sql->modificar('usuarios', $datosUser, 'usuario = "' . $usuario . '"');

                $contrasena = substr(md5(uniqid(rand(), true)), 0, 8);
                $datosContrasena['contrasena'] = md5($contrasena);
                $consulta = $sql->modificar('usuarios', $datosContrasena, 'usuario = "' . $usuario . '"');

                if ($consulta) {
                    $user = new Usuario($usuario);
                    $url = $configuracion['SERVIDOR']['principal'];
                    $mensaje = str_replace('%1', $user->persona->nombre, $textos->id('CONTENIDO_MENSAJE_USUARIO_BLOQUEADO'));
                    $mensaje = str_replace('%2', $url, $mensaje);
                    $mensaje = str_replace('%3', $contrasena, $mensaje);
                    Servidor::enviarCorreo($user->persona->correo, $textos->id('ASUNTO_MENSAJE_USUARIO_BLOQUEADO'), $mensaje, $user->persona->nombreCompleto);
                }
            }
        }//fin del if existe el usuario
    }


    /**
     * Funcion que crea en codigo html el bloque izquierdo con la informacion del usuario (de la pagina principal del perfil del usuario), esto con el fin
     * de devolverlo como respuesta via ajax en el caso de que el usuario actualize su información
     * @global type $sql
     * @global type $configuracion
     * @global type $textos
     * @param type $id
     * @return type 
     * 
     */
    public static function modificarUsuarioAjax($id) {
        global $configuracion, $textos, $sesion_usuarioSesion;

        if (!isset($id)) {
            return NULL;
        }

        $usuario = new Usuario($id);

        $botones = HTML::nuevoBotonModificarUsuarioInterno($id, $usuario->urlBase);
        $botones = HTML::contenedor($botones, 'botonesInternos', 'botonesInternos');
        $contenidoUsuario = $botones;
        $img = HTML::imagen($usuario->persona->imagenPrincipal, 'imagenUsuario');
        $contenidoUsuario .= HTML::enlace($img, $usuario->persona->imagenPrincipal, '', '', array('rel' => 'prettyPhoto[""]'));
        $contenidoUsuario .= HTML::parrafo($textos->id('NOMBRE_COMPLETO'), 'negrilla');
        $contenidoUsuario .= HTML::parrafo($usuario->persona->nombreCompleto, 'justificado margenInferior ancho200px');
        $imagen = HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'warning_blue.png');

        if ($usuario->persona->ciudadResidencia) {
            $contenidoUsuario .= HTML::parrafo($textos->id('CIUDAD'), 'negrilla');
            $contenidoUsuario .= HTML::parrafo($usuario->persona->ciudadResidencia . ', ' . $usuario->persona->paisResidencia, 'justificado margenInferior ancho250px');
        } else {
            if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) {
                $contenidoUsuario .= HTML::parrafo($textos->id('SIN_CIUDAD') . $imagen, 'negrilla');
                $contenidoUsuario .= HTML::botonImagenAjax($textos->id('POR_FAVOR_ESCOJA_CIUDAD'), 'estiloEnlace justificado margenInferior ancho250px', 'iLikeIt', '', '/ajax/users/edit', array('id' => $usuario->id), '');
            }
        }

        if ($usuario->centro) {
            $contenidoUsuario .= HTML::parrafo($textos->id('CENTRO_BINACIONAL'), 'negrilla');
            $contenidoUsuario .= HTML::parrafo($usuario->centro, 'justificado margenInferior ancho250px');
        } else {
            if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $usuario->id) {
                $contenidoUsuario .= HTML::parrafo($textos->id('SIN_CENTRO_BINACIONAL') . $imagen, 'negrilla');
                $contenidoUsuario .= HTML::botonImagenAjax($textos->id('POR_FAVOR_ESCOJA_CENTRO'), 'estiloEnlace justificado margenInferior ancho250px', 'iLikeIt', '', '/ajax/users/edit', array('id' => $usuario->id), '');
            }
        }

        if ($usuario->persona->descripcion) {
            $contenidoUsuario .= HTML::parrafo($textos->id('ACERCA_DE_USUARIO'), 'negrilla');
            $contenidoUsuario .= HTML::parrafo($usuario->persona->descripcion, 'justificado margenInferior ancho250px');
        }

        if ($usuario->tipo) {
            $contenidoUsuario .= HTML::parrafo($textos->id('PERFIL'), 'negrilla');
            $contenidoUsuario .= HTML::parrafo($usuario->tipo, 'justificado margenInferior ancho250px');
        }

        $contenidoUsuario = HTML::contenedor($contenidoUsuario, '', 'contenidoUsuario');
        $contenido = HTML::bloque('usuario_' . $usuario->id, $usuario->sobrenombre, $contenidoUsuario);

        return $contenido;
    }

    public static function modificarImagenDerechaUsuario() {
        global $sesion_usuarioSesion;

        $contenido = HTML::enlace(HTML::imagen($sesion_usuarioSesion->persona->imagenPrincipal, 'imagenPrincipalUsuario margenInferior'), $sesion_usuarioSesion->url);

        return $contenido;
    }

    /**
     * @global type $sesion_usuarioSesion
     * @global type $textos
     * @global type $modulo
     * @param type $arregloItems
     * @param type $datosTabla
     * @param type $rutaPaginador
     * @param type $datosPaginacion
     * @return type 
     */
    static function generarTablaRegistros($arregloItems, $datosTabla, $rutaPaginador, $datosPaginacion = NULL, $rutaConsulta = NULL) {
        global  $textos;

        $fila = 0;
        $columnas = array(); //columnas que va a tener la tabla
        $celdas = array(); //celdas que va a tener la tabla  

        $ids = array(); //identificador de cada uno de los registros
        $arregloCeldas = array();
        $item = ''; //codigo html final a devolver por el metodo    

        if (isset($datosTabla) && is_array($datosTabla)) {//verifico que llegue un arreglo con los nombres de las columnas y con que celdas(posiciones del objeto en el array devuelto por el listar) se van a recorrer
            foreach ($datosTabla as $columna => $celda) {//recorro el arreglo
                $columnas[] = $columna; //agrego a cada uno su valor correspondiente            
                $celdas[] = $celda;
            }

            foreach ($arregloItems as $elemento) {//recorro el arreglo de registros que me envian
                if ($elemento->id != 0) {
                    $fila++;

                    $filas = array(); //filas que va a aparecer en la tabla

                    foreach ($celdas as $registro) {//armo las celdas que se van a pasar a la tabla para ser generada
                        $registro = explode('|', $registro); //en celdas viene el nombre usado en el objeto, y el nombre del mismo usado para la consulta ej: nombreItem | i.nombre
                        $filas[] = HTML::parrafo($elemento->$registro[0], 'centrado'); //por eso accedo a la posicion 0 que es donde viene el nombre del objeto
                    }

                    $arregloCeldas[$fila] = $filas;
                    $ids[] = 'tr_' . $elemento->id;
                }
            }//fin del foreach
//        print_r($arregloItems);
        }//fin del if(isset($datosTabla) && is_array($datosTabla))

        $paginador = '';
        $pag = '';
        if (isset($datosPaginacion) && is_array($datosPaginacion)) {
            //$datosPaginacion =                  0=>totalRegistrosActivos  1=>registroInicial   2=>registros         3=>pagina
            $paginador = Recursos::mostrarPaginadorPeque($datosPaginacion[0], $datosPaginacion[1], $datosPaginacion[2], $datosPaginacion[3]);
            $pag = $datosPaginacion[3];
        }

        $estilosColumnas = array('ancho25porCiento', 'ancho25porCiento', 'ancho25porCiento', 'ancho25porCiento');
        $opciones = array('cellpadding' => '3', 'border' => '2', 'cellspacing' => '1', 'ruta_paginador' => $rutaPaginador, 'ruta' => $rutaConsulta, 'pagina' => $pag);
        $item .= HTML::tablaGrilla($columnas, $arregloCeldas, 'tablaRegistros', 'tablaRegistros', $estilosColumnas, 'filasTabla', $opciones, $ids, $celdas);

        $ayuda = HTML::cargarIconoAyuda($textos->id('AYUDA_MODULO'));


        $item .= HTML::contenedor($ayuda . $paginador, 'contenedorInferiorTabla', 'contenedorInferiorTabla');

        $item = HTML::contenedor($item, 'contenedorTablaRegistros', 'contenedorTablaRegistros');


        return $item;
    }

    public static function mostrarPaginadorPeque($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas = NULL) {
        global $textos;

        if ($totalRegistrosActivos > $registros) {
            $totalPaginas = ceil($totalRegistrosActivos / $registros);
            $botonPrimera = $botonUltima = $botonAnterior = $botonSiguiente = '';

            if ($pagina > 1) {
                $botonPrimera = ''; //HTML::campoOculto('pagina', 1);
                $botonPrimera .= HTML::contenedor($textos->id('PRIMERA_PAGINA'), 'botonPrimeraPagina botonPaginacion', 'botonPrimeraPagina', array('pagina' => (1)));

                $botonAnterior = ''; //HTML::campoOculto('pagina', $pagina-1);
                $botonAnterior .= HTML::contenedor($textos->id('PAGINA_ANTERIOR'), 'botonAtrasPagina botonPaginacion', 'botonAtrasPagina', array('pagina' => ($pagina - 1)));
            }

            if ($pagina < $totalPaginas) {
                $botonSiguiente = ''; //HTML::campoOculto('pagina', $pagina+1);
                $botonSiguiente .= HTML::contenedor($textos->id('PAGINA_SIGUIENTE'), 'botonSiguientePagina botonPaginacion', 'botonSiguientePagina', array('pagina' => ($pagina + 1)));

                $botonUltima = ''; //HTML::campoOculto('pagina', $totalPaginas);
                $botonUltima .= HTML::contenedor($textos->id('ULTIMA_PAGINA'), 'botonUltimaPagina botonPaginacion', 'botonUltimaPagina', array('pagina' => ($totalPaginas)));
            }

            $infoPaginacion = self::contarPaginacion($totalRegistrosActivos, $registroInicial, $registros, $pagina, $totalPaginas);
        }

        $paginador = HTML::contenedor($botonPrimera . $botonAnterior . $botonSiguiente . $botonUltima, 'paginadorTabla', 'paginadorTabla');
        $paginador .= HTML::contenedor($infoPaginacion, 'informacionPaginacion');

        return $paginador;
    }

//fin del metodo mostrar paginador
}

?>
