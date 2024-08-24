<?php

namespace Helios\View;

use Helios\Module\Module;

interface IView
{
    public function getTemplate(): string;
    public function getTemplateData(): array;
    public function setData(array $data): void;
    public function setModule(Module $module): void;
}
