<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendrier', name: 'app_calendrier')]
    public function index(): Response
    {
        return $this->render('pages/calendrier.html.twig');
    }
}