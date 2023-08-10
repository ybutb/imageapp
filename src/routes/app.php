<?php

declare(strict_types=1);

use App\Controller\ImageController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('modify', new Route('/{imageName}/{imageAction}', [
    'controller' => ImageController::class,
    'method' => 'GET',
    'action' => 'index'
]));

return $routes;