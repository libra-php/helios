<?php

namespace Helios\View;

use App\Models\Post as PostModel;

/**
 * @class Post
 * A single post
 */
class Post extends View
{
    protected string $template = "admin/post/index.html";

    public function getTemplateData(): array
    {
        $id = $this->module->getId();
        return [
            ...parent::getTemplateData(),
            "id" => $id,
            "og" => $this->getMeta($id),
            "data" => $this->module->getCustomData(),
        ];
    }

    protected function getMeta($id)
    {
        $post = PostModel::findOrFail($id);
        if ($post) {
            $app = config("app");
            return [
                'title' => '@' . $post->user()->username . "'s post on " . $app['name'],
                'description' => substr($post->body, 0, 150) . '...',
                'image' => $app['url'] . $post->user()->avatar(),
                'url' => $app['url'] . "/admin/feed/" . $post->id,
                'type' => 'article'
            ];
        }
        return [];
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
