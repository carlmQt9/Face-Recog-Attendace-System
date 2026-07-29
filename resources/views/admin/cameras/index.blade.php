@extends('layouts.app')
@section('title', 'Camera Management')
@section('page-title', 'Camera Management')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.cameras.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill me-1"></i> Add Camera
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Location</th><th>Type</th><th>Device ID</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($cameras as $camera)
                    <tr>
                        <td>{{ $camera->name }}</td>
                        <td>{{ $camera->location }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($camera->type) }}</span></td>
                        <td>{{ $camera->device_identifier ?? '—' }}</td>
                        <td>
                            @if($camera->is_active)
                                <span class="badge bg-success"><i class="bi bi-circle-fill me-1"></i>Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.cameras.toggle', $camera) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-{{ $camera->is_active ? 'warning' : 'success' }}">
                                    {{ $camera->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <a href="{{ route('admin.cameras.edit', $camera) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.cameras.destroy', $camera) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this camera?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No cameras added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $cameras->links() }}
@endsection
