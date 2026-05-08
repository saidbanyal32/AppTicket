<?php

return [
    'resources' => [
        'tickets' => [
            'view' => ['ticket.tab.my_request', 'ticket.tab.need_assignment', 'ticket.tab.assign_to_me', 'ticket.tab.overdue', 'ticket.tab.closed', 'ticket.tab.all'],
            'create' => ['tickets.create', 'ticket.tab.my_request'],
            'update' => 'tickets.update',
            'delete' => 'tickets.delete',
            'assign' => 'tickets.assign',
            'approve' => 'tickets.approve',
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
            'tickets' => ['permission' => ['ticket.tab.my_request', 'ticket.tab.need_assignment', 'ticket.tab.assign_to_me', 'ticket.tab.overdue', 'ticket.tab.closed', 'ticket.tab.all']],
            'notifications' => ['permission' => null],
            'settings' => ['permission' => 'settings.view'],
        ],
    ],
];
