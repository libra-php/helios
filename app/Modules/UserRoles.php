<?php

namespace App\Modules;

use App\Models\UserRole;
use Helios\Module\Module;

class UserRoles extends Module
{
    protected string $model = UserRole::class;

    public function __construct()
    {
        $this->rules = [
            "name" => ["required"],
        ];

        $this->table("ID", "id")
            ->table("Name", "name")
            ->table("Created", "created_at");

        $this->format("created_at", "ago");

        $this->form("Permission Level", "permission_level");
    }

    public function hasEditPermission(int $id): bool
    {
        return $id > 3;
    }

    public function hasDeletePermission(int $id): bool
    {
        return $id > 3;
    }
}
