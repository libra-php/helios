<?php

namespace Helios\Module;

use PDO;

/**
 * @class Table
 * The table view
 */
class Table extends View
{
    public string $template = "admin/module/table.html";

    protected function getQuery(): string
    {
        $select = array_values($this->table);
        $columns = implode(", ", $select);
        return sprintf("SELECT $columns FROM %s", $this->sql_table);
    }

    protected function getResult(): array|false
    {
        $sql = $this->getQuery();
        $results = db()->run($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    private function format(array $data): array
    {
        foreach ($data as $i => $row) {
            foreach ($row as $column => $value) {
                if (isset($this->format[$column])) {
                    // A format column is set
                    $callback = $this->format[$column];
                    $data[$i][$column] = $callback($column, $value);
                }
            }
        }
        return $data;
    }

    public function getData(): array
    {
        $rows = $this->getResult();
        return [
            "table" => [
                "columns" => array_keys($this->table),
                "rows" => $this->format($rows)
            ]
        ];
    }
}
