<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('catalogos');

if (isset($_POST['add_category'])) {

    csrf_validar();

    $nocate = trim($_POST['catnom'] ?? '');

    if ($nocate === '') {
        avisar_y_redirigir('Error!', 'Escribe el nombre de la categoria.', 'error', '../categorias/nuevo.php');
        return;
    }

    // si esta dada de baja se reactiva en vez de duplicar
    $buscar = $connect->prepare('SELECT idcate, state FROM categoria WHERE nocate = ? LIMIT 1');
    $buscar->execute([$nocate]);
    $existente = $buscar->fetch();

    if ($existente) {
        if ((int)$existente->state === 1) {
            avisar_y_redirigir('Error!', 'Esa categoria ya existe.', 'error', '../categorias/nuevo.php');
            return;
        }

        $connect->prepare('UPDATE categoria SET state = 1 WHERE idcate = ?')->execute([$existente->idcate]);
        avisar_y_redirigir('Reactivada!', 'La categoria estaba dada de baja y se volvio a activar.', 'success', '../categorias/mostrar.php');
        return;
    }

    $insertar = $connect->prepare('INSERT INTO categoria (nocate, state) VALUES (?, 1)');

    if ($insertar->execute([$nocate])) {
        avisar_y_redirigir('Registrado!', 'Se agrego correctamente.', 'success', '../categorias/mostrar.php');
    } else {
        avisar_y_redirigir('Error!', 'No se pudo guardar. Comuniquese con el administrador.', 'error', '../categorias/nuevo.php');
    }
}
