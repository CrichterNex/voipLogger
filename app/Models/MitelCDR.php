<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MitelCDR extends Model
{
    public static function PreProcessData(string $data): void
    {
        if (empty($data)) {
            throw new \Exception("Empty data received, ignoring."); // ignore empty data
        }

        if ($data == "\n") {
            throw new \Exception("Received a newline character, ignoring."); // ignore newline characters
        }
        if (empty(trim($data))) {
            throw new \Exception("Received an empty line, ignoring."); // ignore empty lines
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
            throw new \Exception("Invalid VOIP record: expected 11 or 13 fields, found " . count($data) . " fields in record: $orig"); // ignore this record, it is not a valid VOIP record
        }

        if (count($data) < 11) {
            throw new \Exception("Invalid VOIP record: less than 11 fields found in record: $orig"); // ignore this record, it is not a valid VOIP record
        }
        try {
            ///////////////////////////////////////////////////////////////////////////////
            $record['termination_reason'] = '';
            $record['bill_code'] = null;
            $record['bill_rate'] = (float)0;
            $record['bill_cost'] = (float)0;
            $record['bill_name'] = null;
            $record['chain_routed'] = "no info sent";
            $record['record_type'] = 'Mitel CDR';

            ////////////////////////////////////////////////////////////////////////////////////////////
            $record = [];
            $record['extension'] = $data[0] ?? '';
            $record['ddi'] = $data[1] ?? '';
            $datetime = Carbon::createFromFormat('ymd',$data[2] ?? '')->toDate();
            $time = explode(":",$data[3]);
            $datetime->setTime($time[0] ?? 0, $time[1] ?? 0, $time[2] ?? 0);
            if (!$datetime) {
                echo 'Invalid datetime format';
                throw new \Exception("Invalid datetime format in record: $orig"); 
            }
            $record['datetime'] = $datetime;
            $duration = [
                'hours' => (int)substr($data[4], 2) ?? 0,
                'mins' => (int)substr($data[4], 3, 2) ?? 0,
                'secs' => (int)substr($data[4], 6, 2) ?? 0
            ];

            $seconds = $duration['hours'] * 3600 + $duration['mins'] * 60 + $duration['secs'];
            $record['duration_in_seconds'] = $seconds;
            $record['time_answered'] = null;
            $record['time_end'] = null;

            if (count($data) == 11) {
                $record['call_direction'] = 'incoming';
                $record['initiator'] = $data[6] ?? '';
                $record['another_number'] = $data[5] ?? '';
                $record['external_number'] = $data[7] ?? '';
                $record['received_id'] = $data[10] ?? '';

            } else if (count($data) == 13) {
                $record['call_direction'] = 'outgoing';
               
                $record['cost'] = (float)($data[5] ?? 0.0);
                $record['another_number'] = $data[7] ?? '';
                $record['initiator'] = $data[8] ?? '';
                $record['pri_number'] = $data[9] ?? '';
                $record['destination_number'] = $data[10] ?? '';
                $record['received_id'] = $data[12] ?? '';
                $record['external_number'] = $data[11] ?? '';
                $record['final_number'] = $data[11] ?? '';
            }


            VoipRecord::create($record);
            

        }catch (Throwable $e) {
            
            //dump original data into filr and error message for debugging
            echo "Failed to create VoipRecord in DB: " . $e->getMessage() . "\n";
            Storage::append('logs/mitel_cdr_errors.log', "Original data: $orig\n");
            throw new \Exception("Failed to decode VOIP record: " . $e->getMessage() . "\nOriginal data: $orig\nData after processing: " . implode(' ', $data) . "\nStack trace: " . $e->getTraceAsString() . "\n"); // ignore this record, it is not a valid VOIP record
        }

    }
}
