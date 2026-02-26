<?php

namespace App\Controller\Api;

use App\Entity\Article;
use App\Entity\Magazine;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class TranslationApiController extends AbstractController
{
    #[Route('/api/translate', name: 'api_translate', methods: ['POST'])]
    public function translate(
        Request $request,
        TranslationService $translationService,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (null === $data) {
                return $this->json(['error' => 'Invalid JSON'], 400);
            }

            $target = $this->normalizeTargetLanguage($data['target'] ?? 'en');
            $type = $data['type'] ?? 'text'; // 'article', 'magazine', or 'text'
            $id = $data['id'] ?? null;
            $textToTranslate = $data['text'] ?? '';

            $result = [];

            if ($type === 'article' && $id) {
                $article = $entityManager->getRepository(Article::class)->find($id);
                if (!$article) {
                    return $this->json(['error' => 'Article not found'], 404);
                }
                
                $result['title'] = $translationService->translate($article->getTitle(), $target);
                $result['resume'] = $translationService->translate($article->getResume(), $target);
                $result['original_title'] = $article->getTitle();
                
            } elseif ($type === 'magazine' && $id) {
                $magazine = $entityManager->getRepository(Magazine::class)->find($id);
                if (!$magazine) {
                    return $this->json(['error' => 'Magazine not found'], 404);
                }
                
                $result['title'] = $translationService->translate($magazine->getTitle(), $target);
                $result['description'] = $translationService->translate($magazine->getDescription(), $target);
                $result['original_title'] = $magazine->getTitle();
                
            } elseif ($type === 'text' && !empty($textToTranslate)) {
                $result['translated'] = $translationService->translate($textToTranslate, $target);
                $result['original'] = $textToTranslate;
            } else {
                return $this->json(['error' => 'Missing text or invalid type/id combination'], 400);
            }

            return $this->json($result);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => 'Translation provider error. Check API key/configuration.',
                'details' => $e->getMessage(),
            ], 502);
        }
    }

    private function normalizeTargetLanguage(string $target): string
    {
        $normalized = strtolower(trim($target));

        return match ($normalized) {
            'fr', 'french', 'francais', 'français' => 'French',
            'en', 'english', 'anglais' => 'English',
            'ar', 'arabic', 'arabe' => 'Arabic',
            'es', 'spanish', 'espagnol', 'espanol', 'español' => 'Spanish',
            'de', 'german', 'allemand' => 'German',
            default => $target,
        };
    }
}
