<?php

namespace App\Modules;

use App\Modules\Users;
use App\Models\User;
use Helios\View\Form;
use Helios\View\IView;

class Profile extends Users
{
    private User $user;

    public function __construct()
    {
        parent::__construct(); 
        // No search
        $this->clearSearch();
        // No filter links
        $this->clearFilterLinks();
        // User cannot edit role / privs
        $this->removeForm("Role");
        unset($this->rules["user_role_id"]);
        // No creation of new users
        $this->has_create = false;
        // No cancel button (no table view)
        $this->has_cancel = false;

        // You can only view your own profile
        $this->user = user();
        $this->where("id = ?", $this->user->id);

    }

    public function view(IView $view, ?int $id = null): string
    {
        // We want to stay on the profile route
        header("Hx-Push-Url: /admin/profile");
        return parent::view(new Form, $this->user->id);
    }

    public function hasEditPermission(int $id): bool
    {
        // You can only edit your own user
        if ($id == $this->user->id) return true;

        return false;
    }

    public function hasDeletePermission(?int $id): bool
    {
        // You can only delete your own user
        if ($id == $this->user->id) return true;

        return false;
    }
}

