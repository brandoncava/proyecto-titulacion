<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('catalogos');

$nombre = trim($_POST['trat'] ?? '');

if ($nombre === '') {
    http_response_code(400);
    exit('Indica el nombre de la marca.');
}

// si estaba dada de baja se reactiva en vez de duplicar
$buscar = $connect->prepare('SELECT idmar, state FROM marca WHERE nomarc = ? LIMIT 1');
$buscar->execute([$nombre]);
$existente = $buscar->fetch();

if ($existente) {
    if ((int)$existente->state === 1) {
        exit('Esa marca ya esta registrada');
    }
    $connect->prepare('UPDATE marca SET state = 1 WHERE idmar = ?')->execute([$existente->idmar]);
    exit('Marca reactivada correctamente');
}

$insertar = $connect->prepare('INSERT INTO marca (nomarc, state) VALUES (?, 1)');
$insertar->execute([$nombre]);

echo 'Agregado correctamente';
