<?php

namespace App\Support;

class PhilippineMobileNumber
{
    /**
     * Normalize a Philippine mobile number to 09XXXXXXXXX format.
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', trim($value)) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            return '0'.$digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '09')) {
            return $digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '639')) {
            return '0'.substr($digits, 2);
        }

        return null;
    }

    public static function isValid(?string $value): bool
    {
        return self::normalize($value) !== null;
    }

    /**
     * Convert to 639XXXXXXXXX format for SMS gateways.
     */
    public static function toInternationalDigits(?string $value): ?string
    {
        $normalized = self::normalize($value);

        if ($normalized === null) {
            return null;
        }

        return '63'.substr($normalized, 1);
    }
}
