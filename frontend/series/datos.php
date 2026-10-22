<?php
// pagina del listado de series (TablaPaginada, responde json)
require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/../comun/acciones_tabla.php';

requiere_permiso_api('series');

tabla_paginada(
    $connect,
    's.idserie, s.tipc, s.serie, s.ultimo, s.state, (SELECT COUNT(*) FROM orders o WHERE o.idserie = s.idserie) AS comprobantes',
    'FROM series s WHERE 1 = 1',
    [],
    [0 => 's.idserie', 1 => 's.tipc', 2 => 's.serie', 3 => 's.ultimo', 4 => 'comprobantes', 5 => 's.state', 'defecto' => 's.idserie DESC'],
    ['s.serie', 's.tipc'],
    fn($d) => [
        (string)(int)$d->idserie,
        e($d->tipc),
        '<b>' . e($d->serie) . '</b>',
        e(formato_comprobante($d->serie, $d->ultimo)),
        (string)(int)$d->comprobantes,
        (int)$d->state === 1 ? '<span class="chip ok">En uso</span>' : '<span class="chip">Inactiva</span>',
        (int)$d->state === 1 ? ''
            : accion_formulario(['idserie' => $d->idserie], 'cambiar_serie', 'la-check-circle', 'Usar esta serie',
                                'Esta serie pasará a usarse y se desactivará la actual. ¿Continuar?'),
    ]
);
