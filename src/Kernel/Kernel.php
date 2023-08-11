<?php

declare(strict_types=1);

namespace App\Kernel;

use App\Exception\ApiException;
use Exception;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

final class Kernel
{
    public function __construct(
        private readonly RouteCollection $routes,
        private readonly Request $request
    ) {
    }

    /**
     * @throws ApiException
     * @throws Exception
     * @throws ResourceNotFoundException
     * @throws MethodNotAllowedException
     */
    public function run(): Response
    {
        $context = new RequestContext();
        $context->fromRequest($this->request);
        $matcher = new UrlMatcher($this->routes, $context);
        $routeParams = $matcher->match($this->request->getPathInfo());

        $methodParams = array_filter($routeParams, static fn($routeParamValue, $routeParamName) =>
            !in_array($routeParamName, ['action', 'controller', 'methods', '_route'], true),
                ARRAY_FILTER_USE_BOTH);

        $controllerClass = $routeParams['controller'];
        $action = $routeParams['action'];

        $dependencies = $this->getDependencyClasses($controllerClass);
        $dependencyInstances = [];

        foreach ($dependencies as $dependency) {

            if ($dependency === Request::class) {
                $dependencyInstances[] = $this->request;
                continue;
            }

            $dependencyInstances[] = new $dependency();
        }

        $controllerInstance = new $controllerClass(...$dependencyInstances);

        if (!method_exists($controllerInstance, $action)) {
            throw new ApiException('Wrong route configuration', 500);
        }

        return $controllerInstance->{$action}(...$methodParams);
    }

    private function getDependencyClasses(string $class): array
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
}
