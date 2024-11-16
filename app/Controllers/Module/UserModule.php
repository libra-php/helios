<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/users", middleware: ["module" => "users"])]
class UserModule extends ModuleController
{
    protected string $module_title = "Users";
    protected string $module_parent = "Administration";

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
    protected array $table_format = [
        "created_at" => "ago",
        "updated_at" => "ago",
    ];
}
