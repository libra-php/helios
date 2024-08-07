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
        /** SQL Table */
        $view->sqlTable("users");

        /** Table definition (index view) */
        $view->table("ID", "id")
            ->table("UUID", "uuid")
            ->table("Name", "name")
            ->table("Email", "email")
            ->table("Created", "created_at");

        /** Filters */
        $user = user();
        $view->filterLink("Me", "id = $user->id")
            ->filterLink("Others", "id != $user->id")
            ->filterLink("All", "1=1");

        /** Table formatting */
        $view->tableFormat("created_at", fn($column, $value) => Format::ago($column, $value));

        /** Form definition (edit & create view) */
        $view->form("Name", "email")
            ->form("Email", "email");

        parent::configure($view);
    }
}
