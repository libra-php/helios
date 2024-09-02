<?php

namespace App\Modules;

use App\Models\Module as ModuleModel;
use Helios\Module\Module;

class Modules extends Module
{
    protected string $model = ModuleModel::class;

    public function __construct()
    {
        $this->table("ID", "id")
            ->table("Enabled", "enabled")
            ->table("Title", "title")
            ->table("Max Permission", "(SELECT name FROM user_types WHERE permission_level = max_permission_level) as max_permission")
            ->table("Created", "created_at");

        $this->filterLink("Root", "parent_module_id IS NULL")
            ->filterLink("Children", "parent_module_id IS NOT NULL")
            ->filterLink("All", "1=1");

        $this->format("created_at", "ago");
    }

    public function hasDeletePermission(?int $id): bool
    {
        return $id > 8;
    }
}
