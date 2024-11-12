<?php

namespace Helios\Admin;

use Helios\Database\QueryBuilder;
use Helios\Web\Controller;
use PDO;
use StellarRouter\{Get, Group};

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
    protected string $table = '';
    protected array $where = [];
    protected array $order_by = [];
    protected int $per_page = 10;
    protected int $page = 1;

    // Table columns 
    protected array $table_columns = [];
    // Table format
    protected array $table_format = [];


    public function __construct()
    {
        $this->module = route()->getMiddleware()["module"];
    }

    #[Get("/", "module.index")]
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
        ]);
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
        return $this->render("/admin/module/edit.html", [
            "id" => $id,
        ]);
    }

    #[Get("/create", "module.create")]
    public function create(): string
    {
        return $this->render("/admin/module/create.html", []);
    }

    protected function setState(): void
    {
        $this->page = $this->getSession("page") ?? $this->page;
    }

    protected function processRequest(): void {}

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
            ->execute()
            ->rowCount();
    }
}
