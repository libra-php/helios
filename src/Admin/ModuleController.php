<?php

namespace Helios\Admin;

use App\Models\{Audit, UserSession};
use Helios\Database\QueryBuilder;
use Helios\View\{Flash, FormControls, TableFormat};
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
    protected string $default_order = "";
    protected string $default_sort = "ASC";

    // Table stuff 
    protected array $table_columns = [];
    protected bool $export_csv = true;

    // Filters
    protected array $filter_links = [];
    protected int $filter_link_index = 0;
    protected array $searchable = [];
    protected string $search_term = '';

    // Form stuff
    protected array $form_columns = [];
    protected array $dropdown_queries = [];
    protected array $validation_rules = [];

    // Permissions
    protected bool $has_edit = true;
    protected bool $has_create = true;
    protected bool $has_delete = true;

    public function __construct()
    {
        // The module is defined by the calling class
        $this->module = route()->getMiddleware()["module"];
        $id = route()->getParameters()[$this->primary_key] ?? null;
        $this->default_order = $this->primary_key;
        $this->init($id);
    }

    /**
     * Table endpoint
     */
    #[Get("/", "module.index", ["auth"])]
    public function index(): string
    {
        $this->recordUserSession();
        $path = "/admin/{$this->module}";
        header("HX-Push-Url: $path");

        // Sets the class properties from session
        $this->setState();

        return $this->render("/admin/module/index.html", [
            "module" => $this->getModuleData(),
            "table" => $this->getTableData(),
            "actions" => $this->getTableActions(),
            "pagination" => $this->getPaginationData(),
            "permissions" => $this->getPermissions(),
            "filters" => $this->getFilterData(),
        ]);
    }

    /**
     * Export table in csv format
     */
    #[Get("/export-csv", "module.export-csv", ["auth"])]
    public function exportCSV(): void
    {
        $this->setState();
        $time = time();
        $filename = "{$this->module}_{$time}_export.csv";
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $fp = fopen("php://output", "wb");
        $titles = array_keys($this->table_columns);
        fputcsv($fp, $titles);
        $this->per_page = 1000;
        $this->page = 1;
        $total_results = $this->getTotalResultsCount();
        $total_pages = ceil($total_results / $this->per_page);
        while ($this->page <= $total_pages) {
            $result = $this->getTableData();
            foreach ($result['data'] as $item) {
                $row = array_map(fn($one) => $one['raw'], $item);
                $values = array_values($row);
                fputcsv($fp, $values);
            }
            $this->page++;
        }
        fclose($fp);
        exit();
    }

    /**
     * Table filter link count endpoint
     */
    #[Get("/filter-link-count/{index}", "module.filter-link-count", ["auth"])]
    public function filterLinkCount(int $index): string
    {
        // We call set state so that the other filters are considered
        $this->setState(true);
        $filters = array_values($this->filter_links);
        $this->addWhere($filters[$index]);
        return $this->getTotalResultsCount();
    }

    /**
     * Table filter link endpoint
     */
    #[Get("/filter-link/{index}", "module.filter-link", ["auth"])]
    public function filterLink(int $index): string
    {
        // Reset to page 1 when the filter is activated
        $this->handlePage(1);
        $this->handleFilterLink($index);
        return $this->index();
    }

    /**
     * Table search
     */
    #[Get("/search", "module.search", ["auth"])]
    public function search(): string
    {
        $valid = $this->validateRequest([
            "search_term" => [],
        ]);
        if ($valid) {
            $this->handleSearch($valid->search_term);
        }
        return $this->index();
    }

    /**
     * Table sort by column
     */
    #[Get("/sort", "module.sort", ["auth"])]
    public function sort(): string
    {
        $valid = $this->validateRequest([
            "index" => ["required"],
        ]);
        if ($valid) {
            $this->handleSort($valid->index);
        }
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
        $this->recordUserSession();
        if (!$this->hasEditPermission($id)) {
            redirect("/permission-denied");
        }
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
        $this->recordUserSession();
        if (!$this->hasCreatePermission()) {
            redirect("/permission-denied");
        }
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
        if (!$this->hasEditPermission($id)) {
            redirect("/permission-denied");
        }
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
        if (!$this->hasCreatePermission()) {
            redirect("/permission-denied");
        }
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
        if (!$this->hasDeletePermission($id)) {
            redirect("/permission-denied");
        }
        $result = $this->delete($id);
        if ($result) {
            Flash::add("success", "Successfully deleted record");
        } else {
            Flash::add("danger", "Delete failed");
        }
        return $this->index();
    }

    /**
     * This method can be used to configure the module properties
     */
    public function init(?int $id): void {}

    /**
     * Record active user session
     */
    protected function recordUserSession(): void
    {
        $actual_link = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        UserSession::create([
            "user_id" => user()->id,
            "module" => $this->module,
            "url" => $actual_link,
            "ip" => ip2long(getClientIp()),
        ]);
    }

    /**
     * Set the module state
     */
    protected function setState($skip_filter_links = false): void
    {
        // Search
        if (!empty($this->searchable)) {
            $this->search_term = $this->getSession("search_term") ?? $this->search_term;
            $map = array_map(fn($column) => "$column LIKE ?", $this->searchable);
            $clause = "((" . implode(") OR (", $map) . "))";
            $this->addWhere($clause, ...array_fill(0, count($this->searchable), "%$this->search_term%"));
        }

        // Sort
        $order = $this->getSession("order") ?? $this->default_order;
        $sort = $this->getSession("sort") ?? $this->default_sort;
        $this->order_by = ["$order $sort"];

        // Filter link
        if (!$skip_filter_links) {
            if (!empty($this->filter_links)) {
                $this->filter_link_index = $this->getSession("filter_link") ?? $this->filter_link_index;
                $filters = array_values($this->filter_links);
                $this->addWhere($filters[$this->filter_link_index]);
            }
        }

        // Current page
        $this->page = $this->getSession("page") ?? $this->page;
    }

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
     * Handle search request
     */
    protected function handleSearch(string $search_term): void
    {
        $search_term = trim($search_term);
        $search_term = filter_var($search_term, FILTER_SANITIZE_ENCODED);
        if ($search_term !== '') {
            $this->setSession("search_term", $search_term);
        } else {
            $this->deleteSession("search_term");
        }
    }

    /**
     * Handle sort request
     * Sets the sort / order for a table view
     */
    protected function handleSort(string $index): void
    {
        $headers = array_values($this->filterTableColumns());
        if (isset($headers[$index])) {
            $column = $this->getAlias($headers[$index]);
            $session_order = $this->getSession("order");
            $session_sort = $this->getSession("sort");
            $this->setSession("order", $column);
            if ($column === $session_order) {
                $this->setSession("sort", $session_sort === "ASC" ? "DESC" : "ASC");
            } else {
                $this->setSession("sort", "ASC");
            }
        }
    }

    /**
     * Handle page request
     */
    protected function handlePage(int $page): void
    {
        $pagination = $this->getPaginationData();
        // Check out of bounds
        if ($page < 0) $page = 1;
        if ($page > $pagination['total_pages']) $page = $pagination['total_pages'];
        $this->setSession("page", $page);
    }

    /**
     * Set the module session key
     */
    protected function setSession(string $key, mixed $value): void
    {
        session()->set($this->module . '_' . $key, $value);
    }

    /**
     * Return the module session key
     */
    protected function getSession(string $key): mixed
    {
        $session = session()->get($this->module . '_' . $key);
        return $session;
    }

    /**
     * Delete the module session key
     */
    protected function deleteSession(string $key): void
    {
        session()->delete($this->module . '_' . $key);
    }

    /**
     * Return the module data
     */
    protected function getModuleData(): array
    {
        // Data used by modules
        return [
            "module" => $this->module,
            "title" => $this->module_title,
            "parent" => $this->module_parent,
            "route" => rtrim(moduleRoute("module.index", $this->module), DIRECTORY_SEPARATOR),
            "primary_key" => $this->primary_key,
        ];
    }

    /**
     * Return the filter data
     */
    protected function getFilterData(): array
    {
        // Data used by table filters
        return [
            "filter_links" => array_keys($this->filter_links),
            "filter_link_index" => $this->filter_link_index,
            "search_term" => $this->search_term,
            "searchable" => $this->searchable,
        ];
    }

    /**
     * Return create permission
     */
    public function hasCreatePermission(): bool
    {
        return $this->has_create && !empty($this->form_columns);
    }

    /**
     * Return edit permission
     */
    public function hasEditPermission(int $id): bool
    {
        return $this->has_edit && !empty($this->form_columns);
    }

    /**
     * Return delete permission
     */
    public function hasDeletePermission(int $id): bool
    {
        return $this->has_delete && !empty($this->form_columns);
    }

    /**
     * Return the permission functions
     */
    protected function getPermissions(): object
    {
        // Giving twig access to some methods
        $functions = new class($this) {
            public function __construct(private ModuleController $module) {}
            public function hasCreate(): bool
            {
                return $this->module->hasCreatePermission();
            }

            public function hasEdit(int $id): bool
            {
                return $this->module->hasEditPermission($id);
            }

            public function hasDelete(int $id): bool
            {
                return $this->module->hasDeletePermission($id);
            }
        };
        return $functions;
    }

    /**
     * Return the table actions
     */
    protected function getTableActions(): array
    {
        return [
            "export_csv" => $this->export_csv,
        ];
    }

    /**
     * Return the pagination data
     */
    protected function getPaginationData(): array
    {
        if (!$this->table_columns) return [];
        // This data is used for pagination at the bottom of the table
        $total_results = $this->getTotalResultsCount();
        return [
            "page" => $this->page,
            "total_results" => $total_results,
            "total_pages" => ceil($total_results / $this->per_page),
            "link_range" => 2,
        ];
    }

    /**
     * Get a column by alias
     */
    protected function getAlias(string $column): string
    {
        $arr = explode(" as ", strtolower($column));
        return end($arr);
    }

    /**
     * Mapping function for edit/create form
     */
    protected function formMap(string $label, string $column, ?string $value)
    {
        $column = $this->getAlias($column);
        $value = request()->request->get($column) ?? $value;
        $type = $this->form_controls[$column] ?? null;
        if (is_null($type)) return [];
        $opts = [
            "class" => "form-control" . (key_exists($column, $this->request_errors) ? ' is-invalid' : ''),
            "id" => "control-$column",
            "name" => $column,
            "title" => $label,
            "value" => $value,
        ];
        if ($type === 'select' && isset($this->dropdown_queries[$column])) {
            if (is_array($this->dropdown_queries[$column])) {
                // Array of dropdown options
                $opts['options'] = $this->dropdown_queries[$column];
            } else {
                // Must be query
                $opts['options'] = db()->fetchAll($this->dropdown_queries[$column]);
            }
        }
        return [
            "label" => $label,
            "column" => $column,
            "control" => $this->control($type, $opts),
        ];
    }

    /**
     * Return the edit form data
     */
    protected function getEditFormData(int $id = null): array
    {
        // Add the primary key to the where clause
        $this->addWhere("{$this->primary_key} = ?", $id);
        // The fetch the form data
        $qb = new QueryBuilder;
        $result = $qb
            ->select(array_values($this->form_columns))
            ->from($this->table)
            ->where($this->where)
            ->params($this->params)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);
        // Prepare the edit form data
        $data = array_map([$this, "formMap"], array_keys($this->form_columns), array_keys($result), array_values($result));
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
        // Prepare the create form data structure
        $data = array_map([$this, "formMap"], array_keys($this->form_columns), array_values($this->form_columns), array_fill(0, count($this->form_columns), null));
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
        if (!$this->table_columns) return [];
        // Calculate the page offset
        $offset = $this->per_page * ($this->page - 1);
        // Always include primary key
        if (!in_array($this->primary_key, $this->table_columns)) {
            $this->table_columns[] = $this->primary_key;
        }
        // Fetch the table data
        $qb = new QueryBuilder;
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
        // Prepare the data structure for the table
        foreach ($data as &$result) {
            $result = array_map(function ($label, $column, $value) use ($result) {
                $format = $this->table_format[$column] ?? null;
                return [
                    "label" => is_string($label) ? $label : null,
                    "column" => $column,
                    "raw" => $value,
                    "formatted" => $value ? $this->format($format, $column, $value) : '',
                    "id" => $result[$this->primary_key],
                ];
            }, array_keys($this->table_columns), array_keys($result), array_values($result));
        }
        $headers = $this->filterTableColumns();
        return [
            "data" => $data,
            "headers" => $headers,
            // +1 to colspan to account for the actions cell
            "colspan" => count($headers) + 1,
        ];
    }

    /**
     * Filter table columns
     * (ie, part of the dataset but not rendered in the table)
     */
    protected function filterTableColumns(): array
    {
        $headers = [];
        foreach ($this->table_columns as $label => $column) {
            if (is_string($label)) {
                $headers[$label] = $column;
            }
        }
        return $headers;
    }

    /**
     * Get total results for a table query
     */
    protected function getTotalResultsCount(): int
    {
        // Return the total table result count
        $qb = new QueryBuilder;
        return $qb
            ->select(array_values($this->table_columns))
            ->from($this->table)
            ->where($this->where)
            ->having($this->having)
            ->params($this->params)
            ->execute()
            ->rowCount() ?? 0;
    }

    /**
     * Save the existing module data to the database
     */
    protected function save(int $id, array $data): bool
    {
        // The update values
        $params = array_values($data);
        // The primary key is required for the update
        $params[] = $id;
        $qb = new QueryBuilder;
        $row = $qb->select()
            ->from($this->table)
            ->where(["{$this->primary_key} = ?"])
            ->params([$id])
            ->execute()
            ->fetch();
        // Update query
        $result = $qb
            ->update($data)
            ->table($this->table)
            ->where(["{$this->primary_key} = ?"])
            ->params($params)
            ->execute();
        if ($result) {
            // Audit the result
            if ($row) {
                foreach ($data as $column => $value) {
                    if ($row->$column != $value) {
                        Audit::create([
                            "user_id" => user()->id,
                            "table_name" => $this->table,
                            "table_id" => $id,
                            "field" => $column,
                            "old_value" => $row->$column,
                            "new_value" => $value,
                            "tag" => "UPDATE",
                        ]);
                    }
                }
            }
        }
        return $result ? true : false;
    }

    /**
     * Save the new module data to the database
     */
    protected function new(array $data): ?int
    {
        // The insert values
        $params = array_values($data);
        // Insert query
        $qb = new QueryBuilder;
        $result = $qb
            ->insert($data)
            ->into($this->table)
            ->params($params)
            ->execute();
        if ($result) {
            $id = db()->lastInsertId();
            $row = $qb->select()
                ->from($this->table)
                ->where(["{$this->primary_key} = ?"])
                ->params([$id])
                ->execute()
                ->fetch(PDO::FETCH_ASSOC);
            // Audit the result
            if ($row) {
                foreach ($row as $column => $value) {
                    Audit::create([
                        "user_id" => user()->id,
                        "table_name" => $this->table,
                        "table_id" => $id,
                        "field" => $column,
                        "new_value" => $value,
                        "tag" => "CREATE",
                    ]);
                }
            }
        }
        // The id will be returned if successful
        return $result ? $id : null;
    }

    /**
     * Delete the module data from the database
     */
    protected function delete(int $id): bool
    {
        // Delete query
        $qb = new QueryBuilder;
        $result = $qb
            ->delete()
            ->from($this->table)
            ->where(["{$this->primary_key} = ?"])
            ->params([$id])
            ->execute();
        if ($result) {
            Audit::create([
                "user_id" => user()->id,
                "table_name" => $this->table,
                "table_id" => $id,
                "field" => $this->primary_key,
                "old_value" => $id,
                "new_value" => null,
                "tag" => "DELETE",
            ]);
        }
        return $result ? true : false;
    }
}
