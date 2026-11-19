<?php

namespace Tests\Unit\Support;

use App\Support\UploadGuard;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UploadGuardTest extends TestCase
{
    #[Test]
    public function blocks_php_extension(): void
    {
        $file = UploadedFile::fake()->create('evil.php', 10, 'application/x-php');
        $this->expectException(ValidationException::class);
        UploadGuard::assertUploadedFile($file);
    }

    #[Test]
    public function allows_png_extension(): void
    {
        $file = UploadedFile::fake()->image('photo.png');
        UploadGuard::assertUploadedFile($file);
        $this->assertTrue(true);
    }
}
