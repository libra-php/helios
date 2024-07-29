<?php

namespace Helios\Kernel;

use DI\{Container, ContainerBuilder};
use Dotenv\Dotenv;
use Error;

class Kernel
{
    private Container $container;

    public function __construct()
    {
        $this->initEnvironment();
        $this->initContainer();
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
        $builder = new ContainerBuilder();
        $definitions = config("container");
        $builder->addDefinitions($definitions);
        $this->container = $builder->build();
    }

    public function container()
    {
        return $this->container;
    }
}
