<?php
// pagina del listado de clientes (TablaPaginada, responde json)
require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/../comun/acciones_tabla.php';

requiere_permiso_api('clientes');

tabla_paginada(
    $connect,
    'idcli, tipd, nudoc, nocl, apcl, telfcl, emailcl, acepta_promos',
    'FROM clientes WHERE state = 1',
    [],
    [0 => 'idcli', 1 => 'nocl', 2 => 'telfcl', 'defecto' => 'idcli DESC'],
    ['nocl', 'apcl', 'nudoc', 'telfcl', 'emailcl'],
    fn($d) => [
        (string)(int)$d->idcli,
        celda_doble(trim($d->nocl . ' ' . $d->apcl), strtoupper($d->tipd) . ' ' . $d->nudoc),
        '<b>' . ($d->telfcl !== '' ? e($d->telfcl) : '—') . '</b><small>' . e($d->emailcl ?? '')
            . ((int)$d->acepta_promos === 1 ? ' <span class="las la-bullhorn" title="Acepta promociones"></span>' : '') . '</small>',
        accion_enlace('editar.php?id=' . (int)$d->idcli, 'la-pen', 'Editar')
            . accion_baja('idcli', $d->idcli, 'delete_customer', '¿Dar de baja este cliente?'),
    ]
);
