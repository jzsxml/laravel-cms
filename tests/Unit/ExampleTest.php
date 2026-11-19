<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase as AppTestCase;

class ExampleTest extends AppTestCase
{
    #[Test]
    public function application_binds_config(): void
    {
        $this->assertNotEmpty(config('cms.table_prefix'));
    }
}
