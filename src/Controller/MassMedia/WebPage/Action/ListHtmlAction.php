<?php

declare(strict_types=1);

namespace App\Controller\MassMedia\WebPage\Action;

use App\Repository\NewsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route(
    '/mass-media/news',
    'news_list_html',
    methods: ['GET'],
    condition: "request.headers.get('accept') contains 'html'",
    priority: 10
)]
class ListHtmlAction
{
    public function __construct(private readonly Environment $twig) {}

    public function __invoke(NewsRepository $newsRepository): Response
    {
        $content = $this->twig->render('news/index.html.twig', [
            'news' => $newsRepository->findAll()
        ]);
        
        return new Response($content);
    }
}
