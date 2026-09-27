<?php
/* call the FPDF library */
require('../../backend/fpdf/fpdf.php');
require_once __DIR__ . "/../../backend/config/auth.php";
require '../../backend/config/Conexion.php';

requiere_permiso("compras");

$empresa_nombre = 'CK Computers';
$empresa_direccion = ['Trujillo', 'Stand A 109, ORO AZUL'];
$empresa_ruc = 'RUC: 10469119667';
$empresa_web = 'ventapro.pe/c/ck-computers';

/* ID de la compra a imprimir */
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) ?: 0;

$stmt = $connect->prepare(
    "SELECT op.*, p.rucprv, p.nomprv
       FROM orders_purchase op
       INNER JOIN proveedores p ON op.idprov = p.idprov
      WHERE op.idordpur = :id"
);
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

if (!$row) {
    die('No se encontro la compra solicitada.');
}

/* Detalle con precios; las compras antiguas no lo tienen y se leen de total_products */
$stmt = $connect->prepare(
    "SELECT nombre, precio, cantidad, subtotal
       FROM orders_purchase_detalle
      WHERE idordpur = :id
      ORDER BY iddet"
);
$stmt->execute([':id' => $id]);
$detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);

$productos = [];
if ($detalle) {
    foreach ($detalle as $d) {
        $productos[] = [
            'desc'     => $d['nombre'],
            'cant'     => $d['cantidad'],
            'precio'   => number_format($d['precio'], 2),
            'subtotal' => number_format($d['subtotal'], 2),
        ];
    }
} else {
    foreach (preg_split('/\)\s*,\s*/', $row['total_products']) as $item) {
        $item = trim($item, " ,");
        if ($item === '') {
            continue;
        }
        if (preg_match('/^(.*)\(\s*(\d+)\s*\)?$/', $item, $m)) {
            $productos[] = ['desc' => trim($m[1], " ,"), 'cant' => $m[2], 'precio' => '', 'subtotal' => ''];
        } else {
            $productos[] = ['desc' => $item, 'cant' => '', 'precio' => '', 'subtotal' => ''];
        }
    }
}

class ComprobanteCompra extends FPDF
{
    public $empresa_nombre;
    public $empresa_direccion = [];
    public $empresa_ruc;
    public $empresa_web;
    public $datos_compra = [];

    function Header()
    {
        // Titulo centrado
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 12, utf8_decode('COMPROBANTE DE COMPRA'), 0, 1, 'C');
        $this->Ln(4);

        $margen  = 15;
        $yInicio = $this->GetY();

        /* Columna izquierda: datos de la empresa */
        $this->SetXY($margen, $yInicio);
        $this->SetFont('Arial', 'B', 13);
        $this->Cell(90, 7, utf8_decode($this->empresa_nombre), 0, 1);

        $this->SetFont('Arial', '', 10);
        foreach ($this->empresa_direccion as $linea) {
            $this->SetX($margen);
            $this->Cell(90, 6, utf8_decode($linea), 0, 1);
        }
        $this->SetX($margen);
        $this->Cell(90, 6, utf8_decode($this->empresa_ruc), 0, 1);
        $this->SetX($margen);
        $this->Cell(90, 6, utf8_decode($this->empresa_web), 0, 1);

        $leftEndY = $this->GetY();

        /* Columna derecha: detalles de la compra */
        $colDerecha = 110;
        $this->SetXY($colDerecha, $yInicio);
        $this->SetFont('Arial', 'B', 13);
        $this->Cell(85, 7, 'Detalles', 0, 1);

        $etiquetas = [
            'N. Compra:'   => $this->datos_compra['idordpur'],
            'Comprobante:' => $this->datos_compra['tipc'],
            'Proveedor:'   => $this->datos_compra['proveedor'],
            'RUC prov.:'   => $this->datos_compra['rucprv'],
            'Fecha:'       => $this->datos_compra['fecha'],
            'Pago:'        => $this->datos_compra['pago'],
            'Estado:'      => $this->datos_compra['estado'],
        ];
        foreach ($etiquetas as $label => $valor) {
            $this->SetX($colDerecha);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(27, 6, $label, 0, 0);
            $this->SetFont('Arial', '', 10);
            $this->Cell(58, 6, utf8_decode($valor), 0, 1);
        }

        /* Linea separadora */
        $y = max($leftEndY, $this->GetY()) + 4;
        $this->SetY($y);
        $this->SetX($margen);
        $this->Cell(180, 0, '', 'T');
        $this->Ln(8);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new ComprobanteCompra('P', 'mm', 'A4');
$pdf->empresa_nombre    = $empresa_nombre;
$pdf->empresa_direccion = $empresa_direccion;
$pdf->empresa_ruc       = $empresa_ruc;
$pdf->empresa_web       = $empresa_web;
$pdf->datos_compra = [
    'idordpur'  => str_pad($row['idordpur'], 8, '0', STR_PAD_LEFT),
    'tipc'      => (string)$row['tipc'],
    'proveedor' => (string)$row['nomprv'],
    'rucprv'    => (string)$row['rucprv'],
    'fecha'     => (string)$row['placed_on'],
    'pago'      => (string)$row['method'],
    'estado'    => (string)$row['payment_status'],
];
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

/* Tabla de productos */
$margen = 15;
$anchoDesc = 100;
$anchoCant = 20;
$anchoPrecio = 30;
$anchoSub = 30;

$pdf->SetX($margen);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(128, 128, 128);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell($anchoDesc, 8, 'Producto', 1, 0, 'C', true);
$pdf->Cell($anchoCant, 8, 'Cant.', 1, 0, 'C', true);
$pdf->Cell($anchoPrecio, 8, 'P. Unit.', 1, 0, 'C', true);
$pdf->Cell($anchoSub, 8, 'Subtotal', 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 9);

foreach ($productos as $p) {
    $x = $margen;
    $y = $pdf->GetY();

    $lineas = max(1, ceil($pdf->GetStringWidth(utf8_decode($p['desc'])) / ($anchoDesc - 6)));
    $alto = $lineas * 6 + 2;

    $pdf->Rect($x, $y, $anchoDesc, $alto);
    $pdf->Rect($x + $anchoDesc, $y, $anchoCant, $alto);
    $pdf->Rect($x + $anchoDesc + $anchoCant, $y, $anchoPrecio, $alto);
    $pdf->Rect($x + $anchoDesc + $anchoCant + $anchoPrecio, $y, $anchoSub, $alto);

    $pdf->SetXY($x + 3, $y + 1.5);
    $pdf->MultiCell($anchoDesc - 6, 6, utf8_decode($p['desc']));

    $pdf->SetXY($x + $anchoDesc, $y);
    $pdf->Cell($anchoCant, $alto, $p['cant'], 0, 0, 'C');
    $pdf->Cell($anchoPrecio, $alto, $p['precio'] !== '' ? 'S/ ' . $p['precio'] : '', 0, 0, 'R');
    $pdf->Cell($anchoSub, $alto, $p['subtotal'] !== '' ? 'S/ ' . $p['subtotal'] : '', 0, 0, 'R');

    $pdf->SetXY($x, $y + $alto);
}

$pdf->Ln(6);

/* Totales */
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(128, 128, 128);
$pdf->SetX($margen + 180 - 80);
$pdf->Cell(50, 9, 'Total:', 1, 0, 'C', true);
$pdf->Cell(30, 9, 'S/ ' . number_format($row['total_price'], 2), 1, 1, 'R');

$pdf->Output('compra_' . $row['idordpur'] . '.pdf', 'D');
