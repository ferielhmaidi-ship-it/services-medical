<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Repository\MedecinRepository;
use App\Repository\SpecialiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class AppointmentController extends AbstractController
{
    #[Route('/appointment', name: 'app_appointment')]
    public function index(SpecialiteRepository $specialiteRepo): Response
    {
        $specialites = $specialiteRepo->findAll();

        return $this->render('pages/appointment.html.twig', [
            'specialites' => $specialites,
        ]);
    }

    #[Route('/api/doctors/{specialiteId}', name: 'api_doctors_by_specialite')]
    public function getDoctorsBySpecialite(int $specialiteId, MedecinRepository $medecinRepo, SpecialiteRepository $specialiteRepo): JsonResponse
    {
        // Ideally, Medecin should have a relation to Specialite entity.
        // Current Medecin entity stores specialty as a string.
        // We need to match the string name of the specialite.
        
        $specialite = $specialiteRepo->find($specialiteId);
        
        if (!$specialite) {
            return new JsonResponse([]);
        }

        $doctors = $medecinRepo->findBy(['specialty' => $specialite->getNom()]);
        
        $data = [];
        foreach ($doctors as $doctor) {
            $data[] = [
                'id' => $doctor->getId(),
                'name' => 'Dr. ' . $doctor->getFirstName() . ' ' . $doctor->getLastName(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/appointment/submit', name: 'app_appointment_submit', methods: ['POST'])]
    public function submit(Request $request, EntityManagerInterface $em, MedecinRepository $medecinRepo, \App\Repository\PatientRepository $patientRepo): JsonResponse
    {
        // Basic validation
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $phone = $request->request->get('phone');
        $dateStr = $request->request->get('date');
        $doctorId = $request->request->get('doctor');
        $message = $request->request->get('message');
        $department = $request->request->get('department'); // We might want to store this

        if (!$name || !$email || !$dateStr || !$doctorId) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
        }

        try {
            $appointment = new Appointment();
            $appointment->setDate(new \DateTime($dateStr));
            $appointment->setMessage($message);
            $appointment->setDepartment($department);
            
            // Link Doctor
            $doctor = $medecinRepo->find($doctorId);
            if (!$doctor) {
                 return new JsonResponse(['status' => 'error', 'message' => 'Invalid doctor selected'], 400);
            }
            $appointment->setMedecin($doctor);

            // Handle Patient (Current User or Create/Find based on email?)
            // For now, let's assume the user is logged in as Patient.
            $user = $this->getUser();
            if ($user && in_array('ROLE_PATIENT', $user->getRoles())) {
                $appointment->setPatient($user);
            } else {
                 return new JsonResponse(['status' => 'error', 'message' => 'You must be logged in as a patient to book an appointment'], 403);
            }

            $em->persist($appointment);
            $em->flush();

            return new JsonResponse(['status' => 'success', 'message' => 'Appointment booked successfully']);

        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
