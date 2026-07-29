@extends('layouts.app')
@section('title', 'Capture Face')
@section('page-title', 'Capture Face — ' . $person->user->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-camera-fill me-2 text-primary"></i>
                    Webcam Capture for <strong>{{ $person->user->name }}</strong>
                    <span class="badge bg-{{ $type === 'student' ? 'primary' : 'success' }} ms-2">{{ ucfirst($type) }}</span>
                </h6>
            </div>
            <div class="card-body text-center">
                <video id="webcam" autoplay playsinline width="100%" style="border-radius:8px;max-height:380px;object-fit:cover;" class="mb-3"></video>
                <canvas id="snapshot" style="display:none;"></canvas>
                <img id="preview" src="" alt="" style="display:none;border-radius:8px;max-width:100%;" class="mb-3">

                <div class="d-flex gap-3 justify-content-center">
                    <button id="captureBtn" class="btn btn-primary" onclick="takeSnapshot()">
                        <i class="bi bi-camera-fill me-1"></i> Take Photo
                    </button>
                    <button id="retakeBtn" class="btn btn-outline-secondary d-none" onclick="retake()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Retake
                    </button>
                    <button id="saveBtn" class="btn btn-success d-none" onclick="savePhoto()">
                        <i class="bi bi-check-circle-fill me-1"></i> Save Face
                    </button>
                </div>

                <form id="faceForm" action="{{ route('admin.face-registration.store', ['type'=>$type, 'id'=>$person->id]) }}" method="POST" class="d-none">
                    @csrf
                    <input type="hidden" name="face_image" id="face_image">
                </form>
            </div>
        </div>
        <a href="{{ route('admin.face-registration.index') }}" class="btn btn-link mt-2">
            <i class="bi bi-arrow-left"></i> Back to list
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
const video   = document.getElementById('webcam');
const canvas  = document.getElementById('snapshot');
const preview = document.getElementById('preview');

// Start webcam
navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => { video.srcObject = stream; })
    .catch(err => { alert('Could not access webcam: ' + err.message); });

function takeSnapshot() {
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);

    const dataUrl = canvas.toDataURL('image/png');
    preview.src = dataUrl;
    preview.style.display = 'block';
    video.style.display   = 'none';

    document.getElementById('captureBtn').classList.add('d-none');
    document.getElementById('retakeBtn').classList.remove('d-none');
    document.getElementById('saveBtn').classList.remove('d-none');
}

function retake() {
    preview.style.display = 'none';
    video.style.display   = 'block';
    document.getElementById('captureBtn').classList.remove('d-none');
    document.getElementById('retakeBtn').classList.add('d-none');
    document.getElementById('saveBtn').classList.add('d-none');
}

function savePhoto() {
    document.getElementById('face_image').value = preview.src;
    document.getElementById('faceForm').submit();
}
</script>
@endpush
