<?php
// PDF de la boleta o factura de una venta
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/pdf.php';
require '../../backend/config/Conexion.php';

requiere_permiso('ventas');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

$stmt = $connect->prepare('SELECT * FROM orders WHERE idord = ?');
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('No se encontró la venta solicitada.');
}

$detalle = $connect->prepare('SELECT nombre, precio, cantidad, subtotal FROM orders_detalle WHERE idord = ? ORDER BY iddet');
$detalle->execute([$id]);
$lineas = pdf_lineas($detalle->fetchAll(PDO::FETCH_ASSOC), $row['total_products']);

$esFactura = ($row['tipc'] ?? '') === 'Factura';
$docTipo   = (string)($row['doc_tipo'] ?? '');
// las ventas viejas no tienen serie, se muestra el numero de venta
$numero = !empty($row['serie'])
    ? formato_comprobante($row['serie'], $row['numero'])
    : str_pad($row['idord'], 8, '0', STR_PAD_LEFT);

$pdf = new PdfCk();
$pdf->titulo = $esFactura ? 'FACTURA' : 'BOLETA DE VENTA';
$pdf->colDetalles = 120;
$pdf->anchoEtiqueta = 25;
$pdf->detalles = [
    ($esFactura ? 'N. Factura:' : 'N. Boleta:') => $numero,
    'Cliente:' => $row['nomcl'],
    ($docTipo !== '' ? $docTipo : 'Doc.') . ':' => $row['doc_num'] ?: '-',
    'Fecha:'   => date('d/m/Y H:i', strtotime($row['placed_on'])),
    'Pago:'    => $row['method'],
    'Estado:'  => $row['payment_status'],
];
$pdf->pie = 'Representación impresa de la venta registrada en el sistema. La emisión electrónica válida ante SUNAT se realiza por los medios autorizados.';
$pdf->AddPage();

$pdf->TablaProductos([
    ['Producto', 100, 'L', 'desc'],
    ['Cant.',     20, 'C', 'cant'],
    ['P. Unit.',  30, 'R', 'precio'],
    ['Subtotal',  30, 'R', 'subtotal'],
], $lineas);

// el precio ya incluye igv, se saca del total
list($opGravada, $igvMonto) = igv_desglosar($row['total_price']);
$pdf->Totales([
    'Op. gravada:' => $opGravada,
    'IGV (' . (IGV_TASA * 100) . '%):' => $igvMonto,
    'Total:' => $row['total_price'],
]);

// pagos: uno o varios medios
$pagos = $connect->prepare('SELECT metodo, monto, num_operacion FROM orders_pagos WHERE idord = ? ORDER BY idpago');
$pagos->execute([$row['idord']]);
$pagos = $pagos->fetchAll(PDO::FETCH_ASSOC);

if ($pagos) {
    $pdf->Ln(6);
    $pdf->TablaProductos([
        ['Medio de pago',   60, 'L', 'metodo'],
        ['N° de operación', 70, 'C', 'operacion'],
        ['Monto',           50, 'R', 'monto'],
    ], array_map(fn($p) => [
        'metodo'    => $p['metodo'],
        'operacion' => $p['num_operacion'] ?: '-',
        'monto'     => 'S/ ' . number_format($p['monto'], 2),
    ], $pagos));
}

// venta que reemplaza a otra (modificacion) o venta anulada
$pdf->SetFont('Arial', 'I', 9);
if (!empty($row['reemplaza_a'])) {
    $orig = $connect->prepare('SELECT serie, numero FROM orders WHERE idord = ?');
    $orig->execute([$row['reemplaza_a']]);
    $orig = $orig->fetch(PDO::FETCH_ASSOC);
    if ($orig) {
        $pdf->Cell(0, 6, pdf_txt('Este comprobante reemplaza a ' . formato_comprobante($orig['serie'], $orig['numero']) . ' (anulado).'), 0, 1);
    }
}
if ((int)$row['anulada'] === 1) {
    $pdf->SetTextColor(180, 35, 24);
    $pdf->MultiCell(PdfCk::ANCHO, 6, pdf_txt('ANULADO: ' . $row['motivo_anulacion']));
    $pdf->SetTextColor(0, 0, 0);
}

$pdf->Output(($esFactura ? 'factura_' : 'boleta_') . $numero . '.pdf', 'D');
