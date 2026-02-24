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
    #[Route('/admin/specialites', name: 'list_specialites')]
    public function list(ManagerRegistry $doctrine, Request $request): Response
    {
        $repo = $doctrine->getRepository(Specialite::class);
        $specialites = $repo->findAll();

        $specialite = new Specialite();
        $addForm = $this->createForm(SpecialiteType::class, $specialite, [
            'action' => $this->generateUrl('add_specialite'),
            'method' => 'POST',
        ]);

        return $this->render('spesialitdash/spes.html.twig', [
            'specialites' => $specialites,
            'addForm' => $addForm->createView(),
        ]);
    }

    #[Route('/admin/specialite/add', name: 'add_specialite')]
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

        // If error, ideally we should show the modal again with errors.
        // For now, let's render height usual add template or list with errors if we can.
        // But since we are redirecting from a separate route, if validation fails, 
        // we might end up on the /admin/specialite/add page. 
        // Let's keep it simple: if valid, redirect. If not, render form (which will show as a full page).
        // A better approach for modals is submitting to the same route (list) or handling submission via AJAX.
        // But the user just asked for "modals".
        
        return $this->render('specialite/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/specialite/update/{id}', name: 'update_specialite')]
    public function update(int $id, Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $specialite = $em->getRepository(Specialite::class)->find($id);

        if (!$specialite) {
            throw $this->createNotFoundException('Specialite non trouvée');
        }

        $form = $this->createForm(SpecialiteType::class, $specialite, [
            'action' => $this->generateUrl('update_specialite', ['id' => $specialite->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('list_specialites');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('specialite/_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $this->render('specialite/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/specialite/delete/{id}', name: 'delete_specialite')]
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
