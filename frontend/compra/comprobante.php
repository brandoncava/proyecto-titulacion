<?php
// PDF del comprobante interno de una compra
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/pdf.php';
require '../../backend/config/Conexion.php';

requiere_permiso('compras');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

$stmt = $connect->prepare(
    'SELECT op.*, p.rucprv, p.nomprv
       FROM orders_purchase op
       INNER JOIN proveedores p ON op.idprov = p.idprov
      WHERE op.idordpur = ?'
);
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('No se encontró la compra solicitada.');
}

// detalle con precios; las compras antiguas no lo tienen y se leen de total_products
$detalle = $connect->prepare('SELECT nombre, precio, cantidad, subtotal FROM orders_purchase_detalle WHERE idordpur = ? ORDER BY iddet');
$detalle->execute([$id]);
$lineas = pdf_lineas($detalle->fetchAll(PDO::FETCH_ASSOC), $row['total_products']);

$pdf = new PdfCk();
$pdf->titulo = 'COMPROBANTE DE COMPRA';
$pdf->colDetalles = 110;
$pdf->detalles = [
    'N. Compra:'   => str_pad($row['idordpur'], 8, '0', STR_PAD_LEFT),
    'Comprobante:' => $row['tipc'],
    'Doc. prov.:'  => !empty($row['serie_doc']) ? $row['serie_doc'] . '-' . $row['numero_doc'] : '-',
    'F. emisión:'  => !empty($row['fecha_emision']) ? date('d/m/Y', strtotime($row['fecha_emision'])) : '-',
    'Proveedor:'   => $row['nomprv'],
    'RUC prov.:'   => $row['rucprv'],
    'Fecha:'       => date('d/m/Y H:i', strtotime($row['placed_on'])),
    'Pago:'        => $row['method'],
    'Estado:'      => $row['payment_status'],
];
$pdf->AddPage();

$pdf->TablaProductos([
    ['Producto', 100, 'L', 'desc'],
    ['Cant.',     20, 'C', 'cant'],
    ['P. Unit.',  30, 'R', 'precio'],
    ['Subtotal',  30, 'R', 'subtotal'],
], $lineas);

// las compras nuevas guardan base e igv, las viejas se sacan del total
$base = (float)($row['subtotal_sin_igv'] ?? 0);
$igv  = (float)($row['igv'] ?? 0);
if ($base <= 0) {
    list($base, $igv) = igv_desglosar($row['total_price']);
}
$pdf->Totales([
    'Subtotal (sin IGV):' => $base,
    ($igv > 0 ? 'IGV (' . (IGV_TASA * 100) . '%):' : 'IGV (no incluye):') => $igv,
    'Total:' => $row['total_price'],
]);

if ((int)$row['anulada'] === 1) {
    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(180, 35, 24);
    $pdf->MultiCell(PdfCk::ANCHO, 6, pdf_txt('ANULADA: ' . $row['motivo_anulacion']));
    $pdf->SetTextColor(0, 0, 0);
}

$pdf->Output('compra_' . $row['idordpur'] . '.pdf', 'D');
