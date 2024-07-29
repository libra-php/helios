<?php

namespace Helios\Kernel;

class HTTP extends Kernel implements IKernel
{
    public function main()
    {
        echo "Hi from http kernel";
    }
}
