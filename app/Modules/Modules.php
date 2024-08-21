<?php

namespace App\Modules;

use App\Modules\Module;
use Helios\View\View;

class Modules extends Module
{
    public function configure(View $view)
    {
        $view->sqlTable('modules');

        $view->table("ID", "id")
            ->table("Enabled", "enabled")
            ->table("Title", "title")
            ->table("Max Permission", "(SELECT name FROM user_types WHERE permission_level = max_permission_level) as max_permission")
            ->table("Created", "created_at");

        $view->filterLink("Root", "parent_module_id IS NULL")
            ->filterLink("Children", "parent_module_id IS NOT NULL")
            ->filterLink("All", "1=1");

        $view->tableFormat("created_at", "ago");

        parent::configure($view);
    }
}
