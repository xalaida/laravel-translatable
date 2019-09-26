<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('translatable');
            $table->string('translatable_attribute');
            $table->string('translatable_value');
            $table->string('locale', 24)->comment('RFC 5646. See: http://www.rfc-editor.org/rfc/rfc5646.txt');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
}
