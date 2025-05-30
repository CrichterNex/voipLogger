<?php

namespace App\Http\Controllers;

use App\Exports\RecordExport;
use App\Models\VoipRecord;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SearchController extends Controller
{
    /**
     * Reurns the search index view.
     * This is the main entry point for the search functionality.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \Throwable
     */
    public function index() {
        try {
            return view('search.index');

        } catch (\Throwable $e) {
            // Log the error or handle it as needed
            return response()->view('errors.500', ['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Search functionality.
     * @param Request $request
     * @return \illuminate\Http\View
     * @throws \Throwable
     */
    public function search(Request $request) {
        $request->validate([
            'extension' => 'required|string|max:255',
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d',
        ]);
        try {
            $records  = VoipRecord::where('extension', 'like', '%' . $request->extension . '%')->whereBetween('datetime', [
                $request->from . ' 00:00:00',
                $request->to . ' 23:59:59'
            ])->orderBy('datetime', 'desc')->get();

            return view('search.index', [ 'records' => $records, 'extension' => $request->extension, 'from' => $request->from, 'to' => $request->to ]);
        } catch (\Throwable $e) {
            // Log the error or handle it as needed
            return back()->withErrors(['error' => 'Failed to search records: ' . $e->getMessage()]);
        }
    }

    /**
     * Export functionality.
     */
    public function export(Request $request) {
        $request->validate([
            'extension' => 'required|string|max:255',
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d',
        ]);
        $records = VoipRecord::where('extension', 'like', '%' . $request->extension . '%')->whereBetween('datetime', [
            $request->from . ' 00:00:00',
            $request->to . ' 23:59:59'
        ])->orderBy('datetime', 'desc')->get();

        try {
            return Excel::download(new RecordExport($records), 'call_records_' . $request->extension . '_' . $request->from . '_' . $request->to . '.xlsx');
        } catch (\Throwable $e) {
            // Log the error or handle it as needed
            return back()->withErrors(['error' => 'Failed to export records: ' . $e->getMessage()]);
        }
    }



}
