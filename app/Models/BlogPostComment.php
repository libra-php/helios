<?php

namespace App\Models;

use Helios\Model\Model;

class BlogPostComment extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('blog_post_comments', $id);
    }
}
