<?php
namespace App\Http\Handler;

use App\Model\Category;
use Core\Container\Attribute\Injectable;
use Core\Container\Attribute\Injector;
use Core\Http\Attribute\AllowMethod;
use Core\Http\Attribute\WithMiddleware;
use Core\Http\RequestHandler\PhtmlRenderableInterface;
use Core\Http\RequestHandler\RequestHandler;
use Core\Http\RequestHandler\Traits\PhtmlRendarableTrait;
use Core\Http\RequestInterface;
use Core\Http\ResponseInterface;

#[AllowMethod('GET')]
#[WithMiddleware(\App\Http\Handler\Middleware\TestRequestHandlerMiddleware::class)]
#[Injectable]
class Index extends RequestHandler implements PhtmlRenderableInterface
{
    use PhtmlRendarableTrait;

    /**
     * @var Category
     */
    private Category $categoryModel;

    #[Injector]
    /**
     * @param Category $category
     */
    public function requireModels(Category $category)
    {
        $this->categoryModel = $category;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
     
        /**@var \App\Model\Category\Collection $categoryCollection */
        $categoryCollection = $this->categoryModel()->collection();


        return $this->render(
            'index',
            [
                'category_tree' => $categoryCollection->buildTree()
            ]
        );
    }
    
    /**
     * @return Category
     */
    protected function categoryModel(): Category
    {
        return $this->categoryModel;
    }
}