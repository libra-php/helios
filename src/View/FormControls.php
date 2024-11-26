<?php

namespace Helios\View;


trait FormControls
{
    protected array $form_controls = [];

    /**
     * Form controls
     */
    protected function control(mixed $type, string $label, string $column, ?string $value = null, array $opts = []): mixed
    {
        if (is_callable($type)) {
            return $type($label, $column, $value, $opts);
        } else if (method_exists($this, $type)) {
            return call_user_func([$this, $type], $label, $column, $value, $opts);
        } else {
            throw new \Error("control type does not exist: $type");
        }
    }

    protected function input(string $label, string $column, ?string $value, array $opts = []): string
    {
        $opts = [
            'id' => $opts["id"] ?? '',
            'class' => $opts["class"] ?? '',
            'type' => 'input',
            'name' => $column,
            'value' => $value,
            'title' => $label,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function number(string $label, string $column, ?string $value, array $opts = []): string
    {
        $opts = [
            'id' => $opts["id"] ?? '',
            'class' => $opts["class"] ?? '',
            'type' => 'number',
            'name' => $column,
            'value' => $value,
            'title' => $label,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function email(string $label, string $column, ?string $value, array $opts = []): string
    {
        $opts = [
            'id' => $opts["id"] ?? '',
            'class' => $opts["class"] ?? '',
            'type' => 'email',
            'name' => $column,
            'value' => $value,
            'title' => $label,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function hidden(string $label, string $column, ?string $value, array $opts = []): string
    {
        $opts = [
            'id' => $opts["id"] ?? '',
            'class' => $opts["class"] ?? '',
            'type' => 'hidden',
            'name' => $column,
            'value' => $value,
            'title' => $label,
            'readonly' => true,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function readonly(string $label, string $column, ?string $value, array $opts = []): string
    {
        $opts = [
            'id' => $opts["id"] ?? '',
            'class' => $opts["class"] ?? '',
            'type' => 'input',
            'name' => $column,
            'value' => $value,
            'title' => $label,
            'readonly' => true,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function password(string $label, string $column, ?string $value, array $opts = []): string
    {
        $opts = [
            'id' => $opts["id"] ?? '',
            'class' => $opts["class"] ?? '',
            'type' => 'password',
            'name' => $column,
            'value' => $value,
            'title' => $label,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function checkbox(string $label, string $column, ?string $value, array $opts = []): string
    {
        $opts = [
            'id' => $opts["id"] ?? '',
            'class' => 'form-check-input',
            'type' => 'checkbox',
            'name' => $column,
            'title' => $label,
            'value' => true,
            'checked' => $value == 1,
        ];
        return template("admin/module/controls/input.html", $opts);
    }

    protected function switch(string $label, string $column, ?string $value, array $opts = []): string
    {
        return template("admin/module/controls/switch.html", [
            "checkbox" => $this->checkbox($label, $column, $value),
        ]);
    }

    protected function textarea(string $label, string $column, ?string $value, array $opts = []): string
    {
        $opts = [
            'id' => $opts["id"] ?? '',
            'class' => $opts["class"] ?? '',
            'name' => $column,
            'title' => $label,
            'rows' => 10,
            'value' => $value,
        ];
        return template("admin/module/controls/textarea.html", $opts);
    }
}
