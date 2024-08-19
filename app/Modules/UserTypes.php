<?php

namespace App\Modules;

use App\Models\Module;
use Helios\View\{Format, View};

class UserTypes extends Module
{
    public function configure(View $view)
    {
        $view->sqlTable($this->sql_table);

        $view->table("ID", "id")
            ->table("Name", "name")
            ->table("Permission Level", "permission_level")
            ->table("Created", "created_at");

        $view->tableFormat("created_at", "ago");

        $view->form("Name", "name")
            ->form("Permission Level", "permission_level");

        parent::configure($view);
    }
}
