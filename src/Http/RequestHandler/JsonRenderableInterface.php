<?php
namespace Core\Http\RequestHandler;

use Core\Http\ResponseInterface;

interface JsonRenderableInterface
{
    /**
     * @param string|array $json
     * 
     * @return ResponseInterface
     */
    public function json(string|array $json): ResponseInterface;
}