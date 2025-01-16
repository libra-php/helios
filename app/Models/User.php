<?php

namespace App\Models;

use Helios\Model\Model;

class User extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct("users", $id);
    }

    public function role()
    {
        return UserRole::find($this->user_role_id);
    }

    public function avatar()
    {
        if ($this->avatar) {
            $file = File::find($this->avatar);
            if ($file) {
                return "/uploads/{$file->name}";
            }
        }
        return $this->gravatar();
    }

    public function gravatar()
    {
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}";
    }
}
