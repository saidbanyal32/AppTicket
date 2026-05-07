@extends('layouts.erp')

@php($actions = '<a class="btn btn-sm btn-primary" href="'.route('settings.create').'"><i class="bi bi-plus-lg me-1"></i>Create Setting</a>')

@section('content')
    @if (session('status'))<div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>@endif
    <section class="erp-panel">
        <div class="erp-table-wrap">
            <table class="table table-hover align-middle">
                <thead><tr><th>Key</th><th>Value</th><th>Type</th><th>Description</th><th style="width:110px">Actions</th></tr></thead>
                <tbody>
                    @foreach ($settings as $setting)
                        <tr>
                            <td class="fw-semibold">{{ $setting->key }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($setting->value, 70) }}</td>
                            <td>{{ $setting->type ?? '-' }}</td>
                            <td>{{ $setting->description ?? '-' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('settings.edit', $setting) }}"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('settings.destroy', $setting) }}" onsubmit="return confirm('Delete setting?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-2">{{ $settings->links() }}</div>
    </section>
@endsection
