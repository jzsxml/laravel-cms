<?php

use App\Support\CmsTable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(CmsTable::name('archives'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id')->default(0)->comment('栏目');
            $table->unsignedInteger('admin_id')->default(0)->comment('管理员');
            $table->string('slug', 120)->default('')->comment('URL别名');
            $table->string('title', 200)->default('')->comment('标题');
            $table->string('subtitle', 255)->default('')->comment('副标题');
            $table->string('image', 255)->default('')->comment('封面');
            $table->string('description', 500)->default('')->comment('摘要');
            $table->mediumText('content')->nullable()->comment('正文');
            $table->string('keywords', 200)->default('')->comment('关键字');
            $table->string('flag', 100)->default('')->comment('标志');
            $table->unsignedInteger('weigh')->default(0)->comment('权重');
            $table->unsignedInteger('views')->default(0)->comment('浏览量');
            $table->enum('status', ['draft', 'normal', 'hidden'])->default('draft')->comment('状态');
            $table->unsignedBigInteger('publishtime')->nullable()->comment('发布时间');
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->unsignedBigInteger('deletetime')->nullable();
            $table->unique('slug');
            $table->index(['category_id', 'status', 'weigh']);
            $table->index(['status', 'publishtime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CmsTable::name('archives'));
    }
};