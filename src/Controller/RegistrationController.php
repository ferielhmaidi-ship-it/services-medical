<?php

namespace App\Controller;

use App\Entity\Medecin;
use App\Entity\Patient;
use App\Form\MedecinRegistrationType;
use App\Form\PatientRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
   #[Route('/register/medecin', name: 'register_medecin')]
public function registerMedecin(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
{
    $medecin = new Medecin();
    $form = $this->createForm(MedecinRegistrationType::class, $medecin);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Encode the plain password
        $medecin->setPassword(
            $passwordHasher->hashPassword(
                $medecin,
                $form->get('plainPassword')->getData()
            )
        );

        // Set default values
       // $medecin->setIsVerified(false);
        //$medecin->setIsActive(true);

        try {
            $entityManager->persist($medecin);
            $entityManager->flush();

            $this->addFlash('success', '🎉 Doctor account created successfully! Your account is pending administrator verification. You will be notified by email once verified.');

            // Redirect to home or login
            return $this->redirectToRoute('app_home');

        } catch (\Exception $e) {
            // Check for duplicate CIN or email
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'cin') !== false) {
                    $this->addFlash('error', 'This CIN is already registered. Please use a different CIN or contact support if this is an error.');
                } elseif (strpos($e->getMessage(), 'email') !== false) {
                    $this->addFlash('error', 'This email is already registered. Please use a different email or try to login.');
                } else {
                    $this->addFlash('error', 'An error occurred during registration. Please try again.');
                }
            } else {
                $this->addFlash('error', 'An unexpected error occurred. Please try again.');
            }
        }
    }

    return $this->render('registration/register_medecin.html.twig', [
        'registrationForm' => $form->createView(),
    ]);
}

#[Route('/register/patient', name: 'register_patient')]
public function registerPatient(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
{
    $patient = new Patient();
    $form = $this->createForm(PatientRegistrationType::class, $patient);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Encode the plain password
        $patient->setPassword(
            $passwordHasher->hashPassword(
                $patient,
                $form->get('plainPassword')->getData()
            )
        );

        try {
            $entityManager->persist($patient);
            $entityManager->flush();

            $this->addFlash('success', '🎉 Patient account created successfully! You can now login.');

            // Redirect to login page
            return $this->redirectToRoute('app_login');

        } catch (\Exception $e) {
            // Check for duplicate email
            if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'email') !== false) {
                $this->addFlash('error', 'This email is already registered. Please use a different email or login.');
            } else {
                $this->addFlash('error', 'An unexpected error occurred. Please try again.');
            }
        }
    }

    return $this->render('registration/register_patient.html.twig', [
        'registrationForm' => $form->createView(),
    ]);
}
}
