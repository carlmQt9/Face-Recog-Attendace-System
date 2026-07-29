<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use Illuminate\Http\Request;

class CameraController extends Controller
{
    public function index()
    {
        $cameras = Camera::latest()->paginate(20);
        return view('admin.cameras.index', compact('cameras'));
    }

    public function create()
    {
        return view('admin.cameras.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'location'          => 'required|string|max:255',
            'type'              => 'required|in:classroom,entrance,kiosk',
            'device_identifier' => 'nullable|string|max:255',
        ]);

        Camera::create($request->only('name', 'location', 'type', 'device_identifier'));

        return redirect()->route('admin.cameras.index')
            ->with('success', 'Camera added successfully.');
    }

    public function edit(Camera $camera)
    {
        return view('admin.cameras.edit', compact('camera'));
    }

    public function update(Request $request, Camera $camera)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'location'          => 'required|string|max:255',
            'type'              => 'required|in:classroom,entrance,kiosk',
            'device_identifier' => 'nullable|string|max:255',
        ]);

        $camera->update($request->only('name', 'location', 'type', 'device_identifier'));

        return redirect()->route('admin.cameras.index')
            ->with('success', 'Camera updated successfully.');
    }

    public function toggle(Camera $camera)
    {
        $camera->update(['is_active' => !$camera->is_active]);

        $status = $camera->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Camera {$status} successfully.");
    }

    public function destroy(Camera $camera)
    {
        $camera->delete();
        return redirect()->route('admin.cameras.index')
            ->with('success', 'Camera removed.');
    }
}
