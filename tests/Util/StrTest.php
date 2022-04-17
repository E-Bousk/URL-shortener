<?php

namespace App\Tests\Util;

use App\Util\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    // (avec « testQuelquechose »)
    public function testRandomStringLength(): void
    {
        // $this->assertTrue(mb_strlen(Str::random(6)) === 6);
        $this->assertSame(6, mb_strlen(Str::random(6)));
    }

    // (avec annotation « test »)
    /** @test */
    public function randomStringLength(): void
    {
        // $this->assertTrue(mb_strlen(Str::random()) === 16);
        $this->assertSame(16, mb_strlen(Str::random()));
    }
}
