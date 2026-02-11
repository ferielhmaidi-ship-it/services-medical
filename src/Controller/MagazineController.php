<?php

namespace App\Controller;

use App\Entity\Magazine;
use App\Form\MagazineType;
use App\Repository\MagazineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/magazine')]
final class MagazineController extends AbstractController
{
    #[Route(name: 'app_magazine_index', methods: ['GET'])]
    public function index(MagazineRepository $magazineRepository): Response
    {
        return $this->render('admin/magazine/index.html.twig', [
            'magazines' => $magazineRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_magazine_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $magazine = new Magazine();
        $form = $this->createForm(MagazineType::class, $magazine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($magazine);
            $entityManager->flush();

            return $this->redirectToRoute('app_magazine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/magazine/new.html.twig', [
            'magazine' => $magazine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_magazine_show', methods: ['GET'])]
    public function show(Magazine $magazine): Response
    {
        return $this->render('admin/magazine/show.html.twig', [
            'magazine' => $magazine,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_magazine_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Magazine $magazine, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MagazineType::class, $magazine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_magazine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/magazine/edit.html.twig', [
            'magazine' => $magazine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_magazine_delete', methods: ['POST'])]
    public function delete(Request $request, Magazine $magazine, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$magazine->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($magazine);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_magazine_index', [], Response::HTTP_SEE_OTHER);
    }
}
