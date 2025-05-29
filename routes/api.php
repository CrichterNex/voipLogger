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
    $records = \App\Models\VoipRecord::orderBy('created_at', 'desc')->take(50)->get();
    
    return response()->json(['records' => $records]);
});


route::post('/filter-voip-records', function (Request $request) {
    $query = \App\Models\VoipRecord::query();
    
    if ($request->has('extension')) {
        $query->where('extension', $request->input('extension'));
    }
    $records = \App\Models\VoipRecord::orderBy('created_at', 'desc')->take(50)->get();
    return response()->json($records);
})->name('filter-voip-records');