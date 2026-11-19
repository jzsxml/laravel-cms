<?php

use App\Support\CmsTable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(CmsTable::name('user'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 50)->default('')->unique();
            $table->string('nickname', 50)->default('');
            $table->string('password', 64)->default('');
            $table->string('salt', 30)->default('');
            $table->string('email', 100)->default('');
            $table->string('mobile', 20)->default('');
            $table->string('avatar', 255)->default('');
            $table->unsignedBigInteger('logintime')->nullable();
            $table->string('loginip', 50)->nullable();
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->string('token', 59)->default('');
            $table->enum('status', ['normal', 'hidden'])->default('normal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CmsTable::name('user'));
    }
};