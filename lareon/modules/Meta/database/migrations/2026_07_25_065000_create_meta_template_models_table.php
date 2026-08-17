<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meta_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template' , 150)->unique();
            $table->string('title' , 150)->unique();
            $table->string('model_type')->index();

            $table->timestamps();
        });

        Schema::create('meta_elements', function (Blueprint $table) {
            $table->id();
            $table->string('element' , 150)->unique();
            $table->string('title' , 150)->unique();
            $table->json('settings')->nullable();
            $table->timestamps();
        });


        Schema::create('meta_templates_elements', function (Blueprint $table) {
            $table->foreignId('template_id')->constrained('meta_templates')->cascadeOnDelete();
            $table->foreignId('element_id')->constrained('meta_elements')->cascadeOnDelete();
            $table->string('title');
            $table->string('name');
            $table->json('settings')->nullable();
            $table->tinyInteger('sort' ,false ,true)->default(0);

            $table->unique(['template_id' , 'element_id', 'name'] ,'meta_templates_elements_name');
        });


        Schema::create('meta_models', function (Blueprint $table) {
            $table->foreignId('element_id')->constrained('meta_elements')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('meta_templates')->cascadeOnDelete();
            $table->morphs('model');
            $table->string('key');
            $table->json('content')->nullable();

            $table->unique(['element_id', 'template_id', 'model_type', 'model_id' ,'key'] ,'meta_element_template_model_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_models');
        Schema::dropIfExists('meta_templates_elements');
        Schema::dropIfExists('meta_elements');
        Schema::dropIfExists('meta_templates');

    }
};
