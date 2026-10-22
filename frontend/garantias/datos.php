<?php
// pagina del listado de garantias (TablaPaginada, responde json); ?estado= filtra
require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/../comun/acciones_tabla.php';
require_once __DIR__ . '/datos_filtros.php';

requiere_permiso_api('garantias');

[$where, $params] = where_garantias(filtro_garantias($_GET['estado'] ?? ''));

tabla_paginada(
    $connect,
    'g.idgar, g.fecha_ingreso, g.cliente_nombre, g.cliente_doc, g.comprobante, g.vigente, g.vence, g.estado, g.resultado, p.nomprd, pr.nomprv',
    "FROM garantias g INNER JOIN productos p ON p.idprod = g.idprod LEFT JOIN proveedores pr ON pr.idprov = g.idprov WHERE $where",
    $params,
    [0 => 'g.idgar', 1 => 'g.fecha_ingreso', 2 => 'g.cliente_nombre', 3 => 'p.nomprd', 4 => 'g.comprobante', 6 => 'g.estado', 'defecto' => 'g.idgar DESC'],
    ['g.cliente_nombre', 'g.cliente_doc', 'p.nomprd', 'g.comprobante', 'g.numero_serie'],
    function ($g) {
        $estado = '<span class="chip ' . (GARANTIA_CHIP[$g->estado] ?? '') . '">' . e(GARANTIA_ESTADOS[$g->estado] ?? $g->estado) . '</span>';
        foreach ([$g->resultado, $g->nomprv] as $detalle) {
            if ($detalle) {
                $estado .= '<small>' . e($detalle) . '</small>';
            }
        }
        return [
            '<b class="sin-salto">' . e(formato_garantia($g->idgar)) . '</b>',
            '<span class="sin-salto">' . date('d/m/Y', strtotime($g->fecha_ingreso)) . '</span>',
            celda_doble($g->cliente_nombre, (string)$g->cliente_doc),
            e($g->nomprd),
            '<span class="sin-salto">' . e($g->comprobante ?: '—') . '</span>',
            ((int)$g->vigente === 1 ? '<span class="chip ok">Vigente</span>' : '<span class="chip peligro">Vencida</span>')
                . '<small>hasta ' . date('d/m/Y', strtotime($g->vence)) . '</small>',
            $estado,
            accion_enlace('ver.php?id=' . (int)$g->idgar, 'la-eye', 'Ver y actualizar')
                . accion_enlace('orden.php?id=' . (int)$g->idgar, 'la-file-alt', 'Imprimir orden'),
        ];
    }
);
