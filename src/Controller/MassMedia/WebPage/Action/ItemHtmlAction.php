<?php

declare(strict_types=1);

namespace App\Controller\MassMedia\WebPage\Action;

use App\Entity\News;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route(
    '/mass-media/news/{id}',
    'news_item_html',
    methods: ['GET'],
    condition: "request.headers.get('accept') contains 'html'",
    priority: 10
)]
class ItemHtmlAction
{
    public function __construct(private readonly Environment $twig) {}

    public function __invoke(News $news): Response
    {
        $content = $this->twig->render('news/item.html.twig', [
            'news' => $news
        ]);
        
        return new Response($content);
    }
}
