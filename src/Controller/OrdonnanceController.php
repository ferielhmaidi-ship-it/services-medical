<?php

namespace App\Controller;

use App\Entity\Ordonnance;
use App\Entity\Rendezvous;
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
        $ordonnance->setCreatedAt(new \DateTime());
        $ordonnance->setUpdatedAt(new \DateTime());
        $ordonnance->setDateordonnance(new \DateTime());

        $form = $this->createForm(OrdonnanceType::class, $ordonnance);
        $form->handleRequest($request);

        $rendezvous = $em->getRepository(Rendezvous::class)
            ->createQueryBuilder('r')
            ->orderBy('r.date', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $medecins = $em->getRepository(Medecin::class)->findAll();
        $patients = $em->getRepository(Patient::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion Rendez-vous
            $dateRdv = $form->get('idrendezvous')->getData();
            $rdv = $em->getRepository(Rendezvous::class)->findOneBy(['date' => new \DateTime($dateRdv)]);
            if (!$rdv) {
                $rdv = new Rendezvous();
                $rdv->setDate(new \DateTime($dateRdv));
                $rdv->setSpecialite('');
                $em->persist($rdv);
            }
            $ordonnance->setIdrendezvous($rdv);

            // Gestion Médecin
            $nomMedecin = $form->get('idmedecin')->getData();
            $medecin = $em->getRepository(Medecin::class)->findOneBy(['nom' => $nomMedecin]);
            if (!$medecin) {
                $medecin = new Medecin();
                $medecin->setNom($nomMedecin);
                $medecin->setSpecialite('');
                $em->persist($medecin);
            }
            $ordonnance->setIdmedecin($medecin);

            // Gestion Patient
            $nomPatient = $form->get('idpatient')->getData();
            $patient = null;
            if (strpos($nomPatient, ' ') !== false) {
                [$nom, $prenom] = explode(' ', $nomPatient, 2);
                $patient = $em->getRepository(Patient::class)->findOneBy(['nom' => $nom, 'prenom' => $prenom]);
            } else {
                $patient = $em->getRepository(Patient::class)->findOneBy(['nom' => $nomPatient]);
            }
            if (!$patient) {
                $patient = new Patient();
                if (strpos($nomPatient, ' ') !== false) {
                    [$nom, $prenom] = explode(' ', $nomPatient, 2);
                    $patient->setNom($nom);
                    $patient->setPrenom($prenom);
                } else {
                    $patient->setNom($nomPatient);
                    $patient->setPrenom('');
                }
                $em->persist($patient);
            }
            $ordonnance->setIdpatient($patient);

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

    
    
    #[Route(
    '/ordonnance/mod/{idrendezvous}/{idmedecin}/{idpatient}/{idordonnance}',
    name: 'ordonnance_mod'
)]
public function mod(
    Request $request,
    EntityManagerInterface $em,
    int $idordonnance
): Response
{
    $ordonnance = $em->getRepository(Ordonnance::class)->find($idordonnance);

    if (!$ordonnance) {
        throw $this->createNotFoundException('Ordonnance introuvable');
    }

    // IMPORTANT : on passe is_mod = true
    $form = $this->createForm(OrdonnanceType::class, $ordonnance, [
        'is_mod' => true,
    ]);
    $form->handleRequest($request);

    $rendezvous = $em->getRepository(Rendezvous::class)
        ->createQueryBuilder('r')
        ->orderBy('r.date', 'DESC')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();

    $medecins = $em->getRepository(Medecin::class)->findAll();
    $patients = $em->getRepository(Patient::class)->findAll();

    if ($form->isSubmitted() && $form->isValid()) {

        /* ================= RENDEZ-VOUS ================= */
        $dateRdv = $form->get('idrendezvous')->getData();
        if ($dateRdv) {
            $rdv = $em->getRepository(Rendezvous::class)
                ->findOneBy(['date' => new \DateTime($dateRdv)]);
            if ($rdv) {
                $ordonnance->setIdrendezvous($rdv);
            }
        }

        /* ================= MEDECIN ================= */
        $nomMedecin = $form->get('idmedecin')->getData();
        if ($nomMedecin) {
            $medecin = $em->getRepository(Medecin::class)
                ->findOneBy(['nom' => $nomMedecin]);
            if ($medecin) {
                $ordonnance->setIdmedecin($medecin);
            }
        }

        /* ================= PATIENT ================= */
        $nomPatient = $form->get('idpatient')->getData();
        if ($nomPatient) {
            if (strpos($nomPatient, ' ') !== false) {
                [$nom, $prenom] = explode(' ', $nomPatient, 2);
                $patient = $em->getRepository(Patient::class)
                    ->findOneBy(['nom' => $nom, 'prenom' => $prenom]);
            } else {
                $patient = $em->getRepository(Patient::class)
                    ->findOneBy(['nom' => $nomPatient]);
            }
            if ($patient) {
                $ordonnance->setIdpatient($patient);
            }
        }

        $ordonnance->setUpdatedAt(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('ordonnance_mod', [
            'idrendezvous' => $ordonnance->getIdrendezvous()->getIdrendezvous(),
            'idmedecin'    => $ordonnance->getIdmedecin()->getIdmedecin(),
            'idpatient'    => $ordonnance->getIdpatient()->getIdpatient(),
            'idordonnance' => $ordonnance->getIdordonnance(),
        ]);
    }

    return $this->render('ordonnance/modOrdonnance.html.twig', [
        'form' => $form->createView(),
        'rendezvous' => $rendezvous,
        'medecins' => $medecins,
        'patients' => $patients,
    ]);
}
    #[Route('/ordonnance/del/{idrendezvous}/{idmedecin}/{idpatient}/{idordonnance}', name: 'ordonnance_del')]
public function deleteOrdonnance(
    Request $request,                 // ✅ ضروري
    EntityManagerInterface $em,
    int $idrendezvous,
    int $idmedecin,
    int $idpatient,
    int $idordonnance
): Response {
    $ordonnance = $em->getRepository(Ordonnance::class)->find($idordonnance);

    if (!$ordonnance) {
        $this->addFlash('error', 'Ordonnance introuvable.');
        return $this->redirect($request->headers->get('referer'));
    }

    $em->remove($ordonnance);
    $em->flush();

    $this->addFlash('success', 'Ordonnance supprimée avec succès.');

    // ✅ الرجوع لنفس الصفحة
    return $this->redirect($request->headers->get('referer'));
}


    #[Route('/ordonnance/show/{idordonnance}', name: 'ordonnance_show')]
public function show(Ordonnance $ordonnance): Response
{
    return $this->render('ordonnance/afficher.html.twig', [
        'ordonnance' => $ordonnance
    ]);
}

}