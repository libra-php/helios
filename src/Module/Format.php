<?php

namespace Helios\Module;

use Carbon\Carbon;

class Format
{
    public static function ago($column, $value)
    {
        $carbon = Carbon::parse($value)->diffForHumans();
        return template("components/format/span.html", [
            "column" => $column,
            "value" => $carbon,
            "title" => $value,
        ]);
    }
}
