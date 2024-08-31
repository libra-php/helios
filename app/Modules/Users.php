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

        $this->table("ID", "id")
            ->table("UUID", "uuid")
            ->table("Name", "name")
            ->table("Email", "email")
            ->table("Created", "created_at");

        $this->filterLink("Me", "id = $user->id")
            ->filterLink("Others", "id != $user->id")
            ->filterLink("All", "1=1");

        $this->format("created_at", "ago");

        $this->search("name")
            ->search("email");
    }

    public function hasRowDelete(?int $id): bool
    {
        // You may not delete your own user from here
        // Delete from profile
        return $id != user()->id;
    }
}
