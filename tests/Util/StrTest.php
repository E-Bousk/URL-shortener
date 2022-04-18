<?php

namespace App\Tests\Util;

use App\Util\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    public function testRandomStringLength(): void
    {
        $this->assertSame(6, mb_strlen(Str::random(6)));
        $this->assertSame(16, mb_strlen(Str::random()));
    }
}
