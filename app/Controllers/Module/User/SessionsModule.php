<?php

namespace App\Controllers\Module\User;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

#[
    Group(
        prefix: "/admin/user/sessions",
        middleware: ["module" => "user-sessions"]
    )
]
class SessionsModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->roles = ["Super Admin", "Admin"];
        $this->table = "user_sessions";
        $this->module_title = "Sessions";
        $this->link_parent = "Users & Roles";
        $this->table_columns = [
            "ID" => "id",
            "User" => "(SELECT users.username 
                FROM users 
                WHERE users.id = user_sessions.user_id) as user",
            "Module" => "module",
            "IP" => "INET_NTOA(ip) as ip",
            "URL" => "url",
            "Created" => "created_at",
        ];
        $this->table_format = [
            "created_at" => "ago",
        ];
        $this->searchable = ["module", "url"];
        $this->default_sort = "DESC";
        $this->has_delete = $this->has_edit = $this->has_create = false;
    }
}
