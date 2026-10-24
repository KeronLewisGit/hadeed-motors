<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('stock_no', 30)->nullable()->index();
            $table->string('condition', 10)->default('used')->index();
            $table->string('status', 12)->default('available')->index();
            $table->string('make', 40)->index();
            $table->string('model', 60);
            // Line-up model key from config/hadeed.php, so new stock shows on its model page.
            $table->string('model_key', 40)->nullable()->index();
            $table->string('trim', 60)->nullable();
            $table->unsignedSmallInteger('year')->index();
            $table->string('body_type', 20)->index();
            $table->string('transmission', 12)->index();
            $table->string('fuel', 20)->nullable();
            $table->string('engine', 40)->nullable();
            $table->string('drive', 20)->nullable();
            $table->unsignedInteger('mileage_km')->nullable();
            $table->string('colour', 40)->nullable();
            $table->unsignedTinyInteger('seats')->nullable();
            // Whole EC dollars. Null shows "Call for price".
            $table->unsignedInteger('price')->nullable()->index();
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->boolean('featured')->default(false)->index();
            $table->boolean('published')->default(true);
            // Sample stock from DemoSeeder; flagged on screen so nobody mistakes it for real inventory.
            $table->boolean('is_demo')->default(false);
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            // Path on the "uploads" disk (public_path('uploads')).
            $table->string('path');
            // 800px copy for cards and thumbnails (null when GD was unavailable at upload).
            $table->string('thumb_path')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('credit', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_photos');
        Schema::dropIfExists('vehicles');
    }
};
