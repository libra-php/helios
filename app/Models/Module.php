<?php

namespace App\Models;

use Helios\Model\Model;

class Module extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("modules", $key);
    }
}
