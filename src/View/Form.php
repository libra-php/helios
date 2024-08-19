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

    public function __construct(private ?int $id = null)
    {
        if (!is_null($id)) {
            $pk = $this->primary_key;
            $this->addClause($this->where, "$pk = ?", $this->id);
        }
    }

    public function processRequest(): void {}

    public function getData(): array
    {
        return [
            ...parent::getData(),
            "id" => $this->id,
            "form" => $this->form,
            "data" => $this->control($this->getPayload()),
            "actions" => [],
        ];
    }

    private function defaultPayload()
    {
        $payload = [];
        foreach ($this->form as $title => $column) {
            $payload[$column] = null;
        }
        return $payload;
    }

    private function control(array $data): array
    {
        foreach ($data as $column => $value) {
            if (isset($this->control[$column])) {
                // A control column is set
                $callback = $this->control[$column];
                $module_class = request()->get("module")->class_name;
                if (is_callable($callback)) {
                    // The callback method is the value
                    $data[$column] = $callback($column, $value);
                } else if (is_string($callback) && method_exists($module_class, $callback)) {
                    // The module static callback method is the value
                    $data[$column] = $module_class::$callback($column, $value);
                } else if (
                    is_string($callback) &&
                    method_exists(Control::class, $callback)
                ) {
                    // The control class callback is the value
                    $data[$column] = Control::$callback($column, $value);
                }
            } else {
                $data[$column] = Control::input($column, $value);
            }
        }
        return $data;
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
