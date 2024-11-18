<?php

namespace Helios\View;


trait FormControls
{
    protected array $form_controls = [];

    /**
     * Form controls
     */
    protected function control(mixed $type, string $label, string $column, ?string $value = null): mixed
    {
        if (is_callable($type)) {
            return $type($label, $column, $value);
        } else if (method_exists($this, $type)) {
            return call_user_func([$this, $type], $label, $column, $value);
        } else {
            throw new \Error("control type does not exist: $type");
        }
    }

    protected function input(string $label, string $column, ?string $value): string
    {
        $opts = [
            'id' => "control-$column",
            'class' => 'form-control',
            'type' => 'input',
            'name' => $column,
            'value' => $value,
            'title' => $label,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function number(string $label, string $column, ?string $value): string
    {
        $opts = [
            'id' => "control-$column",
            'class' => 'form-control',
            'type' => 'number',
            'name' => $column,
            'value' => $value,
            'title' => $label,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function email(string $label, string $column, ?string $value): string
    {
        $opts = [
            'id' => "control-$column",
            'class' => 'form-control',
            'type' => 'email',
            'name' => $column,
            'value' => $value,
            'title' => $label,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function hidden(string $label, string $column, ?string $value): string
    {
        $opts = [
            'id' => "control-$column",
            'class' => 'form-control',
            'type' => 'hidden',
            'name' => $column,
            'value' => $value,
            'title' => $label,
            'readonly' => true,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function readonly(string $label, string $column, ?string $value): string
    {
        $opts = [
            'id' => "control-$column",
            'class' => 'form-control',
            'type' => 'input',
            'name' => $column,
            'value' => $value,
            'title' => $label,
            'readonly' => true,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function password(string $label, string $column, ?string $value): string
    {
        $opts = [
            'id' => "control-$column",
            'class' => 'form-control',
            'type' => 'password',
            'name' => $column,
            'value' => $value,
            'title' => $label,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function checkbox(string $label, string $column, ?string $value): string
    {
        $opts = [
            'id' => "control-$column",
            'class' => 'form-check-input',
            'type' => 'checkbox',
            'name' => $column,
            'title' => $label,
            'value' => true,
            'checked' => $value == 1,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function switch(string $label, string $column, ?string $value): string
    {
        return template("admin/module/controls/switch.html", [
            "checkbox" => $this->checkbox($label, $column, $value),
        ]);
    }

    protected function textarea(string $label, string $column, ?string $value): string
    {
        $opts = [
            'id' => "control-$column",
            'class' => 'form-control',
            'name' => $column,
            'title' => $label,
            'rows' => 10,
            'value' => $value,
        ];
        return template("admin/module/controls/textarea.html", $opts);
    }
}
