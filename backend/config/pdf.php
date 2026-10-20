<?php
// piezas comunes de los PDF (boleta/factura, comprobante de compra, orden de garantia)
require_once __DIR__ . '/../fpdf/fpdf.php';

// datos de la tienda que salen en todos los documentos
const EMPRESA = [
    'nombre'    => 'CK Computers',
    'direccion' => ['Trujillo', 'Stand A 109, ORO AZUL'],
    'ruc'       => '10469119667',
];

// FPDF trabaja en latin1
function pdf_txt($texto)
{
    return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
}

// lineas de un comprobante: [desc, cant, precio, subtotal] desde su tabla de detalle
// o, en registros antiguos sin detalle, desde el texto "Producto ( 2 ), Otro ( 1 )"
function pdf_lineas($detalle, $totalProducts)
{
    $lineas = [];
    foreach ($detalle as $d) {
        $lineas[] = [
            'desc'     => $d['nombre'],
            'cant'     => $d['cantidad'],
            'precio'   => 'S/ ' . number_format($d['precio'], 2),
            'subtotal' => 'S/ ' . number_format($d['subtotal'], 2),
        ];
    }
    if ($lineas) {
        return $lineas;
    }

    // el nombre puede llevar comas: se corta solo despues de "( n )"
    $buffer = '';
    foreach (explode(',', (string)$totalProducts) as $parte) {
        $buffer .= ($buffer === '' ? '' : ',') . $parte;
        if (!preg_match('/\)\s*$/', trim($parte))) {
            continue;
        }
        $item = trim($buffer, ' ,');
        $buffer = '';
        if (preg_match('/^(.*)\(\s*(\d+)\s*\)$/', $item, $m)) {
            $lineas[] = ['desc' => trim($m[1], ' ,'), 'cant' => $m[2], 'precio' => '', 'subtotal' => ''];
        } elseif ($item !== '') {
            $lineas[] = ['desc' => $item, 'cant' => '', 'precio' => '', 'subtotal' => ''];
        }
    }
    if (trim($buffer, ' ,') !== '') {
        $lineas[] = ['desc' => trim($buffer, ' ,'), 'cant' => '', 'precio' => '', 'subtotal' => ''];
    }
    return $lineas;
}

class PdfCk extends FPDF
{
    const MARGEN = 15;
    const ANCHO = 180;

    // si $titulo esta definido, cada pagina empieza con titulo + empresa + $detalles
    public $titulo = '';
    public $detalles = [];          // etiqueta => valor (columna derecha)
    public $colDetalles = 115;      // x de la columna derecha
    public $anchoEtiqueta = 27;
    public $pie = '';               // texto sobre el numero de pagina

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4');
        $this->SetMargins(self::MARGEN, self::MARGEN, self::MARGEN);
        $this->SetAutoPageBreak(true, 22);
    }

    function Header()
    {
        if ($this->titulo === '') {
            return;
        }
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 12, pdf_txt($this->titulo), 0, 1, 'C');
        $this->Ln(4);
        $yInicio = $this->GetY();

        // izquierda: la tienda
        $this->SetFont('Arial', 'B', 13);
        $this->Cell(90, 7, pdf_txt(EMPRESA['nombre']), 0, 1);
        $this->SetFont('Arial', '', 10);
        foreach (array_merge(EMPRESA['direccion'], ['RUC: ' . EMPRESA['ruc']]) as $linea) {
            $this->Cell(90, 6, pdf_txt($linea), 0, 1);
        }
        $finIzquierda = $this->GetY();

        // derecha: datos del documento
        $anchoValor = self::MARGEN + self::ANCHO - $this->colDetalles - $this->anchoEtiqueta;
        $this->SetXY($this->colDetalles, $yInicio);
        $this->SetFont('Arial', 'B', 13);
        $this->Cell(0, 7, 'Detalles', 0, 1);
        foreach ($this->detalles as $etiqueta => $valor) {
            $this->SetX($this->colDetalles);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell($this->anchoEtiqueta, 6, pdf_txt($etiqueta), 0, 0);
            $this->SetFont('Arial', '', 10);
            $this->Cell($anchoValor, 6, $this->recortar(pdf_txt($valor), $anchoValor - 2), 0, 1);
        }

        $this->SetY(max($finIzquierda, $this->GetY()) + 4);
        $this->Cell(self::ANCHO, 0, '', 'T');
        $this->Ln(8);
    }

    function Footer()
    {
        $this->SetY(-18);
        $this->SetFont('Arial', 'I', 8);
        if ($this->pie !== '') {
            $this->MultiCell(0, 4, pdf_txt($this->pie), 0, 'C');
        }
        $this->Cell(0, 5, pdf_txt('Página ' . $this->PageNo()), 0, 0, 'C');
    }

    // texto (ya en latin1) recortado con "..." para que entre en $ancho mm con la fuente actual
    private function recortar($texto, $ancho)
    {
        if ($this->GetStringWidth($texto) <= $ancho) {
            return $texto;
        }
        while ($texto !== '' && $this->GetStringWidth($texto . '...') > $ancho) {
            $texto = substr($texto, 0, -1);
        }
        return rtrim($texto) . '...';
    }

    // cuantas lineas ocupa $texto (latin1) en una MultiCell de $ancho mm: corta por palabras como FPDF
    private function contarLineas($texto, $ancho)
    {
        $max = $ancho - 2 * $this->cMargin;
        $lineas = 0;
        foreach (explode("\n", $texto) as $parrafo) {
            $lineas++;
            $actual = '';
            foreach (explode(' ', $parrafo) as $palabra) {
                $prueba = $actual === '' ? $palabra : $actual . ' ' . $palabra;
                if ($actual !== '' && $this->GetStringWidth($prueba) > $max) {
                    $lineas++;
                    $actual = $palabra;
                } else {
                    $actual = $prueba;
                }
            }
        }
        return $lineas;
    }

    // fondo gris + texto blanco para cabeceras de tabla y etiquetas de totales
    private function relleno()
    {
        $this->SetFillColor(128, 128, 128);
        $this->SetTextColor(255, 255, 255);
    }

    // tabla de productos. $columnas: [[titulo, ancho, alineacion, clave]]; la primera es la descripcion
    // (puede ocupar varias lineas), las demas una sola linea
    function TablaProductos($columnas, $lineas)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->relleno();
        foreach ($columnas as $i => $c) {
            $this->Cell($c[1], 8, pdf_txt($c[0]), 1, $i === count($columnas) - 1 ? 1 : 0, 'C', true);
        }
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 9);

        $anchoDesc = $columnas[0][1];
        foreach ($lineas as $l) {
            $desc = pdf_txt($l[$columnas[0][3]]);
            $alto = $this->contarLineas($desc, $anchoDesc - 6) * 6 + 2;
            if ($this->GetY() + $alto > $this->PageBreakTrigger) {
                $this->AddPage();
            }
            $x = self::MARGEN;
            $y = $this->GetY();

            foreach ($columnas as $c) {
                $this->Rect($x, $y, $c[1], $alto);
                $x += $c[1];
            }
            $this->SetXY(self::MARGEN + 3, $y + 1.5);
            $this->MultiCell($anchoDesc - 6, 6, $desc, 0, 'L');

            $x = self::MARGEN + $anchoDesc;
            foreach (array_slice($columnas, 1) as $c) {
                $this->SetXY($x, $y);
                $this->Cell($c[1], $alto, pdf_txt($l[$c[3]]), 0, 0, $c[2]);
                $x += $c[1];
            }
            $this->SetXY(self::MARGEN, $y + $alto);
        }
        $this->Ln(6);
    }

    // cuadro de totales alineado a la derecha: [etiqueta => monto]; el ultimo va mas alto
    function Totales($filas)
    {
        $this->SetFont('Arial', 'B', 11);
        $x = self::MARGEN + self::ANCHO - 80;
        $n = 0;
        foreach ($filas as $etiqueta => $monto) {
            $alto = ++$n === count($filas) ? 9 : 8;
            $this->SetX($x);
            $this->relleno();
            $this->Cell(50, $alto, pdf_txt($etiqueta), 1, 0, 'C', true);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(30, $alto, 'S/ ' . number_format($monto, 2), 1, 1, 'R');
        }
    }
}
