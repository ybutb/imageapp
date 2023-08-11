<?php

namespace App\Tests\Unit;

use App\Controller\ImageController;
use App\Exception\ApiException;
use App\Kernel\Kernel;
use App\Tests\BaseRequestTestCase;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteTest extends BaseRequestTestCase
{
    public function testWrongRoute()
    {
        $routes = new RouteCollection();

        $routes->add('existing', new Route('/existing', [
            'controller' => ImageController::class,
            'method' => 'GET',
            'action' => 'show'
        ]));

        $request = $this->createRequest([], '/non-existing');

        $kernel = new Kernel($routes, $request);

        $this->expectException(ResourceNotFoundException::class);

        $kernel->run();
    }

    public function testWrongMethodRoute()
    {
        $routes = new RouteCollection();

        $routes->add('existing', (new Route('/existing', [
            'controller' => ImageController::class,
            'action' => 'show'
        ]))->setMethods(['GET']));

        $request = $this->createRequest([], '/existing');
        $request->setMethod('POST');

        $kernel = new Kernel($routes, $request);

        $this->expectException(MethodNotAllowedException::class);

        $kernel->run();
    }

    public function testWrongControllerMethodRoute()
    {
        $routes = new RouteCollection();

        $routes->add('existing', (new Route('/existing', [
            'controller' => ImageController::class,
            'action' => 'nonexistingMethod'
        ]))->setMethods(['GET']));

        $request = $this->createRequest([], '/existing');

        $kernel = new Kernel($routes, $request);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Wrong route configuration');
        $this->expectExceptionCode(500);

        $kernel->run();
    }
}
