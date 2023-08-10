<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

$request = Request::createFromGlobals();
$context = new RequestContext();
$context->fromRequest($request);

$routes = include __DIR__.'/../src/routes/app.php';

$matcher = new UrlMatcher($routes, $context);

try {
    $routeParams = $matcher->match($request->getPathInfo());

    $methodParams = array_filter($routeParams, static fn($routeParaValue, $routeParamName) =>
        !in_array($routeParamName, ['action', 'controller', 'method', '_route'], true),
    ARRAY_FILTER_USE_BOTH);

    $controller = $routeParams['controller'];
    $action = $routeParams['action'];

    $dependencies = getDependencies($controller);
    $dependencyInstances = [];

    foreach ($dependencies as $dependency) {

        if ($dependency === Request::class) {
            $dependencyInstances[] = $request;
            continue;
        }

        $dependencyInstances[] = new $dependency();
    }

    $controllerInstance = new $controller(...$dependencyInstances);

    $response = $controllerInstance->{$action}(...$methodParams);
} catch (ResourceNotFoundException $exception) {
    $response = new Response('Not Found', 404);
} catch (HttpException $exception) {
    $response = new Response('An error occurred', $exception->getCode());
} catch (Exception $exception) {
    $response = new Response('An error occurred', 500);
}

$response->send();

function getDependencies(string $class): array
{
    $reflectionClass = new ReflectionClass($class);

    $constructor = $reflectionClass->getConstructor();

    if (!$constructor) {
        return [];
    }

    $parameters = $constructor->getParameters();

    $dependencies = [];
    // Loop through the parameters to see their types
    foreach ($parameters as $parameter) {
        $parameterClass = $parameter->getType()?->getName();

        if ($parameterClass) {
            $dependencies[] = $parameterClass;
        }
    }

    return $dependencies;
}

