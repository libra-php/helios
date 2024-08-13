<?php

namespace App\Models;

use Helios\Model\Model;

class Audit extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("audit", $key);
    }
}

