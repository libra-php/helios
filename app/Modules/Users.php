<?php

namespace App\Modules;

use Helios\Module\Format;
use Helios\Module\View;
use Helios\Module\Module;

class Users extends Module
{
    protected string $name = "Users";
    protected string $path = "users";

    public function configure(View $view)
    {
        $view->setTable("users");

        $view->addTable("ID", "id")
            ->addTable("UUID", "uuid")
            ->addTable("Name", "name")
            ->addTable("Email", "email")
            ->addTable("Created", "created_at");

        $user = user();
        $view->addFilterLink("Me", "id = $user->id")
            ->addFilterLink("Others", "id != $user->id")
            ->addFilterLink("All", "1=1");

        $view->tableFormat("created_at", fn($column, $value) => Format::ago($column, $value));

        $view->addForm("Name", "email")
            ->addForm("Email", "email");

        parent::configure($view);
    }
}
