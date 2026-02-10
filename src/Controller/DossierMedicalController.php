<?php

namespace App\Controller;

use App\Entity\Rapport;
use App\Entity\Ordonnance;
use App\Entity\Document;
use App\Entity\Patient;
use App\Entity\Medecin;
use App\Form\DocumentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DossierMedicalController extends AbstractController
{
    #[Route('/rapportsordonnances/{idpatient}/{idmedecin}', name: 'rapports_ordonnances')]
    public function index(
        int $idpatient,
        int $idmedecin,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $patient = $em->getRepository(Patient::class)->find($idpatient);
        $medecin = $em->getRepository(Medecin::class)->find($idmedecin);

        if (!$patient || !$medecin) {
            throw $this->createNotFoundException();
        }

        $rapports = $em->getRepository(Rapport::class)->findBy([
            'idpatient' => $patient,
            'idmedecin' => $medecin
        ]);

        $ordonnances = $em->getRepository(Ordonnance::class)->findBy([
            'idpatient' => $patient,
            'idmedecin' => $medecin
        ]);

        $document = $em->getRepository(Document::class)->findOneBy([
            'idpatient' => $patient,
            'idmedecin' => $medecin
        ]);

        // ===================== CAS 1 : DOCUMENT EXISTE =====================
        if ($document) {
            $updated = false;

            foreach ($rapports as $r) {
                if ($r->getIddocument() === null) {
                    $r->setIddocument($document);
                    $updated = true;
                }
            }

            foreach ($ordonnances as $o) {
                if ($o->getIddocument() === null) {
                    $o->setIddocument($document);
                    $updated = true;
                }
            }

            if ($updated) {
                $em->flush();
            }

            return $this->render('dossier_medical/dossier_medical.html.twig', [
                'rapports' => $rapports,
                'ordonnances' => $ordonnances,
                'showCreateButton' => false,
                'form' => null,
                'idpatient' => $idpatient,
                'idmedecin' => $idmedecin,
            ]);
        }

        // ===================== CAS 2 : PAS DE DOCUMENT =====================
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $document->setIdpatient($patient);
            $document->setIdmedecin($medecin);
            $document->setCreatedAt(new \DateTime());
            $document->setUpdatedAt(new \DateTime());

            $em->persist($document);
            $em->flush();

            foreach ($rapports as $r) {
                if ($r->getIddocument() === null) {
                    $r->setIddocument($document);
                }
            }

            foreach ($ordonnances as $o) {
                if ($o->getIddocument() === null) {
                    $o->setIddocument($document);
                }
            }

            $em->flush();

            return $this->redirectToRoute('rapports_ordonnances', [
                'idpatient' => $idpatient,
                'idmedecin' => $idmedecin,
            ]);
        }

        return $this->render('dossier_medical/dossier_medical.html.twig', [
            'rapports' => $rapports,
            'ordonnances' => $ordonnances,
            'showCreateButton' => true,
            'form' => $form->createView(),
            'idpatient' => $idpatient,
            'idmedecin' => $idmedecin,
        ]);
    }






// Edit document
#[Route('/dossiermedical/mod/{iddocument}', name: 'edit_document')]
public function editDocument(
    int $iddocument,
    Request $request,
    EntityManagerInterface $em
): Response {
    $document = $em->getRepository(Document::class)->find($iddocument);

    if (!$document) {
        throw $this->createNotFoundException('Document introuvable');
    }

    $form = $this->createForm(DocumentType::class, $document, ['is_edit' => true]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $document->setUpdatedAt(new \DateTime());
        $em->flush();

        return $this->redirectToRoute('pages_dossier_medical');
    }

    return $this->render('dossier_medical/moddossier_medical.html.twig', [
        'form' => $form->createView(),
        'document' => $document,
    ]);
}

// Delete document
#[Route('/dossiermedical/del/{iddocument}', name: 'delete_document')]
public function deleteDocument(
    int $iddocument,
    EntityManagerInterface $em
): Response {
    $document = $em->getRepository(Document::class)->find($iddocument);

    if ($document) {
        $em->remove($document);
        $em->flush();
        $this->addFlash('success', 'Le document a été supprimé avec succès.');
    } else {
        $this->addFlash('warning', 'Document introuvable.');
    }

    return $this->redirectToRoute('pages_dossier_medical');
}














    #[Route('/dossiermedical/show/{iddocument}', name: 'document_show')]
public function showDocument(int $iddocument, EntityManagerInterface $em): Response
{
    $document = $em->getRepository(Document::class)->find($iddocument);

    if (!$document) {
        throw $this->createNotFoundException('Document introuvable');
    }

    // On récupère les ids du patient et médecin pour le bouton "Retour"
    $idpatient = $document->getIdpatient()->getIdpatient();
    $idmedecin = $document->getIdmedecin()->getIdmedecin();

    return $this->render('dossier_medical/show_document.html.twig', [
        'document' => $document,
        'idpatient' => $idpatient,
        'idmedecin' => $idmedecin,
    ]);
}

    #[Route('/dossier_medical', name: 'pages_dossier_medical')]
    public function tableauDossierMedical(EntityManagerInterface $em): Response
    {
        $documents = $em->getRepository(Document::class)->findAll();

        $data = [];
        foreach ($documents as $doc) {
            $rapports = $em->getRepository(Rapport::class)->findBy(['iddocument' => $doc]);
            $ordonnances = $em->getRepository(Ordonnance::class)->findBy(['iddocument' => $doc]);

            $data[] = [
                'document' => $doc,
                'rapports' => $rapports,
                'ordonnances' => $ordonnances
            ];
        }

        return $this->render('dossier_medical/tableau.html.twig', [
            'data' => $data
        ]);
    }
}