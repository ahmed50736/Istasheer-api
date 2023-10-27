<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;

class IgonreCurrentUser implements Rule
{
    protected $userId;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $uuid)
    {
        $this->userId = $uuid;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !User::where('username', $value)
            ->where('id', '!=', $this->userId)
            ->withTrashed()
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('messages.custom_validation.username_taken');
    }
}
