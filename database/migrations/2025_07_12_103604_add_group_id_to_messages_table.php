<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // in the migration
public function up()
{
    Schema::table('messages', function (Blueprint $table) {
        $table->unsignedBigInteger('group_id')->nullable()->after('receiver_id');

        $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            //
        });
    }
};
