<?php

namespace Helios\Admin;

use Helios\Database\QueryBuilder;
use Helios\View\Flash;
use Helios\View\{FormControls, TableFormat};
use Helios\Web\Controller;
use PDO;
use StellarRouter\{Get, Post, Delete};

/** @package Helios\Admin */
class ModuleController extends Controller
{
    use FormControls, TableFormat;

    // The module
    private string $module;
    protected string $module_title = '';
    protected string $module_parent = '';

    // The sql stuff
    protected string $primary_key = 'id';
    protected string $table = '';
    protected array $where = [];
    protected array $having = [];
    protected array $order_by = [];
    protected array $params = [];
    protected int $per_page = 25;
    protected int $page = 1;

    // Table stuff 
    protected array $table_columns = [];

    // Filters
    protected array $filter_links = [];
    protected int $filter_link_index = 0;
    protected array $validation_rules = [];

    // Form stuff
    protected array $form_columns = [];

    // Permissions
    protected bool $has_edit = true;
    protected bool $has_create = true;
    protected bool $has_delete = true;

    public function __construct()
    {
        $this->module = route()->getMiddleware()["module"];
    }

    /**
     * Table endpoint
     */
    #[Get("/", "module.index", ["auth"])]
    public function index(): string
    {
        $path = "/admin/{$this->module}";
        header("HX-Push-Url: $path");

        $this->processRequest();
        $this->setState();

        return $this->render("/admin/module/index.html", [
            "module" => $this->getModuleData(),
            "table" => $this->getTableData(),
            "pagination" => $this->getPaginationData(),
            "permissions" => $this->getPermissionData(),
            "filters" => $this->getFilterData(),
        ]);
    }

    /**
     * Table filter link count endpoint
     */
    #[Get("/filter-link-count/{index}", "module.filter-link-count", ["auth"])]
    public function filterLinkCount(int $index): string
    {
        $filters = array_values($this->filter_links);
        $this->addWhere($filters[$index]);
        return $this->getTotalResults();
    }

    /**
     * Table filter link endpoint
     */
    #[Get("/filter-link/{index}", "module.filter-link", ["auth"])]
    public function filterLink(int $index): string
    {
        $this->handlePage(1);
        $this->handleFilterLink($index);
        return $this->index();
    }

    /**
     * Table page endpoint
     */
    #[Get("/page/{page}", "module.page", ["auth"])]
    public function page(int $page): string
    {
        $this->handlePage($page);
        return $this->index();
    }

    /**
     * Edit module endpoint
     */
    #[Get("/edit/{id}", "module.edit", ["auth"])]
    public function edit(int $id): string
    {
        $path = "/admin/{$this->module}/edit/$id";
        header("HX-Push-Url: $path");

        return $this->render("/admin/module/edit.html", [
            "id" => $id,
            "module" => $this->getModuleData(),
            "form" => $this->getEditFormData($id),
        ]);
    }

    /**
     * Create module endpoint
     */
    #[Get("/create", "module.create", ["auth"])]
    public function create(): string
    {
        $path = "/admin/{$this->module}/create";
        header("HX-Push-Url: $path");

        return $this->render("/admin/module/create.html", [
            "module" => $this->getModuleData(),
            "form" => $this->getCreateFormData(),
        ]);
    }

    /**
     * Update module endpoint
     */
    #[Post("/{id}", "module.update", ["auth"])]
    public function update(int $id): string
    {
        $valid = $this->validateRequest($this->validation_rules);
        if ($valid) {
            $success = $this->save($id, (array) $valid);
            if ($success) {
                Flash::add("success", "Successfully saved record");
            } else {
                Flash::add("danger", "Save failed");
            }
        } else {
            Flash::add("warning", "Validation error");
        }
        return $this->edit($id);
    }

    /**
     * Store module endpoint
     */
    #[Post("/", "module.store", ["auth"])]
    public function store(): string
    {
        $valid = $this->validateRequest($this->validation_rules);
        if ($valid) {
            $id = $this->new((array) $valid);
            if ($id) {
                Flash::add("success", "Successfully created record");
                return $this->edit($id);
            } else {
                Flash::add("danger", "Create failed");
            }
        } else {
            Flash::add("warning", "Validation error");
        }
        return $this->create();
    }

    /**
     * Destroy module endpoint
     */
    #[Delete("/{id}", "module.destroy", ["auth"])]
    public function destroy(int $id): string
    {
        $result = $this->delete($id);
        if ($result) {
            Flash::add("success", "Successfully deleted record");
        } else {
            Flash::add("danger", "Delete failed");
        }
        return $this->index();
    }

    /**
     * Set the module state
     */
    protected function setState(): void
    {
        // Current page
        $this->page = $this->getSession("page") ?? $this->page;

        // Filter link
        $this->filter_link_index = $this->getSession("filter_link") ?? $this->filter_link_index;
        if (!empty($this->filter_links)) {
            $filters = array_values($this->filter_links);
            $this->addWhere($filters[$this->filter_link_index]);
        }
    }

    /**
     * Process the module request
     */
    protected function processRequest(): void {}

    /**
     * Add to where clause and params
     */
    protected function addWhere($clause, ...$replacements): void
    {
        $this->where[] = $clause;
        foreach ($replacements as $replacement) {
            $this->params[] = $replacement;
        }
    }

    /**
     * Handle filter link request
     */
    protected function handleFilterLink(int $index): void
    {
        $this->setSession("filter_link", $index);
    }

    /**
     * Handle page request
     */
    protected function handlePage(int $page): void
    {
        $pagination = $this->getPaginationData();
        if ($page < 0) $page = 1;
        if ($page > $pagination['total_pages']) $page = $pagination['total_pages'];
        $this->setSession("page", $page);
    }

    /**
     * Set the module session
     */
    protected function setSession(string $key, mixed $value): void
    {
        session()->set($this->module . '_' . $key, $value);
    }

    /**
     * Return the module session
     */
    protected function getSession(string $key): mixed
    {
        $session = session()->get($this->module . '_' . $key);
        return $session;
    }

    /**
     * Return the module data
     */
    protected function getModuleData(): array
    {
        return [
            "title" => $this->module_title,
            "parent" => $this->module_parent,
            "route" => $this->module,
            "primary_key" => $this->primary_key,
        ];
    }

    /**
     * Return the filter data
     */
    protected function getFilterData(): array
    {
        return [
            "filter_links" => array_keys($this->filter_links),
            "filter_link_index" => $this->filter_link_index,
        ];
    }

    /**
     * Return the permission data
     */
    protected function getPermissionData(): array
    {
        return [
            "has_edit" => $this->has_edit && !empty($this->form_columns),
            "has_create" => $this->has_create && !empty($this->form_columns),
            "has_delete" => $this->has_delete,
        ];
    }

    /**
     * Return the pagination data
     */
    protected function getPaginationData(): array
    {
        $total_results = $this->getTotalResults();
        return [
            "page" => $this->page,
            "total_results" => $total_results,
            "total_pages" => ceil($total_results / $this->per_page),
            "link_range" => 3,
        ];
    }

    /**
     * Return the edit form data
     */
    protected function getEditFormData(int $id = null): array
    {
        $this->addWhere("{$this->primary_key} = ?", $id);
        $qb = new QueryBuilder;
        $result = $qb
            ->select(array_values($this->form_columns))
            ->from($this->table)
            ->where($this->where)
            ->params($this->params)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);
        $data = array_map(function ($label, $column, $value) {
            $value = request()->request->get($column) ?? $value;
            $type = $this->form_controls[$column] ?? null;
            if (is_null($type)) return [];
            return [
                "label" => $label,
                "column" => $column,
                "control" => $this->control($type, $label, $column, $value),
            ];
        }, array_keys($this->form_columns), array_keys($result), array_values($result));
        return [
            "data" => $data,
            "action" => "/admin/{$this->module}/$id",
        ];
    }

    /**
     * Return the create form data
     */
    protected function getCreateFormData(): array
    {
        $data = array_map(function ($label, $column) {
            $value = request()->request->get($column) ?? null;
            $type = $this->form_controls[$column] ?? null;
            if (is_null($type)) return [];
            return [
                "label" => $label,
                "column" => $column,
                "control" => $this->control($type, $label, $column, $value),
            ];
        }, array_keys($this->form_columns), array_values($this->form_columns));
        return [
            "data" => $data,
            "action" => "/admin/{$this->module}",
        ];
    }

    /**
     * Return the table data
     */
    protected function getTableData(): array
    {
        $qb = new QueryBuilder;
        $offset = $this->per_page * ($this->page - 1);
        $data = $qb
            ->select(array_values($this->table_columns))
            ->from($this->table)
            ->where($this->where)
            ->having($this->having)
            ->orderBy($this->order_by)
            ->limit($this->per_page)
            ->offset($offset)
            ->params($this->params)
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as &$result) {
            $result = array_map(function($column, $value) use ($result) {
                $format = $this->table_format[$column] ?? null;
                return [
                    "column" => $column,
                    "raw" => $value,
                    "formatted" => $this->format($format, $column, $value),
                    "id" => $result[$this->primary_key],
                ];
            }, array_keys($result), array_values($result));
        }
        return [
            "data" => $data,
            "headers" => array_keys($this->table_columns)
        ];
    }

    /**
     * Get total results for a table query
     */
    protected function getTotalResults(): int
    {

        $qb = new QueryBuilder;
        return $qb
            ->select(array_values($this->table_columns))
            ->from($this->table)
            ->where($this->where)
            ->having($this->having)
            ->params($this->params)
            ->execute()
            ->rowCount();
    }

    /**
     * Save the existing module data to the database
     */
    protected function save(int $id, array $data): bool
    {
        $params = array_values($data);
        $params[] = $id;
        $qb = new QueryBuilder;
        $result = $qb
            ->update($data)
            ->table($this->table)
            ->where(["{$this->primary_key} = ?"])
            ->params($params)
            ->execute();
        return $result ? true : false;
    }

    /**
     * Save the new module data to the database
     */
    protected function new(array $data): ?int
    {
        $params = array_values($data);
        $qb = new QueryBuilder;
        $result = $qb
            ->insert($data)
            ->into($this->table)
            ->params($params)
            ->execute();
        return $result ? db()->lastInsertId() : null;
    }

    /**
     * Delete the module data from the database
     */
    protected function delete(int $id): bool
    {
        $qb = new QueryBuilder;
        $result = $qb
            ->delete()
            ->from($this->table)
            ->where(["{$this->primary_key} = ?"])
            ->params([$id])
            ->execute();
        return $result ? true : false;
    }
}
