<?php

namespace App\Controllers\Admin;

use Helios\Module\{Form, Module, Table};
use Helios\Web\Controller;
use StellarRouter\{Get, Post, Put, Patch, Delete, Group};

#[Group(prefix: "/admin", middleware: ['auth'])]
class ModuleController extends Controller
{
    private Module $module;

    public function __construct()
    {
        $module = request()->get("route")?->getParameters()['module'];
        $this->init($module);
    }

    private function init(string $module): void
    {
        $module = ucfirst($module);
        $class = "\\App\\Modules\\$module";
        if (class_exists($class)) {
            $this->module = new $class;
        } else {
            redirect(route("error.page-not-found"));
        }
    }

    #[Get("/{module}", "module.index")]
    public function index($module)
    {
        $this->module->configure(new Table);
        return $this->render($this->module->getTemplate(), $this->module->getData());
    }

    #[Get("/{module}/create", "module.create")]
    public function create($module)
    {
        $this->module->configure(new Form);
        return $this->render($this->module->getTemplate(), $this->module->getData());
    }

    #[Get("/{module}/{id}/edit", "module.edit")]
    public function edit($module, $id)
    {
        $this->module->configure(new Form);
        return $this->render($this->module->getTemplate(), $this->module->getData());
    }

    #[Post("/{module}", "module.store")]
    public function store($module)
    {
        die("wip: store");
    }

    #[Patch("/{module}/{id}", "module.update.patch")]
    #[Put("/{module}/{id}", "module.update.put")]
    public function update($module, $id)
    {
        die("wip: update");
    }

    #[Delete("/{module}/{id}", "module.destroy")]
    public function destroy($module, $id)
    {
        die("wip: destroy");
    }
}
