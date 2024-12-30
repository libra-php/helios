<?php

namespace App\Models;

use Helios\Model\Model;

class BlogCategories extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('blog_categories', $id);
    }
}
