<?php

namespace Helios\View;

class Flash
{
    /**
     * Add a flash message to the messages array
     * @param string $type (warning,danger,success,info,etc)
     * @param string $message
     */
    public static function add(string $type, string $message): void
    {
        $flash = session()->get("flash") ?? [];
        $flash[strtolower($type)][] = $message;
        session()->set("flash", $flash);
    }

    /**
     * Get flash messages array
     * @return array
     */
    public static function get(): array
    {
        $flash = session()->get("flash") ?? [];
        session()->set("flash", []);
        return $flash;
    }
}
