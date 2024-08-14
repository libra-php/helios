<?php

namespace App\Modules;

use App\Models\Module;
use Helios\View\{Format, View};

class Modules extends Module
{
    public function configure(View $view)
    {
        $view->sqlTable($this->sql_table);

        $view->addTable("ID", "id")
            ->addTable("Enabled", "enabled")
            ->addTable("Title", "title")
            ->addTable("Max Permission", "(SELECT name FROM user_types WHERE permission_level = max_permission_level) as max_permission")
            ->addTable("Created", "created_at");

        $view->filterLink("Root", "parent_module_id IS NULL")
            ->filterLink("Children", "parent_module_id IS NOT NULL")
            ->filterLink("All", "1=1");

        $view->tableFormat("created_at", fn ($column, $value) => Format::ago($column, $value));

        parent::configure($view);
    }
}
