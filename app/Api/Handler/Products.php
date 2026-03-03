<?php
namespace App\Api\Handler;

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
class Products extends RequestHandler implements JsonRenderableInterface
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
        $sort = $this->mapSort($request->request('sort', 'name'));

        $sql = 'SELECT * FROM product p INNER JOIN product_to_category pc ON p.product_id = pc.product_id WHERE pc.category_id = :category_id ';
        /**@var \App\Model\Product\Collection $productCollection */
        $productCollection = $this
            ->productModel()
                ->collection()
                    ->raw($sql, $this->captureParams($request))
                    ->size($request->request('size', 20))
                    ->page($request->request('page', 1))
                    ->sort($sort, 'asc');
        
        
        return $this->json(
            [
                'products' => $productCollection,
                'pagination' => [
                    'total' => $productCollection->total(),
                    'page' => $productCollection->page(),
                    'size' => $productCollection->size(),
                    'pages' => $productCollection->pages(),
                ],
            ]
        );
    }

    private function mapSort(string $sort): string
    {
        return match ($sort) {
            'price' => 'p.price',
            'newest' => 'p.created_at',
            default => 'p.name',
        };
    }

    /**
     * @param RequestInterface $request
     * 
     * @return array
     */
    protected function captureParams(RequestInterface $request): array
    {
        return [
            'category_id' => $request->request('category_id') ?? 1,
        ];
    }

    /**
     * @return Product
     */
    protected function productModel(): Product
    {
        return $this->productModel;
    }
}