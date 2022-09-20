<?php

namespace App\Controller\MassMedia\Resource;

use App\Entity\News;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mass-media/resource/')]
class NewsController extends AbstractController
{
    public function __construct(private readonly NewsRepository $newsRepository) {}

    #[Route('news', 'news_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->newsRepository->findAll());
    }

    #[Route('news/{id}', 'news_item', methods: ['GET'])]
    public function item(News $news): JsonResponse
    {
        return $this->json($news);
    }
    
    #[Route('news', 'news_add', methods: ['POST'])]
    public function add(News $news): JsonResponse
    {
        $this->newsRepository->add($news);
        
        return $this->json($news, Response::HTTP_CREATED);
    }

    #[Route('news/{id}', 'news_remove', methods: ['DELETE'])]
    public function remove(News $news, NewsRepository $newsRepository): JsonResponse
    {
        // save news id before remove, because after removing it will become null
        $id = $news->getId();

        $newsRepository->remove($news);

        return $this->json([
            'message' => sprintf('News with id "%d" has been deleted', $id)
        ]);
    }
}
