<?php
require_once __DIR__ . '/../config/app.php';

requiere_permiso_api('series');

// activa una serie; solo hay una activa por comprobante, la anterior se desactiva sola
if (isset($_POST['cambiar_serie'])) {

    csrf_validar();

    $idserie = filter_input(INPUT_POST, 'idserie', FILTER_VALIDATE_INT);

    $buscar = $connect->prepare('SELECT idserie, tipc, state FROM series WHERE idserie = ? LIMIT 1');
    $buscar->execute([$idserie]);
    $serie = $buscar->fetch();

    if (!$serie) {
        avisar_y_redirigir('Error!', 'La serie no existe.', 'error', '../series/mostrar.php');
        return;
    }

    if ((int)$serie->state === 1) {
        avisar_y_redirigir('Sin cambios', 'Esta serie ya está en uso. Para cambiarla, activa otra serie de ' . $serie->tipc . '.', 'warning', '../series/mostrar.php');
        return;
    }

    try {
        $connect->beginTransaction();

        $connect->prepare('UPDATE series SET state = 0 WHERE tipc = ?')->execute([$serie->tipc]);
        $connect->prepare('UPDATE series SET state = 1 WHERE idserie = ?')->execute([$serie->idserie]);

        $connect->commit();

        avisar_y_redirigir('Actualizado!', 'La serie quedó activa y se usará en las siguientes ventas.', 'success', '../series/mostrar.php');

    } catch (PDOException $e) {
        if ($connect->inTransaction()) {
            $connect->rollBack();
        }
        error_log('Error al cambiar la serie: ' . $e->getMessage());
        avisar_y_redirigir('Error', 'No se pudo cambiar la serie. No se modificó nada.', 'error', '../series/mostrar.php');
    }
}
