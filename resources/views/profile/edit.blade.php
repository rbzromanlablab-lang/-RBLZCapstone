@extends('layouts.app')

@section('title', 'My Profile | PARDS')
@section('page_title', 'My Profile')
@section('section_label', 'Account Settings')

@section('content')
    <div class="dashboard-card p-4 mx-auto" style="max-width: 560px;">
        <h2 class="h4">Profile Picture</h2>
        <p class="text-muted">Choose a photo for your account. You can change it here anytime.</p>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="text-center mb-4">
                @if (auth()->user()->profilePhoto()->exists())
                    <img src="{{ route('profile.photo') }}" alt="Your current profile picture" class="profile-avatar" style="width: 112px; height: 112px;" id="photoPreview">
                @else
                    <img alt="Selected profile picture preview" class="profile-avatar d-none" style="width: 112px; height: 112px;" id="photoPreview">
                    <i class="bi bi-person-circle text-secondary display-1" id="photoPlaceholder" aria-hidden="true"></i>
                @endif
            </div>
            <label for="photo" class="form-label">Choose profile picture</label>
            <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp"
                class="form-control @error('photo') is-invalid @enderror" aria-describedby="photoHelp" required>
            <div id="photoHelp" class="form-text">JPG, PNG or WebP. Maximum 2 MB and 4096 × 4096 pixels.</div>
            @error('photo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn btn-primary w-100 mt-4">Save Profile Picture</button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const photoInput = document.getElementById('photo');
        const photoPreview = document.getElementById('photoPreview');
        let previewUrl;
        photoInput.addEventListener('change', () => {
            const file = photoInput.files[0];
            if (!file) return;
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(file);
            photoPreview.src = previewUrl;
            photoPreview.classList.remove('d-none');
            document.getElementById('photoPlaceholder')?.classList.add('d-none');
        });
    </script>
@endpush
