<?php

namespace Tests\Unit\Services;

use App\Models\Admin\Config;
use App\Services\Admin\AdminCache;
use App\Services\Admin\Dictionary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CmsTestSeeder;
use Tests\TestCase;

class DictionaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsTestSeeder::class);
    }

    #[Test]
    public function get_parses_array_content_lines(): void
    {
        Config::query()->create([
            'name' => 'test_dict',
            'group' => 'basic',
            'title' => '测试字典',
            'type' => 'array',
            'content' => "a|选项A\nb|选项B",
            'value' => '',
            'visible' => '1',
            'createtime' => time(),
            'updatetime' => time(),
        ]);

        app(AdminCache::class)->forgetConfig();

        $dict = app(Dictionary::class);
        $map = $dict->get('test_dict');

        $this->assertSame(['a' => '选项A', 'b' => '选项B'], $map);
        $this->assertSame('选项A', $dict->label('test_dict', 'a'));
    }
}
