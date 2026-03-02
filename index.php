<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = new Core\App\App(__DIR__ . '/etc/cfg.php');
$app->run();
