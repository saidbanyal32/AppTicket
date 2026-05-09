<?php

return [
    'resources' => [
        'tickets' => [
            'view' => 'tickets.view',
            'create' => 'tickets.create',
            'update' => 'tickets.update',
            'delete' => 'tickets.delete',
            'assign' => 'tickets.assign',
            'approve' => 'tickets.approve',
        ],
        'help' => [
            'view' => 'help.view',
            'create' => 'help.create',
            'update' => 'help.edit',
            'delete' => 'help.delete',
            'publish' => 'help.publish',
            'export' => 'help.view',
        ],
        'settings' => [
            'view' => 'settings.view',
            'create' => 'settings.update',
            'update' => 'settings.update',
            'delete' => 'settings.update',
        ],
        'modules' => ['view' => 'permissions.view', 'create' => 'permissions.manage', 'update' => 'permissions.manage', 'delete' => 'permissions.manage'],
        'actions' => ['view' => 'permissions.view', 'create' => 'permissions.manage', 'update' => 'permissions.manage', 'delete' => 'permissions.manage'],
        'permissions' => ['view' => 'permissions.view', 'create' => 'permissions.create', 'update' => 'permissions.update', 'delete' => 'permissions.delete'],
        'role-permissions' => ['view' => ['role-permissions.manage', 'roles.update'], 'create' => ['role-permissions.manage', 'roles.update'], 'update' => ['role-permissions.manage', 'roles.update'], 'delete' => ['role-permissions.manage', 'roles.update']],
    ],

    'menu' => [
        'custom' => [
            'tickets' => ['permission' => 'tickets.view'],
            'help' => ['permission' => 'help.view'],
            'notifications' => ['permission' => null],
            'settings' => ['permission' => 'settings.view'],
        ],
    ],
];
