<?php
namespace App\Http\Handler;

use App\Model\Category;
use App\Model\Product;
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
class Home extends RequestHandler implements PhtmlRenderableInterface
{
    use PhtmlRendarableTrait;

    private Product $productModel;
    private Category $categoryModel;

    #[Injector]
    public function requireModels(Product $product, Category $category)
    {
        $this->productModel = $product;
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

    protected function productModel(): Product
    {
        return $this->productModel;
    
        }
    protected function categoryModel(): Category
    {
        return $this->categoryModel;
    }
}