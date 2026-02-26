<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Magazine;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\SummaryService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/article')]
final class ArticleController extends AbstractController
{
    private const ALLOWED_SORT_FIELDS = [
        'title' => 'a.title',
        'date' => 'a.datePub',
        'views' => 'a.views',
        'status' => 'a.statut',
    ];

    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(
        Request $request,
        ArticleRepository $articleRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = $request->query->getInt('limit', 10);
        $limit = in_array($limit, [5, 10], true) ? $limit : 10;
        $searchQuery = trim((string) $request->query->get('q', ''));
        [$sort, $direction, $sortField] = $this->resolveSort($request, 'date', 'desc');

        $qb = $articleRepository->createQueryBuilder('a')
            ->leftJoin('a.magazine', 'm')
            ->addSelect('m');

        if ($searchQuery !== '') {
            $qb
                ->andWhere('LOWER(a.title) LIKE :search OR LOWER(a.resume) LIKE :search OR LOWER(a.auteur) LIKE :search OR LOWER(a.statut) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($searchQuery) . '%');
        }

        $query = $qb
            ->orderBy($sortField, strtoupper($direction))
            ->addOrderBy('a.id', 'DESC')
            ->getQuery();

        $articles = $paginator->paginate($query, $page, $limit, [
            'sortFieldParameterName' => 'knp_sort',
            'sortDirectionParameterName' => 'knp_direction',
        ]);

        return $this->render('magazine-admin/article/index.html.twig', [
            'articles' => $articles,
            'limit' => $limit,
            'isTopMode' => false,
            'sort' => $sort,
            'direction' => $direction,
            'searchQuery' => $searchQuery,
            'currentRoute' => 'app_article_index',
            'currentRouteParams' => [],
        ]);
    }

    #[Route('/magazine/{id}', name: 'app_article_by_magazine', methods: ['GET'])]
    public function byMagazine(
        Request $request,
        Magazine $magazine,
        ArticleRepository $articleRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = $request->query->getInt('limit', 10);
        $limit = in_array($limit, [5, 10], true) ? $limit : 10;
        $searchQuery = trim((string) $request->query->get('q', ''));
        [$sort, $direction, $sortField] = $this->resolveSort($request, 'date', 'desc');

        $qb = $articleRepository->createQueryBuilder('a')
            ->where('a.magazine = :mag')
            ->setParameter('mag', $magazine);

        if ($searchQuery !== '') {
            $qb
                ->andWhere('LOWER(a.title) LIKE :search OR LOWER(a.resume) LIKE :search OR LOWER(a.auteur) LIKE :search OR LOWER(a.statut) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($searchQuery) . '%');
        }

        $query = $qb
            ->orderBy($sortField, strtoupper($direction))
            ->addOrderBy('a.id', 'DESC')
            ->getQuery();

        $articles = $paginator->paginate($query, $page, $limit, [
            'sortFieldParameterName' => 'knp_sort',
            'sortDirectionParameterName' => 'knp_direction',
        ]);

        return $this->render('magazine-admin/article/index.html.twig', [
            'articles' => $articles,
            'magazine' => $magazine,
            'limit' => $limit,
            'isTopMode' => false,
            'sort' => $sort,
            'direction' => $direction,
            'searchQuery' => $searchQuery,
            'currentRoute' => 'app_article_by_magazine',
            'currentRouteParams' => ['id' => $magazine->getId()],
        ]);
    }

    #[Route('/top', name: 'app_article_top', methods: ['GET'])]
    public function topArticles(
        Request $request,
        ArticleRepository $articleRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = $request->query->getInt('limit', 10);
        $limit = in_array($limit, [5, 10], true) ? $limit : 10;
        $searchQuery = trim((string) $request->query->get('q', ''));
        [$sort, $direction, $sortField] = $this->resolveSort($request, 'views', 'desc');

        $qb = $articleRepository->createQueryBuilder('a')
            ->leftJoin('a.magazine', 'm')
            ->addSelect('m');

        if ($searchQuery !== '') {
            $qb
                ->andWhere('LOWER(a.title) LIKE :search OR LOWER(a.resume) LIKE :search OR LOWER(a.auteur) LIKE :search OR LOWER(a.statut) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($searchQuery) . '%');
        }

        $query = $qb
            ->orderBy($sortField, strtoupper($direction))
            ->addOrderBy('a.id', 'DESC')
            ->getQuery();

        $articles = $paginator->paginate($query, $page, $limit, [
            'sortFieldParameterName' => 'knp_sort',
            'sortDirectionParameterName' => 'knp_direction',
        ]);

        return $this->render('magazine-admin/article/index.html.twig', [
            'articles' => $articles,
            'limit' => $limit,
            'isTopMode' => true,
            'sort' => $sort,
            'direction' => $direction,
            'searchQuery' => $searchQuery,
            'currentRoute' => 'app_article_top',
            'currentRouteParams' => [],
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index');
        }

        return $this->render('magazine-admin/article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('magazine-admin/article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Article $article,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index');
        }

        return $this->render('magazine-admin/article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Article $article,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index');
    }

    #[Route('/{id}/generate-summary', name: 'app_article_generate_summary', methods: ['POST'])]
    public function generateSummary(
        Article $article,
        EntityManagerInterface $entityManager,
        SummaryService $summaryService
    ): JsonResponse {
        try {
            $summary = $summaryService->summarize((string) $article->getResume(), 'article');
            $article->setSummary($summary);
            $entityManager->flush();

            return new JsonResponse(['summary' => $summary]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 502);
        }
    }

    private function resolveSort(Request $request, string $defaultSort, string $defaultDirection): array
    {
        $sort = strtolower((string) $request->query->get('order_by', $request->query->get('sort', $defaultSort)));
        $direction = strtolower((string) $request->query->get('order_dir', $request->query->get('direction', $defaultDirection)));

        if (!array_key_exists($sort, self::ALLOWED_SORT_FIELDS)) {
            $sort = $defaultSort;
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = $defaultDirection;
        }

        return [$sort, $direction, self::ALLOWED_SORT_FIELDS[$sort]];
    }
}
