<?php

namespace App\Modules;

use App\Models\Module;
use Helios\View\{Format, View};

class Users extends Module
{
    public function configure(View $view)
    {
        $user = user();

        /** SQL Table */
        $view->sqlTable($this->sql_table);

        /** Table definition (index view) */
        $view->addTable("ID", "id")
            ->addTable("UUID", "uuid")
            ->addTable("Name", "name")
            ->addTable("Email", "email")
            ->addTable("Created", "created_at");

        /** Filters */
        $view->filterLink("Me", "id = $user->id")
            ->filterLink("Others", "id != $user->id")
            ->filterLink("All", "1=1");

        /** Table formatting */
        $view->tableFormat("created_at", fn ($column, $value) => Format::ago($column, $value));

        /** Table searching */
        $view->addSearch("name")
            ->addSearch("email");

        /** Form definition (edit & create view) */
        $view->addForm("Name", "email")
            ->addForm("Email", "email");

        parent::configure($view);
    }
}
