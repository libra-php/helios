<?php

namespace App\Controllers\Module;

use Exception;
use Helios\Module\Module;
use Helios\View\{Flash, Table, Form};
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
        if (!$this->module->hasCreate()) {
            http_response_code(403);
            exit();
        }
        header("HX-Push-Url: /admin/$module/create");
        return $this->module->view(new Form);
    }

    #[Get("/{module}/{id}", "module.edit")]
    public function edit(string $module, int $id)
    {
        if (!($this->module->hasEdit() && $this->module->hasEditPermission($id))) {
            http_response_code(403);
            exit();
        }
        header("HX-Push-Url: /admin/$module/$id");
        return $this->module->view(new Form, $id);
    }

    #[Post("/{module}", "module.store")]
    public function store(string $module)
    {
        if (!$this->module->hasCreate()) {
            http_response_code(403);
            exit();
        }
        try {
            $valid = $this->validateRequest($this->module->getRules());
            if ($valid) {
                $result = $this->module->create((array)$valid);
                if ($result && $result->getKey()) {
                    Flash::add("success", "Successfully created new record");
                    return $this->edit($module, $result->getKey());
                } else {
                    Flash::add("warning", "Failed to create new record");
                }
            }
        } catch (Exception $ex) {
            Flash::add("danger", "Fatal error");
            error_log("fatal error: module.store => " . print_r($ex, true));
        }
        return $this->create($module);
    }

    #[Patch("/{module}/{id}", "module.update.patch")]
    #[Put("/{module}/{id}", "module.update.put")]
    public function update(string $module, int $id)
    {
        if (!($this->module->hasEdit() && $this->module->hasEditPermission($id))) {
            http_response_code(403);
            exit();
        }
        try {
            $valid = $this->validateRequest($this->module->getRules());
            if ($valid) {
                $result = $this->module->save($id, (array)$valid);
                if ($result) {
                    Flash::add("success", "Successfully updated record");
                } else {
                    Flash::add("warning", "Failed to update record");
                }
            }
        } catch (Exception $ex) {
            Flash::add("danger", "Fatal error");
            error_log("fatal error: module.update => " . print_r($ex, true));
        }
        return $this->edit($module, $id);
    }

    #[Delete("/{module}/{id}", "module.destroy")]
    public function destroy(string $module, int $id)
    {
        if (!($this->module->hasDelete() && $this->module->hasDeletePermission($id))) {
            http_response_code(403);
            exit();
        }
        try {
            $result = $this->module->delete($id);
            if ($result) {
                Flash::add("success", "Successfully deleted record");
            } else {
                Flash::add("warning", "Failed to delete record");
            }
        } catch (Exception $ex) {
            Flash::add("danger", "Fatal error");
            error_log("fatal error: module.destroy => " . print_r($ex, true));
        }
        return $this->index($module);
    }
}
