<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use Illuminate\Http\Request;

class CameraController extends Controller
{
    public function index()
    {
        // Check if there are any cameras, if not, create a default local camera
        $this->ensureDefaultLocalCamera();
        
        $cameras = Camera::latest()->paginate(20);
        return view('admin.cameras.index', compact('cameras'));
    }

    /**
     * Ensure there's at least one default local camera available
     */
    private function ensureDefaultLocalCamera()
    {
        if (Camera::count() === 0) {
            Camera::create([
                'name' => 'Default Local Camera',
                'location' => 'Admin Device',
                'type' => 'classroom',
                'is_local_device' => true,
                'is_active' => true,
                'device_identifier' => null,
            ]);
        }
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

        $data = $request->only('name', 'location', 'type', 'device_identifier');
        $data['is_local_device'] = $request->boolean('is_local_device');
        
        // Automatically activate local cameras by default
        if ($data['is_local_device']) {
            $data['is_active'] = true;
        }

        Camera::create($data);

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

        $data = $request->only('name', 'location', 'type', 'device_identifier');
        $data['is_local_device'] = $request->boolean('is_local_device');

        $camera->update($data);

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
