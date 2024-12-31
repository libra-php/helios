<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/user/sessions", middleware: ["module" => "user-sessions"])]
class UserSessionsModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->table = "user_sessions";
        $this->module_title = "User Sessions";
        $this->link_parent = "Administration";
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
        $this->searchable = [
            "module",
            "url",
        ];
        $this->default_sort = "DESC";
    }
}
