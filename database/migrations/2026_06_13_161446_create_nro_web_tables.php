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
        // 1. Web Shop Items
        if (!Schema::hasTable('web_shop_items')) {
            Schema::create('web_shop_items', function (Blueprint $table) {
                $table->id();
                $table->integer('item_template_id');
                $table->integer('price_vnd');
                $table->integer('quantity')->default(-1); // -1 = unlimited
                $table->tinyInteger('is_active')->default(1);
                $table->string('image_override')->nullable();
                $table->timestamps();
            });
        }

        // 2. Web Shop Purchases
        if (!Schema::hasTable('web_shop_purchases')) {
            Schema::create('web_shop_purchases', function (Blueprint $table) {
                $table->id();
                $table->integer('account_id');
                $table->integer('item_template_id');
                $table->integer('price_vnd');
                $table->timestamps();
            });
        }

        // 3. Web Recharge Packages
        if (!Schema::hasTable('web_recharge_packages')) {
            Schema::create('web_recharge_packages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('amount_vnd');
                $table->integer('bonus_vnd')->default(0);
                $table->tinyInteger('is_popular')->default(0);
                $table->tinyInteger('is_active')->default(1);
                $table->timestamps();
            });
        }

        // 4. Download Files
        if (!Schema::hasTable('download_files')) {
            Schema::create('download_files', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('version')->nullable();
                $table->string('url');
                $table->string('size')->nullable();
                $table->tinyInteger('is_active')->default(1);
                $table->timestamps();
            });
        }

        // 5. Spin Wheel Items
        if (!Schema::hasTable('spin_wheel_items')) {
            Schema::create('spin_wheel_items', function (Blueprint $table) {
                $table->id();
                $table->integer('item_template_id')->nullable();
                $table->string('display_name');
                $table->string('image')->nullable();
                $table->string('reward_type'); // 'item', 'vnd', 'coin'
                $table->integer('reward_value')->default(1);
                $table->decimal('ratio', 5, 2); // e.g. 15.50 %
                $table->string('color')->default('#ff6b00');
                $table->tinyInteger('is_active')->default(1);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 6. Spin Wheel History
        if (!Schema::hasTable('spin_wheel_history')) {
            Schema::create('spin_wheel_history', function (Blueprint $table) {
                $table->id();
                $table->integer('account_id');
                $table->integer('spin_item_id');
                $table->integer('cost_vnd');
                $table->timestamp('spun_at')->useCurrent();
            });
        }

        // ALTER existing table gift_code
        if (Schema::hasTable('gift_code')) {
            Schema::table('gift_code', function (Blueprint $table) {
                if (!Schema::hasColumn('gift_code', 'image')) {
                    $table->string('image')->nullable()->after('content');
                }
                if (!Schema::hasColumn('gift_code', 'display_name')) {
                    $table->string('display_name')->nullable()->after('image');
                }
            });
        }

        // ALTER existing table post
        if (Schema::hasTable('post')) {
            Schema::table('post', function (Blueprint $table) {
                if (!Schema::hasColumn('post', 'category')) {
                    $table->string('category', 100)->default('guide')->after('type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_shop_items');
        Schema::dropIfExists('web_shop_purchases');
        Schema::dropIfExists('web_recharge_packages');
        Schema::dropIfExists('download_files');
        Schema::dropIfExists('spin_wheel_items');
        Schema::dropIfExists('spin_wheel_history');

        if (Schema::hasTable('gift_code')) {
            Schema::table('gift_code', function (Blueprint $table) {
                if (Schema::hasColumn('gift_code', 'image')) {
                    $table->dropColumn('image');
                }
                if (Schema::hasColumn('gift_code', 'display_name')) {
                    $table->dropColumn('display_name');
                }
            });
        }

        if (Schema::hasTable('post')) {
            Schema::table('post', function (Blueprint $table) {
                if (Schema::hasColumn('post', 'category')) {
                    $table->dropColumn('category');
                }
            });
        }
    }
};
