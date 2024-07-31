<?php

namespace App\Console;

use Helios\Kernel\Console;

class Kernel extends Console
{
    public function main()
    {
        $adapter = new Adapter;
        $adapter->run();
    }
}
