<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/user-sessions", middleware: ["module" => "user-sessions"])]
class UserSessionsModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->table = "user_sessions";
        $this->module_title = "User Sessions";
        $this->module_parent = "Administration";
        $this->table_columns = [
            "ID" => "id",
            "User" => "(SELECT users.username 
                FROM users 
                WHERE users.id = user_sessions.user_id) as user",
            "Module" => "module",
            "URL" => "url",
            "IP" => "INET_NTOA(ip) as ip",
            "Created" => "created_at",
        ];
        $this->table_format = [
            "created_at" => "ago",
        ];
        $this->order_by = ["id DESC"];
    }
}
