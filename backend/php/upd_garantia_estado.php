<?php
// avanza la orden de garantia al siguiente estado y lo anota en el historial
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['avanzar_garantia'])) {

    requiere_permiso_api('garantias');
    csrf_validar();

    $user_id  = (int)$_SESSION['id'];
    $idgar    = filter_input(INPUT_POST, 'idgar', FILTER_VALIDATE_INT) ?: 0;
    $actual   = (string)($_POST['estado_actual'] ?? '');
    $coment   = mb_substr(trim($_POST['comentario'] ?? ''), 0, 255);
    $volver   = '../garantias/ver.php?id=' . $idgar;

    try {
        $connect->beginTransaction();

        $b = $connect->prepare('SELECT idgar, estado FROM garantias WHERE idgar = ? FOR UPDATE');
        $b->execute([$idgar]);
        $g = $b->fetch();

        // si otro usuario ya la avanzo (o se reenvio el formulario) no se repite el paso
        if (!$g || $g->estado !== $actual) {
            $connect->rollBack();
            avisar_y_redirigir('Sin cambios', 'La orden ya cambió de estado. Revisa su historial.', 'warning', $volver);
            return;
        }

        $siguiente = garantia_siguiente_estado($g->estado);
        if (!$siguiente) {
            $connect->rollBack();
            avisar_y_redirigir('Sin cambios', 'La orden ya fue entregada.', 'warning', $volver);
            return;
        }

        $sets = ['estado = ?'];
        $vals = [$siguiente];
        $detalle = $coment;

        if ($siguiente === 'Enviado') {
            $idprov = filter_input(INPUT_POST, 'idprov', FILTER_VALIDATE_INT) ?: 0;
            $rma    = mb_substr(strtoupper(trim($_POST['rma_codigo'] ?? '')), 0, 50);
            $fecha  = trim($_POST['rma_fecha_envio'] ?? '');
            $f      = DateTime::createFromFormat('Y-m-d', $fecha);
            $prov   = $connect->prepare('SELECT nomprv FROM proveedores WHERE idprov = ?');
            $prov->execute([$idprov]);
            $nomprv = $prov->fetchColumn();
            if (!$nomprv || !$f || $f->format('Y-m-d') !== $fecha || $fecha > date('Y-m-d')) {
                $connect->rollBack();
                avisar_y_redirigir('Faltan datos', 'Elige el proveedor e indica la fecha de envío.', 'warning', $volver);
                return;
            }
            array_push($sets, 'idprov = ?', 'rma_codigo = ?', 'rma_fecha_envio = ?');
            array_push($vals, $idprov, $rma ?: null, $fecha);
            $detalle = trim('Enviado a ' . $nomprv . ($rma ? ' (RMA/guía ' . $rma . ')' : '') . '. ' . $coment);
        } elseif ($siguiente === 'Resuelto') {
            $resultado = (string)($_POST['resultado'] ?? '');
            $resDet    = mb_substr(trim($_POST['resultado_detalle'] ?? ''), 0, 255);
            if (!in_array($resultado, GARANTIA_RESULTADOS, true) || $resDet === '') {
                $connect->rollBack();
                avisar_y_redirigir('Faltan datos', 'Indica la respuesta de la marca y su detalle.', 'warning', $volver);
                return;
            }
            array_push($sets, 'resultado = ?', 'resultado_detalle = ?');
            array_push($vals, $resultado, $resDet);
            $detalle = trim($resultado . ': ' . $resDet . '. ' . $coment);
        } elseif ($siguiente === 'Entregado') {
            $sets[] = 'fecha_entrega = NOW()';
        }

        $vals[] = $idgar;
        $connect->prepare('UPDATE garantias SET ' . implode(', ', $sets) . ' WHERE idgar = ?')->execute($vals);
        $connect->prepare('INSERT INTO garantias_historial (idgar, estado, comentario, user_id, fecha) VALUES (?, ?, ?, ?, NOW())')
                ->execute([$idgar, $siguiente, mb_substr(rtrim($detalle, '. '), 0, 255) ?: null, $user_id]);

        $connect->commit();

        avisar_y_redirigir('Estado actualizado', 'La orden pasó a: ' . GARANTIA_ESTADOS[$siguiente] . '.', 'success', $volver);

    } catch (PDOException $e) {
        if ($connect->inTransaction()) {
            $connect->rollBack();
        }
        error_log('Error al actualizar la garantia: ' . $e->getMessage());
        avisar_y_redirigir('Error', 'No se pudo actualizar la orden.', 'error', $volver);
    }
}
