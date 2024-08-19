<?php

namespace Helios\View;

use Carbon\Carbon;

class Format
{
    public static function ago(string $column, mixed $value, array $options = [])
    {
        $ago = Carbon::parse($value)->diffForHumans();
        return self::span($column, $ago, $options);
    }

    public static function span(string $column,  mixed $value, array $options = [])
    {
        return template("components/format/span.html", [
            "column" => $column,
            "value" => $value,
            ...$options,
        ]);
    }
}
