<?php

namespace Helios\View;

use PDO;

/**
 * @class Form
 * The form view
 */
class Form extends View
{
    public string $template = "admin/module/form.html";

    public function __construct(protected ?int $id = null)
    {
        if (!is_null($id)) {
            $pk = $this->primary_key;
            $this->addClause($this->where, "$pk = ?", $this->id);
        }
    }

    public function processRequest(): void {}

    public function getData(): array
    {
        $data = $this->getPayload();
        return [
            ...parent::getData(),
            "id" => $this->id,
            "form" => $this->form,
            "data" => $data,
            "actions" => [],
        ];
    }

    public function control(string $column, mixed $value): string
    {
        $module_class = request()->get("module")->class_name;
        $options = [
            "title" => array_search($column, $this->form),
        ];
        if (isset($this->control[$column])) {
            // A control column is set
            $callback = $this->control[$column];
            if (is_callable($callback)) {
                // The callback method is the value
                return $callback($column, $value, $options);
            } else if (is_string($callback) && method_exists($module_class, $callback)) {
                // The module static callback method is the value
                return $module_class::$callback($column, $value, $options);
            } else if (
                is_string($callback) &&
                method_exists(Control::class, $callback)
            ) {
                // The control class callback is the value
                return Control::$callback($column, $value, $options);
            }
        }
        return Control::input($column, $value, $options);
    }

    private function defaultPayload()
    {
        $payload = [];
        foreach ($this->form as $title => $column) {
            $payload[$column] = null;
        }
        return $payload;
    }

    protected function getQuery(): string
    {
        return sprintf(
            "SELECT %s FROM %s %s",
            $this->getSelect($this->form),
            $this->getSqlTable(),
            $this->getWhere()
        );
    }

    protected function getPayload(): array|false
    {
        if (empty($this->form)) return false;
        if (isset($this->id) && $this->id) {
            $sql = $this->getQuery();
            $params = $this->getAllParams();
            $stmt = db()->run($sql, $params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $this->defaultPayload();
    }
}
