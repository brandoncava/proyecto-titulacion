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

    $precio = filter_input(INPUT_POST, 'prdprec', FILTER_VALIDATE_FLOAT);
    $stock  = filter_input(INPUT_POST, 'prdstco', FILTER_VALIDATE_INT);
    $peso   = filter_input(INPUT_POST, 'prdpes', FILTER_VALIDATE_FLOAT);
    $garantia = filter_input(INPUT_POST, 'prdgar', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 120]]);
    $idmar  = filter_input(INPUT_POST, 'prdmarc', FILTER_VALIDATE_INT);
    $idcate = filter_input(INPUT_POST, 'prdcate', FILTER_VALIDATE_INT);

    if ($codpro === '' || $nomprd === '') {
        avisar_y_redirigir('Error!', 'El código y el nombre del producto son obligatorios.', 'error', '../productos/nuevo.php');
        return;
    }

    if ($precio === false || $precio < 0 || $stock === false || $stock < 0) {
        avisar_y_redirigir('Error!', 'El precio y el stock deben ser números no negativos.', 'error', '../productos/nuevo.php');
        return;
    }

    // el codigo no se puede repetir (ni con productos dados de baja)
    $existe = $connect->prepare('SELECT 1 FROM productos WHERE codpro = ? LIMIT 1');
    $existe->execute([$codpro]);
    if ($existe->fetch()) {
        avisar_y_redirigir('Error!', 'Ya existe un producto con ese código.', 'error', '../productos/nuevo.php');
        return;
    }

    if (!$idmar || !$idcate) {
        avisar_y_redirigir('Error!', 'Selecciona la marca y la categoría del producto.', 'error', '../productos/nuevo.php');
        return;
    }

    $foto = guardar_imagen_producto('foto', $errorImagen);

    if ($foto === false) {
        avisar_y_redirigir('Error!', $errorImagen, 'error', '../productos/nuevo.php');
        return;
    }

    try {
        $stmt = $connect->prepare(
            'INSERT INTO productos
                (codpro, nomprd, desprd, foto, precio, stock, garantia_meses, idmar, idcate, modelo, peso, state)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
        );

        $stmt->execute([
            $codpro, $nomprd, $desprd, $foto ?? '', $precio, $stock, $garantia === false || $garantia === null ? 12 : $garantia,
            $idmar, $idcate, $modelo, $peso ?: 0,
        ]);

        if ($stock > 0) {
            registrar_movimiento($connect, $connect->lastInsertId(), (int)$_SESSION['id'], 'Ajuste', $stock);
        }

        avisar_y_redirigir('Registrado!', 'Producto agregado correctamente.', 'success', '../productos/mostrar.php');

    } catch (PDOException $e) {
        error_log('Error al registrar el producto: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo guardar el producto. Comuníquese con el administrador.', 'error', '../productos/nuevo.php');
    }
}
