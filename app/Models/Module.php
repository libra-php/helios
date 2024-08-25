<?php

namespace App\Models;

use Helios\Model\Model;

class Module extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("modules", $key);
    }

    public function parent()
    {
        return Module::findOrFail($this->parent_module_id);
    }
}
