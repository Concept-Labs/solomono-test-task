<?php
namespace App\Api\Handler\Product;

use App\Model\Product;
use Core\Container\Attribute\Injectable;
use Core\Container\Attribute\Injector;
use Core\Http\Attribute\AllowMethod;
use Core\Http\RequestHandler\JsonRenderableInterface;
use Core\Http\RequestHandler\RequestHandler;
use Core\Http\RequestHandler\Traits\JsonRenderableTrait;
use Core\Http\RequestInterface;
use Core\Http\ResponseInterface;

#[AllowMethod('GET')]
#[Injectable]
class Details extends RequestHandler implements JsonRenderableInterface
{
    use JsonRenderableTrait;

    /**@var Product */
    private Product $productModel;

    /**
     * @param Product $product
     * 
     * @return void
     */
    #[Injector]
    public function requireModels(Product $product)
    {
        $this->productModel = $product;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        /**@var \App\Model\Product $product */
        $product = $this
            ->productModel()
                ->find($request->request('id'));
        
        return $this->json(['product' => $product]);
    }

    /**
     * @return Product
     */
    protected function productModel(): Product
    {
        return $this->productModel;
    }
}