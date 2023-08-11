<?php

require_once __DIR__.'/../vendor/autoload.php';

use App\Exception\ApiException;
use App\Kernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

$routesConfig = include __DIR__.'/../src/routes/app.php';
$request = Request::createFromGlobals();
$kernel = new Kernel($routesConfig, $request);

try {
    $response = $kernel->run();
} catch (ResourceNotFoundException $exception) {
    $response = new Response('Not Found', 404);
} catch (ApiException $exception) {
    $response = new Response('An error occurred', $exception->getCode());
} catch (Exception $exception) {
    $response = new Response('An error occurred', 500);
}

$response->send();
