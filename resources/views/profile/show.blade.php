@extends('layouts.erp')

@php
    $photoUrl = $user->photo ? \Illuminate\Support\Facades\Storage::disk('public')->url($user->photo) : null;
    $initial = \Illuminate\Support\Str::of($user->name ?? 'U')->substr(0, 1)->upper();
    $preferences = array_merge([
        'language' => 'id',
        'timezone' => config('app.timezone', 'Asia/Jakarta'),
        'email_notifications' => true,
        'compact_mode' => false,
    ], $preferences ?? []);
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-2">Periksa kembali isian yang bertanda merah.</div>
    @endif

    <div class="erp-profile-layout">
        <aside class="erp-panel erp-profile-summary">
            <div class="erp-panel-body">
                <div class="erp-profile-avatar-wrap">
                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $user->name }}" class="erp-profile-avatar js-profile-avatar-preview">
                    @else
                        <div class="erp-profile-avatar erp-profile-avatar-fallback js-profile-avatar-fallback">{{ $initial }}</div>
                        <img src="" alt="{{ $user->name }}" class="erp-profile-avatar js-profile-avatar-preview d-none">
                    @endif
                </div>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>
                <div class="erp-profile-meta">
                    <div>
                        <span>Jabatan</span>
                        <strong>{{ $user->jabatan?->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <span>Unit/Divisi</span>
                        <strong>{{ $user->unit?->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <span>Nomor HP</span>
                        <strong>{{ $user->phone ?: '-' }}</strong>
                    </div>
                    <div>
                        <span>Last Login</span>
                        <strong>{{ $user->last_login?->format('d M Y H:i') ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </aside>

        <div class="erp-profile-main">
            <section class="erp-panel" id="my-profile">
                <div class="erp-panel-header">
                    <h2 class="erp-panel-title">Biodata Pengguna</h2>
                </div>
                <div class="erp-panel-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="erp-form-grid">
                            <div class="col-span">
                                <label class="form-label" for="profile_name">Nama Lengkap</label>
                                <input class="form-control @error('name') is-invalid @enderror" id="profile_name" name="name" value="{{ old('name', $user->name) }}" autocomplete="name">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label" for="profile_email">Email</label>
                                <input class="form-control @error('email') is-invalid @enderror" id="profile_email" type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label" for="profile_phone">Nomor HP</label>
                                <input class="form-control @error('phone') is-invalid @enderror" id="profile_phone" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label" for="profile_jabatan">Jabatan</label>
                                <select class="form-select js-select2 @error('jabatan_id') is-invalid @enderror" id="profile_jabatan" name="jabatan_id">
                                    @foreach ($jabatan as $item)
                                        <option value="{{ $item->id }}" @selected(old('jabatan_id', $user->jabatan_id) === $item->id)>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('jabatan_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label" for="profile_unit">Unit/Divisi</label>
                                <select class="form-select js-select2 @error('unit_id') is-invalid @enderror" id="profile_unit" name="unit_id">
                                    @foreach ($units as $item)
                                        <option value="{{ $item->id }}" @selected(old('unit_id', $user->unit_id) === $item->id)>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('unit_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-span">
                                <label class="form-label" for="profile_photo">Foto Profile/Avatar</label>
                                <input class="form-control js-profile-avatar-input @error('photo') is-invalid @enderror" id="profile_photo" type="file" name="photo" accept="image/*">
                                <div class="form-text">Format gambar umum, maksimal 2 MB.</div>
                                @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-save me-1"></i> Save Profile</button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="erp-panel" id="account-settings">
                <div class="erp-panel-header">
                    <h2 class="erp-panel-title">Account Settings</h2>
                </div>
                <div class="erp-panel-body">
                    <form method="POST" action="{{ route('profile.preferences') }}">
                        @csrf
                        @method('PUT')

                        <div class="erp-form-grid">
                            <div>
                                <label class="form-label" for="pref_language">Bahasa</label>
                                <select class="form-select @error('language') is-invalid @enderror" id="pref_language" name="language">
                                    <option value="id" @selected(old('language', $preferences['language']) === 'id')>Indonesia</option>
                                    <option value="en" @selected(old('language', $preferences['language']) === 'en')>English</option>
                                </select>
                                @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label" for="pref_timezone">Timezone</label>
                                <select class="form-select js-select2 @error('timezone') is-invalid @enderror" id="pref_timezone" name="timezone">
                                    @foreach (['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'UTC'] as $timezone)
                                        <option value="{{ $timezone }}" @selected(old('timezone', $preferences['timezone']) === $timezone)>{{ $timezone }}</option>
                                    @endforeach
                                </select>
                                @error('timezone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" id="pref_email_notifications" type="checkbox" name="email_notifications" value="1" @checked(old('email_notifications', $preferences['email_notifications']))>
                                    <label class="form-check-label" for="pref_email_notifications">Email Notifications</label>
                                </div>
                            </div>
                            <div>
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" id="pref_compact_mode" type="checkbox" name="compact_mode" value="1" @checked(old('compact_mode', $preferences['compact_mode']))>
                                    <label class="form-check-label" for="pref_compact_mode">Compact Mode</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-sliders me-1"></i> Save Settings</button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="erp-panel" id="change-password">
                <div class="erp-panel-header">
                    <h2 class="erp-panel-title">Change Password</h2>
                </div>
                <div class="erp-panel-body">
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')

                        <div class="erp-form-grid">
                            <div>
                                <label class="form-label" for="current_password">Password Saat Ini</label>
                                <input class="form-control @error('current_password') is-invalid @enderror" id="current_password" type="password" name="current_password" autocomplete="current-password">
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label" for="password">Password Baru</label>
                                <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" autocomplete="new-password">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
                                <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-key me-1"></i> Change Password</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection
