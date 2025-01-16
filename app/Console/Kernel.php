<?php

namespace App\Console;

use Helios\Kernel\Console;

class Kernel extends Console
{
    public function main()
    {
        $adapter = container()->get(Adapter::class);
        $adapter->run();
    }
}
