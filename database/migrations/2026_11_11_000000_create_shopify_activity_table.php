<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function getConnection()
    {
        return config('shopify.activity.connection') ?: parent::getConnection();
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->create(config('shopify.activity.table', 'shopify_activity'), function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->string('source', 20);
            $table->string('status', 20)->index();
            $table->string('topic', 100)->nullable()->index();
            $table->string('store', 100)->nullable()->index();
            $table->string('resource_type', 50)->nullable();
            $table->string('resource_id', 100)->nullable();
            $table->string('resource_title')->nullable();
            $table->string('entry_id')->nullable();
            $table->text('message')->nullable();
            $table->json('changes')->nullable();
            $table->string('delivery_id')->nullable()->index();
            $table->string('parent_id', 40)->nullable()->index();
            $table->timestamp('created_at')->index();
            $table->timestamp('updated_at');
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists(config('shopify.activity.table', 'shopify_activity'));
    }
};
