<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/test", middleware: ["module" => "test"])]
class TestModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->table = "test";
        $this->module_title = "Test";
        $this->module_parent = "Administration";

        $this->table_columns = [
            "Name" => "name",
            "Number" => "number",
            "Created" => "created_at",
        ];
        $this->table_format = [
            "created_at" => "ago",
        ];
        $this->filter_links = [
            "All" => "1=1",
            "Is 7" => "number=7",
            "Over 5" => "number>5",
        ];
        $this->searchable = [
            "name",
            "number"
        ];

        $this->form_columns = [
            "Name" => "name",
            "Number" => "number",
            "Checkbox" => "checked",
            "Comment" => "comment",
        ];
        $this->form_controls = [
            "name" => "input",
            "number" => "number",
            "checked" => "switch",
            "comment" => "textarea"
        ];
        $this->validation_rules = [
            "name" => ["required"],
            "number" => ["required"],
            "comment" => ["required"],
            "checked" => [],
        ];
    }
}
