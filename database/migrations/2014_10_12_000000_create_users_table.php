<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('username')->uniqnue();
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            $table->string('realname')->nullable();

	        //微信资料
	        $table->string('weapp_openid')->nullable()->comment('微信开放id');
	        $table->string('weapp_session_key')->nullable()->comment('微信session_key');
	        $table->string('nickname')->nullable()->comment('昵称');
	        $table->string('country')->nullable()->comment('国家');
	        $table->string('province')->nullable()->comment('省份');
	        $table->string('city')->nullable()->comment('所在城市');
	        $table->string('language')->nullable()->comment('语言');
	        $table->json('location')->nullable()->comment('当前地理信息');

            // 拓展资料
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->string('bio')->nullable();
            $table->json('extends')->nullable();
            $table->json('settings')->nullable();

            // 状态
            $table->integer('level')->default(0);
            $table->boolean('is_admin')->default(false);

            // 数据缓存
            $table->json('cache')->nullable();

            // 账户
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
