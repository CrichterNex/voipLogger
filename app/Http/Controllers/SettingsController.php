<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = \App\Models\Settings::all();
        $availableSettings = [
            'cdr_allowed_hosts' => 'Allowed hosts for CDR data (comma-separated)',
            // Add more settings as needed
        ];
        return view('settings.index', compact('settings', 'availableSettings'));
    }

    public function update(Request $request)
    {
        $setting = \App\Models\Settings::where('name', $request->name)->first();

        if(!$setting) {
            $setting = new \App\Models\Settings();
            $setting->name = $request->input('name');
        }
        $setting->value = $request->input('value');
        $setting->save();

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
