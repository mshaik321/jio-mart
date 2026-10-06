<?php

namespace App\Support;

/**
 * Indian mobile numbers, stored as the bare 10 digits so "+91 98765 43210",
 * "098765-43210" and "9876543210" can't register as three different accounts
 * against the unique users.phone column.
 */
class PhoneNumber
{
    public const MOBILE_PATTERN = '/^[6-9][0-9]{9}$/';

    public static function normalize(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, 1);
        }

        return $digits;
    }
}
