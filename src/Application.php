<?php

namespace Helios;


use DI\Container;
use Dotenv\Dotenv;
use Error;
use Helios\Kernel\IKernel;
use Helios\Trait\Singleton;

class Application
{
    use Singleton;

    private Container $container;

    public function __construct(private IKernel $kernel) {
        $this->initEnvironment();
        $this->initContainer();
    }

    public function run()
    {
        $this->main();
    }

    public function __call($name, $arguments)
    {
        $this->kernel->$name(...$arguments);
    }

    private function initEnvironment()
    {
        $path = config("paths.env");
        if (!file_exists($path)) {
            throw new Error(".env doesn't exist");
        }
        $dotenv = Dotenv::createImmutable($path);
        $dotenv->safeLoad();
    }

    private function initContainer()
    {
        $definitions = config("container");
        $this->container = new Container($definitions);
    }

    public function container()
    {
        return $this->container;
    }
}
