<?php

namespace App\Modules;

use App\Models\Module;
use Helios\View\View;

class Sessions extends Module
{
    public function configure(View $view)
    {
        $user = user();

        $view->tableOnly();

        $view->sqlTable($this->sql_table);

        $view->table("ID", "id")
            ->table("URI", "request_uri")
            ->table("IP", "INET_NTOA(ip) as ip")
            ->table("User", "(SELECT users.name FROM users WHERE users.id = user_id) as user")
            ->table("Module", "(SELECT modules.title FROM modules WHERE modules.id = module_id) as module")
            ->table("Created", "created_at");

        $view->defaultOrder("id")->sortAscending(false);

        $view->filterLink("Me", "user_id = $user->id")
            ->filterLink("Others", "user_id != $user->id")
            ->filterLink("All", "1=1");

        $view->tableFormat("created_at", "ago");
    }
}
