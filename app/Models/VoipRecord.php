<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Throwable;

class VoipRecord extends Model
{

    protected $table = 'voip_records';

    protected $keyType = 'string';
    protected $primaryKey = 'id';
    public $incrementing = false;


    public static function booted() {
    static::creating(function ($model) {
        $model->id = Uuid::uuid4();
    });
}
    /**$table->uuid('id')->primary()->unique();
            $table->integer('received_id')->nullable();
            $table->string('extension');
            $table->string('ddi'); 
            $table->datetime('datetime');
            $table->integer('duration_in_seconds')->default(0);
            $table->double('cost')->default(0.0);
            $table->string('some_cost_field')->nullable();
            $table->string('another_number')->nullable();
            $table->string('initiator')->nullable();
            $table->string('pri_number')->nullable();
            $table->string('destination_number')->nullable();
            $table->string('call_type')->nullable();
            $table->string('some_incoming_call_number1')->nullable();
            $table->string('some_incoming_call_number2')->nullable();
            $table->string('external_number')->nullable();
            $table->timestamps();
        */

    /**
     * creates a VOIP Record to save to DB
     * @param array $data
     * @return void
     * 
     * @throws \Exception
     */
    public static function create(array $data): void {
        try {
            /**
             * Data must have the following keys:
             * call_direction
             * extension
             * final_number
             * external number
             * from_dn
             * to_dn
             * received id
             * pri_number
             * destination number
             * call_id
             * time_start
             * time_answered
             * time_end
             * duration_in_seconds
             * Termination reason -- can be null
             * bill_code -- can be null
             * bill_rate -- can be null
             * bill_cost -- can be null
             * bill_name -- can be null
             * chain_routed -- can be null
             * ddi -- can be null
             */

            $record = new self();
            $record->call_direction = $data['call_direction'] ?? '';
            $record->extension = $data['extension'] ?? '';
            $record->final_number = $data['final_number'] ?? '';
            $record->external_number = $data['external_number'] ?? '';
            $record->from_dn = $data['from_dn'] ?? '';
            $record->to_dn = $data['to_dn'] ?? '';
            $record->received_id = $data['received_id'] ?? null;
            $record->pri_number = $data['pri_number'] ?? '';
            $record->destination_number = $data['destination_number'] ?? '';
            $record->call_id = $data['call_id'] ?? '';
            $record->time_start = $data['time_start'] ?? null;
            $record->time_answered = $data['time_answered'] ?? null;
            $record->time_end = $data['time_end'] ?? null;
            $record->duration_in_seconds = $data['duration_in_seconds'] ?? 0;
            $record->termination_reason = $data['termination_reason'] ?? '';
            $record->bill_code = $data['bill_code'] ?? null;
            $record->bill_rate = $data['bill_rate'] ?? 0.0;
            $record->cost = $data['cost'] ?? 0.0;
            $record->bill_name = $data['bill_name'] ?? null;
            $record->chain_routed = $data['chain_routed'] ?? null;
            $record->ddi = $data['ddi'] ?? null;

            $record->save();
        } catch (Throwable $e) {
            return; // ignore this record, it is not a valid VOIP record
        }

        
    }


    /**
     * Returns durantion in seconds
     * @return int
     */
    public function getDurationInSecondsAttribute($value) {
        return $value;
    }
     

    /**
     * Returns the call type based on the call direction
     */
    protected function callDirection(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords($value),
        );
    }

    /**
     * Returns destination_number, and removes null if it is empty
     * @return string|null
     */
    protected function destinationNumber(): Attribute
    {
        if (empty($this->attributes['destination_number'])) {
            return Attribute::make(
                get: fn () => "",
            );
        }
        return Attribute::make(
            get: fn (string $value) => $value,
        );
    }

    /**
     * Returns call duration in a human-readable format
     */
    public function getDurationAttribute(): string {
        $hours = floor($this->duration_in_seconds / 3600);
        $minutes = floor(($this->duration_in_seconds % 3600) / 60);
        $seconds = $this->duration_in_seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
