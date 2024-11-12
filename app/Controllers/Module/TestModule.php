<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

#[Group(prefix: "/admin/test", middleware: ["module" => "test"])]
class TestModule extends ModuleController
{
    protected string $module_title = "Test";
    protected string $module_parent = "Administration";
    protected string $module_route = "test";

    protected string $table = "test";
    protected array $table_columns = [
        "ID" => "id",
        "Name" => "name",
        "Number" => "number",
    ];
}

