<?php

namespace App\Modules;

use App\Models\UserType;
use Helios\Module\Module;

class UserTypes extends Module
{
    protected string $model = UserType::class;

    protected array $rules = [
        "name" => ["required"],
        "permission_level" => ["required", "min|0", "max|10", "unique|user_types"],
    ];

    public function __construct()
    {
        $this->table("ID", "id")
            ->table("Name", "name")
            ->table("Permission Level", "permission_level")
            ->table("Created", "created_at");

        $this->format("created_at", "ago");

        $this->form("Name", "name")
            ->form("Permission Level", "permission_level");
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
