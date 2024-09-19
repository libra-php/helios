<?php

namespace App\Modules;

use App\Models\Audit as AuditModel;
use Helios\Module\Module;

class Audit extends Module
{
    protected string $model = AuditModel::class;

    public function __construct()
    {
        $this->has_create = $this->has_edit = $this->has_delete = false;

        $this->table("ID", "id")
            ->table("User", "(SELECT name 
                FROM users 
                WHERE id = user_id) as user")
            ->table("Table", "table_name")
            ->table("Table ID", "table_id")
            ->table("Field", "field")
            ->table("Diff", "id as audit_diff")
            ->table("Tag", "tag")
            ->table("Created", "created_at")
            ->table(null, "old_value")
            ->table(null, "new_value");

        $this->format("audit_diff", "auditDiff")
             ->format("created_at", "ago");

        $this->search("user")->search("new_value")->search("old_value");

        $this->defaultOrder("id")
            ->defaultSort("DESC");

        $this->filterLink("Create", "tag = 'CREATE'")
            ->filterLink("Update", "tag = 'UPDATE'")
            ->filterLink("Delete", "tag = 'DELETE'")
            ->filterLink("All", "1=1");
    }
}
