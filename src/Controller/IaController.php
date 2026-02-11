<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\Rapport;
use App\Entity\Ordonnance;
use App\Entity\Document;
use App\Entity\RendezVous;
use App\Service\OllamaService;
use App\Service\PdfReaderService;
use App\Service\GoogleSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IaController extends AbstractController
{
    public function __construct(
        private string $uploadDirectory
    ) {}

    #[Route('/ia-db', name: 'ia_db', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        OllamaService $ollama,
        PdfReaderService $pdfReader,
        GoogleSearchService $googleSearch
    ): Response {
        $question = $request->request->get('prompt', '');
        $answer = '';
        $pdfContent = '';
        $mode = 'db';
        $searchResults = '';

        if ($request->isMethod('POST') && $question !== '') {
            $uploadedFile = $request->files->get('pdf_file');

            // ================= MODE PDF =================
            if ($uploadedFile && $uploadedFile->isValid()) {
                $mode = 'pdf';
                $extension = strtolower($uploadedFile->getClientOriginalExtension());

                if ($extension !== 'pdf') {
                    $this->addFlash('error', 'Le fichier doit être un PDF.');
                    return $this->redirectToRoute('ia_db');
                }

                $pdfPath = $uploadedFile->getRealPath();
                $rawPdfContent = $pdfReader->extractText($pdfPath);
                $pdfContent = $this->cleanText($rawPdfContent);

                if (empty($pdfContent)) {
                    $answer = "Impossible d'extraire le contenu du PDF.";
                } else {
                    $prompt = $this->buildPdfPrompt(substr($pdfContent, 0, 6000), $question);
                    $answer = $ollama->ask($prompt);
                }
            }

            // ================= MODE BASE DE DONNÉES =================
            else {
                $mode = 'db';
                $dbText = $this->buildDatabaseContext($em);
                $dbPrompt = $this->buildDbPrompt($dbText, $question);

                $dbAnswer = $ollama->ask($dbPrompt);

                if ($this->isInformationNotAvailable($dbAnswer)) {
                    // ================= MODE GOOGLE =================
                    $mode = 'google';
                    $searchResults = $googleSearch->search($question);
                    $googlePrompt = $this->buildGooglePrompt($searchResults, $question);
                    $answer = $ollama->ask($googlePrompt);
                } else {
                    $answer = $dbAnswer;
                }
            }
        }

        return $this->render('ia/index.html.twig', [
            'prompt' => $question,
            'answer' => $answer,
            'pdf_content' => $pdfContent,
            'mode' => $mode,
            'search_results' => $searchResults,
        ]);
    }

    // =========================================================
    // ===================== UTILITAIRES ======================
    // =========================================================

    private function isInformationNotAvailable(string $answer): bool
    {
        $phrases = [
            'information non disponible',
            'non disponible dans la base',
            'je ne trouve pas',
            'aucune information',
            'pas trouvé',
            'ne figure pas',
            'aucun résultat',
        ];

        $answer = strtolower($answer);
        foreach ($phrases as $phrase) {
            if (str_contains($answer, $phrase)) {
                return true;
            }
        }
        return false;
    }

    private function cleanText(string $text): string
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    // =========================================================
    // ================= BASE DE DONNÉES =======================
    // =========================================================

    private function buildDatabaseContext(EntityManagerInterface $em): string
    {
        $patients = $em->getRepository(Patient::class)->findAll();
        $medecins = $em->getRepository(Medecin::class)->findAll();
        $rapports = $em->getRepository(Rapport::class)->findAll();
        $ordonnances = $em->getRepository(Ordonnance::class)->findAll();
        $documents = $em->getRepository(Document::class)->findAll();
        $rdvs = $em->getRepository(RendezVous::class)->findAll();

        $dbText = "BASE DE DONNÉES MÉDICALE\n\n";

        // ---------------- PATIENTS ----------------
        $dbText .= "PATIENTS:\n";
        foreach ($patients as $p) {
            $dbText .= "- ID: {$p->getId()} | Nom: {$p->getFullName()} | Email: {$p->getEmail()}\n";
        }

        // ---------------- MEDECINS ----------------
        $dbText .= "\nMEDECINS:\n";
        foreach ($medecins as $m) {
            $dbText .= "- ID: {$m->getId()} | Dr {$m->getFullName()} | Spécialité: {$m->getSpecialty()}\n";
        }

        // ---------------- RAPPORTS ----------------
        $dbText .= "\nRAPPORTS:\n";
        foreach ($rapports as $r) {
            $date = $r->getCreatedAt()?->format('Y-m-d');
            $patient = $r->getPatient()?->getFullName();
            $medecin = $r->getMedecin()?->getFullName();
            $docId = $r->getDocument()?->getId() ?? 'NULL';

            $dbText .= "- Rapport #{$r->getId()}\n";
            $dbText .= "  Date: {$date}\n";
            $dbText .= "  Diagnostic: {$r->getDiagnosis()}\n";
            $dbText .= "  Patient: {$patient}\n";
            $dbText .= "  Médecin: Dr {$medecin}\n";
            $dbText .= "  idDocument: {$docId}\n\n";
        }

        // ---------------- ORDONNANCES ----------------
        $dbText .= "\nORDONNANCES:\n";
        foreach ($ordonnances as $o) {
            $date = $o->getDateOrdonnance()?->format('Y-m-d');
            $patient = $o->getPatient()?->getFullName();
            $medecin = $o->getMedecin()?->getFullName();
            $docId = $o->getDocument()?->getId() ?? 'NULL';

            $dbText .= "- Ordonnance #{$o->getId()}\n";
            $dbText .= "  Date: {$date}\n";
            $dbText .= "  Médicament: {$o->getMedicament()}\n";
            $dbText .= "  Patient: {$patient}\n";
            $dbText .= "  Médecin: Dr {$medecin}\n";
            $dbText .= "  idDocument: {$docId}\n\n";
        }

        // ---------------- DOCUMENTS ----------------
        $dbText .= "\nDOCUMENTS:\n";
        foreach ($documents as $d) {
            $dbText .= "- ID: {$d->getId()} | Nom: {$d->getNom()} | Type: {$d->getType()}\n";
        }

        // ---------------- RENDEZ-VOUS ----------------
        $dbText .= "\nRENDEZ-VOUS:\n";
        foreach ($rdvs as $r) {
            $date = $r->getAppointmentDate()?->format('Y-m-d H:i');
            $dbText .= "- RDV #{$r->getId()} | Date: {$date}\n";
            $dbText .= "  Patient: {$r->getPatient()->getFullName()}\n";
            $dbText .= "  Médecin: Dr {$r->getDoctor()->getFullName()}\n";
            $dbText .= "  Statut: {$r->getStatut()}\n\n";
        }

        return $dbText;
    }

    // =========================================================
    // ======================= PROMPTS =========================
    // =========================================================

    private function buildPdfPrompt(string $pdfContent, string $question): string
    {
        return <<<PROMPT
Tu es un assistant médical spécialisé.
Réponds UNIQUEMENT avec les informations du PDF.
Si l'information n'existe pas, dis exactement :
"Information non disponible dans ce dossier médical."

CONTENU PDF :
---
$pdfContent
---

QUESTION :
$question
PROMPT;
    }

    private function buildDbPrompt(string $dbText, string $question): string
    {
        return <<<PROMPT
Tu es un assistant expert en base de données médicale.

STRUCTURE IMPORTANTE :

TABLE ORDONNANCE :
- id
- dateOrdonnance
- medicament
- idPatient
- idMedecin
- idDocument (peut être NULL)

TABLE RAPPORT :
- id
- dateRapport
- diagnosis
- idPatient
- idMedecin
- idDocument (peut être NULL)

RÈGLE :
- Si idDocument = NULL → NON inclus dans un document.
- Si idDocument contient un nombre → inclus dans un document.

Si la réponse n'existe pas, dis EXACTEMENT :
"Information non disponible dans la base de données."

DONNÉES :
$dbText

QUESTION :
$question
PROMPT;
    }

    private function buildGooglePrompt(string $searchResults, string $question): string
    {
        return <<<PROMPT
Tu es un assistant médical intelligent.

La réponse n'a pas été trouvée dans la base interne.
Voici des résultats de recherche Google :

---
$searchResults
---

QUESTION :
$question

Réponds clairement et professionnellement.
PROMPT;
    }
}
