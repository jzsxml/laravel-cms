<?php

use App\Support\CmsTable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(CmsTable::name('archives'), function (Blueprint $table) {
            $table->string('category_ids', 255)->default('')->after('category_id')->comment('副栏目ID逗号分隔');
        });

        Schema::create(CmsTable::name('tag'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->default('');
            $table->string('slug', 80)->default('');
            $table->unsignedInteger('weigh')->default(0);
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->unique('slug');
        });

        Schema::create(CmsTable::name('archive_tag'), function (Blueprint $table) {
            $table->unsignedInteger('archive_id');
            $table->unsignedInteger('tag_id');
            $table->primary(['archive_id', 'tag_id']);
        });

        Schema::create(CmsTable::name('page'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 120)->default('');
            $table->string('title', 200)->default('');
            $table->string('template', 50)->default('default');
            $table->string('image', 255)->default('');
            $table->string('description', 500)->default('');
            $table->mediumText('content')->nullable();
            $table->string('keywords', 200)->default('');
            $table->string('flag', 100)->default('');
            $table->unsignedInteger('weigh')->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->enum('status', ['draft', 'normal', 'hidden'])->default('draft');
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->unsignedBigInteger('deletetime')->nullable();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CmsTable::name('page'));
        Schema::dropIfExists(CmsTable::name('archive_tag'));
        Schema::dropIfExists(CmsTable::name('tag'));
        Schema::table(CmsTable::name('archives'), function (Blueprint $table) {
            $table->dropColumn('category_ids');
        });
    }
};