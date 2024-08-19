<?php

namespace Helios\View;

interface IView
{
    public function getTemplate(): string;
    public function getData(): array;
    public function processRequest(): void;
}
