<?php

namespace Helios\View;

/**
 * @class Feed
 * The home feed
 */
class Feed extends View
{
    protected string $template = "admin/feed/index.html";

    public function getTemplateData(): array
    {
        return [
            ...parent::getTemplateData(),
            "data" => $this->module->getCustomData(),
        ];
    }
}
