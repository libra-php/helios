<?php

namespace App\Models;

use Helios\Model\Model;

class Test extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("test", $key);
    }
}

