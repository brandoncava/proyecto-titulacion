<?php
// funciones compartidas: escape de salida y csrf

// opciones validas de los formularios de pago (ventas/compras checkout.php)
const METODOS_PAGO = ['Contado', 'Tarjeta'];
// comprobantes de venta (ruc = factura, dni o sin dato = boleta)
const COMPROBANTES = ['Boleta', 'Factura'];
// boleta por mas de este monto pide el dni del comprador
const LIMITE_BOLETA_SIN_DNI = 700.00;
// comprobantes que entrega el proveedor en las compras
const COMPROBANTES_COMPRA = ['Factura', 'Boleta'];

// igv: el precio de venta lo incluye, el costo de compra va sin igv
const IGV_TASA = 0.18;

// del total con igv saca [base, igv]
function igv_desglosar($total)
{
    $total = round((float)$total, 2);
    $base  = round($total / (1 + IGV_TASA), 2);
    return [$base, round($total - $base, 2)];
}

// de la base sin igv saca [base, igv, total]
function igv_agregar($base)
{
    $base = round((float)$base, 2);
    $igv  = round($base * IGV_TASA, 2);
    return [$base, $igv, round($base + $igv, 2)];
}

function validar_dni($numero)
{
    return (bool)preg_match('/^\d{8}$/', (string)$numero);
}

// ruc: 11 digitos, prefijo valido y digito verificador
function validar_ruc($numero)
{
    $numero = (string)$numero;
    if (!preg_match('/^\d{11}$/', $numero)) {
        return false;
    }
    if (!in_array(substr($numero, 0, 2), ['10', '15', '16', '17', '20'], true)) {
        return false;
    }
    $pesos = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
    $suma = 0;
    for ($i = 0; $i < 10; $i++) {
        $suma += (int)$numero[$i] * $pesos[$i];
    }
    $dv = 11 - ($suma % 11);
    if ($dv === 10) {
        $dv = 0;
    } elseif ($dv === 11) {
        $dv = 1;
    }
    return $dv === (int)$numero[10];
}

// ej: B001-00000001
function formato_comprobante($serie, $numero)
{
    return $serie . '-' . str_pad((string)$numero, 8, '0', STR_PAD_LEFT);
}

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

// anota un movimiento en el kardex; se llama justo despues de cambiar el stock, dentro de la misma transaccion
// cantidad: positiva si entra stock, negativa si sale
function registrar_movimiento($connect, $idprod, $user_id, $tipo, $cantidad, $idord = null, $idordpur = null)
{
    $stock = $connect->prepare('SELECT stock FROM productos WHERE idprod = ?');
    $stock->execute([$idprod]);

    $connect->prepare(
        'INSERT INTO movimientos_inventario (idprod, user_id, tipo, cantidad, stock_resultante, idord, idordpur, fecha)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    )->execute([$idprod, $user_id, $tipo, $cantidad, (int)$stock->fetchColumn(), $idord, $idordpur]);
}
