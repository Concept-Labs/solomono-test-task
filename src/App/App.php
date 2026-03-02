<?php
namespace Core\App;

use Core\Config\ConfigInterface;
use Core\Container\ContainerInterface;
use Core\Container\Container;
use Core\Http\RequestInterface;
use Core\Http\ResponseInterface;
use Core\App\Exception\NothingWorksException;
use Core\Http\Exception\NotFoundException;

class App
{

    private ?RequestInterface $request = null;
    private ?ConfigInterface $config = null;

    /**
     * @param string $configPath
     * @param ContainerInterface|null $container можемо використати якийсь більш толковий контейнер
     */
    public function __construct(
        private string $configPath,
        private ?ContainerInterface $container = null
    )
    {}

    /**
     * 
     */
    public function run(): void
    {
        $this->processMiddlewareStack(
            $this->request()
        )->send();
    }

    /**
     * Ну глобальний мідлвар
     * Щоб не хардкодити далі застосунку, використаю класику для подальшої зручності
     * 
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    private function processMiddlewareStack(RequestInterface $request): ResponseInterface
    {
        $handler = array_reduce(
            array_reverse($this->appMiddlewares()),
            function ($next, $middleware) {
                return function (RequestInterface $request) use ($middleware, $next) {
                    $middleware = $this->getContainer()->get($middleware);
                    return $middleware->handle($request, $next);
                };
            },
            function () {
                throw new NotFoundException('No handler found for the request.');
            }
        );

        return $handler($request);
    }

    /**
     * @return ConfigInterface
     */
    private function getConfig(): ConfigInterface
    {
        if ($this->config === null) {
            $this->config = $this->getContainer()
                ->get(ConfigInterface::class)
                    ->load($this->configPath);
        }

        return $this->config;
    }
    
    /**
     * @return ContainerInterface
     */
    private function getContainer(): ContainerInterface
    {
        return $this->container ??= new Container(); // ну використаємо який є, але можна замінити ;)
    }
    
    /**
     * @return RequestInterface
     */
    private function request(): RequestInterface
    {
        return $this->request ??= $this->getContainer()->get(RequestInterface::class);
    }

    /**
     * @return string[]
     */
    private function appMiddlewares(): array
    {
        return $this->getConfig()->get('app.middleware') ?? [];
    }

} 