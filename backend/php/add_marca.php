<?php
// alta rapida de marca desde el modal de productos/nuevo.php (AJAX, responde JSON)
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('catalogos');
csrf_validar();

header('Content-Type: application/json; charset=utf-8');

function responder($ok, $mensaje, $idmar = null)
{
    echo json_encode(['ok' => $ok, 'mensaje' => $mensaje, 'idmar' => $idmar], JSON_UNESCAPED_UNICODE);
    exit;
}

$nombre = trim($_POST['trat'] ?? '');

if ($nombre === '') {
    responder(false, 'Indica el nombre de la marca.');
}

// si estaba dada de baja se reactiva en vez de duplicar
$buscar = $connect->prepare('SELECT idmar, state FROM marca WHERE nomarc = ? LIMIT 1');
$buscar->execute([$nombre]);
$existente = $buscar->fetch();

if ($existente) {
    if ((int)$existente->state === 1) {
        responder(false, 'Esa marca ya está registrada.', (int)$existente->idmar);
    }
    $connect->prepare('UPDATE marca SET state = 1 WHERE idmar = ?')->execute([$existente->idmar]);
    responder(true, 'La marca estaba dada de baja y se volvió a activar.', (int)$existente->idmar);
}

$connect->prepare('INSERT INTO marca (nomarc, state) VALUES (?, 1)')->execute([$nombre]);

responder(true, 'Marca agregada correctamente.', (int)$connect->lastInsertId());
