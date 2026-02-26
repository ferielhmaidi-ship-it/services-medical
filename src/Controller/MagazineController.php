<?php

namespace App\Controller;

use App\Entity\Magazine;
use App\Form\MagazineType;
use App\Repository\MagazineRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/magazine')]
final class MagazineController extends AbstractController
{
    private const ALLOWED_SORT_FIELDS = [
        'title' => 'm.title',
        'date' => 'm.dateCreate',
        'status' => 'm.statut',
        'views' => 'totalViews',
    ];

    #[Route(name: 'app_magazine_index', methods: ['GET'])]
    public function index(
        Request $request,
        MagazineRepository $magazineRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = $request->query->getInt('limit', 10);
        $limit = in_array($limit, [5, 10], true) ? $limit : 10;
        $searchQuery = trim((string) $request->query->get('q', ''));
        [$sort, $direction, $sortField] = $this->resolveSort($request, 'date', 'desc');

        $qb = $magazineRepository->createQueryBuilder('m')
            ->leftJoin('m.articles', 'a')
            ->addSelect('COALESCE(SUM(a.views), 0) AS HIDDEN totalViews')
            ->groupBy('m.id');

        if ($searchQuery !== '') {
            $qb
                ->andWhere('LOWER(m.title) LIKE :search OR LOWER(m.description) LIKE :search OR LOWER(m.statut) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($searchQuery) . '%');
        }

        $query = $qb
            ->orderBy($sortField, strtoupper($direction))
            ->addOrderBy('m.id', 'DESC')
            ->getQuery();

        $magazines = $paginator->paginate($query, $page, $limit, [
            'sortFieldParameterName' => 'knp_sort',
            'sortDirectionParameterName' => 'knp_direction',
        ]);
        [$articleCounts, $totalViewsByMagazine] = $this->buildArticleStatsMaps($magazines, $magazineRepository);

        return $this->render('magazine-admin/magazine/index.html.twig', [
            'magazines' => $magazines,
            'limit' => $limit,
            'sort' => $sort,
            'direction' => $direction,
            'searchQuery' => $searchQuery,
            'articleCounts' => $articleCounts,
            'totalViewsByMagazine' => $totalViewsByMagazine,
        ]);
    }

    #[Route('/new', name: 'app_magazine_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response
    {
        $magazine = new Magazine();
        $form = $this->createForm(MagazineType::class, $magazine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Upload image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageName = $fileUploader->upload($imageFile);
                $magazine->setImage($imageName);
            }

            // Upload PDF
            $pdfFile = $form->get('pdfFileUpload')->getData();
            if ($pdfFile) {
                $pdfName = $fileUploader->upload($pdfFile);
                $magazine->setPdfFile($pdfName);
            }

            $entityManager->persist($magazine);
            $entityManager->flush();

            $this->addFlash('success', 'Le magazine a été créé avec succès.');
            return $this->redirectToRoute('app_magazine_index');
        }

        return $this->render('magazine-admin/magazine/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_magazine_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Magazine $magazine,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response
    {
        $form = $this->createForm(MagazineType::class, $magazine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Upload image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageName = $fileUploader->upload($imageFile);
                $magazine->setImage($imageName);
            }

            // Upload PDF
            $pdfFile = $form->get('pdfFileUpload')->getData();
            if ($pdfFile) {
                $pdfName = $fileUploader->upload($pdfFile);
                $magazine->setPdfFile($pdfName);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le magazine a été mis à jour avec succès.');
            return $this->redirectToRoute('app_magazine_index');
        }

        return $this->render('magazine-admin/magazine/edit.html.twig', [
            'form' => $form,
            'magazine' => $magazine,
        ]);
    }

    #[Route('/{id}', name: 'app_magazine_show', methods: ['GET'])]
    public function show(Magazine $magazine): Response
    {
        return $this->render('magazine-admin/magazine/show.html.twig', [
            'magazine' => $magazine,
        ]);
    }

    #[Route('/{id}', name: 'app_magazine_delete', methods: ['POST'])]
    public function delete(Request $request, Magazine $magazine, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$magazine->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($magazine);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_magazine_index');
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

    private function buildArticleStatsMaps($magazines, MagazineRepository $magazineRepository): array
    {
        $magazineIds = [];
        foreach ($magazines as $magazine) {
            $magazineIds[] = $magazine->getId();
        }

        if ($magazineIds === []) {
            return [[], []];
        }

        $rows = $magazineRepository->createQueryBuilder('m')
            ->select('m.id AS id, COUNT(a.id) AS articleCount, COALESCE(SUM(a.views), 0) AS totalViews')
            ->leftJoin('m.articles', 'a')
            ->where('m.id IN (:ids)')
            ->setParameter('ids', $magazineIds)
            ->groupBy('m.id')
            ->getQuery()
            ->getArrayResult();

        $counts = array_fill_keys($magazineIds, 0);
        $views = array_fill_keys($magazineIds, 0);
        foreach ($rows as $row) {
            $counts[(int) $row['id']] = (int) $row['articleCount'];
            $views[(int) $row['id']] = (int) $row['totalViews'];
        }

        return [$counts, $views];
    }
}
