<?php
// src/Controller/AdminMedecinController.php

namespace App\Controller;

use App\Entity\Medecin;
use App\Form\MedecinEditType;
use App\Constants\Specialty;
use App\Repository\MedecinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\DoctorVerificationService;

#[Route('/admin/medecin')]
#[IsGranted('ROLE_ADMIN')]
class AdminMedecinController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    private DoctorVerificationService $doctorVerificationService;

    public function __construct(UserPasswordHasherInterface $passwordHasher, DoctorVerificationService $doctorVerificationService)
    {
        $this->passwordHasher = $passwordHasher;
        $this->doctorVerificationService = $doctorVerificationService;
    }

    #[Route('/new', name: 'admin_medecin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $medecin = new Medecin();
        $form = $this->createForm(MedecinEditType::class, $medecin, [
            'is_new' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $medecin->setPassword(
                    $this->passwordHasher->hashPassword($medecin, $plainPassword)
                );
            } else {
                // For a new doctor, we need a password
                $this->addFlash('error', 'Un mot de passe est requis pour un nouveau médecin.');
                return $this->render('admin_medecin/new.html.twig', [
                    'medecin' => $medecin,
                    'form' => $form,
                ]);
            }

            $entityManager->persist($medecin);
            $entityManager->flush();

            $this->addFlash('success', 'Médecin créé avec succès!');
            return $this->redirectToRoute('admin_medecin_index');
        }

        return $this->render('admin_medecin/new.html.twig', [
            'medecin' => $medecin,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/', name: 'admin_medecin_index', methods: ['GET'])]
    public function index(MedecinRepository $medecinRepository): Response
    {
        return $this->render('admin_medecin/index.html.twig', [
            'medecins' => $medecinRepository->findAll(),
            'specialties' => Specialty::getChoices(), // Add this line
        ]);
    }

    #[Route('/{id}', name: 'admin_medecin_show', methods: ['GET'])]
    public function show(Medecin $medecin): Response
    {
        return $this->render('admin_medecin/show.html.twig', [
            'medecin' => $medecin,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_medecin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MedecinEditType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle password update if provided
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $encodedPassword = $this->passwordHasher->hashPassword($medecin, $plainPassword);
                $medecin->setPassword($encodedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Médecin modifié avec succès!');
            return $this->redirectToRoute('admin_medecin_show', ['id' => $medecin->getId()]);
        }

        return $this->render('admin_medecin/edit.html.twig', [
            'medecin' => $medecin,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/toggle-status', name: 'admin_medecin_toggle_status', methods: ['POST'])]
    public function toggleStatus(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_status'.$medecin->getId(), $request->request->get('_token'))) {
            $medecin->setIsActive(!$medecin->getIsActive());
            $entityManager->flush();

            $status = $medecin->getIsActive() ? 'activé' : 'désactivé';
            $this->addFlash('success', "Compte médecin {$status} avec succès!");
        }

        return $this->redirectToRoute('admin_medecin_show', ['id' => $medecin->getId()]);
    }

    #[Route('/{id}/toggle-verification', name: 'admin_medecin_toggle_verification', methods: ['POST'])]
    public function toggleVerification(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_verification'.$medecin->getId(), $request->request->get('_token'))) {
            $medecin->setIsVerified(!$medecin->getIsVerified());
            $entityManager->flush();

            $status = $medecin->getIsVerified() ? 'vérifié' : 'non vérifié';
            $this->addFlash('success', "Compte médecin marqué comme {$status}!");
        }

        return $this->redirectToRoute('admin_medecin_show', ['id' => $medecin->getId()]);
    }

    #[Route('/{id}', name: 'admin_medecin_delete', methods: ['POST'])]
    public function delete(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$medecin->getId(), $request->request->get('_token'))) {
            $entityManager->remove($medecin);
            $entityManager->flush();

            $this->addFlash('success', 'Médecin supprimé avec succès!');
        } else {
            $this->addFlash('error', 'Token CSRF invalide!');
        }

        return $this->redirectToRoute('admin_medecin_index');
    }



    #[Route('/{id}/verify-with-official', name: 'admin_medecin_verify_official', methods: ['POST'])]
public function verifyWithOfficial(
    Medecin $medecin,
    EntityManagerInterface $entityManager,
    DoctorVerificationService $verificationService,
    Request $request
): Response {
    if (!$this->isCsrfTokenValid('verify_official' . $medecin->getId(), $request->request->get('_token'))) {
        $this->addFlash('error', 'Token CSRF invalide.');
        return $this->redirectToRoute('admin_medecin_show', ['id' => $medecin->getId()]);
    }

    $found = $verificationService->verify($medecin);

    if ($found) {
        $medecin->setIsVerified(true);
        $entityManager->flush();
        $this->addFlash('success', '✅ Médecin trouvé dans l\'annuaire officiel – compte automatiquement vérifié.');
    } else {
        $this->addFlash('warning', '⚠️ Aucune correspondance trouvée dans l\'annuaire officiel. Vérifiez les informations ou effectuez une vérification manuelle.');
    }

    return $this->redirectToRoute('admin_medecin_show', ['id' => $medecin->getId()]);
}

}
