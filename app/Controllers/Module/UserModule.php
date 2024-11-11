<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

#[Group(prefix: "/admin/users", middleware: ["module" => "users"])]
class UserModule extends ModuleController
{
    protected string $title = "Users";
    protected string $parent = "Administration";
    protected string $route = "users";
    protected string $table = "users";
    protected array $table_columns = [
        "ID" => "id",
        "UUID" => "uuid",
        "Username" => "username",
        "Email" => "email",
        "Name" => "name",
        "Created" => "created_at",
        "Updated" => "updated_at"
    ];
}
