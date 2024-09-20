<?php

namespace App\Modules;

use App\Models\Test;
use Helios\Module\Module;

class Tests extends Module
{
    protected string $model = Test::class;

    public function __construct()
    {
        $this->rules = [
            "control_input" => ["required"],
            "control_select" => [""],
            "control_switch" => [""],
            "control_textarea" => [""],
            "control_image" => ["required"],
            "control_file" => [""],
        ];

        $this->table("ID", "id")
             ->table("Input", "control_input");

        $this->form("Input", "control_input")
             ->form("Select", "control_select")
             ->form("Switch", "control_switch")
             ->form("Text Area", "control_textarea")
             ->form("Image", "control_image")
             ->form("File", "control_file");

        $this->control("control_select", db()->fetchAll("SELECT id as value, name as label 
            FROM user_types 
            ORDER BY name")) 
             ->control("control_switch", "switch")
             ->control("control_textarea", "textarea")
             ->control("control_image", "image")
             ->control("control_file", "file");
    }
}
