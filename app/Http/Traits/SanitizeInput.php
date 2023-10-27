<?php


namespace App\Http\Traits;

trait SanitizeInput
{
    protected function sanitizeInput($input)
    {
        // Sanitize input here based on your specific requirements.
        // For example, you can use PHP's trim and strip_tags functions:
        return is_string($input) ? trim(strip_tags($input)) : $input;
    }

    protected function sanitizeArray(array $data)
    {
        // Recursively sanitize all values in the array
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeArray($value);
            } else {
                $data[$key] = $this->sanitizeInput($value);
            }
        }

        return $data;
    }
}
