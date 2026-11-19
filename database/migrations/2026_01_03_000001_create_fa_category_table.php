<?php

use App\Support\CmsTable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(CmsTable::name('category'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pid')->default(0)->comment('父ID');
            $table->string('type', 50)->default('')->comment('分类类型');
            $table->string('name', 50)->default('')->comment('名称');
            $table->string('nickname', 50)->default('')->comment('别名');
            $table->string('flag', 50)->default('')->comment('标志');
            $table->string('image', 255)->default('')->comment('图片');
            $table->string('keywords', 100)->default('')->comment('关键字');
            $table->string('description', 255)->default('')->comment('描述');
            $table->unsignedInteger('weigh')->default(0)->comment('权重');
            $table->enum('status', ['normal', 'hidden'])->default('normal')->comment('状态');
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CmsTable::name('category'));
    }
};