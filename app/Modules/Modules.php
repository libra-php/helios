<?php

namespace App\Modules;

use App\Models\Module as ModuleModel;
use Helios\Module\Module;

class Modules extends Module
{
    protected string $model = ModuleModel::class;

    public function __construct()
    {
        $this->addTable("ID", "id")
            ->addTable("Enabled", "enabled")
            ->addTable("Title", "title")
            ->addTable("Max Permission", "(SELECT name FROM user_types WHERE permission_level = max_permission_level) as max_permission")
            ->addTable("Created", "created_at");

        $this->filterLink("Root", "parent_module_id IS NULL")
            ->filterLink("Children", "parent_module_id IS NOT NULL")
            ->filterLink("All", "1=1");

        $this->formatTable("created_at", "ago");
    }
}
