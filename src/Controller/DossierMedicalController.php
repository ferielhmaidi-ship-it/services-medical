<?php

namespace App\Controller;

use App\Entity\Rapport;
use App\Entity\Ordonnance;
use App\Entity\Document;
use App\Entity\Patient;
use App\Entity\Medecin;
use App\Form\DocumentType;
use Dompdf\Dompdf;
use Dompdf\Options;
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
            'patient' => $patient,
            'medecin' => $medecin
        ]);

        $ordonnances = $em->getRepository(Ordonnance::class)->findBy([
            'patient' => $patient,
            'medecin' => $medecin
        ]);

        $document = $em->getRepository(Document::class)->findOneBy([
            'patient' => $patient,
            'medecin' => $medecin
        ]);

        // ===================== CAS 1 : DOCUMENT EXISTE =====================
        if ($document) {
            $updated = false;

            foreach ($rapports as $r) {
                if ($r->getDocument() === null) {
                    $r->setDocument($document);
                    $updated = true;
                }
            }

            foreach ($ordonnances as $o) {
                if ($o->getDocument() === null) {
                    $o->setDocument($document);
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

            $document->setPatient($patient);
            $document->setMedecin($medecin);
            // Le champ fichier peut être masqué dans l'UI ; on garde une valeur non nulle pour la BDD.
            if (!$document->getChemin()) {
                $document->setChemin('sans_fichier');
            }
            $document->setCreatedAt(new \DateTime());
            $document->setUpdatedAt(new \DateTime());

            $em->persist($document);
            $em->flush();

            foreach ($rapports as $r) {
                if ($r->getDocument() === null) {
                    $r->setDocument($document);
                }
            }

            foreach ($ordonnances as $o) {
                if ($o->getDocument() === null) {
                    $o->setDocument($document);
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
    $idpatient = $document->getPatient()?->getId();
    $idmedecin = $document->getMedecin()?->getId();

    return $this->render('dossier_medical/show_document.html.twig', [
        'document' => $document,
        'idpatient' => $idpatient,
        'idmedecin' => $idmedecin,
    ]);
}

    #[Route('/dossiermedical/pdf/{iddocument}', name: 'document_pdf')]
    public function documentPdf(int $iddocument, EntityManagerInterface $em): Response
    {
        $document = $em->getRepository(Document::class)->find($iddocument);

        if (!$document) {
            throw $this->createNotFoundException('Document introuvable');
        }

        $rapports = $em->getRepository(Rapport::class)->findBy(['document' => $document]);
        $ordonnances = $em->getRepository(Ordonnance::class)->findBy(['document' => $document]);

        $html = $this->renderView('dossier_medical/document_pdf.html.twig', [
            'document' => $document,
            'rapports' => $rapports,
            'ordonnances' => $ordonnances,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->setDefaultFont('DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $document->getNom());
        $filename = sprintf('document_%d_%s.pdf', $document->getId(), $safeName ?: 'medical');

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            ]
        );
    }

    #[Route('/dossier_medical', name: 'pages_dossier_medical')]
    public function tableauDossierMedical(EntityManagerInterface $em): Response
    {
        $documents = $em->getRepository(Document::class)->findAll();

        $data = [];
        foreach ($documents as $doc) {
            $rapports = $em->getRepository(Rapport::class)->findBy(['document' => $doc]);
            $ordonnances = $em->getRepository(Ordonnance::class)->findBy(['document' => $doc]);

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
