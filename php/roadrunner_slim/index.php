<?php

declare(strict_types=1);

require 'vendor/autoload.php';

$config = [
    'settings' => [
        'routerCacheFile' => __DIR__ . '/routes.cache.php',
    ]
];

$app = new Slim\App($config);
$container = $app->getContainer();

$app->get('/', function ($request, $response) {
    return $response->getBody()->write('');
});

$app->get('/user/{id}', function ($request, $response, $args) {
    return $response->getBody()->write($args['id']);
});

$app->post('/user', function ($request, $response) {
    return $response->getBody()->write('');
});

$relay = new Spiral\Goridge\StreamRelay(STDIN, STDOUT);
$psr7 = new Spiral\RoadRunner\PSR7Client(new Spiral\RoadRunner\Worker($relay));
while ($request = $psr7->acceptRequest()) {
    try {
        $container['request'] = $request;
        $container['response'] = new \Zend\Diactoros\Response();
        $response = $app->run(true);
        $psr7->respond($response);
    } catch (\Throwable $e) {
        $psr7->getWorker()->error((string)$e);
    }
}