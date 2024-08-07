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
        $this->handleFilterCount();
        $this->handleLinkFilter();
        $this->handlePage();
        $this->handlePerPage();
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
                "page_options" => [
                    5,
                    10,
                    50,
                    100,
                    500,
                    1000,
                ],
            ],
            "filters" => [
                "links" => array_keys($this->filter_links),
            ],
        ];
    }

    private function handlePerPage()
    {
        if (request()->query->has("per_page")) {
            $per_page = request()->query->get("per_page");
            $this->setPage(1);
        } else {
            $per_page = $this->getSession("per_page") ?? 10;
        }
        $this->setPerPage($per_page);
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

    private function handleFilterCount()
    {
        if (request()->query->has("filter_count")) {
            $filters = array_values($this->filter_links);
            $index = request()->query->get("filter_count");
            if (key_exists($index, $filters)) {
                $this->addClause($this->where, $filters[$index]);
                echo $this->getTotalResults();
                exit;
            }
        }
    }

    private function handleLinkFilter()
    {
        if (request()->query->has("filter_link")) {
            $index = request()->query->get("filter_link");
            $this->setPage(1);
        } else {
            $index = $this->getSession("filter_link") ?? 0;
        }

        $this->setFilterLink($index);
    }

    private function setFilterLink(int $index)
    {
        $filters = array_values($this->filter_links);
        if (isset($filters[$index])) {
            $this->addClause($this->where, $filters[$index]);
        }

        $this->setSession("filter_link", $index);
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

    private function setPerPage(int $per_page)
    {
        $this->per_page = $per_page;

        $this->setSession("per_page", $this->per_page);
    }

    private function getSelect()
    {
        $columns = array_values($this->table);
        return implode(", ", $columns);
    }

    private function getSqlTable()
    {
        return $this->sql_table;
    }

    private function getWhere()
    {
        return $this->where
            ? "WHERE " . $this->formatClause($this->where)
            : '';
    }

    private function getHaving()
    {
        return $this->where
            ? $this->formatClause($this->having)
            : '';
    }

    private function getGroupBy()
    {
        return '';
    }

    private function getOrderBy()
    {
        $sort = $this->ascending ? "ASC" : "DESC";
        return $this->order_column
            ? "ORDER BY {$this->order_column} $sort"
            : '';
    }

    private function getLimitOffset()
    {
        $page = max(($this->page - 1) * $this->per_page, 0);
        $per_page = $this->per_page;
        return $this->total_results > $this->per_page
            ? "LIMIT $page, $per_page"
            : '';
    }

    private function formatClause(array $clauses)
    {
        $out = [];
        foreach ($clauses as $clause) {
            [$clause, $params] = $clause;
            // Add parens to clause for order of ops
            $out[] = "(" . $clause . ")";
        }
        return sprintf("%s", implode(" AND ", $out));
    }

    private function addClause(
        array &$clauses,
        string $clause,
        int|string ...$replacements
    ): void {
        $clauses[] = [$clause, [...$replacements]];
    }

    private function getParams(array $clauses): array
    {
        if (!$clauses) {
            return [];
        }
        $params = [];
        foreach ($clauses as $clause) {
            [$clause, $param_array] = $clause;
            $params = [...$params, ...$param_array];
        }
        return $param_array;
    }

    private function getAllParams()
    {
        $where_params = $this->getParams($this->where);
        $having_params = $this->getParams($this->having);
        return [...$where_params, ...$having_params];
    }

    protected function getQuery($limit_query = true): string
    {
        return sprintf(
            "SELECT %s FROM %s %s %s %s %s %s",
            $this->getSelect(),
            $this->getSqlTable(),
            $this->getWhere(),
            $this->getGroupBy(),
            $this->getHaving(),
            $this->getOrderBy(),
            $limit_query ? $this->getLimitOffset() : ''
        );
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

    protected function getPayload(): array|false
    {
        $this->processRequest();
        $sql = $this->getQuery();
        $params = $this->getAllParams();
        $stmt = db()->run($sql, $params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    protected function getTotalResults(): int
    {
        $sql = $this->getQuery(false);
        $params = $this->getAllParams();
        $stmt = db()->run($sql, $params);
        return $stmt ? $stmt->rowCount() : 0;
    }
}
