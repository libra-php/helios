<?php

namespace App\Modules;

use App\Models\User;
use Helios\Module\Module;

class Users extends Module
{
    protected string $model = User::class;

    public function __construct()
    {
        $this->rules = [
            "name" => ["required"],
            "email" => ["required", "email"],
            "password" => ["required", "min_length|8", "alpha_num"],
            "password_match" => ["required", function($value) {
                controller()->addErrorMessage("password_match", "Passwords must match");
                return request()->get("password") === $value;
            }],
        ];

        $this->table("ID", "id")
            ->table("UUID", "uuid")
            ->table("Name", "name")
            ->table("Email", "email")
            ->table("Created", "created_at");

        $user = user();
        $this->filterLink("Me", "id = $user->id")
            ->filterLink("Others", "id != $user->id")
            ->filterLink("All", "1=1");

        $this->format("created_at", "ago");

        $this->search("name")
            ->search("email");

        $this->form("Name", "name")
             ->form("Email", "email")
             ->form("Password", "password")
             ->form("Password (again)", "password_match");

        $this->control("email", "email")
             ->control("password", "password")
             ->control("password_match", "password");
    }

    public function hasEditPermission(int $id): bool
    {
        $user = user();
        if ($id === $user->id) return true;

        if ($user->type()->permission_level < 2) {
            return true;
        }

        return false;
    }

    public function hasDeletePermission(?int $id): bool
    {
        $user = user();
        if ($id === $user->id) return true;

        if ($user->type()->permission_level < 2) {
            return true;
        }

        return false;
    }
}
