<?php
// src/Controller/AnnuaireController.php

namespace App\Controller;

use App\Service\AnnuaireService;
use App\Constants\Specialty;
use App\Constants\Governorate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class AnnuaireController extends AbstractController
{
    #[Route('/annuaire', name: 'app_annuaire')]
    public function index(Request $request, AnnuaireService $annuaireService): Response
    {
        $name = $request->query->get('name', '');
        $specialty = $request->query->get('specialty', '');
        $governorate = $request->query->get('governorate', '');
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('per_page', 20);

        $searchCriteria = [];
        if ($name) $searchCriteria['name'] = $name;
        if ($specialty) $searchCriteria['specialty'] = $specialty;
        if ($governorate) $searchCriteria['governorate'] = $governorate;

        $searchResult = $annuaireService->searchDoctors($searchCriteria, $page, $perPage);

        // Always set default pagination
        $pagination = [
            'currentPage' => $page,
            'pageSize' => $perPage,
            'totalItems' => 0,
            'totalPages' => 0,
        ];

        if ($searchResult['success']) {
            $doctors = $searchResult['doctors'];
            $pagination = $searchResult['pagination'];
        } else {
            $doctors = [];
        }

        // Get API status
        $apiStatus = $annuaireService->getApiStatus();

        // Get specialties and governorates from Constants
        $specialties = Specialty::getChoices();
        $governorates = Governorate::getChoices();

        return $this->render('annuaire/index.html.twig', [
            'name' => $name,
            'specialty' => $specialty,
            'governorate' => $governorate,
            'searchResult' => $searchResult,
            'doctors' => $doctors,
            'pagination' => $pagination,
            'specialties' => $specialties,
            'governorates' => $governorates,
            'apiStatus' => $apiStatus,
            'specialtyGroups' => Specialty::getGroups(),
            'currentPage' => $page,
        ]);
    }

    #[Route('/annuaire/api-test', name: 'app_annuaire_api_test')]
    public function apiTest(AnnuaireService $annuaireService): JsonResponse
    {
        try {
            $status = $annuaireService->getApiStatus();
            return $this->json([
                'success' => true,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
