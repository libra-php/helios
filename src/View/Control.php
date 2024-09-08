<?php

namespace Helios\View;

class Control
{
    public static function input(string $column,  mixed $value, array $options)
    {
        return template("components/control/input.html", [
            "class" => "form-control control",
            "column" => $column,
            "value" => $value,
            ...$options
        ]);
    }

    public static function checkbox(string $column, mixed $value, array $options)
    {
        $hidden = self::input($column, $value ? 1 : 0, [
            "type" => "hidden",
        ]);
        $checkbox = self::input("", $value, [
            ...$options,
            "type" => "checkbox",
            "class" => "form-check-input",
            "onchange" => "toggleCheckbox(event)",
            "attrs" => $value ? "checked" : '',
        ]);
        return $hidden . $checkbox;
    }

    public static function select(string $column, mixed $value, array $options)
    {
        return template("components/control/select.html", [
            "class" => "form-select control",
            "column" => $column,
            "value" => $value,
            ...$options
        ]);
    }
}
