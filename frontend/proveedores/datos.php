<?php
// pagina del listado de proveedores (TablaPaginada, responde json)
require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/../comun/acciones_tabla.php';

requiere_permiso_api('proveedores');

tabla_paginada(
    $connect,
    'idprov, rucprv, nomprv, corrprv',
    'FROM proveedores WHERE state = 1',
    [],
    [0 => 'idprov', 1 => 'nomprv', 2 => 'corrprv', 'defecto' => 'idprov DESC'],
    ['nomprv', 'rucprv', 'corrprv'],
    fn($d) => [
        (string)(int)$d->idprov,
        celda_doble($d->nomprv, 'RUC ' . $d->rucprv),
        $d->corrprv !== '' ? e($d->corrprv) : '—',
        accion_enlace('editar.php?id=' . (int)$d->idprov, 'la-pen', 'Editar')
            . accion_baja('idprov', $d->idprov, 'delete_supplier', '¿Dar de baja este proveedor?'),
    ]
);
