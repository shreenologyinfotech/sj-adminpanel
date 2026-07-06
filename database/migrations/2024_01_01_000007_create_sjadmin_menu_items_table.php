<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sjadmin_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('sjadmin_menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('sjadmin_menu_items')->nullOnDelete();
            $table->string('title');
            $table->string('icon')->nullable();
            $table->string('url')->nullable();
            $table->string('route')->nullable();
            $table->string('target')->default('_self');
            $table->string('permission')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sjadmin_menu_items');
    }
};
