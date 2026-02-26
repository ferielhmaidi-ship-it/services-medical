<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Medecin;
use App\Entity\Ordonnance;
use App\Entity\Patient;
use App\Form\OrdonnanceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OrdonnanceController extends AbstractController
{
    #[Route('/ordonnance/add', name: 'ordonnance_add')]
    #[IsGranted('ROLE_MEDECIN')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $medecin = $this->getAuthenticatedMedecin();
        $selectedPatient = $this->getSelectedPatientFromSession($request, $em, $medecin);
        $selectedAppointment = $this->getSelectedAppointmentFromSession($request, $em, $medecin);

        $ordonnance = new Ordonnance();
        $ordonnance->setCreatedAt(new \DateTimeImmutable());
        $ordonnance->setUpdatedAt(new \DateTimeImmutable());
        $ordonnance->setDateordonnance(new \DateTime());
        if ($selectedAppointment instanceof Appointment) {
            $ordonnance->setAppointment($selectedAppointment);
        }

        $form = $this->createForm(OrdonnanceType::class, $ordonnance, [
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

                $ordonnance->setAppointment($appointment);
                $ordonnance->setMedecin($medecin);
                $ordonnance->setPatient($patient);
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
            $ordonnance->setMedecin($medecin);
            $ordonnance->setPatient($patient);
            $ordonnance->setAppointment($appointment);

            $em->persist($ordonnance);
            $em->flush();

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

        return $this->render('ordonnance/addOrdonnance.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $appointmentList,
            'medecins' => [$medecin],
            'patients' => [],
        ]);
    }

    #[Route('/ordonnance/mod/{id}', name: 'ordonnance_mod')]
    #[IsGranted('ROLE_MEDECIN')]
    public function mod(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $medecin = $this->getAuthenticatedMedecin();

        $ordonnance = $em->getRepository(Ordonnance::class)->find($id);
        if (!$ordonnance) {
            throw $this->createNotFoundException('Ordonnance introuvable');
        }

        $this->assertOrdonnanceOwner($ordonnance, $medecin);

        $form = $this->createForm(OrdonnanceType::class, $ordonnance, [
            'is_mod' => true,
            'medecin' => $medecin,
            'patient' => $ordonnance->getPatient(),
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
                $this->assertAppointmentMatchesSelectedPatient($appointment, $ordonnance->getPatient());

                $patient = $this->findPatientForAppointment($em, $appointment);
                if (!$patient instanceof Patient) {
                    throw $this->createAccessDeniedException('Patient introuvable pour ce rendez-vous.');
                }

                $ordonnance->setAppointment($appointment);
                $ordonnance->setMedecin($medecin);
                $ordonnance->setPatient($patient);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $appointment = $form->get('rendezVous')->getData();
            if ($appointment instanceof Appointment) {
                $this->assertAppointmentOwner($appointment, $medecin);
                $this->assertAppointmentMatchesSelectedPatient($appointment, $ordonnance->getPatient());
            }

            $ordonnance->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            $returnUrl = $request->query->get('return');
            if (\is_string($returnUrl) && str_starts_with($returnUrl, '/')) {
                return $this->redirect($returnUrl);
            }

            $currentAppointment = $appointment ?? $ordonnance->getAppointment();
            if ($currentAppointment instanceof Appointment) {
                return $this->redirectToRoute('rapports_ordonnances', [
                    'idpatient' => $currentAppointment->getPatient()->getId(),
                    'idmedecin' => $medecin->getId(),
                    'idrendezvous' => $currentAppointment->getId(),
                ]);
            }

            return $this->redirectToRoute('pages_dossier_medical');
        }

        return $this->render('ordonnance/modOrdonnance.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $appointmentList,
            'medecins' => [$medecin],
            'patients' => [],
        ]);
    }

    #[Route('/ordonnance/mod1/{id}', name: 'ordonnance_mod1')]
    #[IsGranted('ROLE_MEDECIN')]
    public function mod1(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $medecin = $this->getAuthenticatedMedecin();

        $ordonnance = $em->getRepository(Ordonnance::class)->find($id);
        if (!$ordonnance) {
            throw $this->createNotFoundException('Ordonnance introuvable');
        }

        $this->assertOrdonnanceOwner($ordonnance, $medecin);

        $form = $this->createForm(OrdonnanceType::class, $ordonnance, [
            'is_mod' => true,
            'medecin' => $medecin,
            'patient' => $ordonnance->getPatient(),
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
                $this->assertAppointmentMatchesSelectedPatient($appointment, $ordonnance->getPatient());

                $patient = $this->findPatientForAppointment($em, $appointment);
                if (!$patient instanceof Patient) {
                    throw $this->createAccessDeniedException('Patient introuvable pour ce rendez-vous.');
                }

                $ordonnance->setAppointment($appointment);
                $ordonnance->setMedecin($medecin);
                $ordonnance->setPatient($patient);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $appointment = $form->get('rendezVous')->getData();
            if ($appointment instanceof Appointment) {
                $this->assertAppointmentOwner($appointment, $medecin);
                $this->assertAppointmentMatchesSelectedPatient($appointment, $ordonnance->getPatient());
            }

            $ordonnance->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            $returnUrl = $request->query->get('return');
            if (\is_string($returnUrl) && str_starts_with($returnUrl, '/')) {
                return $this->redirect($returnUrl);
            }

            return $this->redirectToRoute('pages_dossier_medical');
        }

        return $this->render('ordonnance/modOrdonnance1.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $appointmentList,
            'medecins' => [$medecin],
            'patients' => [],
        ]);
    }

    #[Route('/ordonnance/del/{id}', name: 'ordonnance_del')]
    #[IsGranted('ROLE_MEDECIN')]
    public function deleteOrdonnance(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $medecin = $this->getAuthenticatedMedecin();

        $ordonnance = $em->getRepository(Ordonnance::class)->find($id);
        if (!$ordonnance) {
            $this->addFlash('error', 'Ordonnance introuvable.');

            return $this->redirect($request->headers->get('referer'));
        }

        $this->assertOrdonnanceOwner($ordonnance, $medecin);

        $em->remove($ordonnance);
        $em->flush();

        $this->addFlash('success', 'Ordonnance supprimee avec succes.');

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/ordonnance/show/{id}', name: 'ordonnance_show')]
    public function show(Request $request, Ordonnance $ordonnance): Response
    {
        $user = $this->getUser();

        if ($user instanceof Medecin) {
            $this->assertOrdonnanceOwner($ordonnance, $user);
        } elseif ($user instanceof Patient) {
            if ($ordonnance->getPatient()?->getId() !== $user->getId()) {
                throw $this->createAccessDeniedException('Acces non autorise a cette ordonnance.');
            }
        } else {
            throw $this->createAccessDeniedException('Acces refuse.');
        }

        $source = $request->query->get('source');
        $template = $source === 'tableau' ? 'ordonnance/afficher.html.twig' : 'ordonnance/show.html.twig';

        return $this->render($template, [
            'ordonnance' => $ordonnance,
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

    private function assertOrdonnanceOwner(Ordonnance $ordonnance, Medecin $medecin): void
    {
        if ($ordonnance->getMedecin()?->getId() !== $medecin->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez acceder qu\'a vos propres ordonnances.');
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
