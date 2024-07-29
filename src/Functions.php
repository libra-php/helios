<?php

use Helios\Application;
use Helios\Kernel\{HTTPKernel, ConsoleKernel};

function app()
{
    $app = new Application(new HTTPKernel);
    return $app;
}

function console()
{
    $app = new Application(new ConsoleKernel);
    return $app;
}
