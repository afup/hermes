<?php

declare(strict_types=1);

namespace Afup\Hermes\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends AbstractController
{
    #[Route('/_health', name: 'health_check')]
    public function index(): Response
    {
        return new Response('OK');
    }
}
