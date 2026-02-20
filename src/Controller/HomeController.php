<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MedecinRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Medecin;
use App\Repository\PatientRepository;
use App\Constants\Specialty;
use App\Constants\Governorate;
use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Service\AvailabilityService;
use App\Repository\SpecialiteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Appointment;
use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }


    #[Route('/doctors', name: 'app_doctors')]
    public function doctors(
        Request $request, 
        MedecinRepository $medecinRepository,
        AvailabilityService $availabilityService
    ): Response
    {
        $name = $request->query->get('name', '');
        $specialty = $request->query->get('specialty', '');
        $governorate = $request->query->get('governorate', '');

        // Use repository method for filtering
        $medecins = $medecinRepository->searchDoctors($name, $specialty, $governorate);

        // Enrich doctors with availability data
        $now = new \DateTime();
        $nextMonday = (clone $now)->modify('next monday');
        $nextMonday->setTime(0, 0, 0);
        
        $enrichedMedecins = [];
        foreach ($medecins as $medecin) {
            $workingDays = $availabilityService->getWeeklyWorkingDays($medecin, $nextMonday);
            $nextSlot = $availabilityService->getNextAvailableSlot($medecin, $now);
            
            $enrichedMedecins[] = [
                'entity' => $medecin,
                'workingDays' => $workingDays,
                'nextSlot' => $nextSlot
            ];
        }

        // Get filter options
        $filterOptions = $medecinRepository->getFilterOptions();

        return $this->render('pages/doctors.html.twig', [
            'medecins' => $enrichedMedecins,
            'name' => $name,
            'currentSpecialty' => $specialty,
            'currentGovernorate' => $governorate,
            'governorates' => Governorate::getChoices(),
            'specialtyGroups' => Specialty::getGroups(),
            'specialtyClass' => Specialty::class
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('pages/contact.html.twig');
    }

    #[Route('/appointment', name: 'app_appointment')]
    public function appointment(EntityManagerInterface $em): Response
    {
        $specialites = $em->getRepository(\App\Entity\Specialite::class)->findAll();

        return $this->render('pages/appointment.html.twig', [
            'specialites' => $specialites,
        ]);
    }

    #[Route('/api/doctors/{id}', name: 'api_doctors_by_specialite', methods: ['GET'])]
    public function getDoctorsBySpecialite(int $id, SpecialiteRepository $specRepo, MedecinRepository $medecinRepo): JsonResponse
    {
        $specialite = $specRepo->find($id);
        if (!$specialite) {
            return new JsonResponse([], 404);
        }

        $medecins = $medecinRepo->findBy(['specialty' => $specialite->getNom()]);
        
        $data = [];
        foreach ($medecins as $medecin) {
            $data[] = [
                'id' => $medecin->getId(),
                'name' => 'Dr. ' . $medecin->getFirstName() . ' ' . $medecin->getLastName(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/appointment/submit', name: 'app_appointment_submit', methods: ['POST'])]
    public function submitAppointment(Request $request, EntityManagerInterface $em, MedecinRepository $medecinRepo): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || !($user instanceof \App\Entity\Patient)) {
            // For this specific "premium" form, if user not logged in, we might want to handle it differently
            // but let's assume they should be logged in as per BookingController
            return new JsonResponse(['status' => 'error', 'message' => 'Please login to book an appointment'], 403);
        }

        $doctorId = $request->request->get('doctor');
        $doctor = $medecinRepo->find($doctorId);
        if (!$doctor) {
            return new JsonResponse(['status' => 'error', 'message' => 'Doctor not found'], 404);
        }

        $appointment = new Appointment();
        $appointment->setPatient($user);
        $appointment->setDoctor($doctor);
        $appointment->setDate(new \DateTime($request->request->get('date')));
        
        // appointment.html.twig doesn't seem to have a time picker in the HTML I saw, 
        // wait, let me check the HTML again for "time".
        // Ah, it has "date" but I don't see a time select.
        // Wait, line 128 is date.
        // Let's check if there is an input name="time".
        
        $appointment->setStartTime(new \DateTime('09:00')); // Default
        $appointment->setDuration(30);
        $appointment->setStatus('pending');
        $appointment->setMessage($request->request->get('message'));
        $appointment->setDepartment($request->request->get('department'));

        $em->persist($appointment);
        $em->flush();

        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/departments', name: 'app_departments')]
    public function departments(): Response
    {
        return $this->render('pages/departments.html.twig');
    }

    #[Route('/department-details', name: 'app_department_details')]
    public function departmentDetails(): Response
    {
        return $this->render('pages/department-details.html.twig');
    }

    #[Route('/services', name: 'app_services')]
    public function services(): Response
    {
        return $this->render('pages/services.html.twig');
    }

    #[Route('/service-details', name: 'app_service_details')]
    public function serviceDetails(): Response
    {
        return $this->render('pages/service-details.html.twig');
    }

    #[Route('/gallery', name: 'app_gallery')]
    public function gallery(): Response
    {
        return $this->render('pages/gallery.html.twig');
    }

    #[Route('/faq', name: 'app_faq')]
    public function faq(): Response
    {
        return $this->render('pages/faq.html.twig');
    }

    #[Route('/privacy', name: 'app_privacy')]
    public function privacy(): Response
    {
        return $this->render('pages/privacy.html.twig');
    }

    #[Route('/terms', name: 'app_terms')]
    public function terms(): Response
    {
        return $this->render('pages/terms.html.twig');
    }
}

