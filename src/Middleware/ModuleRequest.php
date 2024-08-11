<?php

namespace Helios\Middleware;

use App\Models\Module;
use Closure;
use Helios\Middleware\IMiddleware;
use Symfony\Component\HttpFoundation\{Response, Request};

/**
 * Middleware
 * Configures requst for ModuleController
 */
class ModuleRequest implements IMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $middleware = $request->get("route")?->getMiddleware();
        $parameters = $request->get("route")?->getParameters();

        if (key_exists("module", $parameters) && in_array("module", $middleware)) {
            // Fetch from db
            $module = Module::findByAttribute("path", $parameters["module"]);
            // The target module class is defined in class_name
            if ($module && class_exists($module->class_name)) {
                if (key_exists("id", $parameters)) {
                    // See if module exists
                    $exists = db()->fetch("SELECT * FROM {$module->sql_table} WHERE {$module->primary_key} = ?", $parameters['id']);
                    if (!$exists) {
                        // This module doesn't exist
                        redirect(route("error.page-not-found"));
                    }
                }
                // Store the module in the request
                $request->attributes->add(["module" => $module]);
            } else {
                // The module doesn't seem to exist
                redirect(route("error.page-not-found"));
            }
        }

        $response = $next($request);

        return $response;
    }
}

