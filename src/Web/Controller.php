<?php

namespace Helios\Web;

use Helios\View\Flash;
use InvalidArgumentException;
use Twig\Environment;

class Controller
{
    protected array $request_errors = [];
    protected array $error_messages = [
        'required' => 'Required field',
        'email' => 'Must be a valid email address',
        'url' => 'Must be a valid URL',
        'ip' => 'Must be a valid IP address',
        'int' => 'Must be an integer',
        'float' => 'Must be a floating-point number',
        'min' => 'Value is less than minimum: %s',
        'max' => 'Value is greater than maximum: %s',
        'min_length' => 'Length must be at least %s characters',
        'max_length' => 'Length must be no more than %s characters',
        'unique' => 'Value must be unique',
        'regex' => 'Value does not match the required pattern',
        'in' => 'Value must be one of the following: %s',
        'not_in' => 'Value must not be one of the following: %s',
        'alpha_num' => 'Value must contain only letters and numbers',
        'alpha' => 'Value must contain only alphabetic characters',
        'date' => 'Value must be a valid date',
        'json' => 'Value must be a valid JSON string',
        'boolean' => 'Value must be either true or false',
        'numeric' => 'Value must be numeric',
        'phone' => 'Value must be a valid phone number',
        'even' => 'Value must be an even number',
        'odd' => 'Value must be an odd number',
        'starts_with' => 'Value must start with: %s',
        'ends_with' => 'Value must end with: %s',
        'credit_card' => 'Value must be a valid credit card number',
        'digits' => 'Value must contain only digits',
        'uuid' => 'Value must be a valid UUID',
        'mac_address' => 'Value must be a valid MAC address',
        'postal_code' => 'Value must be a valid postal code',
        'palindrome' => 'Value must be a palindrome',
        'uppercase' => 'Value must be uppercase',
        'lowercase' => 'Value must be lowercase',
        'hex_color' => 'Value must be a valid hex color code',
        'divisible_by' => 'Value must be divisible by %s',
        'greater_than' => 'Value must be greater than %s',
        'less_than' => 'Value must be less than %s',
        'date_format' => 'Value must match the date format: %s',
        'timezone' => 'Value must be a valid timezone',
        'multiple_of' => 'Value must be a multiple of %s',
        'file_exists' => 'Value must be a file path that exists',
        'class_exists' => 'Value must be a class that exists',
    ];


    public function __construct()
    {
        request()->attributes->add(["controller" => $this]);
    }

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
    public function validateRequest(array $rules, ?int $id = null): object|false
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
            $is_required = in_array("required", $ruleset);
            if (empty($ruleset) || (!$is_required && !$value)) {
                $validated[$key] = $value;
                continue;
            }

            foreach ($ruleset as $rule) {
                if (is_string($rule) && $rule) {
                    $_rule = explode('|', $rule);
                    $rule = $_rule[0];
                    $rule_arg = $_rule[1] ?? null;
                    // Is request value valid?
                    $result = $this->validate($key, $rule, $value, $rule_arg);
                } elseif (is_callable($rule)) {
                    // The callback determines the result
                    $result = $rule($value, $id);
                }

                if ($result) {
                    $validated[$key] = $value;
                } else {
                    $valid = false;
                    if (is_string($rule) && isset($this->error_messages[$rule])) {
                        // Check for pre-defined error message
                        $this->addRequestError($key, sprintf($this->error_messages[$rule], $rule_arg));
                    } else if (isset($this->error_messages[$key])) {
                        // Look for other error_message entry by key
                        $this->addRequestError($key, sprintf($this->error_messages[$key], $rule_arg));
                    }
                }
            }
        }

        return $valid ? (object)$validated : false;
    }

    public function validate(string $key, string $rule, ?string $value = null, ?string $rule_arg = null): bool
    {
        return match (strtolower($rule)) {
            'required' => !is_null($value) && trim($value) !== '',
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL),
            'url' => filter_var($value, FILTER_VALIDATE_URL),
            'ip' => filter_var($value, FILTER_VALIDATE_IP),
            'int' => filter_var($value, FILTER_VALIDATE_INT),
            'float' => filter_var($value, FILTER_VALIDATE_FLOAT),
            'mac_address' => filter_var($value, FILTER_VALIDATE_MAC),
            'min' => is_numeric($value) && $value >= $rule_arg,
            'max' => is_numeric($value) && $value <= $rule_arg,
            'min_length' => is_string($value) && strlen($value) >= $rule_arg,
            'max_length' => is_string($value) && strlen($value) <= $rule_arg,
            'unique' => !db()->fetch("SELECT 1 FROM $rule_arg WHERE $key = ?", $value),
            'regex' => preg_match($rule_arg, $value),
            'in' => in_array($value, (array)$rule_arg),
            'not_in' => !in_array($value, (array)$rule_arg),
            'alpha_num' => ctype_alnum($value), // alpha numeric
            'alpha' => ctype_alpha($value), // alpha only
            'date' => strtotime($value) !== false,
            'json' => is_string($value) && is_array(json_decode($value, true)) && (json_last_error() == JSON_ERROR_NONE),
            'boolean' => is_bool($value) || in_array(strtolower($value), ['true', 'false', '1', '0', 1, 0], true),
            'numeric' => is_numeric($value),
            'phone' => preg_match('/^[\+0-9\s\-\(\)]+$/', $value), // basic phone number
            'even' => is_numeric($value) && $value % 2 === 0,
            'odd' => is_numeric($value) && $value % 2 !== 0,
            'starts_with' => str_starts_with($value, $rule_arg),
            'ends_with' => str_ends_with($value, $rule_arg),
            'credit_card' => preg_match('/^[0-9]{13,19}$/', $value),
            'digits' => ctype_digit($value), // only digits
            'uuid' => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value),
            'postal_code' => preg_match('/^[A-Z0-9]{3,10}$/i', $value), // canada
            'palindrome' => strrev($value) === $value,
            'uppercase' => ctype_upper($value),
            'lowercase' => ctype_lower($value),
            'hex_color' => preg_match('/^#?([a-f0-9]{6}|[a-f0-9]{3})$/i', $value),
            'divisible_by' => is_numeric($value) && $value % $rule_arg === 0,
            'greater_than' => is_numeric($value) && $value > $rule_arg,
            'less_than' => is_numeric($value) && $value < $rule_arg,
            'date_format' => date_create_from_format($rule_arg, $value) !== false,
            'timezone' => in_array($value, timezone_identifiers_list()),
            'multiple_of' => is_numeric($value) && fmod($value, $rule_arg) == 0,
            'file_exists' => file_exists($value),
            'class_exists' => class_exists($value),
            default => throw new InvalidArgumentException("Validation rule '$rule' is not supported."),
        };
    }

    public function addRequestError(string $field, string $message)
    {
        if (trim($message) != '') $this->request_errors[$field][] = $message;
    }

    public function addErrorMessage(string $field, string $message)
    {
        if (trim($message) != '') $this->error_messages[$field] = $message;
    }
}
