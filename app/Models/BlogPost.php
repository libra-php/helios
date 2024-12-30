<?php

namespace App\Models;

use Helios\Model\Model;

class BlogPost extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('blog_posts', $id);
    }

    public function user(): User
    {
        return User::findOrFail($this->user_id);
    }

    public function category(): BlogCategories
    {
        return BlogCategories::findOrFail($this->category_id);
    }

    public function status(): BlogPostStatus
    {
        return BlogPostStatus::findOrFail($this->status_id);
    }

    public function coverImage(): User
    {
        return File::findOrFail($this->cover_image);
    }
}
