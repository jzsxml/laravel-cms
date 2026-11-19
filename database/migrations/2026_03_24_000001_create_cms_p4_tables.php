<?php

use App\Support\CmsTable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(CmsTable::name('cms_model'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 60)->default('')->comment('标识');
            $table->string('title', 100)->default('');
            $table->mediumText('fields')->nullable()->comment('字段定义JSON');
            $table->enum('status', ['normal', 'hidden'])->default('normal');
            $table->unsignedInteger('weigh')->default(0);
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->unique('name');
        });

        Schema::create(CmsTable::name('cms_model_data'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('model_id')->default(0);
            $table->mediumText('data')->nullable()->comment('JSON数据');
            $table->enum('status', ['normal', 'hidden'])->default('normal');
            $table->unsignedInteger('weigh')->default(0);
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->unsignedBigInteger('deletetime')->nullable();
            $table->index(['model_id', 'status', 'weigh']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CmsTable::name('cms_model_data'));
        Schema::dropIfExists(CmsTable::name('cms_model'));
    }
};