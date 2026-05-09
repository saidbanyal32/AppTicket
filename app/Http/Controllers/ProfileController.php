<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfilePasswordRequest;
use App\Http\Requests\ProfilePreferencesRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Master\RefJabatan;
use App\Models\Master\RefUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user()->load(['unit', 'jabatan']);

        return view('profile.show', [
            'user' => $user,
            'units' => RefUnit::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'jabatan' => RefJabatan::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'preferences' => $user->account_preferences ?? [],
            'breadcrumbs' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'My Profile'],
            ],
            'title' => 'My Profile',
            'subtitle' => 'Kelola biodata, preferensi akun, dan password Anda.',
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $data['photo'] = $request->file('photo')->store('avatars', 'public');
        }

        $user->fill($data)->save();

        return back()->with('status', 'Profile berhasil diperbarui.');
    }

    public function preferences(ProfilePreferencesRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'account_preferences' => $request->validated(),
        ])->save();

        return back()->with('status', 'Preferensi akun berhasil disimpan.');
    }

    public function password(ProfilePasswordRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'password' => Hash::make($request->validated('password')),
        ])->save();

        return back()->with('status', 'Password berhasil diubah.');
    }
}
