# Symfony Application using Controller

Sample Symfony application that demonstrates how to use controllers by separating them into resource and web page types.  
You can read about the types of controllers resource, web page and service in the [article](https://web-mastering.blogspot.com/2022/09/Controllers-for-Resources-Services-and-Web-Pages-in-a-Symfony-Application.html).

This application has a separate `News` resource controller that returns data in JSON format, as well as a web page controller to return data in HTML format.

The `News` resource controller is made in the architectural style of the [REST](https://en.wikipedia.org/wiki/Representational_state_transfer).  
The `News` web page controller is made using the [ADR](https://en.wikipedia.org/wiki/Action%E2%80%93domain%E2%80%93responder) pattern, that is, there is no controller itself, but there are separate actions.

An [Argument Resolver](https://symfony.com/doc/current/controller/argument_value_resolver.html) has been added to define the `News` entity in controller actions.

## News resource controller
```php
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
```

## News web page list action
```php
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
```

## News web page item action
```php
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
```

## Argument Resolver
```php
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
```

## Resources
- [Controllers for Resources, Services and Web Pages in a Symfony Application](https://web-mastering.blogspot.com/2022/09/Controllers-for-Resources-Services-and-Web-Pages-in-a-Symfony-Application.html)
- [Symfony Docs: Controllers](https://symfony.com/doc/current/controller.html)
- [API Platform](https://api-platform.com/)
