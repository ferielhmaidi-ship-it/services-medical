<?php
// src/Controller/AdminDashboardController.php

namespace App\Controller;

use App\Repository\MedecinRepository;
use App\Repository\PatientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(MedecinRepository $medecinRepository, PatientRepository $patientRepository): Response
    {
        $doctorsCount = $medecinRepository->count([]);
        $patientsCount = $patientRepository->count([]);
        $verifiedDoctors = $medecinRepository->count(['isVerified' => true]);

        // Get specialty distribution
        $allMedecins = $medecinRepository->findAll();
        $specialtyCounts = [];
        foreach ($allMedecins as $medecin) {
            $specialty = $medecin->getSpecialty();
            if (!isset($specialtyCounts[$specialty])) {
                $specialtyCounts[$specialty] = 0;
            }
            $specialtyCounts[$specialty]++;
        }

        // Get recent medecins (last 5 registered)
        $recentMedecins = $medecinRepository->findBy([], ['id' => 'DESC'], 5);

        return $this->render('admin_dashboard/index.html.twig', [
            'doctors_count' => $doctorsCount,
            'patients_count' => $patientsCount,
            'verified_doctors' => $verifiedDoctors,
            'today_appointments' => 0, // Add your logic for appointments
            'specialty_counts' => $specialtyCounts,
            'recent_medecins' => $recentMedecins, // Add this
        ]);
    }
}
