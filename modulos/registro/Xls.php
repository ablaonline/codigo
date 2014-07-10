<?php

class XLS {

    var $archivo;

    function __construct() {
        $this->archivo = pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
        return;
    }

    function enviar($nombre) {
        $this->archivo  .= pack("ss", 0x0A, 0x00);
        $abierto        = fopen($nombre, "w");
        $escribe        = fputs($abierto, $this->archivo);
        $cerrado        = fclose($abierto);
        return;
    }

    function escribirNumero($fila, $columna, $valor) {
        $this->archivo  .= pack("sssss", 0x203, 14, $fila, $columna, 0x0);
        $this->archivo  .= pack("d", $valor);
        return;
    }

    function escribirTexto($fila, $columna, $valor ) {
        $longitud = strlen($valor);
        $this->archivo  .= pack("ssssss", 0x204, 8 + $longitud, $fila, $columna, 0x0, $longitud);
        $this->archivo  .= $valor;
        return;
    }

}


?>
