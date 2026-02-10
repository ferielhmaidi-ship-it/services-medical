<?php

namespace App\Controller;

use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ChatbotController extends AbstractController
{
    #[Route('/chatbot/translate', name: 'chatbot_translate', methods: ['POST'])]
public function translate(Request $request, ChatbotService $chatbot): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $text = $data['text'] ?? '';
    $target = $data['target'] ?? 'fr';

    $prompt = "ترجم النص التالي إلى اللغة {$target} بدون شرح:\n\n" . $text;

    $result = $chatbot->ask($prompt);

    return new JsonResponse([
        'result' => $result
    ]);
}
#[Route('/chatbot/fix', name: 'chatbot_fix', methods: ['POST'])]
public function fix(Request $request, ChatbotService $chatbot): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $text = $data['text'] ?? '';
    $lang = $data['lang'] ?? 'fr';

    $prompt = "Corrige les fautes grammaticales et orthographiques en {$lang} sans expliquer :\n\n" . $text;

    $result = $chatbot->ask($prompt);

    return new JsonResponse([
        'result' => $result
    ]);
}

}
