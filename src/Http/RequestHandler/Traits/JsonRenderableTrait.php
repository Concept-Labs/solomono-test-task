<?php
namespace Core\Http\RequestHandler\Traits;

use Core\Http\Code;
use Core\Http\ResponseInterface;

trait JsonRenderableTrait
{

    /**
     * {@inheritDoc}
     */
    public function json(string|array $json): ResponseInterface
    {
        $response = $this->getResponseFactory()->create();

        $json = is_array($json) ? json_encode($json) : $json;
        
        return $response
            ->status(Code::OK)
            ->header('Content-Type', 'application/json')
            ->body($json);
    }
}