<?php
// pagina del listado de compras (TablaPaginada, responde json)
require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/../comun/acciones_tabla.php';

requiere_permiso_api('compras');

$anular = puede('compras_anular');

tabla_paginada(
    $connect,
    'op.idordpur, op.tipc, op.serie_doc, op.numero_doc, op.placed_on, op.total_products, op.total_price, op.anulada, op.motivo_anulacion, pr.nomprv, pr.rucprv',
    'FROM orders_purchase op INNER JOIN proveedores pr ON pr.idprov = op.idprov WHERE 1 = 1',
    [],
    [0 => 'op.idordpur', 1 => 'op.serie_doc, op.numero_doc', 2 => 'op.placed_on', 3 => 'pr.nomprv', 5 => 'op.total_price', 'defecto' => 'op.idordpur DESC'],
    ['pr.nomprv', 'pr.rucprv', 'op.total_products', "CONCAT(op.serie_doc, '-', op.numero_doc)"],
    function ($d) use ($anular) {
        $anulada = (int)$d->anulada === 1;
        $acciones = accion_enlace('comprobante.php?id=' . (int)$d->idordpur, 'la-file-alt', 'Comprobante en PDF');
        if ($anulada) {
            $acciones .= '<span class="chip peligro" title="' . e($d->motivo_anulacion) . '">ANULADA</span>';
        } elseif ($anular) {
            $acciones .= accion_formulario(['idordpur' => $d->idordpur], 'modificar_compra', 'la-pen', 'Modificar compra',
                             '¿Modificar esta compra? Sus productos se cargarán en el carrito de compras para editarlos.')
                       . accion_formulario(['idordpur' => $d->idordpur, 'motivo' => ''], 'anular_compra', 'la-ban', 'Anular compra', 'fn:anularCompra', true);
        }
        return [
            ($anulada ? '<span data-fila-clase="fila-anulada"></span>' : '') . (int)$d->idordpur,
            celda_doble($d->tipc, $d->serie_doc ? $d->serie_doc . '-' . $d->numero_doc : ''),
            '<span class="sin-salto">' . date('d/m/Y H:i', strtotime($d->placed_on)) . '</span>',
            celda_doble($d->nomprv, 'RUC ' . $d->rucprv),
            e($d->total_products),
            '<span class="sin-salto">S/ ' . number_format($d->total_price, 2) . '</span>',
            $acciones,
        ];
    }
);
