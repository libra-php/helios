<?php

namespace App\Modules;

use App\Models\Module;
use Helios\View\View;

class Users extends Module
{
    public function configure(View $view)
    {
        $user = user();

        /** SQL Table */
        $view->sqlTable($this->sql_table);

        /** Table definition (index view) */
        $view->table("ID", "id")
            ->table("UUID", "uuid")
            ->table("Name", "name")
            ->table("Email", "email")
            ->table("Created", "created_at");

        /** Filters */
        $view->filterLink("Me", "id = $user->id")
            ->filterLink("Others", "id != $user->id")
            ->filterLink("All", "1=1");

        /** Table formatting */
        $view->tableFormat("created_at", "ago");

        /** Table searching */
        $view->addSearch("name")
            ->addSearch("email");

        /** Form definition (edit & create view) */
        $view->form("Name", "name")
            ->form("Email", "email");

        parent::configure($view);
    }
}
