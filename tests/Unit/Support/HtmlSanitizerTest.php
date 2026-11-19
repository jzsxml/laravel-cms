<?php

namespace Tests\Unit\Support;

use App\Support\HtmlSanitizer;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_strips_script_tags(): void
    {
        $dirty = '<p>ok</p><script>alert(1)</script>';
        $clean = HtmlSanitizer::clean($dirty);

        $this->assertStringContainsString('<p>ok</p>', $clean);
        $this->assertStringNotContainsString('<script', $clean);
    }

    public function test_removes_javascript_urls(): void
    {
        $dirty = '<a href="javascript:alert(1)">x</a>';
        $clean = HtmlSanitizer::clean($dirty);

        $this->assertStringNotContainsString('javascript:', $clean);
    }
}
