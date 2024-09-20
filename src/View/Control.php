<?php

namespace Helios\View;

use App\Models\File;

class Control
{
    public static function _input(string $column,  mixed $value, array $options)
    {
        return template("components/control/input.html", [
            ...$options,
            "class" => $options["class"] . " form-control",
            "column" => $column,
            "value" => $value,
        ]);
    }

    public static function _checkbox(string $column, mixed $value, array $options)
    {
        $hidden = self::_hidden($column, $value ? 1 : 0, $options);
        $checkbox = self::_input("", $value, [
            ...$options,
            "type" => "checkbox",
            "class" => $options["class"] . " form-check-input",
            "onchange" => "toggleCheckbox(event)",
            "checked" => $value ? "checked" : '',
        ]);
        return $hidden . $checkbox;
    }

    public static function _textarea(string $column, mixed $value, array $options)
    {
        return template("components/control/textarea.html", [
            ...$options,
            "class" => $options["class"] . " form-control",
            "column" => $column,
            "value" => $value,
            "rows" => $options["rows"] ?? 8
        ]);
    }

    public static function _switch(string $column, mixed $value, array $options)
    {
        return template("components/control/switch.html", [
            "input" => self::_checkbox($column, $value, [...$options, "class" => $options["class"] . " form-check-input"])
        ]);
    }

    public static function _number(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "number",
        ]);
    }

    public static function _color(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "color",
        ]);
    }

    public static function _date(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "date",
        ]);
    }

    public static function _datetime(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "datetime-local",
        ]);
    }

    public static function _email(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "email",
        ]);
    }

    public static function _file(string $column, mixed $value, array $options)
    {
        $input = self::_input($column, $value, [
            ...$options,
            "type" => "file",
        ]);
        $hidden = self::_input($column, $value, [
            ...$options,
            "type" => "hidden",
        ]);
        return template("components/control/file.html", [
            "input" => $input,
            "hidden" => $hidden,
            "file" => $value ? File::find($value) : null,
        ]);
    }

    public static function _image(string $column, mixed $value, array $options)
    {
        return '';
    }

    public static function _hidden(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "hidden",
        ]);
    }

    public static function _month(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "month",
        ]);
    }

    public static function _password(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "password",
        ]);
    }

    public static function _range(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "range",
        ]);
    }

    public static function _reset(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "reset",
        ]);
    }

    public static function _search(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "search",
        ]);
    }

    public static function _submit(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "submit",
        ]);
    }

    public static function _tel(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "tel",
        ]);
    }

    public static function _text(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "text",
        ]);
    }

    public static function _time(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "time",
        ]);
    }

    public static function _url(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "url",
        ]);
    }

    public static function _week(string $column, mixed $value, array $options)
    {
        return self::_input($column, $value, [
            ...$options,
            "type" => "week",
        ]);
    }

    public static function _select(string $column, mixed $value, array $options)
    {
        return template("components/control/select.html", [
            ...$options,
            "class" => $options["class"] . " form-select",
            "column" => $column,
            "value" => $value,
        ]);
    }
}
