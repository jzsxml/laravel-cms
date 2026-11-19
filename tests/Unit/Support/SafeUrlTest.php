<?php

namespace Tests\Unit\Support;

use App\Support\SafeUrl;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SafeUrlTest extends TestCase
{
    #[Test]
    public function rejects_javascript_urls(): void
    {
        $this->assertSame('', SafeUrl::forDisplay('javascript:alert(1)'));
    }

    #[Test]
    public function allows_https_and_relative(): void
    {
        $this->assertSame('https://cdn.example.com/a.png', SafeUrl::forDisplay('https://cdn.example.com/a.png'));
        $this->assertSame('/storage/a.png', SafeUrl::forDisplay('/storage/a.png'));
    }
}
