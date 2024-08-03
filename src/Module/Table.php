<?php

namespace Helios\Module;

use PDO;

class Table extends View
{
    public string $template = "admin/module/table.html";

    protected function getQuery(): string
    {
        $select = array_values($this->table);
        $columns = implode(", ", $select);
        return sprintf("SELECT $columns FROM %s", $this->sql_table);
    }

    protected function getResult()
    {
        $sql = $this->getQuery();
        return db()->run($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getData(): array
    {
        return [
            "table" => [
                "columns" => array_keys($this->table),
                "rows" => $this->getResult(),
            ]
        ];
    }
}
