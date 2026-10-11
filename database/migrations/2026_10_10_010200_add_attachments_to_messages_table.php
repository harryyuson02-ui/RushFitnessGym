<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->text('body')->nullable()->change();
            $table->string('attachment_path')->nullable()->after('body');
            $table->string('attachment_type', 20)->nullable()->after('attachment_path');
            $table->string('attachment_mime', 100)->nullable()->after('attachment_type');
            $table->string('attachment_name')->nullable()->after('attachment_mime');
            $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_name');
            $table->index(['attachment_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['attachment_type', 'created_at']);
            $table->dropColumn([
                'attachment_path',
                'attachment_type',
                'attachment_mime',
                'attachment_name',
                'attachment_size',
            ]);
            $table->text('body')->nullable(false)->change();
        });
    }
};
