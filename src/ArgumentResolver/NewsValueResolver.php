<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Entity\News;
use App\Repository\NewsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class NewsValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly NewsRepository $newsRepository
    ) {}

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if ($argument->getType() !== News::class) {
            return false;
        }
        
        return true;
    }
    
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $id = $request->attributes->getInt('id');
        if ($id) {
            $news = $this->newsRepository->find($id);
            if (!$news) {
                throw new NotFoundHttpException(sprintf('News with id "%d" not found.', $id));
            }
            
            yield $news;
            return;
        }

        $isPost = $request->getMethod() === Request::METHOD_POST;
        if ($isPost && !empty($request->getContent())) {
            try {
                $context = (new ObjectNormalizerContextBuilder())
                    ->withGroups('write')
                    ->toArray();
                
                yield $this->serializer->deserialize($request->getContent(), News::class, 'json', $context);
                return;
            } catch (\Exception) {
                throw new BadRequestHttpException('Invalid data was sent.');
            }
        }

        throw new BadRequestHttpException('Bad request');
    }
}
