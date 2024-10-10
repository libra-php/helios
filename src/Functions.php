<?php

/**
 * Helper functions
 */

use App\Console\Kernel as ConsoleKernel;
use App\Http\Kernel as HttpKernel;
use App\Models\User;
use App\Models\Module;
use Helios\Admin\Auth;
use Helios\Application;
use Helios\Web\Controller;
use Helios\Session\Session;
use Lunar\Connection\MySQL;
use StellarRouter\Route;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

/**
 * Return a web application
 */
function app()
{
    $kernel = HttpKernel::getInstance();
    return Application::getInstance($kernel);
}

/**
 * Return a console application
 */
function console()
{
    $kernel = ConsoleKernel::getInstance();
    return Application::getInstance($kernel);
}

/**
 * Print a debug
 */
function dump(...$data)
{
    $debug = debug_backtrace()[1];
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
 * @return mixed configuration settinns
 */
function config(string $name): mixed
{
    // There could be a warning if $attribute
    // is not set, so let's silence it
    @[$file, $key] = explode(".", $name);
    $config_path = __DIR__ . "/../config/$file.php";
    if (file_exists($config_path)) {
        $config = require $config_path;
        if (!is_array($config)) throw new Error("config: must be an array");
        if ($key && key_exists($key, $config)) {
            return $config[$key];
        } else if ($key && !key_exists($key, $config)) {
            throw new Error("config: key doesn't exist");
        } else {
            return $config;
        }
    }
    throw new Error("config: name: $name doesn't exist");
}

/**
 * Return application DI container
 */
function container(): DI\Container
{
    return app()->container();
}

/**
 * Return application mysql class
 */
function db(): MySQL
{
    return container()->get(MySQL::class);
}

/**
 * Return http request
 */
function request(): Request
{
    return container()->get(Request::class);
}

/**
 * Return current route
 */
function route(): ?Route
{
    return request()->get("route");
}

/**
 * Return web controller
 */
function controller(): ?Controller
{
    return request()->get("controller");
}

/**
 * Return admin module
 */
function module(): ?Module
{
    return request()->get("module");
}

/**
 * Return app session
 */
function session(): Session
{
    return Session::getInstance();
}

/**
 * Return app user
 */
function user(): ?User
{
    return Auth::user();
}

/**
 * Find a route by name
 */
function findRoute(string $name, ...$replacements): ?string
{
    $router = app()->router();
    $route = $router->findRouteByName($name);

    if ($route === null) {
        return null;
    }

    $path = $route->getPath();

    foreach ($replacements as $replacement) {
        $path = preg_replace('/\{\w+\}/', $replacement, $path, 1);
    }

    return $path;
}

/**
 * Redirect to an enpoint (htmx)
 */
function redirect(string $path, array $options = [
    "target" => "main",
    "select" => "main",
    "swap" => "outerHTML"
]): void
{
    if (request()->headers->has("HX-Request")) {
        $options['path'] = $path;
        $header =  sprintf("HX-Location:%s", json_encode($options));
        header($header);
        exit;
    } else {
        $header =  sprintf("Location:%s", $path);
        header($header);
        exit;
    }
}

/**
 * Return a template string
 */
function template(string $path, array $data = []): string
{
    $twig = container()->get(Environment::class);
    return $twig->render($path, $data);
}

/**
 * Create a Hx-Trigger
 */
function trigger(string $hook_name)
{
    header("Hx-Trigger: $hook_name");
}
