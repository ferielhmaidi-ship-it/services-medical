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
        $mode = 'db'; // 'db', 'pdf', ou 'google'
        $searchResults = '';

        if ($request->isMethod('POST') && $question !== '') {
            $uploadedFile = $request->files->get('pdf_file');

            if ($uploadedFile && $uploadedFile->isValid()) {
                // ====== MODE PDF ======
                $mode = 'pdf';
                $extension = strtolower($uploadedFile->getClientOriginalExtension());
                
                if ($extension !== 'pdf') {
                    $this->addFlash('error', 'Le fichier doit être au format PDF.');
                    return $this->redirectToRoute('ia_db');
                }

                $pdfPath = $uploadedFile->getRealPath();
                $rawPdfContent = $pdfReader->extractText($pdfPath);
                $pdfContent = $this->cleanText($rawPdfContent);
                
                if (empty($pdfContent)) {
                    $answer = "Impossible d'extraire le contenu du PDF.";
                } else {
                    $pdfContentLimited = substr($pdfContent, 0, 6000);
                    $prompt = $this->buildPdfPrompt($pdfContentLimited, $question);
                    $answer = $ollama->ask($prompt);
                }
                
            } else {
                // ====== MODE BASE DE DONNÉES ======
                $mode = 'db';
                $dbText = $this->buildDatabaseContext($em);
                $dbPrompt = $this->buildDbPrompt($dbText, $question);
                
                // Première requête à Ollama avec la base de données
                $dbAnswer = $ollama->ask($dbPrompt);
                
                // Vérifier si l'information n'est pas disponible
                if ($this->isInformationNotAvailable($dbAnswer)) {
                    // ====== MODE GOOGLE ======
                    $mode = 'google';
                    $searchResults = $googleSearch->search($question);
                    
                    // Deuxième requête avec les résultats Google
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
            'search_results' => $searchResults
        ]);
    }

    /**
     * Vérifie si la réponse indique que l'info n'est pas disponible
     */
    private function isInformationNotAvailable(string $answer): bool
    {
        $phrases = [
            'information non disponible',
            'non disponible dans la base',
            'non disponible dans ce dossier',
            'je ne trouve pas',
            'aucune information',
            'pas trouvé',
            'pas dans la base',
            'ne figure pas',
            'inexistant',
            'aucun résultat'
        ];
        
        $answerLower = strtolower($answer);
        foreach ($phrases as $phrase) {
            if (str_contains($answerLower, $phrase)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Nettoie le texte pour enlever les caractères non-UTF8
     */
    private function cleanText(string $text): string
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        $text = preg_replace('/[^\x{0000}-\x{FFFF}]/u', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Construit le contexte de la base de données
     */
    private function buildDatabaseContext(EntityManagerInterface $em): string
    {
        $patients = $em->getRepository(Patient::class)->findAll();
        $medecins = $em->getRepository(Medecin::class)->findAll();
        $rapports = $em->getRepository(Rapport::class)->findAll();
        $ordonnances = $em->getRepository(Ordonnance::class)->findAll();
        $documents = $em->getRepository(Document::class)->findAll();
        $rdvs = $em->getRepository(RendezVous::class)->findAll();

        $dbText = "BASE DE DONNÉES MÉDICALE\n\n";

        $dbText .= "PATIENTS:\n";
        foreach ($patients as $p) {
            $dbText .= "- {$p->getNom()} {$p->getPrenom()}\n";
        }

        $dbText .= "\nMEDECINS:\n";
        foreach ($medecins as $m) {
            $dbText .= "- Dr {$m->getNom()}\n";
        }

        $dbText .= "\nRAPPORTS:\n";
        foreach ($rapports as $r) {
            $dbText .= "- Rapport #{$r->getIdrapport()} : {$r->getDiagnosis()}\n";
        }

        $dbText .= "\nORDONNANCES:\n";
        foreach ($ordonnances as $o) {
            $dbText .= "- Ordonnance #{$o->getIdordonnance()} : {$o->getMedicament()}\n";
        }

        $dbText .= "\nDOCUMENTS:\n";
        foreach ($documents as $d) {
            $dbText .= "- {$d->getNom()} ({$d->getType()})\n";
        }

        $dbText .= "\nRENDEZ-VOUS:\n";
        foreach ($rdvs as $r) {
            $dbText .= "- RDV #{$r->getIdrendezvous()}\n";
        }

        return $dbText;
    }

    /**
     * Prompt pour analyse PDF
     */
    private function buildPdfPrompt(string $pdfContent, string $question): string
    {
        return "Tu es un assistant medical specialise dans l'analyse de dossiers medicaux PDF.

Tu as recu le contenu d'un dossier medical. Reponds UNIQUEMENT selon ces donnees.
Si l'information n'existe pas, dis : 'Information non disponible dans ce dossier medical.'

Contenu :
---
$pdfContent
---

Question : $question

Reponds de maniere professionnelle et concise.";
    }

    /**
     * Prompt pour questions base de données
     */
    private function buildDbPrompt(string $dbText, string $question): string
    {
        return "Tu es un assistant medical. Tu as acces a une base de donnees medicale.

IMPORTANT : Si la reponse n'est pas dans les donnees ci-dessous, dis EXACTEMENT : 
'Information non disponible dans la base de donnees.'

Donnees :
$dbText

Question : $question

Reponds de maniere professionnelle. Si tu ne trouves pas l'information, utilise la phrase exacte demandee ci-dessus.";
    }

    /**
     * Prompt pour recherche Google
     */
    private function buildGooglePrompt(string $searchResults, string $question): string
    {
        return "Tu es un assistant medical intelligent. 

La question de l'utilisateur n'a pas trouve de reponse dans la base de donnees interne.
J'ai effectue une recherche sur Google pour trouver des informations complementaires.

Resultats de recherche :
---
$searchResults
---

Question originale : $question

Base-toi sur les resultats de recherche ci-dessus pour repondre a la question.
Si les resultats ne permettent pas de repondre completement, indique les sources trouvees et ce que tu as pu en deduire.
Reponds de maniere professionnelle, claire et utile.";
    }
}