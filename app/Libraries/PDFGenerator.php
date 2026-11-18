<?php

namespace App\Libraries;

/**
 * PDF Generator Profesional - Genera PDFs tipo factura para resultados de laboratorio
 * Estructura PDF 1.4 valida
 */
class PDFGenerator {
    private $content = '';
    private $pageWidth = 595;
    private $pageHeight = 842;
    private $marginLeft = 40;
    private $marginRight = 40;
    private $marginTop = 40;
    private $currentY = 40;
    private $pageCount = 1;

    public function __construct() {}

    private function esc($text) {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function text($x, $y, $text, $size = 10, $bold = false, $color = null) {
        $font = $bold ? 'F2' : 'F1';
        if ($color) {
            $this->content .= sprintf("%.2f %.2f %.2f RG ", $color[0], $color[1], $color[2]);
        }
        $this->content .= "BT /$font $size Tf $x $y Td (" . $this->esc($text) . ") Tj ET\n";
        if ($color) {
            $this->content .= "0 0 0 RG\n";
        }
    }

    private function rect($x, $y, $w, $h, $r = 1, $g = 1, $b = 1, $fill = true) {
        $op = $fill ? 'f' : 'S';
        $this->content .= sprintf("%.2f %.2f %.2f rg ", $r, $g, $b);
        $this->content .= "$x $y $w $h re $op\n";
    }

    private function line($x1, $y1, $x2, $y2, $r = 0, $g = 0, $b = 0, $width = 1) {
        $this->content .= sprintf("%.2f %.2f %.2f RG %.2f w ", $r, $g, $b, $width);
        $this->content .= "$x1 $y1 m $x2 $y2 l S\n";
    }

    private function cell($x, $y, $w, $h, $text, $align = 'L', $bold = false, $bgColor = null, $textColor = null) {
        if ($bgColor) {
            $this->rect($x, $y, $w, $h, $bgColor[0], $bgColor[1], $bgColor[2], true);
        }
        $text = $this->esc($text);
        $font = $bold ? 'F2' : 'F1';
        $size = 8;
        $tx = $x + 3;
        if ($align == 'C') $tx = $x + $w/2 - strlen($text) * $size * 0.25;
        if ($align == 'R') $tx = $x + $w - strlen($text) * $size * 0.5 - 2;
        if ($textColor) {
            $this->content .= sprintf("%.2f %.2f %.2f RG ", $textColor[0], $textColor[1], $textColor[2]);
        }
        $this->content .= "BT /$font $size Tf $tx " . ($y + $h/2 + 2.5) . " Td ($text) Tj ET\n";
        if ($textColor) {
            $this->content .= "0 0 0 RG\n";
        }
    }

    private function newPage() {
        if ($this->content !== '') {
            $this->content .= "showpage\n";
        }
        $this->pageCount++;
        $this->currentY = $this->pageHeight - $this->marginTop;
    }

    public function output($filename = 'documento.pdf', $download = true) {
        $pdf = $this->buildPDF();
        if ($download) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
        }
        echo $pdf;
        exit;
    }

    public function save($filepath) {
        file_put_contents($filepath, $this->buildPDF());
    }

    private function buildPDF() {
        $objects = [];

        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$this->pageWidth} {$this->pageHeight}] /Contents 4 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>";

        $stream = trim($this->content);
        $objects[4] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
        $objects[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $objects[6] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        for ($i = 1; $i <= 6; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= "$i 0 obj\n" . $objects[$i] . "\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 7\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 6; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size 7 /Root 1 0 R >>\n";
        $pdf .= "startxref\n$xrefPos\n%%EOF\n";

        return $pdf;
    }

    public function headerFactura($titulo, $subtitulo, $numero, $fecha) {
        $y = $this->currentY;

        $this->rect($this->marginLeft, $y, $this->pageWidth - $this->marginLeft - $this->marginRight, 2, 0.145, 0.392, 0.753);
        $y -= 10;

        $this->text($this->marginLeft, $y, 'D', 20, true, [0.145, 0.392, 0.753]);
        $this->text($this->marginLeft + 25, $y, 'DIALAB', 18, true);
        $this->text($this->marginLeft + 25, $y - 12, 'Laboratorio de Analisis Clinicos', 9);
        $this->text($this->marginLeft + 25, $y - 22, 'Universidad Franz Tamayo - Centro de Diagnostico', 8);
        $this->text($this->marginLeft + 25, $y - 32, 'Tel: 2-2441234 | La Paz, Bolivia', 8);

        $this->text($this->pageWidth - $this->marginRight - 150, $y, $titulo, 16, true, [0.145, 0.392, 0.753]);
        $this->text($this->pageWidth - $this->marginRight - 150, $y - 12, '# ' . $numero, 10, true);
        $this->text($this->pageWidth - $this->marginRight - 150, $y - 22, 'Fecha: ' . $fecha, 9);

        $y -= 50;
        $this->currentY = $y;
    }

    public function infoPaciente($paciente) {
        $y = $this->currentY;

        $this->line($this->marginLeft, $y, $this->pageWidth - $this->marginRight, $y, 0.8, 0.8, 0.8);
        $y -= 8;

        $colW = ($this->pageWidth - $this->marginLeft - $this->marginRight) / 2;

        $this->rect($this->marginLeft, $y - 30, $colW, 35, 0.969, 0.976, 0.984);
        $this->text($this->marginLeft + 5, $y - 10, 'PACIENTE', 8, true, [0.145, 0.392, 0.753]);
        $this->text($this->marginLeft + 5, $y - 20, 'Nombre: ' . $paciente->nombre_completo, 9);
        $this->text($this->marginLeft + 5, $y - 30, 'CI: ' . $paciente->ci, 9);

        $this->rect($this->marginLeft + $colW, $y - 30, $colW, 35, 0.969, 0.976, 0.984);
        $this->text($this->marginLeft + $colW + 5, $y - 10, 'CONTACTO', 8, true, [0.145, 0.392, 0.753]);
        $this->text($this->marginLeft + $colW + 5, $y - 20, 'Edad: ' . $paciente->edad . ' anos | Sexo: ' . $paciente->sexo, 9);
        $this->text($this->marginLeft + $colW + 5, $y - 30, 'Tel: ' . ($paciente->telefono ?? '-'), 9);

        $this->currentY = $y - 40;
    }

    public function tablaResultados($grupos) {
        $y = $this->currentY;
        $contadorGrupo = 1;

        foreach ($grupos as $grupoKey => $grupo) {
            [$fechaGrupo, $horaGrupo] = explode('|', $grupoKey);
            $n = $grupo->count();

            if ($y < 150) {
                $this->newPage();
                $y = $this->currentY;
            }

            $this->rect($this->marginLeft, $y - 20, $this->pageWidth - $this->marginLeft - $this->marginRight, 18, 0.933, 0.961, 0.992);
            $this->text($this->marginLeft + 5, $y - 12, 'Reporte ' . $contadorGrupo, 10, true);
            $fechaStr = ($fechaGrupo !== 'sin-fecha' ? \Carbon\Carbon::parse($fechaGrupo)->format('d/m/Y') : 'Sin fecha');
            $horaStr = ($horaGrupo !== 'sin-hora' ? ' ' . $horaGrupo . ' hrs' : '');
            $this->text($this->marginLeft + 150, $y - 12, $fechaStr . $horaStr, 9);
            $this->text($this->pageWidth - $this->marginRight - 100, $y - 12, $n . ' analisis', 9, true);
            $y -= 25;

            $this->rect($this->marginLeft, $y - 16, $this->pageWidth - $this->marginLeft - $this->marginRight, 16, 0.945, 0.965, 0.98);
            $this->cell($this->marginLeft + 2, $y - 16, 18, 16, '#', 'C', true);
            $this->cell($this->marginLeft + 20, $y - 16, 140, 16, 'Analisis', 'L', true);
            $this->cell($this->marginLeft + 160, $y - 16, 70, 16, 'Muestra', 'L', true);
            $this->cell($this->marginLeft + 230, $y - 16, 100, 16, 'Resultado', 'L', true);
            $this->cell($this->marginLeft + 330, $y - 16, 80, 16, 'Referencia', 'L', true);
            $this->cell($this->marginLeft + 410, $y - 16, 60, 16, 'Estado', 'C', true);
            $y -= 18;

            $i = 1;
            foreach ($grupo as $r) {
                if ($y < 100) {
                    $this->newPage();
                    $y = $this->currentY;
                    $this->rect($this->marginLeft, $y - 16, $this->pageWidth - $this->marginLeft - $this->marginRight, 16, 0.945, 0.965, 0.98);
                    $this->cell($this->marginLeft + 2, $y - 16, 18, 16, '#', 'C', true);
                    $this->cell($this->marginLeft + 20, $y - 16, 140, 16, 'Analisis', 'L', true);
                    $this->cell($this->marginLeft + 160, $y - 16, 70, 16, 'Muestra', 'L', true);
                    $this->cell($this->marginLeft + 230, $y - 16, 100, 16, 'Resultado', 'L', true);
                    $this->cell($this->marginLeft + 330, $y - 16, 80, 16, 'Referencia', 'L', true);
                    $this->cell($this->marginLeft + 410, $y - 16, 60, 16, 'Estado', 'C', true);
                    $y -= 18;
                }

                $estadoColor = $r->estado == 'Validado' ? [0.125, 0.588, 0.314] : ($r->estado == 'Pendiente' ? [0.937, 0.647, 0.129] : [0.906, 0.2, 0.2]);
                $estadoBg = $r->estado == 'Validado' ? [0.812, 0.976, 0.878] : ($r->estado == 'Pendiente' ? [0.984, 0.957, 0.812] : [0.984, 0.855, 0.855]);

                $this->cell($this->marginLeft + 2, $y - 14, 18, 14, (string)$i, 'C');
                $this->cell($this->marginLeft + 20, $y - 14, 140, 14, $r->muestra->tipoAnalisis->nombre, 'L');
                $this->cell($this->marginLeft + 160, $y - 14, 70, 14, $r->muestra->codigo_muestra, 'L');
                $this->cell($this->marginLeft + 230, $y - 14, 100, 14, $r->resultado, 'L', false, null, [0.059, 0.584, 0.42]);
                $this->cell($this->marginLeft + 330, $y - 14, 80, 14, $r->valores_referencia ?? '', 'L');
                $this->cell($this->marginLeft + 410, $y - 14, 60, 14, $r->estado, 'C', true, $estadoBg, $estadoColor);

                $y -= 16;
                $i++;
            }
            $y -= 15;
            $contadorGrupo++;
        }

        $this->currentY = $y;
    }

    public function firma() {
        $y = $this->currentY;
        if ($y < 120) {
            $this->newPage();
            $y = $this->currentY;
        }

        $this->line($this->marginLeft, $y, $this->pageWidth - $this->marginRight, $y, 0.8, 0.8, 0.8);
        $y -= 30;

        $this->text($this->marginLeft, $y, '___________________________', 9);
        $this->text($this->marginLeft, $y - 10, 'Firma del Paciente', 8);
        $this->text($this->marginLeft, $y - 18, 'Nombre y CI', 7);

        $this->text($this->pageWidth - $this->marginRight - 200, $y, '___________________________', 9);
        $this->text($this->pageWidth - $this->marginRight - 200, $y - 10, 'Firma del Bioquimico', 8);
        $this->text($this->pageWidth - $this->marginRight - 200, $y - 18, 'Nombre y Reg. Prof.', 7);

        $y -= 50;
        $this->text($this->marginLeft, $y, 'Documento generado por DIALAB LIS el ' . \Carbon\Carbon::now()->format('d/m/Y H:i:s'), 7);
        $this->currentY = $y;
    }
}