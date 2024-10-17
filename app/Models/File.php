<?php

namespace App\Models;

use Helios\Model\Model;

class File extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("files", $key);
    }

    public function destroy(): bool
    {
        $result = parent::destroy();
        if ($result) {
            $uploads_dir = config("paths.uploads");
            unlink($uploads_dir . $this->filename);
        }
        return $result;
    }
}
