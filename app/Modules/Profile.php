<?php

namespace App\Modules;

use Helios\Module\IView;
use Helios\Module\Module;

class Profile extends Module
{
    public function configure(IView $view)
    {
        $view->table_columns = [
            "ID" => "id",
            "UUID" => "uuid",
            "Email" => "email",
            "Created" => "created_at",
            "Updated" => "updated_at",
        ];
        parent::configure($view);
        dd($this->view);
    }
}
