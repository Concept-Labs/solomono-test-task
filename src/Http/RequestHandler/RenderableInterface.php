<?php
namespace Core\Http\RequestHandler;

use Core\Http\ResponseInterface;

interface RenderableInterface
{
    /**
     * @param string $view
     * @param array $data
     * 
     * @return ResponseInterface
     */
    public function render(string $view, array $data = []): ResponseInterface;
}