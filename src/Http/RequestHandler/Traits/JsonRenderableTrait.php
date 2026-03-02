<?php
namespace Core\Http\RequestHandler\Traits;

use Core\Http\Code;
use Core\Http\ResponseInterface;

trait JsonRenderableTrait
{

    /**
     * {@inheritDoc}
     */
    public function json(string $json): ResponseInterface
    {
        $response = $this->getResponseFactory()->create();
        
        return $response
            ->status(Code::OK)
            ->header('Content-Type', 'application/json')
            ->body($json);
    }
}