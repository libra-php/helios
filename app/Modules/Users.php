<?php

namespace App\Modules;

use App\Models\User;
use Helios\Module\Module;

class Users extends Module
{
    protected string $model = User::class;

    public function __construct()
    {
        $user = user();

        $this->addTable("ID", "id")
            ->addTable("UUID", "uuid")
            ->addTable("Name", "name")
            ->addTable("Email", "email")
            ->addTable("Created", "created_at");

        $this->filterLink("Me", "id = $user->id")
            ->filterLink("Others", "id != $user->id")
            ->filterLink("All", "1=1");

        $this->formatTable("created_at", "ago");

        $this->addSearch("name")
            ->addSearch("email");
    }
}
