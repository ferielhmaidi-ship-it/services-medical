<?php

namespace App\Controller;

use App\Entity\TempsTravail;
use App\Form\TempsTravailType;
use App\Repository\TempsTravailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/temps/travail')]
class TempsTravailController extends AbstractController
{
    #[Route('/', name: 'app_temps_travail_index', methods: ['GET'])]
    public function index(TempsTravailRepository $tempsTravailRepository): Response
    {
        return $this->render('temps_travail/index.html.twig', [
            'temps_travails' => $tempsTravailRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_temps_travail_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tempsTravail = new TempsTravail();
        $form = $this->createForm(TempsTravailType::class, $tempsTravail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tempsTravail);
            $entityManager->flush();

            return $this->redirectToRoute('app_temps_travail_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('temps_travail/new.html.twig', [
            'temps_travail' => $tempsTravail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_temps_travail_show', methods: ['GET'])]
    public function show(TempsTravail $tempsTravail): Response
    {
        return $this->render('temps_travail/show.html.twig', [
            'temps_travail' => $tempsTravail,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_temps_travail_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TempsTravail $tempsTravail, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TempsTravailType::class, $tempsTravail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_temps_travail_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('temps_travail/edit.html.twig', [
            'temps_travail' => $tempsTravail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_temps_travail_delete', methods: ['POST'])]
    public function delete(Request $request, TempsTravail $tempsTravail, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tempsTravail->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tempsTravail);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_temps_travail_index', [], Response::HTTP_SEE_OTHER);
    }
}
