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

    public function processRequest(): void
    {
        $this->handlePage();
    }

    private function handlePage()
    {
        if (request()->query->has("page")) {
            $page = request()->query->get("page");
        } else {
            $page = $this->getSession("page") ?? 1;
        }
        $this->setPage($page);
    }

    private function setPage(int $page)
    {
        $this->total_results = $this->getTotalResults();
        $this->total_pages = ceil($this->total_results / $this->per_page);

        if ($page > 0 && $page <= $this->total_pages) {
            $this->page = $page;
        } else {
            if ($page < 1) {
                $this->page = 1;
            } elseif ($page > $this->total_pages) {
                $this->page = $this->total_pages;
            }
        }

        $this->setSession("page", $this->page);
    }

    protected function getQuery($limit_query = true): string
    {
        $columns = array_values($this->table);
        $select = implode(", ", $columns);
        $where = "";
        $group_by = "";
        $having = "";
        $order_by = "";
        $page = $this->page;
        $per_page = $this->per_page;
        $limit = $limit_query ? "LIMIT $page, $per_page" : '';
        return sprintf("SELECT %s FROM %s %s %s %s %s %s",
            $select,
            $this->sql_table,
            $where,
            $group_by,
            $having,
            $order_by,
            $limit
        );
    }

    protected function getPayload(): array|false
    {
        $this->processRequest();
        $sql = $this->getQuery();
        $stmt = db()->run($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    protected function getTotalResults(): int
    {
        $sql = $this->getQuery(false);
        $stmt = db()->run($sql);
        return $stmt ? $stmt->rowCount() : 0;
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
        $rows = $this->getPayload();
        return [
            "table" => [
                "columns" => array_keys($this->table),
                "rows" => $this->format($rows)
            ],
            "pagination" => [
                "total_results" => $this->total_results,
                "total_pages" => $this->total_pages,
                "page" => $this->page,
                "per_page" => $this->per_page,
            ],
        ];
    }
}
