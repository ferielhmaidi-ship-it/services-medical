<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Rapport;
use App\Entity\Ordonnance;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DocumentPdfController extends AbstractController
{
    #[Route('/document/{iddocument}/pdf', name: 'document_pdf')]
    public function pdf(int $iddocument, EntityManagerInterface $em): Response
    {
        // ===== Récupérer le document =====
        $document = $em->getRepository(Document::class)->find($iddocument);
        if (!$document) {
            throw $this->createNotFoundException('Document introuvable');
        }

        // ===== Récupérer les rapports liés =====
        $rapports = $em->getRepository(Rapport::class)->findBy(['iddocument' => $document]);

        // ===== Récupérer les ordonnances liées =====
        $ordonnances = $em->getRepository(Ordonnance::class)->findBy(['iddocument' => $document]);

        // ===== Configurer Dompdf =====
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);

        // ===== Générer HTML =====
        $html = '<h1 style="text-align:center;">Dossier Médical</h1>';

        // ===== DOCUMENT =====
        $html .= '<h2>📄 Document</h2>';
        $html .= '<table style="width:100%;border-collapse:collapse;">';
        $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Nom</th><td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($document->getNom()) . '</td></tr>';
        $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Type</th><td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($document->getType()) . '</td></tr>';
        $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Description</th><td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($document->getDescription()) . '</td></tr>';
        $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Créé le</th><td style="border:1px solid #333;padding:5px;">' . ($document->getCreatedAt() ? $document->getCreatedAt()->format('d/m/Y H:i') : '-') . '</td></tr>';
        $html .= '</table>';

        // ===== RAPPORTS =====
        $html .= '<h2>🩺 Rapports Médicaux</h2>';
        if ($rapports) {
            foreach ($rapports as $r) {
                $html .= '<h3>Rapport #' . $r->getIdrapport() . '</h3>';
                $html .= '<table style="width:100%;border-collapse:collapse;">';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Motif</th><td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($r->getConsultationReason()) . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Diagnostic</th><td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($r->getDiagnosis()) . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Observations</th><td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($r->getObservations()) . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Recommandations</th><td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($r->getRecommendations()) . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Traitements</th><td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($r->getTreatments()) . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Date</th><td style="border:1px solid #333;padding:5px;">' . ($r->getCreatedAt() ? $r->getCreatedAt()->format('d/m/Y H:i') : '-') . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Patient</th><td style="border:1px solid #333;padding:5px;">' . ($r->getIdpatient() ? htmlspecialchars($r->getIdpatient()->getPrenom() . ' ' . $r->getIdpatient()->getNom()) : '-') . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Médecin</th><td style="border:1px solid #333;padding:5px;">' . ($r->getIdmedecin() ? htmlspecialchars($r->getIdmedecin()->getNom()) : '-') . '</td></tr>';
                $html .= '</table><br>';
            }
        } else {
            $html .= '<p>Aucun rapport.</p>';
        }

        // ===== ORDONNANCES =====
        $html .= '<h2>💊 Ordonnances</h2>';
        if ($ordonnances) {
            foreach ($ordonnances as $o) {
                $html .= '<h3>Ordonnance #' . $o->getIdordonnance() . '</h3>';
                $html .= '<table style="width:100%;border-collapse:collapse;">';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Médicament</th><td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($o->getMedicament()) . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Posologie</th><td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($o->getPosologie()) . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Instructions</th><td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($o->getInstructions()) . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Notes</th><td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($o->getNotes()) . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Date</th><td style="border:1px solid #333;padding:5px;">' . ($o->getDateordonnance() ? $o->getDateordonnance()->format('d/m/Y H:i') : '-') . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Patient</th><td style="border:1px solid #333;padding:5px;">' . ($o->getIdpatient() ? htmlspecialchars($o->getIdpatient()->getPrenom() . ' ' . $o->getIdpatient()->getNom()) : '-') . '</td></tr>';
                $html .= '<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Médecin</th><td style="border:1px solid #333;padding:5px;">' . ($o->getIdmedecin() ? htmlspecialchars($o->getIdmedecin()->getNom()) : '-') . '</td></tr>';
                $html .= '</table><br>';
            }
        } else {
            $html .= '<p>Aucune ordonnance.</p>';
        }

        // ===== Générer le PDF =====
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();

        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="document_' . $document->getIddocument() . '.pdf"');
        $response->headers->set('Content-Length', strlen($pdfContent));

        return $response;
    }
}