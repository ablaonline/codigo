<?php

/**
 * Clase Archivo: encargada de interactuar con los archivos y el servidor
 * @package     FOLCS
 * @subpackage  Base
 * @author      Pablo Andrés Vélez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2011 Colombo-Americano Soft.
 * @version     0.2
 *
 * */
class Archivo {

    /**
     *
     * @global type $configuracion
     * @param type $archivo
     * @param type $configuracionRuta
     * @param type $identificador
     * @return string                     = ruta del archivo que se subio al servidor
     */
    public static function subirArchivoAlServidor($archivo, $configuracionRuta, $identificador = NULL) {
        global $configuracion;

        if (!isset($archivo) && empty($archivo['tmp_name'])) {
            Recursos::escribirTxt("NO venia ningun archivo");
            return NULL;
        }

        $formato = strtolower(substr($archivo['name'], strrpos($archivo['name'], '.') + 1));
        $nombre = substr(md5(uniqid(rand(), true)), 0, 8);
        $subcarpeta = substr($nombre, 0, 2);
        $ruta = $configuracionRuta . '/' . $subcarpeta . '/' . $nombre . '.' . $formato;

        $rutaAdiciona = $subcarpeta . '/' . $nombre . '.' . $formato;

        while (file_exists($ruta)) {
            $nombre = substr(md5(uniqid(rand(), true)), 0, 8);
            $subcarpeta = substr($nombre, 0, 2);
            $ruta = $configuracionRuta . '/' . $subcarpeta . '/' . $nombre . $formato;
        }

        $ruta_carpeta = $configuracionRuta . '/' . $subcarpeta;

        if (!file_exists($ruta_carpeta)) {
            mkdir($configuracionRuta . '/' . $subcarpeta, 0777, true);
        }


        do {
            $copiar = move_uploaded_file($archivo['tmp_name'], $ruta);
        } while (!is_file($ruta));

        if (!$copiar) {
            Recursos::escribirTxt("--**** fallo al copiar la imagen ***----");
            return false;
        } else {

            chmod($ruta, 0777);

            if (in_array($formato, array('wma', 'wav'))) {//se agrego                
                $comando = str_replace('%1', $ruta, $configuracion['PROGRAMAS']['ffmpeg']);
                $comando = str_replace('%2', $configuracionRuta . '/' . $subcarpeta . '/' . $nombre . '.mp3', $comando);
                $convertir = exec($comando);
                $borrado = unlink($ruta);
                $formato = 'mp3';
            } elseif (in_array($formato, array('3gp', 'ogg', '3gpp', 'mp4'))) {
                $comando = str_replace('%1', $ruta, $configuracion['PROGRAMAS']['ffmpeg2']);
                $comando = str_replace('%2', $configuracionRuta . '/' . $subcarpeta . '/' . $nombre . '.mp3', $comando);
                $convertir = exec($comando);
                $borrado = unlink($ruta);
                $formato = 'mp3';
            }
            $rutaAdiciona = $subcarpeta . '/' . $nombre . '.' . $formato; //se agrego

            if ($identificador != '' && is_array($identificador)) {
                $anchoMaximo = $identificador[0];
                $altoMaximo = $identificador[1];
                $anchoMinimo = $identificador[2];
                $altoMinimo = $identificador[3];
                $datos_imagen = getimagesize($ruta);
                $ancho = $datos_imagen[0];
                $alto = $datos_imagen[1];

                $configuracionRutaMini = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['imagenesMiniaturas'];

                if ($anchoMinimo != '' && $altoMinimo != '') {

                    $nombreMini = $nombre; //nombre de la miniatura                
                    $rutaMini = $configuracionRutaMini . '/' . $subcarpeta . '/' . $nombreMini . '.' . $formato;

		$ruta_carpeta_mini = $configuracionRutaMini . '/' . $subcarpeta;

		if (!file_exists($ruta_carpeta_mini)) {
		  mkdir($configuracionRutaMini . '/' . $subcarpeta, 0777, true);
		}


                    $copiarMini = copy($ruta, $rutaMini);


                    do {
                        if (!is_file($rutaMini)) {
                            $copiarMini = copy($ruta, $rutaMini);
                            if ($copiarMini) {
                                chmod($rutaMini, 0777);
                            } else {
                                Recursos::escribirTxt("fallo al copiar la miniatura -> nueva");
				return false;
                            }
                        }
                    } while (!is_file($rutaMini));




                    if ((($ancho / $alto) > ($anchoMinimo / $altoMinimo)) && ($ancho > $anchoMinimo)) {
                        $dimensiones_min[0] = $anchoMinimo;
                        $dimensiones_min[1] = ($anchoMinimo / $ancho) * $alto;
                    } elseif ($alto > $altoMinimo) {
                        $dimensiones_min[0] = ($altoMinimo / $alto) * $ancho;
                        $dimensiones_min[1] = $altoMinimo;
                    } else {
                        $dimensiones_min[0] = $anchoMinimo;
                        $dimensiones_min[1] = $altoMinimo;
                    }

                    $lienzo = imagecreatetruecolor($dimensiones_min[0], $dimensiones_min[1]);

                    switch ($formato) {
                        case 'png' : $imagen = imagecreatefrompng($rutaMini);
                            $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones_min[0], $dimensiones_min[1], $ancho, $alto);
                            $guardar = imagepng($lienzo, $rutaMini);
                            break;


                        case 'jpg' : $imagen = imagecreatefromjpeg($rutaMini);
                            $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones_min[0], $dimensiones_min[1], $ancho, $alto);
                            $guardar = imagejpeg($lienzo, $rutaMini);
                            break;

                        case 'jpeg' : $imagen = imagecreatefromjpeg($rutaMini);
                            $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones_min[0], $dimensiones_min[1], $ancho, $alto);
                            $guardar = imagejpeg($lienzo, $rutaMini);
                            break;

                        /* case 'gif' : $imagen = imagecreatefromgif($rutaMini);
                          $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones_min[0], $dimensiones_min[1], $ancho, $alto);
                          $guardar = imagegif($lienzo, $rutaMini);
                          break; */
                    }

                    if ($formato != 'gif') {
                        imagedestroy($lienzo);
                        imagedestroy($imagen);
                    }
                }

                if ((($ancho / $alto) > ($anchoMaximo / $altoMaximo)) && ($ancho > $anchoMaximo)) {
                    $dimensiones[0] = $anchoMaximo;
                    $dimensiones[1] = ($anchoMaximo / $ancho) * $alto;
                } elseif ($alto > $altoMaximo) {
                    $dimensiones[0] = ($altoMaximo / $alto) * $ancho;
                    $dimensiones[1] = $altoMaximo;
                } else {
                    $dimensiones[0] = $anchoMaximo;
                    $dimensiones[1] = $altoMaximo;
                }

                $lienzo = imagecreatetruecolor($dimensiones[0], $dimensiones[1]);

                switch ($formato) {
                    case 'png' : $imagen = imagecreatefrompng($ruta);
                        $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones[0], $dimensiones[1], $ancho, $alto);
                        $guardar = imagepng($lienzo, $ruta);
                        break;

                    case 'jpg' : $imagen = imagecreatefromjpeg($ruta);
                        $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones[0], $dimensiones[1], $ancho, $alto);
                        $guardar = imagejpeg($lienzo, $ruta);
                        break;

                    case 'jpeg' : $imagen = imagecreatefromjpeg($ruta);
                        $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones[0], $dimensiones[1], $ancho, $alto);
                        $guardar = imagejpeg($lienzo, $ruta);
                        break;

                    /* case 'gif' : $imagen = imagecreatefromgif($ruta);
                      $copia = imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $dimensiones[0], $dimensiones[1], $ancho, $alto);
                      $guardar = imagegif($lienzo, $ruta);
                      break; */
                }
                if ($formato != 'gif') {
                    imagedestroy($lienzo);
                    imagedestroy($imagen);
                }
            }
        }//fin de si el archivo es una imagen 

        return $rutaAdiciona;
    }

    public static function eliminarArchivoDelServidor($ruta) {

        if (!isset($ruta) && !is_array($ruta)) {
            return false;
        }

        $exito = true;

        foreach ($ruta as $archivo) {

            if (!unlink($archivo)) {
                $exito = false;
            }
        }

        return $exito;
    }

    /**
     * Metodo que valida las extensiones de un archivo, devuelve true si tiene una extension valida
     * @global type $configuracion  = arreglo con datos de configuracion del sistema
     * @global type $archivo_imagen = archivo tipo file
     * @param type $archivo
     * @param type $extensiones     = arreglo con las extensiones que se van a validar
     * @return boolean 
     */
    public static function validarArchivo($archivo, $extensiones) {

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
            return false;
        }
    }

}

?>
