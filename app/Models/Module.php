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

    public function configure(View $view)
    {
        $this->view = $view;
        $this->view->processRequest();
    }

    public function getView()
    {
        return $this->view;
    }
}

