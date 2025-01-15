<?php

namespace Helios\Kernel;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Helios\Middleware\Middleware;
use Helios\Trait\Singleton;
use StellarRouter\Route;
use StellarRouter\Router;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};

class Http implements IKernel
{
    use Singleton;

    protected array $middleware = [];
    private ?Route $route;
    private Request $request;
    private Router $router;

    public function __construct()
    {
        $this->initRouter();
        $this->initMiddleware();
        $this->registerControllers();
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function main()
    {
        $this->request = container()->get(Request::class);
        $this->route = $this->router
            ->handleRequest($this->request->getMethod(), $this->request->getPathInfo());
        $this->request->attributes->add(["route" => $this->route]);
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
                $class = container()->get($handlerClass);
                $content = $class->$handlerMethod(...$routeParameters);
            } elseif ($routePayload) {
                $content = $routePayload(...$routeParameters);
            }
            // JSON response for API endpoint
            if (in_array("api", $routeMiddleware)) {
                return new JsonResponse([
                    "request_id" => $this->request->get("request_uuid"),
                    "data" => $content,
                    "ts" => time(),
                    "version" => config("app.version"),
                ]);
            }
            // Serve controller response
            return new Response($content, Response::HTTP_OK);
        }
        // Page not found
        redirect(findRoute("error.page-not-found"));
    }

    private function initRouter()
    {
        $this->router = new Router;
    }

    private function initMiddleware()
    {
        foreach ($this->middleware as $i => $class) {
            $this->middleware[$i] = new $class();
        }
    }

    private function registerControllers()
    {
        $controller_path = config("paths.controllers");
        $map = ClassMapGenerator::createMap($controller_path);
        foreach ($map as $class => $path) {
            $this->router->registerClass($class);
        }
    }
}
