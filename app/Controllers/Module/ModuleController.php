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
        parent::__construct();
        $module_class = module()->module_class;
        if ($module_class && class_exists($module_class)) {
            $this->module = new $module_class;
        }
        // Check password (warn if default admin password)
        $user = user();
        if (password_verify(config("security.default_admin_pass"), $user->password)) {
            Flash::add("warning", "You're using an insecure password<br>Please <a href='/admin/users/{$user->id}'><u>change your password</u></a> immediately to secure your account");
        }
    }

    #[Get("/{module}", "module.index")]
    public function index(string $module): string
    {
        try {
            return $this->module->view(new Table);
        } catch (Exception $ex) {
            error_log("fatal error: module.index => " . print_r($ex, true));
        }
        return "Fatal error (check logs)";
    }

    #[Get("/{module}/create", "module.create")]
    public function create(string $module): string
    {
        if (!$this->module->hasCreate()) {
            http_response_code(403);
            exit();
        }
        header("HX-Push-Url: /admin/$module/create");
        try {
            return $this->module->view(new Form);
        } catch (Exception $ex) {
            error_log("fatal error: module.create => " . print_r($ex, true));
        }
        return "Fatal error (check logs)";
    }

    #[Get("/{module}/{id}", "module.edit")]
    public function edit(string $module, int $id): string
    {
        if (!($this->module->hasEdit() && $this->module->hasEditPermission($id))) {
            http_response_code(403);
            exit();
        }
        header("HX-Push-Url: /admin/$module/$id");
        try {
            return $this->module->view(new Form, $id);
        } catch (Exception $ex) {
            error_log("fatal error: module.edit => " . print_r($ex, true));
        }
        return "Fatal error (check logs)";
    }

    #[Post("/{module}", "module.store")]
    public function store(string $module): string
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
            $message = "Fatal error ";
            $debug_mode = config("app.debug");
            if ($debug_mode) {
                $message .= $ex->getMessage();
            }
            Flash::add("danger", $message);
            error_log("fatal error: module.store => " . print_r($ex, true));
        }
        return $this->create($module);
    }

    #[Patch("/{module}/{id}", "module.update.patch")]
    #[Put("/{module}/{id}", "module.update.put")]
    public function update(string $module, int $id):  string
    {
        if (!($this->module->hasEdit() && $this->module->hasEditPermission($id))) {
            http_response_code(403);
            exit();
        }
        try {
            $valid = $this->validateRequest($this->module->getRules(), $id);
            if ($valid) {
                $result = $this->module->save($id, (array)$valid);
                if ($result) {
                    Flash::add("success", "Successfully updated record");
                } else {
                    Flash::add("warning", "Failed to update record");
                }
            }
        } catch (Exception $ex) {
            $message = "Fatal error ";
            $debug_mode = config("app.debug");
            if ($debug_mode) {
                $message .= $ex->getMessage();
            }
            Flash::add("danger", $message);
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
            $message = "Fatal error ";
            $debug_mode = config("app.debug");
            if ($debug_mode) {
                $message .= $ex->getMessage();
            }
            Flash::add("danger", $message);
            error_log("fatal error: module.destroy => " . print_r($ex, true));
        }
        return $this->index($module);
    }
}
