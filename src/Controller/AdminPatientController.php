<?php
// src/Controller/AdminPatientController.php

namespace App\Controller;

use App\Entity\Patient;
use App\Form\PatientEditType;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/patient')]
#[IsGranted('ROLE_ADMIN')]
class AdminPatientController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/new', name: 'admin_patient_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $patient = new Patient();
        $form = $this->createForm(PatientEditType::class, $patient, [
            'is_new' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $patient->setPassword(
                    $this->passwordHasher->hashPassword($patient, $plainPassword)
                );
            } else {
                $this->addFlash('error', 'Un mot de passe est requis pour un nouveau patient.');
                return $this->render('admin_patient/new.html.twig', [
                    'patient' => $patient,
                    'form' => $form->createView(),
                ]);
            }

            $entityManager->persist($patient);
            $entityManager->flush();

            $this->addFlash('success', 'Patient créé avec succès!');
            return $this->redirectToRoute('admin_patient_index');
        }

        return $this->render('admin_patient/new.html.twig', [
            'patient' => $patient,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/', name: 'admin_patient_index', methods: ['GET'])]
    public function index(PatientRepository $patientRepository): Response
    {
        return $this->render('admin_patient/index.html.twig', [
            'patients' => $patientRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'admin_patient_show', methods: ['GET'])]
    public function show(Patient $patient): Response
    {
        return $this->render('admin_patient/show.html.twig', [
            'patient' => $patient,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_patient_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PatientEditType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle password update if provided
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $encodedPassword = $this->passwordHasher->hashPassword($patient, $plainPassword);
                $patient->setPassword($encodedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Patient modifié avec succès!');
            return $this->redirectToRoute('admin_patient_show', ['id' => $patient->getId()]);
        }

        return $this->render('admin_patient/edit.html.twig', [
            'patient' => $patient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/toggle-status', name: 'admin_patient_toggle_status', methods: ['POST'])]
    public function toggleStatus(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_status'.$patient->getId(), $request->request->get('_token'))) {
            $patient->setIsActive(!$patient->getIsActive());
            $entityManager->flush();

            $status = $patient->getIsActive() ? 'activé' : 'désactivé';
            $this->addFlash('success', "Compte patient {$status} avec succès!");
        }

        return $this->redirectToRoute('admin_patient_show', ['id' => $patient->getId()]);
    }

    #[Route('/{id}', name: 'admin_patient_delete', methods: ['POST'])]
    public function delete(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$patient->getId(), $request->request->get('_token'))) {
            $entityManager->remove($patient);
            $entityManager->flush();

            $this->addFlash('success', 'Patient supprimé avec succès!');
        } else {
            $this->addFlash('error', 'Token CSRF invalide!');
        }

        return $this->redirectToRoute('admin_patient_index');
    }
}
