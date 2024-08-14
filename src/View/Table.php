<?php

namespace Helios\View;

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
        $this->handleSearch();
        $this->handleSort();
        $this->handleFilterCount();
        $this->handleLinkFilter();
        $this->handlePerPage();
        $this->handlePage();
        $this->handleExportCsv();
    }

    public function getData(): array
    {
        $rows = !empty($this->table) ? $this->getPayload() : [];
        return [
            "actions" => [
                "export_csv" => !empty($this->table) && $this->export_csv,
            ],
            "table" => [
                "columns" => $this->stripAlias($this->table),
                "rows" => $this->format($rows)
            ],
            "pagination" => [
                "total_results" => $this->total_results,
                "total_pages" => $this->total_pages,
                "page" => $this->page,
                "per_page" => $this->per_page,
                "page_options" => $this->page_options,
            ],
            "filters" => [
                "filter_link" => $this->filter_link_index,
                "links" => array_keys($this->filter_links),
                "searchable" => $this->searchable,
                "search_term" => $this->search_term,
                "order_by" => $this->order_by,
                "ascending" => $this->ascending,
            ],
        ];
    }

    private function handleSearch(): void
    {
        if (request()->query->has("search_term")) {
            $term = request()->query->get("search_term");
            $this->setPage(1);
        } else {
            $term = $this->getSession("search_term") ?? "";
        }
        $this->setSearchTerm($term);
    }

    private function handleSort()
    {
        if (request()->query->has("order_by")) {
            $order_by = request()->query->get("order_by");
        } else {
            $order_by = $this->getSession("order_by") ?? $this->primary_key;
        }
        if (request()->query->has("ascending")) {
            $ascending = request()->query->get("ascending") === "ASC";
        } else {
            $ascending = $this->getSession("ascending") ?? $this->ascending;
        }

        $this->setOrderBy($order_by);
        $this->setAscending($ascending);
    }

    private function handlePerPage(): void
    {
        if (request()->query->has("per_page")) {
            $per_page = request()->query->get("per_page");
            $this->setPage(1);
        } else {
            $per_page = $this->getSession("per_page") ?? $this->per_page;
        }
        $this->setPerPage($per_page);
    }

    private function handlePage(): void
    {
        if (request()->query->has("page")) {
            $page = request()->query->get("page");
        } else {
            $page = $this->getSession("page") ?? $this->page;
        }
        $this->setPage($page);
    }

    private function handleExportCsv()
    {
        if (request()->query->has("export_csv")) {
            header("Content-Type: text/csv");
            header('Content-Disposition: attachment; filename="csv_export.csv"');
            $fp = fopen("php://output", "wb");
            $headers = array_keys($this->table);
            fputcsv($fp, $headers);
            $this->per_page = 1000;
            $this->page = 1;
            $this->total_results = $this->getTotalResults();
            $this->total_pages = ceil($this->total_results / $this->per_page);
            while ($this->page <= $this->total_pages) {
                $results = $this->getQueryResults();
                foreach ($results as $item) {
                    $values = array_values($item);
                    fputcsv($fp, $values);
                }
                $this->page++;
            }
            fclose($fp);
            exit();
        }
    }

    private function handleFilterCount(): void
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

    private function handleLinkFilter(): void
    {
        if (request()->query->has("filter_link")) {
            $index = request()->query->get("filter_link");
            $this->setPage(1);
        } else {
            $index = $this->getSession("filter_link") ?? 0;
        }
        $this->setFilterLink($index);
    }

    private function setSearchTerm(string $term): void
    {
        if (trim($term)) {
            $this->search_term = $term;
            $clause = [];
            foreach ($this->searchable as $column) {
                $clause[] = "($column LIKE ?)";
            }
            $replacements = array_fill(0, count($clause), "%{$this->search_term}%");
            $this->addClause($this->where, implode(" OR ", $clause), ...$replacements);
        }
        $this->setSession("search_term", $this->search_term);
    }

    private function setFilterLink(int $index): void
    {
        $filters = array_values($this->filter_links);
        if (isset($filters[$index])) {
            $this->filter_link_index = $index;
            $this->addClause($this->where, $filters[$index]);
            $this->setSession("filter_link", $index);
        }
    }

    private function setAscending(bool $ascending = true)
    {
        $this->ascending = $ascending;
        $this->setSession("ascending", $ascending);
    }

    private function setOrderBy(string $column)
    {
        $this->order_by = $column;
        $this->setSession("order_by", $column);
    }


    private function setPage(int $page): void
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

    private function setPerPage(int $per_page): void
    {
        $this->per_page = $per_page;
        $this->setSession("per_page", $this->per_page);
    }

    private function getSelect(): string
    {
        $columns = array_values($this->table);
        return implode(", ", $columns);
    }

    private function getSqlTable(): string
    {
        return $this->sql_table;
    }

    private function getWhere(): string
    {
        return $this->where
            ? "WHERE " . $this->formatClause($this->where)
            : '';
    }

    private function getHaving(): string
    {
        return $this->having
            ? "HAVING " . $this->formatClause($this->having)
            : '';
    }

    private function getGroupBy(): string
    {
        return $this->group_by
            ? "GROUP BY " . $this->formatClause($this->group_by)
            : '';
    }

    private function getOrderBy(): string
    {
        $sort = $this->ascending ? "ASC" : "DESC";
        return $this->order_by
            ? "ORDER BY {$this->order_by} $sort"
            : '';
    }

    private function getLimitOffset(): string
    {
        $page = max(($this->page - 1) * $this->per_page, 0);
        $per_page = $this->per_page;
        return $this->total_results > $this->per_page
            ? "LIMIT $page, $per_page"
            : '';
    }

    private function formatClause(array $clauses): string
    {
        $out = [];
        foreach ($clauses as $clause) {
            [$clause, $params] = $clause;
            // Add parens to clause for order of ops
            $out[] = "(" . $clause . ")";
        }
        return sprintf("%s", implode(" AND ", $out));
    }

    private function addClause(array &$clauses, string $clause, int|string ...$replacements): void
    {
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
        return $params;
    }

    private function getAllParams(): array
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

    private function stripAlias(array $data)
    {
        $out = [];
        foreach ($data as $title => $column) {
            $arr = explode(" as ", $column);
            $out[$title] = end($arr);
        }
        return $out;
    }

    protected function getQueryResults(): array|false
    {
        $sql = $this->getQuery();
        $params = $this->getAllParams();
        $stmt = db()->run($sql, $params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    protected function getPayload(): array|false
    {
        if (empty($this->table)) return false;
        return $this->getQueryResults();
    }

    protected function getTotalResults(): int
    {
        if (empty($this->table)) return 0;
        $sql = $this->getQuery(false);
        $params = $this->getAllParams();
        $stmt = db()->run($sql, $params);
        return $stmt ? $stmt->rowCount() : 0;
    }
}
