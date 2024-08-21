<?php

namespace App\Modules;

use Helios\View\View;

class Module
{
    protected View $view;
    protected array $rules = [];

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

    public function create(array $data)
    {

    }

    public function save(array $data)
    {

    }

    public function delete()
    {

    }
}
