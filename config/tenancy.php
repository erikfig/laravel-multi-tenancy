<?php

return [
    'route_param' => env('TENANCY_ROUTE_PARAM', 'tenant'),
    'subdomains_pattern' => env('TENANCY_SUBDOMAIN_PATTERN', '^((?!www).)*$'),
    'domain' => env('TENANCY_DOMAIN', null),
    'userModel' => \App\User::class
];
