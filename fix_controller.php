<?php
/**
 * Fix RendezvouscontrollerController.php - write via file_put_contents to bypass OneDrive
 */

$path = __DIR__ . '/src/Controller/RendezvouscontrollerController.php';

$content = <<<'CONTROLLER'
<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rendezvous')]
class RendezvouscontrollerController extends AbstractController
{
    #[Route('/', name: 'app_rendezvous_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $appointments = $entityManager
            ->getRepository(Appointment::class)
            ->findAll();

        return $this->render('rendezvous/index.html.twig', [
            'appointments' => $appointments,
        ]);
    }

    #[Route('/new', name: 'app_rendezvous_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();

            return $this->redirectToRoute('app_rendezvous_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rendezvous/new.html.twig', [
            'appointment' => $appointment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_rendezvous_show', methods: ['GET'])]
    public function show(Appointment $appointment): Response
    {
        return $this->render('rendezvous/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rendezvous_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_rendezvous_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rendezvous/edit.html.twig', [
            'appointment' => $appointment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_rendezvous_delete', methods: ['POST'])]
    public function delete(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$appointment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($appointment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rendezvous_index', [], Response::HTTP_SEE_OTHER);
    }
}
CONTROLLER;

$result = file_put_contents($path, $content);
echo "Written: $result bytes\n";

// Verify
$readBack = file_get_contents($path);
if (strpos($readBack, 'class RendezvouscontrollerController') !== false) {
    echo "VERIFIED: Class definition found in file\n";
}
else {
    echo "ERROR: Class definition NOT found after write!\n";
    echo "File content starts with: " . substr($readBack, 0, 100) . "\n";
}

// Clear Symfony cache
echo "\nClearing Symfony cache...\n";
