<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Medecin;

#[Route('/dossier-medical', name: 'app_medical_record_')]
#[IsGranted('ROLE_MEDECIN')]
final class MedicalRecordController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('medical_record/index.html.twig');
    }

    #[Route('/documents', name: 'documents', methods: ['GET'])]
    public function documents(): Response
    {
        return $this->redirectToRoute('pages_dossier_medical');
    }

    #[Route('/ordonnances-rapports', name: 'prescriptions_reports', methods: ['GET'])]
    public function prescriptionsReports(Request $request): Response
    {
        $user = $this->getUser();
        $medecinId = $user instanceof Medecin ? $user->getId() : 0;
        $selection = $request->getSession()->get('dossier_medical_selection');

        if (
            is_array($selection)
            && isset($selection['idpatient'], $selection['idmedecin'])
            && (int) $selection['idmedecin'] === (int) $medecinId
        ) {
            $query = [];
            if (!empty($selection['idrendezvous'])) {
                $query['idrendezvous'] = (int) $selection['idrendezvous'];
            }

            return $this->redirectToRoute('rapports_ordonnances', [
                'idpatient' => (int) $selection['idpatient'],
                'idmedecin' => (int) $selection['idmedecin'],
            ] + $query);
        }

        return $this->render('dossier_medical/dossier_medical.html.twig', [
            'rapports' => [],
            'ordonnances' => [],
            'showCreateButton' => false,
            'form' => null,
            'idpatient' => 0,
            'idmedecin' => $medecinId ?? 0,
            'idrendezvous' => null,
        ]);
    }

    #[Route('/assistance-ia', name: 'ai_assistance', methods: ['GET'])]
    public function aiAssistance(): Response
    {
        return $this->redirectToRoute('ia_db');
    }
}
