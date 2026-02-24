<?php
// src/Controller/MedecinDashboardController.php

namespace App\Controller;

use App\Entity\Medecin;
use App\Form\MedecinProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/medecin')]
#[IsGranted('ROLE_MEDECIN')]
class MedecinDashboardController extends AbstractController
{
    #[Route('/', name: 'medecin_dashboard')]
    public function index(): Response
    {
        $medecin = $this->getUser();

        return $this->render('medecin_dashboard/index.html.twig', [
            'medecin' => $medecin,
        ]);
    }

    #[Route('/profile', name: 'medecin_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var Medecin $medecin */
        $medecin = $this->getUser();

        $form = $this->createForm(MedecinProfileType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($medecin);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('medecin_profile');
        }

        return $this->render('medecin_dashboard/profile.html.twig', [
            'medecin' => $medecin,
            'form' => $form->createView(),
        ]);
    }
}
