<?php

namespace App\Support;

class Money
{
    /**
     * Format a decimal/numeric value as a clean "1221.22" string for JSON APIs.
     *
     * Casting to (float) and letting json_encode serialize it is NOT safe here:
     * many hosts (this app's production host included) run PHP with
     * serialize_precision=100 instead of the modern default of -1, which makes
     * json_encode dump the full IEEE754 expansion of any float that isn't exactly
     * representable in binary (e.g. 1221.22 becomes 1221.220000000000027...).
     * A plain string sidesteps float serialization entirely, on any host.
     */
    public static function format(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
