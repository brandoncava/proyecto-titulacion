<?php
/* call the FPDF library */
require('../../backend/fpdf/fpdf.php');
require_once __DIR__ . "/../../backend/config/auth.php";
require '../../backend/config/Conexion.php';

requiere_permiso("ventas");

$empresa_nombre = 'CK Computers';
$empresa_direccion = ['Trujillo', 'Stand A 109, ORO AZUL'];
$empresa_ruc = 'RUC: 10469119667';

/* ID de la venta a imprimir */
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) ?: 0;

$stmt = $connect->prepare("SELECT * FROM orders WHERE idord = :id");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

if (!$row) {
    die('No se encontro la venta solicitada.');
}

function parsear_productos($texto)
{
    $partes = explode(',', $texto);
    $items = [];
    $buffer = '';

    foreach ($partes as $parte) {
        $buffer .= ($buffer === '' ? '' : ',') . $parte;
        if (preg_match('/\)\s*$/', trim($parte))) {
            $items[] = trim($buffer, " ,");
            $buffer = '';
        }
    }
    if (trim($buffer, " ,") !== '') {
        $items[] = trim($buffer, " ,");
    }

    $resultado = [];
    foreach ($items as $item) {
        if (preg_match('/^(.*)\(\s*(\d+)\s*\)$/', $item, $m)) {
            $resultado[] = ['desc' => trim($m[1], " ,"), 'cant' => $m[2]];
        } elseif ($item !== '') {
            $resultado[] = ['desc' => $item, 'cant' => ''];
        }
    }
    return $resultado;
}

$productos = parsear_productos($row['total_products']);

class Boleta extends FPDF
{
    public $empresa_nombre;
    public $empresa_direccion = [];
    public $empresa_ruc;
    public $datos_venta = [];

    function Header()
    {
        // Titulo centrado
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 12, utf8_decode($this->datos_venta['titulo']), 0, 1, 'C');
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

        $leftEndY = $this->GetY();

        /* Columna derecha: detalles de la venta */
        $colDerecha = 120;
        $this->SetXY($colDerecha, $yInicio);
        $this->SetFont('Arial', 'B', 13);
        $this->Cell(75, 7, 'Detalles', 0, 1);

        $etiquetas = [
            $this->datos_venta['etiqueta_num'] => $this->datos_venta['numero'],
            'Cliente:'   => $this->datos_venta['cliente'],
            $this->datos_venta['etiqueta_doc'] => $this->datos_venta['doc'],
            'Fecha:'     => $this->datos_venta['fecha'],
            'Pago:'      => $this->datos_venta['pago'],
            'Estado:'    => $this->datos_venta['estado'],
        ];
        foreach ($etiquetas as $label => $valor) {
            $this->SetX($colDerecha);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(25, 6, $label, 0, 0);
            $this->SetFont('Arial', '', 10);
            $this->Cell(60, 6, utf8_decode(mb_substr((string)$valor, 0, 34)), 0, 1);
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
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, utf8_decode('Representación impresa de la venta registrada en el sistema. La emisión electrónica válida ante SUNAT se realiza por los medios autorizados.'), 0, 1, 'C');
        $this->Cell(0, 5, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new Boleta('P', 'mm', 'A4');
$pdf->empresa_nombre    = $empresa_nombre;
$pdf->empresa_direccion = $empresa_direccion;
$pdf->empresa_ruc       = $empresa_ruc;
$esFactura = (($row['tipc'] ?? '') === 'Factura');
$docTipo   = (string)($row['doc_tipo'] ?? '');
$docNum    = (string)($row['doc_num'] ?? '');
// las ventas viejas no tienen serie, se muestra el numero de venta
$numeroComprobante = !empty($row['serie'])
    ? formato_comprobante($row['serie'], $row['numero'])
    : str_pad($row['idord'], 8, '0', STR_PAD_LEFT);

$pdf->datos_venta = [
    'titulo'       => $esFactura ? 'FACTURA' : 'BOLETA DE VENTA',
    'etiqueta_num' => $esFactura ? 'N. Factura:' : 'N. Boleta:',
    'numero'       => $numeroComprobante,
    'etiqueta_doc' => ($docTipo !== '' ? $docTipo : 'Doc.') . ':',
    'doc'          => $docNum !== '' ? $docNum : '-',
    'cliente' => $row['nomcl'],
    'fecha'   => $row['placed_on'],
    'pago'    => $row['method'],
    'estado'  => $row['payment_status'],
];
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

/* Tabla de productos */
$margen = 15;
$anchoDesc = 150;
$anchoCant = 30;

$pdf->SetX($margen);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(128, 128, 128);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell($anchoDesc, 8, 'Producto', 1, 0, 'C', true);
$pdf->Cell($anchoCant, 8, 'Cantidad', 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 9);

foreach ($productos as $p) {
    $x = $margen;
    $y = $pdf->GetY();

    $lineas = max(1, ceil($pdf->GetStringWidth(utf8_decode($p['desc'])) / ($anchoDesc - 6)));
    $alto = $lineas * 6 + 2;

    $pdf->Rect($x, $y, $anchoDesc, $alto);
    $pdf->Rect($x + $anchoDesc, $y, $anchoCant, $alto);

    $pdf->SetXY($x + 3, $y + 1.5);
    $pdf->MultiCell($anchoDesc - 6, 6, utf8_decode($p['desc']));

    $pdf->SetXY($x + $anchoDesc, $y);
    $pdf->Cell($anchoCant, $alto, $p['cant'], 0, 0, 'C');

    $pdf->SetXY($x, $y + $alto);
}

$pdf->Ln(6);

/* Totales */
// el precio ya incluye igv, se saca del total
list($opGravada, $igvMonto) = igv_desglosar($row['total_price']);
$xTot = $margen + $anchoDesc + $anchoCant - 80;

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetX($xTot);
$pdf->Cell(50, 8, 'Op. gravada:', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'S/ ' . number_format($opGravada, 2), 1, 1, 'R');
$pdf->SetX($xTot);
$pdf->Cell(50, 8, 'IGV (' . (IGV_TASA * 100) . '%):', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'S/ ' . number_format($igvMonto, 2), 1, 1, 'R');
$pdf->SetX($xTot);
$pdf->Cell(50, 9, 'Total:', 1, 0, 'C', true);
$pdf->Cell(30, 9, 'S/ ' . number_format($row['total_price'], 2), 1, 1, 'R');

/* Pagos: uno o varios medios */
$pagos = $connect->prepare('SELECT metodo, monto, num_operacion FROM orders_pagos WHERE idord = ? ORDER BY idpago');
$pagos->execute([$row['idord']]);
$pagos = $pagos->fetchAll(PDO::FETCH_ASSOC);

if ($pagos) {
    $pdf->Ln(6);
    $pdf->SetX($margen);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(60, 7, 'Medio de pago', 1, 0, 'C', true);
    $pdf->Cell(70, 7, utf8_decode('N° de operación'), 1, 0, 'C', true);
    $pdf->Cell(50, 7, 'Monto', 1, 1, 'C', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);
    foreach ($pagos as $p) {
        $pdf->SetX($margen);
        $pdf->Cell(60, 7, utf8_decode($p['metodo']), 1, 0);
        $pdf->Cell(70, 7, $p['num_operacion'] ?: '-', 1, 0, 'C');
        $pdf->Cell(50, 7, 'S/ ' . number_format($p['monto'], 2), 1, 1, 'R');
    }
}

/* Venta que reemplaza a otra (modificacion) o venta anulada */
$pdf->Ln(4);
$pdf->SetX($margen);
$pdf->SetFont('Arial', 'I', 9);
if (!empty($row['reemplaza_a'])) {
    $orig = $connect->prepare('SELECT serie, numero FROM orders WHERE idord = ?');
    $orig->execute([$row['reemplaza_a']]);
    $orig = $orig->fetch(PDO::FETCH_ASSOC);
    if ($orig) {
        $pdf->Cell(0, 6, utf8_decode('Este comprobante reemplaza a ' . formato_comprobante($orig['serie'], $orig['numero']) . ' (anulado).'), 0, 1);
    }
}
if ((int)$row['anulada'] === 1) {
    $pdf->SetTextColor(180, 35, 24);
    $pdf->MultiCell(180, 6, utf8_decode('ANULADO: ' . $row['motivo_anulacion']));
    $pdf->SetTextColor(0, 0, 0);
}

$pdf->Output('boleta_' . $row['idord'] . '.pdf', 'D');