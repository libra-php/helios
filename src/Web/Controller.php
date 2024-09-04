<?php

namespace Helios\Web;

use Helios\View\Flash;
use Twig\Environment;

class Controller
{
    protected array $request_errors = [];
    protected array $error_messages = [
        "required" => "Required field",
        "email" => "Must be a valid email address",
        "min" => "Value is less than minimum",
        "max" => "Value is greater than maximum",
        "unique" => "Value must be unique",
    ];

    /**
     * Render a twig template
     */
    public function render(string $path, array $data = []): string
    {
        $twig = container()->get(Environment::class);
        $data["request_errors"] = $this->request_errors;
        if (count($this->request_errors) > 0) {
            Flash::add("warning", "Validation error");
        }
        $data["flash"] = Flash::get();
        $data["nonce"] = session()->get("nonce");
        return $twig->render($path, $data);
    }

    /**
     * Validate the request
     */
    public function validateRequest(array $rules): object|false
    {
        $valid = true;
        $request = request()->request;
        $validated = [];

        foreach ($rules as $key => $ruleset) {
            $value = $request->get($key);
            // Ruleset can be provided as | delimited
            if (is_string($ruleset)) {
                $ruleset = explode("|", $ruleset);
            }

            // Empty rulesets are valid
            if (empty($ruleset)) {
                $validated[$key] = $value;
                continue;
            }

            foreach ($ruleset as $rule) {
                $_rule = explode('|', $rule);
                $rule = $_rule[0];
                $rule_arg = $_rule[1] ?? null;
                // Is request value valid?
                $result = match (strtolower($rule)) {
                    'required' => !is_null($value) && trim($value) != '',
                    'email' =>  filter_var($value, FILTER_VALIDATE_EMAIL),
                    'url' => filter_var($value, FILTER_VALIDATE_URL),
                    'ip' => filter_var($value, FILTER_VALIDATE_IP),
                    'int' => filter_var($value, FILTER_VALIDATE_INT),
                    'float' => filter_var($value, FILTER_VALIDATE_FLOAT),
                    'min' => $value >= $rule_arg,
                    'max' => $value <= $rule_arg,
                    'unique' => !db()->fetch("SELECT 1 FROM $rule_arg WHERE $key = ?", $value),
                };

                if ($result) {
                    $validated[$key] = $value;
                } else {
                    $valid = false;
                    $this->addRequestError($key, $this->error_messages[$rule] ?? '');
                }
            }
        }

        return $valid ? (object)$validated : false;
    }

    protected function addRequestError(string $field, string $message)
    {
        if (trim($message) != '') $this->request_errors[$field][] = $message;
    }
}
