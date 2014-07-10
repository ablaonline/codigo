<?php

/**
*
* Copyright (C) 2009 FELINUX Ltda
* Francisco J. Lozano B. <fjlozano@felinux.com.co>
*
* Este archivo es parte de:
* PANCE :: Plataforma para la Administraci�n del Nexo Cliente-Empresa
*
* Este programa es software libre: usted puede redistribuirlo y/o
* modificarlo  bajo los t�rminos de la Licencia P�blica General GNU
* publicada por la Fundaci�n para el Software Libre, ya sea la versi�n 3
* de la Licencia, o (a su elecci�n) cualquier versi�n posterior.
*
* Este programa se distribuye con la esperanza de que sea �til, pero
* SIN GARANT�A ALGUNA; ni siquiera la garant�a impl�cita MERCANTIL o
* de APTITUD PARA UN PROP�SITO DETERMINADO. Consulte los detalles de
* la Licencia P�blica General GNU para obtener una informaci�n m�s
* detallada.
*
* Deber�a haber recibido una copia de la Licencia P�blica General GNU
* junto a este programa. En caso contrario, consulte:
* <http://www.gnu.org/licenses/>.
*
**/

/*** Requiere libreria de terceros (FPDF - www.fpdf.org) ***/
require "fpdf.php";

class PDF extends FPDF {

    var $textoTipo;
    var $textoNombre;
    var $textoCodigo;
    var $textoFecha;
    var $textoVersion;
    var $textoDependencia;
    var $PiePagina;

    /*** Generar tabla ***/
    function generarCabeceraTabla($columnas, $anchoColumnas) {
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(0);
        $this->SetDrawColor(0);
        $this->SetLineWidth(.1);
        $this->SetFont("", "B", "");

        for($i = 0 ; $i < count($columnas); $i++) {
            $this->Cell($anchoColumnas[$i], 4, $columnas[$i], 1, 0, "C", true);
        }
    }

    /*** Generar tabla ***/
    function generarContenidoTabla($filas, $anchoColumnas, $alineacionColumnas = "", $formatoColumnas = "") {
        $this->Ln(0);
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont("");

        $rellenar = true;

        foreach($filas as $fila) {
            $celdas = 0;

            foreach ($fila as $celda) {
                switch (strtoupper($alineacionColumnas[$celdas])) {
                    case "I" :
                        $alineacion = "L";
                        break;
                    case "D" :
                        $alineacion = "R";
                        break;
                    case "C" :
                        $alineacion = "C";
                        break;
                    default :
                        $alineacion = "L";
                        break;
                }

                $this->Cell($anchoColumnas[$celdas], 3, htmlspecialchars_decode($celda), "LRT", 0, $alineacion, $rellenar);
                $celdas++;
            }

            $this->Ln();
            $rellenar = !$rellenar;
        }

        $this->Cell(array_sum($anchoColumnas), 0, "", "T");
    }

    /*** Encabezado ***/
    function Header() {
        global $pance, $imagenesGlobales, $noImprimirEncabezado;

        $this->AliasNbPages();

        if (!$noImprimirEncabezado) {
            if($this->pdfHorizontal == ''){

                $this->SetFont("Arial", "B", 7);
                $this->SetXY(10, 10);
                $this->Cell(52, 24, "", 1, 0);
                $this->Image($imagenesGlobales["logoClienteReportes"], 11, 15, 50);
                $this->SetXY(62, 10);
                $this->Cell(103, 12, $this->textoTipo, 1, 0, "C");
                $this->SetXY(62, 22);
                $this->Cell(103, 12, $this->textoNombre, 1, 0, "C");
                $this->SetXY(165, 10);
                $this->Cell(40, 6, $this->textoCodigo, "LTR", 0, "L");
                $this->SetXY(165, 16);
                $this->Cell(40, 6, $this->textoFecha, "LR", 0, "L");
                $this->SetXY(165, 22);
                $this->Cell(40, 6, $this->textoVersion, "LR", 0, "L");
                $this->SetXY(165, 28);
                $this->Cell(40, 6, $this->textoDependencia, "LBR", 0, "L");
                $this->SetXY(150, 35);

            }else if($this->pdfHorizontal == '1'){

                $this->SetFont("Arial", "B", 7);
                $this->SetXY(10, 10);
                $this->Cell(52, 24, "", 1, 0);
                $this->Image($imagenesGlobales["logoClienteReportes"], 11, 15, 50);
                $this->SetXY(62, 10);
                $this->Cell(140, 12, $this->textoTipo, 1, 0, "C");
                $this->SetXY(62, 22);
                $this->Cell(140, 12, $this->textoNombre, 1, 0, "C");
                $this->SetXY(202 , 10);
                $this->Cell(60, 6, $this->textoCodigo, "LTR", 0, "L");
                $this->SetXY(202, 16);
                $this->Cell(60, 6, $this->textoFecha, "LR", 0, "L");
                $this->SetXY(202, 22);
                $this->Cell(60, 6, $this->textoVersion, "LR", 0, "L");
                $this->SetXY(202, 28);
                $this->Cell(60, 6, $this->textoDependencia, "LBR", 0, "L");
                $this->SetXY(150, 35);
            }
        }
    }

    /*** Pie de p�gina ***/
    function Footer() {
        global $textos, $noImprimirPiePagina, $imprimirPiePagina;
        
        if (!$noImprimirPiePagina) {
            if (!$imprimirPiePagina) {
                $this->SetY(-15);
                $this->SetFont('Arial','I',7);
                $paginas    = str_replace("%n", $this->PageNo(), $textos["PAGINAS"]);
                $paginas    = str_replace("%t", "{nb}", $paginas);
                $this->Cell(0,10,$paginas,0,0,'C');
            } else {
                $this->SetY(-15);
                $this->SetFont('Times','I',12);
                $this->Cell(0,10,$this->PiePagina,0,0,'C');
            }
        }
    }

}
?>
