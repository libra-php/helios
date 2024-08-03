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


    /** Form Properties */
    public array $form = [];

    public function getTemplate(): string
    {
        return $this->template;
    }

    protected function getQuery(): string
    {
        return "";
    }

    private function getResult(): array
    {
        return [];
    }

    public function getData(): array
    {
        return [];
    }
}
