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
        // Add list_user to gift table if table exists and column doesn't
        if (Schema::hasTable('gift')) {
            Schema::table('gift', function (Blueprint $table) {
                if (!Schema::hasColumn('gift', 'list_user')) {
                    $table->text('list_user')->nullable()->after('power');
                }
            });
        }

        // Create web_acc_sales table
        if (!Schema::hasTable('web_acc_sales')) {
            Schema::create('web_acc_sales', function (Blueprint $table) {
                $table->id();
                $table->integer('seller_id');
                $table->integer('buyer_id')->nullable();
                $table->string('username_sold');
                $table->string('password_sold');
                $table->integer('price');
                $table->integer('sale_price')->nullable();
                $table->text('description')->nullable();
                $table->text('images')->nullable(); // Store JSON array of image paths
                $table->string('status', 50)->default('selling'); // 'selling', 'sold', 'cancelled'
                $table->string('payout_status', 50)->default('pending'); // 'pending', 'approved', 'refunded'
                $table->string('cancel_reason', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('gift')) {
            Schema::table('gift', function (Blueprint $table) {
                if (Schema::hasColumn('gift', 'list_user')) {
                    $table->dropColumn('list_user');
                }
            });
        }

        Schema::dropIfExists('web_acc_sales');
    }
};
