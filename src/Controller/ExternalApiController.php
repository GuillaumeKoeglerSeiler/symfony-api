<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExternalApiController extends AbstractController
{
    /**
     * méthode fait appel a la route écrite en dessous
     * recupere les donnéees et les transmet telle quelle
     * plus d'info sur le client : https://symfony.com/doc/currant/http_client.html
     */
    /**
     * @param HttpClientInterface $httpClient
     * @return JsonResponse
     * 
     * @Route("/api/external/getSfDoc", name="external_api", methods="GET")
     */
    public function getSymfonyDoc(HttpClientInterface $httpClient): JsonResponse
    {
        $response = $httpClient->request(
            'GET',
            'https://api.github.com/repos.symfony/symfony-docs'
        );
        return new JsonResponse($response->getContent(), $response->getStatusCode(), [], true);
    }
}
