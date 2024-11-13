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

    protected array $filter_links = [
        "All" => "1 = 1",
        "Is 7" => "number = 7",
        "Over 5" => "number > 5",
    ];

    protected array $form_columns = [
        "Name" => "name",
        "Number" => "number",
    ];
}

