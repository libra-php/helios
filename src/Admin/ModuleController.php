<?php

namespace Helios\Admin;

use Helios\Database\QueryBuilder;
use Helios\View\Flash;
use Helios\Web\Controller;
use PDO;
use StellarRouter\{Get, Post};

/** @package Helios\Admin */
class ModuleController extends Controller
{
    // The module
    private string $module;
    protected string $module_title = '';
    protected string $module_parent = '';

    // The sql stuff
    protected string $primary_key = 'id';
    protected string $table = '';
    protected array $where = [];
    protected array $order_by = [];
    protected array $params = [];
    protected int $per_page = 10;
    protected int $page = 1;

    // Table stuff 
    protected array $table_columns = [];
    protected array $table_format = [];

    // Form stuff
    protected array $form_columns = [];
    protected array $form_controls = [];

    // Permissions
    protected bool $has_edit = true;
    protected bool $has_create = true;
    protected bool $has_delete = true;

    // Filters
    protected array $filter_links = [];
    protected int $filter_link_index = 0;
    protected array $validation_rules = [];


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
            "form" => $this->getFormData($id),
        ]);
    }

    /**
     * Create module endpoint
     */
    #[Get("/create", "module.create", ["auth"])]
    public function create(): string
    {
        return $this->render("/admin/module/create.html", [
            "module" => $this->getModuleData(),
            "form" => $this->getFormData(),
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
                Flash::add("success", "Save successful");
            } else {
                Flash::add("danger", "Save failed");
            }
        } else {
            Flash::add("warning", "Validation error");
        }
        return $this->edit($id);
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
     * Form controls
     */
    protected function control(string $type, string $label, string $column, ?string $value = null)
    {
        return match ($type) {
            'input' => $this->c_input($label, $column, $value),
            'number' => $this->c_number($label, $column, $value),
            'checkbox' => $this->c_checkbox($label, $column, $value),
            'switch' => $this->c_switch($label, $column, $value),
            'textarea' => $this->c_textarea($label, $column, $value),
            default => throw new \Error("control type does not exist: $type"),
        };
    }

    protected function c_input(string $label, string $column, ?string $value): string
    {
        $opts = [
            'id' => "control_$column",
            'class' => 'form-control',
            'type' => 'input',
            'name' => $column,
            'value' => $value,
            'title' => $label,
        ];
        return $this->render("admin/module/controls/input.html", $opts);
    }

    protected function c_number(string $label, string $column, ?string $value): string
    {
        $opts = [
            'id' => "control_$column",
            'class' => 'form-control',
            'type' => 'number',
            'name' => $column,
            'value' => $value,
            'title' => $label,
        ];
        return $this->render("admin/module/controls/input.html", $opts);
    }

    protected function c_checkbox(string $label, string $column, ?string $value): string
    {
        $opts = [
            'id' => "control_$column",
            'class' => 'form-check-input',
            'type' => 'checkbox',
            'name' => $column,
            'title' => $label,
            'checked' => $value == 1,
        ];
        return $this->render("admin/module/controls/input.html", $opts);
    }

    protected function c_switch(string $label, string $column, ?string $value): string
    {
        return $this->render("admin/module/controls/switch.html", [
            "checkbox" => $this->c_checkbox($label, $column, $value),
        ]);
    }

    protected function c_textarea(string $label, string $column, ?string $value): string
    {
        $opts = [
            'id' => "control_$column",
            'class' => 'form-control',
            'name' => $column,
            'title' => $label,
            'rows' => 10,
            'value' => $value,
        ];
        return $this->render("admin/module/controls/textarea.html", $opts);
    }

    /**
     * Table formats
     */
    public function format() {}

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
            "has_edit" => $this->has_edit,
            "has_create" => $this->has_create,
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
     * Return the form data
     */
    protected function getFormData(?int $id = null): array
    {
        if ($id) {
            $this->addWhere("{$this->primary_key} = ?", $id);
        }
        $qb = new QueryBuilder;
        $result = $qb
            ->select(array_values($this->form_columns))
            ->from($this->table)
            ->where($this->where)
            ->params($this->params)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);
        $result = array_map(function ($label, $column, $value) {
            $type = $this->form_controls[$column] ?? null;
            if (is_null($type)) return [];
            return [
                "label" => $label,
                "column" => $column,
                "control" => $this->control($type, $label, $column, $value),
            ];
        }, array_keys($this->form_columns), array_keys($result), array_values($result));
        return [
            "data" => $result,
            "action" => $id ? "/admin/{$this->module}/$id" : "/admin/{$this->module}/create",
        ];
    }

    /**
     * Return the table data
     */
    protected function getTableData(): array
    {
        $qb = new QueryBuilder;
        $offset = $this->per_page * ($this->page - 1);
        $results = $qb
            ->select(array_values($this->table_columns))
            ->from($this->table)
            ->where($this->where)
            ->orderBy($this->order_by)
            ->limit($this->per_page)
            ->offset($offset)
            ->params($this->params)
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);
        return [
            "data" => $results,
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
            ->orderBy($this->order_by)
            ->params($this->params)
            ->execute()
            ->rowCount();
    }

    /**
     * Save the module data to the database
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
}
