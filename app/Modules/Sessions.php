<?php

namespace App\Modules;

use App\Models\Session;
use Helios\Module\Module;

class Sessions extends Module
{
    protected string $model = Session::class;

    public function __construct()
    {
        $this->has_create = $this->has_edit = $this->has_delete = false;

        // Configure table
        $this->table("ID", "id")
            ->table("URI", "request_uri")
            ->table("IP", "INET_NTOA(ip) as ip")
            ->table("User", "(SELECT users.name 
                FROM users 
                WHERE users.id = user_id) as user")
            ->table("Module", "(SELECT modules.title 
                FROM modules 
                WHERE modules.id = module_id) as module")
            ->table("Created", "created_at");

        $this->search("user")
            ->search("module");

        // Set default order/sort
        $this->defaultOrder("id")
            ->defaultSort("DESC");

        // Format columns
        $this->format("created_at", "ago");

        // Filters
        $user = user();
        $this->filterLink("Me", "user_id = $user->id")
            ->filterLink("Others", "user_id != $user->id")
            ->filterLink("All", "1=1");
    }
}
