<?php

namespace App\Controller;

use App\Entity\Ordonnance;
use App\Entity\RendezVous;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Form\OrdonnanceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrdonnanceController extends AbstractController
{
    #[Route('/ordonnance/add', name: 'ordonnance_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $ordonnance = new Ordonnance();
        $ordonnance->setCreatedAt(new \DateTimeImmutable());
        $ordonnance->setUpdatedAt(new \DateTimeImmutable());
        $ordonnance->setDateordonnance(new \DateTime());

        $form = $this->createForm(OrdonnanceType::class, $ordonnance);
        $form->handleRequest($request);

        $rendezvous = $em->getRepository(RendezVous::class)
            ->createQueryBuilder('r')
            ->orderBy('r.appointmentDate', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $medecins = $em->getRepository(Medecin::class)->findAll();
        $patients = $em->getRepository(Patient::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $rdv = $form->get('rendezVous')->getData();
            if (!$rdv) {
                $this->addFlash('error', 'Veuillez selectionner un rendez-vous.');

                return $this->render('ordonnance/addOrdonnance.html.twig', [
                    'form' => $form->createView(),
                    'rendezvous' => $rendezvous,
                    'medecins' => $medecins,
                    'patients' => $patients,
                ]);
            }

            $ordonnance->setRendezVous($rdv);
            $ordonnance->setMedecin($rdv->getDoctor());
            $ordonnance->setPatient($rdv->getPatient());

            $em->persist($ordonnance);
            $em->flush();

            return $this->redirectToRoute('ordonnance_add');
        }

        return $this->render('ordonnance/addOrdonnance.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $rendezvous,
            'medecins' => $medecins,
            'patients' => $patients,
        ]);
    }

    #[Route('/ordonnance/mod/{id}', name: 'ordonnance_mod')]
    public function mod(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $ordonnance = $em->getRepository(Ordonnance::class)->find($id);

        if (!$ordonnance) {
            throw $this->createNotFoundException('Ordonnance introuvable');
        }

        $form = $this->createForm(OrdonnanceType::class, $ordonnance, [
            'is_mod' => true,
        ]);
        $form->handleRequest($request);

        $rendezvous = $em->getRepository(RendezVous::class)
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
                $ordonnance->setRendezVous($rdv);
                $ordonnance->setMedecin($rdv->getDoctor());
                $ordonnance->setPatient($rdv->getPatient());
            }

            $ordonnance->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->redirectToRoute('ordonnance_mod', ['id' => $ordonnance->getId()]);
        }

        return $this->render('ordonnance/modOrdonnance.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $rendezvous,
            'medecins' => $medecins,
            'patients' => $patients,
        ]);
    }

    #[Route('/ordonnance/del/{id}', name: 'ordonnance_del')]
    public function deleteOrdonnance(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $ordonnance = $em->getRepository(Ordonnance::class)->find($id);

        if (!$ordonnance) {
            $this->addFlash('error', 'Ordonnance introuvable.');
            return $this->redirect($request->headers->get('referer'));
        }

        $em->remove($ordonnance);
        $em->flush();

        $this->addFlash('success', 'Ordonnance supprimee avec succes.');
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/ordonnance/show/{id}', name: 'ordonnance_show')]
    public function show(Ordonnance $ordonnance): Response
    {
        return $this->render('ordonnance/afficher.html.twig', [
            'ordonnance' => $ordonnance,
        ]);
    }
}
