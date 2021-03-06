<?php

/**
 * @package     FOLCS
 * @subpackage  Cursos
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondragón <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 * */
global $url_ruta, $sql, $configuracion, $textos, $modulo, $sesion_usuarioSesion, $forma_pagina, $url_funcionalidad, $url_categoria;

$contenido = '';

if (isset($url_ruta)) {

    $seguidor_actual = false;

    $curso = new Curso($url_ruta);

    if (isset($curso->id)) {
        Plantilla::$etiquetas['TITULO_PAGINA'] .= ' :: ' . $textos->id('MODULO_ACTUAL');
        Plantilla::$etiquetas['DESCRIPCION'] = $curso->nombre;

        $nombreBloque = $textos->id('MAS_CURSOS');
        $excluidas = array($curso->id);
        $botones = '';
        $tituloPrincipal = HTML::enlace(strtoupper($textos->id('MODULO_ACTUAL')), '/' . $modulo->url, 'subrayado') . ' :: ' . $curso->nombre;

        $listaSeguidores = HTML::frase($textos->id('SIN_SEGUIDORES'), 'margenInferior');
        $consulta_seguidores = $sql->seleccionar(array('cursos_seguidos'), array('id', 'id_usuario', 'UNIX_TIMESTAMP(fecha_inscripcion)'), 'id_curso = "' . $curso->id . '" ', '', 'fecha_inscripcion DESC');
        if ($sql->filasDevueltas) {

            $lista = array();
            $seguidor_actual = false;
            while ($seguidor = $sql->filaEnObjeto($consulta_seguidores)) {

                $usuario_seguidor = new Usuario($seguidor->id_usuario);

                $item = '';
                if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $curso->idAutor) {
                    $item .= HTML::campoChequeo("$seguidor->id", "", "checksItems");
                }
                $item .= HTML::enlace(HTML::imagen($usuario_seguidor->persona->imagenMiniatura, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $usuario_seguidor->url);
                $item .= HTML::enlace(HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $usuario_seguidor->persona->idGenero . '.png') . $usuario_seguidor->persona->nombreCompleto, 'negrilla'), $usuario_seguidor->url);
                $item3 = ''; //HTML::parrafo(date('D, d M Y h:i:s A', $seguidor->fecha_inscripcion), 'pequenia cursiva negrilla margenInferior');

                if (!empty($usuario_seguidor->persona->ciudadResidencia)) {
                    $item3 .= HTML::parrafo(HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['iconosBanderas'] . '/' . strtolower($usuario_seguidor->persona->codigoIsoPais) . '.png', 'miniaturaBanderas') . ' ' . $usuario_seguidor->persona->ciudadResidencia . ', ' . $usuario_seguidor->persona->paisResidencia);
                    // $item3 .= HTML::imagen($configuracion['SERVIDOR']['media'].$configuracion['RUTAS']['iconosBanderas'].'/'.strtolower($usuario->persona->codigoIsoPais).'.png', 'miniaturaBanderas');
                }
                $item .= HTML::contenedor($item3, 'fondoUltimos5Gris'); //barra del contenedor gris

                if ($seguidor->id_usuario == $sesion_usuarioSesion->id) {
                    $seguidor_actual = true;
                }
                $lista[] = $item;
            }

            $botonBorrar = HTML::contenedor(HTML::boton("basura", $textos->id("ELIMINAR"), "directo", "", "botonBorrarItemsVisibles", "", array("rutaEliminar" => "/ajax/courses/deleteFollowers")), "oculto contenedorBotonBorrarItemsVisibles", "contenedorBotonBorrarItemsVisibles");
            $checkBorrar = HTML::campoChequeo("", "", "marcarItemsVisibles", "marcarItemsVisibles") . HTML::frase($textos->id("SELECCIONAR_TODOS"), "negrilla letraMasGrande1 margenDerechaDoble margenSuperior");
            $checkBorrar = HTML::contenedor($checkBorrar, "flotanteIzquierda");
            $contenedorBorrar = HTML::contenedor($checkBorrar . $botonBorrar, "contenedorMarcadorTodosItemsVisibles");

            $listaSeguidores = '';
            if (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0 || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $curso->idAutor) {
                $listaSeguidores .= $contenedorBorrar;
            }
            $listaSeguidores .= HTML::lista($lista, 'listaVertical listaConIconos bordeInferiorLista');
        }

        if (isset($sesion_usuarioSesion)) {
            if ($sesion_usuarioSesion->idTipo == 0 || $sesion_usuarioSesion->id == $curso->idAutor) {
                $botones .= HTML::botonEliminarItem($curso->id, $curso->urlBase);
                $botones .= HTML::botonModificarItem($curso->id, $curso->urlBase);
                $botones = HTML::contenedor($botones, 'botonesInternos', 'botonesInternos');
                //$img = HTML::imagen($configuracion['SERVIDOR']['media'].'/'.$configuracion['RUTAS']['imagenesEstilos'].'ayuda.png', 'margenSuperior');
                //$videoAlimentarCurso = HTML::enlace($textos->id('VIDEO_ALIMENTAR_CURSO').' '.$img, 'http://www.youtube.com/watch?v=o5DwTEQM9BU', 'alineadoDerecha estiloBoton', '', array('rel' => 'prettyPhoto[]', 'ayuda' => $textos->id('VIDEO_ALIMENTAR_CURSO')) );


                $img = HTML::imagen($configuracion['SERVIDOR']['media'] . '/' . $configuracion['RUTAS']['imagenesEstilos'] . 'ayuda.png', 'margenSuperior');
                $videoAlimentarCurso = HTML::enlace($textos->id('VIDEO_ALIMENTAR_CURSO') . ' ' . $img, 'http://www.youtube.com/watch?v=o5DwTEQM9BU', 'estiloBoton', '', array('rel' => 'prettyPhoto[]', 'ayuda' => $textos->id('VIDEO_ALIMENTAR_CURSO')));
            } elseif ($seguidor_actual == false) {
                $botones .= HTML::contenedor(HTML::botonAjax('chequeo', 'SEGUIR_CURSO', '/ajax' . $curso->urlBase . '/follow', array('id' => $curso->id)), 'derecha margenInferior titulo margenDerechaCuadruple margenSuperior');
            } elseif ($seguidor_actual == true) {
                $botones .= HTML::contenedor(HTML::botonAjax('cerrarGrueso', 'ABANDONAR_CURSO', '/ajax' . $curso->urlBase . '/leave', array('id' => $curso->id)), 'derecha margenInferior');
                
            }
        }


        /*         * ******* Contenido del Curso *************************** */
        $usuario = new Usuario();
        $contenidoCurso = $botones;

        $contenidoCurso .= HTML::contenedor(HTML::frase(str_replace('%1', HTML::enlace($curso->autor, HTML::urlInterna('USUARIOS', $curso->usuarioAutor)), $textos->id('CREADO_POR') . HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $usuario->getGenero($curso->idAutor) . '.png')), ' margenInferior'), 'justificado margenInferior');

        $contenidoCurso .= HTML::contenedor($curso->descripcion, 'justificado negrilla margenInferior');
        $contenidoCurso .= HTML::contenedor($curso->contenido, 'justificado');
        $contenidoCurso .= HTML::contenedor(HTML::botonesCompartir(), 'botonesCompartir');
        $contenidoCurso .= HTML::parrafo($videoAlimentarCurso, 'margenSuperior margenInferior');


        /*         * *******  Contenido de las actividades  *************************** */
        $contenidoActividades = '';
        $botonesActividades = '';

        if ((isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $curso->idAutor )) {
            $botonesActividades = HTML::contenedor(HTML::botonAjax('chequeo', 'AGREGAR_ACTIVIDAD', '/ajax/activities/add', array('id_curso' => $curso->id)), 'derecha margenInferior margenDerecha margenSuperior');
        }

        $contenidoActividades .= $botonesActividades;

        $actividades = new ActividadCurso();
        $listaActividades = array();

        $cantidadActividades = $actividades->contar($curso->id);


        if ($cantidadActividades) {


            foreach ($actividades->listar(0, 0, '', '', $curso->id) as $actividad) {

                if (( isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $actividad->idUsuario)) {
                    $botones1 = "";

                    //contar la cantidad de respuestas que tiene una determinada actividad
                    $cantidadRespuestas = $sql->obtenerValor('respuestas_actividades', 'COUNT(id)', 'id_actividad = "' . $actividad->id . '"');

                    if ($cantidadRespuestas > 0) {
                        $botones1 .= HTML::contenedor(HTML::botonAjax('chequeo', 'VER_RESPUESTAS', '/ajax/activities/listResponses', array('id_actividad' => $actividad->id)), 'contenedorBotonesLista', 'contenedorBotonesListaActividades');
                    }

                    $botones1 .= HTML::botonEliminarItemAjax($actividad->id, $actividades->urlBase, 'contenedorBotonesListaActividades');
                    $botones1 .= HTML::botonModificarItemAjax($actividad->id, $actividades->urlBase, 'contenedorBotonesListaActividades');


                    $botones1 = HTML::contenedor($botones1, "botonesListaActividades", "botonesListaActividades");
                }
                $contenidoActividad = $botones1;
                $contenidoActividad .= HTML::imagen($actividad->icono, "flotanteIzquierda  margenDerecha miniaturaLista32px verActividad manito", '', array('idActividad' => $actividad->id));
                $contenidoActividad .= HTML::parrafo($actividad->titulo, 'estiloEnlace verActividad', '', array('idActividad' => $actividad->id));

                /* if (strlen($actividad->descripcion) > 100) {
                  $descripcionActividad = substr($actividad->descripcion, 0, 97) . '...';
                  } else {
                  $descripcionActividad = $actividad->descripcion;
                  } */

                $descripcionActividad = '';

                $contenidoActividad2 = HTML::parrafo($descripcionActividad, 'estiloEnlace');

                $textoCantidadRespuestas = '';
                if (( isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $actividad->idUsuario)) {
                    if ($cantidadRespuestas > 0) {
                        $textoCantidadRespuestas .= HTML::enlace($textos->id('CANTIDAD_RESPUESTAS') . ': ' . $cantidadRespuestas, '#', 'alineadoDerecha margenDerecha', 'enlaceCantidadRespuestas', array('id_actividad' => $actividad->id, 'ruta' => '/ajax/activities/listResponses'));
                    } else {
                        $textoCantidadRespuestas .= HTML::frase($textos->id('CANTIDAD_RESPUESTAS') . ': ' . $cantidadRespuestas, 'alineadoDerecha margenDerecha negrilla');
                    }
                } else {
                    $resuelta = $sql->existeItem('respuestas_actividades', 'id_usuario', $sesion_usuarioSesion->id, 'id_actividad = "' . $actividad->id . '"');
                    if ($resuelta) {

                        $idRespuesta = $sql->obtenerValor('respuestas_actividades', 'id', 'id_actividad = "' . $actividad->id . '" AND id_usuario = "' . $sesion_usuarioSesion->id . '"');

                        $imgResuelta = HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "/check_verde.png", ' imgIndicadorNota verRespuestaActividad manito medioMargenIzquierda margenSuperior', 'checkVerde', array('ayuda' => $textos->id('ACTIVIDAD_RESUELTA'), 'idRespuesta' => $idRespuesta));

                        $calificado = $sql->obtenerValor('respuestas_actividades', 'id', 'id_usuario = "' . $sesion_usuarioSesion->id . '" AND id_actividad = "' . $actividad->id . '"');
                        $nota = $sql->obtenerValor('respuestas_actividades', 'nota', 'id = "' . $calificado . '" ');
                        if ($nota) {
                            $calificacion = HTML::frase('* ' . $textos->id('VER_CALIFICACION'), 'estiloEnlace consultarCalificacion', '', array('idRespuesta' => $calificado, 'ruta' => '/ajax/activities/seeGrade'));
                        } else {
                            $calificacion = HTML::frase('* ' . $textos->id('NO_HAS_SIDO_CALIFICADO'), ' negrilla flotanteDerecha');
                        }

                        $textoCantidadRespuestas .= HTML::frase($imgResuelta . HTML::frase($textos->id('ACTIVIDAD_RESUELTA'), 'letraVerde') . ' <br>' . $calificacion, 'negrilla alineadoDerecha fraseInfoRespuesta', 'fraseInfoRespuesta');
                    } else {
                        if ($actividad->diasRestantes >= 0) {
                            if ($actividad->diasRestantes == 0) {
                                $imgResuelta = HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "/alert.png", ' imgIndicadorNota verActividad manito medioMargenIzquierda margenSuperior', 'checkVerde', array('ayuda' => $textos->id('FALTA_RESOLVER'), 'idActividad' => $actividad->id));
                                $textoCantidadRespuestas .= HTML::frase($imgResuelta . HTML::frase($textos->id('HOY_ULTIMO_DIA'), 'letraNaranja'), 'negrilla alineadoDerecha fraseInfoRespuesta', 'fraseInfoRespuesta');
                            } else {
                                $imgResuelta = HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "/alert.png", ' imgIndicadorNota verActividad manito medioMargenIzquierda margenSuperior', 'checkVerde', array('ayuda' => $textos->id('FALTA_RESOLVER'), 'idActividad' => $actividad->id));
                                $textoCantidadRespuestas .= HTML::frase($imgResuelta . HTML::frase(str_replace('%1', $actividad->diasRestantes, $textos->id('DIAS_RESTANTES')), 'letraAzul'), 'negrilla alineadoDerecha fraseInfoRespuesta', 'fraseInfoRespuesta');
                            }
                        } else {
                            $imgResuelta = HTML::imagen($configuracion["SERVIDOR"]["media"] . $configuracion["RUTAS"]["imagenesEstilos"] . "/alert.png", ' imgIndicadorNota verActividad manito medioMargenIzquierda margenSuperior', 'checkVerde', array('ayuda' => $textos->id('FALTA_RESOLVER'), 'idActividad' => $actividad->id));
                            $textoCantidadRespuestas .= HTML::frase($imgResuelta . HTML::frase($textos->id('FECHA_LIMITE_ALCANZADA'), 'letraRoja subtitulo'), 'negrilla alineadoDerecha fraseInfoRespuesta', 'fraseInfoRespuesta');
                        }
                    }
                }
                $contenidoActividad2 .= HTML::parrafo(HTML::frase($textos->id('PUBLICADO_EL') . ': ', 'negrilla') . date("D, d M Y", $actividad->fechaPublicacion) . $textoCantidadRespuestas, 'unCuartoMargenSuperior', '');
                $contenidoActividad2 .= HTML::parrafo(HTML::frase($textos->id('FECHA_LIMITE') . ': ', 'negrilla') . date("D, d M Y", $actividad->fechaLimite), 'unCuartoMargenSuperior', '');
                $contenidoActividad .= HTML::contenedor($contenidoActividad2, "contenedorGrisActividades");

                if ($actividad->diasRestantes >= 0) {
                    $listaActividades[] = HTML::contenedor($contenidoActividad, "contenedorListaActividades", "contenedorActividad" . $actividad->id);
                } else {
                    $listaActividades[] = HTML::contenedor($contenidoActividad, "contenedorListaActividades actividadVencida", "contenedorActividad" . $actividad->id);
                }
            }//fin del foreach
        } else {
            $listaActividades[] = HTML::frase(HTML::parrafo($textos->id("SIN_ACTIVIDADES"), "sinRegistros", "sinRegistros"), "margenInferior");
        }


        $contenidoActividades .= HTML::lista($listaActividades, "listaVertical bordeSuperiorLista", "botonesOcultos", "listaActividades");



        $contenidoActividades = HTML::contenedor($contenidoActividades, 'contenedorActividadesCurso');

        if ((isset($sesion_usuarioSesion) && $cantidadActividades && $seguidor_actual == true ) || ( isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $curso->idAutor )) {
            $pestanasCurso = array(
                HTML::frase($textos->id('CONTENIDO_CURSO'), 'letraBlanca') => $contenidoCurso,
                HTML::frase($textos->id('ACTIVIDADES'), 'letraBlanca') => $contenidoActividades
            );

            $pestanasContenidoCurso = HTML::contenedor(HTML::pestanas2('contenidoCurso', $pestanasCurso), 'pestanasContenidoCurso');



            $contenido = HTML::bloque('curso_' . $curso->id, $tituloPrincipal, $pestanasContenidoCurso, '', 'botonesOcultos');
        } else {
            $contenido = HTML::bloque('curso_' . $curso->id, $tituloPrincipal, $contenidoCurso, '', 'botonesOcultos');
        }

        if ($seguidor_actual == true || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == $curso->idAutor || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->id == 0) {

            $recursos = array(
                HTML::frase($textos->id('SEGUIDORES'), 'letraBlanca') => $listaSeguidores,
                HTML::frase($textos->id('VIDEOS'), 'letraBlanca') => Recursos::bloqueVideos('CURSOS', $curso->id, $curso->idAutor),
                HTML::frase($textos->id('AUDIOS'), 'letraBlanca') => Recursos::bloqueAudios('CURSOS', $curso->id, $curso->idAutor),
                HTML::frase($textos->id('IMAGENES'), 'letraBlanca') => Recursos::bloqueImagenes('CURSOS', $curso->id, $curso->idAutor),
                HTML::frase($textos->id('GALERIAS'), 'letraBlanca') => Recursos::bloqueGalerias('CURSOS', $curso->id, $curso->idAutor),
                HTML::frase($textos->id('DOCUMENTOS'), 'letraBlanca') => Recursos::bloqueArchivos('CURSOS', $curso->id, $curso->idAutor),
                HTML::frase($textos->id('FOROS'), 'letraBlanca') => Recursos::bloqueForos('CURSOS', $curso->id, $curso->idAutor)
            );

            if (isset($sesion_usuarioSesion) && Perfil::verificarPermisosConsulta('ENLACES') || isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0) {
                $recursos[HTML::frase($textos->id('ENLACES'), 'letraBlanca')] = Recursos::bloqueEnlaces('CURSOS', $curso->id, $curso->idAutor);
            }

            $contenido .= HTML::contenedor(HTML::pestanas2('recursosCurso', $recursos), 'pestanasRecursosUsuarios');
//			$contenido .= HTML::contenedor('<span class=\'bloqueTitulo ui-helper-clearfix ui-widget-header ui-corner-top\'>'.$textos->id('SALON_CLASE').'</span>\n', 'encabezadoBloque');
            $contenido .= HTML::contenedor(HTML::agregarIframe('http://media.ablaonline.org/phpfreechat?id_item=' . $curso->id . '&usuario=' . $sesion_usuarioSesion->usuario . '&nombre_sala=' . 'Virtual classroom of ' . $curso->nombre . '&id_modulo=' . $modulo->id, 700, 430), 'estiloIframe');
            $contenido .= '<div class="sombraInferior listadoCursos"></div> ';
        }
        $contenido .= '<div class="" id="sombraFondoCursos"></div> ';
    }
} else {
    $nombreBloque = $textos->id('MODULO_ACTUAL');
    $curso = new Curso();
    $excluidas = '';


/////////// DATOS DE PAGINACION ///////////////////////////////////////////   
    $registros = $configuracion['GENERAL']['registrosPorPagina'];

    if (isset($forma_pagina)) {
        $pagina = $forma_pagina;
    } else {
        $pagina = 1;
    }

    $registroInicial = ($pagina - 1) * $registros;

/////////////////////////////////////////////////////////////////////////////

    /**
     * Formulario para adicionar un nuevo elemento
     * */
    if ((isset($sesion_usuarioSesion) && Perfil::verificarPermisosAdicion($curso->idModulo)) || (isset($sesion_usuarioSesion) && $sesion_usuarioSesion->idTipo == 0)) {

        $img = HTML::imagen($configuracion['SERVIDOR']['media'] . '/' . $configuracion['RUTAS']['imagenesEstilos'] . 'ayuda.png', 'margenSuperior');
        $videoCrearCurso = HTML::enlace($textos->id('VIDEO_CREAR_CURSO') . ' ' . $img, 'http://www.youtube.com/watch?v=xV6pEKWUVBE', 'estiloBoton', '', array('rel' => 'prettyPhoto[]', 'ayuda' => $textos->id('VIDEO_CREAR_CURSO')));


        $botonAdicionar = HTML::contenedor(HTML::botonAdicionarItem($curso->urlBase, $textos->id('ADICIONAR_CURSO')) . $videoCrearCurso, 'derecha margenInferior');
    } else {
        $botonAdicionar = '';
    }

    /**
     * Boton que carga la ventana modal para realizar la busqueda
     * */
    //$buscador = HTML::contenedor(HTML::botonAjax('masGrueso', $textos->id('BUSCAR'), HTML::urlInterna('CURSOS', 0, true, 'searchCourses')), 'flotanteDerecha');
    $buscador = HTML::campoTexto("datos[id_curso]", 65, 255, $textos->id("BUSCAR_CURSOS"), "autocompletable margenSuperiorDoble margenInferior margenIzquierda", "campoBuscarCursos", array("title" => HTML::urlInterna("CURSOS", 0, true, "listCoursesFromInput")));

    $cat = '';
    if (isset($url_funcionalidad) && $url_funcionalidad == 'category') {
        $cat = $url_categoria;
    }

    /**
     * Verifico si lo que me estan pidiendo es los blogs que me gustan
     * en ese caso llamo al metodo mostrarBlogs que me gustan
     * */
    if ($cat != 'i_follow') {

        $urlModulo = 'courses';
        $idModulo = $curso->idModulo;
        $valPredeterminado = $cat;
        $nombreModulo = 'CURSOS';
        $filtroCategoria = Categoria::selectorCategorias($urlModulo, $idModulo, $valPredeterminado, $nombreModulo, $botonAdicionar . $buscador, 'si');

        $listaCursos = array();
        $fila = 0;

        if ($curso->registros) {

            /*             * *** Identificar el tipo de perfil del ususario  *********** */
            if (isset($sesion_usuarioSesion)) {
                $idTipo = $sesion_usuarioSesion->idTipo;
            } else {
                $idTipo = 99;
            }

            /*             * *** fin de identificar el tipo de perfil del ususario  *** */
            /*             * ********************Calcular el total de registros activos************************** */
            $totalRegistrosActivos = 0;

            foreach ($curso->listar(0, 0, $excluidas, '', $idTipo, $curso->idModulo, $cat) as $elemento) {
                if ($elemento->activo) {
                    $totalRegistrosActivos++;
                }
            }
            /*             * *********************************************************************************** */
            $reg = sizeof($curso->listar(0, 0, $excluidas, '', $idTipo, $curso->idModulo, $cat));

            if ($reg > 0) {

                foreach ($curso->listar($registroInicial, $registros, $excluidas, '', $idTipo, $curso->idModulo, $cat) as $elemento) {
                    $fila++;
                    $item = '';
                    $celdas = array();

                    if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->idTipo == 0)) {
                        $botones = '';
                        $botones .= HTML::botonEliminarItemAjax($elemento->id, $curso->urlBase);
                        $botones .= HTML::botonModificarItemAjax($elemento->id, $curso->urlBase);

                        $item .= HTML::contenedor($botones, 'botonesLista', 'botonesLista');

                        $item .= HTML::parrafo($textos->id('TITULO'), 'negrilla');
                        $item .= HTML::parrafo(HTML::enlace($elemento->nombre, $elemento->url), 'negrilla');

                        if ($elemento->activo) {
                            $estado = HTML::parrafo($textos->id('ACTIVO'));
                        } else {
                            $estado = HTML::parrafo($textos->id('INACTIVO'));
                        }

                        $celdas[0][] = HTML::parrafo($textos->id('AUTOR'), 'negrilla') . HTML::parrafo($elemento->autor);
                        $celdas[0][] = HTML::parrafo($textos->id('ESTADO'), 'negrilla') . HTML::parrafo($estado);
                        $celdas[1][] = HTML::parrafo($textos->id('FECHA_CREACION'), 'negrilla') . HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaCreacion));
                        $celdas[1][] = HTML::parrafo($textos->id('FECHA_PUBLICACION'), 'negrilla') . HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion));
                        $celdas[1][] = HTML::parrafo($textos->id('FECHA_ACTUALIZACION'), 'negrilla') . HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaActualizacion));
                        $item .= HTML::tabla(array(), $celdas, 'tablaCompleta2');
                        $item = HTML::contenedor($item, 'contenedorListaCursos', 'contenedorListaCursos' . $elemento->id);
                        $listaCursos[] = $item;
                    } else {

                        if ($elemento->activo) {

                            if (isset($sesion_usuarioSesion) && ($sesion_usuarioSesion->id == $elemento->idAutor)) {
                                $botones = '';
                                $botones .= HTML::botonEliminarItemAjax($elemento->id, $curso->urlBase);
                                $botones .= HTML::botonModificarItemAjax($elemento->id, $curso->urlBase);
                                $item .= HTML::contenedor($botones, 'botonesLista', 'botonesLista');
                            }

                            $usuario = new Usuario();

                            $item .= HTML::enlace(HTML::imagen($elemento->fotoAutor, 'flotanteIzquierda  margenDerecha miniaturaListaUltimos5'), $elemento->url);
                            $item .= HTML::parrafo(HTML::enlace($elemento->nombre, $elemento->url) . ' ' . HTML::frase(preg_replace('/\%1/', HTML::enlace($elemento->autor, HTML::urlInterna('USUARIOS', $elemento->usuarioAutor)), HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . $usuario->getGenero($elemento->idAutor) . '.png') . $textos->id('CREADO_POR2')), 'flotanteCentro'));
                            $item2 = HTML::parrafo(date('D, d M Y h:i:s A', $elemento->fechaPublicacion), 'pequenia cursiva negrilla margenInferior');
                            $item2 .= HTML::parrafo($elemento->descripcion, 'margenInferior');
                            $item .= HTML::contenedor($item2, 'fondoUltimos5GrisL'); //barra del contenedor gris
                            $item = HTML::contenedor($item, 'contenedorListaCursos', 'contenedorListaCursos' . $elemento->id);

                            $listaCursos[] = $item;
                        }
                    }
                }
//////////////////paginacion /////////////////////////////////////////////////////
                $paginacion = Recursos:: mostrarPaginador($totalRegistrosActivos, $registroInicial, $registros, $pagina);

                $listaCursos[] = $paginacion;
            } else {

                $listaCursos = array($textos->id('SIN_REGISTROS'));
            }
        }

        $listaCursos = HTML::lista($listaCursos, 'listaVertical listaConIconos bordeSuperiorLista', 'botonesOcultos');
        $listaCursos = $filtroCategoria . $listaCursos;

        $contenido .= HTML::bloque('listadoCursos', $nombreBloque, $listaCursos);
    } else {//lo que estan pidiendo es los cursos que sigo

        /**
         * Cargar el select que muestra las categorias pertenecientes a este modulo, a su vez, se le incluyen el boton adicionar y el boton buscador
         * para que devuelva un contenedor con los tres elementos dentro bien organizados
         * */
        $urlModulo = 'courses';
        $idModulo = $curso->idModulo;
        $valPredeterminado = $cat;
        $nombreModulo = 'CURSOS';
        $filtroCategoria = Categoria::selectorCategorias($urlModulo, $idModulo, $valPredeterminado, $nombreModulo, $botonAdicionar . $buscador, 'si');

        $contenido .= HTML::bloque('listadoCursos', $tituloBloque, $filtroCategoria . $curso->cursosQueSigo());
    }
}
Plantilla::$etiquetas['BLOQUE_IZQUIERDO'] = $contenido;
?>