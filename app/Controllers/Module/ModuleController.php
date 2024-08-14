<?php

namespace App\Controllers\Module;

use App\Models\Module;
use App\Models\Session;
use Helios\View\{Table, View};
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
            $this->module = new $class($module->id);
        }
    }

    private function recordSession($module)
    {
        Session::new([
            "request_uri" => request()->getUri(),
            "ip" => ip2long(request()->getClientIp()),
            "user_id" => user()->id,
            "module_id" => $module->id,
        ]);
    }

    private function buildLinks(?int $parent_module_id = null): array
    {
        $user = user();
        if (is_null($parent_module_id)) {
            $modules = db()->fetchAll("SELECT *
				FROM modules
				WHERE parent_module_id IS NULL
                AND enabled = 1
				ORDER BY item_order");
        } else {
            $modules = db()->fetchAll(
                "SELECT *
				FROM modules
				WHERE parent_module_id = ?
                AND enabled = 1
				ORDER BY item_order",
                $parent_module_id
            );
        }
        $sidebar_links = [];
        foreach ($modules as $module) {
            // Skip the modules that the user doesn't have permission to
            if (
                !is_null($module->max_permission_level) &&
                $user->type()->permission_level > $module->max_permission_level
            ) {
                continue;
            }
            $link = [
                "id" => $module->id,
                "label" => $module->title,
                "link" => "/admin/{$module->path}",
                "children" => $this->buildLinks($module->id),
            ];
            $sidebar_links[] = $link;
        }
        // Add sign out link
        if ($parent_module_id == 2) {
            $link = [
                "id" => null,
                "label" => "Sign Out",
                "link" => "/sign-out",
                "children" => [],
            ];
            $sidebar_links[] = $link;
        }
        return $sidebar_links;
    }

    private function buildBreadcrumbs(string $module_id, $breadcrumbs = []): array
    {
        $module = db()->fetch(
            "SELECT *
            FROM modules
            WHERE id = ?
            AND enabled = 1",
            $module_id
        );
        $breadcrumbs[] = $module;
        if (intval($module->parent_module_id) > 0) {
            return $this->buildBreadcrumbs(
                $module->parent_module_id,
                $breadcrumbs
            );
        }
        return array_reverse($breadcrumbs);
    }

    private function getBreadcrumbs(?string $id): array
    {
        $path = $this->module->path;
        $breadcrumbs = $this->buildBreadcrumbs($this->module->id);
        $route_name = request()->get("route")->getName();
        if ($route_name === "module.create") {
            $breadcrumbs[] = (object) [
                "path" => "$path/create",
                "title" => "Create",
            ];
        } else if (!is_null($id)) {
            $breadcrumbs[] = (object) [
                "path" => "$path/$id",
                "title" => "Edit $id",
            ];
        }
        return $breadcrumbs;
    }

    public function renderView(View $view, ?int $id = null)
    {
        $this->recordSession($this->module);
        $this->module->configure($view);

        $template = $this->module->getView()->getTemplate();
        $data = $this->module->getView()->getData();

        // Adding module specific data
        $data['module'] = request()->get("module");
        $data['breadcrumbs'] = $this->getBreadcrumbs($id);
        $data['links'] = $this->buildLinks();

        return $this->render($template, $data);
    }

    #[Get("/{module}", "module.index")]
    public function index(string $module)
    {
        header("HX-Push-Url: /admin/$module");
        return $this->renderView(new Table);
    }

    #[Get("/{module}/create", "module.create")]
    public function create(string $module)
    {
        // header("HX-Push-Url: /admin/$module/create");
        // return $this->renderView(new Form);
        die("wip: create");
    }

    #[Get("/{module}/{id}", "module.edit")]
    public function edit(string $module, int $id)
    {
        // header("HX-Push-Url: /admin/$module");
        // return $this->renderView(new Form, $id);
        die("wip: edit");
    }

    #[Post("/{module}", "module.store")]
    public function store(string $module)
    {
        die("wip: store");
    }

    #[Patch("/{module}/{id}", "module.update.patch")]
    #[Put("/{module}/{id}", "module.update.put")]
    public function update(string $module, int $id)
    {
        die("wip: update");
    }

    #[Delete("/{module}/{id}", "module.destroy")]
    public function destroy(string $module, int $id)
    {
        die("wip: destroy");
    }
}
