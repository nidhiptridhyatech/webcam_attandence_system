<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class fileCheck implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $allowedExts = array("mp4");
        $extension = $value->getClientOriginalExtension();

        $videoFormatCheck = (in_array($extension, $allowedExts)) ? true : false;

        return $videoFormatCheck;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The File must in mp4 file.';
    }
}
