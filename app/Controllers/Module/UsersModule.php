<?php

namespace App\Controllers\Module;

use App\Models\User;
use Helios\Admin\Auth;
use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/users", middleware: ["module" => "users"])]
class UsersModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->table = "users";
        $this->module_title = "Users";
        $this->module_parent = "Administration";
        $this->table_columns = [
            "ID" => "id",
            "UUID" => "uuid",
            "Username" => "username",
            "Role" => "(SELECT name FROM user_roles WHERE id = user_role_id) as role",
            "Email" => "email",
            "Name" => "name",
            "Created" => "created_at",
            "Updated" => "updated_at"
        ];
        $this->table_format = [
            "created_at" => "ago",
            "updated_at" => "ago",
        ];
        $this->filter_links = [
            "All" => "1=1",
            "Me" => "id=" . user()->id,
        ];
        $this->searchable = [
            "uuid",
            "username",
            "email",
            "name",
        ];

        $this->form_columns = [
            "Name" => "name",
            "Email" => "email",
            "Username" => "username",
            "Role" => "user_role_id",
            "Password" => "password",
            "Repeat Password" => "'' as password_match",
        ];
        $this->form_controls = [
            "name" => "input",
            "user_role_id" => "select",
            "password" => function ($opts) {
                $opts['value'] = '';
                return $this->password($opts);
            },
            "password_match" => function ($opts) {
                $opts['value'] = '';
                return $this->password($opts);
            },
        ];
        $this->dropdown_queries = [
            "user_role_id" => "SELECT id as value, name as label 
            FROM user_roles 
            ORDER BY name",
        ];
        $this->form_controls["email"] = $id == 1 ? "readonly" : "input";
        $this->form_controls["username"] = $id == 1 ? "readonly" : "input";

        $this->addErrorMessage("password.regex", "Must contain: 1 uppercase, 1 number, and 1 symbol");
        $this->addErrorMessage("username.regex", "Invalid username");
        $this->addErrorMessage("password_match", "Passwords must match");

        $password_pattern = "^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$";

        if ($id) {
            // Edit
            $this->validation_rules = [
                "name" => ["required"],
                "email" => ["required", ($id != 1 ? 'email' : ''), function ($value) use ($id) {
                    $user = User::find($id);
                    if ($user && $user->email === $value) return true;
                    if ($user) {
                        $this->addErrorMessage("email", "Email must be unique");
                    }
                    return !$user;
                }],
                "username" => ["required", "regex:=^[a-zA-Z0-9]+$", function ($value) use ($id) {
                    $user = User::find($id);
                    if ($user && $user->username === $value) return true;
                    if ($user) {
                        $this->addErrorMessage("username", "Username must be unique");
                    }
                    return !$user;
                }],
                "user_role_id" => ["required"],
                "password" => ["min_length:=8", "regex:=$password_pattern"],
                "password_match" => [function ($value) {
                    return request()->get("password") === $value;
                }],
            ];
        } else {
            // Create
            $this->validation_rules = [
                "name" => ["required"],
                "email" => ["required", "email", "unique:=users"],
                "username" => ["required", "unique:=users", "regex:=^[a-zA-Z0-9]+$"],
                "user_role_id" => ["required"],
                "password" => [
                    "required",
                    "min_length:=8",
                    "regex:=$password_pattern"
                ],
                "password_match" => ["required", function ($value) {
                    return request()->get("password") === $value;
                }],
            ];
        }
    }

    protected function new(array $data): ?int
    {
        $data['password'] = Auth::hashPassword($data['password']);
        $data['two_fa_secret'] = Auth::generateTwoFactorSecret();
        unset($data['password_match']);
        return parent::new($data);
    }

    protected function save(int $id, array $data): bool
    {
        if (!$data['password'] && !$data['password_match']) {
            unset($data['password']);
            unset($data['password_match']);
        } else {
            $data['password'] = Auth::hashPassword($data['password']);
            unset($data['password_match']);
        }
        return parent::save($id, $data);
    }
}
