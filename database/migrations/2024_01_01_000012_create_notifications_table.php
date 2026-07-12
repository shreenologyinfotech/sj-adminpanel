<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AdminUser uses the Notifiable trait, which relies on Laravel's
 * standard polymorphic "notifications" table. Fresh Laravel apps don't
 * always ship this migration, so the package brings its own — guarded
 * behind a hasTable() check in case the host app already created it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notifications')) {
            return;
        }

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Intentionally left as-is: since we may not have created this
        // table (it might belong to the host app), we never drop it here.
    }
};
