<?php

use App\Support\CmsTable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(CmsTable::name('admin'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 50)->default('')->unique();
            $table->string('nickname', 50)->default('');
            $table->string('password', 64)->default('');
            $table->string('salt', 30)->default('');
            $table->string('avatar', 255)->default('');
            $table->string('email', 100)->default('');
            $table->string('mobile', 20)->default('');
            $table->unsignedTinyInteger('loginfailure')->default(0);
            $table->unsignedBigInteger('logintime')->nullable();
            $table->string('loginip', 50)->nullable();
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->string('token', 59)->default('');
            $table->enum('status', ['normal', 'hidden'])->default('normal');
        });

        Schema::create(CmsTable::name('auth_group'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pid')->default(0);
            $table->string('name', 100)->default('');
            $table->text('rules');
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->enum('status', ['normal', 'hidden'])->default('normal');
        });

        Schema::create(CmsTable::name('auth_group_access'), function (Blueprint $table) {
            $table->unsignedInteger('uid');
            $table->unsignedInteger('group_id');
            $table->primary(['uid', 'group_id']);
        });

        Schema::create(CmsTable::name('auth_rule'), function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['menu', 'file'])->default('file');
            $table->unsignedInteger('pid')->default(0);
            $table->string('name', 100)->default('')->unique();
            $table->string('title', 50)->default('');
            $table->string('icon', 50)->default('');
            $table->string('url', 255)->default('');
            $table->string('condition', 255)->default('');
            $table->string('remark', 255)->default('');
            $table->unsignedTinyInteger('ismenu')->default(0);
            $table->enum('menutype', ['addtabs', 'blank', 'dialog', 'ajax'])->nullable();
            $table->string('extend', 255)->default('');
            $table->string('py', 30)->default('');
            $table->string('pinyin', 100)->default('');
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->integer('weigh')->default(0);
            $table->enum('status', ['normal', 'hidden'])->default('normal');
        });

        Schema::create(CmsTable::name('admin_log'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('admin_id')->default(0);
            $table->string('username', 50)->default('');
            $table->string('url', 1500)->default('');
            $table->string('title', 100)->default('');
            $table->text('content')->nullable();
            $table->string('ip', 50)->default('');
            $table->string('useragent', 255)->default('');
            $table->unsignedBigInteger('createtime')->nullable();
            $table->index('admin_id');
        });

        Schema::create(CmsTable::name('config'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->default('')->unique();
            $table->string('group', 30)->default('');
            $table->string('title', 50)->default('');
            $table->string('tip', 100)->default('');
            $table->string('type', 30)->default('');
            $table->string('visible', 255)->default('');
            $table->text('value')->nullable();
            $table->text('content')->nullable();
            $table->string('rule', 100)->default('');
            $table->string('extend', 255)->default('');
            $table->string('setting', 255)->default('');
        });

        Schema::create(CmsTable::name('attachment'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('category', 50)->default('');
            $table->unsignedInteger('admin_id')->default(0);
            $table->unsignedInteger('user_id')->default(0);
            $table->string('url', 255)->default('');
            $table->unsignedInteger('imagewidth')->default(0);
            $table->unsignedInteger('imageheight')->default(0);
            $table->string('imagetype', 30)->default('');
            $table->unsignedInteger('imageframes')->default(0);
            $table->string('filename', 100)->default('');
            $table->unsignedInteger('filesize')->default(0);
            $table->string('mimetype', 100)->default('');
            $table->string('extparam', 255)->default('');
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->unsignedBigInteger('uploadtime')->nullable();
            $table->string('storage', 20)->default('local');
            $table->string('sha1', 40)->default('');
        });
    }

    public function down(): void
    {
        foreach (['attachment', 'config', 'admin_log', 'auth_rule', 'auth_group_access', 'auth_group', 'admin'] as $table) {
            Schema::dropIfExists(CmsTable::name($table));
        }
    }
};