<?php

namespace App\Util;

class Str
{
    public static function random(int $length = 6): string
    {
        return substr(bin2hex(random_bytes(32)), 0, $length);
    }
}
