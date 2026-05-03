<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$tcpdf = __DIR__ . '/tcpdf/tcpdf.php';
if (!file_exists($tcpdf)) {
    echo json_encode(array('error' => 'tcpdf.php not found at: ' . $tcpdf));
    exit;
}

require_once $tcpdf;

class TestPDF extends TCPDF {
    public function Header() {}
    public function Footer() {}
}

try {
    $pdf = new TestPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Test PDF', 0, 1);

    $bills_dir = __DIR__ . '/bills/';
    if (!is_dir($bills_dir)) mkdir($bills_dir, 0755, true);
    $filepath = $bills_dir . 'test_' . time() . '.pdf';

    $pdf->Output($filepath, 'F');

    $buffered = ob_get_clean();
    if (!empty($buffered)) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'tcpdf_output' => $buffered));
    } else {
        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'file' => $filepath, 'exists' => file_exists($filepath)));
    }
} catch (Exception $e) {
    $buffered = ob_get_clean();
    header('Content-Type: application/json');
    echo json_encode(array('success' => false, 'exception' => $e->getMessage(), 'buffer' => $buffered));
}
?>