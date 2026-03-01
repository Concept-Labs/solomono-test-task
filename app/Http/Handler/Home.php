<?php
namespace App\Http\Handler;

use App\Model\Category;
use App\Model\Product;
use Core\Container\Attribute\Injectable;
use Core\Container\Attribute\Injector;
use Core\Http\Attribute\AllowMethod;
use Core\Http\Attribute\WithMiddleware;
use Core\Http\RequestHandler\RenderableRequestHandler;
use Core\Http\RequestInterface;
use Core\Http\ResponseInterface;

#[AllowMethod('GET')]
#[WithMiddleware(\App\Http\Handler\Middleware\TestRequestHandlerMiddleware::class)]
#[Injectable]
class Home extends RenderableRequestHandler
{
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
     
        $productCollection = $this->ProductModel()
            ->collection()
                ->with($this->categoryModel);


        // $collection->select('id', 'name', 'price')
        //     //->from('products')
        //     ->where('price', '>', 100)
        //     ->order('price', 'DESC')
        //     ->group('category_id');

        //die((string)$productCollection->select());
        


        return $this->render(
            'index', 
            ['products' => $productCollection]);
    }

    protected function ProductModel(): Product
    {
        return $this->productModel;
    }
}