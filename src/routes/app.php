<?php

declare(strict_types=1);

use App\Controller\ImageController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('show', (new Route('/show', [
    'controller' => ImageController::class,
    'action' => 'show'
]))->setMethods(['GET']));

$routes->add('modify', (new Route('/{imageName}/{imageAction}', [
    'controller' => ImageController::class,
    'action' => 'index'
]))->setMethods(['GET']));

return $routes;