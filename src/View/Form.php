<?php

namespace Helios\View;

/**
 * @class Form
 * The form view
 */
class Form extends View
{
    protected string $template = "admin/module/form.html";

    public function control(string $column, mixed $value): string
    {
        $form = $this->module->getForm();
        $control = $this->module->getControl();
        $options = [
            "title" => array_search($column, $form),
            "class" => "control"
        ];
        // Check if there is a validation error
        if (controller()->hasError($column)) {
            $options["class"] .= " is-invalid";
        }
        // Render control
        if (isset($control[$column])) {
            // A control column is set
            $callback = $control[$column];
            if (is_callable($callback)) {
                // The callback method is the value
                return $callback($column, $value, $options);
            } else if (is_string($callback) && method_exists($this->module::class, $callback)) {
                // The module callback method is the value
                return $this->module->$callback($column, $value, $options);
            } else if (
                is_string($callback) &&
                method_exists(Control::class, $callback)
            ) {
                // The control callback is the value
                return Control::$callback($column, $value, $options);
            } else if (is_array($callback)) {
                // If the callback is an array, then we assume it is a select control
                $options['options'] = $callback;
                return Control::select($column, $value, $options);
            }
        }
        return Control::input($column, $value, $options);
    }

    public function getTemplateData(): array
    {
        return [
            ...parent::getTemplateData(),
            "id" => $this->module->getId(),
            "form" => $this->module->getForm(),
        ];
    }
}
