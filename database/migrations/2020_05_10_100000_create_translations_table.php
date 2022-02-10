<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('translations', static function (Blueprint $table) {
            // Fields
            $table->uuid('id')->primary();
            $table->uuidMorphs('translatable');
            $table->string('translatable_attribute');
            $table->text('value');
            $table->string('locale', 24)->nullable()->comment('RFC 5646. See: http://www.rfc-editor.org/rfc/rfc5646.txt');
            $table->timestamps();

            // Indices
            $table->index(['translatable_type', 'translatable_id', 'locale'], 'translation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
}
