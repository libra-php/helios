<?php

namespace App\Controllers\Module;

use App\Models\Module;
use Helios\View\{Form, Table, View};
use Helios\Web\Controller;
use StellarRouter\{Get, Post, Put, Patch, Delete, Group};

#[Group(prefix: "/admin", middleware: ['auth', 'module'])]
class ModuleController extends Controller
{
    private Module $module;

    public function __construct()
    {
        $module = request()->get("module");
        if ($module && class_exists($module->class_name)) {
            $class = $module->class_name;
            $this->module = new $class;
        }
    }

    public function renderView(View $view)
    {
        $this->module->configure($view);
        $template = $this->module->getView()->getTemplate();
        $data = $this->module->getView()->getData();
        // Adding module specific data
        $data['module'] = request()->get("module");
        return $this->render($template, $data);
    }

    #[Get("/{module}", "module.index")]
    public function index($module)
    {
        header("HX-Push-Url: /admin/$module");
        return $this->renderView(new Table);
    }

    #[Get("/{module}/create", "module.create")]
    public function create($module)
    {
        header("HX-Push-Url: /admin/$module/create");
        return $this->renderView(new Form);
    }

    #[Get("/{module}/{id}", "module.edit")]
    public function edit($module, $id)
    {
        header("HX-Push-Url: /admin/$module");
        return $this->renderView(new Form);
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
