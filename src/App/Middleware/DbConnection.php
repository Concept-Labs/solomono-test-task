<?php
namespace Core\App\Middleware;

use Core\Config\ConfigInterface;
use Core\Db\ConnectionInterface;
use Core\Http\RequestInterface;
use Core\Http\ResponseInterface;
use Core\Middleware\MiddlewareInterface;

class DbConnection implements MiddlewareInterface
{

    public function __construct(
        private ConfigInterface $config,
        private ConnectionInterface $connection
    )
    {}
    /**
     * {@inheritDoc}
     */
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $this->connection->connect(
            $this->config->get('db.dsn'),
            $this->config->get('db.username') ?? '',
            $this->config->get('db.password') ?? '',
            $this->config->get('db.options', [])
        );
        
        

        return $next($request);
    }
}