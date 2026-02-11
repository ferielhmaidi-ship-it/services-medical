<?php

namespace App\Controller;

use App\Entity\Rapport;
use App\Entity\RendezVous;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Form\RapportType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RapportController extends AbstractController
{
    #[Route('/rapport/add', name: 'rapport_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $rapport = new Rapport();
        $rapport->setCreatedAt(new \DateTime());
        $rapport->setUpdatedAt(new \DateTime());

        $form = $this->createForm(RapportType::class, $rapport);
        $form->handleRequest($request);

        // Derniers rendez-vous
        $rendezvousList = $em->getRepository(RendezVous::class)
            ->createQueryBuilder('r')
            ->orderBy('r.appointmentDate', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Liste complète des médecins et patients pour datalist
        $medecins = $em->getRepository(Medecin::class)->findAll();
        $patients = $em->getRepository(Patient::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $rdv = $form->get('rendezVous')->getData();
            if ($rdv) {
                $rapport->setRendezVous($rdv);
            }

            $medecin = $form->get('doctor')->getData();
            if ($medecin) {
                $rapport->setMedecin($medecin);
            }

            $patient = $form->get('patient')->getData();
            if ($patient) {
                $rapport->setPatient($patient);
            }

            $em->persist($rapport);
            $em->flush();

            $this->addFlash('success', 'Rapport ajouté avec succès.');

            return $this->redirectToRoute('rapport_add');
        }

        return $this->render('rapport/add.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $rendezvousList,
            'medecins' => $medecins,
            'patients' => $patients,
        ]);
    }

    #[Route('/rapport/mod/{idrapport}', name: 'rapport_mod')]
    public function mod(Request $request, EntityManagerInterface $em, int $idrapport): Response
    {
        $rapport = $em->getRepository(Rapport::class)->find($idrapport);
        if (!$rapport) {
            throw $this->createNotFoundException('Rapport introuvable.');
        }

        $form = $this->createForm(RapportType::class, $rapport, ['is_mod' => true]);
        $form->handleRequest($request);

        $rendezvousList = $em->getRepository(RendezVous::class)
            ->createQueryBuilder('r')
            ->orderBy('r.appointmentDate', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $medecins = $em->getRepository(Medecin::class)->findAll();
        $patients = $em->getRepository(Patient::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $rdv = $form->get('rendezVous')->getData();
            if ($rdv) {
                $rapport->setRendezVous($rdv);
            }

            $medecin = $form->get('doctor')->getData();
            if ($medecin) {
                $rapport->setMedecin($medecin);
            }

            $patient = $form->get('patient')->getData();
            if ($patient) {
                $rapport->setPatient($patient);
            }

            $rapport->setUpdatedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'Rapport modifié avec succès.');

            return $this->redirectToRoute('rapport_mod', [
                'idrapport' => $rapport->getId(),
            ]);
        }

        return $this->render('rapport/mod.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $rendezvousList,
            'medecins' => $medecins,
            'patients' => $patients,
        ]);
    }

    #[Route('/rapport/delete/{idrapport}', name: 'rapport_delete')]
    public function delete(Request $request, EntityManagerInterface $em, int $idrapport): Response
    {
        $rapport = $em->getRepository(Rapport::class)->find($idrapport);
        if (!$rapport) {
            throw $this->createNotFoundException('Rapport introuvable.');
        }

        $em->remove($rapport);
        $em->flush();

        $this->addFlash('success', 'Rapport supprimé avec succès.');

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/rapport/show/{idrapport}', name: 'rapport_show')]
    public function show(EntityManagerInterface $em, int $idrapport): Response
    {
        $rapport = $em->getRepository(Rapport::class)->find($idrapport);
        if (!$rapport) {
            throw $this->createNotFoundException('Rapport introuvable.');
        }

        return $this->render('rapport/show.html.twig', [
            'rapport' => $rapport,
        ]);
    }
}
