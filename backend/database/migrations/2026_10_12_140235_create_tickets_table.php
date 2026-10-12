<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('tickets', function (Blueprint $table) {
        $table->id();

        $table->foreignId('customer_id')
            ->constrained('users')
            ->restrictOnDelete();

        $table->foreignId('category_id')
            ->constrained('categories')
            ->restrictOnDelete();

        $table->string('title', 200);
        $table->text('description');

        $table->string('status')->default('open');
        $table->string('priority')->default('medium');

        $table->timestamp('created_at');
        $table->timestamp('updated_at');
    });

    DB::statement("
        ALTER TABLE tickets
        ADD CONSTRAINT tickets_status_check
        CHECK (status IN ('open', 'in_progress', 'resolved', 'closed'))
    ");

    DB::statement("
        ALTER TABLE tickets
        ADD CONSTRAINT tickets_priority_check
        CHECK (priority IN ('low', 'medium', 'high'))
    ");
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
