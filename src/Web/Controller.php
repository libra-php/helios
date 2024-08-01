<?php

namespace Helios\Web;

use Twig\Environment;

class Controller
{
    protected array $request_errors = [];
    protected array $error_messages = [
        "required" => "Required field",
        "email" => "Must be a valid email address",
    ];

    /**
     * Render a twig template
     */
    public function render(string $path, array $data = []): string
    {
        $twig = container()->get(Environment::class);
        // Request error array
        $data["request_errors"] = $this->request_errors;
        // Template functions
        $data["f"] = new class {
            public function route(string $name)
            {
                return route($name);
            }
        };
        return $twig->render($path, $data);
    }

    /**
     * Validate the request
     */
    public function validateRequest(array $rules): object|false
    {
        $request = request()->request;
        $validated = [];

        foreach ($rules as $key => $ruleset) {
            $value = $request->get($key);
            // Ruleset can be provided as | delimited
            if (is_string($ruleset)) {
                $ruleset = explode("|", $ruleset);
            }

            foreach ($ruleset as $rule) {
                // Is request value valid?
                $result = match (strtolower($rule)) {
                    'required' => trim($value) != '',
                    'email' =>  filter_var($value, FILTER_VALIDATE_EMAIL),
                };

                if ($result) {
                    $validated[$key] = $value;
                } else {
                    $this->addRequestError($key, $this->error_messages[$rule]);
                }
            }
        }

        return $validated ? (object)$validated : false;
    }

    protected function addRequestError(string $field, string $message)
    {
        $this->request_errors[$field][] = $message;
    }
}
