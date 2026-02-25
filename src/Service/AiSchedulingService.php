<?php

namespace App\Service;

use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class AiSchedulingService
{
    private HttpClientInterface $httpClient;
    private AppointmentRepository $appointmentRepo;
    private string $apiKey;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        AppointmentRepository $appointmentRepo,
        string $apiKey,
        LoggerInterface $logger
        )
    {
        $this->httpClient = $httpClient;
        $this->appointmentRepo = $appointmentRepo;
        $this->apiKey = $apiKey;
        $this->logger = $logger;
    }

    public function getSmartSuggestions(Patient $patient, Medecin $doctor, array $availableSlotsByDay, ?array $closestSlot = null): array
    {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_OPENAI_API_KEY') {
            return [
                'recommendation' => 'Veuillez configurer la clé API OpenAI pour obtenir des suggestions intelligentes.',
                'attendance_probability' => null,
                'suggested_slots' => []
            ];
        }

        // 1. Collect Patient History
        $patientAppts = $this->appointmentRepo->findBy(['patient' => $patient], ['date' => 'DESC'], 20);
        $historyData = [];
        $total = count($patientAppts);
        $completed = 0;
        $cancelled = 0;

        foreach ($patientAppts as $app) {
            $status = $app->getStatus();
            $historyData[] = [
                'day' => $app->getDate()->format('l'),
                'time' => $app->getDate()->format('H:i'),
                'status' => $status
            ];
            if ($status === 'termine' || $status === 'completed')
                $completed++;
            if ($status === 'annule' || $status === 'cancelled')
                $cancelled++;
        }

        $attendanceRate = $total > 0 ? round(($completed / $total) * 100) : 100;

        // 2. Prepare Context for Prompt
        $slotsContext = !empty($availableSlotsByDay) ? json_encode($availableSlotsByDay) : "AUCUN créneau dans les 21 prochains jours.";
        $closestContext = $closestSlot ? "CRÉNEAU LE PLUS PROCHE TROUVÉ (même si au-delà de 21j) : " . json_encode($closestSlot) : "Aucun créneau du tout trouvé.";

        // 3. Prepare Prompt
        $prompt = "En tant qu'assistant IA de planification médicale MediNest, analyse l'historique du patient et les disponibilités du médecin.
        
Objectif :
1. Identifier les préférences du patient (ex: matin, jours spécifiques).
2. Optimiser l'agenda du médecin.
3. Suggérer LE meilleur créneau unique basé sur ce compromis.

Patient History: " . json_encode($historyData) . " (Attendance Rate: $attendanceRate%)
Doctor: Dr. {$doctor->getFirstName()} {$doctor->getLastName()} ({$doctor->getSpecialty()})

Available Free Slots (21 days window): $slotsContext
$closestContext

IMPORTANT: Si le 'Available Free Slots' est vide, tu DOIS suggérer le 'CRÉNEAU LE PLUS PROCHE TROUVÉ'. Le but est de toujours proposer une solution au patient.

Format JSON attendu (RÉPONDS UNIQUEMENT AVEC CE JSON):
{
    \"recommendation\": \"Explication courte et motivante personnalisée\",
    \"attendance_probability\": \"XX%\",
    \"suggested_slots\": [{\"date\": \"YYYY-MM-DD\", \"time\": \"HH:MM\"}]
}";

        $model = 'meta-llama/llama-3.3-70b-instruct:free';
        try {
            $response = $this->httpClient->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => 'http://localhost:8000',
                    'X-Title' => 'TabibNet Medical Scheduler',
                ],
                'json' => [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Tu es un expert en logistique médicale. Réponds uniquement en format JSON pur.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.5,
                ],
                'timeout' => 20,
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('AI Provider Error: ' . $response->getStatusCode());
            }

            $content = $response->toArray();
            $aiContent = trim($content['choices'][0]['message']['content'] ?? '');
            $aiContent = preg_replace('/^```json\s*|```$/m', '', $aiContent);
            $aiData = json_decode($aiContent, true);

            if (!$aiData || !isset($aiData['suggested_slots'])) {
                throw new \Exception('Invalid AI Response format');
            }

            return [
                'recommendation' => $aiData['recommendation'] ?? 'Nous avons trouvé ce créneau idéal pour votre prochaine visite.',
                'attendance_probability' => $aiData['attendance_probability'] ?? null,
                'suggested_slots' => $aiData['suggested_slots']
            ];
        }
        catch (\Exception $e) {
            $this->logger->error('AiSchedulingService API Failure: ' . $e->getMessage());
            $fallbackData = !empty($availableSlotsByDay) ? $availableSlotsByDay : ($closestSlot ? [['date' => $closestSlot['date'], 'slots' => [$closestSlot['time']]]] : []);
            return $this->getFallbackSuggestions($patient, $doctor, $fallbackData);
        }
    }

    private function getFallbackSuggestions(Patient $patient, Medecin $doctor, array $availableSlotsByDay): array
    {
        $patientAppts = $this->appointmentRepo->findBy(['patient' => $patient], ['date' => 'DESC'], 30);
        $timePrefs = ['morning' => 0, 'afternoon' => 0, 'evening' => 0];
        $dayPrefs = [];

        foreach ($patientAppts as $app) {
            $status = $app->getStatus();
            if ($status !== 'termine' && $status !== 'completed')
                continue;

            $day = $app->getDate()->format('l');
            $dayPrefs[$day] = ($dayPrefs[$day] ?? 0) + 1;

            $hour = (int)$app->getDate()->format('H');
            if ($hour < 12)
                $timePrefs['morning']++;
            else if ($hour < 17)
                $timePrefs['afternoon']++;
            else
                $timePrefs['evening']++;
        }

        arsort($dayPrefs);
        arsort($timePrefs);
        $prefDay = key($dayPrefs) ?: 'any day';
        $prefTime = key($timePrefs);

        $suggested = [];
        foreach ($availableSlotsByDay as $dayData) {
            foreach ($dayData['slots'] as $time) {
                $hour = (int)explode(':', $time)[0];
                $slotBlock = $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening');
                $score = ($slotBlock === $prefTime ? 10 : 0);
                if ($dayData['date'] === (new \DateTime())->format('Y-m-d'))
                    $score += 5;

                $suggested[] = ['date' => $dayData['date'], 'time' => $time, 'score' => $score];
            }
        }

        usort($suggested, fn($a, $b) => $b['score'] <=> $a['score']);
        $finalSlots = array_slice($suggested, 0, 1);

        if (empty($finalSlots)) {
            return [
                'recommendation' => "Nous n'avons trouvé aucun créneau disponible pour le moment.",
                'attendance_probability' => null,
                'suggested_slots' => []
            ];
        }

        $translatedDay = [
            'Monday' => 'le lundi', 'Tuesday' => 'le mardi', 'Wednesday' => 'le mercredi',
            'Thursday' => 'le jeudi', 'Friday' => 'le vendredi', 'Saturday' => 'le samedi', 'Sunday' => 'le dimanche'
        ][$prefDay] ?? "n'importe quel jour";

        $recommendation = "Comme vous préférez souvent prendre rendez-vous $translatedDay " .
            ($prefTime === 'morning' ? 'le matin' : ($prefTime === 'afternoon' ? 'l\'après-midi' : 'le soir')) .
            ", nous avons sélectionné ce créneau idéal pour vous.";

        if (empty($patientAppts)) {
            $recommendation = "Pour votre première visite avec Dr. {$doctor->getLastName()}, nous avons sélectionné le créneau le plus proche disponible.";
        }

        return [
            'recommendation' => $recommendation,
            'attendance_probability' => count($patientAppts) > 0 ? (max(80, 100 - (count($patientAppts) * 2)) . '%') : '95%',
            'suggested_slots' => array_map(fn($s) => ['date' => $s['date'], 'time' => $s['time']], $finalSlots)
        ];
    }
}
