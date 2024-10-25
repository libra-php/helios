<?php

namespace App\Modules;

use App\Models\Module as ModuleModel;
use Composer\ClassMapGenerator\ClassMapGenerator;
use Helios\Module\Module;
use Helios\View\Control;

class Modules extends Module
{
    protected string $model = ModuleModel::class;

    public function __construct()
    {
        $this->rules = [
            "enabled" => [],
            "title" => ["required"],
            "module_class" => ["class_exists"],
            "item_order" => [],
            "user_role_id" => [],
            "parent_module_id" => [],
        ];

        $this->table("ID", "id")
            ->table("Enabled", "enabled")
            ->table("Title", "title")
            ->table("User Role", "(SELECT name 
                FROM user_roles 
                WHERE id = user_role_id) as user_role")
            ->table("Created", "created_at");

        $this->filterLink("Root", "parent_module_id IS NULL")
            ->filterLink("Children", "parent_module_id IS NOT NULL")
            ->filterLink("All", "1=1");

        $this->format("created_at", "ago")
             ->format("enabled", "check")
             ->format("user_role", fn($column, $value) => !$value ? 'n/a' : $value);

        $this->form("Enabled", "enabled")
            ->form("Title", "title")
            ->form("Module Class", "module_class")
            ->form("Item Order", "item_order")
            ->form("User Role", "user_role_id")
            ->form("Parent Module", "parent_module_id");

        $this->control("enabled", "switch")
            ->control("item_order", "number")
            ->control("module_class", function ($column, $value, $options) {
                $modules_path = config("paths.modules");
                $map = ClassMapGenerator::createMap($modules_path);
                foreach ($map as $class => $_) {
                    $options['options'][] = [
                        "label" => $class,
                        "value" => $class,
                    ];
                }
                return Control::_select($column, $value, $options);
            })
            ->control("item_order", "number")
            ->control("parent_module_id", db()->fetchAll("SELECT id as value, title as label 
                FROM modules 
                ORDER BY title"))
            ->control("user_role_id", db()->fetchAll("SELECT id value, name as label 
                FROM user_roles 
                ORDER BY name"));

        $this->default("enabled", 1);
        $this->default("item_order", 0);
    }

    public function hasDeletePermission(?int $id): bool
    {
        return $id > 7;
    }
}
