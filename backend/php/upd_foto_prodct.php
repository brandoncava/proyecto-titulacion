<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('productos_ed');

if (isset($_POST['upd_foto_prodct'])) {

    csrf_validar();

    $idprod = filter_input(INPUT_POST, 'prdid', FILTER_VALIDATE_INT);

    if (!$idprod) {
        avisar_y_redirigir('Error!', 'Producto no válido.', 'error', '../productos/mostrar.php');
        return;
    }

    $foto = guardar_imagen_producto('foto', $errorImagen);

    // que no quede apuntando a un archivo que no se subio
    if ($foto === false) {
        avisar_y_redirigir('Error!', $errorImagen, 'error', '../productos/mostrar.php');
        return;
    }

    if ($foto === null) {
        avisar_y_redirigir('Sin cambios', 'No seleccionaste ninguna imagen.', 'warning', '../productos/mostrar.php');
        return;
    }

    try {
        $anterior = $connect->prepare('SELECT foto FROM productos WHERE idprod = ?');
        $anterior->execute([$idprod]);
        $fotoAnterior = $anterior->fetchColumn();

        $statement = $connect->prepare('UPDATE productos SET foto = :foto WHERE idprod = :idprod LIMIT 1');
        $statement->execute([':foto' => $foto, ':idprod' => $idprod]);

        // borra la imagen vieja
        if ($fotoAnterior && $fotoAnterior !== $foto) {
            $ruta = __DIR__ . '/../img/subidas/' . basename($fotoAnterior);
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }

        avisar_y_redirigir('Actualizado!', 'Imagen actualizada correctamente.', 'success', '../productos/mostrar.php');

    } catch (PDOException $e) {
        error_log('Error al actualizar la foto del producto: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo actualizar la imagen.', 'error', '../productos/mostrar.php');
    }
}
