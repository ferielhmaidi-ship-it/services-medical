<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\Rapport;
use App\Form\RapportType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RapportController extends AbstractController
{
    #[Route('/rapport/add', name: 'rapport_add')]
    #[IsGranted('ROLE_MEDECIN')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $medecin = $this->getAuthenticatedMedecin();
        $selectedPatient = $this->getSelectedPatientFromSession($request, $em, $medecin);
        $selectedAppointment = $this->getSelectedAppointmentFromSession($request, $em, $medecin);

        $rapport = new Rapport();
        $rapport->setCreatedAt(new \DateTime());
        $rapport->setUpdatedAt(new \DateTime());
        if ($selectedAppointment instanceof Appointment) {
            $rapport->setAppointment($selectedAppointment);
        }

        $form = $this->createForm(RapportType::class, $rapport, [
            'medecin' => $medecin,
            'patient' => $selectedPatient,
        ]);
        $form->handleRequest($request);

        $qb = $em->getRepository(Appointment::class)
            ->createQueryBuilder('a')
            ->andWhere('a.doctor = :doctor')
            ->setParameter('doctor', $medecin)
            ->orderBy('a.date', 'DESC')
            ->addOrderBy('a.startTime', 'DESC');

        if ($selectedPatient instanceof Patient) {
            $qb->andWhere('a.patient = :patient')
                ->setParameter('patient', $selectedPatient);
        }

        $appointmentList = $qb->getQuery()->getResult();

        if ($form->isSubmitted()) {
            $appointment = $form->get('rendezVous')->getData();
            if ($appointment instanceof Appointment) {
                $this->assertAppointmentOwner($appointment, $medecin);
                $this->assertAppointmentMatchesSelectedPatient($appointment, $selectedPatient);
                $patient = $this->findPatientForAppointment($em, $appointment);
                if (!$patient instanceof Patient) {
                    throw $this->createAccessDeniedException('Patient introuvable pour ce rendez-vous.');
                }

                $rapport->setAppointment($appointment);
                $rapport->setMedecin($medecin);
                $rapport->setPatient($patient);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $appointment = $form->get('rendezVous')->getData();
            if (!$appointment instanceof Appointment) {
                throw $this->createAccessDeniedException('Rendez-vous invalide.');
            }

            $this->assertAppointmentOwner($appointment, $medecin);
            $this->assertAppointmentMatchesSelectedPatient($appointment, $selectedPatient);

            $patient = $this->findPatientForAppointment($em, $appointment);
            if (!$patient instanceof Patient) {
                throw $this->createAccessDeniedException('Patient introuvable pour ce rendez-vous.');
            }
            $rapport->setMedecin($medecin);
            $rapport->setPatient($patient);
            $rapport->setAppointment($appointment);

            $em->persist($rapport);
            $em->flush();

            $this->addFlash('success', 'Rapport ajoute avec succes.');

            $returnUrl = $request->query->get('return');
            if (\is_string($returnUrl) && str_starts_with($returnUrl, '/')) {
                return $this->redirect($returnUrl);
            }

            return $this->redirectToRoute('rapports_ordonnances', [
                'idpatient' => $appointment->getPatient()->getId(),
                'idmedecin' => $medecin->getId(),
                'idrendezvous' => $appointment->getId(),
            ]);
        }

        return $this->render('rapport/add.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $appointmentList,
            'medecins' => [$medecin],
            'patients' => [],
        ]);
    }

    #[Route('/rapport/mod/{idrapport}', name: 'rapport_mod')]
    #[IsGranted('ROLE_MEDECIN')]
    public function mod(Request $request, EntityManagerInterface $em, int $idrapport): Response
    {
        $medecin = $this->getAuthenticatedMedecin();

        $rapport = $em->getRepository(Rapport::class)->find($idrapport);
        if (!$rapport) {
            throw $this->createNotFoundException('Rapport introuvable.');
        }

        $this->assertRapportOwner($rapport, $medecin);

        $form = $this->createForm(RapportType::class, $rapport, [
            'is_mod' => true,
            'medecin' => $medecin,
            'patient' => $rapport->getPatient(),
        ]);
        $form->handleRequest($request);

        $appointmentList = $em->getRepository(Appointment::class)
            ->createQueryBuilder('a')
            ->andWhere('a.doctor = :doctor')
            ->setParameter('doctor', $medecin)
            ->orderBy('a.date', 'DESC')
            ->addOrderBy('a.startTime', 'DESC')
            ->getQuery()
            ->getResult();

        if ($form->isSubmitted()) {
            $appointment = $form->get('rendezVous')->getData();
            if ($appointment instanceof Appointment) {
                $this->assertAppointmentOwner($appointment, $medecin);
                $this->assertAppointmentMatchesSelectedPatient($appointment, $rapport->getPatient());

                $patient = $this->findPatientForAppointment($em, $appointment);
                if (!$patient instanceof Patient) {
                    throw $this->createAccessDeniedException('Patient introuvable pour ce rendez-vous.');
                }

                $rapport->setAppointment($appointment);
                $rapport->setMedecin($medecin);
                $rapport->setPatient($patient);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $appointment = $form->get('rendezVous')->getData();
            if ($appointment instanceof Appointment) {
                $this->assertAppointmentOwner($appointment, $medecin);
                $this->assertAppointmentMatchesSelectedPatient($appointment, $rapport->getPatient());
            }

            $rapport->setUpdatedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'Rapport modifie avec succes.');

            $returnUrl = $request->query->get('return');
            if (\is_string($returnUrl) && str_starts_with($returnUrl, '/')) {
                return $this->redirect($returnUrl);
            }

            $currentAppointment = $appointment ?? $rapport->getAppointment();
            if ($currentAppointment instanceof Appointment) {
                return $this->redirectToRoute('rapports_ordonnances', [
                    'idpatient' => $currentAppointment->getPatient()->getId(),
                    'idmedecin' => $medecin->getId(),
                    'idrendezvous' => $currentAppointment->getId(),
                ]);
            }

            return $this->redirectToRoute('pages_dossier_medical');
        }

        return $this->render('rapport/mod.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $appointmentList,
            'medecins' => [$medecin],
            'patients' => [],
        ]);
    }

    #[Route('/rapport/mod1/{idrapport}', name: 'rapport_mod1')]
    #[IsGranted('ROLE_MEDECIN')]
    public function mod1(Request $request, EntityManagerInterface $em, int $idrapport): Response
    {
        $medecin = $this->getAuthenticatedMedecin();

        $rapport = $em->getRepository(Rapport::class)->find($idrapport);
        if (!$rapport) {
            throw $this->createNotFoundException('Rapport introuvable.');
        }

        $this->assertRapportOwner($rapport, $medecin);

        $form = $this->createForm(RapportType::class, $rapport, [
            'is_mod' => true,
            'medecin' => $medecin,
            'patient' => $rapport->getPatient(),
        ]);
        $form->handleRequest($request);

        $appointmentList = $em->getRepository(Appointment::class)
            ->createQueryBuilder('a')
            ->andWhere('a.doctor = :doctor')
            ->setParameter('doctor', $medecin)
            ->orderBy('a.date', 'DESC')
            ->addOrderBy('a.startTime', 'DESC')
            ->getQuery()
            ->getResult();

        if ($form->isSubmitted()) {
            $appointment = $form->get('rendezVous')->getData();
            if ($appointment instanceof Appointment) {
                $this->assertAppointmentOwner($appointment, $medecin);
                $this->assertAppointmentMatchesSelectedPatient($appointment, $rapport->getPatient());

                $patient = $this->findPatientForAppointment($em, $appointment);
                if (!$patient instanceof Patient) {
                    throw $this->createAccessDeniedException('Patient introuvable pour ce rendez-vous.');
                }

                $rapport->setAppointment($appointment);
                $rapport->setMedecin($medecin);
                $rapport->setPatient($patient);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $appointment = $form->get('rendezVous')->getData();
            if ($appointment instanceof Appointment) {
                $this->assertAppointmentOwner($appointment, $medecin);
                $this->assertAppointmentMatchesSelectedPatient($appointment, $rapport->getPatient());
            }

            $rapport->setUpdatedAt(new \DateTime());
            $em->flush();

            $returnUrl = $request->query->get('return');
            if (\is_string($returnUrl) && str_starts_with($returnUrl, '/')) {
                return $this->redirect($returnUrl);
            }

            return $this->redirectToRoute('pages_dossier_medical');
        }

        return $this->render('rapport/mod1.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $appointmentList,
            'medecins' => [$medecin],
            'patients' => [],
        ]);
    }

    #[Route('/rapport/delete/{idrapport}', name: 'rapport_delete')]
    #[IsGranted('ROLE_MEDECIN')]
    public function delete(Request $request, EntityManagerInterface $em, int $idrapport): Response
    {
        $medecin = $this->getAuthenticatedMedecin();

        $rapport = $em->getRepository(Rapport::class)->find($idrapport);
        if (!$rapport) {
            throw $this->createNotFoundException('Rapport introuvable.');
        }

        $this->assertRapportOwner($rapport, $medecin);

        $em->remove($rapport);
        $em->flush();

        $this->addFlash('success', 'Rapport supprime avec succes.');

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/rapport/show/{idrapport}', name: 'rapport_show')]
    public function show(Request $request, EntityManagerInterface $em, int $idrapport): Response
    {
        $rapport = $em->getRepository(Rapport::class)->find($idrapport);
        if (!$rapport) {
            throw $this->createNotFoundException('Rapport introuvable.');
        }

        $user = $this->getUser();
        if ($user instanceof Medecin) {
            $this->assertRapportOwner($rapport, $user);
        } elseif ($user instanceof Patient) {
            if ($rapport->getPatient()?->getId() !== $user->getId()) {
                throw $this->createAccessDeniedException('Acces non autorise a ce rapport.');
            }
        } else {
            throw $this->createAccessDeniedException('Acces refuse.');
        }

        $source = $request->query->get('source');
        $template = $source === 'tableau' ? 'rapport/_detail_modal.html.twig' : 'rapport/show.html.twig';

        return $this->render($template, [
            'rapport' => $rapport,
        ]);
    }

    private function getAuthenticatedMedecin(): Medecin
    {
        $user = $this->getUser();

        if (!$user instanceof Medecin) {
            throw $this->createAccessDeniedException('Acces reserve aux medecins.');
        }

        return $user;
    }

    private function assertAppointmentOwner(Appointment $appointment, Medecin $medecin): void
    {
        if ($appointment->getDoctor()->getId() !== $medecin->getId()) {
            throw $this->createAccessDeniedException('Ce rendez-vous ne vous appartient pas.');
        }
    }

    private function assertRapportOwner(Rapport $rapport, Medecin $medecin): void
    {
        if ($rapport->getMedecin()?->getId() !== $medecin->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez acceder qu\'a vos propres rapports.');
        }
    }

    private function assertAppointmentMatchesSelectedPatient(Appointment $appointment, ?Patient $patient): void
    {
        if ($patient instanceof Patient && $appointment->getPatient()->getId() !== $patient->getId()) {
            throw $this->createAccessDeniedException('Le rendez-vous choisi ne correspond pas au patient selectionne.');
        }
    }

    private function getSelectedPatientFromSession(Request $request, EntityManagerInterface $em, Medecin $medecin): ?Patient
    {
        $selection = $request->getSession()->get('dossier_medical_selection');
        if (!is_array($selection)) {
            return null;
        }
        if ((int) ($selection['idmedecin'] ?? 0) !== (int) $medecin->getId()) {
            return null;
        }

        $idPatient = (int) ($selection['idpatient'] ?? 0);
        if ($idPatient <= 0) {
            return null;
        }

        $patient = $em->getRepository(Patient::class)->find($idPatient);

        return $patient instanceof Patient ? $patient : null;
    }

    private function getSelectedAppointmentFromSession(Request $request, EntityManagerInterface $em, Medecin $medecin): ?Appointment
    {
        $selection = $request->getSession()->get('dossier_medical_selection');
        if (!is_array($selection)) {
            return null;
        }
        if ((int) ($selection['idmedecin'] ?? 0) !== (int) $medecin->getId()) {
            return null;
        }

        $idAppointment = (int) ($selection['idrendezvous'] ?? 0);
        if ($idAppointment <= 0) {
            return null;
        }

        $appointment = $em->getRepository(Appointment::class)->find($idAppointment);
        if (!$appointment instanceof Appointment) {
            return null;
        }
        if ($appointment->getDoctor()->getId() !== $medecin->getId()) {
            return null;
        }

        return $appointment;
    }

    private function findPatientForAppointment(EntityManagerInterface $em, Appointment $appointment): ?Patient
    {
        $patient = $em->getRepository(Patient::class)->find($appointment->getPatient()->getId());

        return $patient instanceof Patient ? $patient : null;
    }
}
