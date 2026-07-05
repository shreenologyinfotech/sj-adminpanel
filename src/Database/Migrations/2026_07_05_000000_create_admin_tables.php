<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sj_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('sj_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('table_name')->nullable();
            $table->string('group_name');
            $table->timestamps();
        });

        Schema::create('sj_user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('sj_roles')->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });

        Schema::create('sj_role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('sj_roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('sj_permissions')->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('sj_bread_configs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name')->unique();
            $table->string('slug')->unique();
            $table->string('model_name');
            $table->string('display_name_singular');
            $table->string('display_name_plural');
            $table->string('icon')->nullable();
            $table->string('controller_name')->nullable();
            $table->string('policy_name')->nullable();
            $table->timestamps();
        });

        Schema::create('sj_bread_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bread_id')->constrained('sj_bread_configs')->onDelete('cascade');
            $table->string('field');
            $table->string('type');
            $table->string('display_name');
            $table->boolean('required')->default(false);
            $table->boolean('nullable')->default(true);
            $table->string('validation_rules')->nullable();
            $table->string('default_value')->nullable();
            $table->boolean('browse')->default(true);
            $table->boolean('read')->default(true);
            $table->boolean('edit')->default(true);
            $table->boolean('add')->default(true);
            $table->integer('order')->default(0);
            $table->json('options')->nullable();
        });

        Schema::create('sj_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('sj_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('sj_menus')->onDelete('cascade');
            $table->string('title');
            $table->string('url');
            $table->string('icon')->nullable();
            $table->string('target')->default('_self');
            $table->integer('order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('sj_menu_items')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('sj_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('display_name');
            $table->text('value')->nullable();
            $table->string('type');
            $table->string('group');
            $table->text('options')->nullable();
            $table->timestamps();
        });

        Schema::create('sj_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_email');
            $table->string('action');
            $table->text('description');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sj_activity_logs');
        Schema::dropIfExists('sj_settings');
        Schema::dropIfExists('sj_menu_items');
        Schema::dropIfExists('sj_menus');
        Schema::dropIfExists('sj_bread_fields');
        Schema::dropIfExists('sj_bread_configs');
        Schema::dropIfExists('sj_role_permissions');
        Schema::dropIfExists('sj_user_roles');
        Schema::dropIfExists('sj_permissions');
        Schema::dropIfExists('sj_roles');
    }
};