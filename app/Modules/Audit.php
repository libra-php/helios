<?php

namespace App\Modules;

use Helios\Module\Module;

class Audit extends Module
{
    public function __construct()
    {
        $this->has_create = $this->has_edit = $this->has_delete = false;
    }
}
