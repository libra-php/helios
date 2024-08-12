<?php

namespace App\Modules;

use App\Models\Module;
use Helios\View\{Format, View};

class Sessions extends Module
{
    public function configure(View $view)
    {
        $user = user();
        $view->sqlTable($this->sql_table);

        $view->addTable("ID", "id")
            ->addTable("URI", "request_uri")
            ->addTable("IP", "INET_NTOA(ip) as ip")
            ->addTable("User", "(SELECT users.name FROM users WHERE users.id = user_id) as user")
            ->addTable("Module", "(SELECT modules.title FROM modules WHERE modules.id = module_id) as module")
            ->addTable("Created", "created_at");

        $view->setOrderByColumn("id");
        $view->setAscending(false);

        $view->filterLink("Me", "user_id = $user->id")
            ->filterLink("Others", "user_id != $user->id")
            ->filterLink("All", "1=1");

        $view->tableFormat("created_at", fn ($column, $value) => Format::ago($column, $value));

        parent::configure($view);
    }
}
