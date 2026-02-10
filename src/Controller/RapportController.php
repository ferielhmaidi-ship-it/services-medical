<?php

namespace App\Controller;

use App\Entity\Rapport;
use App\Entity\Rendezvous;
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
        $rendezvous = $em->getRepository(Rendezvous::class)
            ->createQueryBuilder('r')
            ->orderBy('r.date', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Liste complète des médecins et patients pour datalist
        $medecins = $em->getRepository(Medecin::class)->findAll();
        $patients = $em->getRepository(Patient::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {

            // Récupérer idrendezvous depuis le champ texte
            $dateRdv = $form->get('idrendezvous')->getData();
            $rdv = $em->getRepository(Rendezvous::class)
                      ->findOneBy(['date' => new \DateTime($dateRdv)]);
            if ($rdv) {
                $rapport->setIdrendezvous($rdv);
            }

            // Récupérer idmedecin depuis le texte
            $nomMedecin = $form->get('idmedecin')->getData();
            $medecin = $em->getRepository(Medecin::class)
                           ->findOneBy(['nom' => $nomMedecin]);
            if ($medecin) {
                $rapport->setIdmedecin($medecin);
            }

            // Récupérer idpatient depuis le texte
            $nomPatient = $form->get('idpatient')->getData();
            $prenomPatient = ''; // optionnel si tu veux filtrer par nom+prenom
            if (strpos($nomPatient, ' ') !== false) {
                [$nom, $prenom] = explode(' ', $nomPatient, 2);
                $prenomPatient = $prenom;
                $patient = $em->getRepository(Patient::class)
                              ->findOneBy(['nom' => $nom, 'prenom' => $prenomPatient]);
            } else {
                $patient = $em->getRepository(Patient::class)
                              ->findOneBy(['nom' => $nomPatient]);
            }
            if ($patient) {
                $rapport->setIdpatient($patient);
            }

            $em->persist($rapport);
            $em->flush();

            return $this->redirectToRoute('rapport_add');
        }

        return $this->render('rapport/add.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $rendezvous,
            'medecins' => $medecins,
            'patients' => $patients,
        ]);
    }

    #[Route('/rapport/mod/{idrendezvous}/{idmedecin}/{idpatient}/{idrapport}', name: 'rapport_mod')]
    public function mod(Request $request, EntityManagerInterface $em, int $idrapport): Response
    {
        $rapport = $em->getRepository(Rapport::class)->find($idrapport);
        if (!$rapport) {
            throw $this->createNotFoundException('Rapport introuvable.');
        }

        $form = $this->createForm(RapportType::class, $rapport, ['is_mod' => true]);
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

            $dateRdv = $form->get('idrendezvous')->getData();
            $rdv = $em->getRepository(Rendezvous::class)->findOneBy(['date' => new \DateTime($dateRdv)]);
            if (!$rdv) {
                $rdv = new Rendezvous();
                $rdv->setDate(new \DateTime($dateRdv));
                $rdv->setSpecialite('');
                $em->persist($rdv);
            }
            $rapport->setIdrendezvous($rdv);

            $nomMedecin = $form->get('idmedecin')->getData();
            $medecin = $em->getRepository(Medecin::class)->findOneBy(['nom' => $nomMedecin]);
            if (!$medecin) {
                $medecin = new Medecin();
                $medecin->setNom($nomMedecin);
                $medecin->setSpecialite('');
                $em->persist($medecin);
            }
            $rapport->setIdmedecin($medecin);

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
            $rapport->setIdpatient($patient);

            $rapport->setUpdatedAt(new \DateTime());
            $em->flush();

            return $this->redirectToRoute('rapport_mod', [
                'idrendezvous' => $rapport->getIdrendezvous()->getIdrendezvous(),
                'idmedecin' => $rapport->getIdmedecin()->getIdmedecin(),
                'idpatient' => $rapport->getIdpatient()->getIdpatient(),
                'idrapport' => $rapport->getIdrapport(),
            ]);
        }

        return $this->render('rapport/mod.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $rendezvous,
            'medecins' => $medecins,
            'patients' => $patients,
        ]);
    }

    #[Route('/rapport/delete/{idrendezvous}/{idmedecin}/{idpatient}/{idrapport}', name: 'rapport_delete')]
public function delete(Request $request, EntityManagerInterface $em, int $idrapport): Response
{
    $rapport = $em->getRepository(Rapport::class)->find($idrapport);

    if (!$rapport) {
        throw $this->createNotFoundException('Rapport introuvable.');
    }

    $em->remove($rapport);
    $em->flush();

    $this->addFlash('success', 'Rapport supprimé avec succès.');

    // ✅ RESTER SUR LA MÊME PAGE
    return $this->redirect($request->headers->get('referer'));
}


    #[Route('/rapport/show/{idrapport}', name: 'rapport_show')]
public function show(EntityManagerInterface $em, int $idrapport): Response
{
    $rapport = $em->getRepository(Rapport::class)->find($idrapport);

    if (!$rapport) {
        throw $this->createNotFoundException('Rapport introuvable');
    }

    return $this->render('rapport/show.html.twig', [
        'rapport' => $rapport,
    ]);
}


}