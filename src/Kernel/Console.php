<?php

namespace Helios\Kernel;

class Console implements IKernel
{
    public function main()
    {
        echo "Hi from console kernel" . PHP_EOL;
    }
}
