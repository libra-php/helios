<?php

namespace Helios\View;

/**
 * @class Table
 * The table view
 */
class Table extends View
{
    protected string $template = "admin/module/table.html";

    public function format(string $column, mixed $value): string
    {
        $table = $this->module->getTable();
        $format = $this->module->getFormat();
        $options = [
            "title" => array_search($column, $table),
        ];
        if (isset($format[$column])) {
            // A format column is set
            $callback = $format[$column];
            if (is_callable($callback)) {
                // The callback method is the value
                return $callback($column, $value, $options);
            } else if (is_string($callback) && method_exists($this->module::class, $callback)) {
                // The module callback method is the value
                return $this->module->$callback($column, $value, $options);
            } else if (
                is_string($callback) &&
                method_exists(Format::class, $callback)
            ) {
                // The format callback is the value
                return Format::$callback($column, $value, $options);
            }
        }
        return Format::span($column, $value, $options);
    }

    public function hasRowEdit(?int $id): bool
    {
        return $this->module->hasEditPermission($id);
    }

    public function hasRowDelete(?int $id): bool
    {
        return $this->module->hasDeletePermission($id);
    }

    public function getTemplateData(): array
    {
        return [
            ...parent::getTemplateData(),
            "table" => $this->module->getTable(),
            "actions" => $this->module->getActions(),
            "pagination" => $this->module->getPagination(),
            "filters" => $this->module->getFilters(),
        ];
    }
}
