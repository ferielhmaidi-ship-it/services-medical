<?php

namespace App\Controller;

use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/question')]
class AdminQuestionController extends AbstractController
{
    #[Route('/', name: 'admin_question_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $questions = $em->getRepository(Question::class)->findAll();

        return $this->render('admin_question/index.html.twig', [
            'questions' => $questions,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_question_delete', methods: ['POST', 'GET'])]
    public function delete(Question $question, EntityManagerInterface $em): Response
    {
        $em->remove($question);
        $em->flush();

        $this->addFlash('success', 'La question a été supprimée avec succès.');

        return $this->redirectToRoute('admin_question_index');
    }
}
