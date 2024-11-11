<?php

namespace Helios\Admin;

use Helios\Database\QueryBuilder;
use Helios\Web\Controller;
use PDO;
use StellarRouter\{Get, Group};

#[Group(middleware: ["auth"])]
class ModuleController extends Controller
{
    // The module title
    protected string $title = '';
    // The module parent
    protected string $parent = '';
    // The module route
    protected string $route = '';

    // The sql table
    protected string $table = '';

    // Table columns 
    protected array $table_columns = [];
    // Table format
    protected array $table_format = [];

    #[Get("/", "module.index")]
    function index(): string
    {
        $path = route()->getPath();
        header("HX-Push-Url: $path");
        return $this->render("/admin/module/index.html", [
            "module" => [
                "title" => $this->title,
                "parent" => $this->parent,
                "route" => $this->route,
            ],
            "table" => [
                "data" => $this->getTableData(),
                "headers" => array_keys($this->table_columns)
            ],
        ]);
    }

    #[Get("/edit/{id}", "module.edit")]
    function edit(int $id): string
    {
        return $this->render("/admin/module/edit.html", [
            "id" => $id,
            "title" => $this->title,
            "route" => $this->route,
        ]);
    }

    #[Get("/create", "module.create")]
    function create(): string
    {
        return $this->render("/admin/module/create.html", [
            "title" => $this->title
        ]);
    }

    protected function buildTableQuery(): QueryBuilder
    {
        $qb = new QueryBuilder;
        return $qb
            ->select(array_values($this->table_columns))
            ->from($this->table);
    }

    protected function getTableData(): array|bool
    {
        $qb = $this->buildTableQuery();
        return $qb->execute()->fetchAll(PDO::FETCH_ASSOC);
    }
}
