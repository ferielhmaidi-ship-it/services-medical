<?php

namespace App\Controller;

use App\Entity\Specialite;
use App\Form\SpecialiteType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SpecialiteController extends AbstractController
{
    #[Route('/specialites', name: 'list_specialites')]
    public function list(ManagerRegistry $doctrine): Response
    {
        $repo = $doctrine->getRepository(Specialite::class);
        $specialites = $repo->findAll();

        return $this->render('specialite/list.html.twig', [
            'specialites' => $specialites,
        ]);
    }

    #[Route('/specialite/add', name: 'add_specialite')]
    public function add(Request $request, ManagerRegistry $doctrine): Response
    {
        $specialite = new Specialite();
        $em = $doctrine->getManager();

        $form = $this->createForm(SpecialiteType::class, $specialite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($specialite);
            $em->flush();

            return $this->redirectToRoute('list_specialites');
        }

        return $this->render('specialite/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/specialite/update/{id}', name: 'update_specialite')]
    public function update(int $id, Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $specialite = $em->getRepository(Specialite::class)->find($id);

        if (!$specialite) {
            throw $this->createNotFoundException('Specialite non trouvée');
        }

        $form = $this->createForm(SpecialiteType::class, $specialite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('list_specialites');
        }

        return $this->render('specialite/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/specialite/delete/{id}', name: 'delete_specialite')]
    public function delete(int $id, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $specialite = $em->getRepository(Specialite::class)->find($id);

        if ($specialite) {
            $em->remove($specialite);
            $em->flush();
        }

        return $this->redirectToRoute('list_specialites');
    }
}
