<?php

namespace App\Modules;

use App\Models\User;
use Helios\Admin\Auth;
use Helios\Model\Model;
use Helios\Module\Module;

class Users extends Module
{
    protected string $model = User::class;

    public function __construct()
    {
        controller()->addErrorMessage("regex", "Must contain: 1 uppercase, 1 number, and 1 symbol");
        $this->rules = [
            "name" => ["required"],
            //TODO: email should be unique -- if the value has changed 
            // or is created for the first time
            "email" => ["required", function($value) {
                if ($value !== 'administrator') {
                    return filter_var($value, FILTER_VALIDATE_EMAIL);
                }
                return true;
            }],
            //TODO: username should be unique -- if the value has changed 
            // or is created for the first time
            "username" => ["required"],
            "user_role_id" => ["required"],
            "password" => ["required", "min_length|8", "regex|^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"],
            "password_match" => ["required", function ($value) {
                controller()->addErrorMessage("password_match", "Passwords must match");
                return request()->get("password") === $value;
            }],
        ];

        $this->table("ID", "id")
            ->table("UUID", "uuid")
            ->table("Name", "name")
            ->table("Email", "email")
            ->table("Username", "username")
            ->table("Created", "created_at");

        $user = user();
        $this->filterLink("Me", "id = $user->id")
            ->filterLink("Others", "id != $user->id")
            ->filterLink("All", "1=1");

        $this->format("created_at", "ago");

        $this->search("name")
            ->search("username")
            ->search("email");

        $this->form("Name", "name")
            ->form("Email", "email")
            ->form("Username", "username")
            ->form("Role", "user_role_id")
            ->form("Password", "'' as password")
            ->form("Password (again)", "'' as password_match");

        $this->control("user_role_id", db()->fetchAll("SELECT id as value, name as label FROM user_roles ORDER BY name"))
            ->control("password", "password")
            ->control("password_match", "password");

        $this->default("user_role_id", db()->var("SELECT id FROM user_roles WHERE name = 'Standard'"));
    }

    public function create(array $data): ?Model
    {
        unset($data["password_match"]);
        $data["password"] = Auth::hashPassword($data["password"]);
        return parent::create($data);
    }

    public function save(int $id, array $data): bool
    {
        unset($data["password_match"]);
        $data["password"] = Auth::hashPassword($data["password"]);
        return parent::save($id, $data);
    }

    public function hasEditPermission(int $id): bool
    {
        $user = user();
        if ($id == $user->id) return true;

        if ($user->role()->permission_level < 1) {
            return true;
        }

        return false;
    }

    public function hasDeletePermission(?int $id): bool
    {
        $user = user();
        if ($id == $user->id) return true;

        if ($user->role()->permission_level < 1) {
            return true;
        }

        return false;
    }
}
