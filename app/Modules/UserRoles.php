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
            "permission_level" => ["required", "min|0", "max|10", function($value, $id) {
                controller()->addErrorMessage("permission_level", "Permission level must be unique");
                if (!$id) {
                    return !db()->fetch("SELECT 1 
                        FROM user_roles 
                        WHERE permission_level = ?", $value);
                }
                return !db()->fetch("SELECT 1 
                    FROM user_roles 
                    WHERE permission_level = ? AND id != ?", $value, $id);
            }],
        ];

        $this->table("ID", "id")
            ->table("Name", "name")
            ->table("Access Level", "permission_level")
            ->table("Created", "created_at");

        $this->format("created_at", "ago");

        $this->form("Name", "name")
            ->form("Access Level", "permission_level");

        $this->control("permission_level", "number");

        $max_permission = db()->var("SELECT max(permission_level)+1 
            FROM user_roles");
        $this->default("permission_level", $max_permission);
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
