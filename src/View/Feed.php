<?php

namespace Helios\View;

/**
 * @class Feed
 * The home feed
 */
class Feed extends View
{
    protected string $template = "admin/module/feed.html";

    public function getTemplateData(): array
    {
        return [
            ...parent::getTemplateData(),
            "data" => $this->module->getCustomData(),
        ];
    }
}
