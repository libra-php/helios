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
            ->table("User", "(SELECT name FROM users WHERE id = user_id) as name")
            ->table("Table", "table_name")
            ->table("Table ID", "table_id")
            ->table("Field", "field")
            ->table("Old", "old_value")
            ->table("New", "new_value")
            ->table("Tag", "tag");

        $this->search("user");

        $this->defaultOrder("id")
            ->defaultSort("DESC");

        $user = user();
        $this->filterLink("Me", "user_id = $user->id")
            ->filterLink("Others", "user_id != $user->id")
            ->filterLink("All", "1=1");
    }
}
