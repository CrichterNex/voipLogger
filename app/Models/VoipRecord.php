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
     * @param string $data
     * @return void
     * 
     * @throws \Exception
     */
    public static function create(string $data): void {
        echo "Processing VOIP record: $data\n";
        if (empty($data)) {
            echo "Empty data received, ignoring.\n";
            return; // ignore empty data
        }

        if ($data == "\n") {
            echo "Received a newline character, ignoring.\n";
            return;
        }
        if (empty(trim($data))) {
            echo "Received an empty line, ignoring.\n";
            return; // ignore empty lines
        }
        $orig = $data;
        // Remove any leading or trailing whitespace
        $data = trim($data);

        $data = explode(' ', $data);
        foreach ($data as $key => $value) {
            if (trim($data[$key]) === '') {
                unset($data[$key]);
            }
        }

        $data = array_values($data); // re-index the array

        if (!(count($data) == 11 || count($data) == 13)) {
            echo "Invalid VOIP record: expected 11 or 13 fields, found " . count($data) . " fields in record: $orig";
            return; // ignore this record, it is not a valid VOIP record
        }

        if (count($data) < 11) {
            echo "Invalid VOIP record: less than 11 fields found in record: $orig";
            return; // ignore this record, it is not a valid VOIP record
        }
        try {
            $record = new VoipRecord();
            $record->extension = $data[0] ?? '';
            $record->ddi = $data[1] ?? '';
            $datetime = Carbon::createFromFormat('ymd',$data[2] ?? '')->toDate();
            $time = explode(":",$data[3]);
            $datetime->setTime($time[0] ?? 0, $time[1] ?? 0, $time[2] ?? 0);
            if (!$datetime) {
                echo 'Invalid datetime format';
                return;
            }
            $record->datetime = $datetime;
            $duration = [
                'hours' => (int)substr($data[4], 2) ?? 0,
                'mins' => (int)substr($data[4], 3, 2) ?? 0,
                'secs' => (int)substr($data[4], 6, 2) ?? 0
            ];

            $seconds = $duration['hours'] * 3600 + $duration['mins'] * 60 + $duration['secs'];
            $record->duration_in_seconds = $seconds;

            if (count($data) == 11) {
                $record->call_direction = 'incoming';
                $record->initiator = $data[6] ?? '';
                $record->another_number = $data[5] ?? '';
                $record->external_number = $data[7] ?? '';
                $record->some_incoming_call_number1 = $data[8] ?? '';
                $record->some_incoming_call_number2 = $data[9] ?? '';
                $record->received_id = $data[10] ?? '';

            } else if (count($data) == 13) {
                $record->call_direction = 'outgoing';
               
                $record->cost = (float)($data[5] ?? 0.0);
                $record->another_number = $data[7] ?? '';
                $record->initiator = $data[8] ?? '';
                $record->pri_number = $data[9] ?? '';
                $record->destination_number = $data[10] ?? '';
                $record->received_id = $data[12] ?? '';
                $record->external_number = $data[11] ?? '';
                
                
            }
            
            $record->save();
        } catch (Throwable $e) {
            echo "Failed to create VoipRecord in DB: " . $e->getMessage() . "\n";
            echo "Original data: $orig\n";
            echo "Data after processing: " . implode(' ', $data) . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
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
