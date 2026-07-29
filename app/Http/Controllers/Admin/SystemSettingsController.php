<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'cooldown_seconds' => SystemSetting::get('cooldown_seconds', 5),
            'speaker_volume'   => SystemSetting::get('speaker_volume', 80),
            'success_beep_url' => SystemSetting::get('success_beep_url', '/sounds/success.mp3'),
            'error_beep_url'   => SystemSetting::get('error_beep_url', '/sounds/error.mp3'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'cooldown_seconds' => 'required|integer|min:1|max:30',
            'speaker_volume'   => 'required|integer|min:0|max:100',
        ]);

        SystemSetting::set('cooldown_seconds', $request->cooldown_seconds);
        SystemSetting::set('speaker_volume', $request->speaker_volume);

        return back()->with('success', 'System settings saved.');
    }
}
