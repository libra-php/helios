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
            ->table("Diff", "id as diff")
            ->table("Tag", "tag");

        $this->format("diff", function (string $column, string $value) {
            $record = AuditModel::find($value);
            return $this->htmlDiff($record->old_value ?? '', $record->new_value ?? '');
        });

        $this->search("user");

        $this->defaultOrder("id")
            ->defaultSort("DESC");

        $this->filterLink("Create", "tag = 'CREATE'")
            ->filterLink("Update", "tag = 'UPDATE'")
            ->filterLink("Delete", "tag = 'DELETE'")
            ->filterLink("All", "1=1");
    }

    function diff($old, $new): array
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
            $this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            $this->diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen))
        );
    }

    function htmlDiff($old, $new): string
    {
        $ret = '<div class="table-diff">';
        $diff = $this->diff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
        foreach ($diff as $k) {
            if (is_array($k))
                $ret .= (!empty($k['d']) ? "<span title='Removed' class='diff-remove'>" . implode(' ', $k['d']) . "</span> " : '') .
                    (!empty($k['i']) ? "<span title='Added' class='diff-add'>" . implode(' ', $k['i']) . "</span> " : '');
            else $ret .= $k . ' ';
        }
        $ret .= '</div>';
        return $ret;
    }
}
