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
        $this->addTable("ID", "id")
            ->addTable("Name", "name")
            ->addTable("Permission Level", "permission_level")
            ->addTable("Created", "created_at");

        $this->formatTable("created_at", "ago");
    }
}
