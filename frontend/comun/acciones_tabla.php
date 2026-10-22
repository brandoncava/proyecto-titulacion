<?php
// botones de accion de las filas de los listados (columna "Acciones"). Devuelven html.
// Los formularios se envian a la misma pagina del listado, que incluye su manejador de backend/php

// boton con enlace, ej. editar
function accion_enlace($url, $icono, $titulo)
{
    return '<a href="' . e($url) . '" class="btn-icono" title="' . e($titulo) . '"><span class="las ' . e($icono) . '"></span></a>';
}

// boton que envia un formulario POST con el id de la fila (dar de baja, anular, modificar...)
//   $campos   [name => valor] ocultos, ej. ['idcate' => 5]
//   $boton    name del boton que busca el manejador, ej. 'delete_category'
//   $alEnviar js de onsubmit: un texto se usa como confirm(); 'fn:nombre' llama nombre(this)
function accion_formulario($campos, $boton, $icono, $titulo, $alEnviar, $peligro = false)
{
    $onsubmit = strpos($alEnviar, 'fn:') === 0
        ? 'return ' . substr($alEnviar, 3) . '(this);'
        : 'return confirm(' . json_encode($alEnviar, JSON_UNESCAPED_UNICODE) . ');';
    $html = '<form method="POST" action="" onsubmit="' . e($onsubmit) . '">' . csrf_campo();
    foreach ($campos as $nombre => $valor) {
        $html .= '<input type="hidden" name="' . e($nombre) . '" value="' . e($valor) . '">';
    }
    return $html . '<button type="submit" name="' . e($boton) . '" class="btn-icono' . ($peligro ? ' peligro' : '') . '" title="' . e($titulo) . '">'
         . '<span class="las ' . e($icono) . '"></span></button></form>';
}

// boton "dar de baja" con confirmacion
function accion_baja($campo, $id, $boton, $confirmar)
{
    return accion_formulario([$campo => $id], $boton, 'la-trash-alt', 'Dar de baja', $confirmar, true);
}

// texto principal con un detalle chico debajo (ej. nombre y documento)
function celda_doble($principal, $detalle = '')
{
    return '<b>' . e($principal) . '</b>' . ($detalle !== '' ? '<small class="sin-salto">' . e($detalle) . '</small>' : '');
}
