<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/user-roles", middleware: ["module" => "user-roles"])]
class UserRolesModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->table = "user_roles";
        $this->module_title = "User Roles";
        $this->module_parent = "Administration";

        $this->table_columns = [
            "ID" => "id",
            "Name" => "name",
            "Created" => "created_at",
        ];
        $this->table_format = [
            "created_at" => "ago"
        ];
        $this->form_columns = [
            "Name" => "name"
        ];
        $this->form_controls = [
            "name" => "input",
        ];
        $this->validation_rules = [
            "name" => [
                "required",
                "unique:=user_roles",
            ]
        ];
    }
}
