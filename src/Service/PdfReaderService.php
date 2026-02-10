<?php
// src/Service/PdfReaderService.php
namespace App\Service;  // ← Doit être App\Service, pas App\Controller

use Smalot\PdfParser\Parser;

class PdfReaderService
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    public function extractText(string $pdfPath): string
    {
        try {
            $pdf = $this->parser->parseFile($pdfPath);
            return $pdf->getText();
        } catch (\Exception $e) {
            return 'Erreur lecture PDF : ' . $e->getMessage();
        }
    }
}