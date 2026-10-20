<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('productos_ed');

if (isset($_POST['add_prodct'])) {

    csrf_validar();

    $codpro = trim($_POST['prdcod'] ?? '');
    $nomprd = trim($_POST['prdnom'] ?? '');
    $desprd = trim($_POST['prddes'] ?? '');
    $modelo = trim($_POST['prdmod'] ?? '');
    // el peso es texto libre con su unidad (ej. "1.6 kg")
    $peso   = mb_substr(trim($_POST['prdpes'] ?? ''), 0, 60);

    $precio = filter_input(INPUT_POST, 'prdprec', FILTER_VALIDATE_FLOAT);
    $stock  = filter_input(INPUT_POST, 'prdstock', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $garantia = filter_input(INPUT_POST, 'prdgar', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 120]]);
    if ($garantia === false || $garantia === null) {
        $garantia = 12;
    }
    $idmar  = filter_input(INPUT_POST, 'prdmarc', FILTER_VALIDATE_INT);
    $idcate = filter_input(INPUT_POST, 'prdcate', FILTER_VALIDATE_INT);
    $volver = '../productos/nuevo.php';

    if ($codpro === '' || $nomprd === '') {
        avisar_y_redirigir('Error!', 'El código y el nombre del producto son obligatorios.', 'error', $volver);
        return;
    }

    if ($precio === false || $precio === null || $precio < 0 || $stock === false || $stock === null) {
        avisar_y_redirigir('Error!', 'El precio y el stock deben ser números no negativos.', 'error', $volver);
        return;
    }

    // el codigo no se puede repetir (ni con productos dados de baja)
    $existe = $connect->prepare('SELECT 1 FROM productos WHERE codpro = ? LIMIT 1');
    $existe->execute([$codpro]);
    if ($existe->fetch()) {
        avisar_y_redirigir('Error!', 'Ya existe un producto con ese código.', 'error', $volver);
        return;
    }

    if (!$idmar || !$idcate) {
        avisar_y_redirigir('Error!', 'Selecciona la marca y la categoría del producto.', 'error', $volver);
        return;
    }

    $foto = guardar_imagen_producto('foto', $errorImagen);

    if ($foto === false) {
        avisar_y_redirigir('Error!', $errorImagen, 'error', $volver);
        return;
    }
    if ($foto === null) {
        avisar_y_redirigir('Falta la imagen', 'Sube una imagen del producto.', 'warning', $volver);
        return;
    }

    try {
        $connect->beginTransaction();

        $connect->prepare(
            'INSERT INTO productos
                (codpro, nomprd, desprd, foto, precio, stock, garantia_meses, idmar, idcate, modelo, peso, state)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
        )->execute([$codpro, $nomprd, $desprd, $foto, $precio, $stock, $garantia, $idmar, $idcate, $modelo, $peso]);

        if ($stock > 0) {
            registrar_movimiento($connect, (int)$connect->lastInsertId(), (int)$_SESSION['id'], 'Saldo inicial', $stock);
        }

        $connect->commit();

        avisar_y_redirigir('Registrado!', 'Producto agregado correctamente.', 'success', '../productos/mostrar.php');

    } catch (PDOException $e) {
        if ($connect->inTransaction()) {
            $connect->rollBack();
        }
        // si no se guardo, la imagen recien subida sobra
        @unlink(__DIR__ . '/../img/subidas/' . $foto);
        error_log('Error al registrar el producto: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo guardar el producto. Comuníquese con el administrador.', 'error', $volver);
    }
}
