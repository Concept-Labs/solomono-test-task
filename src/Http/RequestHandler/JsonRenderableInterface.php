<?php
namespace Core\Http\RequestHandler;

use Core\Http\ResponseInterface;

interface JsonRenderableInterface
{
    /**
     * @param string $view
     * @param array $data
     * 
     * @return ResponseInterface
     */
    public function json(string $view, array $data = []): ResponseInterface;
}