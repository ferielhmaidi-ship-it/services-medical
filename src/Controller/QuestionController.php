<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Specialite;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/question')]
class QuestionController extends AbstractController
{
    #[Route('/{id}/like', name: 'question_like', methods: ['POST'])]
    public function like(Question $question, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        if ($question->isLikedBy($user)) {
            $question->removeLikedBy($user);
            $question->setLikes(max(0, $question->getLikes() - 1));
        } else {
            $question->addLikedBy($user);
            $question->setLikes($question->getLikes() + 1);
        }
        
        $em->flush();

        return $this->json([
            'likes' => $question->getLikes(),
            'liked' => $question->isLikedBy($user)
        ]);
    }

    #[Route('/', name: 'question_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = trim((string) $request->query->get('search', ''));
        $specialiteId = $request->query->getInt('specialite', 0);

        $qb = $em->getRepository(Question::class)
            ->createQueryBuilder('q')
            ->leftJoin('q.specialite', 's')
            ->addSelect('s');

        if ($search !== '') {
            $qb->andWhere(
                'q.titre LIKE :term
                 OR q.description LIKE :term
                 OR s.nom LIKE :term'
            )
            ->setParameter('term', '%' . $search . '%');
        }

        if ($specialiteId > 0) {
            $qb->andWhere('s.id = :specialiteId')
               ->setParameter('specialiteId', $specialiteId);
        }

        $questions = $qb
            ->orderBy('q.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $specialiteRows = $em->getRepository(Specialite::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.questions', 'q')
            ->addSelect('COUNT(q.id) AS questionCount')
            ->groupBy('s.id')
            ->orderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('question/index.html.twig', [
            'questions' => $questions,
            'specialiteCards' => $specialiteRows,
            'specialitesList' => $em->getRepository(Specialite::class)->findAll(),
            'selectedSpecialiteId' => $specialiteId,
            'searchTerm' => $search,
        ]);
    }

    #[Route('/new', name: 'question_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Ensure only patients can post questions
        if (!$this->isGranted('ROLE_PATIENT')) {
            $this->addFlash('error', 'Vous devez être connecté en tant que patient pour poser une question.');
            return $this->redirectToRoute('question_index');
        }

        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->setCreatedAt(new \DateTime());
            
            // Assign the current user (Patient) to the question
            /** @var \App\Entity\Patient $user */
            $user = $this->getUser();
            $question->setPatient($user);

            $em->persist($question);
            $em->flush();

            $this->addFlash('success', 'Question ajoutée avec succès');

            return $this->redirectToRoute('question_index');
        }

        return $this->render('question/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'question_edit', methods: ['GET', 'POST'])]
    public function edit(
        Question $question,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Question modifiée avec succès');

            return $this->redirectToRoute('question_index');
        }

        return $this->render('question/edit.html.twig', [
            'form' => $form->createView(),
            'question' => $question,
        ]);
    }
}
