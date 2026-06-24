<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class VietnamPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $phone = preg_replace('/[\s.\-]/', '', (string) $value);

        if (! is_string($phone) || ! preg_match('/^(0|\+84)(3|5|7|8|9)[0-9]{8}$/', $phone)) {
            $fail('Số điện thoại không hợp lệ.');
        }
    }
}
