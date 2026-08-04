@extends('layouts.app')
@section('title', 'System Settings')
@section('page-title', 'System Settings')

@push('styles')
<style>
.settings-section {
    background:#fff; border-radius:16px;
    border:1px solid #e2e8f0;
    box-shadow:0 1px 6px rgba(0,0,0,.06);
    overflow:hidden;
}
.settings-section-header {
    padding:14px 20px; border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; gap:12px; background:#f8fafc;
}
.s-icon {
    width:36px; height:36px; border-radius:9px;
    display:flex; align-items:center; justify-content:center;
    font-size:16px; flex-shrink:0;
}
.settings-section-header h6 { margin:0; font-size:14px; font-weight:700; color:#0f172a; }
.settings-section-header p  { margin:0; font-size:11px; color:#94a3b8; }
.settings-section-body { padding:20px; }

.setting-row {
    display:flex; align-items:flex-start; gap:16px;
    padding:14px 0; border-bottom:1px solid #f1f5f9;
    flex-wrap: wrap;
}
.setting-row:last-child { border-bottom:none; padding-bottom:0; }
.setting-info { flex:1; min-width:180px; }
.setting-info label { font-size:13px; font-weight:600; color:#1e293b; display:block; margin-bottom:2px; }
.setting-desc { font-size:11px; color:#94a3b8; line-height:1.5; }
.setting-control { flex-shrink:0; min-width:160px; text-align:right; }

.num-stepper {
    display:inline-flex; align-items:center;
    border:1.5px solid #e2e8f0; border-radius:10px; overflow:hidden; background:#fff;
}
.num-stepper button {
    width:34px; height:36px; border:none; background:transparent;
    font-size:16px; color:#475569; cursor:pointer;
    display:flex; align-items:center; justify-content:center; transition:background .15s;
}
.num-stepper button:hover { background:#f1f5f9; }
.num-stepper input {
    width:48px; height:36px; border:none; outline:none;
    text-align:center; font-size:14px; font-weight:700; color:#0f172a;
}

.vol-wrap { display:flex; flex-direction:column; align-items:flex-end; gap:5px; }
input[type=range].vol-bar {
    -webkit-appearance:none; width:100%; max-width:200px; height:5px; border-radius:50px;
    outline:none; cursor:pointer;
}
input[type=range].vol-bar::-webkit-slider-thumb {
    -webkit-appearance:none; width:16px; height:16px;
    border-radius:50%; background:#4f46e5;
    box-shadow:0 2px 6px rgba(79,70,229,.4); cursor:pointer;
}
.vol-label { font-size:12px; font-weight:700; color:#4f46e5; }

.beep-card {
    border-radius:10px; padding:12px 14px;
    display:flex; align-items:center; gap:12px;
    border:1px solid #e2e8f0; background:#f8fafc;
}
.beep-icon {
    width:36px; height:36px; border-radius:8px;
    display:flex; align-items:center; justify-content:center;
    font-size:17px; flex-shrink:0;
}
.settings-footer {
    background:#f8fafc; border-top:1px solid #e2e8f0;
    padding:12px 20px; display:flex;
    align-items:center; justify-content:space-between;
}
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.settings.update') }}" method="POST">
@csrf
<div class="row g-3">

    {{-- LEFT: Scan Behaviour --}}
    <div class="col-lg-8">
        <div class="settings-section">
            <div class="settings-section-header">
                <div class="s-icon" style="background:rgba(79,70,229,.1);color:#4f46e5;">
                    <i class="bi bi-gear-fill"></i>
                </div>
                <div>
                    <h6>Scan Behaviour</h6>
                    <p>Controls how the camera handles repeated scans</p>
                </div>
            </div>
            <div class="settings-section-body">

                <div class="setting-row">
                    <div class="setting-info">
                        <label><i class="bi bi-hourglass-split me-1 text-primary"></i> Cool-Down Timer</label>
                        <div class="setting-desc">After a successful scan, the same student is ignored for this many seconds. Range: 1–30.</div>
                        @error('cooldown_seconds')
                            <div class="text-danger" style="font-size:11px;margin-top:2px;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="setting-control">
                        <div class="num-stepper">
                            <button type="button" onclick="stepCooldown(-1)">−</button>
                            <input type="number" name="cooldown_seconds" id="cooldownInput"
                                   value="{{ old('cooldown_seconds', $settings['cooldown_seconds']) }}"
                                   min="1" max="30" readonly>
                            <button type="button" onclick="stepCooldown(1)">+</button>
                        </div>
                        <div style="font-size:10px;color:#94a3b8;margin-top:3px;">seconds</div>
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-info">
                        <label><i class="bi bi-volume-up-fill me-1 text-primary"></i> Speaker Volume</label>
                        <div class="setting-desc">Volume for scan beep sounds. 0 = silent, 100 = maximum.</div>
                    </div>
                    <div class="setting-control">
                        <div class="vol-wrap">
                            <span class="vol-label" id="volLabel">{{ $settings['speaker_volume'] }}%</span>
                            <input type="range" name="speaker_volume" id="volSlider"
                                   class="vol-bar"
                                   value="{{ old('speaker_volume', $settings['speaker_volume']) }}"
                                   min="0" max="100"
                                   oninput="updateVolume(this.value)">
                        </div>
                    </div>
                </div>

            </div>
            <div class="settings-footer">
                <span style="font-size:11px;color:#94a3b8;">
                    <i class="bi bi-info-circle me-1"></i>Changes apply immediately after saving.
                </span>
                <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                    <i class="bi bi-floppy-fill me-1"></i> Save Settings
                </button>
            </div>
        </div>
    </div>

    {{-- RIGHT: Beep Reference --}}
    <div class="col-lg-4">
        <div class="settings-section h-100">
            <div class="settings-section-header">
                <div class="s-icon" style="background:rgba(6,182,212,.1);color:#06b6d4;">
                    <i class="bi bi-soundwave"></i>
                </div>
                <div>
                    <h6>Beep Signal Reference</h6>
                    <p>What each audio signal means</p>
                </div>
            </div>
            <div class="settings-section-body">
                <div class="d-flex flex-column gap-2">
                    <div class="beep-card">
                        <div class="beep-icon" style="background:rgba(74,222,128,.12);color:#16a34a;">🔊</div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#15803d;">1 Happy Beep</div>
                            <div style="font-size:11px;color:#64748b;margin-top:1px;">Scan success — student present, parent notified.</div>
                        </div>
                    </div>
                    <div class="beep-card">
                        <div class="beep-icon" style="background:rgba(248,113,113,.12);color:#dc2626;">🔊🔊</div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#dc2626;">2 Sharp Beeps</div>
                            <div style="font-size:11px;color:#64748b;margin-top:1px;">Error — face not recognised or cool-down active.</div>
                        </div>
                    </div>
                    <div class="beep-card" style="background:#fff7ed;border-color:#fed7aa;">
                        <div class="beep-icon" style="background:rgba(251,146,60,.12);color:#ea580c;">🔇</div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#ea580c;">No Sound</div>
                            <div style="font-size:11px;color:#64748b;margin-top:1px;">Volume set to 0 — scanning still works silently.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</form>

@endsection

@push('scripts')
<script>
function stepCooldown(delta) {
    const el = document.getElementById('cooldownInput');
    el.value = Math.max(1, Math.min(30, parseInt(el.value) + delta));
}
function updateVolume(val) {
    document.getElementById('volLabel').textContent = val + '%';
    const s = document.getElementById('volSlider');
    s.style.background = `linear-gradient(90deg,#4f46e5 ${val}%,#e2e8f0 ${val}%)`;
}
document.addEventListener('DOMContentLoaded', () => {
    updateVolume(document.getElementById('volSlider').value);
});
</script>
@endpush
