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
            "actions" => [],
            "form" => $this->getPayload(),
        ];
    }

    protected function getQuery(): string
    {
        return sprintf("SELECT %s FROM %s %s",
            $this->getSelect($this->form),
            $this->getSqlTable(),
            $this->getWhere()
        );
    }

    protected function getQueryResult(): array|false
    {
        if (isset($this->id) && $this->id) {
            $sql = $this->getQuery();
            $params = $this->getAllParams();
            $stmt = db()->run($sql, $params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = array_filter($this->getSqlColumns(), fn($column) => in_array($column, $this->form));
        }
        dd($result);
        return $result;
    }

    protected function getPayload(): array|false
    {
        if (empty($this->form)) return false;
        return $this->getQueryResult();
    }
}
