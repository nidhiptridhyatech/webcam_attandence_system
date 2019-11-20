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
        $maxSize = 1500000;
        $extension = $value[0]->getClientOriginalExtension();
        $size = $value[0]->getSize();

        $videoFormatCheck = (in_array($extension, $allowedExts)) ? true : false;
        $videoSizeCheck = ($size <= $maxSize) ? true : false;

        if($videoFormatCheck == true && $videoSizeCheck == true){
            $fileCheck = true;
        }else{
            $fileCheck = false;
        }
        
        return $fileCheck;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The File must in mp4 file & less then 1Mb.';
    }
}
