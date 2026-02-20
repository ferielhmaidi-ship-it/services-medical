<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\RendezVous;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/feedback')]
class PatientFeedbackController extends AbstractController
{
    #[Route('/new/{id}', name: 'app_feedback_new_for_rdv')]
    public function newForRdv(
        RendezVous $rendezVous,
        Request $request,
        EntityManagerInterface $em,
        PatientRepository $patientRepo
    ): Response
    {
        // Vérifier que le RDV est terminé
        if ($rendezVous->getStatut() !== 'termine') {
            $this->addFlash('error', 'Vous ne pouvez donner un avis que pour un rendez-vous terminé.');
            return $this->redirectToRoute('app_my_appointments');
        }

        // Patient fixe (ID = 1)
        $patient = $patientRepo->find(1);

        // Vérifier si le patient a déjà donné un feedback pour ce médecin
        $existingFeedback = $em->getRepository(Feedback::class)->findOneBy([
            'patient' => $patient,
            'medecin' => $rendezVous->getDoctor()
        ]);

        if ($existingFeedback) {
            $this->addFlash('error', 'Vous avez déjà donné votre avis pour ce médecin.');
            return $this->redirectToRoute('app_my_appointments');
        }

        if ($request->isMethod('POST')) {
            $rating = (int) $request->request->get('rating');
            $comment = $request->request->get('comment');

            if ($rating < 1 || $rating > 5) {
                $this->addFlash('error', 'La note doit être entre 1 et 5.');
            } elseif (strlen($comment) < 10) {
                $this->addFlash('error', 'Le commentaire doit contenir au moins 10 caractères.');
            } else {
                $feedback = new Feedback();
                $feedback->setRating($rating);
                $feedback->setComment($comment);
                $feedback->setPatient($patient);
                $feedback->setMedecin($rendezVous->getDoctor());

                $em->persist($feedback);
                $em->flush();

                $this->addFlash('success', 'Merci pour votre avis !');
                return $this->redirectToRoute('app_my_appointments');
            }
        }

        return $this->render('patient_feedback/new.html.twig', [
            'rendezVous' => $rendezVous,
        ]);
    }
}