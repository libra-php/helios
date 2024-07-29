<?php

use Helios\Application;
use Helios\Kernel\{HTTP, Console};

/**
 * Return a web application
 */
function app()
{
    $app = new Application(new HTTP);
    return $app;
}

/**
 * Return a console application
 */
function console()
{
    $app = new Application(new Console);
    return $app;
}

/**
 * Print a debug
 */
function dump(...$data)
{
    $debug = debug_backtrace()[0];
    $pre_style =
        "overflow-x: auto; font-size: 0.6rem; border-radius: 10px; padding: 10px; background: #133; color: azure; border: 3px dotted azure;";
    $scrollbar_style =
        "scrollbar-width: thin; scrollbar-color: #5EFFA1 #113333;";

    foreach ($data as $datum) {
        if (php_sapi_name() === "cli") {
            print_r($datum);
        } else {
            printf(
                "<pre style='%s %s'><div style='margin-bottom: 5px;'><strong style='color: #5effa1;'>DUMP</strong></div><div style='margin-bottom: 5px;'><strong>File:</strong> %s:%s</div><div style='margin-top: 10px;'>%s</div></pre>",
                $pre_style,
                $scrollbar_style,
                $debug["file"],
                $debug["line"],
                print_r($datum, true)
            );
        }
    }
}

/**
 * Print a debug and die
 */
function dd($data)
{
    dump($data);
    die();
}

/**
 * Get application environment setting
 * If the env key is not present, then a
 * default value may be specified and returned
 */
function env(string $name, $default = "")
{
    if (isset($_ENV[$name])) {
        $lower = strtolower($_ENV[$name]);
        return match ($lower) {
            "true" => true,
            "false" => false,
            default => $_ENV[$name],
        };
        return $_ENV[$name];
    }
    return $default;
}

/**
 * Get application configuration settings
 * @param string $name name of the configuration attribute
 * @return mixed configuration settings
 */
function config(string $name): mixed
{
    // There could be a warning if $attribute
    // is not set, so let's silence it
    @[$file, $key] = explode(".", $name);
    $config_path = __DIR__ . "/../app/Config/$file.php";
    if (file_exists($config_path)) {
        $config = require $config_path;
        if (!is_array($config)) throw new Error("config: must be an array");
        return $key && key_exists($key, $config)
            ? $config[$key]
            : $config;
    }
    throw new Error("config: name: $name doesn't exist");
}

/**
* Return application mysql class
*/
function db()
{
    return app()->container()->get("mysql");
}
