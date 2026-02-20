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
use Symfony\Component\Security\Http\Attribute\IsGranted;

class IaController extends AbstractController
{
    public function __construct(
        private string $uploadDirectory
    ) {}

    #[Route('/ia-db', name: 'ia_db', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MEDECIN')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        OllamaService $ollama,
        PdfReaderService $pdfReader,
        GoogleSearchService $googleSearch
    ): Response {
        $currentMedecin = $this->getAuthenticatedMedecin();
        $question = $request->request->get('prompt', '');
        $answer = '';
        $pdfContent = '';
        $mode = 'db';
        $searchResults = '';

        if ($request->isMethod('POST') && $question !== '') {
            $session = $request->getSession();
            $uploadedFile = $request->files->get('pdf_file');

            // ================= MODE PDF =================
            if ($uploadedFile && $uploadedFile->isValid()) {
                $mode = 'pdf';
                $extension = strtolower($uploadedFile->getClientOriginalExtension());

                if ($extension !== 'pdf') {
                    $this->addFlash('error', 'Le fichier doit Ãªtre un PDF.');
                    return $this->redirectToRoute('ia_db');
                }

                $pdfPath = $uploadedFile->getRealPath();
                $rawPdfContent = $pdfReader->extractText($pdfPath);
                
                // NOUVEAU : DÃ©tection et parsing intelligent du format
                $parsedData = $this->parsePdfContent($rawPdfContent);
                $pdfContent = $parsedData['structured_text'];
                
                $session->set('ia_last_pdf_content', $pdfContent);
                $session->set('ia_pdf_structure', $parsedData['structure']);

                if (empty($pdfContent)) {
                    $answer = "Impossible d'extraire le contenu du PDF.";
                } else {
                    // Utiliser le prompt adaptÃ© au format dÃ©tectÃ©
                    $prompt = $this->buildSmartPdfPrompt($parsedData, $question);
                    $answer = $ollama->ask($prompt);
                }
            }

            // ================= MODE BASE DE DONNÃ‰ES =================
            else {
                $mode = 'db';
                if ($this->isForbiddenGlobalDbQuestion($question)) {
                    $answer = "Question non autorisee. Utilisez une question liee a vos patients uniquement (ex: \"Quels sont mes patients ?\").";
                } else {
                    $dbText = $this->buildDatabaseContext($em, $currentMedecin);
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
    // ============ NOUVEAU PARSING INTELLIGENT ================
    // =========================================================

    /**
     * Parse le contenu PDF pour dÃ©tecter et structurer le nouveau format
     */
    private function parsePdfContent(string $rawContent): array
    {
        $structure = [
            'format' => 'unknown',
            'document' => [],
            'rapports' => [],
            'ordonnances' => [],
            'metadata' => []
        ];

        // Nettoyage initial
        $cleaned = $this->cleanText($rawContent);
        
        // DÃ©tection du format
        $isNewFormat = $this->detectNewFormat($cleaned);
        
        if ($isNewFormat) {
            $structure['format'] = 'new_html';
            $structure = $this->parseNewFormat($cleaned, $structure);
        } else {
            $structure['format'] = 'legacy';
            // Parser l'ancien format si nÃ©cessaire
        }

        // GÃ©nÃ©ration du texte structurÃ© pour l'IA
        $structuredText = $this->generateStructuredText($structure);

        return [
            'structure' => $structure,
            'structured_text' => $structuredText,
            'raw' => $cleaned
        ];
    }

    /**
     * DÃ©tecte si c'est le nouveau format HTML
     */
    private function detectNewFormat(string $content): bool
    {
        $markers = [
            'dossier mÃ©dical',
            'document confidentiel',
            'usage mÃ©dical uniquement',
            'informations du document',
            'rapports mÃ©dicaux',
            'ordonnances',
            'consultation des dÃ©tails',
            'badge', // Ã‰lÃ©ments visuels du nouveau format
            'progress-bar',
        ];

        $contentLower = mb_strtolower($content, 'UTF-8');
        $score = 0;
        
        foreach ($markers as $marker) {
            if (str_contains($contentLower, $marker)) {
                $score++;
            }
        }

        return $score >= 3; // Au moins 3 marqueurs pour confirmer
    }

    /**
     * Parse le nouveau format HTML/PDF structurÃ©
     */
    private function parseNewFormat(string $content, array $structure): array
    {
        // Extraction par motifs regex structurÃ©s
        
        // SECTION DOCUMENT
        if (preg_match('/Informations du Document.*?Nom[:\s]+([^\n]+)/si', $content, $matches)) {
            $structure['document']['nom'] = trim($matches[1]);
        }
        if (preg_match('/Type[:\s]+([^\n]+)/si', $content, $matches)) {
            $structure['document']['type'] = trim($matches[1]);
        }
        if (preg_match('/Description[:\s]+(.*?)(?=Date de crÃ©ation|$)/si', $content, $matches)) {
            $structure['document']['description'] = trim($matches[1]);
        }
        if (preg_match('/Date de crÃ©ation[:\s]+([^\n]+)/si', $content, $matches)) {
            $structure['document']['date_creation'] = trim($matches[1]);
        }

        // SECTION RAPPORTS MÃ‰DICAUX
        // Pattern pour capturer chaque bloc de rapport
        $rapportPattern = '/Rapport #(\d+).*?Motif[:\s]+(.*?)(?=Diagnostic|$)/si';
        if (preg_match_all('/Rapport #(\d+)(.*?)(?=Rapport #|\Z)/si', $content, $rapportBlocks, PREG_SET_ORDER)) {
            foreach ($rapportBlocks as $block) {
                $rapportId = $block[1];
                $rapportContent = $block[2];
                
                $rapport = [
                    'id' => $rapportId,
                    'motif' => $this->extractField($rapportContent, 'Motif'),
                    'diagnostic' => $this->extractField($rapportContent, 'Diagnostic'),
                    'observations' => $this->extractField($rapportContent, 'Observations'),
                    'recommandations' => $this->extractField($rapportContent, 'Recommandations'),
                    'traitements' => $this->extractField($rapportContent, 'Traitements'),
                ];
                
                $structure['rapports'][] = $rapport;
            }
        }

        // SECTION ORDONNANCES
        if (preg_match_all('/Ordonnance #(\d+)(.*?)(?=Ordonnance #|\Z)/si', $content, $ordoBlocks, PREG_SET_ORDER)) {
            foreach ($ordoBlocks as $block) {
                $ordoId = $block[1];
                $ordoContent = $block[2];
                
                $ordonnance = [
                    'id' => $ordoId,
                    'medicaments' => $this->extractField($ordoContent, 'MÃ©dicaments'),
                    'posologie' => $this->extractField($ordoContent, 'Posologie'),
                    'instructions' => $this->extractField($ordoContent, 'Instructions'),
                    'notes' => $this->extractField($ordoContent, 'Notes'),
                ];
                
                $structure['ordonnances'][] = $ordonnance;
            }
        }

        // MÃ©tadonnÃ©es
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $content, $matches)) {
            $structure['metadata']['date_document'] = $matches[1];
        }
        if (preg_match('/#(\d+)/', $content, $matches)) {
            $structure['metadata']['document_id'] = $matches[1];
        }

        return $structure;
    }

    /**
     * Extrait un champ spÃ©cifique du contenu
     */
    private function extractField(string $content, string $fieldName): string
    {
        $pattern = '/' . preg_quote($fieldName, '/') . '[:\s]+(.*?)(?=[A-Z][a-z]+[:\s]|\Z)/si';
        if (preg_match($pattern, $content, $matches)) {
            $value = trim($matches[1]);
            // Nettoyer les artefacts d'encodage
            $value = $this->fixEncodingArtifacts($value);
            return $value;
        }
        return '';
    }

    /**
     * Corrige les artefacts d'encodage courants
     */
    private function fixEncodingArtifacts(string $text): string
    {
        $replacements = [
            'ÃƒÂ©' => 'Ã©',
            'ÃƒÂ¨' => 'Ã¨',
            'Ãƒ ' => 'Ã ',
            'ÃƒÂ´' => 'Ã´',
            'ÃƒÂª' => 'Ãª',
            'ÃƒÂ§' => 'Ã§',
            'Ãƒâ€°' => 'Ã‰',
            'Ãƒâ‚¬' => 'Ã€',
            'Ãƒ' => 'Ã ', // Cas gÃ©nÃ©ral
            'Ã‚' => '',
            '  ' => ' ',
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * GÃ©nÃ¨re un texte structurÃ© Ã  partir de la structure parsÃ©e
     */
    private function generateStructuredText(array $structure): string
    {
        $text = "=== DOSSIER MÃ‰DICAL STRUCTURÃ‰ ===\n\n";

        // Document
        if (!empty($structure['document'])) {
            $text .= "ðŸ“„ INFORMATIONS DU DOCUMENT\n";
            foreach ($structure['document'] as $key => $value) {
                $text .= ucfirst($key) . ": " . $value . "\n";
            }
            $text .= "\n";
        }

        // Rapports
        if (!empty($structure['rapports'])) {
            $text .= "ðŸ©º RAPPORTS MÃ‰DICAUX (" . count($structure['rapports']) . ")\n";
            foreach ($structure['rapports'] as $rapport) {
                $text .= "---\n";
                $text .= "Rapport #" . $rapport['id'] . "\n";
                foreach ($rapport as $key => $value) {
                    if ($key !== 'id' && !empty($value)) {
                        $text .= ucfirst($key) . ": " . $value . "\n";
                    }
                }
                $text .= "\n";
            }
        }

        // Ordonnances
        if (!empty($structure['ordonnances'])) {
            $text .= "ðŸ’Š ORDONNANCES (" . count($structure['ordonnances']) . ")\n";
            foreach ($structure['ordonnances'] as $ordo) {
                $text .= "---\n";
                $text .= "Ordonnance #" . $ordo['id'] . "\n";
                foreach ($ordo as $key => $value) {
                    if ($key !== 'id' && !empty($value)) {
                        $text .= ucfirst($key) . ": " . $value . "\n";
                    }
                }
                $text .= "\n";
            }
        }

        return $text;
    }

    // =========================================================
    // ============ NOUVEAUX PROMPTS INTELLIGENTS ==============
    // =========================================================

    private function buildSmartPdfPrompt(array $parsedData, string $question): string
    {
        $format = $parsedData['structure']['format'];
        $structuredText = $parsedData['structured_text'];
        
        $formatInstructions = $format === 'new_html' 
            ? $this->getNewFormatInstructions() 
            : $this->getLegacyFormatInstructions();

        return <<<PROMPT
Tu es un assistant mÃ©dical spÃ©cialisÃ© en analyse de dossiers patients.

FORMAT DÃ‰TECTÃ‰ : {$format}

{$formatInstructions}

DONNÃ‰ES EXTRAITES DU PDF :
---
{$structuredText}
---

QUESTION DE L'UTILISATEUR :
{$question}

INSTRUCTIONS DE RÃ‰PONSE :
1. Utilise UNIQUEMENT les informations fournies ci-dessus
2. Si l'information n'existe pas, rÃ©ponds exactement : "Information non disponible dans ce dossier mÃ©dical."
3. Cite les numÃ©ros de rapport ou d'ordonnance quand tu t'y rÃ©fÃ¨res
4. Sois prÃ©cis et concis

RÃ‰PONSE :
PROMPT;
    }

    private function getNewFormatInstructions(): string
    {
        return <<<INSTRUCTIONS
Ce document utilise le NOUVEAU FORMAT HTML structurÃ© :
- Sections clairement dÃ©limitÃ©es (Document, Rapports, Ordonnances)
- Champs normalisÃ©s : Motif, Diagnostic, Observations, Recommandations, Traitements
- MÃ©dicaments avec posologie dÃ©taillÃ©e
- IDs de rÃ©fÃ©rence pour chaque Ã©lÃ©ment
INSTRUCTIONS;
    }

    private function getLegacyFormatInstructions(): string
    {
        return <<<INSTRUCTIONS
Ce document utilise l'ANCIEN FORMAT texte brut.
Analyse le contenu librement en extrayant les informations mÃ©dicales pertinentes.
INSTRUCTIONS;
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
            'pas trouvÃ©',
            'ne figure pas',
            'aucun rÃ©sultat',
            'non disponible dans ce dossier mÃ©dical',
        ];

        $answer = mb_strtolower($answer, 'UTF-8');
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
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        // Conserver la structure tout en nettoyant
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $text = preg_replace('/\n{3,}/u', "\n\n", $text);
        return trim($text);
    }

    private function getAuthenticatedMedecin(): Medecin
    {
        $user = $this->getUser();

        if (!$user instanceof Medecin) {
            throw $this->createAccessDeniedException('Acces reserve aux medecins.');
        }

        return $user;
    }

    private function isForbiddenGlobalDbQuestion(string $question): bool
    {
        $q = mb_strtolower($question, 'UTF-8');

        $mentionsPatients = str_contains($q, 'patient') || str_contains($q, 'patients');
        $explicitOwnerScope = str_contains($q, 'mes patient') || str_contains($q, 'mes patients');
        $globalScope = str_contains($q, 'base de don')
            || str_contains($q, 'dans la base')
            || str_contains($q, 'tous les patients')
            || str_contains($q, 'liste des patients')
            || str_contains($q, 'quels sont les patients');

        return $mentionsPatients && $globalScope && !$explicitOwnerScope;
    }

    // =========================================================
    // ================= BASE DE DONNÃ‰ES =======================
    // =========================================================

    private function buildDatabaseContext(EntityManagerInterface $em, Medecin $currentMedecin): string
    {
        $rapports = $em->getRepository(Rapport::class)->findBy(['medecin' => $currentMedecin]);
        $ordonnances = $em->getRepository(Ordonnance::class)->findBy(['medecin' => $currentMedecin]);
        $documents = $em->getRepository(Document::class)->findBy(['medecin' => $currentMedecin]);
        $rdvs = $em->getRepository(RendezVous::class)->findBy(['doctor' => $currentMedecin]);

        $dbText = "BASE DE DONNÃ‰ES MÃ‰DICALE\n\n";
        $dbText .= "MEDECIN CONNECTE: Dr {$currentMedecin->getFullName()} (ID: {$currentMedecin->getId()})\n";
        $dbText .= "PORTEE: donnees limitees a ce medecin uniquement.\n\n";

        $patientsById = [];
        foreach ($rapports as $r) {
            $p = $r->getPatient();
            if ($p instanceof Patient) {
                $patientsById[$p->getId()] = $p;
            }
        }
        foreach ($ordonnances as $o) {
            $p = $o->getPatient();
            if ($p instanceof Patient) {
                $patientsById[$p->getId()] = $p;
            }
        }
        foreach ($documents as $d) {
            $p = $d->getPatient();
            if ($p instanceof Patient) {
                $patientsById[$p->getId()] = $p;
            }
        }
        foreach ($rdvs as $rdv) {
            $p = $rdv->getPatient();
            if ($p instanceof Patient) {
                $patientsById[$p->getId()] = $p;
            }
        }

        // PATIENTS
        $dbText .= "PATIENTS:\n";
        foreach ($patientsById as $p) {
            $dbText .= "- ID: {$p->getId()} | Nom: {$p->getFullName()} | Email: {$p->getEmail()}\n";
        }
        if (count($patientsById) === 0) {
            $dbText .= "- Aucun patient lie a ce medecin.\n";
        }

        // RAPPORTS
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
            $dbText .= "  MÃ©decin: Dr {$medecin}\n";
            $dbText .= "  idDocument: {$docId}\n\n";
        }

        // ORDONNANCES
        $dbText .= "\nORDONNANCES:\n";
        foreach ($ordonnances as $o) {
            $date = $o->getDateOrdonnance()?->format('Y-m-d');
            $patient = $o->getPatient()?->getFullName();
            $medecin = $o->getMedecin()?->getFullName();
            $docId = $o->getDocument()?->getId() ?? 'NULL';

            $dbText .= "- Ordonnance #{$o->getId()}\n";
            $dbText .= "  Date: {$date}\n";
            $dbText .= "  MÃ©dicament: {$o->getMedicament()}\n";
            $dbText .= "  Patient: {$patient}\n";
            $dbText .= "  MÃ©decin: Dr {$medecin}\n";
            $dbText .= "  idDocument: {$docId}\n\n";
        }

        // DOCUMENTS
        $dbText .= "\nDOCUMENTS:\n";
        foreach ($documents as $d) {
            $dbText .= "- ID: {$d->getId()} | Nom: {$d->getNom()} | Type: {$d->getType()}\n";
        }

        // RENDEZ-VOUS
        $dbText .= "\nRENDEZ-VOUS:\n";
        foreach ($rdvs as $r) {
            $date = $r->getAppointmentDate()?->format('Y-m-d H:i');
            $dbText .= "- RDV #{$r->getId()} | Date: {$date}\n";
            $dbText .= "  Patient: {$r->getPatient()->getFullName()}\n";
            $dbText .= "  MÃ©decin: Dr {$r->getDoctor()->getFullName()}\n";
            $dbText .= "  Statut: {$r->getStatut()}\n\n";
        }

        return $dbText;
    }

    // =========================================================
    // ======================= PROMPTS =========================
    // =========================================================

    private function buildDbPrompt(string $dbText, string $question): string
    {
        return <<<PROMPT
Tu es un assistant expert en base de donnÃ©es mÃ©dicale.

STRUCTURE IMPORTANTE :

TABLE ORDONNANCE :
- id
- dateOrdonnance
- medicament
- idPatient
- idMedecin
- idDocument (peut Ãªtre NULL)

TABLE RAPPORT :
- id
- dateRapport
- diagnosis
- idPatient
- idMedecin
- idDocument (peut Ãªtre NULL)

RÃˆGLE :
- Si idDocument = NULL â†’ NON inclus dans un document.
- Si idDocument contient un nombre â†’ inclus dans un document.
- Repondre uniquement avec les donnees du medecin connecte.
- Refuser les demandes globales sur toute la base (ex: tous les patients).

Si la rÃ©ponse n'existe pas, dis EXACTEMENT :
"Information non disponible dans la base de donnÃ©es."

DONNÃ‰ES :
{$dbText}

QUESTION :
{$question}
PROMPT;
    }

    private function buildGooglePrompt(string $searchResults, string $question): string
    {
        return <<<PROMPT
Tu es un assistant mÃ©dical intelligent.

La rÃ©ponse n'a pas Ã©tÃ© trouvÃ©e dans la base interne.
Voici des rÃ©sultats de recherche Google :

---
{$searchResults}
---

QUESTION :
{$question}

RÃ©ponds clairement et professionnellement.
PROMPT;
    }
}
