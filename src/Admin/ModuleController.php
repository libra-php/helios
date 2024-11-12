<?php

namespace Helios\Admin;

use Helios\Database\QueryBuilder;
use Helios\Web\Controller;
use PDO;
use StellarRouter\{Get, Group};

/** @package Helios\Admin */
#[Group(middleware: ["auth"])]
class ModuleController extends Controller
{
    // The module
    private string $module;
    // The module title
    protected string $module_title = '';
    // The module parent
    protected string $module_parent = '';

    // The sql stuff
    protected string $primary_key = 'id';
    protected string $table = '';
    protected array $where = [];
    protected array $order_by = [];
    protected array $params = [];
    protected int $per_page = 10;
    protected int $page = 1;

    // Table columns 
    protected array $table_columns = [];
    // Table format
    protected array $table_format = [];

    // Permissions
    protected bool $has_edit = true;
    protected bool $has_create = true;
    protected bool $has_delete = true;

    // Filters
    protected array $filter_links = [];
    protected int $filter_link_index = 0;


    public function __construct()
    {
        $this->module = route()->getMiddleware()["module"];
    }

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

    #[Get("/filter-link-count/{index}", "module.filter-link-count")]
    public function filterLinkCount(int $index): string
    {
        $filters = array_values($this->filter_links);
        $this->addWhere($filters[$index]);
        return $this->getTotalResults();
    }

    #[Get("/filter-link/{index}", "module.filter-link")]
    public function filterLink(int $index): string
    {
        $this->handlePage(1);
        $this->handleFilterLink($index);
        return $this->index();
    }

    #[Get("/page/{page}", "module.page")]
    public function page(int $page): string
    {
        $this->handlePage($page);
        return $this->index();
    }

    #[Get("/edit/{id}", "module.edit")]
    public function edit(int $id): string
    {
        $path = "/admin/{$this->module}/edit/$id";
        header("HX-Push-Url: $path");

        return $this->render("/admin/module/edit.html", [
            "id" => $id,
            "module" => $this->getModuleData(),
        ]);
    }

    #[Get("/create", "module.create")]
    public function create(): string
    {
        return $this->render("/admin/module/create.html", [
            "module" => $this->getModuleData(),
        ]);
    }

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

    protected function processRequest(): void {}

    protected function addWhere($clause, ...$replacements): void
    {
        $this->where[] = $clause;
        foreach ($replacements as $replacement) {
            $this->params[] = $replacement;
        }
    }

    protected function handleFilterLink(int $index): void
    {
        $this->setSession("filter_link", $index);
    }

    protected function handlePage(int $page): void
    {
        $pagination = $this->getPaginationData();
        if ($page < 0) $page = 1;
        if ($page > $pagination['total_pages']) $page = $pagination['total_pages'];
        $this->setSession("page", $page);
    }

    protected function setSession(string $key, mixed $value): void
    {
        session()->set($this->module . '_' . $key, $value);
    }

    protected function getSession(string $key): mixed
    {
        $session = session()->get($this->module . '_' . $key);
        return $session;
    }

    protected function getModuleData(): array
    {
        return [
            "title" => $this->module_title,
            "parent" => $this->module_parent,
            "route" => $this->module,
            "primary_key" => $this->primary_key,
        ];
    }

    protected function getFilterData(): array
    {
        return [
            "filter_links" => array_keys($this->filter_links),
            "filter_link_index" => $this->filter_link_index,
        ];
    }

    protected function getPermissionData(): array
    {
        return [
            "has_edit" => $this->has_edit,
            "has_create" => $this->has_create,
            "has_delete" => $this->has_delete,
        ];
    }

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

    protected function getTableData(): mixed
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
}
