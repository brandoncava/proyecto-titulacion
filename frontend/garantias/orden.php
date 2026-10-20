<?php
// PDF de la orden de recepcion por garantia (copia para el cliente)
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/pdf.php';
require '../../backend/config/Conexion.php';

requiere_permiso('garantias');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$b = $connect->prepare(
    'SELECT g.*, p.nomprd, p.codpro, pr.nomprv, u.nombre AS recibido_por
       FROM garantias g
       INNER JOIN productos p ON p.idprod = g.idprod
       LEFT JOIN proveedores pr ON pr.idprov = g.idprov
       INNER JOIN usuarios u ON u.id = g.user_id
      WHERE g.idgar = ?'
);
$b->execute([$id]);
$g = $b->fetch(PDO::FETCH_ASSOC);

if (!$g) {
    die('No se encontro la orden de garantia.');
}

class OrdenGarantia extends PdfCk
{
    // titulo de seccion con fondo gris
    function Seccion($titulo)
    {
        $this->Ln(3);
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(108, 43, 217);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(180, 7, pdf_txt($titulo), 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(1);
    }

    function Dato($etiqueta, $valor)
    {
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(45, 6, pdf_txt($etiqueta), 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->MultiCell(135, 6, pdf_txt($valor !== null && $valor !== '' ? $valor : '-'));
    }
}

$pdf = new OrdenGarantia();
$pdf->pie = EMPRESA['nombre'] . ' - Orden de garantía. Conserve este documento para recoger su producto.';
$pdf->AddPage();

// cabecera: empresa y numero de orden
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(110, 7, pdf_txt(EMPRESA['nombre']), 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 7, formato_garantia($g['idgar']), 0, 1, 'R');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(110, 5, pdf_txt(implode(' - ', EMPRESA['direccion']) . ' - RUC: ' . EMPRESA['ruc']), 0, 0);
$pdf->Cell(70, 5, pdf_txt('Ingreso: ' . date('d/m/Y H:i', strtotime($g['fecha_ingreso']))), 0, 1, 'R');
$pdf->Ln(4);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(180, 9, pdf_txt('ORDEN DE RECEPCIÓN POR GARANTÍA'), 0, 1, 'C');

$pdf->Seccion('Cliente');
$pdf->Dato('Nombre:', $g['cliente_nombre']);
$pdf->Dato('DNI / RUC:', $g['cliente_doc']);
$pdf->Dato('Teléfono:', $g['cliente_tel']);

$pdf->Seccion('Producto y compra original');
$pdf->Dato('Producto:', $g['nomprd']);
$pdf->Dato('Código:', $g['codpro']);
$pdf->Dato('N° de serie:', $g['numero_serie']);
$pdf->Dato('Comprobante:', $g['comprobante'] ?: 'Sin comprobante');
$pdf->Dato('Fecha de compra:', date('d/m/Y', strtotime($g['fecha_compra'])));
$pdf->Dato('Garantía:', ($g['vigente'] ? 'Vigente' : 'VENCIDA') . ' hasta el ' . date('d/m/Y', strtotime($g['vence'])));

$pdf->Seccion('Recepción');
$pdf->Dato('Estado físico:', $g['estado_fisico']);
$pdf->Dato('Accesorios:', $g['accesorios']);
$pdf->Dato('Falla reportada:', $g['falla']);
$pdf->Dato('Recibido por:', $g['recibido_por']);

// si ya avanzo, la copia muestra el estado actual
$pdf->Seccion('Estado de la orden');
$pdf->Dato('Estado actual:', GARANTIA_ESTADOS[$g['estado']] ?? $g['estado']);
if ($g['nomprv']) {
    $pdf->Dato('Enviado a:', $g['nomprv'] . ($g['rma_codigo'] ? '  (RMA/guía ' . $g['rma_codigo'] . ')' : ''));
}
if ($g['resultado']) {
    $pdf->Dato('Resultado:', $g['resultado'] . ' - ' . $g['resultado_detalle']);
}

$pdf->Seccion('Términos');
$pdf->SetFont('Arial', '', 8.5);
$terminos = [
    'El producto queda sujeto a evaluación del centro de servicio técnico autorizado de la marca, que decide si la garantía procede.',
    'La garantía no cubre daños por golpes, humedad, sobrecarga eléctrica, manipulación por terceros ni sellos de garantía rotos.',
    'Los plazos de revisión dependen de la marca. Le avisaremos al teléfono indicado cuando el producto esté listo para entrega.',
    'Para recoger el producto es obligatorio presentar esta orden y el documento de identidad del titular.',
    'Pasados 90 días desde el aviso de entrega sin que el producto sea recogido, la tienda no se responsabiliza por él.',
];
foreach ($terminos as $i => $texto) {
    $pdf->MultiCell(180, 4.5, pdf_txt(($i + 1) . '. ' . $texto));
}

// firmas
$pdf->Ln(18);
$y = $pdf->GetY();
$pdf->Line(25, $y, 85, $y);
$pdf->Line(115, $y, 175, $y);
$pdf->SetFont('Arial', '', 9);
$pdf->SetXY(25, $y + 1);
$pdf->Cell(60, 5, 'Firma del cliente', 0, 0, 'C');
$pdf->SetXY(115, $y + 1);
$pdf->Cell(60, 5, pdf_txt('Recibido por ' . EMPRESA['nombre']), 0, 1, 'C');

$pdf->Output('orden_garantia_' . formato_garantia($g['idgar']) . '.pdf', 'I');
