<?php

namespace Helios\Module;

class View implements IView
{
    public string $sql_table = "";
    public string $template = "";
    public array $data = [];
    public array $table_columns = [];
}
