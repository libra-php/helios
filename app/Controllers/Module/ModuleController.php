<?php

namespace App\Controllers\Module;

use Helios\Module\Module;
use Helios\View\{Table, Form};
use Helios\Web\Controller;
use StellarRouter\{Get, Post, Put, Patch, Delete, Group};

#[Group(prefix: "/admin", middleware: ['auth', 'module'])]
class ModuleController extends Controller
{
    private Module $module;

    public function __construct()
    {
        $module_class = module()->module_class;
        if ($module_class && class_exists($module_class)) {
            $this->module = new $module_class;
        }
    }

    #[Get("/{module}", "module.index")]
    public function index(string $module)
    {
        return $this->module->view(new Table);
    }

    #[Get("/{module}/create", "module.create")]
    public function create(string $module)
    {
        header("HX-Push-Url: /admin/$module/create");
        return $this->module->view(new Form);
    }

    #[Get("/{module}/{id}", "module.edit")]
    public function edit(string $module, int $id)
    {
        header("HX-Push-Url: /admin/$module/$id");
        return $this->module->view(new Form, $id);
    }

    #[Post("/{module}", "module.store")]
    public function store(string $module)
    {
        $valid = $this->validateRequest($this->module->getRules());
        if ($valid) {
            $result = $this->module->create((array)$valid);
            if ($result) {
                return $this->edit($module, $result->getKey());
            } else {
                // TODO: set alert
            }
        }
        return $this->create($module);
    }

    #[Patch("/{module}/{id}", "module.update.patch")]
    #[Put("/{module}/{id}", "module.update.put")]
    public function update(string $module, int $id)
    {
        $valid = $this->validateRequest($this->module->getRules());
        if ($valid) {
            $result = $this->module->save($id, (array)$valid);
            if ($result) {
                // TODO: set alert
            } else {
                // TODO: set alert
            }
        }
        return $this->edit($module, $id);
    }

    #[Delete("/{module}/{id}", "module.destroy")]
    public function destroy(string $module, int $id)
    {
        $result = $this->module->delete($id);
        if ($result) {
            // TODO: set alert
        } else {
            // TODO: set alert
        }
        return $this->index($module);
    }
}
