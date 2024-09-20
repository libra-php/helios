<?php

namespace Helios\View;

use App\Models\Audit;
use Carbon\Carbon;

class Format
{
    public static function _ago(string $column, mixed $value, array $options)
    {
        $ago = Carbon::parse($value)->diffForHumans();
        $options['title'] = $options['title'] . ': ' .$value;
        return self::_span($column, $ago, $options);
    }

    public static function _check(string $column,  mixed $value, array $options)
    {
        return template("components/format/check.html", [
            ...$options,
            "column" => $column,
            "value" => $value,
        ]);
    }

    public static function _span(string $column,  mixed $value, array $options)
    {
        return template("components/format/span.html", [
            ...$options,
            "column" => $column,
            "value" => $value,
        ]);
    }

    private static function _diff(mixed $old, mixed $new): array
    {
        $matrix = array();
        $maxlen = 0;
        foreach ($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) 
                    ? $matrix[$oindex - 1][$nindex - 1] + 1 
                    : 1;
                if ($matrix[$oindex][$nindex] > $maxlen) {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if ($maxlen == 0) return array(array('d' => $old, 'i' => $new));
        return array_merge(
            self::_diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            self::_diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen))
        );
    }

    public static function _auditDiff(string $column,  mixed $value, array $options)
    {
        // The value is an audit id
        $record = Audit::find($value);
        if (is_null($record->old_value)) {
            $record->old_value = 'null';
        }
        if (is_null($record->new_value)) {
            $record->new_value = 'null';
        }
        $diff = self::_diff(preg_split("/[\s]+/", $record->old_value), preg_split("/[\s]+/", $record->new_value));
        return template("components/format/diff.html", ["diff" => $diff]);
    }
}
