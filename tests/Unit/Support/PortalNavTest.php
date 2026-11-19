<?php

namespace Tests\Unit\Support;

use App\Models\Admin\Category;
use App\Support\PortalNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CmsTestSeeder;
use Tests\TestCase;

class PortalNavTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsTestSeeder::class);
    }

    #[Test]
    public function category_tree_nests_children_under_parent(): void
    {
        $now = time();
        $parent = Category::query()->create([
            'pid' => 0,
            'type' => 'article',
            'name' => '新闻中心',
            'nickname' => 'news-center',
            'status' => 'normal',
            'weigh' => 10,
            'createtime' => $now,
            'updatetime' => $now,
        ]);
        Category::query()->create([
            'pid' => $parent->id,
            'type' => 'article',
            'name' => '硬件',
            'nickname' => 'hardware',
            'status' => 'normal',
            'weigh' => 5,
            'createtime' => $now,
            'updatetime' => $now,
        ]);

        $tree = PortalNav::categoryTree();
        $news = collect($tree)->first(fn ($n) => $n['category']->nickname === 'news-center');
        $this->assertNotNull($news);
        $this->assertCount(1, $news['children']);
        $this->assertSame('hardware', $news['children'][0]['category']->nickname);
    }
}
