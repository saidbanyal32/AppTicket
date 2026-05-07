@php
    $palette = [
        'status' => [
            'OPEN' => 'info',
            'ASSIGNED' => 'primary',
            'IN_PROGRESS' => 'warning',
            'PENDING' => 'secondary',
            'RESOLVED' => 'success',
            'CLOSED' => 'dark',
            'REJECTED' => 'danger',
        ],
        'priority' => [
            'LOW' => 'secondary',
            'MEDIUM' => 'info',
            'HIGH' => 'warning',
            'CRITICAL' => 'danger',
        ],
    ];
    $class = $palette[$type][$value] ?? 'secondary';
@endphp
<span class="badge text-bg-{{ $class }}">{{ str_replace('_', ' ', $value) }}</span>
