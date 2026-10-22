<?php
// funciones compartidas: escape de salida y csrf

// opciones validas de los formularios de pago (ventas/compras checkout.php)
const METODOS_PAGO = ['Contado', 'Tarjeta', 'Yape', 'Plin', 'Transferencia'];
// estos medios piden el numero de operacion
const METODOS_CON_OPERACION = ['Yape', 'Plin', 'Transferencia'];

// estados de la orden de garantia, en orden: clave => texto
const GARANTIA_ESTADOS = [
    'Ingresado'     => 'Ingresado / Recibido',
    'Enviado'       => 'Enviado a marca / proveedor',
    'En evaluacion' => 'En evaluación por la marca',
    'Resuelto'      => 'Resuelto por la marca',
    'Listo'         => 'Listo para entrega',
    'Entregado'     => 'Completado / Entregado',
];
const GARANTIA_RESULTADOS = ['Reparado', 'Reemplazado', 'Rechazado'];
// color de la etiqueta (.chip) de cada estado de garantia
const GARANTIA_CHIP = [
    'Ingresado' => 'info', 'Enviado' => 'alerta', 'En evaluacion' => 'alerta',
    'Resuelto' => 'morado', 'Listo' => 'morado', 'Entregado' => 'ok',
];
// desde cuantas unidades se avisa "stock bajo" (listado de productos y reporte de inventario)
const STOCK_BAJO = 5;
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

// pagos del checkout de ventas (pago_metodo[], pago_monto[], pago_operacion[]);
// la suma tiene que ser el total. Devuelve la lista o null y deja el motivo en $error
function leer_pagos($total, &$error)
{
    $metodos     = (array)($_POST['pago_metodo'] ?? []);
    $montos      = (array)($_POST['pago_monto'] ?? []);
    $operaciones = (array)($_POST['pago_operacion'] ?? []);
    $pagos = [];
    $suma  = 0;

    foreach ($metodos as $i => $metodo) {
        $metodo = trim((string)$metodo);
        $monto  = round((float)str_replace(',', '.', (string)($montos[$i] ?? '')), 2);
        $op     = strtoupper(trim((string)($operaciones[$i] ?? '')));

        if ($metodo === '' && $monto == 0) {
            continue;   // fila vacia
        }
        if (!in_array($metodo, METODOS_PAGO, true)) {
            $error = 'Elige el medio de cada pago.';
            return null;
        }
        if ($monto <= 0) {
            $error = 'Cada pago debe tener un monto mayor a cero.';
            return null;
        }
        if (in_array($metodo, METODOS_CON_OPERACION, true)) {
            if (!preg_match('/^[A-Z0-9-]{4,30}$/', $op)) {
                $error = 'Ingresa el número de operación del pago con ' . $metodo . ' (4 a 30 letras o números).';
                return null;
            }
        } else {
            $op = null;
        }

        $pagos[] = ['metodo' => $metodo, 'monto' => $monto, 'num_operacion' => $op];
        $suma += $monto;
    }

    if (!$pagos) {
        $error = 'Agrega al menos un pago.';
        return null;
    }
    if (abs(round($suma, 2) - round((float)$total, 2)) >= 0.01) {
        $error = 'Los pagos suman S/ ' . number_format($suma, 2) . ' y el total es S/ ' . number_format($total, 2) . '.';
        return null;
    }
    return $pagos;
}

// lo que se guarda en orders.method: el medio si es uno solo, "Mixto" si son varios
function resumen_metodo($pagos)
{
    $distintos = array_unique(array_column($pagos, 'metodo'));
    return count($distintos) === 1 ? reset($distintos) : 'Mixto';
}

// anula una venta dentro de una transaccion ya abierta: devuelve el stock al
// inventario (con su movimiento en el kardex) y la marca anulada.
// Devuelve cuantas lineas de detalle tenia (0 = venta antigua sin detalle)
function anular_venta_tx($connect, $idord, $motivo, $user_id)
{
    $detalle = $connect->prepare('SELECT idprod, cantidad FROM orders_detalle WHERE idord = ?');
    $detalle->execute([$idord]);
    $lineas = $detalle->fetchAll(PDO::FETCH_ASSOC);

    $devolver = $connect->prepare('UPDATE productos SET stock = stock + ? WHERE idprod = ?');
    foreach ($lineas as $linea) {
        $devolver->execute([(int)$linea['cantidad'], (int)$linea['idprod']]);
        registrar_movimiento($connect, (int)$linea['idprod'], $user_id, 'Anulacion', (int)$linea['cantidad'], $idord);
    }

    $connect->prepare(
        "UPDATE orders
            SET anulada = 1, anulada_on = NOW(), motivo_anulacion = ?, payment_status = 'Anulado'
          WHERE idord = ?"
    )->execute([mb_substr($motivo, 0, 255), $idord]);

    return count($lineas);
}

// datos del formulario de cliente (clientes/formulario.php), ya validados:
// [tipd, nudoc, nocl, apcl, telfcl, emailcl, promos] o null con el motivo en $error
function leer_datos_cliente(&$error)
{
    $error = '';
    $tipd   = strtoupper(trim($_POST['tipcl'] ?? ''));
    $nudoc  = preg_replace('/\D/', '', (string)($_POST['numcl'] ?? ''));
    $nocl   = mb_substr(trim($_POST['nomcl'] ?? ''), 0, 150);
    $apcl   = mb_substr(trim($_POST['apecl'] ?? ''), 0, 60);
    $telfcl = preg_replace('/\D/', '', (string)($_POST['telcl'] ?? ''));
    $email  = leer_correo($_POST['emailcl'] ?? '', 100);   // null = no valido
    $promos = !empty($_POST['promos']) ? 1 : 0;

    if ($tipd !== 'DNI' && $tipd !== 'RUC') {
        $error = 'Elige el tipo de documento (DNI o RUC).';
    } elseif ($tipd === 'DNI' && !validar_dni($nudoc)) {
        $error = 'El DNI debe tener 8 dígitos.';
    } elseif ($tipd === 'RUC' && !validar_ruc($nudoc)) {
        $error = 'RUC no válido: debe tener 11 dígitos y un dígito verificador correcto.';
    } elseif ($nocl === '') {
        $error = $tipd === 'RUC' ? 'Escribe la razón social.' : 'Escribe los nombres del cliente.';
    } elseif ($telfcl !== '' && !preg_match('/^\d{9}$/', $telfcl)) {
        $error = 'El teléfono debe tener 9 dígitos.';
    } elseif ($email === null) {
        $error = 'Revisa el correo electrónico.';
    } elseif ($promos && $email === '') {
        $error = 'Para recibir promociones el cliente debe dar su correo.';
    }
    if ($error !== '') {
        return null;
    }
    // una empresa no tiene apellidos
    if ($tipd === 'RUC') {
        $apcl = '';
    }
    return [$tipd, $nudoc, $nocl, $apcl, $telfcl, $email !== '' ? $email : null, $promos];
}

// datos de un usuario del sistema (accesos/formulario.php): [nombre, username, correo]
// o null con el motivo en $error. Los largos son los de la tabla usuarios
function leer_datos_usuario($nombre, $username, $correo, &$error)
{
    $error    = '';
    $nombre   = trim((string)$nombre);
    $username = trim((string)$username);
    $correo   = leer_correo($correo, 35);

    if ($nombre === '' || mb_strlen($nombre) > 35) {
        $error = 'Escribe el nombre completo (hasta 35 caracteres).';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{3,25}$/', $username)) {
        $error = 'El usuario debe tener de 3 a 25 letras, números, punto, guion o guion bajo, sin espacios.';
    } elseif ($correo === null || $correo === '') {
        $error = 'Revisa el correo electrónico (hasta 35 caracteres).';
    }
    return $error === '' ? [$nombre, $username, $correo] : null;
}

// ej: GAR-000001
function formato_garantia($idgar)
{
    return 'GAR-' . str_pad((string)$idgar, 6, '0', STR_PAD_LEFT);
}

// siguiente estado de la orden de garantia, null si ya se entrego
function garantia_siguiente_estado($estado)
{
    $claves = array_keys(GARANTIA_ESTADOS);
    $i = array_search($estado, $claves, true);
    return ($i === false || $i === count($claves) - 1) ? null : $claves[$i + 1];
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

// aviso con sweetalert y vuelta a $destino. Si la peticion vino por fetch (campo ajax=1)
// responde json con el aviso y $datos, y no cambia de pagina
function avisar_y_redirigir($titulo, $texto, $tipo, $destino, $datos = [])
{
    if (!empty($_POST['ajax'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $tipo === 'success', 'titulo' => $titulo, 'texto' => $texto, 'tipo' => $tipo] + $datos, JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo '<script type="text/javascript">
swal(' . json_encode($titulo) . ', ' . json_encode($texto) . ', ' . json_encode($tipo) . ').then(function() {
    window.location = ' . json_encode($destino) . ';
});
</script>';
}

// los dos carritos del sistema. El de venta cobra el precio del producto (con igv) y no
// pasa del stock; el de compra guarda el costo sin igv que escribe el usuario.
//   sesion: clave de $_SESSION con la venta/compra que se esta modificando
const CARRITOS = [
    'venta'  => ['tabla' => 'cart',          'id' => 'idv',   'permiso' => 'ventas',  'sesion' => 'modifica_venta'],
    'compra' => ['tabla' => 'cart_purchase', 'id' => 'idcpr', 'permiso' => 'compras', 'sesion' => 'modifica_compra'],
];

// unidades por producto de la venta que se esta modificando: siguen descontadas del stock hasta
// confirmar (ahi se anula la original y vuelven), asi que tambien se pueden vender. [idprod => unidades]
function unidades_venta_modificada($connect)
{
    $idord = (int)($_SESSION['modifica_venta']['idord'] ?? 0);
    if (!$idord) {
        return [];
    }
    $q = $connect->prepare(
        'SELECT d.idprod, SUM(d.cantidad) FROM orders_detalle d INNER JOIN orders o ON o.idord = d.idord
          WHERE d.idord = ? AND o.anulada = 0 GROUP BY d.idprod'
    );
    $q->execute([$idord]);
    return array_map('intval', $q->fetchAll(PDO::FETCH_KEY_PAIR));
}

// stock que se puede vender de un producto: el actual mas lo que devuelve la venta que se modifica
function stock_para_venta($connect, $idprod, $stockActual)
{
    return (int)$stockActual + (unidades_venta_modificada($connect)[(int)$idprod] ?? 0);
}

// lineas del carrito del usuario con los datos actuales del producto
function carrito_lineas($connect, $tipo, $user_id)
{
    $c = CARRITOS[$tipo];
    $consulta = $connect->prepare(
        "SELECT c.{$c['id']} AS idlinea, c.idprod, c.name, c.price, c.quantity, p.codpro, p.nomprd, p.stock
           FROM {$c['tabla']} c INNER JOIN productos p ON p.idprod = c.idprod
          WHERE c.user_id = ? ORDER BY c.{$c['id']}"
    );
    $consulta->execute([(int)$user_id]);
    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

// agrega el producto al carrito; si ya estaba, suma las unidades (y en compras toma el costo nuevo).
// $stock: tope de unidades (ventas) o null sin tope (compras). Avisa y redirige
function carrito_agregar($connect, $tipo, $user_id, $idprod, $nombre, $precio, $cantidad, $stock = null)
{
    $c = CARRITOS[$tipo];
    $buscar = $connect->prepare("SELECT {$c['id']} AS idlinea, quantity FROM {$c['tabla']} WHERE idprod = ? AND user_id = ?");
    $buscar->execute([$idprod, $user_id]);
    $linea = $buscar->fetch();

    if (!$linea) {
        $connect->prepare("INSERT INTO {$c['tabla']} (user_id, idprod, name, price, quantity) VALUES (?, ?, ?, ?, ?)")
                ->execute([$user_id, $idprod, $nombre, $precio, $cantidad]);
        avisar_y_redirigir('¡Registrado!', 'Agregado: ' . $nombre . ' (' . (int)$cantidad . ')', 'success', 'cart.php',
                           carrito_resumen($connect, $tipo, $user_id, $idprod));
        return;
    }

    $total = (int)$linea->quantity + (int)$cantidad;
    if ($stock !== null && $total > (int)$stock) {
        avisar_y_redirigir('Stock insuficiente', 'Ya tienes ' . (int)$linea->quantity . ' en el carrito y solo hay ' . (int)$stock . ' unidades de "' . $nombre . '".', 'warning', 'nuevo.php');
        return;
    }

    // el precio de venta se mantiene (el guardado al agregar); el costo de compra es el ultimo escrito
    $sql = $tipo === 'compra'
        ? "UPDATE {$c['tabla']} SET quantity = ?, price = ? WHERE {$c['id']} = ?"
        : "UPDATE {$c['tabla']} SET quantity = ? WHERE {$c['id']} = ?";
    $connect->prepare($sql)->execute($tipo === 'compra' ? [$total, $precio, $linea->idlinea] : [$total, $linea->idlinea]);
    avisar_y_redirigir('¡Actualizado!', 'Ya estaba en el carrito: se sumaron ' . (int)$cantidad . ' unidad(es). Ahora tienes ' . $total . '.', 'success', 'cart.php',
                       carrito_resumen($connect, $tipo, $user_id, $idprod));
}

// lo que la lista de productos actualiza sin recargar: lineas del carrito y unidades del producto
function carrito_resumen($connect, $tipo, $user_id, $idprod)
{
    $lineas = array_column(carrito_lineas($connect, $tipo, $user_id), null, 'idprod');
    return ['lineas' => count($lineas), 'idprod' => (int)$idprod, 'idlinea' => (int)($lineas[$idprod]['idlinea'] ?? 0),
            'enCarrito' => (int)($lineas[$idprod]['quantity'] ?? 0)];
}

// quita una linea del carrito propio (nunca la de otro usuario)
function carrito_quitar($connect, $tipo, $idlinea, $user_id)
{
    $c = CARRITOS[$tipo];
    return $connect->prepare("DELETE FROM {$c['tabla']} WHERE {$c['id']} = ? AND user_id = ?")
                   ->execute([$idlinea, $user_id]);
}

function carrito_vaciar($connect, $tipo, $user_id)
{
    $connect->prepare('DELETE FROM ' . CARRITOS[$tipo]['tabla'] . ' WHERE user_id = ?')->execute([$user_id]);
}

// TablaPaginada: responde a una tabla de DataTables en modo servidor solo la pagina que se esta viendo,
// para que el modulo cargue rapido aunque haya miles de registros. Lee draw, start, length, search y order.
//   $desde     "FROM ... WHERE ..." sin LIMIT ni ORDER (con ? en $params)
//   $campos    columnas del SELECT
//   $orden     por indice de columna de la tabla: expresion SQL para ordenar (o null si no se ordena por ella);
//              'defecto' => orden cuando la tabla no pide ninguno
//   $buscar    expresiones SQL donde busca el texto del buscador (LIKE)
//   $fila      function ($registro) que devuelve las celdas (html) de una fila
function tabla_paginada($connect, $campos, $desde, $params, $orden, $buscar, $fila)
{
    $inicio = max(0, (int)($_GET['start'] ?? 0));
    $largo  = min(100, max(1, (int)($_GET['length'] ?? 25)));
    $texto  = trim((string)($_GET['search']['value'] ?? ''));

    $total = $connect->prepare("SELECT COUNT(*) $desde");
    $total->execute($params);
    $total = (int)$total->fetchColumn();

    $filtro = '';
    $paramsFiltro = $params;
    if ($texto !== '' && $buscar) {
        $filtro = ' AND (' . implode(' OR ', array_map(fn($c) => "$c LIKE ?", $buscar)) . ')';
        foreach ($buscar as $c) {
            $paramsFiltro[] = '%' . $texto . '%';
        }
    }
    $filtrados = $total;
    if ($filtro !== '') {
        $q = $connect->prepare("SELECT COUNT(*) $desde $filtro");
        $q->execute($paramsFiltro);
        $filtrados = (int)$q->fetchColumn();
    }

    // solo se ordena por columnas de la lista blanca $orden
    $col = (int)($_GET['order'][0]['column'] ?? -1);
    $dir = strtolower((string)($_GET['order'][0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
    $ordenSql = ($col >= 0 && !empty($orden[$col])) ? $orden[$col] . " $dir" : ($orden['defecto'] ?? '1');

    $q = $connect->prepare("SELECT $campos $desde $filtro ORDER BY $ordenSql LIMIT $largo OFFSET $inicio");
    $q->execute($paramsFiltro);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'draw'            => (int)($_GET['draw'] ?? 0),
        'recordsTotal'    => $total,
        'recordsFiltered' => $filtrados,
        'data'            => array_map($fila, $q->fetchAll()),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// correo opcional: '' si viene vacio, null si no es valido o pasa de $max caracteres
function leer_correo($valor, $max)
{
    $correo = strtolower(trim((string)$valor));
    if ($correo === '') {
        return '';
    }
    return (strlen($correo) <= $max && filter_var($correo, FILTER_VALIDATE_EMAIL)) ? $correo : null;
}

// baja logica (state = 0) de un registro y aviso con regreso al listado.
//   $nombre  como se nombra en los avisos, ej. ['La categoría', 'dada'] o ['El cliente', 'dado']
//   $enUso   opcional [sql que cuenta lo que lo usa (un ? = id), mensaje si lo usan]: impide la baja
function dar_de_baja($connect, $tabla, $columnaId, $id, $nombre, $volver, $enUso = null)
{
    [$sujeto, $dado] = $nombre;

    if (!$id) {
        avisar_y_redirigir('Error!', 'Identificador no válido.', 'error', $volver);
        return;
    }

    if ($enUso) {
        $cuenta = $connect->prepare($enUso[0]);
        $cuenta->execute([$id]);
        $usos = (int)$cuenta->fetchColumn();
        if ($usos > 0) {
            avisar_y_redirigir('No se puede', str_replace('{n}', (string)$usos, $enUso[1]), 'warning', $volver);
            return;
        }
    }

    // $tabla y $columnaId vienen del codigo, nunca del usuario
    $sql = $connect->prepare("UPDATE `$tabla` SET state = 0 WHERE `$columnaId` = ? AND state = 1");
    $sql->execute([$id]);

    if ($sql->rowCount() > 0) {
        avisar_y_redirigir('Dado de baja!', $sujeto . ' ya no aparece en el listado.', 'success', $volver);
    } else {
        avisar_y_redirigir('Sin cambios', $sujeto . ' no existe o ya estaba ' . $dado . ' de baja.', 'warning', $volver);
    }
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
