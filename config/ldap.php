<?php

return [
    'auth_with' => env('AUTH_WITH', 'Basic'),
    'ldap_hosts' => env('LDAP_HOSTS'),
    'ldap_base_dn' => env('LDAP_BASE_DN'),
    'ldap_username' => env('LDAP_USERNAME'),
    'ldap_password' => env('LDAP_PASSWORD'),
    'ldap_port' => env('LDAP_PORT', 3268),
    'local_only_users' => array_map(
        'strtolower',
        explode(',', env('LDAP_LOCAL_ONLY_USERS', 'superadmin,admin,doctor'))
    ),
];
