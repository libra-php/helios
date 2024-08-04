<?php

namespace App\Modules;

use Helios\Module\View;
use Helios\Module\Module;

class Users extends Module
{
    protected string $name = "Users";
    protected string $path = "users";

    public function configure(View $view)
    {
        $view->sql_table = "users";

        $view->table = [
            "ID" => "id",
            "UUID" => "uuid",
            "Name" => "name",
            "Email" => "email",
            "Created" => "created_at",
        ];

        $view->format = [
            "name" => fn($column, $value) => template("components/format/span.html", compact("column", "value"))
        ];

        $view->form = [
            "Name" => "name",
            "Email" => "email",
        ];
        parent::configure($view);
    }
}
