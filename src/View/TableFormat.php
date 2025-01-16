<?php

namespace Helios\View;

use Carbon\Carbon;

trait TableFormat
{
    protected array $table_format = [];

    /**
     * Table formatting
     */
    protected function format(
        mixed $type,
        string $column,
        ?string $value = null
    ): mixed {
        if (is_null($type)) {
            return $value;
        }
        if (is_callable($type)) {
            return $type($column, $value);
        } elseif (method_exists($this, $type)) {
            return call_user_func([$this, $type], $column, $value);
        } else {
            throw new \Error("format type does not exist: $type");
        }
    }

    protected function ago(string $column, string $value): string
    {
        $ago = Carbon::parse($value)->diffForHumans();
        $opts = [
            "id" => "format-$column",
            "class" => "format",
            "value" => $ago,
        ];
        return template("admin/module/format/span.html", $opts);
    }
}
