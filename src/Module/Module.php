<?php

namespace Helios\Module;

class Module
{
    protected View $view;

    public function configure(View $view)
    {
        $this->view = $view;
    }

    public function getTemplate()
    {
        return $this->view->template;
    }

    public function getData()
    {
        return $this->view->data;
    }
}
