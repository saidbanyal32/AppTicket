<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;

class UiAuthorizationService
{
    public function canAny(null|string|array $permissions, ?Authenticatable $user = null): bool
    {
        if ($permissions === null || $permissions === []) {
            return true;
        }

        $user ??= auth()->user();

        if (! $user) {
            return false;
        }

        foreach ((array) $permissions as $permission) {
            if ($permission && $user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function permissionForResource(string $resource, string $action = 'view'): null|string|array
    {
        $configured = config("access.resources.$resource.$action");

        if ($configured !== null) {
            return $configured;
        }

        return match ($action) {
            'view' => $resource.'.view',
            'create', 'update', 'delete' => [$resource.'.'.$action, $resource.'.manage'],
            default => $resource.'.'.$action,
        };
    }

    public function canResource(string $resource, string $action = 'view', ?Authenticatable $user = null): bool
    {
        return $this->canAny($this->permissionForResource($resource, $action), $user);
    }

    public function canMasterConfig(array $config, string $action = 'view', ?Authenticatable $user = null): bool
    {
        $resource = $this->resourceKeyFromRoute($config['route'] ?? null);

        return $resource !== null && $this->canResource($resource, $action, $user);
    }

    public function resourceKeyFromRoute(?string $route): ?string
    {
        if (! $route) {
            return null;
        }

        $resource = str($route)->after('master.')->after('master-ticketing.')->toString();

        return match ($resource) {
            'categories' => 'ticket-categories',
            'slas' => 'ticket-slas',
            default => $resource,
        };
    }

    public function authorizeResource(string $resource, string $action = 'view'): void
    {
        abort_unless($this->canResource($resource, $action), 403);
    }
}
