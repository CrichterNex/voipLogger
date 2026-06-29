<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ThreeCXCDR extends Model
{
    public static function PreProcessData(string $data): void
    {
        $orig = $data;
        try {
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

            $data = explode(',', $data);
            
            $record = [];

            if ( str_contains($data[7],"Ext.") && str_contains($data[13],"Ext.")) {
                $record['call_direction'] = 'Internal';
                $record['external_number'] = null;
                $record['initiator'] = str_replace('Ext.', '', trim($data[7]));
                $record['extension'] = str_replace('Ext.', '', trim($data[7]));
                $record['destination_number'] = trim($data[11]);
            } else if (strlen(trim($data[7])) >= 9 && strlen(trim($data[7])) <= 11) {
                $record['call_direction'] = 'Incoming';
                $record['extension'] = trim($data[8]);
                $record['initiator'] = trim($data[7]);
                $record['destination_number'] = str_replace('Ext.', '', trim($data[11]));
                $record['external_number'] = trim($data[7]);
            } else {
                $record['call_direction'] = 'Outgoing';
                $record['external_number'] = trim($data[8]);
                $record['initiator'] = trim(str_replace('Ext.', '', $data[7]));
                $record['destination_number'] = trim($data[8]);
                $record['extension'] = trim($data[9]);
            }

            $record['final_number'] = trim($data[11]);
            $record['from_dn'] = trim($data[9]);
            $record['to_dn'] = trim($data[10]);
            $record['received_id'] = trim($data[0]);
            $record['pri_number'] = '';
            $record['call_id'] = trim($data[1]);
            $record['datetime'] = Carbon::parse(trim($data[3]))->format('Y-m-d H:i:s');
            $record['time_answered'] = Carbon::parse(trim($data[4]))->format('Y-m-d H:i:s');
            $record['time_end'] = Carbon::parse(trim($data[5]))->format('Y-m-d H:i:s');
            $record['ddi'] = '';
            $record['record_type'] = '3CX CDR';

            //Convert 00:XX:XX to seconds
            $durationParts = explode(':', $data[2]);
            $durationInSeconds = 0;
            if (count($durationParts) === 3) {
                $durationInSeconds = ((int)$durationParts[0] * 3600)
                    + ((int)$durationParts[1] * 60)
                    + (int)$durationParts[2];
            }
            $record['duration_in_seconds'] = $durationInSeconds;
            $record['termination_reason'] = trim($data[6]);
            $record['bill_code'] = trim($data[15]);
            $record['bill_rate'] = (float)trim($data[16]);
            $record['cost'] = (float)trim($data[17]);
            $record['bill_name'] = trim($data[18]);
            $chain = trim($data[19]);
            $chain = str_replace('Chain:', '', $chain);
            // Split the chain by semicolon and trim each part
            $chainParts = array_map('trim', explode(';', $chain));
            // show order of the chain in the order it was routed, in number format, seperated by <br>
            $record['chain_routed'] = implode('<br>', $chainParts);
            $record['chain_routed'] = trim($record['chain_routed']);


            VoipRecord::create($record);
        } catch (\Exception $e) {
            
            //dump original data into filr and error message for debugging
            echo "Failed to create VoipRecord in DB: " . $e->getMessage() . "\n";
            Storage::append('logs/threecx_cdr_errors.log', "Original data: $orig\n");
        
            return; // ignore this record, it is not a valid VOIP record
        }
        
    }
}
