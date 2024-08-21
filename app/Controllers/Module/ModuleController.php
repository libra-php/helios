<?php

namespace App\Controllers\Module;

use App\Models\{Module, Session, UserType};
use Helios\View\{Table, Form, IView};
use Helios\Web\Controller;
use StellarRouter\{Get, Post, Put, Patch, Delete, Group};

#[Group(prefix: "/admin", middleware: ['auth', 'module'])]
class ModuleController extends Controller
{
    private $module;

    public function __construct()
    {
        $module = request()->get("module");
        if ($module && class_exists($module->class_name)) {
            $class = $module->class_name;
            $this->module = new $class($module->id);
        }
    }

    private function recordSession($module)
    {
        // Session::new([
        //     "request_uri" => request()->getUri(),
        //     "ip" => ip2long(request()->getClientIp()),
        //     "user_id" => user()->id,
        //     "module_id" => $module->id,
        // ]);
    }

    public function renderView(IView $view)
    {
        $this->module->configure($view);
        $view->processRequest();
        $this->recordSession($this->module);
        $template = $view->getTemplate();
        $data = $view->getData();
        $data['view'] = $view;
        return $this->render($template, $data);
    }

    #[Get("/{module}", "module.index")]
    public function index(string $module)
    {
        return $this->renderView(new Table);
    }

    #[Get("/{module}/create", "module.create")]
    public function create(string $module)
    {
        header("HX-Push-Url: /admin/$module/create");
        return $this->renderView(new Form);
    }

    #[Get("/{module}/{id}", "module.edit")]
    public function edit(string $module, int $id)
    {
        header("HX-Push-Url: /admin/$module/$id");
        return $this->renderView(new Form($id));
    }

    #[Post("/{module}", "module.store")]
    public function store(string $module)
    {
        $valid = $this->validateRequest($this->module->getRules());
        if ($valid) {
            dd($this->module);
            if (!is_null($id)) {
                return $this->edit($module, $id);
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
        }
        return $this->eit($module, $id);
    }

    #[Delete("/{module}/{id}", "module.destroy")]
    public function destroy(string $module, int $id)
    {
        return $this->index($module);
    }
}
