<?php

namespace App\Models;

use Helios\Model\Model;

class BlogPostImages extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct("blog_post_images", $id);
    }

    public function file(): File
    {
        return File::findOrFail($this->image);
    }

    public function image()
    {
        $image = $this->file();
        return "/uploads/{$image->name}";
    }

    public function post()
    {
        return BlogPost::findOrFail($this->blog_post_id);
    }
}
