@extends('layouts.app')
@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear-fill me-2"></i>Configure System Behaviour</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-hourglass-split text-primary me-1"></i>
                            Cool-Down Timer (seconds)
                        </label>
                        <input type="number" name="cooldown_seconds" class="form-control @error('cooldown_seconds') is-invalid @enderror"
                               value="{{ old('cooldown_seconds', $settings['cooldown_seconds']) }}" min="1" max="30">
                        <div class="form-text">After a successful scan, the camera ignores the same student for this many seconds.</div>
                        @error('cooldown_seconds')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-volume-up-fill text-primary me-1"></i>
                            Speaker Volume (0–100)
                        </label>
                        <input type="range" name="speaker_volume" class="form-range"
                               value="{{ old('speaker_volume', $settings['speaker_volume']) }}" min="0" max="100"
                               oninput="document.getElementById('volLabel').textContent = this.value + '%'">
                        <span id="volLabel">{{ $settings['speaker_volume'] }}%</span>
                    </div>

                    <div class="mb-3 p-3 bg-light rounded">
                        <p class="fw-semibold mb-2">Beep Reference</p>
                        <p class="mb-1"><span class="text-success">🔊 1 Happy Beep</span> — Successful scan. Student present. Parent notified.</p>
                        <p class="mb-0"><span class="text-danger">🔊🔊 2 Sharp Beeps</span> — Error. Unrecognized face or cool-down active.</p>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
