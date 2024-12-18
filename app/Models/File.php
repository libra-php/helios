<?php

namespace App\Models;

use Helios\Model\Model;

class File extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('files', $id);
    }
}
