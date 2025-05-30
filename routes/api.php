<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/tcp-listener/status', function (Request $request) {
    $connection = @fsockopen("127.0.0.1", "2533");
    if (is_resource($connection)) {
        fclose($connection);
        return response()->json([
            'status' => 'running',
            'port' => 2533,
            'host' => '0.0.0.0'
        ]);
    } else {
        return response()->json([
            'status' => 'stopped',
            'port' => 2533,
            'host' => '0.0.0.0'
        ]);
    }
});

route::get('/latest-voip-records', function () {
    $records = \App\Models\VoipRecord::orderBy('datetime', 'desc')->take(50)->get();
    $data = convertDataToArray($records);
    return response()->json(['records' => $data]);
});


route::get('/filter-voip-records', function (Request $request) {
    $query = \App\Models\VoipRecord::query();
    if ($request->has('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('extension', 'like', "%{$search}%")
                ->orWhere('ddi', 'like', "%{$search}%")
                ->orWhere('initiator', 'like', "%{$search}%")
                ->orWhere('pri_number', 'like', "%{$search}%")
                ->orWhere('destination_number', 'like', "%{$search}%")
                ->orWhere('call_direction', 'like', "%{$search}%")
                ->orWhere('some_incoming_call_number1', 'like', "%{$search}%")
                ->orWhere('some_incoming_call_number2', 'like', "%{$search}%")
                ->orWhere('external_number', 'like', "%{$search}%");
        });
    } else {
        return response()->json(['error' => 'Search query is required'], 400);
    }

    // Apply sorting and limit to the filtered query
    $records = $query->orderBy('datetime', 'desc')->take(50)->get();
    $data = convertDataToArray($records);
    return response()->json(['records' => $data]);
})->name('filter-voip-records');


function convertDataToArray($data) {
    $arr = [];
    foreach( $data as $record) {
        $arr[] = [
            'duration' => $record->getDurationAttribute(),
            'extension' => $record->extension,
            'datetime' => $record->datetime,
            'initiator' => $record->initiator,
            'destination' => $record->destination_number,
            'call_direction' => $record->call_direction,
            'external_number' => $record->external_number
        ];
    }
    return $arr;
}