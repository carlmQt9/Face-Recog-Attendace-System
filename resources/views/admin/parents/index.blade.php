@extends('layouts.app')
@section('title', 'Parent Setup')
@section('page-title', 'Parent / Guardian Setup')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.parents.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill me-1"></i> Link Parent
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Student</th><th>Parent Name</th><th>Gmail</th><th>Relationship</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($parents as $parent)
                    <tr>
                        <td>{{ $parent->student->user->name }}</td>
                        <td>{{ $parent->parent_name }}</td>
                        <td>{{ $parent->gmail }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($parent->relationship) }}</span></td>
                        <td>
                            <a href="{{ route('admin.parents.edit', $parent) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Remove this parent record?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No parent records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $parents->links() }}
@endsection
