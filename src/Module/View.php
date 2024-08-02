<?php

namespace Helios\Module;

class View implements IView
{
    /** Template properties */
    public string $template = "";


    /** SQL properties */
    public string $sql_table = "";
    public array $data = [];


    /** Table Properties */
    public array $table = [];


    /** Form Properties */
    public array $form = [];
}
