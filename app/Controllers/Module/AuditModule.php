<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/audit", middleware: ["module" => "audit"])]
class AuditModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->table = "audit";
        $this->module_title = "Audit Log";
        $this->module_parent = "Administration";
    }
}
