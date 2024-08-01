<?php

namespace Helios\Trait;

trait Singleton
{
    private static $instance;

    private function __construct() {
        // Prevent instantiation from outside the class
    }

    private function __clone() {
        // Prevent cloning of the instance
    }

    public static function getInstance(...$args)
    {
        if (null === static::$instance) {
            static::$instance = new static(...$args);
        }

        return static::$instance;
    }
}
