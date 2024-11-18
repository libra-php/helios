<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/users", middleware: ["module" => "users"])]
class UserModule extends ModuleController
{
    public function init(): void
    {
        $this->table = "users";
        $this->module_title = "Users";
        $this->module_parent = "Administration";
        $this->table_columns = [
            "ID" => "id",
            "UUID" => "uuid",
            "Username" => "username",
            "Email" => "email",
            "Name" => "name",
            "Created" => "created_at",
            "Updated" => "updated_at"
        ];
        $this->table_format = [
            "created_at" => "ago",
            "updated_at" => "ago",
        ];
        $this->filter_links = [
            "All" => "1=1",
            "Me" => "id=".user()->id,
        ];
        $this->searchable = [
            "uuid",
            "username",
            "email",
            "name",
        ];

        $this->form_columns = [
            "Name" => "name",
            "Email" => "email",
            "Username" => "username",
            "Password" => "password",
            "Password (again)" => "'' as password_match",
        ];
        $this->form_controls = [
            "name" => "input",
            "email" => "input",
            "username" => "input",
            "password" => fn($label, $column, $value) => $this->password($label, $column, ''),
            "password_match" => fn($label, $column, $value) => $this->password($label, $column, ''),
        ];
        $this->validation_rules = [
            "name" => ["required"],
            "email" => ["required", "unique|users"],
            "username" => ["required", "unique|users"],
            "password" => ["required", "min_length|8", "regex|^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"],
            "password_match" => ["required", function ($value) {
                $this->addErrorMessage("password_match", "Passwords must match");
                return request()->get("password") === $value;
            }],
        ];
    }
}
