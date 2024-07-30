<?php

namespace Helios\Kernel;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Helios\Middleware\Middleware;
use StellarRouter\Route;
use StellarRouter\Router;
use Symfony\Component\HttpFoundation\{Response, Request};

class Http implements IKernel
{
    protected array $middleware = [];
    private ?Route $route;
    private Request $request;
    private Router $router;

    public function __construct()
    {
        $this->initMiddleware();
        $this->registerControllers();
    }

    public function main()
    {
        $this->request = container()->get(Request::class);
        $this->route = $this->router
            ->handleRequest($this->request->getMethod(), $this->request->getPathInfo());
        $middleware = container()->get(Middleware::class);
        $response = $middleware->layer($this->middleware)
            ->handle($this->request, fn() => $this->resolve());
        $response->prepare($this->request);
        $response->send();
    }

    private function resolve()
    {
        if ($this->route) {
            $handlerClass = $this->route->getHandlerClass();
            $handlerMethod = $this->route->getHandlerMethod();
            $routeParameters = $this->route->getParameters();
            $routeMiddleware = $this->route->getMiddleware();
            $routePayload = $this->route->getPayload();
            if ($handlerClass) {
                $class = new $handlerClass($this->request);
                $content = $class->$handlerMethod(...$routeParameters);
            } elseif ($routePayload) {
                $content = $routePayload(...$routeParameters);
            }
            if (in_array("api", $routeMiddleware)) {
                return $content;
            }
            // Serve controller response
            return new Response($content, 200);
        }
        // Page not found
        return new Response(null, 404);
    }

    private function initMiddleware()
    {
        foreach ($this->middleware as $i => $class) {
            $this->middleware[$i] = new $class();
        }
    }

    private function registerControllers()
    {
        $this->router = new Router;
        $controller_path = config("paths.controllers");
        $map = ClassMapGenerator::createMap($controller_path);
        foreach ($map as $class => $path) {
            $this->router->registerClass($class);
        }
    }
}
