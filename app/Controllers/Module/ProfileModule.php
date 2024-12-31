<?php

namespace App\Controllers\Module;

use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/profile", middleware: ["module" => "profile"])]
class ProfileModule extends UsersModule
{
    public function init(?int $id): void
    {
        parent::init($id);
        $this->export_csv = false;
        $this->has_create = false;
        $this->searchable = [];
        $this->filter_links = [
            "Me" => "id=".user()->id
        ];
        $this->roles = [];
        $this->module_title = "Profile";
        $this->link_parent = "Account";
    }
}
