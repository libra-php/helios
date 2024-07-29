<?php

namespace Helios\Kernel;

class HTTPKernel extends Kernel implements IKernel
{
    public function main()
    {
        echo "Hi from http kernel";
    }
}
