<?php

namespace Helios\View;

/**
 * @class Post
 * A single post
 */
class Post extends View
{
    protected string $template = "admin/post/index.html";

    public function getTemplateData(): array
    {
        return [
            ...parent::getTemplateData(),
            "id" => $this->module->getId(),
            "data" => $this->module->getCustomData(),
        ];
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
                    "title" => "Show $id",
                ];
            } 
        }
        return $breadcrumbs;
    }
}
