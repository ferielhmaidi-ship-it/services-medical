<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Reponse;
use App\Form\ReponseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reponse')]
final class ReponseController extends AbstractController
{
    #[Route('/', name: 'reponse_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $reponses = $em->getRepository(Reponse::class)
            ->createQueryBuilder('r')
            ->leftJoin('r.question', 'q')
            ->addSelect('q')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('reponse/index.html.twig', [
            'reponses' => $reponses,
        ]);
    }

    #[Route('/new', name: 'reponse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reponse);
            $em->flush();

            $this->addFlash('success', 'Ajouter avec success');

            return $this->redirectToRoute('reponse_index');
        }

        return $this->render('reponse/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/question/{id}/create', name: 'reponse_create', methods: ['POST'])]
    public function createFromQuestion(
        Question $question,
        Request $request,
        EntityManagerInterface $em
    ): RedirectResponse {
        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('reponse_create_' . $question->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('question_index');
        }

        $contenu = trim((string) $request->request->get('contenu', ''));
        if ($contenu === '') {
            $this->addFlash('error', 'La rÃ©ponse ne peut pas Ãªtre vide.');
            return $this->redirectToRoute('question_index');
        }

        $reponse = new Reponse();
        $reponse->setQuestion($question);
        $reponse->setContenu($contenu);

        $em->persist($reponse);
        $em->flush();

        $this->addFlash('success', 'RÃ©ponse ajoutÃ©e avec succÃ¨s');

        return $this->redirectToRoute('question_index');
    }

    #[Route('/{id}/edit', name: 'reponse_edit', methods: ['POST'])]
    public function edit(
        Reponse $reponse,
        Request $request,
        EntityManagerInterface $em
    ): RedirectResponse {
        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('reponse_edit_' . $reponse->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('question_index');
        }

        $contenu = trim((string) $request->request->get('contenu', ''));
        if ($contenu === '') {
            $this->addFlash('error', 'La rÃƒÂ©ponse ne peut pas ÃƒÂªtre vide.');
            return $this->redirectToRoute('question_index');
        }

        $reponse->setContenu($contenu);
        $em->flush();

        $this->addFlash('success', 'RÃƒÂ©ponse modifiÃƒÂ©e avec succÃƒÂ¨s');

        return $this->redirectToRoute('question_index');
    }

    #[Route('/{id}/delete', name: 'reponse_delete', methods: ['POST'])]
    public function delete(
        Reponse $reponse,
        Request $request,
        EntityManagerInterface $em
    ): RedirectResponse {
        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('reponse_delete_' . $reponse->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('question_index');
        }

        $em->remove($reponse);
        $em->flush();

        $this->addFlash('success', 'Réponse supprimée avec succès');

        return $this->redirectToRoute('question_index');
    }
}
