<?php

namespace Helios\Kernel;

class Console implements IKernel
{
    public function main()
    {
        printf("Hello, console! From Helios");
    }
}
