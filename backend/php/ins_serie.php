<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('series');

if (isset($_POST['ins_serie'])) {

    csrf_validar();

    $tipc  = trim($_POST['tipc'] ?? '');
    $serie = strtoupper(trim($_POST['serie'] ?? ''));

    if (!in_array($tipc, COMPROBANTES, true)) {
        avisar_y_redirigir('Error!', 'Elige si la serie es de boleta o de factura.', 'error', '../series/nuevo.php');
        return;
    }

    // la serie tiene 4 caracteres y empieza con B (boleta) o F (factura)
    $inicial = ($tipc === 'Factura') ? 'F' : 'B';
    if (!preg_match('/^' . $inicial . '[A-Z0-9]{3}$/', $serie)) {
        avisar_y_redirigir('Error!', 'La serie debe tener 4 caracteres y empezar con ' . $inicial . ' (ejm: ' . $inicial . '002).', 'error', '../series/nuevo.php');
        return;
    }

    $buscar = $connect->prepare('SELECT 1 FROM series WHERE tipc = ? AND serie = ? LIMIT 1');
    $buscar->execute([$tipc, $serie]);

    if ($buscar->fetch()) {
        avisar_y_redirigir('Error!', 'Esa serie ya existe.', 'error', '../series/nuevo.php');
        return;
    }

    // solo una serie activa por comprobante: la nueva queda inactiva si ya hay una en uso
    $enUso = $connect->prepare('SELECT 1 FROM series WHERE tipc = ? AND state = 1 LIMIT 1');
    $enUso->execute([$tipc]);
    $state = $enUso->fetch() ? 0 : 1;

    $connect->prepare('INSERT INTO series (tipc, serie, ultimo, state) VALUES (?, ?, 0, ?)')->execute([$tipc, $serie, $state]);

    $aviso = $state === 1 ? 'Se agregó correctamente.' : 'Se agregó correctamente. Queda inactiva: actívala cuando quieras usarla.';
    avisar_y_redirigir('Registrado!', $aviso, 'success', '../series/mostrar.php');
}
