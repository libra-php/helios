<?php

namespace Helios\Trait;

trait Singleton
{
    private static $instance;

    public static function getInstance(...$args)
    {
        if (null === static::$instance) {
            static::$instance = new static(...$args);
        }

        return static::$instance;
    }
}
