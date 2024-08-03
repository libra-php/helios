<?php

namespace App\Controllers\Module;

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

    public function renderView()
    {
        $template = $this->module->getView()->getTemplate();
        $data = $this->module->getView()->getData();
        // Adding module specific data
        $data['module_name'] = $this->module->getName();
        $data['module_path'] = $this->module->getPath();
        return $this->render($template, $data);
    }

    private function init(string $module): void
    {
        // FIXME: this is unreliable
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
        return $this->renderView();
    }

    #[Get("/{module}/create", "module.create")]
    public function create($module)
    {
        $this->module->configure(new Form);
        return $this->renderView();
    }

    #[Get("/{module}/{id}/edit", "module.edit")]
    public function edit($module, $id)
    {
        $this->module->configure(new Form);
        return $this->renderView();
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
