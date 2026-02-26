<?php

namespace App\Service;

class PdfReaderService
{
    private ?object $parser = null;

    public function __construct()
    {
        $parserClass = 'Smalot\\PdfParser\\Parser';

        if (class_exists($parserClass)) {
            $this->parser = new $parserClass();
        }
    }

    public function extractText(string $pdfPath): string
    {
        if ($this->parser === null) {
            return 'Erreur lecture PDF : la librairie smalot/pdfparser n\'est pas installee.';
        }

        try {
            $pdf = $this->parser->parseFile($pdfPath);

            return $pdf->getText();
        } catch (\Throwable $e) {
            return 'Erreur lecture PDF : ' . $e->getMessage();
        }
    }
}