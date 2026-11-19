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
            $table->enum('pay_type', ['free', 'pay'])->default('free')->after('views');
            $table->decimal('price', 10, 2)->default(0)->after('pay_type');
            $table->unsignedTinyInteger('pay_ratio')->default(0)->after('price')->comment('试读比例0-100');
            $table->string('download_url', 255)->default('')->after('pay_ratio')->comment('付费附件URL');
            $table->unsignedInteger('likes')->default(0)->after('download_url');
        });

        Schema::table(CmsTable::name('page'), function (Blueprint $table) {
            $table->enum('pay_type', ['free', 'pay'])->default('free')->after('views');
            $table->decimal('price', 10, 2)->default(0)->after('pay_type');
            $table->unsignedTinyInteger('pay_ratio')->default(0)->after('price');
            $table->string('download_url', 255)->default('')->after('pay_ratio');
        });

        Schema::table(CmsTable::name('user'), function (Blueprint $table) {
            $table->decimal('money', 10, 2)->default(0)->after('avatar');
            $table->unsignedBigInteger('vip_expiretime')->nullable()->after('money');
        });

        Schema::create(CmsTable::name('order'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_sn', 32)->unique();
            $table->unsignedInteger('member_id')->default(0);
            $table->enum('product_type', ['archive', 'page'])->default('archive');
            $table->unsignedInteger('product_id')->default(0);
            $table->string('title', 200)->default('');
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('pay_method', ['balance', 'mock'])->default('balance');
            $table->enum('status', ['created', 'paid', 'closed'])->default('created');
            $table->unsignedBigInteger('paytime')->nullable();
            $table->unsignedBigInteger('createtime')->nullable();
            $table->unsignedBigInteger('updatetime')->nullable();
            $table->index(['member_id', 'status']);
            $table->index(['product_type', 'product_id', 'member_id', 'status']);
        });

        Schema::create(CmsTable::name('user_money_log'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('member_id')->default(0);
            $table->decimal('money', 10, 2)->default(0)->comment('变动金额，负为支出');
            $table->decimal('before', 10, 2)->default(0);
            $table->decimal('after', 10, 2)->default(0);
            $table->string('memo', 255)->default('');
            $table->unsignedBigInteger('createtime')->nullable();
            $table->index(['member_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CmsTable::name('user_money_log'));
        Schema::dropIfExists(CmsTable::name('order'));
        Schema::table(CmsTable::name('user'), function (Blueprint $table) {
            $table->dropColumn(['money', 'vip_expiretime']);
        });
        Schema::table(CmsTable::name('page'), function (Blueprint $table) {
            $table->dropColumn(['pay_type', 'price', 'pay_ratio', 'download_url']);
        });
        Schema::table(CmsTable::name('archives'), function (Blueprint $table) {
            $table->dropColumn(['pay_type', 'price', 'pay_ratio', 'download_url', 'likes']);
        });
    }
};