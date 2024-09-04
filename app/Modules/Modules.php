<?php

namespace App\Modules;

use App\Models\Module as ModuleModel;
use Helios\Module\Module;

class Modules extends Module
{
    protected string $model = ModuleModel::class;

    protected array $rules = [
        "enabled" => [],
        "title" => ["required"],
        "module_class" => [],
        "item_order" => [],
        "max_permission_level" => [],
        "parent_module_id" => [],
    ];

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

        $this->form("Enabled", "enabled")
            ->form("Title", "title")
            ->form("Module Class", "module_class")
            ->form("Item Order", "item_order")
            ->form("Max Permission Level", "max_permission_level")
            ->form("Parent Module", "parent_module_id");

        $this->control("enabled", "checkbox")
            ->control("module_class", "select")
            ->control("item_order", "number")
            ->control("max_permission_level", "select")
            ->control("parent_module_id", "select");

        $this->default("item_order", 0);
    }

    public function hasDeletePermission(?int $id): bool
    {
        return $id > 8;
    }
}
