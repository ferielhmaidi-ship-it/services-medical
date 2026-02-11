<?php

namespace App\Controller;

use App\Entity\Magazine;
use App\Repository\ArticleRepository;
use App\Repository\MagazineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    #[Route('/magazines', name: 'front_magazine_index')]
    public function magazines(Request $request, MagazineRepository $repo): Response
    {
        $searchTerm = $request->query->get('search');
        
        if ($searchTerm) {
            $magazines = $repo->findBySearch($searchTerm);
        } else {
            $magazines = $repo->findAll();
        }

        return $this->render('patient/magazines.html.twig', [
            'magazines' => $magazines,
            'searchTerm' => $searchTerm
        ]);
    }

    #[Route('/magazine/{id}', name: 'front_magazine_show')]
    public function show(Request $request, Magazine $magazine, ArticleRepository $articleRepo): Response
    {
        $searchTerm = $request->query->get('search');
        
        if ($searchTerm) {
            $articles = $articleRepo->findByMagazineAndSearch($magazine->getId(), $searchTerm);
        } else {
            $articles = $magazine->getArticles();
        }

        return $this->render('patient/magazine_show.html.twig', [
            'magazine' => $magazine,
            'articles' => $articles,
            'searchTerm' => $searchTerm
        ]);
    }

    #[Route('/articles/search', name: 'front_articles_search')]
    public function articlesSearch(Request $request, ArticleRepository $articleRepo): Response
    {
        $searchTerm = $request->query->get('search', '');

        $articles = [];
        if ($searchTerm) {
            $articles = $articleRepo->findByGlobalSearch($searchTerm);
        }

        return $this->render('patient/articles_search.html.twig', [
            'articles' => $articles,
            'searchTerm' => $searchTerm
        ]);
    }

    #[Route('/api/magazines/autocomplete', name: 'api_magazines_autocomplete')]
    public function magazinesAutocomplete(Request $request, MagazineRepository $repo): JsonResponse
    {
        $term = $request->query->get('q', '');
        
        if (strlen($term) < 2) {
            return new JsonResponse([]);
        }

        $magazines = $repo->findBySearch($term);
        
        $results = array_map(function($magazine) {
            return [
                'id' => $magazine->getId(),
                'title' => $magazine->getTitle(),
                'description' => substr($magazine->getDescription() ?? '', 0, 80),
                'url' => '/magazine/' . $magazine->getId()
            ];
        }, array_slice($magazines, 0, 5)); // Limite à 5 résultats

        return new JsonResponse($results);
    }

    #[Route('/api/articles/autocomplete', name: 'api_articles_autocomplete')]
    public function articlesAutocomplete(Request $request, ArticleRepository $repo): JsonResponse
    {
        $term = $request->query->get('q', '');
        
        if (strlen($term) < 2) {
            return new JsonResponse([]);
        }

        $articles = $repo->findByGlobalSearch($term);
        
        $results = array_map(function($article) {
            return [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'auteur' => $article->getAuteur(),
                'magazine' => $article->getMagazine()->getTitle(),
                'date' => $article->getDatePub()?->format('d/m/Y')
            ];
        }, array_slice($articles, 0, 8)); // Limite à 8 résultats

        return new JsonResponse($results);
    }
}
