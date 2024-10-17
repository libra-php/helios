<?php

namespace App\Modules;

use App\Models\File;
use Helios\Module\Module;

class Files extends Module
{
    protected string $model = File::class;

    public function __construct()
    {
        $this->has_create = $this->has_edit = false;

        $this->table("ID", "id")
            ->table("Name", "filename")
            ->table("Original", "original_name")
            ->table("Mime Type", "mime_type")
            ->table("Size", "size");

        $this->search("filename")
             ->search("original_name")
             ->search("mime_type");
    }
}
