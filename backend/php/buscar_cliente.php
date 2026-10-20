<?php
// busqueda de clientes para Finalizar venta (responde JSON)
//   ?q=texto  -> hasta 8 clientes cuyo documento empieza con el texto o cuyo nombre lo contiene
//   ?doc=num  -> el cliente con ese documento exacto, o null
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('ventas');

header('Content-Type: application/json; charset=utf-8');

$campos = "SELECT idcli, UPPER(tipd) AS tipd, nudoc, TRIM(CONCAT(nocl, ' ', apcl)) AS nombre, telfcl FROM clientes";

$doc = preg_replace('/\D/', '', (string)($_GET['doc'] ?? ''));
if ($doc !== '') {
    $b = $connect->prepare("$campos WHERE nudoc = ? LIMIT 1");
    $b->execute([$doc]);
    echo json_encode($b->fetch(PDO::FETCH_ASSOC) ?: null, JSON_UNESCAPED_UNICODE);
    exit;
}

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) < 2) {
    echo '[]';
    exit;
}

$b = $connect->prepare(
    "$campos
      WHERE state = 1 AND (nudoc LIKE ? OR CONCAT(nocl, ' ', apcl) LIKE ?)
      ORDER BY nocl, apcl
      LIMIT 8"
);
$b->execute([preg_replace('/\D/', '', $q) !== '' ? preg_replace('/\D/', '', $q) . '%' : '-', '%' . $q . '%']);
echo json_encode($b->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
