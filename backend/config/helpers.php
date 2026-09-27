<?php
// funciones compartidas: escape de salida y csrf

function e($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_campo()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_validar()
{
    $recibido = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    $guardado = $_SESSION['csrf_token'] ?? '';

    if ($guardado === '' || $recibido === '' || !hash_equals($guardado, $recibido)) {
        http_response_code(403);
        exit('Sesión expirada o solicitud no válida. Vuelve a cargar la página.');
    }
}

function avisar_y_redirigir($titulo, $texto, $tipo, $destino)
{
    echo '<script type="text/javascript">
swal(' . json_encode($titulo) . ', ' . json_encode($texto) . ', ' . json_encode($tipo) . ').then(function() {
    window.location = ' . json_encode($destino) . ';
});
</script>';
}

// null = no se envio archivo, false = error (queda en $error), string = nombre guardado
function guardar_imagen_producto($campo, &$error)
{
    $error = null;

    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $archivo = $_FILES[$campo];

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $error = 'No se pudo subir la imagen (código ' . $archivo['error'] . ').';
        return false;
    }

    if ($archivo['size'] > 5 * 1024 * 1024) {
        $error = 'La imagen supera el límite de 5 MB.';
        return false;
    }

    // el tipo se saca del contenido real, no de la extension ni el content-type
    $permitidos = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];

    $info = @getimagesize($archivo['tmp_name']);

    if ($info === false || !isset($permitidos[$info[2]])) {
        $error = 'El archivo no es una imagen válida (JPG, PNG, GIF o WEBP).';
        return false;
    }

    // nombre random para que no lo adivinen
    $nombre = bin2hex(random_bytes(16)) . '.' . $permitidos[$info[2]];
    $destino = __DIR__ . '/../img/subidas/' . $nombre;

    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        $error = 'No se pudo guardar la imagen en el servidor.';
        return false;
    }

    return $nombre;
}
