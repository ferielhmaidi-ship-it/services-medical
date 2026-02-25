<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class FrontController extends AbstractController
{
    // =========================
    // LIST MAGAZINES
    // =========================
    #[Route('/magazines', name: 'front_magazine_index')]
    public function magazines(Request $request, MagazineRepository $repo): Response
    {
        $searchTerm = $request->query->get('search');

        if ($searchTerm) {
            $magazines = $repo->createQueryBuilder('m')
                ->where('m.title LIKE :search')
                ->setParameter('search', '%'.$searchTerm.'%')
                ->getQuery()
                ->getResult();
        } else {
            $magazines = $repo->findAll();
        }

        return $this->render('magazine-patient/magazines.html.twig', [
            'magazines' => $magazines,
            'searchTerm' => $searchTerm
        ]);
    }


    // =========================
    // SHOW ONE MAGAZINE
    // =========================
    #[Route('/magazine/{id}', name: 'front_magazine_show')]
    public function show(Request $request, Magazine $magazine, ArticleRepository $articleRepo): Response
    {
        $searchTerm = $request->query->get('search');

        if ($searchTerm) {
            $articles = $articleRepo->createQueryBuilder('a')
                ->where('a.magazine = :mag')
                ->andWhere('a.title LIKE :search')
                ->setParameter('mag', $magazine)
                ->setParameter('search', '%'.$searchTerm.'%')
                ->orderBy('a.id', 'ASC')
                ->getQuery()
                ->getResult();
        } else {
            $articles = $articleRepo->createQueryBuilder('a')
                ->where('a.magazine = :mag')
                ->setParameter('mag', $magazine)
                ->orderBy('a.id', 'ASC')
                ->getQuery()
                ->getResult();
        }

        return $this->render('magazine-patient/magazine_show.html.twig', [
            'magazine' => $magazine,
            'articles' => $articles,
            'searchTerm' => $searchTerm
        ]);
    }


    // =========================
    // SHOW ARTICLE + PREV / NEXT
    // =========================
    #[Route('/magazine/{magazineId}/article/{id}', name: 'front_article_show', methods: ['GET'])]
    public function articleShow(
        int $magazineId,
        Article $article,
        ArticleRepository $articleRepo
    ): Response
    {
        // Security check
        if ($article->getMagazine()->getId() !== $magazineId) {
            throw $this->createNotFoundException('Article not found in this magazine');
        }

        $previous = $articleRepo->findPreviousInMagazine($magazineId, $article->getId());
        $next = $articleRepo->findNextInMagazine($magazineId, $article->getId());

        return $this->render('magazine-patient/article_show.html.twig', [
            'article' => $article,
            'previous' => $previous,
            'next' => $next,
        ]);
    }


    // =========================
    // GLOBAL ARTICLE SEARCH
    // =========================
    #[Route('/articles/search', name: 'front_articles_search')]
    public function articlesSearch(Request $request, ArticleRepository $repo): Response
    {
        $searchTerm = $request->query->get('search', '');

        if ($searchTerm) {
            $articles = $repo->createQueryBuilder('a')
                ->where('LOWER(a.title) LIKE LOWER(:search)')
                ->setParameter('search', '%' . trim($searchTerm) . '%')
                ->orderBy('a.id', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $articles = [];
        }

        return $this->render('magazine-patient/articles_search.html.twig', [
            'articles' => $articles,
            'searchTerm' => $searchTerm
        ]);
    }


    // =========================
    // AUTOCOMPLETE
    // =========================
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
                'url' => '/magazine/'.$magazine->getId()
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
