<?php

namespace Helios\View;

class Control
{
    public static function input(string $column,  mixed $value, array $options)
    {
        return template("components/control/input.html", [
            ...$options,
            "class" => $options["class"] . " form-control",
            "column" => $column,
            "value" => $value,
        ]);
    }

    public static function checkbox(string $column, mixed $value, array $options)
    {
        $hidden = self::hidden($column, $value ? 1 : 0, $options);
        $checkbox = self::input("", $value, [
            ...$options,
            "type" => "checkbox",
            "class" => $options["class"] . " form-check-input",
            "onchange" => "toggleCheckbox(event)",
            "checked" => $value ? "checked" : '',
        ]);
        return $hidden . $checkbox;
    }

    public static function textarea(string $column, mixed $value, array $options)
    {
        return template("components/control/textarea.html", [
            ...$options,
            "class" => $options["class"] . " form-control",
            "column" => $column,
            "value" => $value,
            "rows" => $options["rows"] ?? 8
        ]);
    }

    public static function switch(string $column, mixed $value, array $options)
    {
        return template("components/control/switch.html", [
            "input" => self::checkbox($column, $value, [...$options, "class" => $options["class"] . " form-check-input"])
        ]);
    }

    public static function number(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "number",
        ]);
    }

    public static function color(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "color",
        ]);
    }

    public static function date(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "date",
        ]);
    }

    public static function datetime(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "datetime-local",
        ]);
    }

    public static function email(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "email",
        ]);
    }

    public static function file(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "file",
        ]);
    }

    public static function hidden(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "hidden",
        ]);
    }

    public static function image(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "image",
        ]);
    }

    public static function month(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "month",
        ]);
    }

    public static function password(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "password",
        ]);
    }

    public static function range(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "range",
        ]);
    }

    public static function reset(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "reset",
        ]);
    }

    public static function search(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "search",
        ]);
    }

    public static function submit(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "submit",
        ]);
    }

    public static function tel(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "tel",
        ]);
    }

    public static function text(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "text",
        ]);
    }

    public static function time(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "time",
        ]);
    }

    public static function url(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "url",
        ]);
    }

    public static function week(string $column, mixed $value, array $options)
    {
        return self::input($column, $value, [
            ...$options,
            "type" => "week",
        ]);
    }

    public static function select(string $column, mixed $value, array $options)
    {
        return template("components/control/select.html", [
            ...$options,
            "class" => $options["class"] . " form-select",
            "column" => $column,
            "value" => $value,
        ]);
    }
}
