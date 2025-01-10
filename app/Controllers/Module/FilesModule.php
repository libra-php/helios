<?php

namespace App\Controllers\Module;

use App\Models\File;
use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/files", middleware: ["module" => "files"])]
class FilesModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->roles = ["Super Admin"];
        $this->table = "files";
        $this->module_title = "Files";
        $this->link_parent = "Storage";
        $this->table_columns = [
            "ID" => "id",
            "Name" => "name",
            "Created" => "created_at",
        ];
        $this->table_format = [
            "created_at" => "ago",
        ];
    }

    protected function delete(int $id): bool
    {
        $file = File::find($id);
        $success = parent::delete($id);
        if ($success) {
            $path = $file->path;
            if (file_exists($path)) {
                unlink($path);
            }
        }
        return $success;
    }
}
