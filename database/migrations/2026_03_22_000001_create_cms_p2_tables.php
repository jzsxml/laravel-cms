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
            $table->unsignedInteger('member_id')->default(0)->after('admin_id')->comment('投稿会员');
        });

        Schema::create(CmsTable::name('comment'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('archive_id')->default(0);
            $table->unsignedInteger('member_id')->default(0);
            $table->string('member_nickname', 50)->default('');
            $table->text('content');
            $table->string('ip', 50)->default('');
            $table->enum('status', ['normal', 'hidden'])->default('normal');
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('deletetime')->nullable();
            $table->index(['archive_id', 'status']);
        });

        Schema::create(CmsTable::name('special'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 150)->default('');
            $table->string('slug', 120)->default('');
            $table->string('image', 255)->default('');
            $table->string('description', 500)->default('');
            $table->mediumText('content')->nullable();
            $table->unsignedInteger('weigh')->default(0);
            $table->enum('status', ['normal', 'hidden'])->default('normal');
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->unsignedBigInteger('deletetime')->nullable();
            $table->unique('slug');
        });

        Schema::create(CmsTable::name('archive_special'), function (Blueprint $table) {
            $table->unsignedInteger('archive_id');
            $table->unsignedInteger('special_id');
            $table->primary(['archive_id', 'special_id']);
        });

        Schema::create(CmsTable::name('block'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 80)->default('')->comment('调用标识');
            $table->string('title', 150)->default('');
            $table->enum('type', ['html', 'image', 'text'])->default('html');
            $table->mediumText('content')->nullable();
            $table->enum('status', ['normal', 'hidden'])->default('normal');
            $table->unsignedInteger('weigh')->default(0);
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CmsTable::name('block'));
        Schema::dropIfExists(CmsTable::name('archive_special'));
        Schema::dropIfExists(CmsTable::name('special'));
        Schema::dropIfExists(CmsTable::name('comment'));
        Schema::table(CmsTable::name('archives'), function (Blueprint $table) {
            $table->dropColumn('member_id');
        });
    }
};