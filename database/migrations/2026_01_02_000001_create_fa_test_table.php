<?php

use App\Support\CmsTable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(CmsTable::name('test'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('admin_id')->default(0)->comment('管理员ID');
            $table->unsignedInteger('category_id')->default(0)->comment('分类ID');
            $table->string('title', 100)->default('')->comment('标题');
            $table->string('image', 255)->default('')->comment('图片');
            $table->string('images', 1500)->default('')->comment('图片组');
            $table->unsignedInteger('weigh')->default(0)->comment('权重');
            $table->unsignedTinyInteger('switch')->default(0)->comment('开关');
            $table->enum('status', ['normal', 'hidden'])->default('normal')->comment('状态');
            $table->text('content')->nullable()->comment('内容');
            $table->unsignedBigInteger('createtime')->nullable()->comment('创建时间');
            $table->unsignedBigInteger('updatetime')->nullable()->comment('更新时间');
            $table->unsignedBigInteger('deletetime')->nullable()->comment('删除时间');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CmsTable::name('test'));
    }
};