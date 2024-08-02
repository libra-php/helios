<?php

namespace App\Modules;

use Helios\Module\View;
use Helios\Module\Module;

class Users extends Module
{
    public function configure(View $view)
    {
        $view->sql_table = "users";
        $view->table = [
            "ID" => "id",
            "UUID" => "uuid",
            "Name" => "name",
            "Email" => "email",
            "Created" => "created_at",
            "Updated" => "updated_at",
        ];
        parent::configure($view);
    }
}
