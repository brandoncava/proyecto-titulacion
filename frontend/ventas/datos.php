<?php
// pagina del listado de ventas (TablaPaginada, responde json)
require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/../comun/acciones_tabla.php';

requiere_permiso_api('ventas');

$anular = puede('ventas_anular');

tabla_paginada(
    $connect,
    'idord, tipc, serie, numero, placed_on, nomcl, doc_tipo, doc_num, total_products, total_price, anulada, motivo_anulacion',
    'FROM orders WHERE 1 = 1',
    [],
    [0 => 'idord', 1 => 'serie, numero', 2 => 'placed_on', 3 => 'nomcl', 5 => 'total_price', 'defecto' => 'idord DESC'],
    ['nomcl', 'doc_num', 'total_products', "CONCAT(serie, '-', LPAD(numero, 8, '0'))"],
    function ($d) use ($anular) {
        $anulada = (int)$d->anulada === 1;
        $acciones = accion_enlace('boleta.php?id=' . (int)$d->idord, 'la-file-alt', $d->tipc . ' en PDF');
        if ($anulada) {
            $acciones .= '<span class="chip peligro" title="' . e($d->motivo_anulacion) . '">ANULADA</span>';
        } elseif ($anular) {
            $acciones .= accion_formulario(['idord' => $d->idord], 'modificar_venta', 'la-pen', 'Modificar venta',
                             '¿Modificar esta venta? Sus productos se cargarán en el carrito; al finalizar, la venta se anulará y se emitirá un comprobante nuevo.')
                       . accion_formulario(['idord' => $d->idord, 'motivo' => ''], 'anular_venta', 'la-ban', 'Anular venta', 'fn:anularVenta', true);
        }
        return [
            ($anulada ? '<span data-fila-clase="fila-anulada"></span>' : '') . (int)$d->idord,
            celda_doble($d->tipc, $d->serie ? formato_comprobante($d->serie, $d->numero) : ''),
            '<span class="sin-salto">' . date('d/m/Y H:i', strtotime($d->placed_on)) . '</span>',
            celda_doble($d->nomcl, $d->doc_num ? $d->doc_tipo . ' ' . $d->doc_num : ''),
            e($d->total_products),
            '<span class="sin-salto">S/ ' . number_format($d->total_price, 2) . '</span>',
            $acciones,
        ];
    }
);
