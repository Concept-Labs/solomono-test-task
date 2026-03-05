<?php
$cfg= [
    'db' => [
        'dsn' => 'sqlite:' . realpath(__DIR__ . '/../db/solomono_test.sqlite'),
        // 'dsn' => 'mysql:host=localhost;dbname=solomono_test;charset=utf8mb4;',
        // 'username' => '',
        // 'password' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
    ],

    'path' => [
        'base' => realpath(__DIR__ . '/../'),
        'phtml' => realpath(__DIR__ . '/../res/phtml'),
    ],

    'app' => [
        'debug' => false,
        'middleware' => [
            //\Core\App\Middleware\SendResponse::class,
            \Core\App\Middleware\ErrorHandler::class,
            \Core\App\Middleware\InitSession::class,
            \Core\App\Middleware\DbConnection::class,
            \Core\App\Middleware\RequestAttributes::class,
            \Core\App\Middleware\XHeaders::class,
            \Core\App\Middleware\Cors::class,
            \Core\App\Middleware\Router::class,

        ],
    ],
    
    'routes' => [
        '/' => \App\Http\Handler\Index::class,
        '/api/products' => \App\Api\Handler\Products::class,
        '/api/product/details' => \App\Api\Handler\Product\Details::class,
    ],
];

//щоб не юзати сторонні env бібліотеки, нехай буде просто таке використання локального конфіг
$localCfg = [];
$localCfgPath = __DIR__ . '/cfg.local.php';
if (file_exists($localCfgPath) && is_readable($localCfgPath)) {
    $localCfg = require $localCfgPath;
}

$cfg = array_replace_recursive($cfg, $localCfg);

return $cfg;
