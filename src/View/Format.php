<?php

namespace Helios\View;

use App\Models\Audit;
use Carbon\Carbon;

class Format
{
    public static function ago(string $column, mixed $value, array $options)
    {
        $ago = Carbon::parse($value)->diffForHumans();
        return self::span($column, $ago, $options);
    }

    public static function span(string $column,  mixed $value, array $options)
    {
        return template("components/format/span.html", [
            ...$options,
            "column" => $column,
            "value" => $value,
        ]);
    }

    private static function diff(mixed $old, mixed $new): array
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
            self::diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            self::diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen))
        );
    }

    public static function auditDiff(string $column,  mixed $value, array $options)
    {
        // The value is an audit id
        $record = Audit::find($value);
        $diff = self::diff(preg_split("/[\s]+/", $record->old_value ?? ''), preg_split("/[\s]+/", $record->new_value ?? ''));
        return template("components/format/diff.html", ["diff" => $diff]);
    }
}
