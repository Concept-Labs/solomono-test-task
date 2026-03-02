<?php
namespace Core\App\Middleware;

use Core\Config\ConfigInterface;
use Core\Http\Code;
use Core\Http\Exception\HttpException;
use Core\Http\Exception\NotFoundException;
use Core\Http\RequestInterface;
use Core\Http\ResponseFactory;
use Core\Http\ResponseInterface;
use Core\Middleware\MiddlewareInterface;

class ErrorHandler implements MiddlewareInterface
{
    /**
     * @var ResponseInterface|null
     */
    private ?ResponseInterface $response = null;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(
        private ConfigInterface $config,
        private ResponseFactory $responseFactory
        )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        // set_exception_handler(function ($exception) {
        //     $this->response->status(Code::INTERNAL_SERVER_ERROR);
        //     $this->response->body('An error occurred: ' . $exception->getMessage());
        //     $this->response->send();
        // });

        try {
            return $next($request);
            
        } 
        catch (HttpException $e) {
            return $this->response()
                ->status(Code::from($e->getCode()))
                ->body($e->getMessage());
        }
        catch (\Throwable $e) {
            
            return $this->response()
                ->status(Code::INTERNAL_SERVER_ERROR)
                ->body('An error occurred: ' . $this->formatMessage($e));
        }
    }

    /**
     * @return ResponseInterface
     */
    private function response(): ResponseInterface
    {
        return $this->response ??= $this->responseFactory->create();
    }

    private function config(string $key, mixed $default = null): mixed
    {
        return $this->config->get($key, $default);
    }

    /**
     * @param \Throwable $e
     * @return string
     */
    private function formatMessage(\Throwable $e): string
    {
        if ($this->config('app.debug')) {
            return sprintf(
                "Exception: %s in %s on line %d<br>Stack trace:<br>%s",
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                nl2br($e->getTraceAsString())
            );
        }

        return 'An unexpected error occurred.';
    }
}