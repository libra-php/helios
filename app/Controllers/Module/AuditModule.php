<?php

namespace App\Controllers\Module;

use App\Models\Audit;
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
            "Diff" => "id as diff",
            "Tag" => "tag",
            "Created" => "created_at",
        ];
        $this->table_format = [
            "created_at" => "ago",
            "diff" => "diff",
        ];
        $this->searchable = [
            "(SELECT username FROM users WHERE id = user_id)",
            "table_name",
            "table_id",
            "field",
            "tag",
            "old_value",
            "new_value",
        ];
        $this->filter_links = [
            "All" => "1=1",
            "Me" => "user_id=" . user()->id,
            "Create" => "tag='CREATE'",
            "Update" => "tag='UPDATE'",
            "Delete" => "tag='DELETE'",
        ];
        $this->default_sort = "DESC";
    }

    function diff(string $column, string $value): string
    {
        $audit = Audit::find($value);
        $old = $audit->old_value ?? 'null';
        $new = $audit->new_value ?? 'null';
        return template("admin/module/format/diff.html", [
            "diff" => $this->getDiff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new)),
        ]);
    }

    function getDiff(array $old, array $new): array
    {
        $matrix = array();
        $maxlen = 0;
        foreach ($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                    $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if ($matrix[$oindex][$nindex] > $maxlen) {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if ($maxlen == 0) return array(array('d' => $old, 'i' => $new));
        return array_merge(
            $this->getDiff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            $this->getDiff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen))
        );
    }
}
