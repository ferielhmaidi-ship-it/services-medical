<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Appointment;
use App\Entity\Medecin;
use App\Entity\Ordonnance;
use App\Entity\Patient;
use App\Entity\Rapport;
use App\Form\DocumentType;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MEDECIN')]
class DossierMedicalController extends AbstractController
{
    #[Route('/rapportsordonnances/{idpatient}/{idmedecin}', name: 'rapports_ordonnances')]
    public function index(int $idpatient, int $idmedecin, Request $request, EntityManagerInterface $em): Response
    {
        $currentMedecin = $this->getAuthenticatedMedecin();
        if ($currentMedecin->getId() !== $idmedecin) {
            throw $this->createAccessDeniedException('Vous ne pouvez consulter que vos propres dossiers patients.');
        }

        $patient = $em->getRepository(Patient::class)->find($idpatient);
        $medecin = $em->getRepository(Medecin::class)->find($idmedecin);
        $idRendezVous = $request->query->getInt('idrendezvous', 0);
        $selectedAppointment = null;

        if (!$patient || !$medecin) {
            throw $this->createNotFoundException();
        }

        if ($idRendezVous > 0) {
            $selectedAppointment = $em->getRepository(Appointment::class)->find($idRendezVous);

            if (
                !$selectedAppointment
                || $selectedAppointment->getPatient()->getId() !== $idpatient
                || $selectedAppointment->getDoctor()->getId() !== $idmedecin
            ) {
                $this->addFlash('warning', 'Rendez-vous introuvable pour ce patient. Affichage global du dossier medical.');
                $idRendezVous = 0;
            }
        }

        $criteria = [
            'patient' => $patient,
            'medecin' => $medecin,
        ];

        if ($idRendezVous > 0 && $selectedAppointment) {
            $criteria['appointment'] = $selectedAppointment;
        }

        $rapports = $em->getRepository(Rapport::class)->findBy($criteria);
        $ordonnances = $em->getRepository(Ordonnance::class)->findBy($criteria);

        $request->getSession()->set('dossier_medical_selection', [
            'idpatient' => $idpatient,
            'idmedecin' => $idmedecin,
            'idrendezvous' => $idRendezVous > 0 ? $idRendezVous : null,
        ]);

        $document = $em->getRepository(Document::class)->findOneBy([
            'patient' => $patient,
            'medecin' => $medecin,
        ]);

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
                'idrendezvous' => $idRendezVous,
            ]);
        }

        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document->setPatient($patient);
            $document->setMedecin($medecin);

            if (!$document->getChemin()) {
                $document->setChemin('sans_fichier');
            }

            $document->setCreatedAt(new \DateTimeImmutable());
            $document->setUpdatedAt(new \DateTimeImmutable());

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
                'idrendezvous' => $idRendezVous,
            ]);
        }

        return $this->render('dossier_medical/dossier_medical.html.twig', [
            'rapports' => $rapports,
            'ordonnances' => $ordonnances,
            'showCreateButton' => true,
            'form' => $form->createView(),
            'idpatient' => $idpatient,
            'idmedecin' => $idmedecin,
            'idrendezvous' => $idRendezVous,
        ]);
    }

    #[Route('/dossiermedical/mod/{iddocument}', name: 'edit_document')]
    public function editDocument(int $iddocument, Request $request, EntityManagerInterface $em): Response
    {
        $medecin = $this->getAuthenticatedMedecin();
        $document = $em->getRepository(Document::class)->find($iddocument);

        if (!$document) {
            throw $this->createNotFoundException('Document introuvable');
        }

        $this->assertDocumentOwner($document, $medecin);

        $form = $this->createForm(DocumentType::class, $document, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->redirectToRoute('pages_dossier_medical');
        }

        return $this->render('dossier_medical/moddossier_medical.html.twig', [
            'form' => $form->createView(),
            'document' => $document,
        ]);
    }

    #[Route('/dossiermedical/del/{iddocument}', name: 'delete_document')]
    public function deleteDocument(int $iddocument, EntityManagerInterface $em): Response
    {
        $medecin = $this->getAuthenticatedMedecin();
        $document = $em->getRepository(Document::class)->find($iddocument);

        if ($document) {
            $this->assertDocumentOwner($document, $medecin);
            $em->remove($document);
            $em->flush();
            $this->addFlash('success', 'Le document a ete supprime avec succes.');
        } else {
            $this->addFlash('warning', 'Document introuvable.');
        }

        return $this->redirectToRoute('pages_dossier_medical');
    }

    #[Route('/dossiermedical/show/{iddocument}', name: 'document_show')]
    public function showDocument(int $iddocument, EntityManagerInterface $em): Response
    {
        $medecin = $this->getAuthenticatedMedecin();
        $document = $em->getRepository(Document::class)->find($iddocument);

        if (!$document) {
            throw $this->createNotFoundException('Document introuvable');
        }

        $this->assertDocumentOwner($document, $medecin);

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
        $medecin = $this->getAuthenticatedMedecin();
        $document = $em->getRepository(Document::class)->find($iddocument);

        if (!$document) {
            throw $this->createNotFoundException('Document introuvable');
        }

        $this->assertDocumentOwner($document, $medecin);

        $rapports = $em->getRepository(Rapport::class)->findBy(['document' => $document]);
        $ordonnances = $em->getRepository(Ordonnance::class)->findBy(['document' => $document]);

        $html = $this->renderView('dossier_medical/document_pdf.html.twig', [
            'document' => $document,
            'rapports' => $rapports,
            'ordonnances' => $ordonnances,
        ]);

        if (!class_exists(Dompdf::class) || !class_exists(Options::class)) {
            return new Response(
                'Generation PDF indisponible: la librairie dompdf n\'est pas installee.',
                Response::HTTP_SERVICE_UNAVAILABLE,
                ['Content-Type' => 'text/plain; charset=UTF-8']
            );
        }

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->setDefaultFont('Helvetica');

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
    public function tableauDossierMedical(Request $request, EntityManagerInterface $em): Response
    {
        $medecin = $this->getAuthenticatedMedecin();

        $document = new Document();
        $documentForm = $this->createForm(DocumentType::class, $document);
        $documentForm->handleRequest($request);

        if ($documentForm->isSubmitted() && $documentForm->isValid()) {
            $selection = $request->getSession()->get('dossier_medical_selection', []);
            $idpatient = $selection['idpatient'] ?? null;
            $idmedecin = $selection['idmedecin'] ?? null;

            if (!$idpatient || !$idmedecin) {
                $this->addFlash('error', 'Veuillez d\'abord ouvrir un dossier medical (patient + medecin) avant d\'ajouter un document.');
            } elseif ((int) $idmedecin !== (int) $medecin->getId()) {
                $this->addFlash('error', 'Selection invalide: ce dossier n\'appartient pas au medecin connecte.');
            } else {
                $patient = $em->getRepository(Patient::class)->find($idpatient);

                if (!$patient) {
                    $this->addFlash('error', 'Patient introuvable pour la creation du document.');
                } else {
                    $document->setPatient($patient);
                    $document->setMedecin($medecin);

                    if (!$document->getChemin()) {
                        $document->setChemin('sans_fichier');
                    }

                    $document->setCreatedAt(new \DateTimeImmutable());
                    $document->setUpdatedAt(new \DateTimeImmutable());

                    $em->persist($document);
                    $em->flush();

                    return $this->redirectToRoute('pages_dossier_medical');
                }
            }
        }

        $documents = $em->getRepository(Document::class)->findBy(['medecin' => $medecin]);

        $data = [];
        foreach ($documents as $doc) {
            $rapports = $em->getRepository(Rapport::class)->findBy(['document' => $doc]);
            $ordonnances = $em->getRepository(Ordonnance::class)->findBy(['document' => $doc]);

            $data[] = [
                'document' => $doc,
                'rapports' => $rapports,
                'ordonnances' => $ordonnances,
            ];
        }

        return $this->render('dossier_medical/tableau.html.twig', [
            'data' => $data,
            'documentForm' => $documentForm->createView(),
            'openDocumentModal' => $documentForm->isSubmitted(),
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

    private function assertDocumentOwner(Document $document, Medecin $medecin): void
    {
        if ($document->getMedecin()?->getId() !== $medecin->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez acceder qu\'a vos propres documents medicaux.');
        }
    }
}
