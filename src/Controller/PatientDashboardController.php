<?php
// src/Controller/PatientDashboardController.php

namespace App\Controller;

use App\Entity\Patient;
use App\Form\PatientProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/patient')]
#[IsGranted('ROLE_PATIENT')]
class PatientDashboardController extends AbstractController
{
    #[Route('/', name: 'patient_dashboard')]
    public function index(): Response
    {
        $patient = $this->getUser();

        return $this->render('patient_dashboard/index.html.twig', [
            'patient' => $patient,
        ]);
    }

    #[Route('/profile', name: 'patient_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var Patient $patient */
        $patient = $this->getUser();

        $form = $this->createForm(PatientProfileType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle age calculation from date of birth if needed
            if ($patient->getDateOfBirth()) {
                $today = new \DateTime();
                $birthDate = $patient->getDateOfBirth();
                $age = $today->diff($birthDate)->y;
                $patient->setAge($age);
            }

            $entityManager->persist($patient);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('patient_profile');
        }

        return $this->render('patient_dashboard/profile.html.twig', [
            'patient' => $patient,
            'form' => $form->createView(),
        ]);
    }
}
