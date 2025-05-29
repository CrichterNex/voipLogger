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
        Schema::create('voip_records', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->integer('received_id')->nullable();
            $table->string('extension');
            $table->string('ddi')->nullable(); 
            $table->datetime('datetime');
            $table->integer('duration_in_seconds')->default(0);
            $table->double('cost')->default(0.0);
            $table->string('some_cost_field')->nullable();
            $table->string('another_number')->nullable();
            $table->string('initiator')->nullable();
            $table->string('pri_number')->nullable();
            $table->string('destination_number')->nullable();
            $table->string('call_direction')->nullable();
            $table->string('some_incoming_call_number1')->nullable();
            $table->string('some_incoming_call_number2')->nullable();
            $table->string('external_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voip_records');
    }
};
