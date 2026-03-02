<?php
namespace Core\Http\RequestHandler;

use Core\Http\ResponseInterface;
use Core\Phtml\Phtml;

interface PhtmlRenderableInterface
{
    /**
     * @param string $view
     * @param array $data
     * 
     * @return ResponseInterface
     */
    public function render(string $view, array $data = []): ResponseInterface;

    /**
     * @param Phtml $phtml
     * @return static
     */
    public function setPhtml(Phtml $phtml): static;
}