<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
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
        "Created" => "created_at",
    ];
    protected array $table_format = [
        "created_at" => "ago",
    ];
    protected array $filter_links = [
        "All" => "1 = 1",
        "Is 7" => "number = 7",
        "Over 5" => "number > 5",
    ];
    protected array $searchable = [
        "name",
        "number"
    ];

    protected array $form_columns = [
        "Name" => "name",
        "Number" => "number",
        "Checkbox" => "checked",
        "Comment" => "comment",
        "ID" => "id",
    ];
    protected array $form_controls = [
        "name" => "input",
        "number" => "number",
        "checked" => "switch",
        "comment" => "textarea"
    ];
    protected array $validation_rules = [
        "name" => ["required"],
        "number" => ["required"],
        "comment" => ["required"],
        "checked" => [],
    ];
}

