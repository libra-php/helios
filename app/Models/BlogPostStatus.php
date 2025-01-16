<?php

namespace App\Models;

use Helios\Model\Model;

class BlogPostStatus extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct("blog_post_status", $id);
    }
}
