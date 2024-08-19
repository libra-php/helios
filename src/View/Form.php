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

    public function __construct(?int $id = null)
    {
        if (!is_null($id)) {
            $this->id = $id;
            $pk = $this->primary_key;
            $this->addClause($this->where, "$pk = ?", $this->id);
        }
    }

    public function processRequest(): void {}

    public function getData(): array
    {
        return [
            ...parent::getData(),
            "actions" => [],
            "form" => $this->control($this->form),
            "data" => $this->getPayload(),
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
