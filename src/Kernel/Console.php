<?php

namespace Helios\Kernel;

use Helios\Trait\Singleton;

class Console implements IKernel
{
    use Singleton;

    public function main()
    {
        printf("Hello, console! From Helios");
    }
}
