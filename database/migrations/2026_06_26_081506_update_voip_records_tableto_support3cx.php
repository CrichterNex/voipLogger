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
        /**
         * Records sent from 3CX CDR are in the following format:
         * 0 - !CDR ID
         * 1 - !Call ID
         * 2 - !Duration
         * 3 - !Time start 
         * 4 - !Time answered
         * 5 - !Time end
         * 6 - !Termination reason
         * 7 - !Originating number
         * 8 - !Destination number
         * 9 - !From DN
         * 10 - !to DN
         * 11 - !dial-no
         * 12 - !reason code
         * 13 - !final-number
         * 14 - !final dn
         * 15 - !bill-code
         * 16- !Bill rate
         * 17 - !Bill-cost
         * 18 - !bill name
         * 19 - !Chain (routed)
         */

        /**
         * Current table structure:
         * $table->uuid('id')->primary()->unique();
         *   $table->integer('received_id')->nullable(); -- CALL ID
         *   $table->string('extension');
         *   $table->string('ddi')->nullable();  
         *   $table->datetime('datetime'); -- Time start
         *   $table->integer('duration_in_seconds')->default(0); -- Duration
         *   $table->double('cost')->default(0.0); -- Bill-cost
         *   $table->string('some_cost_field')->nullable(); -- Bill-code
         *   $table->string('another_number')->nullable(); 
         *   $table->string('initiator')->nullable(); -- originating number
         *   $table->string('pri_number')->nullable();
         *   $table->string('destination_number')->nullable(); -- destination number
         *   $table->string('call_direction')->nullable(); --
         *   $table->string('some_incoming_call_number1')->nullable(); --
         *   $table->string('some_incoming_call_number2')->nullable(); --
         *   $table->string('external_number')->nullable(); --
         *   $table->timestamps();
         */

        Schema::table('voip_records', function (Blueprint $table) {
            $table->string('cdr_id')->nullable();
            $table->string('call_id')->nullable();
            $table->string('time_answered')->nullable();
            $table->string('time_end')->nullable();
            $table->string('termination_reason')->nullable();
            $table->string('from_dn')->nullable();
            $table->string('to_dn')->nullable();
            $table->string('dial_no')->nullable();
            $table->string('reason_code')->nullable();
            $table->string('final_dn')->nullable();
            $table->string('bill_code')->nullable();
            $table->double('bill_rate')->default(0.0);
            $table->string('bill_name')->nullable();
            $table->string('dialed_number')->nullable();
            $table->string('chain_routed')->nullable();
            
            $table->string('final_dn')->nullable();
            $table->string('record_type')->nullable();
            //Rename some of the columns to match the 3CX CDR fields
            $table->renameColumn('some_cost_field', 'bill_name');
            $table->renameColumn('some_incoming_call_number1', 'from_dn');
            $table->renameColumn('some_incoming_call_number2', 'to_dn');
            $table->renameColumn('cost', 'bill_cost');
            $table->renameColumn('another_number', 'final_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voip_records', function (Blueprint $table) {
            $table->dropColumn([
                'cdr_id',
                'call_id',
                'time_answered',
                'time_end',
                'termination_reason',
                'from_dn',
                'to_dn',
                'dial_no',
                'reason_code',
                'final_number',
                'final_dn',
                'bill_code',
                'bill_rate',
                'bill_cost',
                'chain_routed'
            ]);
            //Rename bill_name back to some_cost_field
            $table->renameColumn('bill_name', 'some_cost_field');
        });
    }
};
