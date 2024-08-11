<?php

namespace App\Models;

use Helios\Model\Model;
use Helios\View\View;

class Module extends Model
{
    protected View $view;

    public function __construct(?string $key = null)
    {
        parent::__construct("modules", $key);
    }

    private function recordSession()
    {
        db()->query("INSERT INTO sessions SET
            request_uri = ?,
            ip = INET_ATON(?),
            user_id = ?,
            module_id = ?", ...[
            request()->get("route")->getPath(),
            request()->getClientIp(),
            user()->id,
            $this->id,
        ]);
    }

    public function configure(View $view)
    {
        $this->view = $view;
    }

    public function getView()
    {
        return $this->view;
    }
}

