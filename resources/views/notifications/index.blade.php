@extends('layouts.erp')

@php($actions = '<form method="POST" action="'.route('notifications.read-all').'">'.csrf_field().'<button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-check2-all me-1"></i>Mark all read</button></form>')

@section('content')
    @if (session('status'))<div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>@endif
    <section class="erp-panel">
        <div class="erp-panel-body p-0">
            @forelse ($notifications as $notification)
                <div class="erp-notification-row {{ $notification->is_read ? '' : 'unread' }}">
                    <div>
                        <strong>{{ $notification->title }}</strong>
                        <p>{{ $notification->message }}</p>
                        <span>{{ $notification->created_at?->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="d-flex gap-1">
                        @if ($notification->url)<a class="btn btn-sm btn-outline-primary" href="{{ $notification->url }}"><i class="bi bi-box-arrow-up-right"></i></a>@endif
                        @unless ($notification->is_read)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-check2"></i></button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="p-3 text-muted">No notifications.</div>
            @endforelse
        </div>
        <div class="p-2">{{ $notifications->links() }}</div>
    </section>
@endsection
