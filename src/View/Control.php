<?php

namespace Helios\View;

class Control
{
    public static function input(string $column,  mixed $value, array $options = [])
    {
        return template("components/control/input.html", [
            "column" => $column,
            "value" => $value,
            ...$options
        ]);
    }
}
