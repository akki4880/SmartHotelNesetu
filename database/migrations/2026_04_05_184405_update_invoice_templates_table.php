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
        Schema::table('invoice_templates', function (Blueprint $table) {

            // 🔥 Rename template_key → view_file (better meaning)
            $table->renameColumn('template_key', 'view_file');

            // 🔥 Rename image_url → preview_image
            $table->renameColumn('image_url', 'preview_image');

            // 🔥 Rename status → is_active
            $table->renameColumn('status', 'is_active');

            // 🔥 Add new columns
            $table->string('slug')->unique()->after('name');

            $table->enum('type', ['free', 'paid'])
                ->default('free')
                ->after('view_file');

            $table->decimal('price', 10, 2)
                ->nullable()
                ->after('type');

            $table->json('default_settings')
                ->nullable()
                ->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_templates', function (Blueprint $table) {

            // 🔁 Revert renamed columns
            $table->renameColumn('view_file', 'template_key');
            $table->renameColumn('preview_image', 'image_url');

            // 🔁 Drop added columns
            $table->dropColumn([
                'slug',
                'type',
                'price',
                'default_settings'
            ]);
        });
    }
};
