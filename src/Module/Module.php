<?php

namespace Helios\Module;

class Module
{
    protected string $name;
    protected string $path;
    protected View $view;

    public function configure(View $view)
    {
        $this->view = $view;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getView()
    {
        return $this->view;
    }
}
