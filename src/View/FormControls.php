<?php

namespace Helios\View;

use App\Models\File;

trait FormControls
{
    protected array $form_controls = [];

    /**
     * Form controls
     */
    protected function control(mixed $type, array $opts = []): mixed
    {
        if (is_callable($type)) {
            return $type($opts);
        } elseif (method_exists($this, $type)) {
            return call_user_func([$this, $type], $opts);
        } else {
            throw new \Error("control type does not exist: $type");
        }
    }

    protected function input(array $opts): string
    {
        if (!isset($opts["type"])) {
            $opts["type"] = "input";
        }
        return template("admin/module/controls/input.html", $opts);
    }

    protected function button(array $opts): string
    {
        $opts["type"] = "button";
        return $this->input($opts);
    }

    protected function color(array $opts): string
    {
        $opts["type"] = "color";
        return $this->input($opts);
    }

    protected function month(array $opts): string
    {
        $opts["type"] = "month";
        return $this->input($opts);
    }

    protected function week(array $opts): string
    {
        $opts["type"] = "week";
        return $this->input($opts);
    }

    protected function url(array $opts): string
    {
        $opts["type"] = "url";
        return $this->input($opts);
    }

    protected function date(array $opts): string
    {
        $opts["type"] = "date";
        return $this->input($opts);
    }

    protected function time(array $opts): string
    {
        $opts["type"] = "time";
        return $this->input($opts);
    }

    protected function datetime(array $opts): string
    {
        $opts["type"] = "datetime-local";
        return $this->input($opts);
    }

    protected function number(array $opts): string
    {
        $opts["type"] = "number";
        return $this->input($opts);
    }

    protected function email(array $opts): string
    {
        $opts["type"] = "email";
        return $this->input($opts);
    }

    protected function hidden(array $opts): string
    {
        $opts["type"] = "hidden";
        return $this->input($opts);
    }

    protected function readonly(array $opts): string
    {
        $opts["type"] = "input";
        $opts["readonly"] = true;
        return $this->input($opts);
    }

    protected function password(array $opts): string
    {
        $opts["type"] = "password";
        return $this->input($opts);
    }

    protected function checkbox(array $opts): string
    {
        $opts["class"] = "form-check-input";
        $opts["checked"] = $opts["value"] == 1 || $opts["value"] == "on";
        $opts["type"] = "checkbox";
        return $this->input($opts);
    }

    protected function switch(array $opts): string
    {
        return template("admin/module/controls/switch.html", [
            "checkbox" => $this->checkbox($opts),
        ]);
    }

    protected function textarea(array $opts): string
    {
        $opts["rows"] = $opts["rows"] ?? 10;
        return template("admin/module/controls/textarea.html", $opts);
    }

    protected function editor(array $opts): string
    {
        $opts["rows"] = 30;
        $textarea = $this->textarea($opts);
        $opts["textarea"] = $textarea;
        return template("admin/module/controls/editor.html", $opts);
    }

    protected function select(array $opts): string
    {
        return template("admin/module/controls/select.html", $opts);
    }

    protected function upload(array $opts): string
    {
        $opts["type"] = "file";
        $input = $this->input($opts);
        $opts["input"] = $input;
        if ($opts["value"]) {
            $file = File::find($opts["value"]);
            $opts["file_id"] = $file->id;
            $opts["bytes"] = $file->bytes;
            $opts["mime"] = $file->mime;
            $opts["path"] = "/uploads/{$file->name}";
            $opts["name"] = $file->name;
        }
        return template("admin/module/controls/upload.html", $opts);
    }

    protected function image(array $opts): string
    {
        $opts["type"] = "file";
        $input = $this->input($opts);
        $opts["input"] = $input;
        if ($opts["value"]) {
            $file = File::find($opts["value"]);
            $opts["file_id"] = $file->id;
            $opts["bytes"] = $file->bytes;
            $opts["mime"] = $file->mime;
            $opts["path"] = "/uploads/{$file->name}";
        }
        return template("admin/module/controls/image.html", $opts);
    }
}
