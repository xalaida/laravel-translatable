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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->morphs('translatable');
            $table->string('translatable_attribute');
            $table->string('locale', 24)->comment('RFC 5646. See: https://www.rfc-editor.org/rfc/rfc5646.txt.');
            $table->text('value')->nullable();
            $table->timestamps();

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
