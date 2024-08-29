<?php

namespace App\Modules;

use App\Models\UserType;
use Helios\Module\Module;

class UserTypes extends Module
{
    protected string $model = UserType::class;

    protected array $rules = [
        "name" => ["required"],
        "permission_level" => ["required"],
    ];

    public function __construct()
    {
        $this->table("ID", "id")
            ->table("Name", "name")
            ->table("Permission Level", "permission_level")
            ->table("Created", "created_at")
            ->format("created_at", "ago");

        $this->form("Name", "name")
            ->form("Permission Level", "permission_level");
    }
}
