<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDropIdOnEventTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_tag', function (Blueprint $table) {
            // まず 'id' カラムを削除
            $table->dropColumn('id');
            // 複合主キーを追加
            $table->primary(['event_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_tag', function (Blueprint $table) {
            // primary key 削除（安全のためまず削除）
            $table->dropPrimary(['event_id', 'tag_id']);
            // idカラム復活（auto increment）
            $table->bigIncrements('id');
        });
    }
}
