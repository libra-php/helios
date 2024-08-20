<?php

namespace App\Models;

use Helios\Model\Model;
use Helios\View\View;

class Module extends Model
{
    protected View $view;
    protected array $rules = [];

    public function __construct(?string $key = null)
    {
        parent::__construct("modules", $key);
    }

    public function configure(View $view)
    {
        $this->view = $view;
    }

    public function getView()
    {
        return $this->view;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

}

