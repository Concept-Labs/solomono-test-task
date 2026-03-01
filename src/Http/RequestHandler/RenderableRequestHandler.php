<?php
namespace Core\Http\RequestHandler;

use Core\Container\Attribute\Injectable;
use Core\Container\Attribute\Injector;
use Core\Http\Code;
use Core\Http\ResponseInterface;
use Core\Phtml\Phtml;

#[Injectable]
abstract class RenderableRequestHandler extends RequestHandler implements RenderableInterface
{
    /**
     * @var Phtml|null
     */
    private ?Phtml $phtml = null;

    /**
     * @param Phtml $phtml
    */
    #[Injector]
    public function setPhtml(Phtml $phtml): static
    {
        $this->phtml = $phtml;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function render(string $template, array $data = []): ResponseInterface
    {
        $content = $this->getPhtml()->render($template, $data);

        $response = $this->getResponseFactory()->create();

        if (empty($content)) {
            return $response
                ->status(Code::NO_CONTENT)
                ->header('Content-Type', 'text/plain')
                ->body('');

        }
        
        return $response
            ->status(Code::OK)
            ->header('Content-Type', 'text/html')
            ->body($content);
    }

    /**
     * @return Phtml
     */
    protected function getPhtml(): Phtml
    {
        if ($this->phtml === null) {
            throw new \RuntimeException('Phtml dependency not injected or set.');
        }
        return $this->phtml;
    }
}