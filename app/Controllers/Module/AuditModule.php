<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/audit", middleware: ["module" => "audit"])]
class AuditModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->table = "audit";
        $this->module_title = "Audit Log";
        $this->module_parent = "Administration";
        $this->table_columns = [
            "ID" => "id",
            "User" => "(SELECT username FROM users WHERE id = user_id) as user",
            "Table" => "table_name",
            "Table ID" => "table_id",
            "Field" => "field",
            "Old Value" => "old_value",
            "New Value" => "new_value",
            "Tag" => "tag",
            "Created" => "created_at",
        ];
        $this->table_format = [
            "created_at" => "ago",
        ];
        $this->filter_links = [
            "All" => "1=1",
            "Me" => "user_id=" . user()->id,
            "Create" => "tag='CREATE'",
            "Update" => "tag='UPDATE'",
            "Delete" => "tag='DELETE'",
        ];
    }
}
