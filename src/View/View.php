<?php

namespace Helios\View;

use PDO;

class View implements IView
{
    /** Template properties */
    protected string $template = "/admin/module/view.html";

    /** SQL properties */
    protected string $sql_table = "";
    protected string $primary_key = "id";
    protected array $where = [];
    protected array $group_by = [];
    protected array $having = [];
    protected string $order_by = "";
    protected bool $ascending = true;

    /** Pagination */
    protected int $total_results = 0;
    protected int $total_pages = 0;
    protected int $per_page = 5;
    protected int $page = 1;
    protected array $page_options = [5, 10, 25, 50, 100, 250, 500, 750, 1000];

    /** Table Properties */
    protected array $table = [];
    protected array $format = [];
    protected array $filter_links = [];
    protected int $filter_link_index = 0;
    protected bool $export_csv = true;

    /** Permissions */
    protected bool $has_create = true;
    protected bool $has_edit = true;
    protected bool $has_delete = true;

    /**
     * @var array Searchable table columns
     */
    protected array $searchable = [];
    /**
     * @var string Table search term
     */
    protected string $search_term = "";


    /** Form Properties */
    protected array $form = [];
    protected array $control = [];

    public function processRequest(): void {}

    public function sqlTable(string $name)
    {
        $this->sql_table = $name;
        return $this;
    }

    public function sqlPrimaryKey(string $column)
    {
        $this->primary_key = $column;
        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    protected function getQuery(): string
    {
        return "";
    }

    protected function getPayload(): array|false
    {
        return [];
    }

    public function getData(): array
    {
        return [
            "module" => request()->get("module"),
            "links" => $this->buildLinks(),
            "breadcrumbs" => $this->getBreadcrumbs(isset($this->id) ? $this->id : null),
            "permissions" => [
                "has_create" => $this->has_create && !empty($this->form),
                "has_edit" => $this->has_edit && !empty($this->form),
                "has_delete" => $this->has_delete,
            ],
        ];
    }

    public function tableOnly()
    {
        $this->has_create = $this->has_edit = $this->has_delete = false;
        return $this;
    }

    public function hasCreate(bool $state)
    {
        $this->has_create = $state;
        return $this;
    }

    public function hasEdit(bool $state)
    {
        $this->has_edit = $state;
        return $this;
    }

    public function hasDelete(bool $state)
    {
        $this->has_delete = $state;
        return $this;
    }

    public function table(string $title, string $subquery)
    {
        $this->table[$title] = $subquery;
        return $this;
    }

    public function tableFormat(string $column, callable|string $callback)
    {
        $this->format[$column] = $callback;
        return $this;
    }

    public function formControl(string $column, callable|string $callback)
    {
        $this->control[$column] = $callback;
        return $this;
    }

    public function filterLink(string $title, string $subquery)
    {
        $this->filter_links[$title] = $subquery;
        return $this;
    }

    public function form(string $title, string $subquery)
    {
        $this->form[$title] = $subquery;
        return $this;
    }

    public function addSearch(string $column)
    {
        $this->searchable[] = $column;
        return $this;
    }

    public function defaultOrder(string $column)
    {
        $this->order_by = $column;
        return $this;
    }

    public function sortAscending(bool $ascending)
    {
        $this->ascending = $ascending;
        return $this;
    }

    protected function setSession(string $name, mixed $value)
    {
        $module = request()->get("module");
        $module_session = session()->get($module->path) ?? [];
        $module_session[$name] = $value;
        session()->set($module->path, $module_session);
    }

    protected function hasSession(string $name)
    {
        $module = request()->get("module");
        $module_session = session()->get($module->path) ?? [];
        return key_exists($name, $module_session);
    }


    protected function getSession(string $name)
    {
        $module = request()->get("module");
        $module_session = session()->get($module->path) ?? [];
        return key_exists($name, $module_session) ? $module_session[$name] : null;
    }

    protected function getSqlTable(): string
    {
        return $this->sql_table;
    }

    protected function getSqlColumns()
    {
        $table = $this->sql_table;
        return db()->query("DESCRIBE $table")->fetchAll(PDO::FETCH_COLUMN);
    }

    protected function getSelect(array $select): string
    {
        $columns = array_values($select);
        return implode(", ", $columns);
    }

    protected function getWhere(): string
    {
        return $this->where
            ? "WHERE " . $this->formatClause($this->where)
            : '';
    }

    protected function stripAlias(array $data)
    {
        $out = [];
        foreach ($data as $title => $column) {
            $arr = explode(" as ", $column);
            $out[$title] = end($arr);
        }
        return $out;
    }

    protected function formatClause(array $clauses): string
    {
        $out = [];
        foreach ($clauses as $clause) {
            [$clause, $params] = $clause;
            // Add parens to clause for order of ops
            $out[] = "(" . $clause . ")";
        }
        return sprintf("%s", implode(" AND ", $out));
    }

    protected function addClause(array &$clauses, string $clause, int|string ...$replacements): void
    {
        $clauses[] = [$clause, [...$replacements]];
    }

    protected function getParams(array $clauses): array
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

    protected function getAllParams(): array
    {
        $where_params = $this->getParams($this->where);
        $having_params = $this->getParams($this->having);
        return [...$where_params, ...$having_params];
    }

    private function buildBreadcrumbs(string $module_id, $breadcrumbs = []): array
    {
        $module = db()->fetch(
            "SELECT *
            FROM modules
            WHERE id = ?
            AND enabled = 1",
            $module_id
        );
        $breadcrumbs[] = $module;
        if (intval($module->parent_module_id) > 0) {
            return $this->buildBreadcrumbs(
                $module->parent_module_id,
                $breadcrumbs
            );
        }
        return array_reverse($breadcrumbs);
    }

    private function getBreadcrumbs(?string $id): array
    {
        $module = request()->get("module");
        $path = $module->path;
        $breadcrumbs = $this->buildBreadcrumbs($module->id);
        $route_name = request()->get("route")->getName();
        if ($route_name === "module.create") {
            $breadcrumbs[] = (object) [
                "path" => "$path/create",
                "title" => "Create",
            ];
        } else if (!is_null($id)) {
            $breadcrumbs[] = (object) [
                "path" => "$path/$id",
                "title" => "Edit $id",
            ];
        }
        return $breadcrumbs;
    }

    private function buildLinks(?int $parent_module_id = null): array
    {
        $user = user();
        if (is_null($parent_module_id)) {
            $modules = db()->fetchAll("SELECT *
				FROM modules
				WHERE parent_module_id IS NULL
                AND enabled = 1
				ORDER BY item_order");
        } else {
            $modules = db()->fetchAll(
                "SELECT *
				FROM modules
				WHERE parent_module_id = ?
                AND enabled = 1
				ORDER BY item_order",
                $parent_module_id
            );
        }
        $sidebar_links = [];
        foreach ($modules as $module) {
            // Skip the modules that the user doesn't have permission to
            if (
                !is_null($module->max_permission_level) &&
                $user->type()->permission_level > $module->max_permission_level
            ) {
                continue;
            }
            $link = [
                "id" => $module->id,
                "label" => $module->title,
                "link" => "/admin/{$module->path}",
                "children" => $this->buildLinks($module->id),
            ];
            $sidebar_links[] = $link;
        }
        // Add sign out link
        if ($parent_module_id == 2) {
            $link = [
                "id" => null,
                "label" => "Sign Out",
                "link" => "/sign-out",
                "children" => [],
            ];
            $sidebar_links[] = $link;
        }
        return $sidebar_links;
    }
}
