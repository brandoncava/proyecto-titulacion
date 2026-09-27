<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('catalogos');

if (isset($_POST['ins_marca'])) {

    csrf_validar();

    $nombre = trim($_POST['nommar'] ?? '');

    if ($nombre === '') {
        avisar_y_redirigir('Error!', 'Escribe el nombre de la marca.', 'error', '../marcas/nuevo.php');
        return;
    }

    // si esta dada de baja se reactiva en vez de duplicar
    $buscar = $connect->prepare('SELECT idmar, state FROM marca WHERE nomarc = ? LIMIT 1');
    $buscar->execute([$nombre]);
    $existente = $buscar->fetch();

    if ($existente) {
        if ((int)$existente->state === 1) {
            avisar_y_redirigir('Error!', 'Esa marca ya existe.', 'error', '../marcas/nuevo.php');
            return;
        }

        $connect->prepare('UPDATE marca SET state = 1 WHERE idmar = ?')->execute([$existente->idmar]);
        avisar_y_redirigir('Reactivada!', 'La marca estaba dada de baja y se volvió a activar.', 'success', '../marcas/mostrar.php');
        return;
    }

    $connect->prepare('INSERT INTO marca (nomarc, state) VALUES (?, 1)')->execute([$nombre]);
    avisar_y_redirigir('Registrado!', 'Se agregó correctamente.', 'success', '../marcas/mostrar.php');
}
