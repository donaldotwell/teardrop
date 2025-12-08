<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoLinksRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $patterns = [
            '/https?:\/\//',           // http:// or https://
            '/www\./i',                // www.
            '/\.[a-z]{2,4}\b/i',      // domain extensions like .com, .org, etc.
            '/[a-z0-9.-]+\.(com|org|net|io|co|gov|edu|mil)/i', // common domains
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $fail('The :attribute field cannot contain links or URLs.');
                return;
            }
        }

    }
}
