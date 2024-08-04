<?php

namespace Helios\Module;

class View implements IView
{
    /** Template properties */
    protected string $template = "";


    /** SQL properties */
    public string $sql_table = "";
    public string $primary_key = "";


    /** Table Properties */
    public array $table = [];
    public array $format = [];


    /** Form Properties */
    public array $form = [];

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getData(): array
    {
        return [];
    }

    protected function getQuery(): string
    {
        return "";
    }

    protected function getResult(): array|false
    {
        return [];
    }
}
