<?php

namespace Helios\Module;

class View implements IView
{
    /** Template properties */
    protected string $template = "";


    /** SQL properties */
    protected string $sql_table = "";
    protected string $primary_key = "";
    protected array $where = [];
    protected array $group_by = [];
    protected array $having = [];
    protected string $order_column = "";
    protected bool $ascending = true;


    /** Pagination */
    protected int $total_results = 0;
    protected int $total_pages = 0;
    protected int $per_page = 5;
    protected int $page = 1;


    /** Table Properties */
    protected array $table = [];
    protected array $format = [];
    protected array $filter_links = [];
    protected int $filter_link_index = 0;
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

    public function __construct(private string $module)
    {
    }

    public function processRequest(): void
    {
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getData(): array
    {
        return [];
    }

    public function sqlTable(string $name)
    {
        $this->sql_table = $name;
        return $this;
    }

    public function addTable(string $title, string $subquery)
    {
        $this->table[$title] = $subquery;
        return $this;
    }

    public function tableFormat(string $column, callable $callback)
    {
        $this->format[$column] = $callback;
        return $this;
    }

    public function filterLink(string $title, string $subquery)
    {
        $this->filter_links[$title] = $subquery;
        return $this;
    }

    public function addForm(string $title, string $subquery)
    {
        $this->form[$title] = $subquery;
        return $this;
    }

    public function addSearch(string $column)
    {
        $this->searchable[] = $column;
        return $this;
    }

    public function getSession(string $name)
    {
        $module_session = session()->get($this->module) ?? [];
        return key_exists($name, $module_session) ? $module_session[$name] : null;
    }

    public function hasSession(string $name)
    {
        $module_session = session()->get($this->module) ?? [];
        return key_exists($name, $module_session);
    }

    public function setSession(string $name, mixed $value)
    {
        $module_session = session()->get($this->module) ?? [];
        $module_session[$name] = $value;
        session()->set($this->module, $module_session);
    }

    protected function getQuery(): string
    {
        return "";
    }

    protected function getPayload(): array|false
    {
        return [];
    }
}
