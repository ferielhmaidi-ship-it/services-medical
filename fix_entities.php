<?php
/**
 * Comprehensive fix script to remove all RendezVous CLASS references.
 * 
 * Strategy:
 * - Fix entity files: Medecin, Patient, Feedback, Rapport, Ordonnance
 * - Fix controllers: PatientFeedbackController, IaController, HomeController, TestEmailController
 * - Fix form types: RapportType, OrdonnanceType (ensure they use Appointment::class with property_path)
 * - Delete legacy files: RendezVous.php, RendezVousRepository.php, RendezVousType.php
 * 
 * Run: php fix_entities.php
 */

$baseDir = __DIR__;

// ============================================================
// PART 1: Fix entity files by complete replacement
// ============================================================

$medecin = <<<'PHP'
<?php

namespace App\Entity;

use App\Repository\MedecinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: MedecinRepository::class)]
#[ORM\Table(name: 'medecins')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['cin'], message: 'This CIN is already registered.')]
class Medecin extends BaseUser
{
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $specialty;

    #[ORM\Column(type: 'string', length: 8, unique: true)]
    private string $cin;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $governorate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $education = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $experience = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false;

    #[ORM\OneToMany(mappedBy: 'medecin', targetEntity: Reponse::class, orphanRemoval: true)]
    private $reponses;

    #[ORM\Column(type: 'float', nullable: true, name: 'ai_average_score')]
    private ?float $aiAverageScore = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true, name: 'ai_score_updated_at')]
    private ?\DateTimeImmutable $aiScoreUpdatedAt = null;

    /** @var Collection<int, Feedback> */
    #[ORM\OneToMany(mappedBy: 'medecin', targetEntity: Feedback::class)]
    private Collection $feedbacks;

    /** @var Collection<int, Appointment> */
    #[ORM\OneToMany(mappedBy: 'doctor', targetEntity: Appointment::class)]
    private Collection $appointments;

    public function __construct()
    {
        $this->feedbacks = new ArrayCollection();
        $this->reponses = new ArrayCollection();
        $this->appointments = new ArrayCollection();
        $this->roles = ['ROLE_MEDECIN'];
        $this->isVerified = false;
    }

    public function getPhoneNumber(): ?string { return $this->phoneNumber; }
    public function setPhoneNumber(?string $phoneNumber): self { $this->phoneNumber = $phoneNumber; return $this; }
    public function getSpecialty(): ?string { return $this->specialty; }
    public function setSpecialty(string $specialty): self { $this->specialty = $specialty; return $this; }
    public function getSpecialite(): ?string { return $this->specialty; }
    public function setSpecialite(string $specialite): self { $this->specialty = $specialite; return $this; }
    public function getCin(): string { return $this->cin; }
    public function setCin(string $cin): self { $this->cin = $cin; return $this; }
    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $address): self { $this->address = $address; return $this; }
    public function getGovernorate(): ?string { return $this->governorate; }
    public function setGovernorate(?string $governorate): self { $this->governorate = $governorate; return $this; }
    public function getEducation(): ?string { return $this->education; }
    public function setEducation(?string $education): self { $this->education = $education; return $this; }
    public function getExperience(): ?string { return $this->experience; }
    public function setExperience(?string $experience): self { $this->experience = $experience; return $this; }
    public function getIsVerified(): bool { return $this->isVerified; }
    public function setIsVerified(bool $isVerified): self { $this->isVerified = $isVerified; return $this; }
    public function isVerified(): bool { return $this->isVerified; }

    /** @return Collection<int, Reponse> */
    public function getReponses(): Collection { return $this->reponses; }
    public function addReponse(Reponse $reponse): self { if (!$this->reponses->contains($reponse)) { $this->reponses[] = $reponse; $reponse->setMedecin($this); } return $this; }
    public function removeReponse(Reponse $reponse): self { if ($this->reponses->removeElement($reponse)) { if ($reponse->getMedecin() === $this) { $reponse->setMedecin(null); } } return $this; }

    public function getAiAverageScore(): ?float { return $this->aiAverageScore; }
    public function setAiAverageScore(?float $aiAverageScore): self { $this->aiAverageScore = $aiAverageScore; return $this; }
    public function getAiScoreUpdatedAt(): ?\DateTimeImmutable { return $this->aiScoreUpdatedAt; }
    public function setAiScoreUpdatedAt(?\DateTimeImmutable $aiScoreUpdatedAt): self { $this->aiScoreUpdatedAt = $aiScoreUpdatedAt; return $this; }

    /** @return Collection<int, Feedback> */
    public function getFeedbacks(): Collection { return $this->feedbacks; }
    public function addFeedback(Feedback $feedback): self { if (!$this->feedbacks->contains($feedback)) { $this->feedbacks->add($feedback); $feedback->setMedecin($this); } return $this; }
    public function removeFeedback(Feedback $feedback): self { if ($this->feedbacks->removeElement($feedback)) { if ($feedback->getMedecin() === $this) { $feedback->setMedecin(null); } } return $this; }

    /** @return Collection<int, Appointment> */
    public function getAppointments(): Collection { return $this->appointments; }
    public function addAppointment(Appointment $appointment): self { if (!$this->appointments->contains($appointment)) { $this->appointments->add($appointment); $appointment->setDoctor($this); } return $this; }
    public function removeAppointment(Appointment $appointment): self { if ($this->appointments->removeElement($appointment)) { if ($appointment->getDoctor() === $this) { $appointment->setDoctor(null); } } return $this; }
}
PHP;

$patient = <<<'PHP'
<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
#[ORM\Table(name: 'patients')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Patient extends BaseUser
{
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $hasInsurance = false;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $insuranceNumber = null;

    #[ORM\OneToMany(mappedBy: 'patient', targetEntity: Question::class)]
    private Collection $questions;

    /** @var Collection<int, Feedback> */
    #[ORM\OneToMany(mappedBy: 'patient', targetEntity: Feedback::class)]
    private Collection $feedbacks;

    /** @var Collection<int, Appointment> */
    #[ORM\OneToMany(mappedBy: 'patient', targetEntity: Appointment::class)]
    private Collection $appointments;

    public function __construct()
    {
        $this->feedbacks = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->appointments = new ArrayCollection();
        $this->roles = ['ROLE_PATIENT'];
    }

    public function getPhoneNumber(): ?string { return $this->phoneNumber; }
    public function setPhoneNumber(?string $phoneNumber): self { $this->phoneNumber = $phoneNumber; return $this; }
    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $address): self { $this->address = $address; return $this; }
    public function getDateOfBirth(): ?\DateTimeInterface { return $this->dateOfBirth; }
    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): self { $this->dateOfBirth = $dateOfBirth; return $this; }
    public function getHasInsurance(): bool { return $this->hasInsurance; }
    public function setHasInsurance(bool $hasInsurance): self { $this->hasInsurance = $hasInsurance; return $this; }
    public function getInsuranceNumber(): ?string { return $this->insuranceNumber; }
    public function setInsuranceNumber(?string $insuranceNumber): self { $this->insuranceNumber = $insuranceNumber; return $this; }

    /** @return Collection<int, Question> */
    public function getQuestions(): Collection { return $this->questions; }
    public function addQuestion(Question $question): self { if (!$this->questions->contains($question)) { $this->questions->add($question); $question->setPatient($this); } return $this; }
    public function removeQuestion(Question $question): self { if ($this->questions->removeElement($question)) { if ($question->getPatient() === $this) { $question->setPatient(null); } } return $this; }

    /** @return Collection<int, Feedback> */
    public function getFeedbacks(): Collection { return $this->feedbacks; }
    public function addFeedback(Feedback $feedback): self { if (!$this->feedbacks->contains($feedback)) { $this->feedbacks->add($feedback); $feedback->setPatient($this); } return $this; }
    public function removeFeedback(Feedback $feedback): self { if ($this->feedbacks->removeElement($feedback)) { if ($feedback->getPatient() === $this) { $feedback->setPatient(null); } } return $this; }

    /** @return Collection<int, Appointment> */
    public function getAppointments(): Collection { return $this->appointments; }
    public function addAppointment(Appointment $appointment): self { if (!$this->appointments->contains($appointment)) { $this->appointments->add($appointment); $appointment->setPatient($this); } return $this; }
    public function removeAppointment(Appointment $appointment): self { if ($this->appointments->removeElement($appointment)) { if ($appointment->getPatient() === $this) { $appointment->setPatient(null); } } return $this; }
}
PHP;

$feedback = <<<'PHP'
<?php

namespace App\Entity;

use App\Repository\FeedbackRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FeedbackRepository::class)]
class Feedback
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotNull(message: "La note est obligatoire")]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: "La note doit être entre 1 et 5")]
    #[ORM\Column]
    private ?int $rating = null;

    #[Assert\NotBlank(message: "Le commentaire est obligatoire")]
    #[Assert\Length(min: 10, max: 1000, minMessage: "Le commentaire doit contenir au moins 10 caractères")]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $comment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: Patient::class, inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: Medecin::class, inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medecin $medecin = null;

    #[ORM\ManyToOne(targetEntity: Appointment::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Appointment $appointment = null;

    #[ORM\Column(nullable: true)]
    private ?float $sentimentScore = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getRating(): ?int { return $this->rating; }
    public function setRating(int $rating): self { $this->rating = $rating; return $this; }
    public function getComment(): ?string { return $this->comment; }
    public function setComment(string $comment): self { $this->comment = $comment; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function getPatient(): ?Patient { return $this->patient; }
    public function setPatient(?Patient $patient): self { $this->patient = $patient; return $this; }
    public function getMedecin(): ?Medecin { return $this->medecin; }
    public function setMedecin(?Medecin $medecin): self { $this->medecin = $medecin; return $this; }
    public function getAppointment(): ?Appointment { return $this->appointment; }
    public function setAppointment(?Appointment $appointment): self { $this->appointment = $appointment; return $this; }
    public function getSentimentScore(): ?float { return $this->sentimentScore; }
    public function setSentimentScore(?float $sentimentScore): self { $this->sentimentScore = $sentimentScore; return $this; }
}
PHP;

$testEmail = <<<'PHP'
<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestEmailController extends AbstractController
{
    #[Route('/test-email', name: 'test_email')]
    public function testEmail(
        EmailService $emailService,
        AppointmentRepository $appointmentRepo
    ): Response
    {
        $appointment = $appointmentRepo->findOneBy(['status' => 'pending']);

        if (!$appointment) {
            return new Response('No pending appointments found for testing. Please book an appointment first.');
        }

        try {
            $emailService->sendAppointmentConfirmation($appointment);
            $confirmationResult = '✅ Confirmation email sent successfully!<br>';
            
            $emailService->sendAppointmentReminder($appointment);
            $reminderResult = '✅ Reminder email sent successfully!<br>';

            $emailService->sendAppointmentCancellation($appointment);
            $cancellationResult = '✅ Cancellation email sent successfully!<br>';

            return new Response(
                '<h2>Email Test Results:</h2>' .
                $confirmationResult .
                $reminderResult .
                $cancellationResult .
                '<br>All emails sent successfully!'
            );
        } catch (\Exception $e) {
            return new Response(
                '<h2>Email Test Failed:</h2>' .
                '<p style="color: red;">Error: ' . $e->getMessage() . '</p>' .
                '<pre>' . $e->getTraceAsString() . '</pre>'
            );
        }
    }
}
PHP;

$patientFeedbackController = <<<'PHP'
<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Appointment;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/feedback')]
class PatientFeedbackController extends AbstractController
{
    #[Route('/new/{id}', name: 'app_feedback_new_for_rdv')]
    public function newForRdv(
        Appointment $appointment,
        Request $request,
        EntityManagerInterface $em,
        PatientRepository $patientRepo
    ): Response
    {
        // Vérifier que le RDV est terminé
        if ($appointment->getStatus() !== 'completed') {
            $this->addFlash('error', 'Vous ne pouvez donner un avis que pour un rendez-vous terminé.');
            return $this->redirectToRoute('app_my_appointments');
        }

        // Patient fixe (ID = 1)
        $patient = $patientRepo->find(1);

        // Vérifier si le patient a déjà donné un feedback pour ce médecin
        $existingFeedback = $em->getRepository(Feedback::class)->findOneBy([
            'patient' => $patient,
            'medecin' => $appointment->getDoctor()
        ]);

        if ($existingFeedback) {
            $this->addFlash('error', 'Vous avez déjà donné votre avis pour ce médecin.');
            return $this->redirectToRoute('app_my_appointments');
        }

        if ($request->isMethod('POST')) {
            $rating = (int) $request->request->get('rating');
            $comment = $request->request->get('comment');

            if ($rating < 1 || $rating > 5) {
                $this->addFlash('error', 'La note doit être entre 1 et 5.');
            } elseif (strlen($comment) < 10) {
                $this->addFlash('error', 'Le commentaire doit contenir au moins 10 caractères.');
            } else {
                $feedback = new Feedback();
                $feedback->setRating($rating);
                $feedback->setComment($comment);
                $feedback->setPatient($patient);
                $feedback->setMedecin($appointment->getDoctor());

                $em->persist($feedback);
                $em->flush();

                $this->addFlash('success', 'Merci pour votre avis !');
                return $this->redirectToRoute('app_my_appointments');
            }
        }

        return $this->render('patient_feedback/new.html.twig', [
            'rendezVous' => $appointment,
        ]);
    }
}
PHP;

// ============================================================
// PART 2: Write all entity + controller files
// ============================================================
$files = [
    "$baseDir/src/Entity/Medecin.php" => $medecin,
    "$baseDir/src/Entity/Patient.php" => $patient,
    "$baseDir/src/Entity/Feedback.php" => $feedback,
    "$baseDir/src/Controller/TestEmailController.php" => $testEmail,
    "$baseDir/src/Controller/PatientFeedbackController.php" => $patientFeedbackController,
];

foreach ($files as $path => $content) {
    $result = file_put_contents($path, $content);
    echo($result === false ? "FAILED" : "OK") . ": $path ($result bytes)\n";
}

// ============================================================
// PART 3: Fix files via search-and-replace (safer for large files)
// ============================================================

// Fix IaController.php - replace RendezVous import and usage with Appointment
$iaPath = "$baseDir/src/Controller/IaController.php";
$iaContent = file_get_contents($iaPath);
$iaContent = str_replace("use App\\Entity\\RendezVous;\n", "use App\\Entity\\Appointment;\n", $iaContent);
$iaContent = str_replace("use App\Entity\RendezVous;\r\n", "use App\Entity\Appointment;\r\n", $iaContent);
$iaContent = str_replace('RendezVous::class', 'Appointment::class', $iaContent);
// Check if Appointment import already exists to avoid duplication
if (substr_count($iaContent, "use App\\Entity\\Appointment;") > 1) {
    // Remove duplicate
    $iaContent = preg_replace("/(use App\\\\Entity\\\\Appointment;\r?\n)(.*use App\\\\Entity\\\\Appointment;\r?\n)/s", "$1", $iaContent);
}
file_put_contents($iaPath, $iaContent);
echo "OK (search-replace): $iaPath\n";

// Fix HomeController.php - remove unused RendezVous imports
$homePath = "$baseDir/src/Controller/HomeController.php";
$homeContent = file_get_contents($homePath);
$homeContent = str_replace("use App\\Entity\\RendezVous;\r\n", "", $homeContent);
$homeContent = str_replace("use App\\Entity\\RendezVous;\n", "", $homeContent);
$homeContent = str_replace("use App\\Form\\RendezVousType;\r\n", "", $homeContent);
$homeContent = str_replace("use App\\Form\\RendezVousType;\n", "", $homeContent);
file_put_contents($homePath, $homeContent);
echo "OK (search-replace): $homePath\n";

// Fix Rapport.php - remove getRendezVous/setRendezVous aliases
$rapportPath = "$baseDir/src/Entity/Rapport.php";
$rapportContent = file_get_contents($rapportPath);
$rapportContent = preg_replace(
    '/\s*public function getRendezVous\(\).*?public function setRendezVous\(.*?\{.*?return \$this;\s*\}\s*/s',
    "\n\n",
    $rapportContent
);
file_put_contents($rapportPath, $rapportContent);
echo "OK (search-replace): $rapportPath\n";

// Fix Ordonnance.php - remove getRendezVous/setRendezVous aliases
$ordonnancePath = "$baseDir/src/Entity/Ordonnance.php";
$ordonnanceContent = file_get_contents($ordonnancePath);
$ordonnanceContent = preg_replace(
    '/\s*public function getRendezVous\(\).*?public function setRendezVous\(.*?\{.*?return \$this;\s*\}\s*/s',
    "\n\n",
    $ordonnanceContent
);
file_put_contents($ordonnancePath, $ordonnanceContent);
echo "OK (search-replace): $ordonnancePath\n";

// Fix RapportController.php - replace $form->get('rendezVous') with $form->get('appointment')
$rapportCtrlPath = "$baseDir/src/Controller/RapportController.php";
$rapportCtrlContent = file_get_contents($rapportCtrlPath);
$rapportCtrlContent = str_replace("\$form->get('rendezVous')", "\$form->get('appointment')", $rapportCtrlContent);
file_put_contents($rapportCtrlPath, $rapportCtrlContent);
echo "OK (search-replace): $rapportCtrlPath\n";

// Fix OrdonnanceController.php - replace $form->get('rendezVous') with $form->get('appointment')
$ordonnanceCtrlPath = "$baseDir/src/Controller/OrdonnanceController.php";
$ordonnanceCtrlContent = file_get_contents($ordonnanceCtrlPath);
$ordonnanceCtrlContent = str_replace("\$form->get('rendezVous')", "\$form->get('appointment')", $ordonnanceCtrlContent);
file_put_contents($ordonnanceCtrlPath, $ordonnanceCtrlContent);
echo "OK (search-replace): $ordonnanceCtrlPath\n";

// Fix RapportType.php - rename form field from 'rendezVous' to 'appointment'
$rapportTypePath = "$baseDir/src/Form/RapportType.php";
$rapportTypeContent = file_get_contents($rapportTypePath);
$rapportTypeContent = str_replace("->add('rendezVous', EntityType::class, [", "->add('appointment', EntityType::class, [", $rapportTypeContent);
$rapportTypeContent = str_replace("'property_path' => 'appointment',\n", "", $rapportTypeContent);
$rapportTypeContent = str_replace("'property_path' => 'appointment',\r\n", "", $rapportTypeContent);
// also remove leading whitespace line for property_path  
$rapportTypeContent = preg_replace("/\s+'property_path' => 'appointment',\r?\n/", "\n", $rapportTypeContent);
file_put_contents($rapportTypePath, $rapportTypeContent);
echo "OK (search-replace): $rapportTypePath\n";

// Fix OrdonnanceType.php - rename form field
$ordonnanceTypePath = "$baseDir/src/Form/OrdonnanceType.php";
$ordonnanceTypeContent = file_get_contents($ordonnanceTypePath);
$ordonnanceTypeContent = str_replace("->add('rendezVous', EntityType::class, [", "->add('appointment', EntityType::class, [", $ordonnanceTypeContent);
$ordonnanceTypeContent = str_replace("'property_path' => 'appointment',\n", "", $ordonnanceTypeContent);
$ordonnanceTypeContent = str_replace("'property_path' => 'appointment',\r\n", "", $ordonnanceTypeContent);
$ordonnanceTypeContent = preg_replace("/\s+'property_path' => 'appointment',\r?\n/", "\n", $ordonnanceTypeContent);
file_put_contents($ordonnanceTypePath, $ordonnanceTypeContent);
echo "OK (search-replace): $ordonnanceTypePath\n";

// Fix templates - replace form.rendezVous with form.appointment
$templates = [
    "$baseDir/templates/rapport/mod1.html.twig",
    "$baseDir/templates/ordonnance/modOrdonnance1.html.twig",
];
foreach ($templates as $tplPath) {
    if (file_exists($tplPath)) {
        $tplContent = file_get_contents($tplPath);
        $tplContent = str_replace('form.rendezVous', 'form.appointment', $tplContent);
        file_put_contents($tplPath, $tplContent);
        echo "OK (template): $tplPath\n";
    }
    else {
        echo "SKIP (not found): $tplPath\n";
    }
}

// Also fix other rapport/ordonnance templates that might reference form.rendezVous
$additionalTemplates = glob("$baseDir/templates/rapport/*.twig") ?: [];
$additionalTemplates = array_merge($additionalTemplates, glob("$baseDir/templates/ordonnance/*.twig") ?: []);
foreach ($additionalTemplates as $tplPath) {
    if (in_array($tplPath, $templates))
        continue; // already handled
    $tplContent = file_get_contents($tplPath);
    if (strpos($tplContent, 'form.rendezVous') !== false) {
        $tplContent = str_replace('form.rendezVous', 'form.appointment', $tplContent);
        file_put_contents($tplPath, $tplContent);
        echo "OK (template): $tplPath\n";
    }
}

// ============================================================
// PART 4: Delete legacy files
// ============================================================
$legacyFiles = [
    "$baseDir/src/Entity/RendezVous.php",
    "$baseDir/src/Repository/RendezVousRepository.php",
    "$baseDir/src/Form/RendezVousType.php",
];
foreach ($legacyFiles as $legacy) {
    if (file_exists($legacy)) {
        unlink($legacy);
        echo "DELETED: $legacy\n";
    }
}

// ============================================================
// PART 5: Verify — check all src/ files for RendezVous class references
// ============================================================
echo "\n=== VERIFICATION ===\n";
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$baseDir/src"));
$problems = [];
foreach ($iterator as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php')
        continue;
    $content = file_get_contents($file->getPathname());
    // Check for RendezVous CLASS references (not just string mentions like route names)
    if (preg_match('/\bRendezVous\b/', $content)) {
        // Classify the reference
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/\bRendezVous\b/', $line)) {
                $problems[] = basename($file->getPathname()) . ':' . ($lineNum + 1) . ': ' . trim($line);
            }
        }
    }
}

if (empty($problems)) {
    echo "✅ ALL CLEAN! No RendezVous class references found in src/\n";
}
else {
    echo "⚠️ Remaining references:\n";
    foreach ($problems as $p) {
        echo "  $p\n";
    }
}

// Also check templates
echo "\n=== TEMPLATE VERIFICATION ===\n";
$tplIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$baseDir/templates"));
$tplProblems = [];
foreach ($tplIterator as $file) {
    if ($file->isDir() || $file->getExtension() !== 'twig')
        continue;
    $content = file_get_contents($file->getPathname());
    if (preg_match('/form\.rendezVous/', $content)) {
        $tplProblems[] = basename($file->getPathname());
    }
}
if (empty($tplProblems)) {
    echo "✅ ALL CLEAN! No form.rendezVous references in templates\n";
}
else {
    echo "⚠️ Templates with form.rendezVous:\n";
    foreach ($tplProblems as $p)
        echo "  $p\n";
}

echo "\nDone!\n";
