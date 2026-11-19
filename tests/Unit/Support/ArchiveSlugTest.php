<?php

namespace Tests\Unit\Support;

use App\Support\ArchiveSlug;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArchiveSlugTest extends TestCase
{
    #[Test]
    public function slugify_english_title(): void
    {
        $this->assertSame('hello-world', ArchiveSlug::slugify('Hello World'));
    }

    #[Test]
    public function slugify_chinese_title(): void
    {
        $slug = ArchiveSlug::slugify('企业站上线');
        $this->assertNotSame('', $slug);
        $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/', $slug);
    }

    #[Test]
    public function validate_rejects_reserved_slugs(): void
    {
        $this->assertFalse(ArchiveSlug::validate('admin'));
        $this->assertTrue(ArchiveSlug::validate('my-valid-slug'));
    }

    #[Test]
    public function validate_rejects_too_short_slug(): void
    {
        $this->assertFalse(ArchiveSlug::validate('ab'));
    }
}
