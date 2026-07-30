<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 求人のこだわり条件(多対多)。 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posting_feature', function (Blueprint $table) {
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_feature_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['job_posting_id', 'job_feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posting_feature');
    }
};
