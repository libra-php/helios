<?php

namespace App\Console;

use Helios\Kernel\Adapter as KernelAdapter;
use splitbrain\phpcli\Options;

class Adapter extends KernelAdapter
{
    /**
     * Register commands / options
     */
    protected function register(Options $options)
    {
        $options->registerOption("version", "Print version", "v");
    }

    /**
     * Configure option here
     */
    protected function option(string $option)
    {
        match ($option) {
            'version' => $this->version(),
            default => null,
        };
    }

    /**
     * Configure command here
     */
    protected function command(string $command)
    {
        match ($command) {
            default => null,
        };
    }

    /**
     * Display's current application version
     */
    private function version(): void
    {
        $version = config("app.version");
        $this->info("Helios 🌠");
        $this->info("-----------------------------------");
        $this->info("Version: $version");
        $this->info("https://github.com/libra-php/helios");
        exit();
    }
}
