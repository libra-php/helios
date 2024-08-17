<?php

namespace App\Modules;

use App\Models\Module;
use Helios\View\{Format, View};

class UserTypes extends Module
{
    public function configure(View $view)
    {
        $view->sqlTable($this->sql_table);

        $view->addTable("ID", "id")
            ->addTable("Name", "name")
            ->addTable("Permission Level", "permission_level")
            ->addTable("Created", "created_at");

        $view->tableFormat("created_at", fn($column, $value) => Format::ago($column, $value));

        $view->addForm("Name", "name")
            ->addForm("Permission Level", "permission_level");

        parent::configure($view);
    }
}
