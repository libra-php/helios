<?php

namespace App\Modules;

use App\Models\Session;
use Helios\Module\Module;

class Sessions extends Module
{
    protected string $model = Session::class;

    public function __construct()
    {
        // Configure table
        $this->addTable("ID", "id")
            ->addTable("URI", "request_uri")
            ->addTable("IP", "INET_NTOA(ip) as ip")
            ->addTable("User", "(SELECT users.name FROM users WHERE users.id = user_id) as user")
            ->addTable("Module", "(SELECT modules.title FROM modules WHERE modules.id = module_id) as module")
            ->addTable("Created", "created_at");

        // Set default order/sort
        $this->defaultOrder("id")->defaultSort("DESC");

        // Format columns
        $this->formatTable("created_at", "ago");

        // Filters
        $user = user();
        $this->filterLink("Me", "user_id = $user->id")
            ->filterLink("Others", "user_id != $user->id")
            ->filterLink("All", "1=1");
    }
}
