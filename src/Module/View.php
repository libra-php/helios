<?php

namespace Helios\Module;

class View implements IView
{
    /** Template properties */
    protected string $template = "";


    /** SQL properties */
    public string $sql_table = "";
    public string $primary_key = "";
    public array $where = [];
    public array $group_by = [];
    public array $having = [];
    public string $order_column = "";
    public bool $ascending = true;

    /** Pagination */
    public int $total_results = 0;
    public int $total_pages = 0;
    public int $per_page = 5;
    public int $page = 1;


    /** Table Properties */
    public array $table = [];
    public array $format = [];


    /** Form Properties */
    public array $form = [];

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

    protected function getSession(string $name)
    {
        $module_session = session()->get($this->module) ?? [];
        return key_exists($name, $module_session) ? $module_session[$name] : null;
    }

    protected function setSession(string $name, mixed $value)
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
