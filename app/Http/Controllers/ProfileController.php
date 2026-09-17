<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
        ]);

        $photo = $request->file('photo');
        // Keep photos with account data so container rebuilds do not remove uploads.
        $request->user()->profilePhoto()->updateOrCreate([], [
            'mime_type' => $photo->getMimeType(),
            'contents' => base64_encode(file_get_contents($photo->getRealPath())),
        ]);

        return redirect()->route('dashboard')->with('success', 'Profile picture saved.');
    }

    public function photo(Request $request): Response
    {
        $photo = $request->user()->profilePhoto()->firstOrFail();

        return response(base64_decode($photo->contents), 200, [
            'Content-Type' => $photo->mime_type,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
