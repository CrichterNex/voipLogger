<?php

namespace App\Models;

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

                    $data = explode(' ', $data);

                    //Data is not named, so we need to remove any empty fields and re-index the array
                    // Remove any empty fields
                    $data = array_filter($data, function ($value) {
                        return !is_null($value) && $value !== '';
                    });


                    /**
                     * 0 - CDR ID
                     * 1 - Call ID
                     * 2 - Duration
                     * 3 - Time start
                     * 4 - Time answered
                     * 5 - Time end
                     * 6 - Termination reason
                     * 7 - Originating number
                     * 8 - Destination number
                     * 9 - From DN
                     * 10 - to DN
                     * 11 - dial-no
                     * 12 - reason code
                     * 13 - final-number
                     * 14 - final dn
                     * 15 - bill-code
                     * 16- Bill rate
                     * 17 - Bill-cost
                     * 18 - bill name
                     * 19 - Chain (routed)
                     */

                    if (!(count($data) == 11 || count($data) == 13)) {
                        throw new \Exception("Invalid VOIP record: expected 11 or 13 fields, found " . count($data) . " fields in record: $orig"); // ignore this record, it is not a valid VOIP record
                    }

                    if (count($data) < 11) {
                        throw new \Exception("Invalid VOIP record: less than 11 fields found in record: $orig"); // ignore this record, it is not a valid VOIP record
                    }
                    $record = [];

                    if (str_contains($data[6],"Ext:")) {
                        $record['call_direction'] = 'incoming';
                    } else {
                        $record['call_direction'] = 'outgoing';
                    }

                    $record['extension'] = $data[10];
                    $record['final_number'] = $data[9];
                    $record['external_number'] = $data[12];
                    $record['from_dn'] = $data[10];
                    $record['to_dn'] = $data[11];
                    $record['received_id'] = $data[0];
                    $record['pri_number'] = '';
                    $record['destination_number'] = $data[9];
                    $record['call_id'] = $data[1];
                    $record['time_start'] = $data[4];
                    $record['time_answered'] = $data[5];
                    $record['time_end'] = $data[6];
                    $record['ddi'] = '';
                    

                    //Convert 00:XX:XX to seconds
                    $durationParts = explode(':', $data[2]);
                    $durationInSeconds = 0;
                    if (count($durationParts) === 3) {
                        $durationInSeconds = ((int)$durationParts[0] * 3600)
                            + ((int)$durationParts[1] * 60)
                            + (int)$durationParts[2];
                    }
                    $record['duration_in_seconds'] = $durationInSeconds;
                    $record['termination_reason'] = $data[7];
                    $record['bill_code'] = $data[16];
                    $record['bill_rate'] = (float)$data[17];
                    $record['cost'] = (float)$data[18];
                    $record['bill_name'] = $data[19];
                    $record['chain_routed'] = $data[20];


                    VoipRecord::create($record);
        } catch (\Exception $e) {
            
            //dump original data into filr and error message for debugging
            echo "Failed to create VoipRecord in DB: " . $e->getMessage() . "\n";
            Storage::append('logs/threecx_cdr_errors.log', "Original data: $orig\n");
        
            return; // ignore this record, it is not a valid VOIP record
        }
        
    }
}
