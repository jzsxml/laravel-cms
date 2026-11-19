<?php

namespace Tests\Unit\Support;

use App\Models\Admin\CmsBlock as BlockModel;
use App\Support\CmsBlock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CmsTestSeeder;
use Tests\TestCase;

class CmsBlockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsTestSeeder::class);
    }

    #[Test]
    public function get_returns_block_from_database(): void
    {
        BlockModel::query()->create([
            'name' => 'test_block',
            'title' => '测试',
            'type' => 'html',
            'content' => '<p>区块内容</p>',
            'status' => 'normal',
            'weigh' => 0,
            'createtime' => time(),
            'updatetime' => time(),
        ]);

        $block = CmsBlock::get('test_block');
        $this->assertInstanceOf(BlockModel::class, $block);
        $this->assertSame('test_block', $block->name);
    }

    #[Test]
    public function html_returns_content_and_forget_clears_cache(): void
    {
        BlockModel::query()->create([
            'name' => 'html_block',
            'title' => 'HTML',
            'type' => 'html',
            'content' => 'cached-html',
            'status' => 'normal',
            'weigh' => 0,
            'createtime' => time(),
            'updatetime' => time(),
        ]);

        $this->assertSame('cached-html', CmsBlock::html('html_block'));
        CmsBlock::forget('html_block');

        BlockModel::query()->where('name', 'html_block')->update(['content' => 'updated']);
        $this->assertSame('updated', CmsBlock::html('html_block'));
    }
}
