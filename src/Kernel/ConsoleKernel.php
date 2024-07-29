<?php

namespace Helios\Kernel;

class ConsoleKernel extends Kernel implements IKernel
{
    public function main()
    {
        echo "Hi from console kernel";
    }
}
