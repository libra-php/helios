<?php

namespace Helios\View;

use App\Models\Module as ModelsModule;
use Helios\Database\QueryBuilder;
use Helios\Module\Module;

class View implements IView
{
    protected Module $module;
    private array $data = [];
    protected string $template = "/admin/module/view.html";

    public function setModule(Module $module): void
    {
        $this->module = $module;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getTemplateData(): array
    {
        return [
            "id" => $this->module->getId(),
            "key_column" => $this->module->getKeyColumn(),
            "view" => $this,
            "data" => $this->data,
            "module" => module(),
            "links" => $this->buildLinks(),
            "breadcrumbs" => $this->getBreadcrumbs($this->module->getId()),
            "permissions" => [
                "has_cancel" => $this->module->hasCancel(),
                "has_edit" => $this->module->hasEdit(),
                "has_create" => $this->module->hasCreate(),
                "has_delete" => $this->module->hasDelete(),
            ]
        ];
    }

    protected function buildBreadcrumbs(string $module_id, array $breadcrumbs = []): array
    {
        $module = QueryBuilder::select()
            ->from("modules")
            ->where(["id = ? AND enabled = 1"], $module_id)
            ->execute()
            ->fetch();
        $breadcrumbs[] = $module;
        if ($module && intval($module->parent_module_id) > 0) {
            return $this->buildBreadcrumbs(
                $module->parent_module_id,
                $breadcrumbs
            );
        }
        return array_reverse($breadcrumbs);
    }

    protected function getBreadcrumbs(?string $id): array
    {
        $module = module();
        $route = route();
        $path = $module->path;
        $breadcrumbs = $this->buildBreadcrumbs($module->id);
        if (
            $route->getName() !== "module.index" &&
            $route->getName() !== "module.destroy"
        ) {
            if ($id) {
                $breadcrumbs[] = (object) [
                    "path" => "$path/$id",
                    "title" => "Edit $id",
                ];
            } else {
                $breadcrumbs[] = (object) [
                    "path" => "$path/create",
                    "title" => "Create New",
                ];
            }
        }
        return $breadcrumbs;
    }

    protected function buildLinks(?int $parent_module_id = null): array
    {
        $user = user();
        if (is_null($parent_module_id)) {
            $modules = QueryBuilder::select()
                ->from("modules")
                ->where(["parent_module_id IS NULL AND enabled = 1"])
                ->orderBy(["item_order"])
                ->execute()
                ->fetchAll();
        } else {
            $modules = QueryBuilder::select()
                ->from("modules")
                ->where(["parent_module_id = ? AND enabled = 1"], $parent_module_id)
                ->orderBy(["item_order"])
                ->execute()
                ->fetchAll();
        }
        $sidebar_links = [];
        foreach ($modules as $module) {
            // Skip the modules that the user doesn't have permission to
            if (
                !is_null($module->max_permission_level) &&
                $user->role()->permission_level > $module->max_permission_level
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
        $account_module = ModelsModule::findByAttribute("title", "Account");
        if ($parent_module_id == $account_module->id) {
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
}
