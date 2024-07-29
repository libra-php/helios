<?php

namespace Helios;

use Helios\Kernel\Kernel;

class Application
{
    public function __construct(private Kernel $kernel) {}

    public function run()
    {
        $this->kernel->main();
    }
}
